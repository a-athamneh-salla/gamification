<?php

namespace Modules\Gamification\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MissionCompleted
{
    use Dispatchable, SerializesModels;

    /**
     * The store ID.
     *
     * @var int
     */
    public $storeId;

    /**
     * The mission ID.
     *
     * @var int
     */
    public $missionId;
    
    /**
     * Additional mission data.
     *
     * @var array
     */
    public $missionData;

    /**
     * Create a new event instance.
     *
     * @param int $storeId
     * @param int $missionId
     * @param array $missionData
     * @return void
     */
    public function __construct(int $storeId, int $missionId, array $missionData = [])
    {
        $this->storeId = $storeId;
        $this->missionId = $missionId;
        $this->missionData = $missionData;
    }
}