<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Services\NotificationService;

class CacheController extends Controller
{
    public function index()
    {
        return view('cache.index');
    }

    public function clear()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Cache Dihapus',
            "Cache aplikasi telah dihapus.",
            'info'
        );

        return redirect()->route('cache.index')
            ->with('success', 'Cache berhasil dihapus.');
    }

    public function optimize()
    {
        Artisan::call('optimize');
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Cache Dioptimalkan',
            "Cache aplikasi telah dioptimalkan.",
            'success'
        );

        return redirect()->route('cache.index')
            ->with('success', 'Cache berhasil dioptimalkan.');
    }
} 