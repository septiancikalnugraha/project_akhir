<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use App\Services\NotificationService;

class BackupController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index()
    {
        $backups = Storage::disk('backups')->files();
        return view('backups.index', compact('backups'));
    }

    public function create()
    {
        Artisan::call('backup:run');

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Backup Dibuat',
            "Backup database telah dibuat.",
            'success'
        );

        return redirect()->route('backups.index')
            ->with('success', 'Backup berhasil dibuat.');
    }

    public function download($filename)
    {
        $path = storage_path('app/backups/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Backup Diunduh',
            "Backup database telah diunduh.",
            'info',
            ['filename' => $filename]
        );

        return response()->download($path);
    }

    public function destroy($filename)
    {
        Storage::disk('backups')->delete($filename);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Backup Dihapus',
            "Backup database telah dihapus.",
            'warning',
            ['filename' => $filename]
        );

        return redirect()->route('backups.index')
            ->with('success', 'Backup berhasil dihapus.');
    }

    public function restore($filename)
    {
        $path = storage_path('app/backups/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        Artisan::call('backup:restore', [
            '--filename' => $filename
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Backup Dipulihkan',
            "Backup database telah dipulihkan.",
            'success',
            ['filename' => $filename]
        );

        return redirect()->route('backups.index')
            ->with('success', 'Backup berhasil dipulihkan.');
    }

    public function cleanup()
    {
        $count = $this->backupService->cleanupOldBackups();
        
        return redirect()->route('backups.index')
            ->with('success', "{$count} backup lama berhasil dihapus");
    }
} 