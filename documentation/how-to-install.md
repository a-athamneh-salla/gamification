# Salla Gamification System - Installation Guide

This document provides step-by-step instructions for installing and configuring the Salla Gamification System in your Laravel application.

**Author**: Ahmad Athamneh <a.alathamneh@salla.sa>

## Prerequisites

Before installing the Salla Gamification System, ensure your environment meets the following requirements:

- PHP 8.1 or higher
- Laravel 9.x or higher
- MySQL 8.0 or higher (for relational data storage)
- Redis 6.x or higher (for caching and real-time state)
- Composer 2.x

## Installation Steps

### Step 1: Install the Package via Composer

```bash
composer require salla/gamification
```

### Step 2: Publish Configuration File

```bash
php artisan vendor:publish --provider="Salla\Gamification\GamificationServiceProvider" --tag="config"
```

This will create a new configuration file at `config/gamification.php` where you can customize the gamification settings.

### Step 3: Run Database Migrations

```bash
php artisan migrate
```

This will create all necessary database tables for the gamification system.

### Step 4: Seed Initial Data (Optional)

If you want to seed your database with sample data for testing:

```bash
php artisan db:seed --class="Salla\\Gamification\\Database\\Seeders\\GamificationSeeder"
```

## Configuration

### Basic Configuration

Open the configuration file at `config/gamification.php` and update the following settings:

```php
return [
    // Set the default tenant model (typically your Store model)
    'tenant_model' => \App\Models\Store::class,
    
    // Configure caching settings
    'cache' => [
        'enabled' => true,
        'ttl' => 60 * 24, // Cache for 24 hours
    ],
    
    // Configure event processing
    'events' => [
        'process_immediately' => true,
        'queue_name' => 'gamification', // For queued processing
    ],
    
    // Configure analytics integration
    'analytics' => [
        'enabled' => true,
        'provider' => 'jitsu', // Options: 'jitsu', 'custom'
    ],
];
```

### Store/Tenant Model Integration

Your Store/Tenant model should implement the necessary traits and interfaces to work with the gamification system:

```php
use Salla\Gamification\Traits\HasGamification;
use Salla\Gamification\Contracts\GamificationTarget;

class Store extends Model implements GamificationTarget
{
    use HasGamification;
    
    // Your existing model code...
}
```

### Event Setup

To make your application events work with the gamification system, you need to dispatch them through the gamification event system:

```php
use Salla\Gamification\Events\GamificationEvent;

// Example: When a product is created
event(new GamificationEvent('product_created', $store->id, [
    'product_id' => $product->id,
    'product_type' => $product->type,
    // Any other relevant data for task conditions
]));
```

## Frontend Integration

### Include the React Component

If you're using the provided React component, add it to your app's entry point:

```javascript
// In your app.js or equivalent
import SallaGamificationProgress from '@salla/gamification-ui';

// Then use it in your component
function Dashboard() {
  return (
    <div>
      <h1>Dashboard</h1>
      <SallaGamificationProgress storeId={currentStoreId} />
    </div>
  );
}
```

### API Endpoints

The main API endpoints available for the frontend to interact with:

- `GET /api/gamification/missions` - Get all missions
- `GET /api/gamification/missions/{id}` - Get details of a specific mission
- `GET /api/gamification/tasks` - Get all tasks
- `POST /api/gamification/tasks/{id}/complete` - Manually complete a task
- `POST /api/gamification/missions/{id}/ignore` - Ignore a mission
- `GET /api/gamification/progress/summary` - Get progress summary for current store

## Troubleshooting

### Common Issues

1. **Migration Issues**

If you encounter problems with migrations, try:

```bash
php artisan migrate:fresh --path=vendor/salla/gamification/database/migrations
```

2. **Event Processing Delays**

If events aren't being processed timely, check your queue configuration:

```bash
php artisan queue:work gamification
```

3. **Caching Issues**

To clear the gamification cache:

```bash
php artisan cache:clear --tag=salla-gamification
```

### Logging

The package writes logs to the `gamification` channel. To see these logs, ensure your `config/logging.php` file includes:

```php
'channels' => [
    // ... other channels
    'gamification' => [
        'driver' => 'single',
        'path' => storage_path('logs/gamification.log'),
        'level' => 'debug',
    ],
],
```

## Support

If you encounter any issues or need assistance, please:

1. Check the documentation in the `/documentation` folder
2. Look for existing issues in the GitHub repository
3. Contact Salla support at support@salla.sa

## License

The Salla Gamification System is proprietary software licensed exclusively for use in Salla platform applications.