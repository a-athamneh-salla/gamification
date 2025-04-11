<?php

namespace Salla\Gamification\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array handleEvent(string $eventName, int $storeId, array $payload = [])
 * @method static \Illuminate\Support\Collection getAvailableMissions(int $storeId)
 * @method static \Illuminate\Support\Collection getMissionsWithTasks(int $storeId)
 * @method static array getProgressSummary(int $storeId)
 * @method static \Illuminate\Support\Collection getStoreRewards(int $storeId)
 * @method static bool ignoreMission(int $missionId, int $storeId)
 * 
 * @see \Salla\Gamification\Services\GamificationService
 */
class Gamification extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'gamification';
    }
}