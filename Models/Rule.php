<?php

namespace Modules\Gamification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rule extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gamification_rules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mission_id',
        'rule_type',
        'condition_type',
        'condition_payload',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'condition_payload' => 'json',
    ];

    /**
     * Get the mission that owns the rule.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    /**
     * Check if the rule is satisfied for a given store.
     *
     * @param int $storeId
     * @return bool
     */
    public function isSatisfied(int $storeId): bool
    {
        // Handle different condition types
        switch ($this->condition_type) {
            case 'mission_completion':
                return $this->checkMissionCompletion($storeId);
            
            case 'tasks_completion':
                return $this->checkTasksCompletion($storeId);
                
            case 'date_range':
                return $this->checkDateRange();
                
            case 'custom':
                // Custom conditions would be handled by appropriate handlers
                return $this->checkCustomCondition($storeId);
                
            default:
                return false;
        }
    }

    /**
     * Check if a mission completion condition is satisfied.
     *
     * @param int $storeId
     * @return bool
     */
    protected function checkMissionCompletion(int $storeId): bool
    {
        $payload = $this->condition_payload;
        
        if (!isset($payload['mission_id'])) {
            return false;
        }
        
        $missionId = $payload['mission_id'];
        
        // Check if the store has completed the required mission
        return StoreProgress::where('store_id', $storeId)
            ->where('mission_id', $missionId)
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Check if tasks completion condition is satisfied.
     *
     * @param int $storeId
     * @return bool
     */
    protected function checkTasksCompletion(int $storeId): bool
    {
        $payload = $this->condition_payload;
        
        if (!isset($payload['task_ids']) || !is_array($payload['task_ids'])) {
            return false;
        }
        
        $taskIds = $payload['task_ids'];
        $requiredCount = $payload['required_count'] ?? count($taskIds);
        
        // Check how many of the required tasks have been completed
        $completedCount = TaskCompletion::where('store_id', $storeId)
            ->whereIn('task_id', $taskIds)
            ->where('status', 'completed')
            ->count();
        
        return $completedCount >= $requiredCount;
    }

    /**
     * Check if a date range condition is satisfied.
     *
     * @return bool
     */
    protected function checkDateRange(): bool
    {
        $payload = $this->condition_payload;
        
        if (!isset($payload['start_date']) || !isset($payload['end_date'])) {
            return false;
        }
        
        $now = now();
        $startDate = new \DateTime($payload['start_date']);
        $endDate = new \DateTime($payload['end_date']);
        
        return $now >= $startDate && $now <= $endDate;
    }

    /**
     * Check if a custom condition is satisfied.
     *
     * @param int $storeId
     * @return bool
     */
    protected function checkCustomCondition(int $storeId): bool
    {
        $payload = $this->condition_payload;
        
        // Custom conditions would be handled by specific rule handlers
        // This is a placeholder for extensibility
        
        return false;
    }

    /**
     * Scope a query to only include rules of a specific type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('rule_type', $type);
    }
}