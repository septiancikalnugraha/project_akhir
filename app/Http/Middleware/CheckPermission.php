<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\NotificationService;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!$request->user() || !$request->user()->hasPermission($permission)) {
            // Create notification
            $notificationService = app(NotificationService::class);
            $notificationService->create(
                'Akses Ditolak',
                "User {$request->user()->name} mencoba mengakses halaman yang tidak diizinkan.",
                'error',
                [
                    'user_id' => $request->user()->id,
                    'permission' => $permission,
                    'url' => $request->fullUrl()
                ]
            );

            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
} 