<?php

namespace Modules\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;

class GamificationSeeder extends Seeder
{
    /**
     * Seed the gamification tables with demo data.
     *
     * @return void
     */
    public function run()
    {
        // Seed badges first
        $this->call(BadgeSeeder::class);
        
        // Seed tasks
        $this->call(TaskSeeder::class);
        
        // Seed missions and associate tasks
        $this->call(MissionSeeder::class);
        
        // Seed rewards for missions
        $this->call(RewardSeeder::class);
    }
}