<?php

namespace App\Http\Controllers;

use App\Models\MenuGroup;
use App\Models\MenuGroupPermission;
use App\Models\Permission;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MenuGroupPermissionController extends Controller
{
    public function index()
    {
        $menuGroupPermissions = MenuGroupPermission::with(['menuGroup', 'permission'])->get();
        return view('menu-group-permissions.index', compact('menuGroupPermissions'));
    }

    public function create()
    {
        $menuGroups = MenuGroup::all();
        $permissions = Permission::all();
        return view('menu-group-permissions.create', compact('menuGroups', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'menu_group_id' => ['required', 'exists:menu_groups,id'],
            'permission_id' => ['required', 'exists:permissions,id'],
        ]);

        $menuGroupPermission = MenuGroupPermission::create([
            'menu_group_id' => $request->menu_group_id,
            'permission_id' => $request->permission_id,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group Permission Baru',
            "Menu group permission baru telah dibuat.",
            'success',
            ['menu_group_permission_id' => $menuGroupPermission->id]
        );

        return redirect()->route('menu-group-permissions.index')
            ->with('success', 'Menu group permission berhasil dibuat.');
    }

    public function edit(MenuGroupPermission $menuGroupPermission)
    {
        $menuGroups = MenuGroup::all();
        $permissions = Permission::all();
        return view('menu-group-permissions.edit', compact('menuGroupPermission', 'menuGroups', 'permissions'));
    }

    public function update(Request $request, MenuGroupPermission $menuGroupPermission)
    {
        $request->validate([
            'menu_group_id' => ['required', 'exists:menu_groups,id'],
            'permission_id' => ['required', 'exists:permissions,id'],
        ]);

        $menuGroupPermission->update([
            'menu_group_id' => $request->menu_group_id,
            'permission_id' => $request->permission_id,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group Permission Diperbarui',
            "Menu group permission telah diperbarui.",
            'info',
            ['menu_group_permission_id' => $menuGroupPermission->id]
        );

        return redirect()->route('menu-group-permissions.index')
            ->with('success', 'Menu group permission berhasil diperbarui.');
    }

    public function destroy(MenuGroupPermission $menuGroupPermission)
    {
        $menuGroupPermission->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group Permission Dihapus',
            "Menu group permission telah dihapus.",
            'warning',
            ['menu_group_permission_id' => $menuGroupPermission->id]
        );

        return redirect()->route('menu-group-permissions.index')
            ->with('success', 'Menu group permission berhasil dihapus.');
    }
} 