<?php

namespace Salla\Gamification\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Salla\Gamification\Models\Task;

interface TaskRepository
{
    /**
     * Get all tasks.
     *
     * @param array $filters
     * @return Collection
     */
    public function all(array $filters = []): Collection;

    /**
     * Get paginated tasks.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Find a task by ID.
     *
     * @param int|string $id
     * @return Task|null
     */
    public function find($id): ?Task;

    /**
     * Find a task by key.
     *
     * @param string $key
     * @return Task|null
     */
    public function findByKey(string $key): ?Task;

    /**
     * Create a new task.
     *
     * @param array $attributes
     * @return Task
     */
    public function create(array $attributes): Task;

    /**
     * Update a task.
     *
     * @param Task|int $task
     * @param array $attributes
     * @return Task
     */
    public function update($task, array $attributes): Task;

    /**
     * Delete a task.
     *
     * @param Task|int $task
     * @return bool
     */
    public function delete($task): bool;

    /**
     * Find tasks by event name.
     *
     * @param string $eventName
     * @return Collection
     */
    public function findByEventName(string $eventName): Collection;
}