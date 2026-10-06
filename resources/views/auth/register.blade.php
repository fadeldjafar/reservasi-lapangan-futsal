@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
    <div class="card auth-card">
        <div class="auth-ico">📝</div>
        <h1>Registrasi Akun Pelanggan</h1>
        <p class="muted">Akun admin &amp; pemilik dibuat oleh sistem.</p>

        @if ($errors->any())
            <div class="errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <label for="name">Nama Lengkap</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Nama Anda" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required>

            <label for="telepon">No. Telepon (opsional)</label>
            <input type="tel" id="telepon" name="telepon" value="{{ old('telepon') }}" placeholder="08xxxxxxxxxx">

            <label for="password">Password (min. 8 karakter)</label>
            <input type="password" id="password" name="password" required>

            <label for="password_confirmation">Ulangi Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>

            <p style="margin-top:22px;">
                <button class="btn" type="submit" style="width:100%;margin:0;">Daftar Sekarang</button>
            </p>
        </form>

        <hr class="divider">

        <p class="muted">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    </div>
@endsection
