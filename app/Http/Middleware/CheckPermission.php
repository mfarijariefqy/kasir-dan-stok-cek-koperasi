<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Split permissions by pipe (|) to support multiple permissions
        $permissions = explode('|', $permission);

        // Check if user has any of the required permissions
        if (!$request->user() || !$request->user()->hasAnyPermission($permissions)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
