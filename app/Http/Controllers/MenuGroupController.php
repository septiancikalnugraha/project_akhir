<?php

namespace App\Http\Controllers;

use App\Models\MenuGroup;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MenuGroupController extends Controller
{
    public function index()
    {
        $menuGroups = MenuGroup::with('menus')->get();
        return view('menu-groups.index', compact('menuGroups'));
    }

    public function create()
    {
        return view('menu-groups.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'order' => ['required', 'integer'],
        ]);

        $menuGroup = MenuGroup::create([
            'name' => $request->name,
            'order' => $request->order,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group Baru',
            "Menu group baru telah dibuat.",
            'success',
            ['menu_group_id' => $menuGroup->id]
        );

        return redirect()->route('menu-groups.index')
            ->with('success', 'Menu group berhasil dibuat.');
    }

    public function edit(MenuGroup $menuGroup)
    {
        return view('menu-groups.edit', compact('menuGroup'));
    }

    public function update(Request $request, MenuGroup $menuGroup)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'order' => ['required', 'integer'],
        ]);

        $menuGroup->update([
            'name' => $request->name,
            'order' => $request->order,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group Diperbarui',
            "Menu group {$menuGroup->name} telah diperbarui.",
            'info',
            ['menu_group_id' => $menuGroup->id]
        );

        return redirect()->route('menu-groups.index')
            ->with('success', 'Menu group berhasil diperbarui.');
    }

    public function destroy(MenuGroup $menuGroup)
    {
        $menuGroup->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Menu Group Dihapus',
            "Menu group {$menuGroup->name} telah dihapus.",
            'warning',
            ['menu_group_id' => $menuGroup->id]
        );

        return redirect()->route('menu-groups.index')
            ->with('success', 'Menu group berhasil dihapus.');
    }
} 