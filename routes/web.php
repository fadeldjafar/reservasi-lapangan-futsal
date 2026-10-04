<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LapanganController;
use App\Http\Controllers\Pemilik;
use App\Http\Controllers\ReservasiController;
use Illuminate\Support\Facades\Route;

// Halaman publik
Route::get('/', function () {
    return view('home');
})->name('home');

// Lihat lapangan & jadwal (terbuka untuk semua orang)
Route::get('/lapangan', [LapanganController::class, 'index'])->name('lapangan.index');
Route::get('/lapangan/{lapangan}', [LapanganController::class, 'show'])->name('lapangan.show');

// Tamu (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'create'])->name('register');
    Route::post('/register', [AuthController::class, 'store']);
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate']);
});

// Sudah login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Reservasi (khusus pelanggan yang login)
    Route::get('/reservasi', [ReservasiController::class, 'index'])->name('reservasi.index');
    Route::get('/reservasi/{reservasi}', [ReservasiController::class, 'show'])->name('reservasi.show');
    Route::post('/lapangan/{lapangan}/jadwal/{jadwal}/reservasi', [ReservasiController::class, 'store'])->name('reservasi.store');
    Route::post('/reservasi/{reservasi}/pembayaran', [ReservasiController::class, 'uploadBukti'])->name('pembayaran.upload');
});

// Khusus admin/petugas
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Kelola lapangan
    Route::get('/lapangan', [Admin\LapanganController::class, 'index'])->name('lapangan.index');
    Route::get('/lapangan/create', [Admin\LapanganController::class, 'create'])->name('lapangan.create');
    Route::post('/lapangan', [Admin\LapanganController::class, 'store'])->name('lapangan.store');
    Route::get('/lapangan/{lapangan}/edit', [Admin\LapanganController::class, 'edit'])->name('lapangan.edit');
    Route::put('/lapangan/{lapangan}', [Admin\LapanganController::class, 'update'])->name('lapangan.update');
    Route::delete('/lapangan/{lapangan}', [Admin\LapanganController::class, 'destroy'])->name('lapangan.destroy');

    // Kelola jadwal
    Route::get('/jadwal', [Admin\JadwalController::class, 'index'])->name('jadwal.index');
    Route::get('/jadwal/create', [Admin\JadwalController::class, 'create'])->name('jadwal.create');
    Route::post('/jadwal', [Admin\JadwalController::class, 'store'])->name('jadwal.store');
    Route::get('/jadwal/{jadwal}/edit', [Admin\JadwalController::class, 'edit'])->name('jadwal.edit');
    Route::put('/jadwal/{jadwal}', [Admin\JadwalController::class, 'update'])->name('jadwal.update');
    Route::delete('/jadwal/{jadwal}', [Admin\JadwalController::class, 'destroy'])->name('jadwal.destroy');

    // Data pelanggan
    Route::get('/pelanggan', [Admin\PelangganController::class, 'index'])->name('pelanggan.index');
    Route::get('/pelanggan/{pelanggan}', [Admin\PelangganController::class, 'show'])->name('pelanggan.show');
    Route::delete('/pelanggan/{pelanggan}', [Admin\PelangganController::class, 'destroy'])->name('pelanggan.destroy');

    // Data reservasi, bukti pembayaran & verifikasi
    Route::get('/reservasi', [Admin\ReservasiController::class, 'index'])->name('reservasi.index');
    Route::get('/reservasi/{reservasi}', [Admin\ReservasiController::class, 'show'])->name('reservasi.show');
    Route::post('/reservasi/{reservasi}/verifikasi', [Admin\ReservasiController::class, 'verifikasi'])->name('reservasi.verifikasi');
    Route::post('/reservasi/{reservasi}/status', [Admin\ReservasiController::class, 'updateStatus'])->name('reservasi.status');
});

// Khusus pemilik
Route::middleware(['auth', 'role:pemilik'])->prefix('pemilik')->name('pemilik.')->group(function () {
    Route::get('/dashboard', [Pemilik\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reservasi', [Pemilik\ReservasiController::class, 'index'])->name('reservasi');
});
