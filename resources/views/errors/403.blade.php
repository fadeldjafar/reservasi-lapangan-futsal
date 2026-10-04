@extends('layouts.app')

@section('title', 'Akses Ditolak')

@section('content')
    <div class="card" style="text-align:center;padding:48px 24px;">
        <h1 style="font-size:44px;color:#b91c1c;margin-bottom:8px;">403</h1>
        <p style="font-size:18px;">{{ $exception->getMessage() ?: 'Maaf, Anda tidak punya akses ke halaman ini.' }}</p>
        <p class="muted" style="margin-top:8px;">Silakan masuk dengan akun yang berwenang.</p>
        <p style="margin-top:20px;">
            <a class="btn" href="{{ route('home') }}">Kembali ke Beranda</a>
            @guest
                <a class="btn btn-secondary" href="{{ route('login') }}">Masuk</a>
            @endguest
        </p>
    </div>
@endsection
