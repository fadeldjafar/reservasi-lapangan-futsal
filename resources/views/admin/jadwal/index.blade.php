@extends('layouts.app')

@section('title', 'Kelola Jadwal')

@section('content')
    <div class="card">
        <h1>Kelola Jadwal</h1>

        <form method="GET" action="{{ route('admin.jadwal.index') }}" style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <label for="lapangan" style="margin:0;">Filter lapangan:</label>
            <select id="lapangan" name="lapangan" style="padding:8px;border:1px solid #cbd5e1;border-radius:8px;">
                <option value="">Semua</option>
                @foreach ($lapangans as $l)
                    <option value="{{ $l->id }}" @selected((string) request('lapangan') === (string) $l->id)>{{ $l->nama }}</option>
                @endforeach
            </select>
            <button class="btn btn-secondary" style="padding:8px 14px;" type="submit">Filter</button>
            <a class="btn" href="{{ route('admin.jadwal.create') }}">+ Tambah Jadwal</a>
        </form>
    </div>

    <div class="card">
        @if ($jadwals->isEmpty())
            <p class="muted">Tidak ada jadwal.</p>
        @else
            <table>
                <thead>
                    <tr><th>Tanggal</th><th>Jam</th><th>Lapangan</th><th>Ketersediaan</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @foreach ($jadwals as $jadwal)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($jadwal->tanggal)->format('d-m-Y') }}</td>
                            <td>{{ substr($jadwal->jam_mulai, 0, 5) }} – {{ substr($jadwal->jam_selesai, 0, 5) }}</td>
                            <td>{{ $jadwal->lapangan?->nama ?? '-' }}</td>
                            <td>
                                @if ($jadwal->reservasi)
                                    <span style="color:#b91c1c;font-weight:700;">Dipesan</span>
                                @else
                                    <span style="color:#166534;font-weight:700;">Tersedia</span>
                                @endif
                            </td>
                            <td>
                                @unless ($jadwal->reservasi)
                                    <a class="btn" style="padding:6px 12px;" href="{{ route('admin.jadwal.edit', $jadwal) }}">Ubah</a>
                                    <form method="POST" action="{{ route('admin.jadwal.destroy', $jadwal) }}"
                                          style="display:inline;" onsubmit="return confirm('Hapus jadwal ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-secondary" style="padding:6px 12px;" type="submit">Hapus</button>
                                    </form>
                                @else
                                    <span class="muted">Terkunci (sudah dipesan)</span>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if ($errors->any())
            <div class="errors" style="margin-top:14px;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
