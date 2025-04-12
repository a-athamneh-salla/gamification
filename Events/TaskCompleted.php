<?php

namespace Modules\Gamification\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCompleted
{
    use Dispatchable, SerializesModels;

    /**
     * The store ID.
     *
     * @var int
     */
    public $storeId;

    /**
     * The task ID.
     *
     * @var int
     */
    public $taskId;
    
    /**
     * The mission ID.
     *
     * @var int
     */
    public $missionId;
    
    /**
     * Additional task data.
     *
     * @var array
     */
    public $taskData;

    /**
     * Create a new event instance.
     *
     * @param int $storeId
     * @param int $taskId
     * @param int $missionId
     * @param array $taskData
     * @return void
     */
    public function __construct(int $storeId, int $taskId, int $missionId, array $taskData = [])
    {
        $this->storeId = $storeId;
        $this->taskId = $taskId;
        $this->missionId = $missionId;
        $this->taskData = $taskData;
    }
}