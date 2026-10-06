@extends('layouts.app')

@section('title', 'Detail Reservasi #'.$reservasi->id)

@section('content')
    <div class="card">
        <h1>Reservasi #{{ $reservasi->id }}</h1>
        <p>Status: @include('partials.badge', ['status' => $reservasi->status, 'label' => $reservasi->status_label])</p>
        <p class="muted" style="margin-top:6px;">Dibuat {{ $reservasi->created_at->format('d-m-Y H:i') }}</p>
        <p style="margin-top:12px;">
            <a class="btn btn-secondary" href="{{ route('admin.reservasi.index') }}">← Kembali ke Data Reservasi</a>
        </p>
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
        <h1>Detail Reservasi</h1>
        <table>
            <tr><th>Pelanggan</th><td>{{ $reservasi->pelanggan?->name ?? '-' }} ({{ $reservasi->pelanggan?->email ?? '-' }})</td></tr>
            <tr><th>Lapangan</th><td>{{ $reservasi->jadwal?->lapangan?->nama ?? 'Jadwal sudah dilepas (dibatalkan/ditolak)' }}</td></tr>
            <tr><th>Tanggal</th><td>{{ $reservasi->jadwal?->tanggal?->format('d-m-Y') ?? '-' }}</td></tr>
            <tr><th>Jam</th><td>{{ $reservasi->jadwal ? substr($reservasi->jadwal->jam_mulai, 0, 5).' – '.substr($reservasi->jadwal->jam_selesai, 0, 5) : '-' }}</td></tr>
            <tr><th>Total</th><td><strong>Rp {{ number_format($reservasi->total_harga, 0, ',', '.') }}</strong></td></tr>
            @if ($reservasi->catatan)
                <tr><th>Catatan</th><td>{{ $reservasi->catatan }}</td></tr>
            @endif
        </table>
    </div>

    @if ($reservasi->pembayaran)
        <div class="card">
            <h1>Pembayaran</h1>
            <table>
                <tr><th>Jumlah</th><td>Rp {{ number_format($reservasi->pembayaran->jumlah, 0, ',', '.') }}</td></tr>
                <tr><th>Status</th><td>@include('partials.badge', ['status' => $reservasi->pembayaran->status])</td></tr>
                <tr><th>Nama Pengirim</th><td>{{ $reservasi->pembayaran->nama_pengirim ?? '-' }}</td></tr>
                <tr>
                    <th>Diverifikasi</th>
                    <td>
                        @if ($reservasi->pembayaran->diverifikasi_pada)
                            {{ $reservasi->pembayaran->diverifikasi_pada->format('d-m-Y H:i') }}
                            ({{ $reservasi->pembayaran->petugas?->name ?? 'petugas' }})
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @if ($reservasi->pembayaran->catatan)
                    <tr><th>Catatan</th><td>{{ $reservasi->pembayaran->catatan }}</td></tr>
                @endif
            </table>

            @if ($reservasi->pembayaran->bukti_transfer)
                <div style="margin-top:16px;">
                    <h1 style="font-size:18px;">Bukti Transfer</h1>
                    <p style="margin-top:8px;">
                        <img src="{{ asset($reservasi->pembayaran->bukti_transfer) }}"
                             alt="Bukti pembayaran reservasi #{{ $reservasi->id }}"
                             style="max-width:380px;width:100%;border:1px solid #e5e7eb;border-radius:8px;">
                    </p>
                </div>
            @else
                <p class="muted" style="margin-top:14px;">Pelanggan belum mengunggah bukti transfer.</p>
            @endif
        </div>

        {{-- Verifikasi pembayaran --}}
        @if ($reservasi->pembayaran->status === 'menunggu_verifikasi')
            <div class="card">
                <h1>Verifikasi Pembayaran</h1>

                <form method="POST" action="{{ route('admin.reservasi.verifikasi', $reservasi) }}">
                    @csrf
                    <label>
                        <input type="radio" name="keputusan" value="valid" checked> Pembayaran <strong>VALID</strong> (reservasi dikonfirmasi)
                    </label>
                    <label style="margin-top:8px;">
                        <input type="radio" name="keputusan" value="tidak_valid"> Pembayaran <strong>TIDAK VALID</strong> (minta unggah ulang)
                    </label>

                    <label for="catatan">Catatan (wajib diisi bila tidak valid)</label>
                    <input type="text" id="catatan" name="catatan" value="{{ old('catatan') }}" placeholder="Contoh: nominal kurang, bukti tidak terbaca">

                    <p style="margin-top:16px;">
                        <button class="btn" type="submit">Simpan Verifikasi</button>
                    </p>
                </form>
            </div>
        @endif
    @endif

    {{-- Ubah status reservasi --}}
    @unless (in_array($reservasi->status, ['dibatalkan', 'ditolak']))
        <div class="card">
            <h1>Ubah Status Reservasi</h1>

            <form method="POST" action="{{ route('admin.reservasi.status', $reservasi) }}">
                @csrf
                <select name="status" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;">
                    @foreach (['menunggu_pembayaran' => 'Menunggu Pembayaran', 'menunggu_verifikasi' => 'Menunggu Verifikasi', 'dikonfirmasi' => 'Dikonfirmasi', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan'] as $value => $label)
                        @if ($value !== $reservasi->status)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endif
                    @endforeach
                </select>
                <p class="muted" style="margin-top:8px;">Memilih <strong>Ditolak</strong> atau <strong>Dibatalkan</strong> akan melepas jadwal sehingga slot bisa dipesan lagi.</p>
                <p style="margin-top:12px;">
                    <button class="btn" type="submit">Ubah Status</button>
                </p>
            </form>
        </div>
    @endunless
@endsection
