<?php

namespace Salla\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;
use Salla\Gamification\Models\Mission;
use Salla\Gamification\Models\Reward;
use Salla\Gamification\Models\Badge;

class RewardSeeder extends Seeder
{
    /**
     * Seed the rewards table with mission rewards.
     *
     * @return void
     */
    public function run()
    {
        $rewardData = [
            'store-setup' => [
                [
                    'reward_type' => 'points',
                    'reward_value' => '150',
                ],
                [
                    'reward_type' => 'badge',
                    'reward_value' => 'store-setup',
                ],
            ],
            'product-catalog' => [
                [
                    'reward_type' => 'points',
                    'reward_value' => '190',
                ],
                [
                    'reward_type' => 'badge',
                    'reward_value' => 'first-product',
                ],
            ],
            'payment-shipping' => [
                [
                    'reward_type' => 'points',
                    'reward_value' => '135',
                ],
                [
                    'reward_type' => 'feature_unlock',
                    'reward_value' => 'advanced_analytics',
                    'reward_meta' => [
                        'feature_name' => 'Advanced Analytics',
                        'feature_description' => 'Access advanced analytics to track your store performance'
                    ],
                ],
            ],
            'marketing' => [
                [
                    'reward_type' => 'points',
                    'reward_value' => '145',
                ],
                [
                    'reward_type' => 'badge',
                    'reward_value' => 'marketing-pro',
                ],
            ],
            'first-sale' => [
                [
                    'reward_type' => 'points',
                    'reward_value' => '200',
                ],
                [
                    'reward_type' => 'badge',
                    'reward_value' => 'first-sale',
                ],
                [
                    'reward_type' => 'coupon',
                    'reward_value' => 'FIRSTSALE50',
                    'reward_meta' => [
                        'coupon_name' => '50% Off Premium Plan',
                        'coupon_description' => 'Get 50% off your first month of the Premium plan',
                        'expiry_days' => 30
                    ],
                ],
            ],
        ];

        // Create rewards for each mission
        foreach ($rewardData as $missionKey => $rewards) {
            $mission = Mission::where('key', $missionKey)->first();

            if ($mission) {
                foreach ($rewards as $rewardInfo) {
                    // Skip if the badge doesn't exist for badge rewards
                    if ($rewardInfo['reward_type'] === 'badge') {
                        $badge = Badge::where('key', $rewardInfo['reward_value'])->first();
                        if (!$badge) {
                            continue;
                        }
                    }

                    // Create reward
                    Reward::firstOrCreate(
                        [
                            'mission_id' => $mission->id,
                            'reward_type' => $rewardInfo['reward_type'],
                            'reward_value' => $rewardInfo['reward_value']
                        ],
                        [
                            'reward_meta' => $rewardInfo['reward_meta'] ?? null,
                        ]
                    );
                }
            }
        }

        $this->command->info('Rewards seeded successfully!');
    }
}