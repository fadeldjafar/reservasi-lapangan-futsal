<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @use HasFactory<\Database\Factories\ReservasiFactory>
 */
class Reservasi extends Model
{
    /** @use HasFactory<\Database\Factories\ReservasiFactory> */
    use HasFactory;

    /**
     * Nama tabel (bentuk jamak tidak otomatis untuk kata ini).
     */
    protected $table = 'reservasi';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'jadwal_id',
        'total_harga',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'total_harga' => 'integer',
        ];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'reservasi_id');
    }

    /**
     * Label status dalam Bahasa Indonesia untuk ditampilkan.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'dikonfirmasi' => 'Dikonfirmasi',
            'ditolak' => 'Ditolak',
            'dibatalkan' => 'Dibatalkan',
            default => $this->status,
        };
    }
}
