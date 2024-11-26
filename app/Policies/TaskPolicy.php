<?php

namespace App\Policies;

use App\Models\AuthenticatedUser;
use App\Models\Task;

class TaskPolicy
{
    public function view(AuthenticatedUser $user, Task $task): bool
    {
        return $task->users->contains($user);
    }

    public function update(AuthenticatedUser $user, Task $task): bool
    {
        return $task->users->contains($user);
    }

    public function delete(AuthenticatedUser $user, Task $task): bool
    {
        return $task->users->contains($user) && $user->pivot->role === 'Project manager';
    }

    public function assign(AuthenticatedUser $user, Task $task): bool
    {
        return $task->project->members->contains($user) && $user->pivot->role === 'Project manager';
    }
}