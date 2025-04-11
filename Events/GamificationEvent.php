<?php

namespace Salla\Gamification\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GamificationEvent
{
    use Dispatchable, SerializesModels;

    /**
     * The event name.
     *
     * @var string
     */
    public $eventName;

    /**
     * The store ID.
     *
     * @var int
     */
    public $storeId;

    /**
     * The event payload.
     *
     * @var array
     */
    public $payload;

    /**
     * Create a new event instance.
     *
     * @param string $eventName
     * @param int $storeId
     * @param array $payload
     * @return void
     */
    public function __construct(string $eventName, int $storeId, array $payload = [])
    {
        $this->eventName = $eventName;
        $this->storeId = $storeId;
        $this->payload = $payload;
    }
}