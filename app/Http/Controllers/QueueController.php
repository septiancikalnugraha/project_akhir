<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Services\NotificationService;

class QueueController extends Controller
{
    public function index()
    {
        return view('queue.index');
    }

    public function start()
    {
        Artisan::call('queue:work', [
            '--daemon' => true
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Queue Dimulai',
            "Queue worker telah dimulai.",
            'success'
        );

        return redirect()->route('queue.index')
            ->with('success', 'Queue worker berhasil dimulai.');
    }

    public function stop()
    {
        Artisan::call('queue:restart');

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Queue Dihentikan',
            "Queue worker telah dihentikan.",
            'warning'
        );

        return redirect()->route('queue.index')
            ->with('success', 'Queue worker berhasil dihentikan.');
    }

    public function clear()
    {
        Artisan::call('queue:flush');
        Artisan::call('queue:clear');

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Queue Dihapus',
            "Queue telah dihapus.",
            'info'
        );

        return redirect()->route('queue.index')
            ->with('success', 'Queue berhasil dihapus.');
    }
} 