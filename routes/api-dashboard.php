<?php


use Illuminate\Support\Facades\Route;
use Modules\DashboardApi\Http\Middleware\TokenableMiddleware;
use Modules\Gamification\Http\Controllers\TaskController;
use Modules\Gamification\Http\Controllers\MissionController;
use Modules\Gamification\Http\Controllers\RewardController;
use Modules\Gamification\Http\Controllers\RuleController;


Route::prefix('api/gamification')
    ->as('api.marketing_integration.')
    ->middleware(['api', TokenableMiddleware::class])->group(function () {

        // Event types endpoint
        Route::get('tasks/event-types', [TaskController::class, 'getEventTypes']);

        // Admin Tasks Management
        Route::apiResource('tasks', TaskController::class)->except(['index', 'show']);

        // Admin Missions Management
        Route::apiResource('missions', MissionController::class)->except(['index', 'show']);

        // Admin Rules Management
        Route::apiResource('rules', RuleController::class);
    
        // Admin Rewards Management
        Route::apiResource('rewards', RewardController::class)->except(['index', 'show']);
    });
