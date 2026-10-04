@extends('layouts.app')

@section('title', 'Dashboard Pemilik')

@section('content')
    <div class="card">
        <h1>Dashboard Pemilik</h1>
        <p class="muted">Pantau reservasi, pendapatan, dan penggunaan lapangan.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:16px;margin-bottom:20px;">
        <div class="card">
            <h1 style="font-size:26px;">Rp {{ number_format($pendapatanTotal, 0, ',', '.') }}</h1>
            <p class="muted">Pendapatan Total (dikonfirmasi)</p>
        </div>
        <div class="card">
            <h1 style="font-size:26px;">{{ $ringkasan['dikonfirmasi'] }}</h1>
            <p class="muted">Reservasi Dikonfirmasi</p>
        </div>
        <div class="card">
            <h1 style="font-size:26px;">{{ $ringkasan['menunggu'] }}</h1>
            <p class="muted">Menunggu Pembayaran/Verifikasi</p>
        </div>
        <div class="card">
            <h1 style="font-size:26px;">{{ $ringkasan['pelanggan'] }}</h1>
            <p class="muted">Pelanggan</p>
        </div>
        <div class="card">
            <h1 style="font-size:26px;">{{ $ringkasan['lapangan'] }}</h1>
            <p class="muted">Lapangan</p>
        </div>
    </div>

    {{-- Laporan periode --}}
    <div class="card">
        <h1>Laporan (periode tanggal main)</h1>

        <form method="GET" action="{{ route('pemilik.dashboard') }}"
              style="margin-top:12px;display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
            <div>
                <label for="dari">Dari</label>
                <input type="date" id="dari" name="dari" value="{{ $dari }}">
            </div>
            <div>
                <label for="sampai">Sampai</label>
                <input type="date" id="sampai" name="sampai" value="{{ $sampai }}">
            </div>
            <button class="btn" type="submit" style="margin-bottom:2px;">Tampilkan</button>
        </form>

        <p style="margin-top:16px;">
            <strong>Rp {{ number_format($pendapatanPeriode, 0, ',', '.') }}</strong>
            <span class="muted">pendapatan dari {{ \Illuminate\Support\Carbon::parse($dari)->format('d-m-Y') }}
                s/d {{ \Illuminate\Support\Carbon::parse($sampai)->format('d-m-Y') }}
                ({{ $jumlahPeriode }} reservasi dikonfirmasi)</span>
        </p>

        <h1 style="font-size:18px;margin-top:20px;">Penggunaan Lapangan</h1>
        <table style="margin-top:8px;">
            <thead>
                <tr>
                    <th>Lapangan</th>
                    <th>Jadwal</th>
                    <th>Terpakai</th>
                    <th>Okupansi</th>
                    <th>Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($laporan as $l)
                    <tr>
                        <td>{{ $l['nama'] }}</td>
                        <td>{{ $l['jadwal'] }}</td>
                        <td>{{ $l['terpakai'] }}</td>
                        <td>
                            <div style="background:#e5e7eb;border-radius:6px;height:14px;width:140px;display:inline-block;vertical-align:middle;">
                                <div style="background:#166534;height:14px;border-radius:6px;width:{{ $l['okupansi'] }}%;"></div>
                            </div>
                            {{ $l['okupansi'] }}%
                        </td>
                        <td>Rp {{ number_format($l['pendapatan'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Belum ada lapangan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <h1>Reservasi Terbaru</h1>
        @if ($terbaru->isEmpty())
            <p class="muted">Belum ada reservasi.</p>
        @else
            <table>
                <thead>
                    <tr><th>#</th><th>Pelanggan</th><th>Lapangan</th><th>Tanggal</th><th>Total</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach ($terbaru as $r)
                        <tr>
                            <td>{{ $r->id }}</td>
                            <td>{{ $r->pelanggan?->name ?? '-' }}</td>
                            <td>{{ $r->jadwal?->lapangan?->nama ?? '-' }}</td>
                            <td>{{ $r->jadwal?->tanggal?->format('d-m-Y') ?? '-' }}</td>
                            <td>Rp {{ number_format($r->total_harga, 0, ',', '.') }}</td>
                            <td><span class="badge">{{ $r->status_label }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p style="margin-top:12px;">
                <a class="btn btn-secondary" href="{{ route('pemilik.reservasi') }}">Lihat Semua Reservasi</a>
            </p>
        @endif
    </div>
@endsection
