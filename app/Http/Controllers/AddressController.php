<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'address' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->address = $request->address;
        $request->user()->save();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Alamat Diperbarui',
            "Alamat user {$request->user()->name} telah diperbarui.",
            'info',
            ['user_id' => $request->user()->id]
        );

        return back()->with('status', 'address-updated');
    }
} 