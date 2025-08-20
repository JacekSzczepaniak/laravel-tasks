<?php

namespace App\Application\Tasks;

use App\Domain\Tasks\Entities\TaskEntity;
use App\Domain\Tasks\Enum\TaskStatus;
use App\Domain\Tasks\Repositories\TaskRepository;
use Carbon\CarbonImmutable;
use DateTimeInterface;

final class CreateTask
{
    public function __construct(private TaskRepository $repo)
    {
    }

    public function __invoke(
        int $ownerId,
        string $title,
        ?string $description,
        ?TaskStatus $status,
        ?string $dueAt
    ): TaskEntity {
        $e = new TaskEntity(
            id: null,
            ownerId: $ownerId,
            title: $title,
            description: $description,
            status: $status ?? TaskStatus::Todo,
            dueAt: $dueAt ? CarbonImmutable::parse($dueAt) : null
        );
        return $this->repo->save($e);
    }

    /** Adapter pod aktualne wywołanie w Livewire */
    public function handle(int $ownerId, array $data): TaskEntity
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
            $ownerId,
            $data['title'],
            $data['description'] ?? null,
            $status,
            $dueAt
        );
    }
}
