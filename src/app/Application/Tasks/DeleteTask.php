<?php

namespace App\Application\Tasks;

use App\Infrastructure\Tasks\Models\Task;

final class DeleteTask
{
    public function __invoke(int $taskId, int $actorId): void
    {
        $task = Task::findOrFail($taskId);
        $this->handle($task);
    }

    public function handle(Task $task): void
    {
        $task->delete();
    }
}
