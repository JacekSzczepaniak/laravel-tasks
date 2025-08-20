<?php

namespace App\Domain\Tasks\Repositories;

use App\Domain\Tasks\Entities\TaskEntity;

interface TaskRepository {
    public function findForView(int $taskId, int $requesterId): ?TaskEntity;
    public function save(TaskEntity $task): TaskEntity;
    public function delete(int $taskId, int $requesterId): void;

    /** @return TaskEntity[] */
    public function listForUser(int $userId, array $filters = []): array;
    public function assignObserver(int $taskId, int $observerId, int $requesterId): void;
    public function removeObserver(int $taskId, int $observerId, int $requesterId): void;
    public function paginateForUser(int $userId, array $filters = [], int $perPage = 15, int $page = 1);

}
