<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Services\NotificationService;

class SystemController extends Controller
{
    public function index()
    {
        $system = [
            'os' => PHP_OS,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server' => $_SERVER['SERVER_SOFTWARE'],
            'database' => config('database.default'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'debug' => config('app.debug'),
            'maintenance' => app()->isDownForMaintenance(),
        ];

        return view('system.index', compact('system'));
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
            'Sistem Dioptimalkan',
            "Sistem telah dioptimalkan.",
            'success'
        );

        return redirect()->route('system.index')
            ->with('success', 'Sistem berhasil dioptimalkan.');
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
            'Sistem Dihapus',
            "Sistem telah dihapus.",
            'warning'
        );

        return redirect()->route('system.index')
            ->with('success', 'Sistem berhasil dihapus.');
    }
} 