<?php

namespace App\Http\Controllers;

use App\Models\MenuGroupUser;
use App\Models\MenuGroup;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MenuGroupUserController extends Controller
{
    public function index()
    {
        $menuGroupUsers = MenuGroupUser::with(['menuGroup', 'user'])->get();
        return view('menu-group-users.index', compact('menuGroupUsers'));
    }

    public function create()
    {
        $menuGroups = MenuGroup::all();
        $users = User::all();
        return view('menu-group-users.create', compact('menuGroups', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'menu_group_id' => ['required', 'exists:menu_groups,id'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $menuGroupUser = MenuGroupUser::create([
            'menu_group_id' => $request->menu_group_id,
            'user_id' => $request->user_id,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group User Baru',
            "Menu group user baru telah dibuat.",
            'success',
            ['menu_group_user_id' => $menuGroupUser->id]
        );

        return redirect()->route('menu-group-users.index')
            ->with('success', 'Menu group user berhasil dibuat.');
    }

    public function edit(MenuGroupUser $menuGroupUser)
    {
        $menuGroups = MenuGroup::all();
        $users = User::all();
        return view('menu-group-users.edit', compact('menuGroupUser', 'menuGroups', 'users'));
    }

    public function update(Request $request, MenuGroupUser $menuGroupUser)
    {
        $request->validate([
            'menu_group_id' => ['required', 'exists:menu_groups,id'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $menuGroupUser->update([
            'menu_group_id' => $request->menu_group_id,
            'user_id' => $request->user_id,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group User Diperbarui',
            "Menu group user telah diperbarui.",
            'info',
            ['menu_group_user_id' => $menuGroupUser->id]
        );

        return redirect()->route('menu-group-users.index')
            ->with('success', 'Menu group user berhasil diperbarui.');
    }

    public function destroy(MenuGroupUser $menuGroupUser)
    {
        $menuGroupUser->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group User Dihapus',
            "Menu group user telah dihapus.",
            'warning',
            ['menu_group_user_id' => $menuGroupUser->id]
        );

        return redirect()->route('menu-group-users.index')
            ->with('success', 'Menu group user berhasil dihapus.');
    }
} 