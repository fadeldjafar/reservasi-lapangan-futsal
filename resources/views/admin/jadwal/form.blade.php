@extends('layouts.app')

@section('title', $jadwal->exists ? 'Ubah Jadwal' : 'Tambah Jadwal')

@section('content')
    <div class="card" style="max-width:560px;margin:0 auto;">
        <h1>{{ $jadwal->exists ? 'Ubah Jadwal' : 'Tambah Jadwal' }}</h1>

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
              action="{{ $jadwal->exists ? route('admin.jadwal.update', $jadwal) : route('admin.jadwal.store') }}">
            @csrf
            @if ($jadwal->exists)
                @method('PUT')
            @endif

            <label for="lapangan_id">Lapangan</label>
            <select id="lapangan_id" name="lapangan_id" required
                    style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;">
                <option value="">— Pilih Lapangan —</option>
                @foreach ($lapangans as $l)
                    <option value="{{ $l->id }}" @selected(old('lapangan_id', $jadwal->lapangan_id) == $l->id)>{{ $l->nama }}</option>
                @endforeach
            </select>

            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" min="{{ now()->toDateString() }}"
                   value="{{ old('tanggal', $jadwal->tanggal?->format('Y-m-d')) }}" required>

            <label for="jam_mulai">Jam Mulai</label>
            <input type="time" id="jam_mulai" name="jam_mulai"
                   value="{{ old('jam_mulai', $jadwal->jam_mulai ? substr($jadwal->jam_mulai, 0, 5) : '') }}" required>

            <label for="jam_selesai">Jam Selesai</label>
            <input type="time" id="jam_selesai" name="jam_selesai"
                   value="{{ old('jam_selesai', $jadwal->jam_selesai ? substr($jadwal->jam_selesai, 0, 5) : '') }}" required>

            <p style="margin-top:18px;">
                <button class="btn" type="submit">Simpan</button>
                <a class="btn btn-secondary" href="{{ route('admin.jadwal.index') }}">Batal</a>
            </p>
        </form>
    </div>
@endsection
