<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelangganController extends Controller
{
    public function index(): View
    {
        $pelanggans = User::where('role', 'pelanggan')
            ->withCount(['reservasi as jumlah_reservasi'])
            ->orderBy('name')
            ->get();

        return view('admin.pelanggan.index', compact('pelanggans'));
    }

    public function show(User $pelanggan): View
    {
        abort_unless($pelanggan->role === 'pelanggan', 404);

        $reservasis = Reservasi::where('user_id', $pelanggan->id)
            ->with(['jadwal.lapangan', 'pembayaran'])
            ->latest()
            ->get();

        return view('admin.pelanggan.show', compact('pelanggan', 'reservasis'));
    }

    public function destroy(User $pelanggan): RedirectResponse
    {
        abort_unless($pelanggan->role === 'pelanggan', 403);
        abort_unless($pelanggan->id !== auth()->id(), 403);

        $pelanggan->delete();

        return redirect('/admin/pelanggan')->with('success', 'Data pelanggan beserta riwayat reservasinya dihapus.');
    }
}
