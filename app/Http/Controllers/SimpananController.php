<?php

namespace App\Http\Controllers;

use App\Models\Simpanan;
use App\Models\Anggota;
use Illuminate\Http\Request;
use App\Services\NotificationService;

class SimpananController extends Controller
{
    public function index()
    {
        $simpanans = Simpanan::with('anggota')->latest()->paginate(10);
        return view('simpanan.index', compact('simpanans'));
    }

    public function create()
    {
        $anggotas = Anggota::all();
        return view('simpanan.create', compact('anggotas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'anggota_id' => ['required', 'exists:anggotas,id'],
            'jenis' => ['required', 'string', 'in:pokok,wajib,sukarela'],
            'jumlah' => ['required', 'numeric', 'min:0'],
            'tanggal' => ['required', 'date'],
        ]);

        $simpanan = Simpanan::create([
            'anggota_id' => $request->anggota_id,
            'jenis' => $request->jenis,
            'jumlah' => $request->jumlah,
            'tanggal' => $request->tanggal,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Simpanan Baru',
            "Simpanan baru telah ditambahkan.",
            'success',
            ['simpanan_id' => $simpanan->id]
        );

        return redirect()->route('simpanan.index')
            ->with('success', 'Simpanan berhasil ditambahkan.');
    }

    public function show(Simpanan $simpanan)
    {
        return view('simpanan.show', compact('simpanan'));
    }

    public function edit(Simpanan $simpanan)
    {
        $anggotas = Anggota::all();
        return view('simpanan.edit', compact('simpanan', 'anggotas'));
    }

    public function update(Request $request, Simpanan $simpanan)
    {
        $request->validate([
            'anggota_id' => ['required', 'exists:anggotas,id'],
            'jenis' => ['required', 'string', 'in:pokok,wajib,sukarela'],
            'jumlah' => ['required', 'numeric', 'min:0'],
            'tanggal' => ['required', 'date'],
        ]);

        $simpanan->update([
            'anggota_id' => $request->anggota_id,
            'jenis' => $request->jenis,
            'jumlah' => $request->jumlah,
            'tanggal' => $request->tanggal,
        ]);

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Simpanan Diperbarui',
            "Simpanan telah diperbarui.",
            'info',
            ['simpanan_id' => $simpanan->id]
        );

        return redirect()->route('simpanan.index')
            ->with('success', 'Simpanan berhasil diperbarui.');
    }

    public function destroy(Simpanan $simpanan)
    {
        $simpanan->delete();

        // Create notification
        $notificationService = app(NotificationService::class);
        $notificationService->create(
            'Simpanan Dihapus',
            "Simpanan telah dihapus.",
            'warning',
            ['simpanan_id' => $simpanan->id]
        );

        return redirect()->route('simpanan.index')
            ->with('success', 'Simpanan berhasil dihapus.');
    }

    public function search(Request $request)
    {
        $search = $request->search;
        $simpanans = Simpanan::with('anggota')
            ->whereHas('anggota', function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%");
            })
            ->orWhere('jenis', 'like', "%{$search}%")
            ->orWhere('jumlah', 'like', "%{$search}%")
            ->orWhere('tanggal', 'like', "%{$search}%")
            ->get();

        return response()->json($simpanans);
    }

    public function anggota(Anggota $anggota)
    {
        $simpanans = $anggota->simpanans()->latest()->paginate(10);
        return view('simpanan.anggota', compact('anggota', 'simpanans'));
    }
} 