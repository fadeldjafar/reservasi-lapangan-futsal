<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $ringkasan = [
            'lapangan' => Lapangan::count(),
            'jadwal' => Jadwal::count(),
            'pelanggan' => User::where('role', 'pelanggan')->count(),
            'reservasi' => Reservasi::count(),
            'menunggu_verifikasi' => Reservasi::where('status', 'menunggu_verifikasi')->count(),
            'dikonfirmasi' => Reservasi::where('status', 'dikonfirmasi')->count(),
            'pendapatan' => (int) Reservasi::where('status', 'dikonfirmasi')->sum('total_harga'),
        ];

        $perStatus = Reservasi::selectRaw('status, count(*) as jml')
            ->groupBy('status')
            ->pluck('jml', 'status');

        $antrean = Reservasi::with(['pelanggan', 'jadwal.lapangan', 'pembayaran'])
            ->where('status', 'menunggu_verifikasi')
            ->latest()
            ->get();

        return view('admin.dashboard', compact('ringkasan', 'perStatus', 'antrean'));
    }
}
