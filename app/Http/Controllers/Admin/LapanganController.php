<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lapangan;
use App\Models\Reservasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LapanganController extends Controller
{
    public function index(): View
    {
        $lapangans = Lapangan::withCount('jadwal')->orderBy('nama')->get();

        return view('admin.lapangan.index', compact('lapangans'));
    }

    public function create(): View
    {
        return view('admin.lapangan.form', ['lapangan' => new Lapangan()]);
    }

    /**
     * Pesan validasi Bahasa Indonesia untuk form lapangan.
     *
     * @return array<string, string>
     */
    private function pesan(): array
    {
        return [
            'nama.required' => 'Nama lapangan wajib diisi.',
            'nama.max' => 'Nama lapangan maksimal 255 karakter.',
            'deskripsi.string' => 'Deskripsi harus berupa teks.',
            'harga_per_jam.required' => 'Harga per jam wajib diisi.',
            'harga_per_jam.integer' => 'Harga per jam harus berupa angka.',
            'harga_per_jam.min' => 'Harga per jam tidak boleh minus.',
            'status.in' => 'Status harus aktif atau nonaktif.',
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'harga_per_jam' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ], $this->pesan());

        Lapangan::create($data);

        return redirect('/admin/lapangan')->with('success', 'Lapangan berhasil ditambahkan.');
    }

    public function edit(Lapangan $lapangan): View
    {
        return view('admin.lapangan.form', compact('lapangan'));
    }

    public function update(Request $request, Lapangan $lapangan): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'harga_per_jam' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ], $this->pesan());

        $lapangan->update($data);

        return redirect('/admin/lapangan')->with('success', 'Lapangan berhasil diperbarui.');
    }

    public function destroy(Lapangan $lapangan): RedirectResponse
    {
        // Jangan hapus lapangan yang sudah punya riwayat reservasi
        // (menghapus lapangan = menghapus jadwal = ikut menghapus reservasi).
        $adaRiwayat = Reservasi::whereIn(
            'jadwal_id',
            $lapangan->jadwal()->pluck('id')
        )->exists();

        if ($adaRiwayat) {
            return redirect('/admin/lapangan')
                ->withErrors(['lapangan' => 'Lapangan tidak bisa dihapus karena sudah ada riwayat reservasi. Nonaktifkan saja.']);
        }

        $lapangan->delete();

        return redirect('/admin/lapangan')->with('success', 'Lapangan berhasil dihapus.');
    }
}
