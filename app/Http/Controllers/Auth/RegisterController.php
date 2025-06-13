<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\NotificationService;

class RegisterController extends Controller
{
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'User Baru',
            "User {$user->name} telah mendaftar ke sistem.",
            'info',
            ['user_id' => $user->id]
        );

        return $user;
    }
} 