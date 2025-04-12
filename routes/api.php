<?php

use Illuminate\Support\Facades\Route;
use Salla\Gamification\Http\Controllers\TaskController;
use Salla\Gamification\Http\Controllers\MissionController;
use Salla\Gamification\Http\Controllers\RewardController;
use Salla\Gamification\Http\Controllers\ProgressController;
use Salla\Gamification\Http\Controllers\BadgeController;
use Salla\Gamification\Http\Controllers\LeaderboardController;
use Salla\Gamification\Http\Controllers\RuleController;

/*
|--------------------------------------------------------------------------
| Gamification API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the GamificationServiceProvider within a group
| with the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix' => config('gamification.api.prefix', 'api/gamification'),
    'middleware' => config('gamification.api.middleware', ['api', 'auth:api']),
], function () {
    // Mission endpoints
    Route::get('/missions', 'Salla\Gamification\Http\Controllers\MissionController@index');
    Route::get('/missions/{id}', 'Salla\Gamification\Http\Controllers\MissionController@show');
    Route::post('/missions/{id}/ignore', 'Salla\Gamification\Http\Controllers\MissionController@ignore');
    
    // Progress endpoints
    Route::get('/progress/summary', 'Salla\Gamification\Http\Controllers\ProgressController@summary');
    
    // Task endpoints
    Route::get('/tasks', 'Salla\Gamification\Http\Controllers\TaskController@index');
    Route::post('/tasks/{id}/complete', 'Salla\Gamification\Http\Controllers\TaskController@completeManually');
    
    // Reward endpoints
    Route::get('/rewards', 'Salla\Gamification\Http\Controllers\RewardController@index');
    
    // Badge endpoints
    Route::get('/badges', 'Salla\Gamification\Http\Controllers\BadgeController@index');
    
    // Leaderboard endpoints
    Route::get('/leaderboard', 'Salla\Gamification\Http\Controllers\LeaderboardController@index');
});

// Admin routes (requires admin middleware)
Route::prefix('api/admin/gamification')->middleware(['api', 'admin'])->group(function () {
    // Admin Tasks Management
    Route::apiResource('tasks', TaskController::class)->except(['index', 'show']);
    
    // Admin Missions Management
    Route::apiResource('missions', MissionController::class)->except(['index', 'show']);
    
    // Admin Rules Management
    Route::apiResource('rules', RuleController::class);
    
    // Admin Rewards Management
    Route::apiResource('rewards', RewardController::class)->except(['index', 'show']);
});