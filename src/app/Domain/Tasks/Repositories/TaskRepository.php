<?php

namespace App\Domain\Tasks\Repositories;

use App\Domain\Tasks\Entities\TaskEntity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


interface TaskRepository
{
    public function findForView(int $taskId, int $requesterId): ?TaskEntity;
    public function save(TaskEntity $task): TaskEntity;
    public function delete(int $taskId, int $requesterId): void;

    /**
     * @param array<string, mixed> $filters
     * @return array<int, TaskEntity>
     */

    public function listForUser(int $userId, array $filters = []): array;
    public function assignObserver(int $taskId, int $observerId, int $requesterId): void;
    public function removeObserver(int $taskId, int $observerId, int $requesterId): void;

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, TaskEntity>
     */
    public function paginateForUser(int $userId, array $filters = [], int $perPage = 15, int $page = 1): LengthAwarePaginator;

    /**
     * @return array<int, int>
     */
    public function getObserverIds(int $taskId, int $requesterId): array;
}
