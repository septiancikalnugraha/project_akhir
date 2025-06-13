<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuPermission;
use App\Models\Permission;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MenuPermissionController extends Controller
{
    public function index()
    {
        $menuPermissions = MenuPermission::with(['menu', 'permission'])->get();
        return view('menu-permissions.index', compact('menuPermissions'));
    }

    public function create()
    {
        $menus = Menu::all();
        $permissions = Permission::all();
        return view('menu-permissions.create', compact('menus', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'menu_id' => ['required', 'exists:menus,id'],
            'permission_id' => ['required', 'exists:permissions,id'],
        ]);

        $menuPermission = MenuPermission::create([
            'menu_id' => $request->menu_id,
            'permission_id' => $request->permission_id,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Permission Baru',
            "Menu permission baru telah dibuat.",
            'success',
            ['menu_permission_id' => $menuPermission->id]
        );

        return redirect()->route('menu-permissions.index')
            ->with('success', 'Menu permission berhasil dibuat.');
    }

    public function edit(MenuPermission $menuPermission)
    {
        $menus = Menu::all();
        $permissions = Permission::all();
        return view('menu-permissions.edit', compact('menuPermission', 'menus', 'permissions'));
    }

    public function update(Request $request, MenuPermission $menuPermission)
    {
        $request->validate([
            'menu_id' => ['required', 'exists:menus,id'],
            'permission_id' => ['required', 'exists:permissions,id'],
        ]);

        $menuPermission->update([
            'menu_id' => $request->menu_id,
            'permission_id' => $request->permission_id,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Permission Diperbarui',
            "Menu permission telah diperbarui.",
            'info',
            ['menu_permission_id' => $menuPermission->id]
        );

        return redirect()->route('menu-permissions.index')
            ->with('success', 'Menu permission berhasil diperbarui.');
    }

    public function destroy(MenuPermission $menuPermission)
    {
        $menuPermission->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Permission Dihapus',
            "Menu permission telah dihapus.",
            'warning',
            ['menu_permission_id' => $menuPermission->id]
        );

        return redirect()->route('menu-permissions.index')
            ->with('success', 'Menu permission berhasil dihapus.');
    }
} 