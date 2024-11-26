<?php

namespace App\Policies;

use App\Models\AuthenticatedUser;
use App\Models\TaskComment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class AuthenticatedUserPolicy
{
    public function show(AuthenticatedUser $user): bool
    {
        return Auth::user()->id === $user->id;
    }

    public function edit(AuthenticatedUser $user): bool
    {
        return Auth::user()->id === $user->id;
    }

    public function updateTaskComment(AuthenticatedUser $user, TaskComment $taskComment): bool
    {
        return $user->id === $taskComment->user_id;
    }

    public function viewProject(AuthenticatedUser $user, Project $project): bool
    {
        return $project->members->contains($user);
    }

    public function updateTask(AuthenticatedUser $user, Task $task): bool
    {
        return $task->users->contains($user);
    }

    public function manageProjectInvitations(AuthenticatedUser $user, Project $project): bool
    {
        return $project->members->contains($user) && $user->pivot->role === 'Project manager';
    }

    public function markAsFavorite(AuthenticatedUser $user, Project $project): bool
    {
        return $project->members->contains($user);
    }

    public function viewNotifications(AuthenticatedUser $user): bool
    {
        return Auth::user()->id === $user->id;
    }
}