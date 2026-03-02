<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProjectManagementPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->role?->can_manage_projects) {
            abort(403); // Forbidden
        }

        return $next($request);
    }
}
