<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
        ]);

        $request->user()->email = $request->email;
        $request->user()->email_verified_at = null;
        $request->user()->save();

        $request->user()->sendEmailVerificationNotification();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Email Diperbarui',
            "Email user {$request->user()->name} telah diperbarui.",
            'info',
            ['user_id' => $request->user()->id]
        );

        return back()->with('status', 'email-updated');
    }
} 