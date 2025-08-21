<?php

namespace app\Infrastructure\Tasks\Repositories;

use App\Domain\Tasks\Entities\TaskEntity;

interface TaskRepository
{
    public function findForView(int $taskId, int $requesterId): ?TaskEntity;  // owner lub observer
    public function save(TaskEntity $task): TaskEntity;
    public function delete(int $taskId, int $requesterId): void;
    /**
     * @param array{
     *     status?: string,
     *     due_from?: string|\DateTimeInterface,
     *     due_to?: string|\DateTimeInterface,
     *     sort?: string|array<int,string>,
     *     q?: string,
     *     scope?: 'all'|'owned'|'observed'
     * } $filters
     * @return TaskEntity[]
     */
    public function listForUser(int $userId, array $filters = []): array;     // właściciel + obserwowane (read-only)
    public function assignObserver(int $taskId, int $observerId, int $requesterId): void;
    public function removeObserver(int $taskId, int $observerId, int $requesterId): void;
}
