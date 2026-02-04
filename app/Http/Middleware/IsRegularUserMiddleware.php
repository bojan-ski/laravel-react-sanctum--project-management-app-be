<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ApiResponse;
use App\Enums\UserRole;

class IsRegularUserMiddleware
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

        if (!$user) return $this->error(message: 'Unauthorized', statusCode: 401);
        if ($user->role !== UserRole::USER) return $this->error(message: 'Forbidden', statusCode: 403);

        return $next($request);
    }
}
