<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackLastActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = auth()->user()) {
            if (! $user->last_active_at || $user->last_active_at->lt(now()->subMinute())) {
                $user->update(['last_active_at' => now()]);
            }
        }

        return $next($request);
    }
}
