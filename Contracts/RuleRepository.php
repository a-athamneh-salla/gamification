<?php

namespace Modules\Gamification\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Gamification\Models\Rule;
use Modules\Gamification\Models\Mission;

interface RuleRepository
{
    /**
     * Get all rules for a mission.
     *
     * @param int $missionId
     * @return Collection
     */
    public function getAllForMission(int $missionId): Collection;
    
    /**
     * Find a rule by ID.
     *
     * @param int|string $id
     * @return Rule|null
     */
    public function find($id): ?Rule;
    
    /**
     * Create a new rule.
     *
     * @param array $attributes
     * @return Rule
     */
    public function create(array $attributes): Rule;
    
    /**
     * Update a rule.
     *
     * @param Rule|int $rule
     * @param array $attributes
     * @return Rule
     */
    public function update($rule, array $attributes): Rule;
    
    /**
     * Delete a rule.
     *
     * @param Rule|int $rule
     * @return bool
     */
    public function delete($rule): bool;
    
    /**
     * Get start rules for a mission.
     *
     * @param int $missionId
     * @return Collection
     */
    public function getStartRulesForMission(int $missionId): Collection;
    
    /**
     * Get finish rules for a mission.
     *
     * @param int $missionId
     * @return Collection
     */
    public function getFinishRulesForMission(int $missionId): Collection;
    
    /**
     * Check if a mission can be started for a store based on rules.
     *
     * @param Mission $mission
     * @param int $storeId
     * @return bool
     */
    public function canMissionStart(Mission $mission, int $storeId): bool;
    
    /**
     * Check if a mission is completed for a store based on rules.
     *
     * @param Mission $mission
     * @param int $storeId
     * @return bool
     */
    public function isMissionCompleted(Mission $mission, int $storeId): bool;
}