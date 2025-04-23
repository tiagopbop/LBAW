<?php

namespace App\Policies;

use Illuminate\Support\Facades\Auth;
use App\Models\AuthenticatedUser;
use App\Models\Project;

class ProjectPolicy
{
    public function __construct() {

    }

    /**
     * Determine if a user can create a project.
     */
    public function create(AuthenticatedUser $user): bool
    {
        return Auth::check();
    }

    /**
     * Determine if the given project can be viewed by the user.
     */
    public function view(AuthenticatedUser $user, Project $project): bool
    {
        return $project->members->contains($user);
    }

    /**
     * Determine if the given project can be updated by the user.
     */
    public function update(AuthenticatedUser $user, Project $project): bool
    {
        $member = $project->members->where('id', $user->id)->first();
        return $member && in_array($member->pivot->role, ['Project manager', 'Project owner']);
    }

    /**
     * Determine if the given project can be deleted by the user.
     */
    public function delete(AuthenticatedUser $user, Project $project): bool
    {
        $member = $project->members->where('id', $user->id)->first();
        return $member && $member->pivot->role === 'Project owner';
    }

    /**
     * Determine if the given project can be archived by the user.
     */
    public function archive(AuthenticatedUser $user, Project $project): bool
    {
        $member = $project->members->where('id', $user->id)->first();
        return $member && $member->pivot->role === 'Project owner';
    }
}