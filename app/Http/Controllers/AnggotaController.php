<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Http\Request;
use App\Services\NotificationService;

class AnggotaController extends Controller
{
    public function index()
    {
        $anggotas = Anggota::latest()->paginate(10);
        return view('anggota.index', compact('anggotas'));
    }

    public function create()
    {
        return view('anggota.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'telepon' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $anggota = Anggota::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
            'email' => $request->email,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Anggota Baru',
            "Anggota baru {$anggota->nama} telah ditambahkan.",
            'success',
            ['anggota_id' => $anggota->id]
        );

        return redirect()->route('anggota.index')
            ->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function show(Anggota $anggota)
    {
        return view('anggota.show', compact('anggota'));
    }

    public function edit(Anggota $anggota)
    {
        return view('anggota.edit', compact('anggota'));
    }

    public function update(Request $request, Anggota $anggota)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'telepon' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $anggota->update([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
            'email' => $request->email,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Anggota Diperbarui',
            "Data anggota {$anggota->nama} telah diperbarui.",
            'info',
            ['anggota_id' => $anggota->id]
        );

        return redirect()->route('anggota.index')
            ->with('success', 'Anggota berhasil diperbarui.');
    }

    public function destroy(Anggota $anggota)
    {
        $anggota->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Anggota Dihapus',
            "Anggota {$anggota->nama} telah dihapus.",
            'warning',
            ['anggota_id' => $anggota->id]
        );

        return redirect()->route('anggota.index')
            ->with('success', 'Anggota berhasil dihapus.');
    }

    public function search(Request $request)
    {
        $search = $request->search;
        $anggotas = Anggota::where('nama', 'like', "%{$search}%")
            ->orWhere('alamat', 'like', "%{$search}%")
            ->orWhere('telepon', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->get();

        return response()->json($anggotas);
    }
} 