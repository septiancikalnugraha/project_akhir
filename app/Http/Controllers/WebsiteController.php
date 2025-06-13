<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'website' => ['required', 'url', 'max:255'],
        ]);

        $request->user()->website = $request->website;
        $request->user()->save();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Website Diperbarui',
            "Website user {$request->user()->name} telah diperbarui.",
            'info',
            ['user_id' => $request->user()->id]
        );

        return back()->with('status', 'website-updated');
    }
} 