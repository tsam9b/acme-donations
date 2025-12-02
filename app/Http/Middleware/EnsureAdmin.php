<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user || (isset($user->role) && $user->role !== 'admin')) {
            // If AJAX or Inertia request, abort with 403
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                abort(403);
            }

            // Otherwise redirect to dashboard or homepage
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}

