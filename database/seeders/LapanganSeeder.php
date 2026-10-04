<?php

namespace Database\Seeders;

use App\Models\Jadwal;
use App\Models\Lapangan;
use Illuminate\Database\Seeder;

class LapanganSeeder extends Seeder
{
    /**
     * Data lapangan dan jadwal 7 hari ke depan.
     */
    public function run(): void
    {
        $lapangan = [
            ['nama' => 'Lapangan A (Sintetis Indoor)', 'deskripsi' => 'Lapangan utama berukuran 20x40 m dengan pencahayaan malam dan tribun penonton.', 'harga_per_jam' => 150000],
            ['nama' => 'Lapangan B (Sintetis Outdoor)', 'deskripsi' => 'Lapangan cadangan untuk latihan tim, tersedia air minum gratis.', 'harga_per_jam' => 120000],
        ];

        $slots = [
            ['09:00:00', '10:00:00'],
            ['16:00:00', '17:00:00'],
            ['19:00:00', '20:00:00'],
            ['20:00:00', '21:00:00'],
        ];

        foreach ($lapangan as $data) {
            $lap = Lapangan::updateOrCreate(['nama' => $data['nama']], [...$data, 'status' => 'aktif']);

            // Lapangan B tidak punya slot pagi.
            $pakaiSlots = str_contains($data['nama'], 'B') ? array_slice($slots, 1) : $slots;

            for ($hari = 1; $hari <= 7; $hari++) {
                $tanggal = now()->addDays($hari)->toDateString();

                foreach ($pakaiSlots as [$mulai, $selesai]) {
                    Jadwal::updateOrCreate(
                        [
                            'lapangan_id' => $lap->id,
                            'tanggal' => $tanggal,
                            'jam_mulai' => $mulai,
                        ],
                        ['jam_selesai' => $selesai],
                    );
                }
            }
        }
    }
}
