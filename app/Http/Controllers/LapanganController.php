<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LapanganController extends Controller
{
    /**
     * Daftar lapangan yang aktif.
     */
    public function index(): View
    {
        $lapangans = Lapangan::where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        return view('lapangan.index', compact('lapangans'));
    }

    /**
     * Detail lapangan beserta jadwal dan ketersediaannya.
     */
    public function show(Lapangan $lapangan): View
    {
        abort_unless($lapangan->isAktif(), 404);

        // Tampilkan jadwal mulai hari ini, urut tanggal lalu jam mulai.
        $jadwals = $lapangan->jadwal()
            ->with('reservasi')
            ->whereDate('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        return view('lapangan.show', compact('lapangan', 'jadwals'));
    }
}
