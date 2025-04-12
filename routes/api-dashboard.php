<?php


use Illuminate\Support\Facades\Route;
use Modules\DashboardApi\Http\Middleware\TokenableMiddleware;
use Salla\Gamification\Http\Controllers\TaskController;
use Salla\Gamification\Http\Controllers\MissionController;
use Salla\Gamification\Http\Controllers\RewardController;
// use Salla\Gamification\Http\Controllers\RuleController;



Route::prefix('api/admin/gamification')
    ->as('api.marketing_integration.')
    ->middleware(['api', 'admin', TokenableMiddleware::class])->group(function () {
        // Admin Tasks Management
        Route::apiResource('tasks', TaskController::class)->except(['index', 'show']);

        // Admin Missions Management
        Route::apiResource('missions', MissionController::class)->except(['index', 'show']);

        // Admin Rules Management
        // Route::apiResource('rules', RuleController::class);
    
        // Admin Rewards Management
        Route::apiResource('rewards', RewardController::class)->except(['index', 'show']);
    });
