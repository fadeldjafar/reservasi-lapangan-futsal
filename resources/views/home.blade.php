@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <div class="card hero">
        <h1>Sistem Informasi Reservasi Lapangan Futsal</h1>
        <p>Lihat jadwal, pesan lapangan, dan bayar secara online — mudah dan cepat.</p>

        <a class="btn btn-secondary" href="{{ route('lapangan.index') }}">Lihat Lapangan</a>

        @guest
            <a class="btn" href="{{ route('register') }}">Daftar Sekarang</a>
            <a class="btn btn-secondary" href="{{ route('login') }}">Sudah Punya Akun? Masuk</a>
        @else
            <a class="btn" href="{{ route('dashboard') }}">Masuk ke Dashboard</a>
        @endguest
    </div>

    <div class="card">
        <h1>Fitur</h1>
        <ul class="features">
            <li>Registrasi &amp; login akun pelanggan</li>
            <li>Lihat daftar lapangan dan jadwal yang tersedia</li>
            <li>Reservasi jadwal (anti double-booking)</li>
            <li>Upload bukti pembayaran (transfer manual)</li>
            <li>Admin/Petugas memverifikasi pembayaran &amp; reservasi</li>
            <li>Pemilik memantau laporan pendapatan</li>
        </ul>
    </div>
@endsection
