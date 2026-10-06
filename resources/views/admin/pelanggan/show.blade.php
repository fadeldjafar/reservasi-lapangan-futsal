@extends('layouts.app')

@section('title', 'Riwayat Pelanggan')

@section('content')
    <div class="card">
        <h1>{{ $pelanggan->name }}</h1>
        <p class="muted">{{ $pelanggan->email }} · {{ $pelanggan->telepon ?? 'tanpa telepon' }} · bergabung {{ $pelanggan->created_at->format('d-m-Y') }}</p>
        <p style="margin-top:12px;">
            <a class="btn btn-secondary" href="{{ route('admin.pelanggan.index') }}">← Kembali</a>
        </p>
    </div>

    <div class="card">
        <h1>Riwayat Reservasi</h1>
        @if ($reservasis->isEmpty())
            <p class="muted">Pelanggan ini belum pernah reservasi.</p>
        @else
            <table>
                <thead>
                    <tr><th>#</th><th>Lapangan</th><th>Tanggal</th><th>Total</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach ($reservasis as $r)
                        <tr>
                            <td><a href="{{ route('admin.reservasi.show', $r) }}">#{{ $r->id }}</a></td>
                            <td>{{ $r->jadwal?->lapangan?->nama ?? '-' }}</td>
                            <td>{{ $r->jadwal?->tanggal?->format('d-m-Y') ?? '-' }}</td>
                            <td>Rp {{ number_format($r->total_harga, 0, ',', '.') }}</td>
                            <td>@include('partials.badge', ['status' => $r->status, 'label' => $r->status_label])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
