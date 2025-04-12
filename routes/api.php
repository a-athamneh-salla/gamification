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
    Route::get('/missions', 'MissionController@index');
    Route::get('/missions/{id}', 'MissionController@show');
    Route::post('/missions/{id}/ignore', 'MissionController@ignore');
    
    // Progress endpoints
    Route::get('/progress/summary', 'ProgressController@summary');
    
    // Task endpoints
    Route::get('/tasks', 'TaskController@index');
    Route::post('/tasks/{id}/complete', 'TaskController@completeManually');
    
    // Reward endpoints
    Route::get('/rewards', 'RewardController@index');
    
    // Badge endpoints
    Route::get('/badges', 'BadgeController@index');
    
    // Leaderboard endpoints
    Route::get('/leaderboard', 'LeaderboardController@index');
});
