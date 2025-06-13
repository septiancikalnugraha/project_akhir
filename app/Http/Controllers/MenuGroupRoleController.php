<?php

namespace App\Http\Controllers;

use App\Models\MenuGroupRole;
use App\Models\MenuGroup;
use App\Models\Role;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MenuGroupRoleController extends Controller
{
    public function index()
    {
        $menuGroupRoles = MenuGroupRole::with(['menuGroup', 'role'])->get();
        return view('menu-group-roles.index', compact('menuGroupRoles'));
    }

    public function create()
    {
        $menuGroups = MenuGroup::all();
        $roles = Role::all();
        return view('menu-group-roles.create', compact('menuGroups', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'menu_group_id' => ['required', 'exists:menu_groups,id'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $menuGroupRole = MenuGroupRole::create([
            'menu_group_id' => $request->menu_group_id,
            'role_id' => $request->role_id,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group Role Baru',
            "Menu group role baru telah dibuat.",
            'success',
            ['menu_group_role_id' => $menuGroupRole->id]
        );

        return redirect()->route('menu-group-roles.index')
            ->with('success', 'Menu group role berhasil dibuat.');
    }

    public function edit(MenuGroupRole $menuGroupRole)
    {
        $menuGroups = MenuGroup::all();
        $roles = Role::all();
        return view('menu-group-roles.edit', compact('menuGroupRole', 'menuGroups', 'roles'));
    }

    public function update(Request $request, MenuGroupRole $menuGroupRole)
    {
        $request->validate([
            'menu_group_id' => ['required', 'exists:menu_groups,id'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $menuGroupRole->update([
            'menu_group_id' => $request->menu_group_id,
            'role_id' => $request->role_id,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group Role Diperbarui',
            "Menu group role telah diperbarui.",
            'info',
            ['menu_group_role_id' => $menuGroupRole->id]
        );

        return redirect()->route('menu-group-roles.index')
            ->with('success', 'Menu group role berhasil diperbarui.');
    }

    public function destroy(MenuGroupRole $menuGroupRole)
    {
        $menuGroupRole->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group Role Dihapus',
            "Menu group role telah dihapus.",
            'warning',
            ['menu_group_role_id' => $menuGroupRole->id]
        );

        return redirect()->route('menu-group-roles.index')
            ->with('success', 'Menu group role berhasil dihapus.');
    }
} 