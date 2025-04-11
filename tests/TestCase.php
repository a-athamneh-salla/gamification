<?php

namespace Salla\Gamification\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Salla\Gamification\GamificationServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            GamificationServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite in memory
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set the store model for testing
        $app['config']->set('gamification.store_model', 'Salla\Gamification\Tests\Models\Store');
        
        // Configure level-up package for testing
        $app['config']->set('gamification.level_up.enabled', true);
        $app['config']->set('level-up.user.model', 'Salla\Gamification\Tests\Models\Store');
        $app['config']->set('level-up.user.foreign_key', 'store_id');
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Run the migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        // Also run the level-up migrations if we're using the package
        if (config('gamification.level_up.enabled')) {
            $this->loadMigrationsFrom(dirname(__DIR__, 3) . '/vendor/cjmellor/level-up/database/migrations');
        }
    }
}