<?php

use Illuminate\Support\Facades\Route;

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
    Route::get('/missions', 'Modules\Gamification\Http\Controllers\MissionController@index');
    Route::get('/missions/{id}', 'Modules\Gamification\Http\Controllers\MissionController@show');
    Route::post('/missions/{id}/ignore', 'Modules\Gamification\Http\Controllers\MissionController@ignore');
    
    // Progress endpoints
    Route::get('/progress/summary', 'Modules\Gamification\Http\Controllers\ProgressController@summary');
    
    // Task endpoints
    Route::get('/tasks', 'Modules\Gamification\Http\Controllers\TaskController@index');
    Route::post('/tasks/{id}/complete', 'Modules\Gamification\Http\Controllers\TaskController@completeManually');
    
    // Reward endpoints
    Route::get('/rewards', 'Modules\Gamification\Http\Controllers\RewardController@index');
    
    // Badge endpoints
    Route::get('/badges', 'Modules\Gamification\Http\Controllers\BadgeController@index');
    
    // Leaderboard endpoints
    Route::get('/leaderboard', 'Modules\Gamification\Http\Controllers\LeaderboardController@index');
});
