<?php

namespace Salla\Gamification\Services;

use Illuminate\Support\Collection;
use Salla\Gamification\Contracts\TaskRepository;
use Salla\Gamification\Contracts\MissionRepository;
use Salla\Gamification\Contracts\RuleRepository;
use Salla\Gamification\Contracts\RewardRepository;
use Salla\Gamification\Models\TaskCompletion;
use Salla\Gamification\Models\StoreProgress;

class GamificationService
{
    /**
     * The task repository instance.
     *
     * @var \Salla\Gamification\Contracts\TaskRepository
     */
    protected $taskRepository;
    
    /**
     * The mission repository instance.
     *
     * @var \Salla\Gamification\Contracts\MissionRepository
     */
    protected $missionRepository;
    
    /**
     * The rule repository instance.
     *
     * @var \Salla\Gamification\Contracts\RuleRepository
     */
    protected $ruleRepository;
    
    /**
     * The reward repository instance.
     *
     * @var \Salla\Gamification\Contracts\RewardRepository
     */
    protected $rewardRepository;
    
    /**
     * Create a new gamification service instance.
     *
     * @param \Salla\Gamification\Contracts\TaskRepository $taskRepository
     * @param \Salla\Gamification\Contracts\MissionRepository $missionRepository
     * @param \Salla\Gamification\Contracts\RuleRepository $ruleRepository
     * @param \Salla\Gamification\Contracts\RewardRepository $rewardRepository
     * @return void
     */
    public function __construct(
        TaskRepository $taskRepository,
        MissionRepository $missionRepository,
        RuleRepository $ruleRepository,
        RewardRepository $rewardRepository
    ) {
        $this->taskRepository = $taskRepository;
        $this->missionRepository = $missionRepository;
        $this->ruleRepository = $ruleRepository;
        $this->rewardRepository = $rewardRepository;
    }
    
    /**
     * Handle an event for a store.
     *
     * @param string $eventName
     * @param int $storeId
     * @param array $payload
     * @return array
     */
    public function handleEvent(string $eventName, int $storeId, array $payload = []): array
    {
        // Find tasks that match the event name
        $tasks = $this->taskRepository->findByEventName($eventName);
        
        $completedTasks = [];
        $progressUpdates = [];
        $completedMissions = [];
        $rewards = [];
        
        foreach ($tasks as $task) {
            // Check if the payload matches the task conditions
            if (!$task->matchesEventPayload($payload)) {
                continue;
            }
            
            // Get all missions related to this task
            $missions = $task->missions;
            
            foreach ($missions as $mission) {
                // Check if the mission is unlocked for this store
                if (!$this->missionRepository->isMissionUnlocked($mission->id, $storeId)) {
                    continue;
                }
                
                // Check if the task is already completed for this mission
                $taskCompletion = TaskCompletion::firstOrCreate([
                    'store_id' => $storeId,
                    'task_id' => $task->id,
                    'mission_id' => $mission->id,
                ], [
                    'status' => 'not_started'
                ]);
                
                // Mark as completed if not already
                if (!$taskCompletion->isCompleted()) {
                    $taskCompletion->markAsCompleted();
                    $completedTasks[] = [
                        'task_id' => $task->id,
                        'task_key' => $task->key,
                        'task_name' => $task->name,
                        'mission_id' => $mission->id,
                        'mission_key' => $mission->key,
                        'mission_name' => $mission->name,
                        'points' => $task->points,
                    ];
                    
                    // Get or create store progress
                    $storeProgress = StoreProgress::firstOrCreate([
                        'store_id' => $storeId,
                        'mission_id' => $mission->id,
                    ], [
                        'status' => 'not_started',
                        'progress_percentage' => 0,
                    ]);
                    
                    // Update progress percentage
                    $storeProgress->updateProgressPercentage();
                    $progressUpdates[] = [
                        'mission_id' => $mission->id,
                        'mission_key' => $mission->key,
                        'mission_name' => $mission->name,
                        'progress_percentage' => $storeProgress->progress_percentage,
                        'status' => $storeProgress->status,
                    ];
                    
                    // Check if the mission is completed
                    if ($storeProgress->isCompleted()) {
                        $completedMissions[] = [
                            'mission_id' => $mission->id,
                            'mission_key' => $mission->key,
                            'mission_name' => $mission->name,
                            'total_points' => $mission->total_points,
                        ];
                        
                        // Grant rewards for the completed mission
                        if ($this->rewardRepository->grantRewards($mission, $storeId)) {
                            // Get the mission rewards
                            $missionRewards = $this->rewardRepository->getAllForMission($mission->id);
                            
                            foreach ($missionRewards as $reward) {
                                $rewards[] = [
                                    'reward_id' => $reward->id,
                                    'reward_type' => $reward->reward_type,
                                    'reward_value' => $reward->reward_value,
                                    'mission_id' => $mission->id,
                                    'mission_name' => $mission->name,
                                ];
                            }
                        }
                    }
                }
            }
        }
        
        return [
            'completed_tasks' => $completedTasks,
            'progress_updates' => $progressUpdates,
            'completed_missions' => $completedMissions,
            'rewards' => $rewards,
        ];
    }
    
