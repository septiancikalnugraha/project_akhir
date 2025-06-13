<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::with('causer')
            ->latest()
            ->paginate(10);

        return view('activities.index', compact('activities'));
    }

    public function show(Activity $activity)
    {
        return view('activities.show', compact('activity'));
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Aktivitas Dihapus',
            "Aktivitas telah dihapus.",
            'warning',
            ['activity_id' => $activity->id]
        );

        return redirect()->route('activities.index')
            ->with('success', 'Aktivitas berhasil dihapus.');
    }

    public function clear()
    {
        Activity::truncate();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Semua Aktivitas Dihapus',
            "Semua aktivitas telah dihapus.",
            'warning'
        );

        return redirect()->route('activities.index')
            ->with('success', 'Semua aktivitas berhasil dihapus.');
    }
} 