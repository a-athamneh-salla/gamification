<?php

namespace Salla\Gamification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreBadge extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gamification_store_badges';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'badge_id',
        'earned_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'earned_at' => 'datetime',
    ];

    /**
     * Get the badge associated with this record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    /**
     * Get the store that earned this badge.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store(): BelongsTo
    {
        $storeModel = config('gamification.store_model');
        return $this->belongsTo($storeModel, 'store_id');
    }
}