<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class PhoneController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->phone = $request->phone;
        $request->user()->save();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Nomor Telepon Diperbarui',
            "Nomor telepon user {$request->user()->name} telah diperbarui.",
            'info',
            ['user_id' => $request->user()->id]
        );

        return back()->with('status', 'phone-updated');
    }
} 