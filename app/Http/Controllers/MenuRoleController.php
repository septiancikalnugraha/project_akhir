<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuRole;
use App\Models\Role;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MenuRoleController extends Controller
{
    public function index()
    {
        $menuRoles = MenuRole::with(['menu', 'role'])->get();
        return view('menu-roles.index', compact('menuRoles'));
    }

    public function create()
    {
        $menus = Menu::all();
        $roles = Role::all();
        return view('menu-roles.create', compact('menus', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'menu_id' => ['required', 'exists:menus,id'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $menuRole = MenuRole::create([
            'menu_id' => $request->menu_id,
            'role_id' => $request->role_id,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Role Baru',
            "Menu role baru telah dibuat.",
            'success',
            ['menu_role_id' => $menuRole->id]
        );

        return redirect()->route('menu-roles.index')
            ->with('success', 'Menu role berhasil dibuat.');
    }

    public function edit(MenuRole $menuRole)
    {
        $menus = Menu::all();
        $roles = Role::all();
        return view('menu-roles.edit', compact('menuRole', 'menus', 'roles'));
    }

    public function update(Request $request, MenuRole $menuRole)
    {
        $request->validate([
            'menu_id' => ['required', 'exists:menus,id'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $menuRole->update([
            'menu_id' => $request->menu_id,
            'role_id' => $request->role_id,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Role Diperbarui',
            "Menu role telah diperbarui.",
            'info',
            ['menu_role_id' => $menuRole->id]
        );

        return redirect()->route('menu-roles.index')
            ->with('success', 'Menu role berhasil diperbarui.');
    }

    public function destroy(MenuRole $menuRole)
    {
        $menuRole->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Role Dihapus',
            "Menu role telah dihapus.",
            'warning',
            ['menu_role_id' => $menuRole->id]
        );

        return redirect()->route('menu-roles.index')
            ->with('success', 'Menu role berhasil dihapus.');
    }
} 