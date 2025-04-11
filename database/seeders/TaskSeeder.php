<?php

namespace Salla\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;
use Salla\Gamification\Models\Task;

class TaskSeeder extends Seeder
{
    /**
     * Seed the tasks table with initial gamification tasks.
     *
     * @return void
     */
    public function run()
    {
        $tasks = [
            // Store Setup Tasks
            [
                'key' => 'update-store-logo',
                'name' => 'Add Your Store Logo',
                'description' => 'Upload your store logo to build your brand identity.',
                'points' => 50,
                'event_name' => 'store_logo_updated',
                'icon' => 'image',
                'is_active' => true,
            ],
            [
                'key' => 'update-store-name',
                'name' => 'Set Your Store Name',
                'description' => 'Choose a name that represents your brand.',
                'points' => 25,
                'event_name' => 'store_name_updated',
                'icon' => 'store',
                'is_active' => true,
            ],
            [
                'key' => 'customize-theme',
                'name' => 'Customize Your Theme',
                'description' => 'Make your store look unique by customizing the theme.',
                'points' => 75,
                'event_name' => 'theme_customized',
                'icon' => 'palette',
                'is_active' => true,
            ],

            // Product Tasks
            [
                'key' => 'add-first-product',
                'name' => 'Add Your First Product',
                'description' => 'Create your first product listing.',
                'points' => 100,
                'event_name' => 'product_created',
                'event_payload_conditions' => [
                    'is_first_product' => true
                ],
                'icon' => 'package',
                'is_active' => true,
            ],
            [
                'key' => 'add-product-images',
                'name' => 'Add Product Images',
                'description' => 'Upload high-quality images for your products.',
                'points' => 50,
                'event_name' => 'product_images_added',
                'icon' => 'image',
                'is_active' => true,
            ],
            [
                'key' => 'create-product-category',
                'name' => 'Create Product Category',
                'description' => 'Organize your products with categories.',
                'points' => 40,
                'event_name' => 'category_created',
                'icon' => 'folder',
                'is_active' => true,
            ],

            // Payment Tasks
            [
                'key' => 'setup-payment-method',
                'name' => 'Set Up Payment Method',
                'description' => 'Configure how you\'ll receive payments from customers.',
                'points' => 75,
                'event_name' => 'payment_method_configured',
                'icon' => 'credit-card',
                'is_active' => true,
            ],
            [
                'key' => 'setup-shipping',
                'name' => 'Configure Shipping Options',
                'description' => 'Set up shipping methods for your products.',
                'points' => 60,
                'event_name' => 'shipping_method_configured',
                'icon' => 'truck',
                'is_active' => true,
            ],

            // Marketing Tasks
            [
                'key' => 'social-media-links',
                'name' => 'Add Social Media Links',
                'description' => 'Connect your store to your social media accounts.',
                'points' => 35,
                'event_name' => 'social_media_linked',
                'icon' => 'share',
                'is_active' => true,
            ],
            [
                'key' => 'create-discount',
                'name' => 'Create First Discount',
                'description' => 'Create your first promotional discount code.',
                'points' => 45,
                'event_name' => 'discount_created',
                'icon' => 'tag',
                'is_active' => true,
            ],
            [
                'key' => 'setup-seo',
                'name' => 'Configure SEO Settings',
                'description' => 'Optimize your store for search engines.',
                'points' => 65,
                'event_name' => 'seo_configured',
                'icon' => 'search',
                'is_active' => true,
            ],

            // First Sale Tasks
            [
                'key' => 'first-order',
                'name' => 'Receive First Order',
                'description' => 'Congratulations on your first customer order!',
                'points' => 150,
                'event_name' => 'order_created',
                'event_payload_conditions' => [
                    'is_first_order' => true
                ],
                'icon' => 'shopping-cart',
                'is_active' => true,
            ],
            [
                'key' => 'first-order-shipped',
                'name' => 'Ship First Order',
                'description' => 'Ship your first customer order.',
                'points' => 50,
                'event_name' => 'order_shipped',
                'event_payload_conditions' => [
                    'is_first_order' => true
                ],
                'icon' => 'check-circle',
                'is_active' => true,
            ],
        ];

        foreach ($tasks as $task) {
            Task::firstOrCreate(['key' => $task['key']], $task);
        }

        $this->command->info('Tasks seeded successfully!');
    }
}