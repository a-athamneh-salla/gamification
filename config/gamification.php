<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gamification General Configuration
    |--------------------------------------------------------------------------
    |
    | This section contains general configuration options for the
    | gamification system.
    |
    */
    
    // The model class to use for stores (tenants)
    'store_model' => env('GAMIFICATION_STORE_MODEL', 'App\\Models\\Store'),
    
    // Cache settings
    'cache' => [
        'enabled' => true,
        'ttl' => env('GAMIFICATION_CACHE_TTL', 3600), // Time in seconds
        'prefix' => 'gamification_',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Level Up Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for integrating with the level-up package.
    |
    */
    'level_up' => [
        'enabled' => true,
        'points_multiplier' => env('GAMIFICATION_POINTS_MULTIPLIER', 1),
        'level_cap' => env('GAMIFICATION_LEVEL_CAP', 100),
        'level_cap_enabled' => env('GAMIFICATION_LEVEL_CAP_ENABLED', true),
        'points_continue' => env('GAMIFICATION_POINTS_CONTINUE', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Events Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for event processing in the gamification system.
    |
    */
    'events' => [
        'log_enabled' => env('GAMIFICATION_LOG_EVENTS', true),
        'clickhouse_enabled' => env('GAMIFICATION_CLICKHOUSE_ENABLED', false),
        'jitsu_enabled' => env('GAMIFICATION_JITSU_ENABLED', false),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the API endpoints exposed by the gamification system.
    |
    */
    'api' => [
        'prefix' => 'api/gamification',
        'middleware' => ['api', 'auth:api'],
        'rate_limit' => env('GAMIFICATION_API_RATE_LIMIT', 60), // Requests per minute
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Dashboard Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the optional administration dashboard.
    |
    */
    'dashboard' => [
        'enabled' => env('GAMIFICATION_DASHBOARD_ENABLED', true),
        'prefix' => 'admin/gamification',
        'middleware' => ['web', 'auth', 'admin'],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Notifications Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for notifications sent by the gamification system.
    |
    */
    'notifications' => [
        'enabled' => env('GAMIFICATION_NOTIFICATIONS_ENABLED', true),
        'channels' => ['database', 'broadcast'], // Available: database, mail, broadcast
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Seed Data
    |--------------------------------------------------------------------------
    |
    | Configuration for seeding initial data into the gamification system.
    |
    */
    'seed' => [
        'initial_badges' => [
            'welcome' => 'Welcome to Salla!',
            'first_product' => 'Created First Product',
            'first_order' => 'Received First Order',
            'store_setup' => 'Completed Store Setup',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Default Mission Categories
    |--------------------------------------------------------------------------
    |
    | Default categories for organizing missions.
    |
    */
    'mission_categories' => [
        'onboarding' => [
            'name' => 'Store Setup',
            'description' => 'Essential setup tasks for your store',
        ],
        'products' => [
            'name' => 'Product Management',
            'description' => 'Tasks related to managing your products',
        ],
        'marketing' => [
            'name' => 'Marketing',
            'description' => 'Marketing and promotion tasks',
        ],
        'orders' => [
            'name' => 'Orders',
            'description' => 'Tasks related to order management',
        ],
    ],
];