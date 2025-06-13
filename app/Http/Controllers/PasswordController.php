<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Password Diperbarui',
            "Password user {$request->user()->name} telah diperbarui.",
            'warning',
            ['user_id' => $request->user()->id]
        );

        return back()->with('status', 'password-updated');
    }
} 