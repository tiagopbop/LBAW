<?php

namespace App\Http\Controllers;

use App\Models\AuthenticatedUserNotif;
use App\Models\InviteNotif;
use App\Models\AuthenticatedUser;
use App\Models\ProjectMember;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
            $notifications = AuthenticatedUser::find(auth()->id())
            ->notifs()
            ->with(['inviteNotifs.project'])  ->get();

        return view('pages.notifications', compact('notifications'));
    }

}
