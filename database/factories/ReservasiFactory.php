<?php

namespace Database\Factories;

use App\Models\Jadwal;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservasi>
 */
class ReservasiFactory extends Factory
{
    protected $model = Reservasi::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->pelanggan(),
            'jadwal_id' => null,
            'total_harga' => 75000,
            'status' => 'menunggu_pembayaran',
            'catatan' => null,
        ];
    }

    /**
     * Setiap reservasi selalu punya satu pembayaran (di aplikasi keduanya
     * dibuat bersamaan), jadi factory ikut membuatkannya.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Reservasi $reservasi) {
            Pembayaran::firstOrCreate(
                ['reservasi_id' => $reservasi->id],
                [
                    'jumlah' => $reservasi->total_harga,
                    'nama_pengirim' => $reservasi->pelanggan?->name,
                    'status' => match ($reservasi->status) {
                        'menunggu_verifikasi' => 'menunggu_verifikasi',
                        'dikonfirmasi' => 'valid',
                        default => 'menunggu_pembayaran',
                    },
                    'bukti_transfer' => $reservasi->status === 'menunggu_pembayaran'
                        ? null
                        : 'uploads/bukti/bukti_test.png',
                ],
            );
        });
    }

    /**
     * Reservasi yang memakai satu slot jadwal tertentu (total harga dihitung
     * dari harga lapangan x durasi slot).
     */
    public function untukJadwal(Jadwal $jadwal): static
    {
        return $this->state(fn () => [
            'jadwal_id' => $jadwal->id,
            'total_harga' => $jadwal->lapangan->harga_per_jam * $jadwal->durasiJam(),
        ]);
    }

    public function menungguVerifikasi(): static
    {
        return $this->state(fn () => ['status' => 'menunggu_verifikasi']);
    }

    public function dikonfirmasi(): static
    {
        return $this->state(fn () => ['status' => 'dikonfirmasi']);
    }

    /**
     * Status batal/ditolak: jadwal dilepas agar slot bisa dipakai lagi.
     */
    public function dibatalkan(): static
    {
        return $this->state(fn () => ['status' => 'dibatalkan']);
    }
}