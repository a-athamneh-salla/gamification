<?php

namespace Salla\Gamification;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Salla\Gamification\Contracts\TaskRepository;
use Salla\Gamification\Contracts\MissionRepository;
use Salla\Gamification\Contracts\RuleRepository;
use Salla\Gamification\Contracts\RewardRepository;
use Salla\Gamification\Repositories\EloquentTaskRepository;
use Salla\Gamification\Repositories\EloquentMissionRepository;
use Salla\Gamification\Repositories\EloquentRuleRepository;
use Salla\Gamification\Repositories\EloquentRewardRepository;
use Salla\Gamification\Services\GamificationService;
use Salla\Gamification\Events\GamificationEvent;
use Salla\Gamification\Listeners\ProcessGameEvent;

/**
 * Gamification Service Provider
 * 
 * @author Ahmad Athamneh <a.alathamneh@salla.sa>
 */
class GamificationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/config/gamification.php' => config_path('gamification.php'),
        ], 'gamification-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/database/migrations/' => database_path('migrations'),
        ], 'gamification-migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/routes/api.php');

        // Register event listeners
        Event::listen(
            GamificationEvent::class,
            ProcessGameEvent::class
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/config/gamification.php', 'gamification'
        );

        // Bind repositories
        $this->app->bind(TaskRepository::class, EloquentTaskRepository::class);
        $this->app->bind(MissionRepository::class, EloquentMissionRepository::class);
        $this->app->bind(RuleRepository::class, EloquentRuleRepository::class);
        $this->app->bind(RewardRepository::class, EloquentRewardRepository::class);

        // Register the main service
        $this->app->singleton('gamification', function ($app) {
            return new GamificationService(
                $app->make(TaskRepository::class),
                $app->make(MissionRepository::class),
                $app->make(RuleRepository::class),
                $app->make(RewardRepository::class)
            );
        });

        // Register the level-up package integration if enabled
        if (config('gamification.level_up.enabled')) {
            $this->registerLevelUpIntegration();
        }
    }

    /**
     * Register Level-Up package integration.
     *
     * @return void
     */
    protected function registerLevelUpIntegration()
    {
        // Set up level-up package configuration
        config([
            'level-up.user.model' => config('gamification.store_model'),
            'level-up.user.foreign_key' => 'store_id',
            'level-up.level_cap.enabled' => config('gamification.level_up.level_cap_enabled'),
            'level-up.level_cap.level' => config('gamification.level_up.level_cap'),
            'level-up.points_continue' => config('gamification.level_up.points_continue'),
        ]);
    }
}