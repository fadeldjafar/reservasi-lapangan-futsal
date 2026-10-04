<?php

namespace Database\Factories;

use App\Models\Pembayaran;
use App\Models\Reservasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pembayaran>
 */
class PembayaranFactory extends Factory
{
    protected $model = Pembayaran::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reservasi_id' => Reservasi::factory(),
            'jumlah' => 75000,
            'nama_pengirim' => fake()->name(),
            'bukti_transfer' => null,
            'status' => 'menunggu_pembayaran',
            'diverifikasi_oleh' => null,
            'diverifikasi_pada' => null,
            'catatan' => null,
        ];
    }
}