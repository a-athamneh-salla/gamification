<?php

namespace Salla\Gamification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Badge extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gamification_badges';

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
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the store badges associated with this badge.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function storeBadges(): HasMany
    {
        return $this->hasMany(StoreBadge::class);
    }

    /**
     * Scope a query to only include active badges.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if a store has earned this badge.
     *
     * @param int $storeId
     * @return bool
     */
    public function isEarnedByStore(int $storeId): bool
    {
        return $this->storeBadges()->where('store_id', $storeId)->exists();
    }

    /**
     * Award this badge to a store.
     *
     * @param int $storeId
     * @return StoreBadge|null
     */
    public function awardToStore(int $storeId): ?StoreBadge
    {
        if ($this->isEarnedByStore($storeId)) {
            return null;
        }
        
        return StoreBadge::create([
            'store_id' => $storeId,
            'badge_id' => $this->id,
        ]);
    }
}