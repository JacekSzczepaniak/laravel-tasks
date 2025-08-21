<?php

namespace App\Domain\Tasks\Enum;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Done = 'done';

    /**
     * @return list<string>
     */
    public function nextAllowed(): array
    {
        $map = [
            self::Todo->value => ['in_progress', 'done'],
            self::InProgress->value => ['done', 'todo'],
            self::Done->value => [],
        ];

        return $map[$this->value];
    }

    public function canTransition(self $to): bool
    {
        return in_array($to->value, $this->nextAllowed(), true);
    }
}
