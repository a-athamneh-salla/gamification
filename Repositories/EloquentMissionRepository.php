<?php

namespace Salla\Gamification\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Salla\Gamification\Contracts\MissionRepository;
use Salla\Gamification\Models\Mission;
use Salla\Gamification\Models\Locker;
use Salla\Gamification\Models\StoreProgress;

class EloquentMissionRepository implements MissionRepository
{
    /**
     * Get all missions.
     *
     * @param array $filters
     * @return Collection
     */
    public function all(array $filters = []): Collection
    {
        $query = Mission::query();
        
        // Apply filters
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        
        if (isset($filters['available'])) {
            $query->available();
        }
        
        if (isset($filters['order_by'])) {
            $direction = $filters['order_direction'] ?? 'asc';
            $query->orderBy($filters['order_by'], $direction);
        } else {
            $query->ordered();
        }
        
        return $query->get();
    }

    /**
     * Get paginated missions.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Mission::query();
        
        // Apply filters
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        
        if (isset($filters['available'])) {
            $query->available();
        }
        
        if (isset($filters['order_by'])) {
            $direction = $filters['order_direction'] ?? 'asc';
            $query->orderBy($filters['order_by'], $direction);
        } else {
            $query->ordered();
        }
        
        return $query->paginate($perPage);
    }

    /**
     * Find a mission by ID.
     *
     * @param int|string $id
     * @return Mission|null
     */
    public function find($id): ?Mission
    {
        return Mission::find($id);
    }

    /**
     * Find a mission by key.
     *
     * @param string $key
     * @return Mission|null
     */
    public function findByKey(string $key): ?Mission
    {
        return Mission::where('key', $key)->first();
    }

    /**
     * Create a new mission.
     *
     * @param array $attributes
     * @return Mission
     */
    public function create(array $attributes): Mission
    {
        return Mission::create($attributes);
    }

    /**
     * Update a mission.
     *
     * @param Mission|int $mission
     * @param array $attributes
     * @return Mission
     */
    public function update($mission, array $attributes): Mission
    {
        if (!$mission instanceof Mission) {
            $mission = $this->find($mission);
        }
        
        if (!$mission) {
            throw new \InvalidArgumentException('Mission not found');
        }
        
        $mission->update($attributes);
        
        return $mission->refresh();
    }

    /**
     * Delete a mission.
     *
     * @param Mission|int $mission
     * @return bool
     */
    public function delete($mission): bool
    {
        if (!$mission instanceof Mission) {
            $mission = $this->find($mission);
        }
        
        if (!$mission) {
            throw new \InvalidArgumentException('Mission not found');
        }
        
        return $mission->delete();
    }

    /**
     * Get available missions for a store.
     *
     * @param int $storeId
     * @return Collection
     */
    public function getAvailableForStore(int $storeId): Collection
    {
        // Get all active and available missions
        $missions = Mission::active()
            ->available()
            ->ordered()
            ->get();
        
        // Filter out locked missions
        return $missions->filter(function ($mission) use ($storeId) {
            return $this->isMissionUnlocked($mission->id, $storeId);
        });
    }

    /**
     * Get missions with tasks for a store.
     *
     * @param int $storeId
     * @return Collection
     */
    public function getMissionsWithTasks(int $storeId): Collection
    {
        // Get available missions for the store
        $missions = $this->getAvailableForStore($storeId);
        
        // Load tasks and progress for each mission
        $missions->load(['tasks', 'storeProgress' => function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        }]);
        
        // Load task completion status
        foreach ($missions as $mission) {
            $taskIds = $mission->tasks->pluck('id')->toArray();
            
            $taskCompletions = \Salla\Gamification\Models\TaskCompletion::where('store_id', $storeId)
                ->where('mission_id', $mission->id)
                ->whereIn('task_id', $taskIds)
                ->get()
                ->keyBy('task_id');
            
            // Add completion status to each task
            $mission->tasks->each(function ($task) use ($taskCompletions) {
                $task->completion = $taskCompletions->get($task->id);
            });
        }
        
        return $missions;
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
        $progress = StoreProgress::firstOrCreate([
            'store_id' => $storeId,
            'mission_id' => $missionId,
        ], [
            'status' => 'not_started',
            'progress_percentage' => 0,
        ]);
        
        return $progress->ignore();
    }

    /**
     * Check if mission is unlocked for a store.
     *
     * @param int $missionId
     * @param int $storeId
     * @return bool
     */
    public function isMissionUnlocked(int $missionId, int $storeId): bool
    {
        $mission = $this->find($missionId);
        
        if (!$mission) {
            return false;
        }
        
        // Check if the mission has lockers
        $lockers = $mission->lockers;
        
        if ($lockers->isEmpty()) {
            return true; // No lockers means the mission is unlocked
        }
        
        // Check each locker
        foreach ($lockers as $locker) {
            if (!$locker->isUnlocked($storeId)) {
                return false; // At least one locker is still locked
            }
        }
        
        return true; // All lockers are unlocked
    }
}