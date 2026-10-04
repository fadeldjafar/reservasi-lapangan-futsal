@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
    <div class="card" style="max-width:480px;margin:0 auto;">
        <h1>Masuk</h1>

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
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <p style="margin-top:18px;">
                <button class="btn" type="submit">Masuk</button>
            </p>
        </form>

        <p class="muted" style="margin-top:12px;">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
    </div>
@endsection
