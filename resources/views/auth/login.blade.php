@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
    <div class="card auth-card">
        <div class="auth-ico">🔑</div>
        <h1>Masuk ke Akun Anda</h1>
        <p class="muted">Gunakan email terdaftar untuk mengakses sistem reservasi.</p>

        @if ($errors->any())
            <div class="errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>

            <p style="margin-top:22px;">
                <button class="btn" type="submit" style="width:100%;margin:0;">Masuk</button>
            </p>
        </form>

        <hr class="divider">

        <p class="muted">Belum punya akun? <a href="{{ route('register') }}">Daftar gratis</a></p>
        <p class="muted" style="margin-top:6px;">Ingin melihat jadwal dulu? <a href="{{ route('lapangan.index') }}">Lihat lapangan</a></p>
    </div>
@endsection
