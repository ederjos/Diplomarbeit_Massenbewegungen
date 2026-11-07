<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMeasurementExportPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->user()?->role;

        if (! $role?->is_admin && ! $role?->can_view_detailed_data) {
            abort(403);
        }

        return $next($request);
    }
}
