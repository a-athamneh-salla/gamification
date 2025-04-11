<?php

namespace Salla\Gamification\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Salla\Gamification\Contracts\RuleRepository;
use Salla\Gamification\Models\Rule;
use Salla\Gamification\Models\Mission;

class EloquentRuleRepository implements RuleRepository
{
    /**
     * Get all rules for a mission.
     *
     * @param int $missionId
     * @return Collection
     */
    public function getAllForMission(int $missionId): Collection
    {
        return Rule::where('mission_id', $missionId)->get();
    }
    
    /**
     * Find a rule by ID.
     *
     * @param int|string $id
     * @return Rule|null
     */
    public function find($id): ?Rule
    {
        return Rule::find($id);
    }
    
    /**
     * Create a new rule.
     *
     * @param array $attributes
     * @return Rule
     */
    public function create(array $attributes): Rule
    {
        return Rule::create($attributes);
    }
    
    /**
     * Update a rule.
     *
     * @param Rule|int $rule
     * @param array $attributes
     * @return Rule
     */
    public function update($rule, array $attributes): Rule
    {
        if (!$rule instanceof Rule) {
            $rule = $this->find($rule);
        }
        
        if (!$rule) {
            throw new \InvalidArgumentException('Rule not found');
        }
        
        $rule->update($attributes);
        
        return $rule->refresh();
    }
    
    /**
     * Delete a rule.
     *
     * @param Rule|int $rule
     * @return bool
     */
    public function delete($rule): bool
    {
        if (!$rule instanceof Rule) {
            $rule = $this->find($rule);
        }
        
        if (!$rule) {
            throw new \InvalidArgumentException('Rule not found');
        }
        
        return $rule->delete();
    }
    
    /**
     * Get start rules for a mission.
     *
     * @param int $missionId
     * @return Collection
     */
    public function getStartRulesForMission(int $missionId): Collection
    {
        return Rule::where('mission_id', $missionId)
            ->where('rule_type', 'start')
            ->get();
    }
    
    /**
     * Get finish rules for a mission.
     *
     * @param int $missionId
     * @return Collection
     */
    public function getFinishRulesForMission(int $missionId): Collection
    {
        return Rule::where('mission_id', $missionId)
            ->where('rule_type', 'finish')
            ->get();
    }
    
    /**
     * Check if a mission can be started for a store based on rules.
     *
     * @param Mission $mission
     * @param int $storeId
     * @return bool
     */
    public function canMissionStart(Mission $mission, int $storeId): bool
    {
        $startRules = $this->getStartRulesForMission($mission->id);
        
        // If no start rules, the mission can start
        if ($startRules->isEmpty()) {
            return true;
        }
        
        // Check if all rules are satisfied
        foreach ($startRules as $rule) {
            if (!$rule->isSatisfied($storeId)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check if a mission is completed for a store based on rules.
     *
     * @param Mission $mission
     * @param int $storeId
     * @return bool
     */
    public function isMissionCompleted(Mission $mission, int $storeId): bool
    {
        $finishRules = $this->getFinishRulesForMission($mission->id);
        
        // If no finish rules, check completion status
        if ($finishRules->isEmpty()) {
            // Default behavior: check if all tasks are completed
            $totalTasks = $mission->tasks()->count();
            
            if ($totalTasks === 0) {
                return false; // No tasks to complete
            }
            
            $completedTasks = \Salla\Gamification\Models\TaskCompletion::where('store_id', $storeId)
                ->where('mission_id', $mission->id)
                ->where('status', 'completed')
                ->count();
                
            return $completedTasks >= $totalTasks;
        }
        
        // Check if all rules are satisfied
        foreach ($finishRules as $rule) {
            if (!$rule->isSatisfied($storeId)) {
                return false;
            }
        }
        
        return true;
    }
}