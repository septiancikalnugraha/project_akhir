<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Log;

class LogController extends Controller
{
    public function index()
    {
        $logs = Log::latest()->paginate(10);
        return view('logs.index', compact('logs'));
    }

    public function show(Log $log)
    {
        return view('logs.show', compact('log'));
    }

    public function download($filename)
    {
        $path = storage_path('logs/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Log Diunduh',
            "Log telah diunduh.",
            'info',
            ['filename' => $filename]
        );

        return response()->download($path);
    }

    public function destroy(Log $log)
    {
        $log->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Log Dihapus',
            "Log telah dihapus.",
            'warning',
            ['log_id' => $log->id]
        );

        return redirect()->route('logs.index')
            ->with('success', 'Log berhasil dihapus.');
    }

    public function clear()
    {
        Log::truncate();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Semua Log Dihapus',
            "Semua log telah dihapus.",
            'warning'
        );

        return redirect()->route('logs.index')
            ->with('success', 'Semua log berhasil dihapus.');
    }
} 