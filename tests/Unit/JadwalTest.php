<?php

namespace Tests\Unit;

use App\Models\Jadwal;
use Tests\TestCase;

class JadwalTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string, 2: int}>
     */
    public static function durasi(): array
    {
        return [
            'satu jam' => ['10:00', '11:00', 1],
            'dua jam' => ['14:00', '16:00', 2],
            'tiga jam' => ['07:00', '10:00', 3],
            'lewat tengah malam' => ['23:00', '01:00', 2],
            'sama dengan nol -> minimal 1 jam' => ['09:00', '09:00', 1],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('durasi')]
    public function test_durasi_jam_dihitung_benar(string $mulai, string $selesai, int $harapan): void
    {
        $jadwal = new Jadwal([
            'jam_mulai' => $mulai,
            'jam_selesai' => $selesai,
        ]);

        $this->assertSame($harapan, $jadwal->durasiJam());
    }

    public function test_slot_tersedia_bila_belum_ada_reservasi(): void
    {
        $jadwal = $this->jadwal();

        $this->assertTrue($jadwal->tersedia);
    }

    public function test_slot_tidak_tersedia_bila_sudah_dipesan(): void
    {
        $jadwal = $this->jadwal();
        $jadwal->reservasi()->create([
            'user_id' => $this->pengguna()->id,
            'total_harga' => 50000,
            'status' => 'menunggu_pembayaran',
        ]);

        $this->assertFalse($jadwal->fresh()->tersedia);
    }
}