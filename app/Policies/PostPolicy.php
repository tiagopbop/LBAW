<?php

namespace App\Policies;

use App\Models\AuthenticatedUser;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class PostPolicy
{
    public function __construct()
    {
        //
    }
    
    public function view(AuthenticatedUser $user, Post $post): bool
    {
        return $post->project->members->contains($user);
    }

    public function create(AuthenticatedUser $user, Post $post): bool
    {
        return $post->project->members->contains($user);
    }

    public function update(AuthenticatedUser $user, Post $post): bool
    {
        return $user->id === $post->id;
    }

    public function delete(AuthenticatedUser $user, Post $post): bool
    {
        return $user->id === $post->id || $post->project->members->contains($user) && $user->pivot->role === 'Project manager';
    }
}