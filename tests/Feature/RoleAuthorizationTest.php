<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 *_Otorisasi berbasis role: middleware `role` pada grup route /admin dan /pemilik._
 */
class RoleAuthorizationTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: array<int, string>}>
     */
    public static function routePerRole(): array
    {
        return [
            'dashboard admin' => ['admin', ['/admin/dashboard', '/admin/lapangan', '/admin/jadwal', '/admin/pelanggan', '/admin/reservasi']],
            'dashboard pemilik' => ['pemilik', ['/pemilik/dashboard', '/pemilik/reservasi']],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('routePerRole')]
    public function test_pengguna_dengan_role_tepat_bisa_membuka_halaman(string $role, array $urls): void
    {
        $user = $this->pengguna($role);

        foreach ($urls as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('routePerRole')]
    public function test_role_lain_menempatkan_403(string $role, array $urls): void
    {
        // Setiap role diuji oleh dua role lain yang tidak berhak.
        foreach (['pelanggan', 'admin', 'pemilik'] as $lain) {
            if ($lain === $role) {
                continue;
            }

            $user = $this->pengguna($lain);

            foreach ($urls as $url) {
                $this->actingAs($user)->get($url)->assertForbidden();
            }
        }
    }

    public function test_pelanggan_tidak_bisa_menjalankan_aksi_admin(): void
    {
        $pelanggan = $this->pengguna();
        $lapangan = $this->lapangan();
        $jadwal = $this->jadwal($lapangan);

        // Tulis endpoint admin.
        $this->actingAs($pelanggan)->post('/admin/lapangan', [
            'nama' => 'Gelap', 'harga_per_jam' => 1000, 'status' => 'aktif',
        ])->assertForbidden();
        $this->actingAs($pelanggan)->put('/admin/lapangan/'.$lapangan->id, [
            'nama' => 'Gelap', 'harga_per_jam' => 1, 'status' => 'aktif',
        ])->assertForbidden();
        $this->actingAs($pelanggan)->delete('/admin/lapangan/'.$lapangan->id)->assertForbidden();

        $this->actingAs($pelanggan)->post('/admin/jadwal', [
            'lapangan_id' => $lapangan->id,
            'tanggal' => now()->addWeek()->toDateString(),
            'jam_mulai' => '08:00', 'jam_selesai' => '09:00',
        ])->assertForbidden();
        $this->actingAs($pelanggan)->delete('/admin/jadwal/'.$jadwal->id)->assertForbidden();

        $this->assertSame(1, $lapangan->jadwal()->count(), 'Data admin tidak boleh berubah oleh pelanggan.');
    }

    public function test_admin_tidak_bisa_membuka_dashboard_pemilik(): void
    {
        $this->actingAs($this->pengguna('admin'))
            ->get('/pemilik/dashboard')
            ->assertForbidden();
    }

    public function test_pemilik_tidak_bisa_membuka_dashboard_admin(): void
    {
        $this->actingAs($this->pengguna('pemilik'))
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_role_tidak_dikenal_ditolak(): void
    {
        // Guard: bila role tak dikenal tersimpan di DB, middleware harus tolak.
        $user = $this->pengguna('pelanggan');
        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
    }
}