<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use Illuminate\Http\RedirectResponse;
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

        return view('admin.reservasi.index', compact('reservasis', 'statuses', 'status'));
    }

    public function show(Reservasi $reservasi): View
    {
        $reservasi->load(['pelanggan', 'jadwal.lapangan', 'pembayaran']);

        return view('admin.reservasi.show', compact('reservasi'));
    }

    /**
     * Verifikasi bukti pembayaran: valid atau tidak valid.
     */
    public function verifikasi(Request $request, Reservasi $reservasi): RedirectResponse
    {
        $data = $request->validate([
            'keputusan' => ['required', 'in:valid,tidak_valid'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ], [
            'keputusan.in' => 'Pilihan verifikasi tidak dikenal.',
        ]);

        // Field nullable yang tidak dikirim tidak muncul di $data hasil validasi.
        $catatan = $data['catatan'] ?? null;

        if ($data['keputusan'] === 'tidak_valid' && trim((string) $catatan) === '') {
            return back()->withErrors(['catatan' => 'Wajib mengisi catatan bila bukti pembayaran tidak valid.']);
        }

        $pembayaran = $reservasi->pembayaran;
        abort_unless($pembayaran !== null, 404);

        if ($pembayaran->status !== 'menunggu_verifikasi') {
            return back()->withErrors(['keputusan' => 'Reservasi ini tidak sedang menunggu verifikasi pembayaran.']);
        }

        $pembayaran->update([
            'status' => $data['keputusan'],
            'catatan' => $catatan ?: null,
            'diverifikasi_oleh' => $request->user()->id,
            'diverifikasi_pada' => now(),
        ]);

        if ($data['keputusan'] === 'valid') {
            // Pembayaran valid -> reservasi dikonfirmasi.
            $reservasi->update(['status' => 'dikonfirmasi']);

            return back()->with('success', 'Pembayaran diverifikasi (valid). Reservasi dikonfirmasi.');
        }

        // Pembayaran tidak valid -> pelanggan diminta mengunggah bukti kembali.
        $reservasi->update(['status' => 'menunggu_pembayaran']);

        return back()->with('success', 'Pembayaran ditandai tidak valid. Pelanggan diminta mengunggah ulang bukti.');
    }

    /**
     * Ubah status reservasi secara manual oleh admin/petugas.
     */
    public function updateStatus(Request $request, Reservasi $reservasi): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:dikonfirmasi,menunggu_verifikasi,menunggu_pembayaran,ditolak,dibatalkan'],
        ]);

        if ($data['status'] === $reservasi->status) {
            return back()->withErrors(['status' => 'Status reservasi sama seperti sebelumnya.']);
        }

        $reservasi->update(['status' => $data['status']]);

        // Reservasi batal/ditolak: lepas jadwalnya supaya slot bisa dipesan lagi
        // (kolom jadwal_id nullable, jadi unique tidak menahan slot).
        if (in_array($data['status'], ['dibatalkan', 'ditolak'], true) && $reservasi->jadwal_id !== null) {
            $reservasi->update(['jadwal_id' => null]);
        }

        return back()->with('success', 'Status reservasi diubah menjadi "'.ucfirst(str_replace('_', ' ', $data['status'])).'".');
    }
}
