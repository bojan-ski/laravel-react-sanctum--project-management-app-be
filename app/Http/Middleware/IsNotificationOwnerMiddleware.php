<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\NotificationException;
use App\Traits\ApiResponse;

class IsNotificationOwnerMiddleware
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
        $notification = $request->route('notification');

        if (!$user) return $this->error(message: 'Unauthorized', statusCode: 401);
        if (!$notification) return $this->error(message: 'Not Found', statusCode: 404);

        if ($user->id !== $notification->user_id) {
            throw NotificationException::notNotificationOwner(
                notificationId: $notification->id,
                userId: $user->id,
            );
        }

        return $next($request);
    }
}
