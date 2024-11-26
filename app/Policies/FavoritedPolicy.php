<?php

namespace App\Policies;

use App\Models\AuthenticatedUser;
use App\Models\Favorited;
use Illuminate\Support\Facades\Auth;

class FavoritedPolicy
{
    public function view(AuthenticatedUser $user, Favorited $favorited): bool
    {
        return $favorited->user->id === $user->id;
    }

    public function create(AuthenticatedUser $user, Favorited $favorited): bool
    {
        return $favorited->user->id === $user->id;
    }

    public function delete(AuthenticatedUser $user, Favorited $favorited): bool
    {
        return $favorited->user->id === $user->id;
    }
}