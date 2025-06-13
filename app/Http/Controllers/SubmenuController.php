<?php

namespace App\Http\Controllers;

use App\Models\Submenu;
use App\Models\Menu;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class SubmenuController extends Controller
{
    public function index()
    {
        $submenus = Submenu::with('menu')->get();
        return view('submenus.index', compact('submenus'));
    }

    public function create()
    {
        $menus = Menu::all();
        return view('submenus.create', compact('menus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'menu_id' => ['required', 'exists:menus,id'],
            'order' => ['required', 'integer'],
        ]);

        $submenu = Submenu::create([
            'name' => $request->name,
            'url' => $request->url,
            'icon' => $request->icon,
            'menu_id' => $request->menu_id,
            'order' => $request->order,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Submenu Baru',
            "Submenu baru telah dibuat.",
            'success',
            ['submenu_id' => $submenu->id]
        );

        return redirect()->route('submenus.index')
            ->with('success', 'Submenu berhasil dibuat.');
    }

    public function edit(Submenu $submenu)
    {
        $menus = Menu::all();
        return view('submenus.edit', compact('submenu', 'menus'));
    }

    public function update(Request $request, Submenu $submenu)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'menu_id' => ['required', 'exists:menus,id'],
            'order' => ['required', 'integer'],
        ]);

        $submenu->update([
            'name' => $request->name,
            'url' => $request->url,
            'icon' => $request->icon,
            'menu_id' => $request->menu_id,
            'order' => $request->order,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Submenu Diperbarui',
            "Submenu {$submenu->name} telah diperbarui.",
            'info',
            ['submenu_id' => $submenu->id]
        );

        return redirect()->route('submenus.index')
            ->with('success', 'Submenu berhasil diperbarui.');
    }

    public function destroy(Submenu $submenu)
    {
        $submenu->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Submenu Dihapus',
            "Submenu {$submenu->name} telah dihapus.",
            'warning',
            ['submenu_id' => $submenu->id]
        );

        return redirect()->route('submenus.index')
            ->with('success', 'Submenu berhasil dihapus.');
    }
} 