<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use App\Services\NotificationService;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            // Create notification
            $notificationService = app(NotificationService::class);
            $notificationService->create(
                'Login Gagal',
                "Seseorang mencoba mengakses halaman yang membutuhkan autentikasi.",
                'error',
                [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl()
                ]
            );
        }

        return $request->expectsJson() ? null : route('login');
    }
} 