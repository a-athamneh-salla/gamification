<?php

namespace Modules\Gamification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreProgress extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gamification_store_progress';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'mission_id',
        'status',
        'progress_percentage',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'progress_percentage' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the mission associated with this progress record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    /**
     * Get the store associated with this progress record.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store(): BelongsTo
    {
        $storeModel = config('gamification.store_model');
        return $this->belongsTo($storeModel, 'store_id');
    }

    /**
     * Check if the mission is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the mission is in progress.
     *
     * @return bool
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if the mission is ignored.
     *
     * @return bool
     */
    public function isIgnored(): bool
    {
        return $this->status === 'ignored';
    }

    /**
     * Check if the mission is not started.
     *
     * @return bool
     */
    public function isNotStarted(): bool
    {
        return $this->status === 'not_started';
    }

    /**
     * Update the progress percentage based on completed tasks.
     *
     * @return bool
     */
    public function updateProgressPercentage(): bool
    {
        $mission = $this->mission;
        
        // If no tasks in mission, progress is 0
        if ($mission->tasks()->count() === 0) {
            $this->progress_percentage = 0;
            return $this->save();
        }
        
        // Count total tasks and completed tasks
        $totalTasks = $mission->tasks()->count();
        $completedTasks = TaskCompletion::where('store_id', $this->store_id)
            ->where('mission_id', $this->mission_id)
            ->where('status', 'completed')
            ->count();
        
        // Calculate percentage
        $percentage = ($completedTasks / $totalTasks) * 100;
        
        // Update progress
        $this->progress_percentage = $percentage;
        
        // If all tasks are completed, update status
        if ($percentage >= 100) {
            $this->status = 'completed';
            $this->completed_at = now();
        } else if ($percentage > 0) {
            $this->status = 'in_progress';
        }
        
        return $this->save();
    }

    /**
     * Mark the mission as ignored.
     *
     * @return bool
     */
    public function ignore(): bool
    {
        $this->status = 'ignored';
        return $this->save();
    }

    /**
     * Scope a query to only include completed missions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include in-progress missions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include not-started missions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotStarted($query)
    {
        return $query->where('status', 'not_started');
    }

    /**
     * Scope a query to only include ignored missions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIgnored($query)
    {
        return $query->where('status', 'ignored');
    }
}