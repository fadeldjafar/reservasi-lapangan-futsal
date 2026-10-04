<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @use HasFactory<\Database\Factories\JadwalFactory>
 */
class Jadwal extends Model
{
    /** @use HasFactory<\Database\Factories\JadwalFactory> */
    use HasFactory;

    /**
     * Nama tabel (bentuk jamak tidak otomatis untuk kata ini).
     */
    protected $table = 'jadwal';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'lapangan_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'tersedia',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function lapangan(): BelongsTo
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }

    /**
     * Satu jadwal hanya boleh dipakai satu reservasi aktif.
     * Saat reservasi dibatalkan/ditolak, jadwal_id di-set NULL.
     */
    public function reservasi(): HasOne
    {
        return $this->hasOne(Reservasi::class, 'jadwal_id');
    }

    /**
     * Slot ini masih boleh dipesan?
     */
    public function getTersediaAttribute(): bool
    {
        return $this->reservasi === null;
    }

    /**
     * Durasi dalam jam (dibulatkan ke atas, minimal 1 jam).
     */
    public function durasiJam(): int
    {
        $mulai = (int) substr($this->jam_mulai, 0, 2);
        $selesai = (int) substr($this->jam_selesai, 0, 2);

        // Dukung jam lewat tengah malam (misal 23:00 - 01:00).
        $jam = $selesai - $mulai;
        if ($jam < 0) {
            $jam += 24;
        }

        // Jam mulai = jam selesai tidak mungkin terjadi lewat form admin
        // (jam_selesai wajib setelah jam_mulai); fallback-nya 1 jam.
        return max(1, $jam);
    }
}
