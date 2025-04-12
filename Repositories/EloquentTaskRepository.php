<?php

namespace Modules\Gamification\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Gamification\Contracts\TaskRepository;
use Modules\Gamification\Models\Task;

class EloquentTaskRepository implements TaskRepository
{
    /**
     * Get all tasks.
     *
     * @param array $filters
     * @return Collection
     */
    public function all(array $filters = []): Collection
    {
        $query = Task::query();
        
        // Apply filters
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        
        if (isset($filters['event_name'])) {
            $query->where('event_name', $filters['event_name']);
        }
        
        if (isset($filters['order_by'])) {
            $direction = $filters['order_direction'] ?? 'asc';
            $query->orderBy($filters['order_by'], $direction);
        }
        
        return $query->get();
    }

    /**
     * Get paginated tasks.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Task::query();
        
        // Apply filters
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        
        if (isset($filters['event_name'])) {
            $query->where('event_name', $filters['event_name']);
        }
        
        if (isset($filters['order_by'])) {
            $direction = $filters['order_direction'] ?? 'asc';
            $query->orderBy($filters['order_by'], $direction);
        }
        
        return $query->paginate($perPage);
    }

    /**
     * Find a task by ID.
     *
     * @param int|string $id
     * @return Task|null
     */
    public function find($id): ?Task
    {
        return Task::find($id);
    }

    /**
     * Find a task by key.
     *
     * @param string $key
     * @return Task|null
     */
    public function findByKey(string $key): ?Task
    {
        return Task::where('key', $key)->first();
    }

    /**
     * Create a new task.
     *
     * @param array $attributes
     * @return Task
     */
    public function create(array $attributes): Task
    {
        return Task::create($attributes);
    }

    /**
     * Update a task.
     *
     * @param Task|int $task
     * @param array $attributes
     * @return Task
     */
    public function update($task, array $attributes): Task
    {
        if (!$task instanceof Task) {
            $task = $this->find($task);
        }
        
        if (!$task) {
            throw new \InvalidArgumentException('Task not found');
        }
        
        $task->update($attributes);
        
        return $task->refresh();
    }

    /**
     * Delete a task.
     *
     * @param Task|int $task
     * @return bool
     */
    public function delete($task): bool
    {
        if (!$task instanceof Task) {
            $task = $this->find($task);
        }
        
        if (!$task) {
            throw new \InvalidArgumentException('Task not found');
        }
        
        return $task->delete();
    }

    /**
     * Find tasks by event name.
     *
     * @param string $eventName
     * @return Collection
     */
    public function findByEventName(string $eventName): Collection
    {
        return Task::where('event_name', $eventName)
                ->where('is_active', true)
                ->get();
    }
}