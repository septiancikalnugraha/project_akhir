<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Services\NotificationService;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();
        return view('permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('permissions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions'],
        ]);

        $permission = Permission::create([
            'name' => $request->name,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Permission Baru',
            "Permission baru telah dibuat.",
            'success',
            ['permission_id' => $permission->id]
        );

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil dibuat.');
    }

    public function edit(Permission $permission)
    {
        return view('permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name,' . $permission->id],
        ]);

        $permission->update([
            'name' => $request->name,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Permission Diperbarui',
            "Permission {$permission->name} telah diperbarui.",
            'info',
            ['permission_id' => $permission->id]
        );

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil diperbarui.');
    }

    public function destroy(Permission $permission)
    {
        if ($permission->roles()->exists()) {
            return redirect()->route('permissions.index')
                ->with('error', 'Permission tidak dapat dihapus karena masih digunakan oleh role.');
        }

        $permission->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Permission Dihapus',
            "Permission {$permission->name} telah dihapus.",
            'warning',
            ['permission_id' => $permission->id]
        );

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil dihapus.');
    }
} 