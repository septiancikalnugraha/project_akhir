<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BioController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'bio' => ['required', 'string', 'max:1000'],
        ]);

        $request->user()->bio = $request->bio;
        $request->user()->save();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Bio Diperbarui',
            "Bio user {$request->user()->name} telah diperbarui.",
            'info',
            ['user_id' => $request->user()->id]
        );

        return back()->with('status', 'bio-updated');
    }
} 