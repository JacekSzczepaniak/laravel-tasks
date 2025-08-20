<?php

namespace App\Application\Tasks;

use App\Domain\Tasks\Repositories\TaskRepository;

final class AssignObserver
{
    public function __construct(private TaskRepository $repo)
    {
    }
    public function __invoke(int $taskId, int $observerId, int $requesterId): void
    {
        $this->repo->assignObserver($taskId, $observerId, $requesterId);
    }
}
