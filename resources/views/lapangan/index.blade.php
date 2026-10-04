@extends('layouts.app')

@section('title', 'Daftar Lapangan')

@section('content')
    <div class="card">
        <h1>Daftar Lapangan</h1>
        <p class="muted">Pilih lapangan untuk melihat jadwal yang tersedia.</p>
    </div>

    @if ($lapangans->isEmpty())
        <div class="card">
            <p>Belum ada lapangan tersedia.</p>
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;">
            @foreach ($lapangans as $lapangan)
                <div class="card">
                    <h1 style="font-size:20px;">{{ $lapangan->nama }}</h1>
                    <p class="muted">{{ \Illuminate\Support\Str::limit($lapangan->deskripsi, 100) }}</p>
                    <p style="margin-top:10px;"><strong>Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }}</strong> / jam</p>
                    <p style="margin-top:12px;">
                        <a class="btn" href="{{ route('lapangan.show', $lapangan) }}">Lihat Jadwal</a>
                    </p>
                </div>
            @endforeach
        </div>
    @endif
@endsection
