<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request, plus specify which methods are allowed
     * By default GET,POST,PUT,DELETE,OPTIONS,PATCH are allowed
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next, ...$allowedMethods)
    {
        return $next($request)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', implode(",", $allowedMethods) );
    }
}
