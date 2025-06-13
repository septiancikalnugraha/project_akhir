<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $notifications = Notification::latest()->paginate(10);
        return view('notifications.index', compact('notifications'));
    }

    public function getUnreadCount()
    {
        $count = $this->notificationService->getUnreadCount(auth()->id());
        return response()->json(['count' => $count]);
    }

    public function getUnread()
    {
        $notifications = $this->notificationService->getUnreadNotifications(auth()->id());
        return response()->json($notifications);
    }

    public function show(Notification $notification)
    {
        return view('notifications.show', compact('notification'));
    }

    public function markAsRead(Notification $notification)
    {
        $notification->update([
            'read_at' => now(),
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Notifikasi Dibaca',
            "Notifikasi telah ditandai sebagai dibaca.",
            'info',
            ['notification_id' => $notification->id]
        );

        return redirect()->route('notifications.index')
            ->with('success', 'Notifikasi berhasil ditandai sebagai dibaca.');
    }

    public function markAllAsRead()
    {
        Notification::whereNull('read_at')->update([
            'read_at' => now(),
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Semua Notifikasi Dibaca',
            "Semua notifikasi telah ditandai sebagai dibaca.",
            'info'
        );

        return redirect()->route('notifications.index')
            ->with('success', 'Semua notifikasi berhasil ditandai sebagai dibaca.');
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Notifikasi Dihapus',
            "Notifikasi telah dihapus.",
            'warning',
            ['notification_id' => $notification->id]
        );

        return redirect()->route('notifications.index')
            ->with('success', 'Notifikasi berhasil dihapus.');
    }

    public function clear()
    {
        Notification::truncate();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Semua Notifikasi Dihapus',
            "Semua notifikasi telah dihapus.",
            'warning'
        );

        return redirect()->route('notifications.index')
            ->with('success', 'Semua notifikasi berhasil dihapus.');
    }
} 