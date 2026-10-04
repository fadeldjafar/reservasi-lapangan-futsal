@extends('layouts.app')

@section('title', $lapangan->nama)

@section('content')
    <div class="card">
        <h1>{{ $lapangan->nama }}</h1>
        <p>{{ $lapangan->deskripsi }}</p>
        <p style="margin-top:8px;"><strong>Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }}</strong> / jam</p>
        <p style="margin-top:12px;">
            <a class="btn btn-secondary" href="{{ route('lapangan.index') }}">← Kembali ke Daftar Lapangan</a>
        </p>
    </div>

    <div class="card">
        <h1>Jadwal &amp; Ketersediaan</h1>

        @if ($errors->any())
            <div class="errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($jadwals->isEmpty())
            <p class="muted">Belum ada jadwal untuk lapangan ini.</p>
        @else
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
                                    <span style="color:#166534;font-weight:700;">Tersedia</span>
                                @else
                                    <span style="color:#b91c1c;font-weight:700;">Dipesan</span>
                                @endif
                            </td>
                            <td>
                                @if ($jadwal->tersedia)
                                    @auth
                                        <form method="POST" action="{{ route('reservasi.store', [$lapangan, $jadwal]) }}"
                                              onsubmit="return confirm('Reservasi {{ $lapangan->nama }} pada {{ $jadwal->tanggal->format('d-m-Y') }} jam {{ substr($jadwal->jam_mulai, 0, 5) }}?');">
                                            @csrf
                                            <button class="btn" type="submit">Reservasi</button>
                                        </form>
                                    @else
                                        <a class="btn" href="{{ route('login') }}">Login untuk Reservasi</a>
                                    @endauth
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
