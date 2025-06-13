<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class AvatarController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:1024'],
        ]);

        $path = $request->file('avatar')->store('avatars', 'public');

        $request->user()->avatar = $path;
        $request->user()->save();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Avatar Diperbarui',
            "Avatar user {$request->user()->name} telah diperbarui.",
            'info',
            ['user_id' => $request->user()->id]
        );

        return back()->with('status', 'avatar-updated');
    }
} 