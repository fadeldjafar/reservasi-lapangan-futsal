<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use Tests\TestCase;

class ReservasiTest extends TestCase
{
    public function test_pelanggan_dapat_memesan_jadwal_yang_tersedia(): void
    {
        $lapangan = $this->lapangan(['harga_per_jam' => 80000]);
        $jadwal = $this->jadwal($lapangan, [
            'tanggal' => now()->addDays(2)->toDateString(),
            'jam_mulai' => '15:00',
            'jam_selesai' => '17:00',
        ]);
        $pelanggan = $this->pengguna();

        $response = $this->actingAs($pelanggan)->post(
            '/lapangan/'.$lapangan->id.'/jadwal/'.$jadwal->id.'/reservasi'
        );

        $response->assertRedirect('/reservasi');
        $response->assertSessionHas('success');

        // Total = harga per jam x durasi (2 jam) = 160.000
        $reservasi = Reservasi::where('jadwal_id', $jadwal->id)->sole();

        $this->assertSame($pelanggan->id, $reservasi->user_id);
        $this->assertSame(160000, $reservasi->total_harga);
        $this->assertSame('menunggu_pembayaran', $reservasi->status);

        $pembayaran = Pembayaran::where('reservasi_id', $reservasi->id)->sole();
        $this->assertSame(160000, $pembayaran->jumlah);
        $this->assertSame('menunggu_pembayaran', $pembayaran->status);
    }

    public function test_slot_yang_sudah_dipesan_tidak_bisa_dipesan_lagi(): void
    {
        $lapangan = $this->lapangan();
        $jadwal = $this->jadwal($lapangan);

        $this->actingAs($this->pengguna())->post(
            '/lapangan/'.$lapangan->id.'/jadwal/'.$jadwal->id.'/reservasi'
        )->assertSessionHasNoErrors();

        $response = $this->actingAs($this->pengguna())->post(
            '/lapangan/'.$lapangan->id.'/jadwal/'.$jadwal->id.'/reservasi'
        );

        $response->assertSessionHasErrors('jadwal');
        $this->assertSame(1, Reservasi::where('jadwal_id', $jadwal->id)->count());
    }

    public function test_jadwal_lampau_tidak_bisa_dipesan(): void
    {
        $lapangan = $this->lapangan();
        $jadwal = $this->jadwal($lapangan, [
            'tanggal' => now()->subDay()->toDateString(),
            'jam_mulai' => '10:00',
            'jam_selesai' => '11:00',
        ]);

        $response = $this->actingAs($this->pengguna())->post(
            '/lapangan/'.$lapangan->id.'/jadwal/'.$jadwal->id.'/reservasi'
        );

        $response->assertSessionHasErrors('jadwal');
        $this->assertDatabaseMissing('reservasi', ['jadwal_id' => $jadwal->id]);
    }

    public function test_tamu_tidak_bisa_memesan(): void
    {
        $lapangan = $this->lapangan();
        $jadwal = $this->jadwal($lapangan);

        $this->post('/lapangan/'.$lapangan->id.'/jadwal/'.$jadwal->id.'/reservasi')
            ->assertRedirect('/login');

        $this->assertDatabaseMissing('reservasi', ['jadwal_id' => $jadwal->id]);
    }

    public function test_jadwal_dari_lapangan_lain_menghasilkan_404(): void
    {
        $lapanganA = $this->lapangan();
        $lapanganB = $this->lapangan();
        $jadwalB = $this->jadwal($lapanganB);

        $this->actingAs($this->pengguna())->post(
            '/lapangan/'.$lapanganA->id.'/jadwal/'.$jadwalB->id.'/reservasi'
        )->assertNotFound();

        $this->assertDatabaseMissing('reservasi', ['jadwal_id' => $jadwalB->id]);
    }

    public function test_admin_tidak_bisa_membuat_reservasi(): void
    {
        $lapangan = $this->lapangan();
        $jadwal = $this->jadwal($lapangan);
        $admin = $this->pengguna('admin');

        $this->actingAs($admin)->post(
            '/lapangan/'.$lapangan->id.'/jadwal/'.$jadwal->id.'/reservasi'
        )->assertForbidden();

        $this->assertDatabaseMissing('reservasi', ['jadwal_id' => $jadwal->id]);
    }

