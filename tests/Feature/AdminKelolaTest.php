<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Reservasi;
use Tests\TestCase;

class AdminKelolaTest extends TestCase
{
    // -----------------------------------------------------------------
    // Lapangan
    // -----------------------------------------------------------------

    public function test_admin_bisa_menambah_lapangan(): void
    {
        $admin = $this->pengguna('admin');

        $this->actingAs($admin)->post('/admin/lapangan', [
            'nama' => 'Lapangan A',
            'deskripsi' => 'Lapangan indoor',
            'harga_per_jam' => 75000,
            'status' => 'aktif',
        ])->assertRedirect('/admin/lapangan');

        $this->assertDatabaseHas('lapangan', [
            'nama' => 'Lapangan A',
            'harga_per_jam' => 75000,
            'status' => 'aktif',
        ]);
    }

    public function test_lapangan_tanpa_nama_ditolak(): void
    {
        $admin = $this->pengguna('admin');

        $this->actingAs($admin)
            ->post('/admin/lapangan', ['harga_per_jam' => 50000, 'status' => 'aktif'])
            ->assertSessionHasErrors('nama');

        $this->assertSame(0, Lapangan::count());
    }

    public function test_admin_bisa_memperbarui_lapangan(): void
    {
        $admin = $this->pengguna('admin');
        $lapangan = $this->lapangan(['nama' => 'Lama', 'harga_per_jam' => 50000]);

        $this->actingAs($admin)->put('/admin/lapangan/'.$lapangan->id, [
            'nama' => 'Baru',
            'harga_per_jam' => 60000,
            'status' => 'nonaktif',
        ])->assertRedirect('/admin/lapangan');

        $lapangan->refresh();
        $this->assertSame('Baru', $lapangan->nama);
        $this->assertSame(60000, $lapangan->harga_per_jam);
        $this->assertSame('nonaktif', $lapangan->status);
    }

    public function test_lapangan_tanpa_riwayat_bisa_dihapus(): void
    {
        $admin = $this->pengguna('admin');
        $lapangan = $this->lapangan();

        $this->actingAs($admin)
            ->delete('/admin/lapangan/'.$lapangan->id)
            ->assertRedirect('/admin/lapangan')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('lapangan', ['id' => $lapangan->id]);
    }

    public function test_lapangan_dengan_riwayat_reservasi_tidak_bisa_dihapus(): void
    {
        $admin = $this->pengguna('admin');
        $lapangan = $this->lapangan();
        $jadwal = $this->jadwal($lapangan);
        Reservasi::factory()->untukJadwal($jadwal)->create();

        $this->actingAs($admin)
            ->delete('/admin/lapangan/'.$lapangan->id)
            ->assertSessionHasErrors('lapangan');

        $this->assertDatabaseHas('lapangan', ['id' => $lapangan->id]);
    }

    public function test_lapangan_nonaktif_tidak_ditampilkan_di_halaman_publik(): void
    {
        $aktif = $this->lapangan(['nama' => 'Aktif']);
        $nonaktif = $this->lapangan(['nama' => 'Nonaktif', 'status' => 'nonaktif']);

        $this->get('/lapangan')
            ->assertOk()
            ->assertSee('Aktif')
            ->assertDontSee('Nonaktif');

        $this->get('/lapangan/'.$aktif->id)->assertOk();
        $this->get('/lapangan/'.$nonaktif->id)->assertNotFound();
    }

    // -----------------------------------------------------------------
    // Jadwal
    // -----------------------------------------------------------------

    public function test_admin_bisa_menambah_jadwal(): void
    {
        $admin = $this->pengguna('admin');
        $lapangan = $this->lapangan();

        $this->actingAs($admin)->post('/admin/jadwal', [
            'lapangan_id' => $lapangan->id,
            'tanggal' => now()->addDays(3)->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
        ])->assertRedirect('/admin/jadwal');

        $this->assertDatabaseHas('jadwal', [
            'lapangan_id' => $lapangan->id,
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
        ]);
    }

    public function test_jadwal_slot_duplikat_ditolak(): void
    {
        $admin = $this->pengguna('admin');
        $lapangan = $this->lapangan();
        $tanggal = now()->addDays(3)->toDateString();
        $this->jadwal($lapangan, ['tanggal' => $tanggal, 'jam_mulai' => '08:00', 'jam_selesai' => '09:00']);

        $this->actingAs($admin)
            ->post('/admin/jadwal', [
                'lapangan_id' => $lapangan->id,
                'tanggal' => $tanggal,
                'jam_mulai' => '08:00',
                'jam_selesai' => '10:00',
            ])
            ->assertSessionHasErrors('jam_mulai');

        $this->assertSame(1, Jadwal::where('lapangan_id', $lapangan->id)->count());
    }

