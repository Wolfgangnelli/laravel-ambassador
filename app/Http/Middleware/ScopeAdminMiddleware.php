<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ScopeAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //se lo user authenticato non è admin scope/ability
        if (!$request->user()->tokenCan('admin')) {
            abort(401, 'Unauthorized');
        }

        return $next($request);
    }
}
