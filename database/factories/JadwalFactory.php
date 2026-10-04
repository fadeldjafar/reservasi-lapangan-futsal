<?php

namespace Database\Factories;

use App\Models\Lapangan;
use App\Models\Jadwal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Jadwal>
 */
class JadwalFactory extends Factory
{
    protected $model = Jadwal::class;

    /**
     * Default: slot 1 jamBesok, aman dipesan (tidak di masa lalu).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lapangan_id' => Lapangan::factory(),
            'tanggal' => now()->addDay()->toDateString(),
            'jam_mulai' => '10:00',
            'jam_selesai' => '11:00',
        ];
    }
}