    public function test_jadwal_yang_sudah_dipesan_tidak_bisa_diubah_atau_dihapus(): void
    {
        $admin = $this->pengguna('admin');
        $lapangan = $this->lapangan();
        $jadwal = $this->jadwal($lapangan);
        $reservasi = Reservasi::factory()->untukJadwal($jadwal)->create();

        $this->actingAs($admin)->put('/admin/jadwal/'.$jadwal->id, [
            'lapangan_id' => $lapangan->id,
            'tanggal' => now()->addDays(5)->toDateString(),
            'jam_mulai' => '12:00',
            'jam_selesai' => '13:00',
        ])->assertSessionHasErrors('jadwal');

        $this->actingAs($admin)
            ->delete('/admin/jadwal/'.$jadwal->id)
            ->assertSessionHasErrors('jadwal');

        $this->assertDatabaseHas('jadwal', ['id' => $jadwal->id]);
        $this->assertDatabaseHas('reservasi', ['id' => $reservasi->id, 'jadwal_id' => $jadwal->id]);
    }

    public function test_jadwal_tanggal_lampau_ditolak(): void
    {
        $admin = $this->pengguna('admin');
        $lapangan = $this->lapangan();

        $this->actingAs($admin)->post('/admin/jadwal', [
            'lapangan_id' => $lapangan->id,
            'tanggal' => now()->subDay()->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:00',
        ])->assertSessionHasErrors('tanggal');
    }

    public function test_jadwal_yang_sama_dengan_sendiri_bisa_disimpan(): void
    {
        $admin = $this->pengguna('admin');
        $lapangan = $this->lapangan();
        $tanggal = now()->addDays(3)->toDateString();
        $jadwal = $this->jadwal($lapangan, ['tanggal' => $tanggal, 'jam_mulai' => '08:00', 'jam_selesai' => '09:00']);

        $this->actingAs($admin)->put('/admin/jadwal/'.$jadwal->id, [
            'lapangan_id' => $lapangan->id,
            'tanggal' => $tanggal,
            'jam_mulai' => '08:00',
            'jam_selesai' => '11:00',
        ])->assertSessionHas('success');

        // MySQL menyimpan kolom TIME lengkap dengan detik ("11:00:00").
        $this->assertSame('11:00', substr($jadwal->fresh()->jam_selesai, 0, 5));
    }

    // -----------------------------------------------------------------
    // Pelanggan
    // -----------------------------------------------------------------

    public function test_admin_melihat_daftar_pelanggan(): void
    {
        $admin = $this->pengguna('admin');
        $pelanggan = $this->pengguna();

        $this->actingAs($admin)
            ->get('/admin/pelanggan')
            ->assertOk()
            ->assertSee($pelanggan->name);
    }

    public function test_admin_tidak_bisa_menghapus_akun_non_pelanggan(): void
    {
        $admin = $this->pengguna('admin');
        $adminLain = $this->pengguna('admin');
        $pemilik = $this->pengguna('pemilik');

        $this->actingAs($admin)->delete('/admin/pelanggan/'.$adminLain->id)->assertForbidden();
        $this->actingAs($admin)->delete('/admin/pelanggan/'.$pemilik->id)->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $adminLain->id]);
        $this->assertDatabaseHas('users', ['id' => $pemilik->id]);
    }

    public function test_admin_tidak_bisa_menghapus_diri_sendiri(): void
    {
        $admin = $this->pengguna('admin');

        // Guard: akun admin tidak berperan pelanggan, jadi tetap 403.
        $this->actingAs($admin)->delete('/admin/pelanggan/'.$admin->id)->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_bisa_menghapus_pelanggan_beserta_riwayatnya(): void
    {
        $admin = $this->pengguna('admin');
        $pelanggan = $this->pengguna();
        $reservasi = Reservasi::factory()->untukJadwal($this->jadwal())->create([
            'user_id' => $pelanggan->id,
        ]);

        $this->actingAs($admin)
            ->delete('/admin/pelanggan/'.$pelanggan->id)
            ->assertRedirect('/admin/pelanggan')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $pelanggan->id]);
        // Foreign key cascade: reservasi (dan pembayarannya) ikut terhapus.
        $this->assertDatabaseMissing('reservasi', ['id' => $reservasi->id]);
        $this->assertDatabaseMissing('pembayaran', ['reservasi_id' => $reservasi->id]);
    }

    public function test_halaman_detail_pelanggan_hanya_untuk_role_pelanggan(): void
    {
        $admin = $this->pengguna('admin');
        $pelanggan = $this->pengguna();

        $this->actingAs($admin)->get('/admin/pelanggan/'.$pelanggan->id)->assertOk();
        $this->actingAs($admin)->get('/admin/pelanggan/'.$admin->id)->assertNotFound();
    }
}