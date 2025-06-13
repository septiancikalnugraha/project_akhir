<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ScheduleController extends Controller
{
    public function index()
    {
        return view('schedule.index');
    }

    public function run()
    {
        Artisan::call('schedule:run');

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Schedule Dijalankan',
            "Schedule telah dijalankan.",
            'success'
        );

        return redirect()->route('schedule.index')
            ->with('success', 'Schedule berhasil dijalankan.');
    }

    public function list()
    {
        Artisan::call('schedule:list');

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Schedule Daftar',
            "Daftar schedule telah ditampilkan.",
            'info'
        );

        return redirect()->route('schedule.index')
            ->with('success', 'Daftar schedule berhasil ditampilkan.');
    }
} 