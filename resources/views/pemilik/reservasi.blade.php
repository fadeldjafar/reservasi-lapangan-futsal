@extends('layouts.app')

@section('title', 'Data Reservasi')

@section('content')
    <div class="card">
        <h1>Data Reservasi</h1>
        <form method="GET" action="{{ route('pemilik.reservasi') }}"
              style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <label for="status" style="margin:0;">Filter status:</label>
            <select id="status" name="status" style="padding:8px;border:1px solid #cbd5e1;border-radius:8px;">
                <option value="">Semua</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn btn-secondary" style="padding:8px 14px;" type="submit">Filter</button>
        </form>
    </div>

    <div class="card">
        @if ($reservasis->isEmpty())
            <p class="muted">Tidak ada reservasi.</p>
        @else
            <table>
                <thead>
                    <tr><th>#</th><th>Pelanggan</th><th>Lapangan</th><th>Tanggal/Jam</th><th>Total</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach ($reservasis as $r)
                        <tr>
                            <td>{{ $r->id }}</td>
                            <td>{{ $r->pelanggan?->name ?? '-' }}</td>
                            <td>{{ $r->jadwal?->lapangan?->nama ?? '-' }}</td>
                            <td>
                                @if ($r->jadwal)
                                    {{ $r->jadwal->tanggal->format('d-m-Y') }}
                                    <br><span class="muted">{{ substr($r->jadwal->jam_mulai, 0, 5) }} – {{ substr($r->jadwal->jam_selesai, 0, 5) }}</span>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                            <td>Rp {{ number_format($r->total_harga, 0, ',', '.') }}</td>
                            <td><span class="badge">{{ $r->status_label }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
