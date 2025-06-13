<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class StorageController extends Controller
{
    public function index()
    {
        return view('storage.index');
    }

    public function link()
    {
        Artisan::call('storage:link');

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Storage Link Dibuat',
            "Storage link telah dibuat.",
            'success'
        );

        return redirect()->route('storage.index')
            ->with('success', 'Storage link berhasil dibuat.');
    }

    public function clear()
    {
        Artisan::call('storage:clear');

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Storage Dihapus',
            "Storage telah dihapus.",
            'warning'
        );

        return redirect()->route('storage.index')
            ->with('success', 'Storage berhasil dihapus.');
    }
} 