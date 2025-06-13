<?php

namespace App\Http\Controllers;

use App\Models\MenuGroupMenuItem;
use App\Models\MenuGroup;
use App\Models\MenuItem;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MenuGroupMenuItemController extends Controller
{
    public function index()
    {
        $menuGroupMenuItems = MenuGroupMenuItem::with(['menuGroup', 'menuItem'])->get();
        return view('menu-group-menu-items.index', compact('menuGroupMenuItems'));
    }

    public function create()
    {
        $menuGroups = MenuGroup::all();
        $menuItems = MenuItem::all();
        return view('menu-group-menu-items.create', compact('menuGroups', 'menuItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'menu_group_id' => ['required', 'exists:menu_groups,id'],
            'menu_item_id' => ['required', 'exists:menu_items,id'],
        ]);

        $menuGroupMenuItem = MenuGroupMenuItem::create([
            'menu_group_id' => $request->menu_group_id,
            'menu_item_id' => $request->menu_item_id,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group Menu Item Baru',
            "Menu group menu item baru telah dibuat.",
            'success',
            ['menu_group_menu_item_id' => $menuGroupMenuItem->id]
        );

        return redirect()->route('menu-group-menu-items.index')
            ->with('success', 'Menu group menu item berhasil dibuat.');
    }

    public function edit(MenuGroupMenuItem $menuGroupMenuItem)
    {
        $menuGroups = MenuGroup::all();
        $menuItems = MenuItem::all();
        return view('menu-group-menu-items.edit', compact('menuGroupMenuItem', 'menuGroups', 'menuItems'));
    }

    public function update(Request $request, MenuGroupMenuItem $menuGroupMenuItem)
    {
        $request->validate([
            'menu_group_id' => ['required', 'exists:menu_groups,id'],
            'menu_item_id' => ['required', 'exists:menu_items,id'],
        ]);

        $menuGroupMenuItem->update([
            'menu_group_id' => $request->menu_group_id,
            'menu_item_id' => $request->menu_item_id,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group Menu Item Diperbarui',
            "Menu group menu item telah diperbarui.",
            'info',
            ['menu_group_menu_item_id' => $menuGroupMenuItem->id]
        );

        return redirect()->route('menu-group-menu-items.index')
            ->with('success', 'Menu group menu item berhasil diperbarui.');
    }

    public function destroy(MenuGroupMenuItem $menuGroupMenuItem)
    {
        $menuGroupMenuItem->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group Menu Item Dihapus',
            "Menu group menu item telah dihapus.",
            'warning',
            ['menu_group_menu_item_id' => $menuGroupMenuItem->id]
        );

        return redirect()->route('menu-group-menu-items.index')
            ->with('success', 'Menu group menu item berhasil dihapus.');
    }
} 