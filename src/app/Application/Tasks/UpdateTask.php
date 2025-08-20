<?php

namespace App\Application\Tasks;

use App\Domain\Tasks\Entities\TaskEntity;
use App\Domain\Tasks\Enum\TaskStatus;
use App\Domain\Tasks\Repositories\TaskRepository;
use App\Infrastructure\Tasks\Models\Task as ETask;
use Carbon\CarbonImmutable;
use DateTimeInterface;

final class UpdateTask
{
    public function __construct(private TaskRepository $repo) {}

    /**
     * Adapter pod aktualne wywołanie w Livewire:
     * $update->handle($eloquentTask, $payload)
     */
    public function handle(ETask $task, array $data): TaskEntity
    {
        $status = isset($data['status'])
            ? TaskStatus::tryFrom($data['status'])
            : null;

        $dueAt = null;
        if (array_key_exists('due_at', $data) && $data['due_at'] !== null) {
            $dueAt = $data['due_at'] instanceof DateTimeInterface
                ? $data['due_at']->format(DATE_ATOM)
                : (string) $data['due_at'];
        }

        return $this->__invoke(
            id: (int) $task->id,
            ownerId: (int) ($task->owner_id ?? $task->user_id),
            title: $data['title'],
            description: $data['description'] ?? null,
            status: $status,
            dueAt: $dueAt
        );
    }

    public function __invoke(
        int $id,
        int $ownerId,
        string $title,
        ?string $description,
        ?TaskStatus $status,
        ?string $dueAt
    ): TaskEntity {
        $entity = new TaskEntity(
            id: $id,
            ownerId: $ownerId,
            title: $title,
            description: $description,
            status: $status ?? TaskStatus::Todo,
            dueAt: $dueAt ? CarbonImmutable::parse($dueAt) : null,
        );

        return $this->repo->save($entity);
    }
}
