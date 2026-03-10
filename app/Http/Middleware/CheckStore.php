<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStore
{
    /**
     * Ensure every authenticated user has a valid store.
     * Shares store data to all views.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->store_id) {
            // Share store to all blade views
            view()->share('store', $request->user()->store);
        }

        return $next($request);
    }
}
