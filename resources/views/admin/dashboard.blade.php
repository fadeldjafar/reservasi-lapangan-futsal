@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="card">
        <h1>Dashboard Admin / Petugas</h1>
        <p class="muted">Ringkasan sistem reservasi.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:16px;margin-bottom:20px;">
        <div class="card"><h1 style="font-size:28px;">{{ $ringkasan['lapangan'] }}</h1><p class="muted">Lapangan</p></div>
        <div class="card"><h1 style="font-size:28px;">{{ $ringkasan['jadwal'] }}</h1><p class="muted">Jadwal</p></div>
        <div class="card"><h1 style="font-size:28px;">{{ $ringkasan['pelanggan'] }}</h1><p class="muted">Pelanggan</p></div>
        <div class="card"><h1 style="font-size:28px;">{{ $ringkasan['reservasi'] }}</h1><p class="muted">Reservasi</p></div>
        <div class="card" style="border-color:#f59e0b;">
            <h1 style="font-size:28px;color:#b45309;">{{ $ringkasan['menunggu_verifikasi'] }}</h1>
            <p class="muted">Menunggu Verifikasi</p>
        </div>
        <div class="card" style="border-color:#166534;">
            <h1 style="font-size:28px;color:#166534;">Rp {{ number_format($ringkasan['pendapatan'], 0, ',', '.') }}</h1>
            <p class="muted">Pendapatan (dikonfirmasi)</p>
        </div>
    </div>

    <div class="card">
        <h1>Reservasi per Status</h1>
        <p style="margin-top:10px;">
            @php
                $labelStatus = [
                    'menunggu_pembayaran' => 'Menunggu Pembayaran',
                    'menunggu_verifikasi' => 'Menunggu Verifikasi',
                    'dikonfirmasi' => 'Dikonfirmasi',
                    'ditolak' => 'Ditolak',
                    'dibatalkan' => 'Dibatalkan',
                ];
            @endphp
            @forelse ($perStatus as $st => $jml)
                <a class="btn btn-secondary" style="padding:6px 12px;" href="{{ route('admin.reservasi.index', ['status' => $st]) }}">
                    {{ $labelStatus[$st] ?? $st }}: {{ $jml }}
                </a>
            @empty
                <span class="muted">Belum ada reservasi.</span>
            @endforelse
        </p>
    </div>

    <div class="card">
        <h1>Menunggu Verifikasi Pembayaran</h1>
        @if ($antrean->isEmpty())
            <p class="muted">Tidak ada pembayaran yang menunggu verifikasi.</p>
        @else
            <table>
                <thead>
                    <tr><th>#</th><th>Pelanggan</th><th>Lapangan</th><th>Tanggal</th><th>Total</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($antrean as $r)
                        <tr>
                            <td>{{ $r->id }}</td>
                            <td>{{ $r->pelanggan?->name ?? '-' }}</td>
                            <td>{{ $r->jadwal?->lapangan?->nama ?? '-' }}</td>
                            <td>{{ $r->jadwal?->tanggal?->format('d-m-Y') ?? '-' }}</td>
                            <td>Rp {{ number_format($r->total_harga, 0, ',', '.') }}</td>
                            <td><a class="btn" style="padding:6px 12px;" href="{{ route('admin.reservasi.show', $r) }}">Verifikasi</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