    public function test_pemilik_tidak_bisa_membuat_reservasi(): void
    {
        $lapangan = $this->lapangan();
        $jadwal = $this->jadwal($lapangan);
        $pemilik = $this->pengguna('pemilik');

        $this->actingAs($pemilik)->post(
            '/lapangan/'.$lapangan->id.'/jadwal/'.$jadwal->id.'/reservasi'
        )->assertForbidden();

        $this->assertDatabaseMissing('reservasi', ['jadwal_id' => $jadwal->id]);
    }

    /**
     * Endpoint reservasi pelanggan (/reservasi, /reservasi/{id}, upload bukti)
     * tidak boleh bisa dibuka langsung oleh admin/petugas maupun pemilik.
     */
    public function test_admin_dan_pemilik_ditolak_membuka_endpoint_reservasi_pelanggan(): void
    {
        $reservasi = Reservasi::factory()->untukJadwal($this->jadwal())->create();

        foreach (['admin', 'pemilik'] as $role) {
            $user = $this->pengguna($role);

            $this->actingAs($user)->get('/reservasi')->assertForbidden();
            $this->actingAs($user)->get('/reservasi/'.$reservasi->id)->assertForbidden();
            $this->actingAs($user)
                ->post('/reservasi/'.$reservasi->id.'/pembayaran', ['bukti' => $this->fileGambarPng()])
                ->assertForbidden();
        }

        // Tidak ada berkas bukti yang tertulis untuk request yang ditolak.
        $this->assertNull($reservasi->fresh()->pembayaran->bukti_transfer);
    }

    public function test_daftar_reservasi_hanya_menampilkan_miliknya_sendiri(): void
    {
        $saya = $this->pengguna();
        $orangLain = $this->pengguna();

        $reservasiSaya = Reservasi::factory()->untukJadwal($this->jadwal())->create(['user_id' => $saya->id]);
        Reservasi::factory()->untukJadwal($this->jadwal($this->lapangan()))->create(['user_id' => $orangLain->id]);

        $response = $this->actingAs($saya)->get('/reservasi');

        $response->assertOk();
        $response->assertViewHas('reservasis', function ($reservasis) use ($reservasiSaya) {
            return $reservasis->count() === 1 && $reservasis->first()->id === $reservasiSaya->id;
        });
    }

    public function test_detail_reservasi_milik_orang_lain_dilarang(): void
    {
        $saya = $this->pengguna();
        $reservasiLain = Reservasi::factory()->untukJadwal($this->jadwal())->create();

        $this->actingAs($saya)
            ->get('/reservasi/'.$reservasiLain->id)
            ->assertForbidden();
    }

    public function test_detail_reservasi_sendiri_bisa_dibuka(): void
    {
        $saya = $this->pengguna();
        $reservasi = Reservasi::factory()->untukJadwal($this->jadwal())->create(['user_id' => $saya->id]);

        $this->actingAs($saya)
            ->get('/reservasi/'.$reservasi->id)
            ->assertOk()
            ->assertViewHas('reservasi', fn ($r) => $r->id === $reservasi->id);
    }

    public function test_slot_terbepas_setelah_reservasi_dibatalkan_admin(): void
    {
        $lapangan = $this->lapangan();
        $jadwal = $this->jadwal($lapangan);
        $pelanggan = $this->pengguna();
        $admin = $this->pengguna('admin');

        $this->actingAs($pelanggan)->post(
            '/lapangan/'.$lapangan->id.'/jadwal/'.$jadwal->id.'/reservasi'
        );
        $reservasi = Reservasi::where('jadwal_id', $jadwal->id)->sole();

        $this->actingAs($admin)
            ->post('/admin/reservasi/'.$reservasi->id.'/status', ['status' => 'dibatalkan'])
            ->assertSessionHas('success');

        $reservasi->refresh();
        $this->assertSame('dibatalkan', $reservasi->status);
        $this->assertNull($reservasi->jadwal_id, 'Jadwal harus dilepas agar slot bisa dipakai lagi.');

        // Slot yang sama harus bisa dipesan pelanggan lain.
        $pelangganLain = $this->pengguna();
        $this->actingAs($pelangganLain)->post(
            '/lapangan/'.$lapangan->id.'/jadwal/'.$jadwal->id.'/reservasi'
        )->assertSessionHasNoErrors();

        $this->assertSame(1, Jadwal::find($jadwal->id)->reservasi()->count());
    }
}