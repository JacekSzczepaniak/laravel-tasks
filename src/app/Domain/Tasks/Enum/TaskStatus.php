<?php


namespace App\Domain\Tasks\Enum;

enum TaskStatus: string {
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Done = 'done';
    public static function canTransition(self $from, self $to): bool
    {
        $map = [
            self::Todo->value => [self::InProgress->value, self::Done->value],
            self::InProgress->value => [self::Done->value, self::Todo->value],
            self::Done->value => [],
        ];
        return in_array($to->value, $map[$from->value] ?? [], true);
    }
}
