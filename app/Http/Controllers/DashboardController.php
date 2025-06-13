<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $users = User::count();
        $roles = Role::count();
        $permissions = Permission::count();
        $notifications = Notification::count();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Dashboard Diakses',
            "Dashboard telah diakses oleh user " . auth()->user()->name . ".",
            'info'
        );

        return view('dashboard', compact('users', 'roles', 'permissions', 'notifications'));
    }
} 