@extends('layouts.app')

@section('title', 'Reservasi Saya')

@section('content')
    <div class="card">
        <h1>Reservasi Saya</h1>
        <p class="muted">Status dan riwayat reservasi Anda.</p>
    </div>

    @if ($errors->any())
        <div class="errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        @if ($reservasis->isEmpty())
            <p>Anda belum memiliki reservasi. <a href="{{ route('lapangan.index') }}">Lihat lapangan</a></p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Lapangan</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reservasis as $reservasi)
                        <tr>
                            <td>{{ $reservasi->id }}</td>
                            <td>{{ $reservasi->jadwal?->lapangan?->nama ?? '-' }}</td>
                            <td>{{ $reservasi->jadwal?->tanggal?->format('d-m-Y') ?? '-' }}</td>
                            <td>{{ $reservasi->jadwal ? substr($reservasi->jadwal->jam_mulai, 0, 5).' – '.substr($reservasi->jadwal->jam_selesai, 0, 5) : '-' }}</td>
                            <td>Rp {{ number_format($reservasi->total_harga, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge">{{ $reservasi->status_label }}</span>
                                @if ($reservasi->pembayaran && $reservasi->pembayaran->status === 'tidak_valid')
                                    <br><span style="color:#b91c1c;font-size:13px;">Bukti ditolak — unggah ulang</span>
                                @elseif ($reservasi->pembayaran && $reservasi->pembayaran->bukti_transfer)
                                    <br><span class="muted">Bukti sudah diunggah</span>
                                @endif
                            </td>
                            <td>{{ $reservasi->created_at->format('d-m-Y H:i') }}</td>
                            <td>
                                <a class="btn" style="padding:6px 12px;"
                                   href="{{ route('reservasi.show', $reservasi) }}">
                                    @if ($reservasi->pembayaran && in_array($reservasi->pembayaran->status, ['menunggu_pembayaran', 'tidak_valid'], true))
                                        Bayar
                                    @else
                                        Detail
                                    @endif
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
