<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\NotificationService;

class CheckRole
{
    public function handle($request, Closure $next, $role)
    {
        if (!$request->user() || !$request->user()->hasRole($role)) {
            // Create notification
            $notificationService = app(NotificationService::class);
            $notificationService->create(
                'Akses Ditolak',
                "User {$request->user()->name} mencoba mengakses halaman yang tidak diizinkan.",
                'error',
                [
                    'user_id' => $request->user()->id,
                    'role' => $role,
                    'url' => $request->fullUrl()
                ]
            );

            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
} 