<?php

namespace Tests\Feature;

use App\Models\Pembayaran;
use App\Models\Reservasi;
use App\Models\User;
use Tests\TestCase;

class AdminVerifikasiPembayaranTest extends TestCase
{
    private function reservasiMenungguVerifikasi(): array
    {
        $admin = User::factory()->admin()->create();
        $pelanggan = $this->pengguna();
        $reservasi = Reservasi::factory()->menungguVerifikasi()->create([
            'user_id' => $pelanggan->id,
            'status' => 'menunggu_verifikasi',
        ]);

        return [$admin, $pelanggan, $reservasi->fresh()];
    }

    public function test_bukti_valid_mengonfirmasi_reservasi(): void
    {
        [$admin, , $reservasi] = $this->reservasiMenungguVerifikasi();

        $this->actingAs($admin)
            ->post('/admin/reservasi/'.$reservasi->id.'/verifikasi', ['keputusan' => 'valid'])
            ->assertSessionHas('success');

        $pembayaran = $reservasi->fresh()->pembayaran;

        $this->assertSame('valid', $pembayaran->status);
        $this->assertSame($admin->id, $pembayaran->diverifikasi_oleh);
        $this->assertNotNull($pembayaran->diverifikasi_pada);
        $this->assertSame('dikonfirmasi', $reservasi->fresh()->status);
    }

    public function test_bukti_tidak_valid_tanpa_catatan_ditolak(): void
    {
        [$admin, , $reservasi] = $this->reservasiMenungguVerifikasi();

        $this->actingAs($admin)
            ->from('/admin/reservasi/'.$reservasi->id)
            ->post('/admin/reservasi/'.$reservasi->id.'/verifikasi', ['keputusan' => 'tidak_valid'])
            ->assertRedirect('/admin/reservasi/'.$reservasi->id)
            ->assertSessionHasErrors('catatan');

        $this->assertSame('menunggu_verifikasi', $reservasi->fresh()->pembayaran->status);
        $this->assertSame('menunggu_verifikasi', $reservasi->fresh()->status);
    }

    public function test_bukti_tidak_valid_dengan_catatan_minta_unggah_ulang(): void
    {
        [$admin, , $reservasi] = $this->reservasiMenungguVerifikasi();

        $this->actingAs($admin)
            ->post('/admin/reservasi/'.$reservasi->id.'/verifikasi', [
                'keputusan' => 'tidak_valid',
                'catatan' => 'Bukti tidak terbaca, mohon unggah ulang.',
            ])
            ->assertSessionHas('success');

        $pembayaran = $reservasi->fresh()->pembayaran;

        $this->assertSame('tidak_valid', $pembayaran->status);
        $this->assertSame('Bukti tidak terbaca, mohon unggah ulang.', $pembayaran->catatan);
        $this->assertSame($admin->id, $pembayaran->diverifikasi_oleh);
        $this->assertSame('menunggu_pembayaran', $reservasi->fresh()->status);
    }

    public function test_keputusan_tidak_dikenal_ditolak(): void
    {
        [$admin, , $reservasi] = $this->reservasiMenungguVerifikasi();

        $this->actingAs($admin)
            ->post('/admin/reservasi/'.$reservasi->id.'/verifikasi', ['keputusan' => 'mungkin'])
            ->assertSessionHasErrors('keputusan');

        $this->assertSame('menunggu_verifikasi', $reservasi->fresh()->pembayaran->status);
    }

    public function test_verifikasi_pembayaran_yang_tidak_menunggu_verifikasi_ditolak(): void
    {
        $admin = $this->pengguna('admin');
        $reservasi = Reservasi::factory()->create(['status' => 'menunggu_pembayaran']);
        Pembayaran::firstOrCreate(['reservasi_id' => $reservasi->id], [
            'jumlah' => $reservasi->total_harga,
            'status' => 'menunggu_pembayaran',
        ]);

        $this->actingAs($admin)
            ->post('/admin/reservasi/'.$reservasi->id.'/verifikasi', ['keputusan' => 'valid'])
            ->assertSessionHasErrors('keputusan');

        $this->assertSame('menunggu_pembayaran', $reservasi->fresh()->pembayaran->status);
    }

    public function test_pelanggan_tidak_bisa_memverifikasi_pembayaran(): void
    {
        [$admin, $pelanggan, $reservasi] = $this->reservasiMenungguVerifikasi();

        $this->actingAs($pelanggan)
            ->post('/admin/reservasi/'.$reservasi->id.'/verifikasi', ['keputusan' => 'valid'])
            ->assertForbidden();

        $this->assertSame('menunggu_verifikasi', $reservasi->fresh()->pembayaran->status);
        unset($admin);
    }

    public function test_status_yang_sama_ditolak(): void
    {
        $admin = $this->pengguna('admin');
        $reservasi = Reservasi::factory()->dikonfirmasi()->create(['status' => 'dikonfirmasi']);

        $this->actingAs($admin)
            ->post('/admin/reservasi/'.$reservasi->id.'/status', ['status' => 'dikonfirmasi'])
            ->assertSessionHasErrors('status');
    }

    public function test_status_tidak_dikenal_ditolak(): void
    {
        $admin = $this->pengguna('admin');
        $reservasi = Reservasi::factory()->create(['status' => 'menunggu_pembayaran']);

        $this->actingAs($admin)
            ->post('/admin/reservasi/'.$reservasi->id.'/status', ['status' => 'entah'])
            ->assertSessionHasErrors('status');

        $this->assertSame('menunggu_pembayaran', $reservasi->fresh()->status);
    }

    public function test_status_ditolak_melepas_jadwal(): void
    {
        $admin = $this->pengguna('admin');
        $jadwal = $this->jadwal();
        $reservasi = Reservasi::factory()->untukJadwal($jadwal)->create([
            'status' => 'menunggu_verifikasi',
        ]);

        $this->actingAs($admin)
            ->post('/admin/reservasi/'.$reservasi->id.'/status', ['status' => 'ditolak'])
            ->assertSessionHas('success');

        $reservasi->refresh();
        $this->assertSame('ditolak', $reservasi->status);
        $this->assertNull($reservasi->jadwal_id);
    }

    public function test_daftar_reservasi_admin_bisa_difilter_status(): void
    {
        $admin = $this->pengguna('admin');
        Reservasi::factory()->dikonfirmasi()->create(['status' => 'dikonfirmasi']);
        Reservasi::factory()->create(['status' => 'menunggu_pembayaran']);

        $this->actingAs($admin)
            ->get('/admin/reservasi?status=dikonfirmasi')
            ->assertOk()
            ->assertViewHas('reservasis', fn ($r) => $r->count() === 1 && $r->first()->status === 'dikonfirmasi');
    }
}