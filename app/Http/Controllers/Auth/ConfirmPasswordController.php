<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ConfirmPasswordController extends Controller
{
    protected function resetPasswordConfirmationTimeout(Request $request)
    {
        $request->session()->put('auth.password_confirmed_at', time());

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Konfirmasi Password',
            "User {$request->user()->name} telah mengkonfirmasi password.",
            'info',
            ['user_id' => $request->user()->id]
        );
    }
} 