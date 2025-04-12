<?php

namespace Modules\Gamification\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RewardAwarded
{
    use Dispatchable, SerializesModels;

    /**
     * The store ID.
     *
     * @var int
     */
    public $storeId;

    /**
     * The reward ID.
     *
     * @var int
     */
    public $rewardId;
    
    /**
     * Additional reward data.
     *
     * @var array
     */
    public $rewardData;

    /**
     * Create a new event instance.
     *
     * @param int $storeId
     * @param int $rewardId
     * @param array $rewardData
     * @return void
     */
    public function __construct(int $storeId, int $rewardId, array $rewardData = [])
    {
        $this->storeId = $storeId;
        $this->rewardId = $rewardId;
        $this->rewardData = $rewardData;
    }
}