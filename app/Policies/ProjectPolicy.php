<?php

namespace App\Policies;

use App\Models\AuthenticatedUser;
use App\Models\Project;

class ProjectPolicy
{
    public function view(AuthenticatedUser $user, Project $project): bool
    {
        return $project->members->contains($user);
    }

    public function update(AuthenticatedUser $user, Project $project): bool
    {
        return $project->members->contains($user) && $user->pivot->role === 'Project manager';
    }

    public function delete(AuthenticatedUser $user, Project $project): bool
    {
        return $project->members->contains($user) && $user->pivot->role === 'Project owner';
    }

    public function archive(AuthenticatedUser $user, Project $project): bool
    {
        return $project->members->contains($user) && $user->pivot->role === 'Project owner';
    }

    public function manageMembers(AuthenticatedUser $user, Project $project): bool
    {
        return $project->members->contains($user) && $user->pivot->role === 'Project owner';
    }
}