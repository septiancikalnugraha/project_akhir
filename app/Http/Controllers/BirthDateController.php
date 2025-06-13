<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BirthDateController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'birth_date' => ['required', 'date'],
        ]);

        $request->user()->birth_date = $request->birth_date;
        $request->user()->save();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Tanggal Lahir Diperbarui',
            "Tanggal lahir user {$request->user()->name} telah diperbarui.",
            'info',
            ['user_id' => $request->user()->id]
        );

        return back()->with('status', 'birth-date-updated');
    }
} 