@extends('layouts.app')

@section('title', $lapangan->nama)

@section('content')
    @php
        $rolePelanggan = auth()->check() && auth()->user()->role === 'pelanggan';
    @endphp

    <div class="card">
        <div class="section-head">
            <div>
                <span class="badge badge-success">{{ ucfirst($lapangan->status) }}</span>
                <h1 style="margin-top:8px;">{{ $lapangan->nama }}</h1>
                <p class="muted">{{ $lapangan->deskripsi }}</p>
                <p class="price" style="margin-top:8px;">Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }} <span class="muted" style="font-weight:400;font-size:13px;">/ jam</span></p>
            </div>
            <a class="btn btn-secondary btn-sm" href="{{ route('lapangan.index') }}">← Daftar Lapangan</a>
        </div>
    </div>

    <div class="card">
        <div class="section-head">
            <div>
                <h1>Jadwal &amp; Ketersediaan</h1>
                <p class="muted">Slot kosong bisa langsung dipesan oleh pelanggan yang login.</p>
            </div>
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

        @if (auth()->check() && ! $rolePelanggan)
            <div class="alert alert-info">
                ℹ️ Anda masuk sebagai {{ auth()->user()->role === 'admin' ? 'Admin/Petugas' : 'Pemilik' }} —
                pembuatan reservasi hanya untuk akun pelanggan.
            </div>
        @endif

        @if ($jadwals->isEmpty())
            <div class="empty">
                <p>Belum ada jadwal untuk lapangan ini.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Durasi</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jadwals as $jadwal)
                            <tr>
                                <td>{{ $jadwal->tanggal->format('d-m-Y') }} ({{ $jadwal->tanggal->locale('id')->dayName }})</td>
                                <td>{{ substr($jadwal->jam_mulai, 0, 5) }} – {{ substr($jadwal->jam_selesai, 0, 5) }}</td>
                                <td>{{ $jadwal->durasiJam() }} jam</td>
                                <td>Rp {{ number_format($lapangan->harga_per_jam * $jadwal->durasiJam(), 0, ',', '.') }}</td>
                                <td>
                                    @if ($jadwal->tersedia)
                                        <span class="badge badge-success">Tersedia</span>
                                    @else
                                        <span class="badge badge-danger">Dipesan</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($jadwal->tersedia)
                                        @guest
                                            <a class="btn btn-sm" href="{{ route('login') }}">Login untuk Reservasi</a>
                                        @elseif ($rolePelanggan)
                                            <form method="POST" action="{{ route('reservasi.store', [$lapangan, $jadwal]) }}"
                                                  style="margin:0;"
                                                  onsubmit="return confirm('Reservasi {{ $lapangan->nama }} pada {{ $jadwal->tanggal->format('d-m-Y') }} jam {{ substr($jadwal->jam_mulai, 0, 5) }}?');">
                                                @csrf
                                                <button class="btn btn-sm" type="submit">Reservasi</button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
