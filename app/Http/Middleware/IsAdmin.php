<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && (Auth::user()->user_type === 'admin' || 
                Auth::user()->user_type === 'staff' || 
                Auth::user()->user_type === 'tour listing associate')) {
            return $next($request);
        }
        auth()->logout();
        return redirect()->route('login')->with('error', 'You don’t have permission to access the dashboard. Please contact the admin.');
    }
}
