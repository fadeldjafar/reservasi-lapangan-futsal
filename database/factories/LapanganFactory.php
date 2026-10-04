<?php

namespace Database\Factories;

use App\Models\Lapangan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lapangan>
 */
class LapanganFactory extends Factory
{
    protected $model = Lapangan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'Lapangan '.fake()->unique()->numberBetween(1, 999),
            'deskripsi' => fake()->sentence(),
            'harga_per_jam' => fake()->numberBetween(50, 150) * 1000,
            'foto' => null,
            'status' => 'aktif',
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn () => ['status' => 'nonaktif']);
    }
}