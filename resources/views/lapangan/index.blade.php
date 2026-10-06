@extends('layouts.app')

@section('title', 'Daftar Lapangan')

@section('content')
    <div class="card">
        <div class="section-head">
            <div>
                <h1>Daftar Lapangan</h1>
                <p class="muted">Pilih lapangan untuk melihat jadwal yang tersedia.</p>
            </div>
            @auth
                @if (auth()->user()->role === 'pelanggan')
                    <a class="btn btn-secondary btn-sm" href="{{ route('reservasi.index') }}">🧾 Reservasi Saya</a>
                @endif
            @endauth
        </div>
    </div>

    @if ($lapangans->isEmpty())
        <div class="card empty">
            <p>Belum ada lapangan tersedia.</p>
        </div>
    @else
        <div class="grid grid-cards">
            @foreach ($lapangans as $lapangan)
                <div class="card">
                    <span class="feature-ico">🏟️</span>
                    <div class="section-head">
                        <h2 style="margin-bottom:4px;">{{ $lapangan->nama }}</h2>
                        <span class="badge badge-success">{{ ucfirst($lapangan->status) }}</span>
                    </div>
                    <p class="muted">{{ \Illuminate\Support\Str::limit($lapangan->deskripsi, 100) }}</p>
                    <p class="price" style="margin:12px 0 4px;">Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }} <span class="muted" style="font-weight:400;font-size:13px;">/ jam</span></p>
                    <p style="margin-top:10px;">
                        <a class="btn" href="{{ route('lapangan.show', $lapangan) }}">Lihat Jadwal →</a>
                    </p>
                </div>
            @endforeach
        </div>
    @endif
@endsection
