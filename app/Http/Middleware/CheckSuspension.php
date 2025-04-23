<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSuspension
{

    public function handle($request, Closure $next)
    {

        if (auth()->check() && auth()->user()->suspended_status) {
            return redirect()->route('pleading.page');
        }

        return $next($request);
    }


}
