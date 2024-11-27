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
        $project = $task->project;

        return $project->members()->wherePivot('role', 'Project owner')->first()->id === $user->id
            || $project->members()->wherePivot('role', 'Manager')->pluck('id')->contains($user->id);
    }

    public function delete(AuthenticatedUser $user, Task $task): bool
    {
        $project = $task->project;

        return $project->members()->wherePivot('role', 'Project owner')->first()->id === $user->id
            || $project->members()->wherePivot('role', 'Manager')->pluck('id')->contains($user->id);
    }

    public function create(AuthenticatedUser $user, ?Project $project): bool
    {
        $project = $task->project;

        return $project 
            ? $project->members()->wherePivot('role', 'Project owner')->first()->id === $user->id
            || $project->members()->wherePivot('role', 'Manager')->pluck('id')->contains($user->id)
            : false;
    }
}
