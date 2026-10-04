@extends('layouts.app')

@section('title', $lapangan->exists ? 'Ubah Lapangan' : 'Tambah Lapangan')

@section('content')
    <div class="card" style="max-width:560px;margin:0 auto;">
        <h1>{{ $lapangan->exists ? 'Ubah Lapangan' : 'Tambah Lapangan' }}</h1>

        @if ($errors->any())
            <div class="errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ $lapangan->exists ? route('admin.lapangan.update', $lapangan) : route('admin.lapangan.store') }}">
            @csrf
            @if ($lapangan->exists)
                @method('PUT')
            @endif

            <label for="nama">Nama Lapangan</label>
            <input type="text" id="nama" name="nama" value="{{ old('nama', $lapangan->nama) }}" required>

            <label for="deskripsi">Deskripsi</label>
            <input type="text" id="deskripsi" name="deskripsi" value="{{ old('deskripsi', $lapangan->deskripsi) }}">

            <label for="harga_per_jam">Harga per Jam (Rp)</label>
            <input type="number" id="harga_per_jam" name="harga_per_jam" min="0"
                   value="{{ old('harga_per_jam', $lapangan->harga_per_jam) }}" required>

            <label for="status">Status</label>
            <select id="status" name="status" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;">
                <option value="aktif" @selected(old('status', $lapangan->status) === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(old('status', $lapangan->status) === 'nonaktif')>Nonaktif</option>
            </select>

            <p style="margin-top:18px;">
                <button class="btn" type="submit">Simpan</button>
                <a class="btn btn-secondary" href="{{ route('admin.lapangan.index') }}">Batal</a>
            </p>
        </form>
    </div>
@endsection
