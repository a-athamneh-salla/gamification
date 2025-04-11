# Salla Gamification System Documentation

## Overview

The Salla Gamification System is a multi-tenant gamification engine designed to enhance merchant onboarding in Salla's e-commerce platform. It guides merchants through setup tasks and rewards their progress.

## Core Components

### Tasks

Tasks are individual actions merchants need to complete (e.g., "Add First Product", "Set Store Logo"). When a task is completed, it contributes to mission progress.

### Missions

Missions are collections of related tasks with overall progress tracking. Completing all tasks in a mission marks the mission as complete and may unlock rewards or other missions.

### Rules

Rules govern the starting and finishing conditions for missions. They determine when missions become available and when they're considered complete.

### Rewards

Points, badges, or coupons granted upon mission completion. Rewards incentivize merchants to complete missions.

### Lockers

Conditions that keep missions locked until prerequisites are met. For example, a mission might be locked until another mission is completed.

## API Endpoints

### Missions

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/gamification/missions` | GET | Get all available missions for the current store |
| `/api/gamification/missions/{id}` | GET | Get detailed information about a specific mission |
| `/api/gamification/missions/{id}/ignore` | POST | Ignore a mission (mark it as skipped) |

### Tasks

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/gamification/tasks` | GET | Get all tasks for the current store |
| `/api/gamification/tasks/{id}/complete` | POST | Manually complete a task |

### Progress

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/gamification/progress/summary` | GET | Get a summary of the merchant's progress in the gamification system |

### Rewards

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/gamification/rewards` | GET | Get all rewards earned by the current store |

### Badges

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/gamification/badges` | GET | Get all badges earned by the current store |

### Leaderboard

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/gamification/leaderboard` | GET | Get the leaderboard data |

### Admin Endpoints

The following endpoints require admin privileges:

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/admin/gamification/tasks` | POST, PUT, DELETE | Create, update, or delete tasks |
| `/api/admin/gamification/missions` | POST, PUT, DELETE | Create, update, or delete missions |
| `/api/admin/gamification/rules` | GET, POST, PUT, DELETE | Manage rules for missions |
| `/api/admin/gamification/rewards` | POST, PUT, DELETE | Create, update, or delete rewards |

## Usage Examples

### Event Handling

```php
// Example of how events are processed
$gamificationService = app(GamificationService::class);

// When a product is created
$result = $gamificationService->handleEvent('product_created', $storeId, [
    'product_id' => 123,
    'product_name' => 'New Product'
]);

// Result contains information about completed tasks, missions, and rewards
```

### Getting Available Missions

```php
// Get all available missions for a store
$missionRepository = app(MissionRepository::class);
$missions = $missionRepository->getAvailableForStore($storeId);
```

### Getting Progress Summary

```php
// Get a summary of the merchant's progress
$gamificationService = app(GamificationService::class);
$summary = $gamificationService->getProgressSummary($storeId);

// $summary contains statistics about missions and tasks completion
```

### Ignoring a Mission

```php
// Allow merchant to ignore/skip a mission
$gamificationService = app(GamificationService::class);
$result = $gamificationService->ignoreMission($missionId, $storeId);
```

### Creating a Task

```php
// Create a new task
$taskRepository = app(TaskRepository::class);
$task = $taskRepository->create([
    'key' => 'add-first-product',
    'name' => 'Add Your First Product',
    'description' => 'Add your first product to your store',
    'points' => 100,
    'event_name' => 'product_created',
    'icon' => 'shopping-bag',
    'is_active' => true,
]);
```

### Creating a Mission

```php
// Create a new mission
$missionRepository = app(MissionRepository::class);
$mission = $missionRepository->create([
    'key' => 'store-setup',
    'name' => 'Set Up Your Store',
    'description' => 'Complete the initial store setup',
    'total_points' => 150,
    'is_active' => true,
    'sort_order' => 1,
]);
```

### Attaching Tasks to a Mission

```php
// Associate tasks with a mission
$mission->tasks()->attach([
    $task1->id => ['sort_order' => 1],
    $task2->id => ['sort_order' => 2],
]);
```

### Creating a Locker for a Mission

```php
// Create a locker that requires another mission to be completed
use Salla\Gamification\Models\Locker;

Locker::create([
    'mission_id' => $missionId,
    'condition_type' => 'mission_completion',
    'condition_payload' => [
        'mission_id' => $prerequisiteMissionId
    ],
]);
```

## Event Payload Conditions

```php
// Create a task that only completes for high-value orders
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

## Integration with Level-Up Package

```php
// Configuration in gamification.php
'level_up' => [
    'enabled' => true,
    'user_model' => \App\Models\Store::class,
    'foreign_key' => 'store_id',
]
```

## Running Unit Tests

To run the unit tests for the Salla Gamification System, follow these steps:

1. Navigate to the gamification package directory:

```bash
cd /path/to/Salla/packages/gamification
```

2. Run PHPUnit:

```bash
vendor/bin/phpunit
```

3. For specific tests:

```bash
vendor/bin/phpunit --filter=SpecificTestName
```

4. To generate a coverage report:

```bash
vendor/bin/phpunit --coverage-html reports/
```

## Multi-Tenant Architecture

All data in the system is scoped by a Store ID (tenant ID), ensuring data isolation between merchants:

```php
// Example of how queries are scoped to specific stores
$tasks = TaskCompletion::where('store_id', $storeId)
    ->where('status', 'completed')
    ->get();
```

## Event Tracking

```php
// Example of tracking a task completion event
event(new TaskCompleted($storeId, $taskId, $missionId, $points));
```

## Performance Considerations

- Use caching for configuration and progress data
- Optimize queries for multi-tenant environments 
- Ensure response times for event processing are under 1 second