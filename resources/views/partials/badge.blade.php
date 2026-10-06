{{-- Badge status berwarna.
     Pakai: @include('partials.badge', ['status' => $r->status, 'label' => $r->status_label]) --}}
@php
    $peta = [
        'menunggu_pembayaran' => ['Menunggu Pembayaran', 'badge-warning'],
        'menunggu_verifikasi' => ['Menunggu Verifikasi', 'badge-info'],
        'dikonfirmasi' => ['Dikonfirmasi', 'badge-success'],
        'valid' => ['Valid', 'badge-success'],
        'ditolak' => ['Ditolak', 'badge-danger'],
        'tidak_valid' => ['Tidak Valid', 'badge-danger'],
        'dibatalkan' => ['Dibatalkan', 'badge-muted'],
        'aktif' => ['Aktif', 'badge-success'],
        'nonaktif' => ['Nonaktif', 'badge-muted'],
    ];
    [$badgeTeks, $badgeKelas] = $peta[$status] ?? [$status, 'badge-muted'];
    $badgeTeks = $label ?? $badgeTeks;
@endphp
<span class="badge {{ $badgeKelas }}">{{ $badgeTeks }}</span>
