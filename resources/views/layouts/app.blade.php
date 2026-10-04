<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Beranda') — Reservasi Lapangan Futsal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Segoe UI, Arial, sans-serif; background: #f4f6f8; color: #1f2937; }
        nav { background: #166534; color: #fff; padding: 12px 24px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
        nav .brand { font-weight: 700; text-decoration: none; color: #fff; }
        nav .spacer { flex: 1; }
        nav a { color: #e5f3ea; text-decoration: none; }
        nav a:hover { text-decoration: underline; }
        .container { max-width: 960px; margin: 32px auto; padding: 0 16px; }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 24px; margin-bottom: 20px; }
        .card h1 { font-size: 24px; margin-bottom: 12px; }
        .hero { text-align: center; padding: 48px 24px; }
        .hero h1 { font-size: 30px; margin-bottom: 12px; }
        .hero p { color: #4b5563; margin-bottom: 24px; }
        .btn { display: inline-block; background: #166534; color: #fff; border: none; padding: 10px 18px; border-radius: 8px; font-size: 15px; cursor: pointer; text-decoration: none; margin: 4px; }
        .btn:hover { background: #14532d; }
        .btn-secondary { background: #64748b; }
        .btn-secondary:hover { background: #475569; }
        label { display: block; font-weight: 600; margin: 14px 0 6px; }
        input[type=text], input[type=email], input[type=password], input[type=tel] {
            width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px;
        }
        .errors { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 8px; padding: 12px 16px; margin: 12px 0; }
        .errors ul { margin-left: 18px; }
        .muted { color: #6b7280; font-size: 14px; }
        .badge { display: inline-block; background: #fef3c7; color: #92400e; border-radius: 999px; padding: 2px 10px; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #e5e7eb; }
        ul.features { margin: 12px 0 0 20px; line-height: 1.9; }
    </style>
</head>
<body>
    <nav>
        <a class="brand" href="{{ route('home') }}">{{ config('app.name') }}</a>
        <a href="{{ route('lapangan.index') }}">Lapangan</a>
        <div class="spacer"></div>

        @guest
            <a href="{{ route('login') }}">Masuk</a>
            <a href="{{ route('register') }}">Daftar</a>
        @else
            <a href="{{ route('reservasi.index') }}">Reservasi Saya</a>
            @if (auth()->user()->role === 'pemilik')
                <a href="{{ route('pemilik.dashboard') }}">Dashboard Pemilik</a>
            @endif
            <a href="{{ route('dashboard') }}">Beranda</a>
            <span>{{ auth()->user()->name }} <span class="badge">{{ auth()->user()->role }}</span></span>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button class="btn btn-secondary" style="padding:6px 12px;">Keluar</button>
            </form>
        @endguest
    </nav>

    @if (auth()->check() && auth()->user()->role === 'admin')
        <nav style="background:#14532d;padding:8px 24px;display:flex;gap:16px;flex-wrap:wrap;font-size:14px;">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.lapangan.index') }}">Lapangan</a>
            <a href="{{ route('admin.jadwal.index') }}">Jadwal</a>
            <a href="{{ route('admin.pelanggan.index') }}">Pelanggan</a>
            <a href="{{ route('admin.reservasi.index') }}">Reservasi</a>
        </nav>
    @endif

    @if (auth()->check() && auth()->user()->role === 'pemilik')
        <nav style="background:#14532d;padding:8px 24px;display:flex;gap:16px;flex-wrap:wrap;font-size:14px;">
            <a href="{{ route('pemilik.dashboard') }}">Dashboard</a>
            <a href="{{ route('pemilik.reservasi') }}">Data Reservasi</a>
        </nav>
    @endif

    <div class="container">
        @if (session('success'))
            <div class="card" style="background:#f0fdf4;border-color:#bbf7d0;color:#166534;">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
