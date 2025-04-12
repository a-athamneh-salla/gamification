<?php

namespace Modules\Gamification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskCompletion extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gamification_task_completion';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'task_id',
        'mission_id',
        'status',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * Get the task associated with this completion record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the mission associated with this completion record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    /**
     * Get the store associated with this completion record.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store(): BelongsTo
    {
        $storeModel = config('gamification.store_model');
        return $this->belongsTo($storeModel, 'store_id');
    }

    /**
     * Check if the task is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the task is ignored.
     *
     * @return bool
     */
    public function isIgnored(): bool
    {
        return $this->status === 'ignored';
    }

    /**
     * Check if the task is not started.
     *
     * @return bool
     */
    public function isNotStarted(): bool
    {
        return $this->status === 'not_started';
    }

    /**
     * Mark the task as completed.
     *
     * @return bool
     */
    public function markAsCompleted(): bool
    {
        if ($this->isCompleted()) {
            return true;
        }
        
        $this->status = 'completed';
        $this->completed_at = now();
        
        $saved = $this->save();
        
        if ($saved) {
            // Update the mission progress
            $storeProgress = StoreProgress::firstOrCreate([
                'store_id' => $this->store_id,
                'mission_id' => $this->mission_id,
            ], [
                'status' => 'not_started',
                'progress_percentage' => 0,
            ]);
            
            $storeProgress->updateProgressPercentage();
        }
        
        return $saved;
    }

    /**
     * Mark the task as ignored.
     *
     * @return bool
     */
    public function ignore(): bool
    {
        $this->status = 'ignored';
        return $this->save();
    }

    /**
     * Scope a query to only include completed tasks.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include not-started tasks.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotStarted($query)
    {
        return $query->where('status', 'not_started');
    }

    /**
     * Scope a query to only include ignored tasks.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIgnored($query)
    {
        return $query->where('status', 'ignored');
    }
}