<?php

namespace App\Http\Controllers;

use App\Models\MenuUser;
use App\Models\Menu;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MenuUserController extends Controller
{
    public function index()
    {
        $menuUsers = MenuUser::with(['menu', 'user'])->get();
        return view('menu-users.index', compact('menuUsers'));
    }

    public function create()
    {
        $menus = Menu::all();
        $users = User::all();
        return view('menu-users.create', compact('menus', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'menu_id' => ['required', 'exists:menus,id'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $menuUser = MenuUser::create([
            'menu_id' => $request->menu_id,
            'user_id' => $request->user_id,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu User Baru',
            "Menu user baru telah dibuat.",
            'success',
            ['menu_user_id' => $menuUser->id]
        );

        return redirect()->route('menu-users.index')
            ->with('success', 'Menu user berhasil dibuat.');
    }

    public function edit(MenuUser $menuUser)
    {
        $menus = Menu::all();
        $users = User::all();
        return view('menu-users.edit', compact('menuUser', 'menus', 'users'));
    }

    public function update(Request $request, MenuUser $menuUser)
    {
        $request->validate([
            'menu_id' => ['required', 'exists:menus,id'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $menuUser->update([
            'menu_id' => $request->menu_id,
            'user_id' => $request->user_id,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu User Diperbarui',
            "Menu user telah diperbarui.",
            'info',
            ['menu_user_id' => $menuUser->id]
        );

        return redirect()->route('menu-users.index')
            ->with('success', 'Menu user berhasil diperbarui.');
    }

    public function destroy(MenuUser $menuUser)
    {
        $menuUser->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu User Dihapus',
            "Menu user telah dihapus.",
            'warning',
            ['menu_user_id' => $menuUser->id]
        );

        return redirect()->route('menu-users.index')
            ->with('success', 'Menu user berhasil dihapus.');
    }
} 