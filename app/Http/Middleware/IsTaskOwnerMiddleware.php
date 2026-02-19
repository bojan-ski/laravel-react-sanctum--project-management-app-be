<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\TaskException;
use App\Traits\ApiResponse;

class IsTaskOwnerMiddleware
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
        $task = $request->route('task');

        if (!$user) return $this->error(message: 'Unauthorized', statusCode: 401);
        if (!$task) return $this->error(message: 'Not Found', statusCode: 404);

        if (!$task->canManageTask($user)) {
            throw TaskException::notTaskOwner(
                userId: $user->id,
                taskId: $task->id,
            );
        }

        return $next($request);
    }
}
