<?php

namespace App\Http\Middleware;

use Closure;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    function handle($request, Closure $next)
    {
        if (optional(auth()->user())->isAdmin()) {
            return $next($request);
        }

        abort_if($request->wantsJson(), 401);
        return redirect()->guest('login');
    }
}
