<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Beranda') — {{ config('app.name') }}</title>
    <style>
        :root {
            --green-950: #052e16;
            --green-900: #0a3d1f;
            --green-800: #14532d;
            --green-700: #166534;
            --green-600: #15803d;
            --green-500: #22c55e;
            --lime: #a3e635;
            --bg: #f1f5f9;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text: #0f172a;
            --text-soft: #334155;
            --muted: #64748b;
            --danger: #dc2626;
            --warning: #d97706;
            --info: #2563eb;
            --radius: 14px;
            --shadow: 0 1px 2px rgba(15, 23, 42, .06), 0 6px 18px rgba(15, 23, 42, .05);
            --sidebar-w: 252px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 15px;
            line-height: 1.55;
        }

        a { color: var(--green-700); }

        /* ================= Navbar tamu ================= */
        .navbar {
            background: linear-gradient(135deg, var(--green-900), var(--green-700));
            color: #fff;
            padding: 0 24px;
            min-height: 60px;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            box-shadow: 0 2px 10px rgba(5, 46, 22, .25);
            position: sticky;
            top: 0;
            z-index: 20;
        }
        .navbar .brand { color: #fff; }
        .navbar .spacer { flex: 1; }
        .navbar a:not(.brand) {
            color: #dcfce7;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 4px;
            border-bottom: 2px solid transparent;
        }
        .navbar a:not(.brand):hover { color: #fff; border-bottom-color: var(--lime); }
        .navbar .nav-cta {
            background: var(--lime);
            color: var(--green-950) !important;
            padding: 8px 16px !important;
            border-radius: 8px;
            border-bottom: none !important;
            font-weight: 700;
        }
        .navbar .nav-cta:hover { filter: brightness(1.05); }
        .navbar .nav-outline {
            border: 1px solid rgba(255, 255, 255, .5) !important;
            padding: 8px 16px !important;
            border-radius: 8px;
        }

        .brand {
            font-weight: 800;
            font-size: 17px;
            text-decoration: none;
            letter-spacing: .2px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .brand-icon {
            background: rgba(255, 255, 255, .15);
            border-radius: 8px;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        /* ================= Layout sidebar ================= */
        .app { display: flex; min-height: 100vh; }

        .sidebar {
            width: var(--sidebar-w);
            flex-shrink: 0;
            background: linear-gradient(180deg, var(--green-900) 0%, var(--green-950) 100%);
            color: #e8f5ec;
            display: flex;
            flex-direction: column;
            padding: 18px 14px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar .brand { color: #fff; padding: 4px 8px 18px; }

        .side-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #86a992;
            padding: 10px 10px 6px;
            font-weight: 700;
        }
        .side-nav { display: flex; flex-direction: column; gap: 4px; }
        .side-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #d3e8da;
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 14.5px;
            transition: background .15s, color .15s;
        }
        .side-nav a:hover { background: rgba(255, 255, 255, .08); color: #fff; }
        .side-nav a.active {
            background: linear-gradient(90deg, rgba(163, 230, 53, .22), rgba(163, 230, 53, .06));
            color: #fff;
            box-shadow: inset 3px 0 0 var(--lime);
        }
        .side-nav .nav-icon { width: 20px; text-align: center; }

        .side-foot { margin-top: auto; padding-top: 16px; border-top: 1px solid rgba(255, 255, 255, .12); }
        .user-chip { display: flex; align-items: center; gap: 10px; padding: 6px 8px 12px; }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--lime); color: var(--green-950);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 15px; flex-shrink: 0;
        }
        .user-meta { min-width: 0; }
        .user-meta .uname { font-weight: 600; font-size: 14px; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-meta .urole { font-size: 12px; color: #9dc4a9; text-transform: capitalize; }

        .main { flex: 1; min-width: 0; display: flex; flex-direction: column; }

        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 12px 28px;
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }
        .topbar .spacer { flex: 1; }
        .topbar-link {
            color: var(--muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        .topbar-link:hover { color: var(--green-700); }

        .content {
            padding: 28px;
            max-width: 1140px;
            width: 100%;
            margin: 0 auto;
            flex: 1;
        }
        .content-public { max-width: 1040px; padding: 36px 20px 56px; }

        .foot {
            text-align: center;
            color: var(--muted);
            font-size: 13px;
            padding: 18px;
            border-top: 1px solid var(--border);
        }

        /* ================= Kartu ================= */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 24px;
            margin-bottom: 20px;
        }
        .card h1 { font-size: 21px; font-weight: 700; margin-bottom: 10px; color: var(--text); }
        .card h2 { font-size: 17px; font-weight: 700; margin-bottom: 8px; }

        .grid { display: grid; gap: 16px; }
        .grid-cards { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
        .grid-stats { grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); }

        /* Statistik / angka besar di dalam card */
        .stat-number { font-size: 26px; font-weight: 800; color: var(--green-700); line-height: 1.2; }

        /* ================= Tombol ================= */
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, var(--green-600), var(--green-700));
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 9px;
            font-size: 14.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            margin: 4px 4px 4px 0;
            transition: filter .15s, transform .05s;
            box-shadow: 0 1px 2px rgba(21, 128, 61, .3);
        }
        .btn:hover { filter: brightness(1.08); }
        .btn:active { transform: translateY(1px); }
        .btn-secondary { background: #e2e8f0; color: var(--text-soft); box-shadow: none; }
        .btn-secondary:hover { background: #cbd5e1; filter: none; }
        .btn-danger { background: var(--danger); box-shadow: 0 1px 2px rgba(220, 38, 38, .3); }
        .btn-sm { padding: 6px 12px; font-size: 13.5px; border-radius: 8px; }

        /* ================= Form ================= */
        label { display: block; font-weight: 600; margin: 14px 0 6px; font-size: 14px; color: var(--text-soft); }
        input[type=text], input[type=email], input[type=password], input[type=tel],
        input[type=date], input[type=number], input[type=file], select, textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            font-size: 15px;
            font-family: inherit;
            background: #fff;
            color: var(--text);
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--green-600);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .15);
        }

        .errors {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            border-radius: 10px;
            padding: 12px 16px;
            margin: 12px 0;
            font-size: 14.5px;
        }
        .errors ul { margin-left: 18px; }

        .alert {
            border-radius: 10px;
            padding: 13px 18px;
            margin-bottom: 20px;
            font-weight: 500;
            font-size: 14.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
        .alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
        .alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }

        .muted { color: var(--muted); font-size: 14px; }

        /* ================= Badge ================= */
        .badge {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            border-radius: 999px;
            padding: 3px 11px;
            font-size: 12.5px;
            font-weight: 700;
            line-height: 1.5;
            white-space: nowrap;
        }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-info { background: #dbeafe; color: #1d4ed8; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .badge-muted { background: #e2e8f0; color: #475569; }

        /* ================= Tabel ================= */
        .table-wrap { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14.5px;
        }
        thead th {
            text-align: left;
            padding: 11px 12px;
            background: #f8fafc;
            color: #475569;
            font-size: 12.5px;
            text-transform: uppercase;
            letter-spacing: .6px;
            border-bottom: 2px solid var(--border);
            white-space: nowrap;
        }
        tbody td { padding: 11px 12px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #f8fafc; }
        table th:first-child, table td:first-child { padding-left: 4px; }

        /* ================= Halaman publik / hero ================= */
        .hero {
            text-align: center;
            padding: 64px 28px;
            background:
                radial-gradient(1000px 300px at 20% -10%, rgba(163, 230, 53, .18), transparent),
                linear-gradient(135deg, var(--green-900), var(--green-700));
            color: #fff;
            border: none;
            box-shadow: 0 18px 40px rgba(5, 46, 22, .25);
        }
        .hero h1 { color: #fff; font-size: 32px; line-height: 1.25; margin-bottom: 12px; }
        .hero p { color: #d1fae5; max-width: 620px; margin: 0 auto 26px; font-size: 16.5px; }
        .hero .btn { margin: 6px; }
        .hero .btn-secondary { background: rgba(255, 255, 255, .14); color: #fff; border: 1px solid rgba(255, 255, 255, .45); }
        .hero .btn-secondary:hover { background: rgba(255, 255, 255, .24); }

        ul.features { margin: 12px 0 0 20px; line-height: 2; color: var(--text-soft); }

        .feature-ico {
            font-size: 26px;
            display: inline-flex;
            width: 48px; height: 48px;
            align-items: center; justify-content: center;
            background: #f0fdf4;
            border-radius: 12px;
            margin-bottom: 12px;
        }

        .section-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 4px;
        }

        .price { color: var(--green-700); font-weight: 800; font-size: 18px; }

        .auth-card {
            max-width: 440px;
            margin: 40px auto;
            padding: 34px;
        }
        .auth-card .auth-ico {
            width: 54px; height: 54px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--green-600), var(--green-800));
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 26px;
            margin-bottom: 16px;
            box-shadow: 0 8px 18px rgba(21, 128, 61, .3);
        }

        .divider { border: none; border-top: 1px solid var(--border); margin: 18px 0; }

        .empty {
            text-align: center;
            color: var(--muted);
            padding: 30px 16px;
        }
        .empty a { font-weight: 600; }

        .kvs th { width: 200px; background: transparent; border-bottom: 1px solid var(--border); text-transform: none; letter-spacing: normal; font-size: 14.5px; color: var(--text-soft); padding-left: 0; }
        .kvs td { border-bottom: 1px solid var(--border); }

        .img-preview { max-width: 340px; width: 100%; border: 1px solid var(--border); border-radius: 10px; display: block; }

        @media (max-width: 900px) {
            .app { flex-direction: column; }
            .sidebar { width: 100%; height: auto; position: static; padding: 14px; }
            .side-nav { flex-direction: row; flex-wrap: wrap; }
            .side-foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
            .user-chip { padding: 6px 0; }
            .content { padding: 18px 14px; }
        }
    </style>
</head>
<body>
@auth
    <div class="app">
        <aside class="sidebar">
            <a class="brand" href="{{ route('home') }}"><span class="brand-icon">⚽</span> {{ config('app.name') }}</a>

            <div class="side-label">Menu</div>
            <nav class="side-nav">
                @if (auth()->user()->role === 'pelanggan')
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">🏠</span> Beranda
                    </a>
                    <a href="{{ route('lapangan.index') }}" class="{{ request()->routeIs('lapangan.*') ? 'active' : '' }}">
                        <span class="nav-icon">🏟️</span> Lapangan &amp; Jadwal
                    </a>
                    <a href="{{ route('reservasi.index') }}" class="{{ request()->routeIs('reservasi.*') ? 'active' : '' }}">
                        <span class="nav-icon">🧾</span> Reservasi Saya
                    </a>
                @elseif (auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">🏠</span> Dashboard
                    </a>
                    <a href="{{ route('admin.lapangan.index') }}" class="{{ request()->routeIs('admin.lapangan.*') ? 'active' : '' }}">
                        <span class="nav-icon">🏟️</span> Kelola Lapangan
                    </a>
                    <a href="{{ route('admin.jadwal.index') }}" class="{{ request()->routeIs('admin.jadwal.*') ? 'active' : '' }}">
                        <span class="nav-icon">📅</span> Kelola Jadwal
                    </a>
                    <a href="{{ route('admin.pelanggan.index') }}" class="{{ request()->routeIs('admin.pelanggan.*') ? 'active' : '' }}">
                        <span class="nav-icon">👥</span> Data Pelanggan
                    </a>
                    <a href="{{ route('admin.reservasi.index') }}" class="{{ request()->routeIs('admin.reservasi.*') ? 'active' : '' }}">
                        <span class="nav-icon">✅</span> Reservasi &amp; Verifikasi
                    </a>
                @else
                    <a href="{{ route('pemilik.dashboard') }}" class="{{ request()->routeIs('pemilik.dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">🏠</span> Dashboard Monitoring
                    </a>
                    <a href="{{ route('pemilik.reservasi') }}" class="{{ request()->routeIs('pemilik.reservasi') ? 'active' : '' }}">
                        <span class="nav-icon">📊</span> Data Reservasi
                    </a>
                @endif

                <a href="{{ route('lapangan.index') }}"><span class="nav-icon">🔎</span> Lihat Situs</a>
            </nav>

            <div class="side-foot">
                <div class="user-chip">
                    <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="user-meta">
                        <span class="uname">{{ auth()->user()->name }}</span>
                        <span class="urole">{{ auth()->user()->role === 'admin' ? 'Admin/Petugas' : auth()->user()->role }}</span>
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-secondary btn-sm" type="submit" style="width:100%;margin:0;">⟵ Keluar</button>
                </form>
            </div>
        </aside>

        <div class="main">
            <header class="topbar">
                <strong>@yield('title', 'Beranda')</strong>
                <div class="spacer"></div>
                @if (auth()->user()->role === 'pelanggan')
                    <span class="badge badge-success">Pelanggan</span>
                @elseif (auth()->user()->role === 'admin')
                    <span class="badge badge-info">Admin / Petugas</span>
                @else
                    <span class="badge badge-warning">Pemilik</span>
                @endif
            </header>

            <main class="content">
                @if (session('success'))
                    <div class="alert alert-success">✔ {{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">✖ {{ session('error') }}</div>
                @endif

                @yield('content')
            </main>

            <footer class="foot">{{ config('app.name') }} — Sistem Informasi Reservasi Lapangan Futsal</footer>
        </div>
    </div>
@else
    <header class="navbar">
        <a class="brand" href="{{ route('home') }}"><span class="brand-icon">⚽</span> {{ config('app.name') }}</a>
        <div class="spacer"></div>
        <a href="{{ route('lapangan.index') }}">Lapangan</a>
        <a href="{{ route('login') }}" class="nav-outline">Masuk</a>
        <a href="{{ route('register') }}" class="nav-cta">Daftar</a>
    </header>

    <main class="content content-public">
        @if (session('success'))
            <div class="alert alert-success">✔ {{ session('success') }}</div>
        @endif

        @yield('content')
    </main>
@endauth
</body>
</html>
