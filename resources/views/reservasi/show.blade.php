@extends('layouts.app')

@section('title', 'Detail Reservasi')

@section('content')
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
        <div class="section-head">
            <div>
                <span class="muted">Reservasi</span>
                <h1>#{{ $reservasi->id }}</h1>
                <p style="margin-top:4px;">Status: @include('partials.badge', ['status' => $reservasi->status, 'label' => $reservasi->status_label])</p>
            </div>
            <a class="btn btn-secondary btn-sm" href="{{ route('reservasi.index') }}">← Kembali ke Reservasi Saya</a>
        </div>
    </div>

    <div class="grid grid-cards" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));">
        <div class="card">
            <h1>Detail Jadwal</h1>
            <table class="kvs">
                <tr><th>Lapangan</th><td>{{ $reservasi->jadwal?->lapangan?->nama ?? '-' }}</td></tr>
                <tr><th>Tanggal</th><td>{{ $reservasi->jadwal?->tanggal?->format('d-m-Y') ?? '-' }}</td></tr>
                <tr><th>Jam</th><td>{{ $reservasi->jadwal ? substr($reservasi->jadwal->jam_mulai, 0, 5).' – '.substr($reservasi->jadwal->jam_selesai, 0, 5) : '-' }}</td></tr>
                <tr><th>Total Tagihan</th><td><strong class="price">Rp {{ number_format($reservasi->total_harga, 0, ',', '.') }}</strong></td></tr>
            </table>
        </div>

        @if ($reservasi->pembayaran)
            <div class="card">
                <h1>Pembayaran</h1>
                <table class="kvs">
                    <tr>
                        <th>Status</th>
                        <td>@include('partials.badge', ['status' => $reservasi->pembayaran->status])</td>
                    </tr>
                    <tr><th>Jumlah</th><td><strong>Rp {{ number_format($reservasi->pembayaran->jumlah, 0, ',', '.') }}</strong></td></tr>
                    <tr><th>Transfer ke</th><td>Bank BCA — <strong>1234567890</strong> a.n. Futsal Reserve</td></tr>
                    <tr><th>Nama Pengirim</th><td>{{ $reservasi->pembayaran->nama_pengirim ?? '-' }}</td></tr>
                    @if ($reservasi->pembayaran->catatan)
                        <tr><th>Catatan Petugas</th><td style="color:#b91c1c;">{{ $reservasi->pembayaran->catatan }}</td></tr>
                    @endif
                </table>
            </div>
        @endif
    </div>

    @if ($reservasi->pembayaran)
        @php
            $bolehUpload = in_array($reservasi->pembayaran->status, ['menunggu_pembayaran', 'tidak_valid'], true);
        @endphp

        @if ($bolehUpload)
            <div class="card">
                <span class="feature-ico">📤</span>
                <h1>Unggah Bukti Pembayaran</h1>
                <p class="muted">Setelah transfer, unggah foto/screenshot bukti (JPG/PNG, maks. 2 MB).</p>

                <form method="POST" action="{{ route('pembayaran.upload', $reservasi) }}" enctype="multipart/form-data">
                    @csrf
                    <label for="bukti">File Bukti</label>
                    <input type="file" id="bukti" name="bukti" accept="image/jpeg,image/png" required>
                    <p style="margin-top:16px;">
                        <button class="btn" type="submit">Unggah Bukti</button>
                    </p>
                </form>
            </div>
        @endif

        @if ($reservasi->pembayaran->bukti_transfer)
            <div class="card">
                <h1>Bukti Terunggah</h1>
                <p style="margin-top:8px;">
                    <img class="img-preview"
                         src="{{ asset($reservasi->pembayaran->bukti_transfer) }}"
                         alt="Bukti pembayaran reservasi #{{ $reservasi->id }}">
                </p>
                @if (! $bolehUpload)
                    <p class="muted">Bukti sedang diproses oleh petugas.</p>
                @else
                    <p class="muted">Gambar lama akan diganti dengan yang baru setelah Anda mengunggah ulang.</p>
                @endif
            </div>
        @endif
    @endif
@endsection
