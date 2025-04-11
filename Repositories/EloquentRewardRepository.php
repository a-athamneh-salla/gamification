<?php

namespace Salla\Gamification\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Salla\Gamification\Contracts\RewardRepository;
use Salla\Gamification\Models\Reward;
use Salla\Gamification\Models\Mission;
use Salla\Gamification\Models\StoreProgress;

class EloquentRewardRepository implements RewardRepository
{
    /**
     * Get all rewards for a mission.
     *
     * @param int $missionId
     * @return Collection
     */
    public function getAllForMission(int $missionId): Collection
    {
        return Reward::where('mission_id', $missionId)->get();
    }
    
    /**
     * Find a reward by ID.
     *
     * @param int|string $id
     * @return Reward|null
     */
    public function find($id): ?Reward
    {
        return Reward::find($id);
    }
    
    /**
     * Create a new reward.
     *
     * @param array $attributes
     * @return Reward
     */
    public function create(array $attributes): Reward
    {
        return Reward::create($attributes);
    }
    
    /**
     * Update a reward.
     *
     * @param Reward|int $reward
     * @param array $attributes
     * @return Reward
     */
    public function update($reward, array $attributes): Reward
    {
        if (!$reward instanceof Reward) {
            $reward = $this->find($reward);
        }
        
        if (!$reward) {
            throw new \InvalidArgumentException('Reward not found');
        }
        
        $reward->update($attributes);
        
        return $reward->refresh();
    }
    
    /**
     * Delete a reward.
     *
     * @param Reward|int $reward
     * @return bool
     */
    public function delete($reward): bool
    {
        if (!$reward instanceof Reward) {
            $reward = $this->find($reward);
        }
        
        if (!$reward) {
            throw new \InvalidArgumentException('Reward not found');
        }
        
        return $reward->delete();
    }
    
    /**
     * Grant rewards to a store for completing a mission.
     *
     * @param Mission $mission
     * @param int $storeId
     * @return bool
     */
    public function grantRewards(Mission $mission, int $storeId): bool
    {
        // Check if the mission is already completed by the store
        $storeProgress = StoreProgress::where('mission_id', $mission->id)
            ->where('store_id', $storeId)
            ->first();
            
        if (!$storeProgress || !$storeProgress->isCompleted()) {
            return false;
        }
        
        // Check if the rewards have already been granted
        // This check could be more sophisticated based on your requirements
        
        // Get all rewards for the mission
        $rewards = $this->getAllForMission($mission->id);
        
        $allSuccessful = true;
        
        // Grant each reward
        foreach ($rewards as $reward) {
            $success = $reward->processForStore($storeId);
            
            if (!$success) {
                $allSuccessful = false;
                // Optionally log the failure
            }
        }
        
        return $allSuccessful;
    }
    
    /**
     * Get all rewards earned by a store.
     *
     * @param int $storeId
     * @return Collection
     */
    public function getStoreRewards(int $storeId): Collection
    {
        // Get all completed missions for the store
        $completedMissionIds = StoreProgress::where('store_id', $storeId)
            ->where('status', 'completed')
            ->pluck('mission_id')
            ->toArray();
        
        // Get rewards for these missions
        return Reward::whereIn('mission_id', $completedMissionIds)->get();
    }
}