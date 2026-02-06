<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\ProjectException;
use App\Traits\ApiResponse;

class IsProjectMemberMiddleware
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $project = $request->route('project');

        if (!$user) return $this->error(message: 'Unauthorized', statusCode: 401);
        if (!$project) return $this->error(message: 'Not Found', statusCode: 404);

        if (!$project->isMember($user)) {
            throw ProjectException::notMember(
                userId: $user->id,
                projectId: $project->id,
            );
        }

        return $next($request);
    }
}
