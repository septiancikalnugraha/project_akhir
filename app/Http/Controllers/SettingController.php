<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => ['required', 'array'],
        ]);

        foreach ($request->settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Pengaturan Diperbarui',
            "Pengaturan telah diperbarui.",
            'info'
        );

        return redirect()->route('settings.index')
            ->with('success', 'Pengaturan berhasil diperbarui.');
    }
} 