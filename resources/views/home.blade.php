@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <div class="card hero">
        <h1>Sistem Informasi Reservasi Lapangan Futsal</h1>
        <p>Lihat jadwal real-time, pesan lapangan favorit Anda, dan bayar dengan transfer manual — cepat, mudah, tanpa antre.</p>

        <a class="btn" href="{{ route('lapangan.index') }}">🏟️ Lihat Lapangan</a>

        @guest
            <a class="btn" href="{{ route('register') }}">Daftar Sekarang</a>
            <a class="btn btn-secondary" href="{{ route('login') }}">Sudah Punya Akun? Masuk</a>
        @else
            <a class="btn" href="{{ route('dashboard') }}">Masuk ke Dashboard</a>
        @endguest
    </div>

    <div class="grid grid-cards">
        <div class="card">
            <span class="feature-ico">📅</span>
            <h2>Jadwal Real-Time</h2>
            <p class="muted">Lihat ketersediaan lapangan per jam, lengkap dengan tanggal dan durasi bermain.</p>
        </div>
        <div class="card">
            <span class="feature-ico">🔒</span>
            <h2>Anti Double-Booking</h2>
            <p class="muted">Satu slot hanya bisa dipesan satu pelanggan — dijaga sampai level database.</p>
        </div>
        <div class="card">
            <span class="feature-ico">🧾</span>
            <h2>Bayar &amp; Unggah Bukti</h2>
            <p class="muted">Transfer manual lalu unggah bukti pembayaran (JPG/PNG) langsung dari aplikasi.</p>
        </div>
        <div class="card">
            <span class="feature-ico">✅</span>
            <h2>Verifikasi Petugas</h2>
            <p class="muted">Admin memverifikasi pembayaran dan menentukan status reservasi Anda.</p>
        </div>
        <div class="card">
            <span class="feature-ico">📊</span>
            <h2>Laporan Pemilik</h2>
            <p class="muted">Pantau pendapatan dan okupansi penggunaan lapangan kapan saja.</p>
        </div>
        <div class="card">
            <span class="feature-ico">👤</span>
            <h2>Akun Pelanggan</h2>
            <p class="muted">Registrasi gratis, login aman, dan riwayat reservasi tersimpan rapi.</p>
        </div>
    </div>
@endsection
