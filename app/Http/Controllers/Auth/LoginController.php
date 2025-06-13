<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    protected function authenticated(Request $request, $user)
    {
        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Login Berhasil',
            "User {$user->name} telah login ke sistem.",
            'success',
            [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]
        );
    }

    protected function loggedOut(Request $request)
    {
        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Logout',
            "User telah logout dari sistem.",
            'info',
            [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]
        );
    }
} 