@extends('layouts.app')

@section('title', 'Data Pelanggan')

@section('content')
    <div class="card">
        <h1>Data Pelanggan</h1>
        <p class="muted">Akun dengan role pelanggan.</p>
    </div>

    <div class="card">
        @if ($pelanggans->isEmpty())
            <p class="muted">Belum ada pelanggan.</p>
        @else
            <table>
                <thead>
                    <tr><th>Nama</th><th>Email</th><th>Telepon</th><th>Reservasi</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @foreach ($pelanggans as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td>{{ $p->email }}</td>
                            <td>{{ $p->telepon ?? '-' }}</td>
                            <td>{{ $p->jumlah_reservasi }}</td>
                            <td>
                                <a class="btn" style="padding:6px 12px;" href="{{ route('admin.pelanggan.show', $p) }}">Riwayat</a>
                                <form method="POST" action="{{ route('admin.pelanggan.destroy', $p) }}"
                                      style="display:inline;" onsubmit="return confirm('Hapus pelanggan {{ $p->name }} beserta seluruh riwayat reservasinya?');">
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
