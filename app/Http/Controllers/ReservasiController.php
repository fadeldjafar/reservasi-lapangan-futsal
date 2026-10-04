<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReservasiController extends Controller
{
    /**
     * Riwayat & status reservasi milik pelanggan yang login.
     */
    public function index(Request $request): View
    {
        $reservasis = Reservasi::where('user_id', $request->user()->id)
            ->with(['jadwal.lapangan', 'pembayaran'])
            ->latest()
            ->get();

        return view('reservasi.index', compact('reservasis'));
    }

    /**
     * Detail satu reservasi: jadwal, tagihan, dan pembayaran.
     */
    public function show(Request $request, Reservasi $reservasi): View
    {
        abort_unless($reservasi->user_id === $request->user()->id, 403);

        $reservasi->load(['jadwal.lapangan', 'pembayaran']);

        return view('reservasi.show', compact('reservasi'));
    }

    /**
     * Upload bukti pembayaran (transfer bank manual).
     */
    public function uploadBukti(Request $request, Reservasi $reservasi): RedirectResponse
    {
        abort_unless($reservasi->user_id === $request->user()->id, 403);

        $reservasi->load('pembayaran');
        $pembayaran = $reservasi->pembayaran;

        abort_unless($pembayaran !== null, 404);

        // Boleh upload kalau belum bayar, atau sebelumnya ditandai tidak_valid
        // (diminta mengunggah ulang oleh petugas).
        if (! in_array($pembayaran->status, ['menunggu_pembayaran', 'tidak_valid'], true)) {
            return back()->withErrors([
                'bukti' => 'Bukti pembayaran sudah diunggah dan sedang menunggu verifikasi petugas.',
            ]);
        }

        $request->validate([
            'bukti' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'bukti.required' => 'Bukti pembayaran wajib diunggah.',
            'bukti.file' => 'Bukti pembayaran harus berupa file.',
            'bukti.image' => 'Bukti pembayaran harus berupa gambar (JPG/PNG).',
            'bukti.mimes' => 'Format gambar harus JPG atau PNG.',
            'bukti.max' => 'Ukuran gambar maksimal 2 MB.',
        ]);

        // Ganti file lama kalau ada (misalnya unggah ulang setelah ditolak).
        if ($pembayaran->bukti_transfer && is_file(public_path($pembayaran->bukti_transfer))) {
            unlink(public_path($pembayaran->bukti_transfer));
        }

        $namaFile = 'bukti_'.$reservasi->id.'_'.now()->format('YmdHis').'_'.Str::random(6)
            .'.'.$request->file('bukti')->extension();
        $request->file('bukti')->move(public_path('uploads/bukti'), $namaFile);

        $pembayaran->update([
            'bukti_transfer' => 'uploads/bukti/'.$namaFile,
            'status' => 'menunggu_verifikasi',
        ]);

        // Status reservasi ikut naik: menunggu pembayaran -> menunggu verifikasi.
        $reservasi->update(['status' => 'menunggu_verifikasi']);

        return redirect('/reservasi/'.$reservasi->id)
            ->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi petugas.');
    }

    /**
     * Proses reservasi satu jadwal oleh pelanggan yang login.
     */
    public function store(Request $request, Lapangan $lapangan, Jadwal $jadwal): RedirectResponse
    {
        abort_unless($jadwal->lapangan_id === $lapangan->id, 404);

        // Validasi sederhana: jadwal lampau tidak bisa dipesan.
        if ($jadwal->tanggal->lt(today())) {
            return back()->withErrors(['jadwal' => 'Jadwal yang sudah lewat tidak bisa dipesan.']);
        }

        // Cek ketersediaan di aplikasi (jalan juga untuk kasus balapan/race condition
        // karena constraint UNIQUE di database tetap jadi penjaga terakhir).
        if (! $jadwal->tersedia) {
            return back()->withErrors(['jadwal' => 'Maaf, jadwal ini baru saja dipesan pelanggan lain.']);
        }

        $total = $lapangan->harga_per_jam * $jadwal->durasiJam();

        try {
            DB::transaction(function () use ($request, $lapangan, $jadwal, $total) {
                $reservasi = Reservasi::create([
                    'user_id' => $request->user()->id,
                    'jadwal_id' => $jadwal->id,
                    'total_harga' => $total,
                    'status' => 'menunggu_pembayaran',
                ]);

                // Satu reservasi selalu punya satu pembayaran (dibuat bersamaan,
                // diisi bukti transfer pada Tahap 3).
                Pembayaran::create([
                    'reservasi_id' => $reservasi->id,
                    'jumlah' => $total,
                    'status' => 'menunggu_pembayaran',
                ]);
            });
        } catch (QueryException $e) {
            // Constraint UNIQUE jadwal_id: pencegah double-booking terakhir.
            if ((string) $e->getCode() === '23000') {
                return back()->withErrors(['jadwal' => 'Maaf, jadwal ini sudah dipesan pelanggan lain.']);
            }

            throw $e;
        }

        return redirect('/reservasi')->with('success', 'Reservasi berhasil dibuat. Silakan lakukan pembayaran.');
    }
}
