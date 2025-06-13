<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class GenderController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'gender' => ['required', 'string', 'in:male,female'],
        ]);

        $request->user()->gender = $request->gender;
        $request->user()->save();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Jenis Kelamin Diperbarui',
            "Jenis kelamin user {$request->user()->name} telah diperbarui.",
            'info',
            ['user_id' => $request->user()->id]
        );

        return back()->with('status', 'gender-updated');
    }
} 