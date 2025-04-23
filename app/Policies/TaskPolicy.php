<?php

namespace App\Policies;

use App\Models\AuthenticatedUser;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class TaskPolicy
{
    public function before($user)
    {
        if (!Auth::check()) {
            return false;
        }
        return null;
    }
    
    public function view(AuthenticatedUser $user, Task $task): bool
    {
        $project = $task->project;
        return $project->members->contains($user);
    }

    public function update(AuthenticatedUser $user, Task $task): bool
    {
        $project = $task->project;
        $owner = $project->members()->wherePivot('role', 'Project owner')->first();
        $manager = $project->members()->wherePivot('role', 'Project manager')->first();

        return ($owner && $owner->id === $user->id) || 
               ($manager && $manager->id === $user->id);
    }

    public function delete(AuthenticatedUser $user, Task $task): bool
    {
        $project = $task->project;
        $owner = $project->members()->wherePivot('role', 'Project owner')->first();
        $managers = $project->members()->wherePivot('role', 'Project manager')->pluck('id');

        return ($owner && $owner->id === $user->id) || 
               $managers->contains($user->id);
    }

    public function create(AuthenticatedUser $user, Project $project): bool
    {
        $owner = $project->members()->wherePivot('role', 'Project owner')->first();
        $managers = $project->members()->wherePivot('role', 'Project manager')->pluck('id');

        return ($owner && $owner->id === $user->id) || 
               $managers->contains($user->id);
    }
}
