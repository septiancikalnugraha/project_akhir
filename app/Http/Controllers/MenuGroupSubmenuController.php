<?php

namespace App\Http\Controllers;

use App\Models\MenuGroupSubmenu;
use App\Models\MenuGroup;
use App\Models\Submenu;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MenuGroupSubmenuController extends Controller
{
    public function index()
    {
        $menuGroupSubmenus = MenuGroupSubmenu::with(['menuGroup', 'submenu'])->get();
        return view('menu-group-submenus.index', compact('menuGroupSubmenus'));
    }

    public function create()
    {
        $menuGroups = MenuGroup::all();
        $submenus = Submenu::all();
        return view('menu-group-submenus.create', compact('menuGroups', 'submenus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'menu_group_id' => ['required', 'exists:menu_groups,id'],
            'submenu_id' => ['required', 'exists:submenus,id'],
        ]);

        $menuGroupSubmenu = MenuGroupSubmenu::create([
            'menu_group_id' => $request->menu_group_id,
            'submenu_id' => $request->submenu_id,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group Submenu Baru',
            "Menu group submenu baru telah dibuat.",
            'success',
            ['menu_group_submenu_id' => $menuGroupSubmenu->id]
        );

        return redirect()->route('menu-group-submenus.index')
            ->with('success', 'Menu group submenu berhasil dibuat.');
    }

    public function edit(MenuGroupSubmenu $menuGroupSubmenu)
    {
        $menuGroups = MenuGroup::all();
        $submenus = Submenu::all();
        return view('menu-group-submenus.edit', compact('menuGroupSubmenu', 'menuGroups', 'submenus'));
    }

    public function update(Request $request, MenuGroupSubmenu $menuGroupSubmenu)
    {
        $request->validate([
            'menu_group_id' => ['required', 'exists:menu_groups,id'],
            'submenu_id' => ['required', 'exists:submenus,id'],
        ]);

        $menuGroupSubmenu->update([
            'menu_group_id' => $request->menu_group_id,
            'submenu_id' => $request->submenu_id,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group Submenu Diperbarui',
            "Menu group submenu telah diperbarui.",
            'info',
            ['menu_group_submenu_id' => $menuGroupSubmenu->id]
        );

        return redirect()->route('menu-group-submenus.index')
            ->with('success', 'Menu group submenu berhasil diperbarui.');
    }

    public function destroy(MenuGroupSubmenu $menuGroupSubmenu)
    {
        $menuGroupSubmenu->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group Submenu Dihapus',
            "Menu group submenu telah dihapus.",
            'warning',
            ['menu_group_submenu_id' => $menuGroupSubmenu->id]
        );

        return redirect()->route('menu-group-submenus.index')
            ->with('success', 'Menu group submenu berhasil dihapus.');
    }
} 