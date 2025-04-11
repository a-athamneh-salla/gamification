<?php

namespace Salla\Gamification\Tests\Unit\Repositories;

use Salla\Gamification\Tests\TestCase;
use Salla\Gamification\Models\Task;
use Salla\Gamification\Contracts\TaskRepository;
use Salla\Gamification\Repositories\EloquentTaskRepository;

class TaskRepositoryTest extends TestCase
{
    protected TaskRepository $taskRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskRepository = app(TaskRepository::class);
    }

    /** @test */
    public function it_can_create_a_task()
    {
        $taskData = [
            'key' => 'add-first-product',
            'name' => 'Add Your First Product',
            'description' => 'Add your first product to your store',
            'points' => 100,
            'event_name' => 'product_created',
            'icon' => 'shopping-bag',
            'is_active' => true,
        ];

        $task = $this->taskRepository->create($taskData);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('add-first-product', $task->key);
        $this->assertEquals('Add Your First Product', $task->name);
        $this->assertEquals(100, $task->points);
        $this->assertEquals('product_created', $task->event_name);
        $this->assertTrue($task->is_active);
    }

    /** @test */
    public function it_can_find_a_task_by_id()
    {
        $task = Task::create([
            'key' => 'setup-payment',
            'name' => 'Set Up Payment Method',
            'description' => 'Configure your store payment methods',
            'points' => 50,
            'event_name' => 'payment_method_configured',
            'is_active' => true,
        ]);

        $foundTask = $this->taskRepository->find($task->id);

        $this->assertInstanceOf(Task::class, $foundTask);
        $this->assertEquals($task->id, $foundTask->id);
        $this->assertEquals('setup-payment', $foundTask->key);
    }

    /** @test */
    public function it_can_find_a_task_by_key()
    {
        Task::create([
            'key' => 'configure-shipping',
            'name' => 'Configure Shipping Options',
            'description' => 'Set up shipping methods for your store',
            'points' => 75,
            'event_name' => 'shipping_method_configured',
            'is_active' => true,
        ]);

        $task = $this->taskRepository->findByKey('configure-shipping');

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('configure-shipping', $task->key);
        $this->assertEquals('Configure Shipping Options', $task->name);
    }

    /** @test */
    public function it_can_update_a_task()
    {
        $task = Task::create([
            'key' => 'customize-theme',
            'name' => 'Customize Your Store Theme',
            'description' => 'Personalize your store appearance',
            'points' => 60,
            'event_name' => 'theme_customized',
            'is_active' => true,
        ]);

        $updatedTask = $this->taskRepository->update($task, [
            'name' => 'Update Your Store Theme',
            'points' => 80,
        ]);

        $this->assertEquals('Update Your Store Theme', $updatedTask->name);
        $this->assertEquals(80, $updatedTask->points);
        $this->assertEquals('customize-theme', $updatedTask->key);
        $this->assertEquals('theme_customized', $updatedTask->event_name);
    }

    /** @test */
    public function it_can_delete_a_task()
    {
        $task = Task::create([
            'key' => 'add-store-logo',
            'name' => 'Add Your Store Logo',
            'description' => 'Upload your store logo',
            'points' => 40,
            'event_name' => 'store_logo_updated',
            'is_active' => true,
        ]);

        $this->assertTrue($this->taskRepository->delete($task));
        $this->assertNull(Task::find($task->id));
    }

    /** @test */
    public function it_can_find_tasks_by_event_name()
    {
        // Create multiple tasks with the same event name
        Task::create([
            'key' => 'add-product-1',
            'name' => 'Add First Product',
            'points' => 50,
            'event_name' => 'product_created',
            'is_active' => true,
        ]);

        Task::create([
            'key' => 'add-product-2',
            'name' => 'Add Second Product',
            'points' => 30,
            'event_name' => 'product_created',
            'is_active' => true,
        ]);

        Task::create([
            'key' => 'inactive-product',
            'name' => 'Inactive Product Task',
            'points' => 20,
            'event_name' => 'product_created',
            'is_active' => false,
        ]);

        $tasks = $this->taskRepository->findByEventName('product_created');

        $this->assertCount(2, $tasks);
        $this->assertTrue($tasks->contains('key', 'add-product-1'));
        $this->assertTrue($tasks->contains('key', 'add-product-2'));
        $this->assertFalse($tasks->contains('key', 'inactive-product'));
    }

    /** @test */
    public function it_can_filter_tasks_by_active_status()
    {
        Task::create([
            'key' => 'active-task-1',
            'name' => 'Active Task 1',
            'points' => 10,
            'event_name' => 'test_event_1',
            'is_active' => true,
        ]);

        Task::create([
            'key' => 'active-task-2',
            'name' => 'Active Task 2',
            'points' => 20,
            'event_name' => 'test_event_2',
            'is_active' => true,
        ]);

        Task::create([
            'key' => 'inactive-task',
            'name' => 'Inactive Task',
            'points' => 30,
            'event_name' => 'test_event_3',
            'is_active' => false,
        ]);

        $activeTasks = $this->taskRepository->all(['is_active' => true]);
        $this->assertCount(2, $activeTasks);

        $inactiveTasks = $this->taskRepository->all(['is_active' => false]);
        $this->assertCount(1, $inactiveTasks);
        $this->assertEquals('inactive-task', $inactiveTasks->first()->key);
    }
}