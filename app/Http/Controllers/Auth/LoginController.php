<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    protected function authenticated(Request $request, $user)
    {
        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Login Berhasil',
            "User {$user->name} telah login ke sistem.",
            'success',
            [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]
        );
    }

    protected function loggedOut(Request $request)
    {
        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Logout',
            "User telah logout dari sistem.",
            'info',
            [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]
        );
    }
} 