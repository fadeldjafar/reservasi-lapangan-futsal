<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Reservasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JadwalController extends Controller
{
    public function index(Request $request): View
    {
        $jadwals = Jadwal::with(['lapangan', 'reservasi'])
            ->when($request->filled('lapangan'), fn ($q) => $q->where('lapangan_id', $request->integer('lapangan')))
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        $lapangans = Lapangan::orderBy('nama')->get();

        return view('admin.jadwal.index', compact('jadwals', 'lapangans'));
    }

    public function create(): View
    {
        return view('admin.jadwal.form', [
            'jadwal' => new Jadwal(),
            'lapangans' => Lapangan::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateJadwal($request);

        Jadwal::create($data);

        return redirect('/admin/jadwal')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function edit(Jadwal $jadwal): View
    {
        return view('admin.jadwal.form', [
            'jadwal' => $jadwal,
            'lapangans' => Lapangan::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Jadwal $jadwal): RedirectResponse
    {
        if (Reservasi::where('jadwal_id', $jadwal->id)->exists()) {
            return redirect('/admin/jadwal')
                ->withErrors(['jadwal' => 'Jadwal yang sudah dipesan tidak bisa diubah.']);
        }

        $data = $this->validateJadwal($request, $jadwal->id);

        $jadwal->update($data);

        return redirect('/admin/jadwal')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal): RedirectResponse
    {
        if (Reservasi::where('jadwal_id', $jadwal->id)->exists()) {
            return redirect('/admin/jadwal')
                ->withErrors(['jadwal' => 'Jadwal yang sudah dipesan tidak bisa dihapus.']);
        }

        $jadwal->delete();

        return redirect('/admin/jadwal')->with('success', 'Jadwal berhasil dihapus.');
    }

    /**
     * Validasi bersama untuk create & update jadwal.
     *
     * @return array<string, mixed>
     */
    private function validateJadwal(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'lapangan_id' => ['required', 'integer', Rule::exists('lapangan', 'id')],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ], [
            'lapangan_id.required' => 'Lapangan wajib dipilih.',
            'lapangan_id.exists' => 'Lapangan tidak ditemukan.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Tanggal tidak valid.',
            'tanggal.after_or_equal' => 'Tanggal jadwal tidak boleh hari kemarin atau sebelumnya.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_mulai.date_format' => 'Format jam mulai tidak valid.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.date_format' => 'Format jam selesai tidak valid.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
        ]);

        // Satu slot (lapangan + tanggal + jam mulai) hanya boleh ada satu kali.
        $bentrok = DB::table('jadwal')
            ->where('lapangan_id', $data['lapangan_id'])
            ->where('tanggal', $data['tanggal'])
            ->where('jam_mulai', $data['jam_mulai'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($bentrok) {
            throw ValidationException::withMessages([
                'jam_mulai' => 'Jadwal untuk lapangan, tanggal, dan jam mulai tersebut sudah ada.',
            ]);
        }

        return $data;
    }
}
