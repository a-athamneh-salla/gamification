<?php

namespace Modules\Gamification\Listeners;

use Modules\Gamification\Events\GamificationEvent;
use Modules\Gamification\Services\GamificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessGameEvent implements ShouldQueue
{
    /**
     * The gamification service instance.
     *
     * @var \Modules\Gamification\Services\GamificationService
     */
    protected $gamificationService;

    /**
     * Create the event listener.
     *
     * @param \Modules\Gamification\Services\GamificationService $gamificationService
     * @return void
     */
    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * Handle the event.
     *
     * @param  GamificationEvent  $event
     * @return void
     */
    public function handle(GamificationEvent $event)
    {
        // Log the event if logging is enabled
        if (config('gamification.events.log_enabled', true)) {
            $this->logEvent($event);
        }

        // Process the event
        $result = $this->gamificationService->handleEvent(
            $event->eventName,
            $event->storeId,
            $event->payload
        );

        // Dispatch additional events based on results
        $this->dispatchResultEvents($event->storeId, $result);
    }

    /**
     * Log the gamification event.
     *
     * @param GamificationEvent $event
     * @return void
     */
    protected function logEvent(GamificationEvent $event)
    {
        // Create a log entry in the gamification_events_log table
        \Modules\Gamification\Models\EventLog::create([
            'store_id' => $event->storeId,
            'event_name' => $event->eventName,
            'event_payload' => $event->payload,
            'processed' => false,
        ]);
    }

    /**
     * Dispatch additional events based on processing results.
     *
     * @param int $storeId
     * @param array $result
     * @return void
     */
    protected function dispatchResultEvents(int $storeId, array $result)
    {
        // Dispatch events for completed tasks
        foreach ($result['completed_tasks'] as $task) {
            event(new \Modules\Gamification\Events\TaskCompleted(
                $storeId,
                $task['task_id'],
                $task['mission_id'],
                $task
            ));
        }

        // Dispatch events for completed missions
        foreach ($result['completed_missions'] as $mission) {
            event(new \Modules\Gamification\Events\MissionCompleted(
                $storeId,
                $mission['mission_id'],
                $mission
            ));
        }

        // Dispatch events for awarded rewards
        foreach ($result['rewards'] as $reward) {
            event(new \Modules\Gamification\Events\RewardAwarded(
                $storeId,
                $reward['reward_id'],
                $reward
            ));
        }
    }
}