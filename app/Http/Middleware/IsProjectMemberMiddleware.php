<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsProjectMemberMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // get project data
        $project = $request->route('project');

        // check if user is project member
        if ($project && ($project->isMember($request->user()) || Auth::user()->role == 'admin')) return $next($request);

        return response()->json([
            'message' => 'Access denied: Not project member!'
        ], 403);
    }
}
