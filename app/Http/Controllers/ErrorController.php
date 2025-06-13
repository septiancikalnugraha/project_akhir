<?php

namespace App\Http\Controllers;

use App\Models\Error;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ErrorController extends Controller
{
    public function index()
    {
        $errors = Error::latest()->paginate(10);
        return view('errors.index', compact('errors'));
    }

    public function show(Error $error)
    {
        return view('errors.show', compact('error'));
    }

    public function destroy(Error $error)
    {
        $error->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Error Dihapus',
            "Error telah dihapus.",
            'warning',
            ['error_id' => $error->id]
        );

        return redirect()->route('errors.index')
            ->with('success', 'Error berhasil dihapus.');
    }

    public function clear()
    {
        Error::truncate();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Semua Error Dihapus',
            "Semua error telah dihapus.",
            'warning'
        );

        return redirect()->route('errors.index')
            ->with('success', 'Semua error berhasil dihapus.');
    }
} 