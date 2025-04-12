<?php

namespace Modules\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Gamification\Models\Badge;

class BadgeSeeder extends Seeder
{
    /**
     * Seed the badges table with initial achievements.
     *
     * @return void
     */
    public function run()
    {
        $badges = [
            [
                'key' => 'welcome',
                'name' => 'Welcome to Salla!',
                'description' => 'Congratulations on joining Salla and starting your e-commerce journey!',
                'image' => 'badges/welcome.png',
                'is_active' => true,
            ],
            [
                'key' => 'first-product',
                'name' => 'First Product',
                'description' => 'You added your first product to your store.',
                'image' => 'badges/first-product.png',
                'is_active' => true,
            ],
            [
                'key' => 'first-sale',
                'name' => 'First Sale',
                'description' => 'You made your first sale!',
                'image' => 'badges/first-sale.png',
                'is_active' => true,
            ],
            [
                'key' => 'store-setup',
                'name' => 'Store Setup Genius',
                'description' => 'You completed all the essential store setup tasks.',
                'image' => 'badges/store-setup.png',
                'is_active' => true,
            ],
            [
                'key' => 'marketing-pro',
                'name' => 'Marketing Pro',
                'description' => 'You set up all the essential marketing tools for your store.',
                'image' => 'badges/marketing-pro.png',
                'is_active' => true,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::firstOrCreate(['key' => $badge['key']], $badge);
        }

        $this->command->info('Badges seeded successfully!');
    }
}