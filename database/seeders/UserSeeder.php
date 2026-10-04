<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Akun admin/pemilik (login khusus) dan pelanggan demo.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@futsal.test'],
            [
                'name' => 'Admin Futsal',
                'password' => Hash::make('admin12345'),
                'telepon' => '081111111111',
                'role' => 'admin',
            ],
        );

        User::updateOrCreate(
            ['email' => 'pemilik@futsal.test'],
            [
                'name' => 'Pemilik Futsal',
                'password' => Hash::make('pemilik12345'),
                'telepon' => '082222222222',
                'role' => 'pemilik',
            ],
        );

        $pelanggan = [
            ['budi@futsal.test', 'Budi Santoso', '081234567890'],
            ['sari@futsal.test', 'Sari Wijaya', '081298765432'],
            ['andi@futsal.test', 'Andi Pratama', '081377777777'],
        ];

        foreach ($pelanggan as [$email, $nama, $telepon]) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $nama,
                    'password' => Hash::make('password123'),
                    'telepon' => $telepon,
                    'role' => 'pelanggan',
                ],
            );
        }
    }
}
