<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        [$dari, $sampai] = $this->periode($request);

        // Laporan pendapatan: reservasi berstatus dikonfirmasi dengan tanggal main dalam periode.
        $reservasiPeriode = Reservasi::where('status', 'dikonfirmasi')
            ->whereHas('jadwal', fn ($q) => $q->whereBetween('tanggal', [$dari, $sampai]));

        $pendapatanPeriode = (int) (clone $reservasiPeriode)->sum('total_harga');
        $jumlahPeriode = (clone $reservasiPeriode)->count();

        $pendapatanTotal = (int) Reservasi::where('status', 'dikonfirmasi')->sum('total_harga');

        $ringkasan = [
            'reservasi' => Reservasi::count(),
            'dikonfirmasi' => Reservasi::where('status', 'dikonfirmasi')->count(),
            'menunggu' => Reservasi::whereIn('status', ['menunggu_pembayaran', 'menunggu_verifikasi'])->count(),
            'pelanggan' => User::where('role', 'pelanggan')->count(),
            'lapangan' => Lapangan::count(),
            'jadwal' => Jadwal::count(),
        ];

        // Laporan penggunaan lapangan per periode.
        $lapangans = Lapangan::orderBy('nama')->get();

        $jadwalPeriode = Jadwal::whereBetween('tanggal', [$dari, $sampai])
            ->selectRaw('lapangan_id, count(*) as jml')
            ->groupBy('lapangan_id')
            ->pluck('jml', 'lapangan_id');

        $terpakai = DB::table('reservasi')
            ->join('jadwal', 'jadwal.id', '=', 'reservasi.jadwal_id')
            ->where('reservasi.status', 'dikonfirmasi')
            ->whereBetween('jadwal.tanggal', [$dari, $sampai])
            ->selectRaw('jadwal.lapangan_id, count(*) as jml, coalesce(sum(reservasi.total_harga),0) as pendapatan')
            ->groupBy('jadwal.lapangan_id')
            ->get()
            ->keyBy('lapangan_id');

        $laporan = $lapangans->map(function ($lapangan) use ($jadwalPeriode, $terpakai) {
            $total = (int) ($jadwalPeriode[$lapangan->id] ?? 0);
            $dipakai = (int) ($terpakai[$lapangan->id]->jml ?? 0);

            return [
                'nama' => $lapangan->nama,
                'jadwal' => $total,
                'terpakai' => $dipakai,
                'okupansi' => $total > 0 ? (int) round($dipakai / $total * 100) : 0,
                'pendapatan' => (int) ($terpakai[$lapangan->id]->pendapatan ?? 0),
            ];
        });

        $terbaru = Reservasi::with(['pelanggan', 'jadwal.lapangan'])
            ->latest()
            ->limit(5)
            ->get();

        return view('pemilik.dashboard', compact(
            'dari', 'sampai', 'ringkasan', 'laporan', 'terbaru',
            'pendapatanPeriode', 'jumlahPeriode', 'pendapatanTotal'
        ));
    }

    /**
     * Baca periode laporan dari query string (default: bulan berjalan).
     *
     * @return array{0: string, 1: string}
     */
    private function periode(Request $request): array
    {
        $dari = $request->query('dari');
        $sampai = $request->query('sampai');

        $dari = is_string($dari) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari)
            ? $dari
            : now()->startOfMonth()->format('Y-m-d');

        $sampai = is_string($sampai) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)
            ? $sampai
            : now()->endOfMonth()->format('Y-m-d');

        return $dari <= $sampai ? [$dari, $sampai] : [$sampai, $dari];
    }
}
