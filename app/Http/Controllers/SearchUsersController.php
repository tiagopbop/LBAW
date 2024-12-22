<?php

namespace App\Http\Controllers;

use App\Models\AuthenticatedUser;
use Illuminate\Http\Request;

class SearchUsersController extends Controller
{
    public function index()
{
    return view('pages.searchusers');  // Ensure it's 'searchusers', not 'searchresults'
}


    public function search(Request $request)
    {
        $query = $request->input('query');
        if (empty($query)) {
            // If the query is empty, return all users without filtering
            $users = AuthenticatedUser::all();
        } else {
            // If there is a query, filter by username or ID
            $users = AuthenticatedUser::where('username', 'like', '%' . $query . '%')
                                      ->get();
        }
        return response()->json($users);
    }
}

