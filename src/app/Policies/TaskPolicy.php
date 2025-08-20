<?php

namespace App\Policies;

use App\Infrastructure\Tasks\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function view(User $u, Task $t): bool
    {
        // właściciel lub obserwator może zobaczyć
        return $t->user_id === $u->id
            || $t->observers()->whereKey($u->id)->exists();
    }

    public function update(User $u, Task $t): bool
    {
        // aktualizować może tylko właściciel
        return $t->user_id === $u->id;
    }

    public function delete(User $u, Task $t): bool
    {
        return $t->user_id === $u->id;
    }

    public function attachObserver(User $u, Task $t): bool
    {
        // tylko właściciel może dodawać obserwatorów
        return $t->user_id === $u->id;
    }

    public function detachObserver(User $u, Task $t): bool
    {
        // tylko właściciel może usuwać obserwatorów
        return $t->user_id === $u->id;
    }
}
