<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'theme' => ['required', 'string', 'in:light,dark'],
            'language' => ['required', 'string', 'in:en,id'],
            'timezone' => ['required', 'string'],
            'date_format' => ['required', 'string'],
            'time_format' => ['required', 'string'],
        ]);

        $request->user()->update([
            'theme' => $request->theme,
            'language' => $request->language,
            'timezone' => $request->timezone,
            'date_format' => $request->date_format,
            'time_format' => $request->time_format,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Pengaturan Diperbarui',
            "Pengaturan user {$request->user()->name} telah diperbarui.",
            'info',
            ['user_id' => $request->user()->id]
        );

        return back()->with('status', 'settings-updated');
    }
} 