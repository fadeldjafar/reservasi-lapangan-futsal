<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservasiController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $reservasis = Reservasi::with(['pelanggan', 'jadwal.lapangan', 'pembayaran'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        $statuses = [
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'dikonfirmasi' => 'Dikonfirmasi',
            'ditolak' => 'Ditolak',
            'dibatalkan' => 'Dibatalkan',
        ];

        return view('pemilik.reservasi', compact('reservasis', 'statuses', 'status'));
    }
}
