# Salla Gamification System

A powerful multi-tenant gamification engine designed to enhance merchant onboarding in Salla's e-commerce platform. This system guides merchants through setup tasks and rewards their progress.

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Running Tests](#running-tests)
- [Architecture](#architecture)
- [Contributing](#contributing)

## Overview

The Salla Gamification System is a comprehensive solution for implementing gamification mechanics within Salla's e-commerce platform. It provides a structured approach to onboarding merchants by breaking down complex setup processes into manageable tasks, organizing them into missions, and rewarding progress.

## Features

- **Tasks & Missions**: Define granular tasks and group them into themed missions
- **Event-Driven**: Automatically track task completion through system events
- **Rules Engine**: Flexible conditions for mission unlocking and progression
- **Multi-Tenant**: Complete data isolation between merchants
- **Rewards System**: Award points, badges, and other benefits upon completion
- **Flexible Configuration**: Easily configure tasks and missions through admin interfaces
- **Comprehensive Analytics**: Track merchant progress and engagement metrics

## Installation

```bash
# Install via Composer
composer require salla/gamification

# Publish configuration
php artisan vendor:publish --provider="Salla\Gamification\GamificationServiceProvider"

# Run migrations
php artisan migrate
```

## Configuration

After installation, configure the package in the `config/gamification.php` file:

```php
return [
    // Database connection for gamification tables
    'database' => [
        'connection' => env('GAMIFICATION_DB_CONNECTION', 'mysql'),
        'prefix' => env('GAMIFICATION_TABLE_PREFIX', 'gamification_'),
    ],
    
    // Level-up package integration
    'level_up' => [
        'enabled' => true,
        'user_model' => \App\Models\Store::class,
        'foreign_key' => 'store_id',
    ],
    
    // Event tracking configuration
    'events' => [
        'enable_tracking' => true,
        'provider' => 'jitsu',
    ],
    
    // Caching configuration
    'cache' => [
        'ttl' => 3600, // seconds
        'prefix' => 'gamification_',
    ],
];
```

## Usage

### Basic Usage

```php
// Get the gamification service
$gamificationService = app(GamificationService::class);

// Handle an event that might complete tasks
$result = $gamificationService->handleEvent('product_created', $storeId, [
    'product_id' => 123,
    'product_name' => 'Test Product',
]);

// Get available missions for a store
$missionRepository = app(MissionRepository::class);
$missions = $missionRepository->getAvailableForStore($storeId);

// Get progress summary
$summary = $gamificationService->getProgressSummary($storeId);
```

### Working with Tasks

```php
// Create a task
$taskRepository = app(TaskRepository::class);
$task = $taskRepository->create([
    'key' => 'add-first-product',
    'name' => 'Add Your First Product',
    'description' => 'Add your first product to your store',
    'points' => 100,
    'event_name' => 'product_created',
    'is_active' => true,
]);
```

### Working with Missions

```php
// Create a mission
$missionRepository = app(MissionRepository::class);
$mission = $missionRepository->create([
    'key' => 'store-setup',
    'name' => 'Set Up Your Store',
    'description' => 'Complete the initial store setup',
    'total_points' => 150,
    'is_active' => true,
    'sort_order' => 1,
]);

// Associate tasks with a mission
$mission->tasks()->attach([
    $task1->id => ['sort_order' => 1],
    $task2->id => ['sort_order' => 2],
]);
```

### Event Payload Conditions

You can create tasks that only complete when events include specific payload data:

```php
$task = Task::create([
    'key' => 'high-value-order',
    'name' => 'Receive High Value Order',
    'points' => 200,
    'event_name' => 'order_created',
    'event_payload_conditions' => [
        'total_amount' => 1000,
        'payment_status' => 'paid'
    ],
    'is_active' => true,
]);
```

### Using Lockers

Lock missions until prerequisites are completed:

```php
// Create a locker that requires another mission to be completed first
use Salla\Gamification\Models\Locker;

Locker::create([
    'mission_id' => $missionId,
    'condition_type' => 'mission_completion',
    'condition_payload' => [
        'mission_id' => $prerequisiteMissionId
    ],
]);
```

## Running Tests

The Salla Gamification System includes comprehensive unit tests. To run them:

```bash
# Navigate to the package directory
cd /path/to/salla/packages/gamification

# Run all tests
vendor/bin/phpunit

# Run specific tests
vendor/bin/phpunit --filter=MissionRepositoryTest

# Generate coverage report
vendor/bin/phpunit --coverage-html reports/
```

### Testing Notes

- The test suite uses an in-memory SQLite database by default
- Tests are organized into Unit and Feature categories
- All tests use a clean database state via migrations and rollbacks
- Mock Store model is provided for multi-tenant testing

### Troubleshooting Tests

If you encounter issues with the tests:

1. Make sure your PHP environment has the required extensions (PDO SQLite)
2. Check that the phpunit.xml configuration is properly set up
3. Verify that all dependencies are installed via Composer

## Architecture

The Salla Gamification System follows a service-repository pattern:

```ini
src/
  ├── Contracts/         # Interfaces for repositories
  ├── Events/            # Event classes for system events
  ├── Http/              # Controllers and API resources
  ├── Listeners/         # Event listeners
  ├── Models/            # Eloquent models
  ├── Repositories/      # Repository implementations
  ├── Services/          # Business logic services
  └── Traits/            # Reusable PHP traits
```

### Multi-Tenant Design

All data operations are scoped to a specific store (tenant) ID:

```php
// Example of tenant-scoped query
$tasks = TaskCompletion::where('store_id', $storeId)
    ->where('status', 'completed')
    ->get();
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

For more detailed information about the system, please refer to the [DOCUMENTATION.md](DOCUMENTATION.md) file.