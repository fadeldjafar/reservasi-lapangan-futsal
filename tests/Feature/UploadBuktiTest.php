<?php

namespace Tests\Feature;

use App\Models\Pembayaran;
use App\Models\Reservasi;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UploadBuktiTest extends TestCase
{
    private function reservasiPelanggan(array $statusReservasi = ['menunggu_pembayaran'], ?Pembayaran $pembayaran = null): array
    {
        $pelanggan = $this->pengguna();
        $reservasi = Reservasi::factory()->untukJadwal($this->jadwal())->create([
            'user_id' => $pelanggan->id,
            'status' => $statusReservasi[0],
        ]);

        return [$pelanggan->fresh(), $reservasi->fresh()];
    }

    public function test_pelanggan_dapat_mengunggah_bukti_pembayaran(): void
    {
        [$pelanggan, $reservasi] = $this->reservasiPelanggan();

        $response = $this->actingAs($pelanggan)->post(
            '/reservasi/'.$reservasi->id.'/pembayaran',
            ['bukti' => $this->fileGambarPng()]
        );

        $response->assertRedirect('/reservasi/'.$reservasi->id);
        $response->assertSessionHas('success');

        $pembayaran = $reservasi->fresh()->pembayaran;

        $this->assertSame('menunggu_verifikasi', $pembayaran->status);
        $this->assertNotNull($pembayaran->bukti_transfer);
        $this->assertStringStartsWith('uploads/bukti/bukti_'.$reservasi->id.'_', $pembayaran->bukti_transfer);
        $this->assertFileExists(public_path($pembayaran->bukti_transfer));

        $this->assertSame('menunggu_verifikasi', $reservasi->fresh()->status);
    }

    public function test_berkas_bukan_gambar_ditolak(): void
    {
        [$pelanggan, $reservasi] = $this->reservasiPelanggan();

        $response = $this->actingAs($pelanggan)->post(
            '/reservasi/'.$reservasi->id.'/pembayaran',
            ['bukti' => UploadedFile::fake()->create('bukti.pdf', 10, 'application/pdf')]
        );

        $response->assertSessionHasErrors('bukti');
        $this->assertNull($reservasi->fresh()->pembayaran->bukti_transfer);
        $this->assertSame('menunggu_pembayaran', $reservasi->fresh()->status);
    }

    public function test_berkas_lebih_dari_2mb_ditolak(): void
    {
        [$pelanggan, $reservasi] = $this->reservasiPelanggan();

        // > 2 MB dengan ekstensi .png
        $besar = UploadedFile::fake()->create('bukti_kecil.png', 2049);

        $response = $this->actingAs($pelanggan)->post(
            '/reservasi/'.$reservasi->id.'/pembayaran',
            ['bukti' => $besar]
        );

        $response->assertSessionHasErrors('bukti');
        $this->assertNull($reservasi->fresh()->pembayaran->bukti_transfer);
    }

    public function test_bukti_wajib_diunggah(): void
    {
        [$pelanggan, $reservasi] = $this->reservasiPelanggan();

        $this->actingAs($pelanggan)
            ->post('/reservasi/'.$reservasi->id.'/pembayaran')
            ->assertSessionHasErrors('bukti');
    }

    public function test_tidak_bisa_mengunggah_ulang_saat_sedang_menunggu_verifikasi(): void
    {
        [$pelanggan, $reservasi] = $this->reservasiPelanggan(['menunggu_verifikasi']);
        $sebelum = $reservasi->pembayaran->bukti_transfer;

        $response = $this->actingAs($pelanggan)->post(
            '/reservasi/'.$reservasi->id.'/pembayaran',
            ['bukti' => $this->fileGambarPng()]
        );

        $response->assertSessionHasErrors('bukti');
        $this->assertSame($sebelum, $reservasi->fresh()->pembayaran->bukti_transfer);
    }

    public function test_orang_lain_tidak_bisa_mengunggah_bukti_reservasi_orang_lain(): void
    {
        [$pemilik, $reservasi] = $this->reservasiPelanggan();
        $orangLain = $this->pengguna();

        $this->actingAs($orangLain)
            ->post('/reservasi/'.$reservasi->id.'/pembayaran', ['bukti' => $this->fileGambarPng()])
            ->assertForbidden();

        $this->assertNull($reservasi->fresh()->pembayaran->bukti_transfer);
    }

    public function test_tamu_tidak_bisa_mengunggah_bukti(): void
    {
        [, $reservasi] = $this->reservasiPelanggan();

        $this->post('/reservasi/'.$reservasi->id.'/pembayaran', ['bukti' => $this->fileGambarPng()])
            ->assertRedirect('/login');

        $this->assertNull($reservasi->fresh()->pembayaran->bukti_transfer);
    }

    public function test_unggah_ulang_setelah_ditolak_menghapus_berkas_lama(): void
    {
        [$pelanggan, $reservasi] = $this->reservasiPelanggan();

        $lama = 'uploads/bukti/bukti_'.$reservasi->id.'_lama.png';
        file_put_contents(public_path($lama), 'PLACEHOLDER');
        $reservasi->pembayaran->update([
            'bukti_transfer' => $lama,
            'status' => 'tidak_valid',
        ]);

        $this->assertFileExists(public_path($lama));

        $this->actingAs($pelanggan)->post(
            '/reservasi/'.$reservasi->id.'/pembayaran',
            ['bukti' => $this->fileGambarPng()]
        )->assertRedirect('/reservasi/'.$reservasi->id);

        $pembayaran = $reservasi->fresh()->pembayaran;

        $this->assertSame('menunggu_verifikasi', $pembayaran->status);
        $this->assertNotSame($lama, $pembayaran->bukti_transfer);
        $this->assertFileDoesNotExist(public_path($lama), 'Berkas bukti lama harus dihapus.');
        $this->assertFileExists(public_path($pembayaran->bukti_transfer));
    }

    public function test_berkas_lama_di_folder_bukti_ikut_ada_saat_test(): void
    {
        // Guard: helper test benar-benar menulis ke folder upload publik.
        [$pelanggan, $reservasi] = $this->reservasiPelanggan();

        $this->actingAs($pelanggan)->post(
            '/reservasi/'.$reservasi->id.'/pembayaran',
            ['bukti' => $this->fileGambarPng()]
        );

        $pembayaran = $reservasi->fresh()->pembayaran;

        $this->assertStringStartsWith('uploads/bukti/', $pembayaran->bukti_transfer);
        $this->assertFileExists(public_path($pembayaran->bukti_transfer));
    }
}