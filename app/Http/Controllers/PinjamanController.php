<?php

namespace App\Http\Controllers;

use App\Models\Pinjaman;
use App\Models\Anggota;
use Illuminate\Http\Request;
use App\Services\NotificationService;

class PinjamanController extends Controller
{
    public function index()
    {
        $pinjamans = Pinjaman::with('anggota')->latest()->paginate(10);
        return view('pinjaman.index', compact('pinjamans'));
    }

    public function create()
    {
        $anggotas = Anggota::all();
        return view('pinjaman.create', compact('anggotas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'anggota_id' => ['required', 'exists:anggotas,id'],
            'jumlah' => ['required', 'numeric', 'min:0'],
            'bunga' => ['required', 'numeric', 'min:0'],
            'lama' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'date'],
        ]);

        $pinjaman = Pinjaman::create([
            'anggota_id' => $request->anggota_id,
            'jumlah' => $request->jumlah,
            'bunga' => $request->bunga,
            'lama' => $request->lama,
            'tanggal' => $request->tanggal,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Pinjaman Baru',
            "Pinjaman baru telah ditambahkan.",
            'success',
            ['pinjaman_id' => $pinjaman->id]
        );

        return redirect()->route('pinjaman.index')
            ->with('success', 'Pinjaman berhasil ditambahkan.');
    }

    public function show(Pinjaman $pinjaman)
    {
        return view('pinjaman.show', compact('pinjaman'));
    }

    public function edit(Pinjaman $pinjaman)
    {
        $anggotas = Anggota::all();
        return view('pinjaman.edit', compact('pinjaman', 'anggotas'));
    }

    public function update(Request $request, Pinjaman $pinjaman)
    {
        $request->validate([
            'anggota_id' => ['required', 'exists:anggotas,id'],
            'jumlah' => ['required', 'numeric', 'min:0'],
            'bunga' => ['required', 'numeric', 'min:0'],
            'lama' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'date'],
        ]);

        $pinjaman->update([
            'anggota_id' => $request->anggota_id,
            'jumlah' => $request->jumlah,
            'bunga' => $request->bunga,
            'lama' => $request->lama,
            'tanggal' => $request->tanggal,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Pinjaman Diperbarui',
            "Pinjaman telah diperbarui.",
            'info',
            ['pinjaman_id' => $pinjaman->id]
        );

        return redirect()->route('pinjaman.index')
            ->with('success', 'Pinjaman berhasil diperbarui.');
    }

    public function destroy(Pinjaman $pinjaman)
    {
        $pinjaman->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Pinjaman Dihapus',
            "Pinjaman telah dihapus.",
            'warning',
            ['pinjaman_id' => $pinjaman->id]
        );

        return redirect()->route('pinjaman.index')
            ->with('success', 'Pinjaman berhasil dihapus.');
    }

    public function search(Request $request)
    {
        $search = $request->search;
        $pinjamans = Pinjaman::with('anggota')
            ->whereHas('anggota', function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%");
            })
            ->orWhere('jumlah', 'like', "%{$search}%")
            ->orWhere('bunga', 'like', "%{$search}%")
            ->orWhere('lama', 'like', "%{$search}%")
            ->orWhere('tanggal', 'like', "%{$search}%")
            ->get();

        return response()->json($pinjamans);
    }

    public function anggota(Anggota $anggota)
    {
        $pinjamans = $anggota->pinjamans()->latest()->paginate(10);
        return view('pinjaman.anggota', compact('anggota', 'pinjamans'));
    }
} 