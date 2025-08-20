<?php

namespace App\Domain\Tasks\Entities;

use App\Domain\Tasks\Enum\TaskStatus;
use Carbon\CarbonImmutable;

final class TaskEntity {
    public function __construct(
        public readonly ?int $id,
        public readonly int $ownerId,
        public string $title,
        public ?string $description,
        public TaskStatus $status,
        public ?CarbonImmutable $dueAt,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public function complete(): void {
        if ($this->status !== TaskStatus::Done) {
            $this->status = TaskStatus::Done;
        }
    }

    public function reschedule(CarbonImmutable $newDue): void {
        $this->dueAt = $newDue;
    }
}
