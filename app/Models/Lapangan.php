<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @use HasFactory<\Database\Factories\LapanganFactory>
 */
class Lapangan extends Model
{
    /** @use HasFactory<\Database\Factories\LapanganFactory> */
    use HasFactory;

    /**
     * Nama tabel (bentuk jamak tidak otomatis untuk kata ini).
     */
    protected $table = 'lapangan';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'deskripsi',
        'harga_per_jam',
        'foto',
        'status',
    ];

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'lapangan_id');
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }
}
