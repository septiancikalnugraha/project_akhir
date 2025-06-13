<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . auth()->id()],
        ]);

        $request->user()->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Profil Diperbarui',
            "Profil user {$request->user()->name} telah diperbarui.",
            'info',
            ['user_id' => $request->user()->id]
        );

        return redirect()->route('profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Password Diperbarui',
            "Password user {$request->user()->name} telah diperbarui.",
            'info',
            ['user_id' => $request->user()->id]
        );

        return redirect()->route('profile.edit')
            ->with('success', 'Password berhasil diperbarui.');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:1024'],
        ]);

        if ($request->user()->avatar) {
            Storage::delete($request->user()->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $request->user()->update([
            'avatar' => $path,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Avatar Diperbarui',
            "Avatar user {$request->user()->name} telah diperbarui.",
            'info',
            ['user_id' => $request->user()->id]
        );

        return redirect()->route('profile.edit')
            ->with('success', 'Avatar berhasil diperbarui.');
    }

    public function deleteAvatar(Request $request)
    {
        if ($request->user()->avatar) {
            Storage::delete($request->user()->avatar);
        }

        $request->user()->update([
            'avatar' => null,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Avatar Dihapus',
            "Avatar user {$request->user()->name} telah dihapus.",
            'warning',
            ['user_id' => $request->user()->id]
        );

        return redirect()->route('profile.edit')
            ->with('success', 'Avatar berhasil dihapus.');
    }

    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Akun Dihapus',
            "Akun user {$user->name} telah dihapus.",
            'warning',
            ['user_id' => $user->id]
        );

        return redirect('/');
    }
} 