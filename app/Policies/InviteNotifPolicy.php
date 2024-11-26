<?php

namespace App\Policies;

use App\Models\AuthenticatedUser;
use App\Models\InviteNotif;
use Illuminate\Support\Facades\Auth;

class InviteNotifPolicy
{
    public function view(AuthenticatedUser $user, InviteNotif $inviteNotif): bool
    {
        return $inviteNotif->notif->users->contains($user);
    }

    public function accept(AuthenticatedUser $user, InviteNotif $inviteNotif): bool
    {
        return $inviteNotif->notif->users->contains($user);
    }

    public function decline(AuthenticatedUser $user, InviteNotif $inviteNotif): bool
    {
        return $inviteNotif->notif->users->contains($user);
    }
}