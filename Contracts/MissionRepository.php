<?php

namespace Salla\Gamification\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Salla\Gamification\Models\Mission;

interface MissionRepository
{
    /**
     * Get all missions.
     *
     * @param array $filters
     * @return Collection
     */
    public function all(array $filters = []): Collection;

    /**
     * Get paginated missions.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Find a mission by ID.
     *
     * @param int|string $id
     * @return Mission|null
     */
    public function find($id): ?Mission;

    /**
     * Find a mission by key.
     *
     * @param string $key
     * @return Mission|null
     */
    public function findByKey(string $key): ?Mission;

    /**
     * Create a new mission.
     *
     * @param array $attributes
     * @return Mission
     */
    public function create(array $attributes): Mission;

    /**
     * Update a mission.
     *
     * @param Mission|int $mission
     * @param array $attributes
     * @return Mission
     */
    public function update($mission, array $attributes): Mission;

    /**
     * Delete a mission.
     *
     * @param Mission|int $mission
     * @return bool
     */
    public function delete($mission): bool;

    /**
     * Get available missions for a store.
     *
     * @param int $storeId
     * @return Collection
     */
    public function getAvailableForStore(int $storeId): Collection;

    /**
     * Get missions with tasks for a store.
     *
     * @param int $storeId
     * @return Collection
     */
    public function getMissionsWithTasks(int $storeId): Collection;

    /**
     * Ignore a mission for a store.
     *
     * @param int $missionId
     * @param int $storeId
     * @return bool
     */
    public function ignoreMission(int $missionId, int $storeId): bool;

    /**
     * Check if mission is unlocked for a store.
     *
     * @param int $missionId
     * @param int $storeId
     * @return bool
     */
    public function isMissionUnlocked(int $missionId, int $storeId): bool;
}