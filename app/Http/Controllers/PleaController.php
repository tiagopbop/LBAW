<?php
namespace App\Http\Controllers;
use App\Models\Plea;

use Illuminate\Http\Request;

class PleaController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'plea' => 'required|string|max:500',
        ]);

        Plea::create([
            'authenticated_user_id' => $request->user()->id,
            'plea' => $request->plea,
        ]);

        return redirect()->back()->with('success', 'Your plea has been submitted. We will review it shortly.');
    }
}