    /**
     * Get available missions for a store.
     *
     * @param int $storeId
     * @return Collection
     */
    public function getAvailableMissions(int $storeId): Collection
    {
        return $this->missionRepository->getAvailableForStore($storeId);
    }
    
    /**
     * Get missions with tasks for a store.
     *
     * @param int $storeId
     * @return Collection
     */
    public function getMissionsWithTasks(int $storeId): Collection
    {
        return $this->missionRepository->getMissionsWithTasks($storeId);
    }
    
    /**
     * Get progress summary for a store.
     *
     * @param int $storeId
     * @return array
     */
    public function getProgressSummary(int $storeId): array
    {
        $totalMissions = $this->missionRepository->getAvailableForStore($storeId)->count();
        $completedMissions = StoreProgress::where('store_id', $storeId)
            ->where('status', 'completed')
            ->count();
        
        // Calculate total tasks and completed tasks
        $tasks = $this->taskRepository->all(['is_active' => true]);
        $totalTasks = $tasks->count();
        
        $completedTasks = TaskCompletion::where('store_id', $storeId)
            ->where('status', 'completed')
            ->count();
        
        // Get total points earned
        $totalPoints = 0;
        
        // If level-up package is enabled, get points from there
        if (config('gamification.level_up.enabled')) {
            try {
                $storeModel = config('gamification.store_model');
                $store = $storeModel::find($storeId);
                $totalPoints = $store ? $store->getPoints() : 0;
            } catch (\Exception $e) {
                // Log error and fallback to calculated points
            }
        }
        
        if ($totalPoints === 0) {
            // Calculate from completed tasks
            $completedTasksWithPoints = TaskCompletion::where('store_id', $storeId)
                ->where('status', 'completed')
                ->with('task')
                ->get();
                
            foreach ($completedTasksWithPoints as $completion) {
                $totalPoints += $completion->task->points ?? 0;
            }
        }
        
        return [
            'total_missions' => $totalMissions,
            'completed_missions' => $completedMissions,
            'missions_completion_rate' => $totalMissions > 0 
                ? round(($completedMissions / $totalMissions) * 100, 2) 
                : 0,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'tasks_completion_rate' => $totalTasks > 0 
                ? round(($completedTasks / $totalTasks) * 100, 2) 
                : 0,
            'total_points' => $totalPoints,
        ];
    }
    
    /**
     * Get rewards earned by a store.
     *
     * @param int $storeId
     * @return Collection
     */
    public function getStoreRewards(int $storeId): Collection
    {
        return $this->rewardRepository->getStoreRewards($storeId);
    }
    
    /**
     * Ignore a mission for a store.
     *
     * @param int $missionId
     * @param int $storeId
     * @return bool
     */
    public function ignoreMission(int $missionId, int $storeId): bool
    {
        return $this->missionRepository->ignoreMission($missionId, $storeId);
    }
}