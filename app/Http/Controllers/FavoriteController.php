<?php

namespace App\Http\Controllers;

use App\Models\Favorited;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function toggleFavorite(Project $project): JsonResponse
    {
        $userId = Auth::id();
        
        $favorite = Favorited::where('id', $userId)
            ->where('project_id', $project->project_id)
            ->first();

        if ($favorite) {
            $favorite->checks = !$favorite->checks;
            $favorite->save();
        } else {
            Favorited::create([
                'id' => $userId,
                'project_id' => $project->project_id,
                'checks' => true
            ]);
            return response()->json([
                'status' => 'favorited',
                'project_id' => $project->project_id
            ]);
        }

        return response()->json([
            'status' => $favorite->checks ? 'favorited' : 'unfavorited',
            'project_id' => $project->project_id
        ]);
    }
}