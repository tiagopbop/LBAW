<?php

namespace App\Policies;

use App\Models\AuthenticatedUser;
use App\Models\AuthenticatedUserNotif;
use App\Models\Notif;
use App\Models\Task;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class NotifPolicy
{
    public function __construct()
    {
        //
    }

    
    public function view(AuthenticatedUser $user, Notif $notif): bool
    {
        return $notif->user()->id === $user->id;
    }

    public function accept(AuthenticatedUser $user, Notif $notif): bool
    {
        return $notif->user()->id === $user->id;
    }

    public function decline(AuthenticatedUser $user, Notif $notif): bool
    {
        return $notif->user()->id === $user->id;
    }
}