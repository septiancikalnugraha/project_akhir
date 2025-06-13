<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;

class VerifyEmailController extends Controller
{
    public function verify(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect($this->redirectPath())->with('verified', true);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));

            // Create notification
            $notificationService = app(NotificationService::class);
            $notificationService->create(
                'Email Terverifikasi',
                "Email user {$request->user()->name} telah diverifikasi.",
                'success',
                ['user_id' => $request->user()->id]
            );
        }

        return redirect($this->redirectPath())->with('verified', true);
    }
} 