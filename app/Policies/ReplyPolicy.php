<?php

namespace App\Policies;

use App\Models\AuthenticatedUser;
use App\Models\Reply;
use Illuminate\Support\Facades\Auth;

class ReplyPolicy
{
    public function view(AuthenticatedUser $user, Reply $reply): bool
    {
        return $reply->post->project->members->contains($user);
    }

    public function create(AuthenticatedUser $user, Reply $reply): bool
    {
        return $reply->post->project->members->contains($user);
    }

    public function update(AuthenticatedUser $user, Reply $reply): bool
    {
        return $user->id === $reply->id;
    }

    public function delete(AuthenticatedUser $user, Reply $reply): bool
    {
        return $user->id === $reply->id || $reply->post->project->members->contains($user) && $user->pivot->role === 'Project manager';
    }
}