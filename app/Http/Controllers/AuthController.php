<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Services\NotificationService;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            // Create notification
            $notificationService = app(NotificationService::class);
            $notificationService->create(
                'Login Berhasil',
                "User {$request->user()->name} telah login.",
                'success',
                ['user_id' => $request->user()->id]
            );

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Registrasi Berhasil',
            "User baru {$user->name} telah mendaftar.",
            'success',
            ['user_id' => $user->id]
        );

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Logout Berhasil',
            "User " . auth()->user()->name . " telah logout.",
            'info',
            ['user_id' => auth()->id()]
        );

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            // Create notification
            $notificationService = app(NotificationService::class);
            $notificationService->create(
                'Reset Password',
                "Link reset password telah dikirim ke email {$request->email}.",
                'info'
            );

            return back()->with('status', __($status));
        }

        return back()->withErrors([
            'email' => __($status),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->update([
                    'password' => Hash::make($password),
                ]);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // Create notification
            $notificationService = app(NotificationService::class);
            $notificationService->create(
                'Password Direset',
                "Password user telah direset.",
                'success'
            );

            return redirect()->route('login')->with('status', __($status));
        }

        return back()->withErrors([
            'email' => __($status),
        ]);
    }
} 