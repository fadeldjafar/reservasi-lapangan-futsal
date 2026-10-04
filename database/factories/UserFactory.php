<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Password default semua akun dummy (sama dengan isi kolom di DB).
     */
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            // Default: pelanggan, supaya test tidak lupafilled role.
            'role' => 'pelanggan',
            'telepon' => '08'.fake()->numerify('##########'),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }

    public function pemilik(): static
    {
        return $this->state(fn () => ['role' => 'pemilik']);
    }

    public function pelanggan(): static
    {
        return $this->state(fn () => ['role' => 'pelanggan']);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}