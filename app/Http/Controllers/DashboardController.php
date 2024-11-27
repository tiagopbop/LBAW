<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuthenticatedUser;

class DashboardController extends Controller
{

    public function index()
    {
        $users = AuthenticatedUser::all();

        return view('dashboard', compact('users'));
    }
}
