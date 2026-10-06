@extends('layouts.app')

@section('title', 'Kelola Lapangan')

@section('content')
    <div class="card">
        <h1>Kelola Lapangan</h1>
        <p style="margin-top:12px;">
            <a class="btn" href="{{ route('admin.lapangan.create') }}">+ Tambah Lapangan</a>
        </p>
    </div>

    <div class="card">
        @if ($lapangans->isEmpty())
            <p class="muted">Belum ada lapangan.</p>
        @else
            <table>
                <thead>
                    <tr><th>Nama</th><th>Harga/Jam</th><th>Status</th><th>Jadwal</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @foreach ($lapangans as $lapangan)
                        <tr>
                            <td>{{ $lapangan->nama }}</td>
                            <td>Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }}</td>
                            <td>@include('partials.badge', ['status' => $lapangan->status])</td>
                            <td>{{ $lapangan->jadwal_count }}</td>
                            <td>
                                <a class="btn" style="padding:6px 12px;" href="{{ route('admin.lapangan.edit', $lapangan) }}">Ubah</a>
                                <form method="POST" action="{{ route('admin.lapangan.destroy', $lapangan) }}"
                                      style="display:inline;" onsubmit="return confirm('Hapus lapangan {{ $lapangan->nama }}? Jadwal ikut terhapus.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-secondary" style="padding:6px 12px;" type="submit">Hapus</button>
                                </form>
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
