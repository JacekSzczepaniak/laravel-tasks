<?php

namespace App\Application\Tasks;

use App\Domain\Tasks\Entities\TaskEntity;
use App\Domain\Tasks\Repositories\TaskRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ListTasksForUser
{
    public function __construct(private TaskRepository $repo)
    {
    }
    /**
     * @param array<string, mixed> $filters
     * @return array<int, TaskEntity>
     */
    public function __invoke(int $userId, array $filters = []): array
    {
        return $this->repo->listForUser($userId, $filters);
    }

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, TaskEntity>
    */

    public function paginate(int $userId, array $filters = [], int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->repo->paginateForUser($userId, $filters, $perPage, $page);
    }

    public function findById(int $taskId, int $requesterId): TaskEntity
    {
        return $this->repo->findForView($taskId, $requesterId);
    }
}
