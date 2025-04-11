<?php

namespace Salla\Gamification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mission extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gamification_missions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'name',
        'description',
        'image',
        'total_points',
        'is_active',
        'start_date',
        'end_date',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'total_points' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'sort_order' => 'integer',
    ];

    /**
     * Get the tasks that belong to this mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'gamification_mission_tasks')
            ->withPivot('sort_order')
            ->orderBy('gamification_mission_tasks.sort_order')
            ->withTimestamps();
    }

    /**
     * Get the rules for this mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class);
    }

    /**
     * Get the start rules for this mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function startRules(): HasMany
    {
        return $this->hasMany(Rule::class)->where('rule_type', 'start');
    }

    /**
     * Get the finish rules for this mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function finishRules(): HasMany
    {
        return $this->hasMany(Rule::class)->where('rule_type', 'finish');
    }

    /**
     * Get the rewards for this mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }

    /**
     * Get the lockers for this mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lockers(): HasMany
    {
        return $this->hasMany(Locker::class);
    }

    /**
     * Get the store progress records for this mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function storeProgress(): HasMany
    {
        return $this->hasMany(StoreProgress::class);
    }

    /**
     * Get the task completion records for this mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taskCompletions(): HasMany
    {
        return $this->hasMany(TaskCompletion::class);
    }

    /**
     * Check if the mission is available (within start and end dates).
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        $now = now();
        
        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }
        
        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }
        
        return $this->is_active;
    }

    /**
     * Scope a query to only include active missions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include available missions based on dates.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        $now = now();
        
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $now);
            });
    }

    /**
     * Scope a query to order by sort order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}