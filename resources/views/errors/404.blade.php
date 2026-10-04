@extends('layouts.app')

@section('title', 'Tidak Ditemukan')

@section('content')
    <div class="card" style="text-align:center;padding:48px 24px;">
        <h1 style="font-size:44px;color:#b91c1c;margin-bottom:8px;">404</h1>
        <p style="font-size:18px;">Halaman atau data yang Anda cari tidak ditemukan.</p>
        <p style="margin-top:20px;">
            <a class="btn" href="{{ route('home') }}">Kembali ke Beranda</a>
            <a class="btn btn-secondary" href="{{ route('lapangan.index') }}">Lihat Lapangan</a>
        </p>
    </div>
@endsection
