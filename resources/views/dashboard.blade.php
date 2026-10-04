@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="card">
        <h1>Halo, {{ auth()->user()->name }} 👋</h1>
        <p>Anda masuk sebagai <span class="badge">{{ auth()->user()->role }}</span></p>
    </div>

    <div class="card">
        <h1>Menu Anda</h1>

        @if (auth()->user()->role === 'pelanggan')
            <p style="margin-bottom:12px;">
                <a class="btn" href="{{ route('lapangan.index') }}">Lihat Lapangan &amp; Jadwal</a>
                <a class="btn btn-secondary" href="{{ route('reservasi.index') }}">Reservasi Saya</a>
            </p>
            <ul class="features">
                <li>Lihat daftar lapangan</li>
                <li>Lihat jadwal &amp; ketersediaan</li>
                <li>Reservasi &amp; upload bukti pembayaran</li>
                <li>Lihat status &amp; riwayat reservasi</li>
            </ul>
            <p class="muted" style="margin-top:10px;">Fitur pembayaran &amp; verifikasi menyusul di Tahap 3–4.</p>
        @elseif (auth()->user()->role === 'admin')
            <p>Sebagai admin/petugas Anda dapat mengelola lapangan, jadwal, reservasi, dan memverifikasi pembayaran.</p>
            <p style="margin-top:12px;"><a class="btn" href="{{ route('admin.dashboard') }}">Buka Dashboard Admin</a></p>
        @else
            <p>Sebagai pemilik Anda dapat memantau reservasi serta laporan pendapatan dan penggunaan lapangan.</p>
            <p style="margin-top:12px;"><a class="btn" href="{{ route('pemilik.dashboard') }}">Buka Dashboard Pemilik</a></p>
        @endif
    </div>
@endsection
