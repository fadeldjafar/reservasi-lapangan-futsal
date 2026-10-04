<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @use HasFactory<\Database\Factories\PembayaranFactory>
 */
class Pembayaran extends Model
{
    /** @use HasFactory<\Database\Factories\PembayaranFactory> */
    use HasFactory;

    /**
     * Nama tabel (bentuk jamak tidak otomatis untuk kata ini).
     */
    protected $table = 'pembayaran';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reservasi_id',
        'jumlah',
        'nama_pengirim',
        'bukti_transfer',
        'status',
        'diverifikasi_oleh',
        'diverifikasi_pada',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'diverifikasi_pada' => 'datetime',
        ];
    }

    public function reservasi(): BelongsTo
    {
        return $this->belongsTo(Reservasi::class, 'reservasi_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
