<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Verified;
use App\Services\NotificationService;

class EmailVerificationPromptController extends Controller
{
    public function __invoke(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(RouteServiceProvider::HOME)
            : view('auth.verify-email');
    }

    public function send(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        $request->user()->sendEmailVerificationNotification();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Verifikasi Email',
            "Link verifikasi email telah dikirim ke {$request->user()->email}.",
            'info',
            ['user_id' => $request->user()->id]
        );

        return back()->with('status', 'verification-link-sent');
    }
} 