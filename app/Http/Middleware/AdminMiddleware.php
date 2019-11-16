<?php

namespace App\Http\Middleware;

use App\Components\ErrorMessage;
use App\Components\Response;
use Closure;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user() && !$request->user()->is_admin) {
            return Response::error(ErrorMessage::CANNOT_PERFORM_ACTION, 'Admin Permission needed', null, 401);
        }
        return $next($request);
    }
}
