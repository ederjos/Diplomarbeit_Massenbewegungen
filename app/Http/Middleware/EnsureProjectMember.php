<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProjectMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $projectId = $request->route('project')?->id;

        if (! $request->user()?->isMemberOfProject($projectId)) {
            abort(403); // Forbidden
        }

        return $next($request);
    }
}
