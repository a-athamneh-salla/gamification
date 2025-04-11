<?php

namespace Salla\Gamification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reward extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gamification_rewards';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mission_id',
        'reward_type',
        'reward_value',
        'reward_meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reward_meta' => 'json',
    ];

    /**
     * Get the mission that owns the reward.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    /**
     * Process the reward for a specific store.
     *
     * @param int $storeId
     * @return bool
     */
    public function processForStore(int $storeId): bool
    {
        // Handle different reward types
        switch ($this->reward_type) {
            case 'points':
                return $this->awardPoints($storeId);
                
            case 'badge':
                return $this->awardBadge($storeId);
                
            case 'coupon':
                return $this->awardCoupon($storeId);
                
            case 'feature_unlock':
                return $this->unlockFeature($storeId);
                
            default:
                return false;
        }
    }

    /**
     * Award points to a store.
     *
     * @param int $storeId
     * @return bool
     */
    protected function awardPoints(int $storeId): bool
    {
        $points = (int) $this->reward_value;
        
        if ($points <= 0) {
            return false;
        }

        // Use level-up package integration if enabled
        if (config('gamification.level_up.enabled')) {
            // Get the store model (user entity for level-up)
            $storeModel = config('gamification.store_model');
            $store = $storeModel::find($storeId);
            
            if (!$store) {
                return false;
            }
            
            // Calculate multiplier from config
            $multiplier = config('gamification.level_up.points_multiplier', 1);
            $pointsToAward = $points * $multiplier;
            
            // Award points using level-up
            try {
                $store->addPoints($pointsToAward, null, 'Completed mission: ' . $this->mission->name);
                return true;
            } catch (\Exception $e) {
                // Log error
                return false;
            }
        }
        
        // If level-up not enabled, we could implement our own points system
        // This is a placeholder for custom implementation
        return true;
    }

    /**
     * Award a badge to a store.
     *
     * @param int $storeId
     * @return bool
     */
    protected function awardBadge(int $storeId): bool
    {
        $badgeKey = $this->reward_value;
        
        // Find the badge by key
        $badge = Badge::where('key', $badgeKey)->first();
        
        if (!$badge) {
            return false;
        }
        
        // Check if the store already has this badge
        $alreadyAwarded = StoreBadge::where('store_id', $storeId)
            ->where('badge_id', $badge->id)
            ->exists();
            
        if ($alreadyAwarded) {
            return true; // Already awarded, consider it successful
        }
        
        // Award the badge
        StoreBadge::create([
            'store_id' => $storeId,
            'badge_id' => $badge->id,
        ]);
        
        return true;
    }

    /**
     * Award a coupon to a store.
     *
     * @param int $storeId
     * @return bool
     */
    protected function awardCoupon(int $storeId): bool
    {
        // Implementation would depend on the coupon system in place
        // This is a placeholder for custom implementation
        
        return true;
    }

    /**
     * Unlock a feature for a store.
     *
     * @param int $storeId
     * @return bool
     */
    protected function unlockFeature(int $storeId): bool
    {
        // Implementation would depend on the feature flagging system
        // This is a placeholder for custom implementation
        
        return true;
    }
}