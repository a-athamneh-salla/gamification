<?php

namespace Salla\Gamification\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Salla\Gamification\Models\Reward;
use Salla\Gamification\Models\Mission;

interface RewardRepository
{
    /**
     * Get all rewards for a mission.
     *
     * @param int $missionId
     * @return Collection
     */
    public function getAllForMission(int $missionId): Collection;
    
    /**
     * Find a reward by ID.
     *
     * @param int|string $id
     * @return Reward|null
     */
    public function find($id): ?Reward;
    
    /**
     * Create a new reward.
     *
     * @param array $attributes
     * @return Reward
     */
    public function create(array $attributes): Reward;
    
    /**
     * Update a reward.
     *
     * @param Reward|int $reward
     * @param array $attributes
     * @return Reward
     */
    public function update($reward, array $attributes): Reward;
    
    /**
     * Delete a reward.
     *
     * @param Reward|int $reward
     * @return bool
     */
    public function delete($reward): bool;
    
    /**
     * Grant rewards to a store for completing a mission.
     *
     * @param Mission $mission
     * @param int $storeId
     * @return bool
     */
    public function grantRewards(Mission $mission, int $storeId): bool;
    
    /**
     * Get all rewards earned by a store.
     *
     * @param int $storeId
     * @return Collection
     */
    public function getStoreRewards(int $storeId): Collection;
}