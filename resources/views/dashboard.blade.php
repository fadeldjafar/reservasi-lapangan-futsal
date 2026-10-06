@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $user = auth()->user();
        $peran = match ($user->role) {
            'admin' => 'Admin / Petugas',
            'pemilik' => 'Pemilik',
            default => 'Pelanggan',
        };
        $badgeKelas = match ($user->role) {
            'admin' => 'badge-info',
            'pemilik' => 'badge-warning',
            default => 'badge-success',
        };
    @endphp

    <div class="card">
        <div class="section-head">
            <div>
                <h1>Halo, {{ $user->name }} 👋</h1>
                <p class="muted">Selamat datang kembali di sistem reservasi lapangan futsal.</p>
            </div>
            <span class="badge {{ $badgeKelas }}">{{ $peran }}</span>
        </div>
    </div>

    @if ($user->role === 'pelanggan')
        <div class="grid grid-cards">
            <a class="card" href="{{ route('lapangan.index') }}" style="text-decoration:none;color:inherit;">
                <span class="feature-ico">🏟️</span>
                <h2>Pesan Lapangan</h2>
                <p class="muted">Lihat daftar lapangan, jadwal kosong, dan buat reservasi.</p>
            </a>
            <a class="card" href="{{ route('reservasi.index') }}" style="text-decoration:none;color:inherit;">
                <span class="feature-ico">🧾</span>
                <h2>Reservasi Saya</h2>
                <p class="muted">Status &amp; riwayat reservasi, unggah bukti pembayaran.</p>
            </a>
        </div>

        <div class="card">
            <h2>Alur Singkat</h2>
            <ul class="features">
                <li>Pilih lapangan → pilih jadwal yang tersedia → reservasi.</li>
                <li>Transfer sesuai total tagihan, lalu unggah bukti pembayaran.</li>
                <li>Petugas memverifikasi bukti → reservasi dikonfirmasi.</li>
            </ul>
        </div>
    @elseif ($user->role === 'admin')
        <div class="grid grid-cards">
            <a class="card" href="{{ route('admin.reservasi.index') }}" style="text-decoration:none;color:inherit;">
                <span class="feature-ico">✅</span>
                <h2>Reservasi &amp; Verifikasi</h2>
                <p class="muted">Verifikasi bukti pembayaran dan tentukan status reservasi.</p>
            </a>
            <a class="card" href="{{ route('admin.lapangan.index') }}" style="text-decoration:none;color:inherit;">
                <span class="feature-ico">🏟️</span>
                <h2>Kelola Lapangan</h2>
                <p class="muted">Tambah, ubah, atau nonaktifkan data lapangan.</p>
            </a>
            <a class="card" href="{{ route('admin.jadwal.index') }}" style="text-decoration:none;color:inherit;">
                <span class="feature-ico">📅</span>
                <h2>Kelola Jadwal</h2>
                <p class="muted">Atur jadwal buka tutup slot per lapangan.</p>
            </a>
            <a class="card" href="{{ route('admin.pelanggan.index') }}" style="text-decoration:none;color:inherit;">
                <span class="feature-ico">👥</span>
                <h2>Data Pelanggan</h2>
                <p class="muted">Lihat profil dan riwayat reservasi pelanggan.</p>
            </a>
        </div>

        <div class="card">
            <p class="muted">Catatan: pembuatan reservasi &amp; pembayaran hanya untuk akun pelanggan —
                admin bertugas mengelola data dan memverifikasi pembayaran.</p>
        </div>
    @else
        <div class="grid grid-cards">
            <a class="card" href="{{ route('pemilik.dashboard') }}" style="text-decoration:none;color:inherit;">
                <span class="feature-ico">📊</span>
                <h2>Laporan Pendapatan</h2>
                <p class="muted">Pendapatan per periode dan penggunaan/okupansi lapangan.</p>
            </a>
            <a class="card" href="{{ route('pemilik.reservasi') }}" style="text-decoration:none;color:inherit;">
                <span class="feature-ico">🗂️</span>
                <h2>Monitoring Reservasi</h2>
                <p class="muted">Pantau seluruh reservasi beserta statusnya.</p>
            </a>
        </div>

        <div class="card">
            <p class="muted">Sebagai pemilik Anda memantau laporan — pembuatan reservasi dan verifikasi
                pembayaran dijalankan pelanggan dan petugas.</p>
        </div>
    @endif
@endsection
