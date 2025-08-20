<?php

namespace App\Policies;

use App\Infrastructure\Tasks\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        // właściciel lub obserwator ma dostęp do odczytu
        return $task->user_id === $user->id
            || $task->observers()->whereKey($user->id)->exists();

    }

    public function update(User $user, Task $task): bool
    {
        // tylko właściciel może modyfikować
        return $task->user_id === $user->id;

    }

    public function delete(User $user, Task $task): bool
    {
        // tylko właściciel może usuwać
        return $task->user_id === $user->id;
    }

    public function assignObservers(User $user, Task $task): bool
    {
        return $task->user_id === $user->id;
    }

}
