<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Favorited;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function toggleFavorite(Project $project): \Illuminate\Http\JsonResponse
    {
        $userId = auth()->id();

        // Find the favorite record for the specific project ID
        $favorite = Favorited::where('id', $userId)
            ->where('project_id', $project->project_id)
            ->first();

        if ($favorite) {
            // Toggle the `checks` column
            $favorite->checks = !$favorite->checks;
            $favorite->save();
        } else {
            return response()->json([
                'error' => 'Entry not found for this user and project.',
            ], 404);
        }

        return response()->json([
            'status' => $favorite->checks ? 'favorited' : 'unfavorited',
            'project_id' => $project->project_id,
        ]);
    }

}

