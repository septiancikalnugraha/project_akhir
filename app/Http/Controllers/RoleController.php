<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Services\NotificationService;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
            'permissions' => ['required', 'array'],
        ]);

        $role = Role::create([
            'name' => $request->name,
        ]);

        $role->permissions()->attach($request->permissions);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Role Baru',
            "Role baru telah dibuat.",
            'success',
            ['role_id' => $role->id]
        );

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil dibuat.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        return view('roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['required', 'array'],
        ]);

        $role->update([
            'name' => $request->name,
        ]);

        $role->permissions()->sync($request->permissions);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Role Diperbarui',
            "Role {$role->name} telah diperbarui.",
            'info',
            ['role_id' => $role->id]
        );

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Role Dihapus',
            "Role {$role->name} telah dihapus.",
            'warning',
            ['role_id' => $role->id]
        );

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil dihapus.');
    }
} 