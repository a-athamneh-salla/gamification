<?php

namespace Modules\Gamification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gamification_tasks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'name',
        'description',
        'points',
        'icon',
        'event_name',
        'event_payload_conditions',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'event_payload_conditions' => 'json',
        'is_active' => 'boolean',
        'points' => 'integer',
    ];

    /**
     * Get the missions that this task belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function missions(): BelongsToMany
    {
        return $this->belongsToMany(Mission::class, 'gamification_mission_tasks')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    /**
     * Get the task completions for this task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taskCompletions(): HasMany
    {
        return $this->hasMany(TaskCompletion::class);
    }

    /**
     * Check if the event payload matches the conditions for this task.
     *
     * @param array $payload
     * @return bool
     */
    public function matchesEventPayload(array $payload): bool
    {
        if (empty($this->event_payload_conditions)) {
            return true;
        }

        $conditions = $this->event_payload_conditions;

        foreach ($conditions as $key => $value) {
            if (!isset($payload[$key]) || $payload[$key] != $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Scope a query to only include active tasks.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to find tasks by event name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $eventName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByEventName($query, string $eventName)
    {
        return $query->where('event_name', $eventName);
    }
}