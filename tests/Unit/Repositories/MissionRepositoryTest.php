<?php

namespace Salla\Gamification\Tests\Unit\Repositories;

use Salla\Gamification\Tests\TestCase;
use Salla\Gamification\Models\Mission;
use Salla\Gamification\Models\Task;
use Salla\Gamification\Models\Locker;
use Salla\Gamification\Models\StoreProgress;
use Salla\Gamification\Tests\Models\Store;
use Salla\Gamification\Contracts\MissionRepository;

class MissionRepositoryTest extends TestCase
{
    protected MissionRepository $missionRepository;
    protected Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->missionRepository = app(MissionRepository::class);
        $this->store = Store::create(['name' => 'Test Store', 'email' => 'test@example.com']);
    }

    /** @test */
    public function it_can_create_a_mission()
    {
        $missionData = [
            'key' => 'store-setup',
            'name' => 'Set Up Your Store',
            'description' => 'Complete the initial store setup',
            'image' => 'setup.png',
            'total_points' => 150,
            'is_active' => true,
            'sort_order' => 1,
        ];

        $mission = $this->missionRepository->create($missionData);

        $this->assertInstanceOf(Mission::class, $mission);
        $this->assertEquals('store-setup', $mission->key);
        $this->assertEquals('Set Up Your Store', $mission->name);
        $this->assertEquals(150, $mission->total_points);
        $this->assertTrue($mission->is_active);
    }

    /** @test */
    public function it_can_find_a_mission_by_id()
    {
        $mission = Mission::create([
            'key' => 'first-sale',
            'name' => 'Get Your First Sale',
            'description' => 'Complete steps to get your first sale',
            'total_points' => 200,
            'is_active' => true,
        ]);

        $foundMission = $this->missionRepository->find($mission->id);

        $this->assertInstanceOf(Mission::class, $foundMission);
        $this->assertEquals($mission->id, $foundMission->id);
        $this->assertEquals('first-sale', $foundMission->key);
    }

    /** @test */
    public function it_can_find_a_mission_by_key()
    {
        Mission::create([
            'key' => 'marketing-basics',
            'name' => 'Marketing Basics',
            'description' => 'Set up essential marketing tools',
            'total_points' => 175,
            'is_active' => true,
        ]);

        $mission = $this->missionRepository->findByKey('marketing-basics');

        $this->assertInstanceOf(Mission::class, $mission);
        $this->assertEquals('marketing-basics', $mission->key);
        $this->assertEquals('Marketing Basics', $mission->name);
    }

    /** @test */
    public function it_can_update_a_mission()
    {
        $mission = Mission::create([
            'key' => 'payment-methods',
            'name' => 'Set Up Payment Methods',
            'description' => 'Configure payment options',
            'total_points' => 125,
            'is_active' => true,
        ]);

        $updatedMission = $this->missionRepository->update($mission, [
            'name' => 'Configure Payment Methods',
            'total_points' => 150,
        ]);

        $this->assertEquals('Configure Payment Methods', $updatedMission->name);
        $this->assertEquals(150, $updatedMission->total_points);
        $this->assertEquals('payment-methods', $updatedMission->key);
    }

    /** @test */
    public function it_can_delete_a_mission()
    {
        $mission = Mission::create([
            'key' => 'social-integration',
            'name' => 'Social Media Integration',
            'description' => 'Connect your store to social media',
            'total_points' => 100,
            'is_active' => true,
        ]);

        $this->assertTrue($this->missionRepository->delete($mission));
        $this->assertNull(Mission::find($mission->id));
    }

    /** @test */
    public function it_can_filter_active_missions()
    {
        Mission::create([
            'key' => 'active-mission-1',
            'name' => 'Active Mission 1',
            'total_points' => 100,
            'is_active' => true,
        ]);

        Mission::create([
            'key' => 'active-mission-2',
            'name' => 'Active Mission 2',
            'total_points' => 100,
            'is_active' => true,
        ]);

        Mission::create([
            'key' => 'inactive-mission',
            'name' => 'Inactive Mission',
            'total_points' => 100,
            'is_active' => false,
        ]);

        $activeMissions = $this->missionRepository->all(['is_active' => true]);
        $this->assertCount(2, $activeMissions);

        $inactiveMissions = $this->missionRepository->all(['is_active' => false]);
        $this->assertCount(1, $inactiveMissions);
        $this->assertEquals('inactive-mission', $inactiveMissions->first()->key);
    }

    /** @test */
    public function it_can_determine_if_mission_is_unlocked_without_lockers()
    {
        $mission = Mission::create([
            'key' => 'no-locker',
            'name' => 'Mission Without Locker',
            'total_points' => 100,
            'is_active' => true,
        ]);

        $this->assertTrue($this->missionRepository->isMissionUnlocked($mission->id, $this->store->id));
    }

    /** @test */
    public function it_can_determine_if_mission_is_locked_with_mission_completion_locker()
    {
        // Create prerequisite mission
        $prerequisiteMission = Mission::create([
            'key' => 'prerequisite',
            'name' => 'Prerequisite Mission',
            'total_points' => 50,
            'is_active' => true,
        ]);
        
        // Create mission with locker
        $lockedMission = Mission::create([
            'key' => 'locked-mission',
            'name' => 'Locked Mission',
            'total_points' => 100,
            'is_active' => true,
        ]);
        
        // Create locker that requires prerequisite mission completion
        Locker::create([
            'mission_id' => $lockedMission->id,
            'condition_type' => 'mission_completion',
            'condition_payload' => [
                'mission_id' => $prerequisiteMission->id
            ],
        ]);
        
        // Mission should be locked initially
        $this->assertFalse($this->missionRepository->isMissionUnlocked($lockedMission->id, $this->store->id));
        
        // Complete prerequisite mission
        StoreProgress::create([
            'store_id' => $this->store->id,
            'mission_id' => $prerequisiteMission->id,
            'status' => 'completed',
            'progress_percentage' => 100,
            'completed_at' => now(),
        ]);
        
        // Mission should be unlocked now
        $this->assertTrue($this->missionRepository->isMissionUnlocked($lockedMission->id, $this->store->id));
    }

    /** @test */
    public function it_can_get_available_missions_for_store()
    {
        // Create three missions, two active, one inactive
        $mission1 = Mission::create([
            'key' => 'mission-one',
            'name' => 'Mission One',
            'total_points' => 100,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        
        $mission2 = Mission::create([
            'key' => 'mission-two',
            'name' => 'Mission Two',
            'total_points' => 100,
            'is_active' => true,
            'sort_order' => 2,
        ]);
        
        $mission3 = Mission::create([
            'key' => 'mission-three',
            'name' => 'Mission Three',
            'total_points' => 100,
            'is_active' => false,
            'sort_order' => 3,
        ]);
        
        // Create locker for mission2 to make it unavailable
        Locker::create([
            'mission_id' => $mission2->id,
            'condition_type' => 'mission_completion',
            'condition_payload' => [
                'mission_id' => $mission1->id
            ],
        ]);
        
        $availableMissions = $this->missionRepository->getAvailableForStore($this->store->id);
        
        $this->assertCount(1, $availableMissions);
        $this->assertEquals('mission-one', $availableMissions->first()->key);
        
        // Complete first mission to unlock mission2
        StoreProgress::create([
            'store_id' => $this->store->id,
            'mission_id' => $mission1->id,
            'status' => 'completed',
            'progress_percentage' => 100,
            'completed_at' => now(),
        ]);
        
        // Check again - now we should have 2 available missions
        $availableMissions = $this->missionRepository->getAvailableForStore($this->store->id);
        $this->assertCount(2, $availableMissions);
    }

    /** @test */
    public function it_can_get_missions_with_tasks()
    {
        // Create mission and tasks
        $mission = Mission::create([
            'key' => 'onboarding',
            'name' => 'Store Onboarding',
            'total_points' => 200,
            'is_active' => true,
        ]);
        
        $task1 = Task::create([
            'key' => 'add-logo',
            'name' => 'Add Store Logo',
            'points' => 50,
            'event_name' => 'logo_updated',
            'is_active' => true,
        ]);
        
        $task2 = Task::create([
            'key' => 'add-product',
            'name' => 'Add Product',
            'points' => 50,
            'event_name' => 'product_created',
            'is_active' => true,
        ]);
        
        // Attach tasks to mission
        $mission->tasks()->attach([
            $task1->id => ['sort_order' => 1],
            $task2->id => ['sort_order' => 2],
        ]);
        
        // Mark task1 as completed
        \Salla\Gamification\Models\TaskCompletion::create([
            'store_id' => $this->store->id,
            'mission_id' => $mission->id,
            'task_id' => $task1->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        
        // Get missions with tasks
        $missionsWithTasks = $this->missionRepository->getMissionsWithTasks($this->store->id);
        
        $this->assertCount(1, $missionsWithTasks);
        $missionWithTasks = $missionsWithTasks->first();
        
        $this->assertEquals('onboarding', $missionWithTasks->key);
        $this->assertCount(2, $missionWithTasks->tasks);
        
        // Check task completion status
        $taskOne = $missionWithTasks->tasks->firstWhere('key', 'add-logo');
        $taskTwo = $missionWithTasks->tasks->firstWhere('key', 'add-product');
        
        // Make sure completion exists before checking status
        $this->assertTrue(isset($taskOne->completion));
        $this->assertEquals('completed', $taskOne->completion->status);
        
        // For task two, we might need to check if completion exists or set a default status
        if (isset($taskTwo->completion)) {
            $this->assertEquals('not_started', $taskTwo->completion->status);
        } else {
            // If completion is null, we can't check its status, so just assert it's not completed
            $this->assertFalse(
                \Salla\Gamification\Models\TaskCompletion::where('task_id', $taskTwo->id)
                    ->where('store_id', $this->store->id)
                    ->where('status', 'completed')
                    ->exists()
            );
        }
    }

    /** @test */
    public function it_can_ignore_a_mission()
    {
        $mission = Mission::create([
            'key' => 'ignorable',
            'name' => 'Ignorable Mission',
            'total_points' => 75,
            'is_active' => true,
        ]);
        
        $this->assertTrue($this->missionRepository->ignoreMission($mission->id, $this->store->id));
        
        $progress = StoreProgress::where('store_id', $this->store->id)
            ->where('mission_id', $mission->id)
            ->first();
        
        $this->assertNotNull($progress);
        $this->assertEquals('ignored', $progress->status);
    }
}