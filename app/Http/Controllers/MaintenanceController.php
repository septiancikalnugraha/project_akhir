<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Services\NotificationService;

class MaintenanceController extends Controller
{
    public function index()
    {
        return view('maintenance.index');
    }

    public function down()
    {
        Artisan::call('down');

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Mode Maintenance',
            "Aplikasi telah dimasukkan ke mode maintenance.",
            'warning'
        );

        return redirect()->route('maintenance.index')
            ->with('success', 'Aplikasi berhasil dimasukkan ke mode maintenance.');
    }

    public function up()
    {
        Artisan::call('up');

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Mode Normal',
            "Aplikasi telah dikembalikan ke mode normal.",
            'success'
        );

        return redirect()->route('maintenance.index')
            ->with('success', 'Aplikasi berhasil dikembalikan ke mode normal.');
    }
} 