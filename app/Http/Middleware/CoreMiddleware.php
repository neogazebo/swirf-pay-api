<?php

namespace App\Http\Middleware;

use Closure;

class CoreMiddleware
{
    /**
     * check secret key is valid
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
