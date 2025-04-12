<?php

namespace Modules\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Gamification\Models\Mission;
use Modules\Gamification\Models\Task;
use Modules\Gamification\Models\Locker;

class MissionSeeder extends Seeder
{
    /**
     * Seed the missions table and associate tasks with missions.
     *
     * @return void
     */
    public function run()
    {
        // Define missions
        $missions = [
            [
                'key' => 'store-setup',
                'name' => 'Store Setup',
                'description' => 'Get your store set up with basic information and branding.',
                'image' => 'missions/store-setup.png',
                'total_points' => 150,
                'is_active' => true,
                'sort_order' => 1,
                'tasks' => [
                    'update-store-logo' => 1,
                    'update-store-name' => 2,
                    'customize-theme' => 3,
                ],
            ],
            [
                'key' => 'product-catalog',
                'name' => 'Product Catalog',
                'description' => 'Set up your product catalog to start selling.',
                'image' => 'missions/product-catalog.png',
                'total_points' => 190,
                'is_active' => true,
                'sort_order' => 2,
                'tasks' => [
                    'add-first-product' => 1,
                    'add-product-images' => 2,
                    'create-product-category' => 3,
                ],
                'lockers' => [
                    [
                        'condition_type' => 'mission_completion',
                        'condition_payload' => [
                            'mission_id' => 'store-setup' // Key will be replaced with ID
                        ]
                    ]
                ],
            ],
            [
                'key' => 'payment-shipping',
                'name' => 'Payment & Shipping',
                'description' => 'Configure how you\'ll receive payments and ship products.',
                'image' => 'missions/payment-shipping.png',
                'total_points' => 135,
                'is_active' => true,
                'sort_order' => 3,
                'tasks' => [
                    'setup-payment-method' => 1,
                    'setup-shipping' => 2,
                ],
                'lockers' => [
                    [
                        'condition_type' => 'mission_completion',
                        'condition_payload' => [
                            'mission_id' => 'product-catalog' // Key will be replaced with ID
                        ]
                    ]
                ],
            ],
            [
                'key' => 'marketing',
                'name' => 'Marketing',
                'description' => 'Set up marketing tools to drive traffic to your store.',
                'image' => 'missions/marketing.png',
                'total_points' => 145,
                'is_active' => true,
                'sort_order' => 4,
                'tasks' => [
                    'social-media-links' => 1,
                    'create-discount' => 2,
                    'setup-seo' => 3,
                ],
                'lockers' => [
                    [
                        'condition_type' => 'mission_completion',
                        'condition_payload' => [
                            'mission_id' => 'payment-shipping' // Key will be replaced with ID
                        ]
                    ]
                ],
            ],
            [
                'key' => 'first-sale',
                'name' => 'First Sale',
                'description' => 'Get your first sale and learn the order fulfillment process.',
                'image' => 'missions/first-sale.png',
                'total_points' => 200,
                'is_active' => true,
                'sort_order' => 5,
                'tasks' => [
                    'first-order' => 1,
                    'first-order-shipped' => 2,
                ],
                'lockers' => [
                    [
                        'condition_type' => 'mission_completion',
                        'condition_payload' => [
                            'mission_id' => 'marketing' // Key will be replaced with ID
                        ]
                    ]
                ],
            ],
        ];

        // Create missions and associate tasks
        $missionIds = [];

        // First pass: create all missions to generate IDs
        foreach ($missions as $missionData) {
            $taskData = $missionData['tasks'] ?? [];
            $lockerData = $missionData['lockers'] ?? [];
            
            unset($missionData['tasks']);
            unset($missionData['lockers']);
            
            $mission = Mission::firstOrCreate(['key' => $missionData['key']], $missionData);
            $missionIds[$missionData['key']] = $mission->id;
        }
        
        // Second pass: associate tasks with missions and create lockers
        foreach ($missions as $missionData) {
            $mission = Mission::where('key', $missionData['key'])->first();
            
            // Associate tasks
            if (isset($missionData['tasks']) && !empty($missionData['tasks'])) {
                $taskPivotData = [];
                
                foreach ($missionData['tasks'] as $taskKey => $sortOrder) {
                    $task = Task::where('key', $taskKey)->first();
                    
                    if ($task) {
                        $taskPivotData[$task->id] = ['sort_order' => $sortOrder];
                    }
                }
                
                $mission->tasks()->sync($taskPivotData);
            }
            
            // Create lockers
            if (isset($missionData['lockers']) && !empty($missionData['lockers'])) {
                foreach ($missionData['lockers'] as $lockerData) {
                    if ($lockerData['condition_type'] === 'mission_completion' && 
                        isset($lockerData['condition_payload']['mission_id']) && 
                        isset($missionIds[$lockerData['condition_payload']['mission_id']])) {
                        
                        // Replace mission key with ID
                        $lockerData['condition_payload']['mission_id'] = $missionIds[$lockerData['condition_payload']['mission_id']];
                        
                        // Create locker
                        Locker::firstOrCreate([
                            'mission_id' => $mission->id,
                            'condition_type' => $lockerData['condition_type'],
                            'condition_payload' => $lockerData['condition_payload'],
                        ]);
                    }
                }
            }
        }

        $this->command->info('Missions seeded successfully!');
    }
}