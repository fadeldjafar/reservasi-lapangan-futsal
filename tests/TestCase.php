<?php

namespace Tests;

use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Folder tempat file bukti pembayaran yang diunggah selama test.
     */
    protected string $folderBukti;

    /**
     * Daftar berkas bukti yang sudah ada sebelum test berjalan.
     *
     * @var array<int, string>
     */
    protected array $buktiSebelumTest = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->folderBukti = public_path('uploads/bukti');

        if (! is_dir($this->folderBukti)) {
            mkdir($this->folderBukti, 0755, true);
        }

        $this->buktiSebelumTest = $this->daftarBerkasBukti();
    }

    protected function tearDown(): void
    {
        // BerkasController menamai berkas "bukti_<id>_<waktu>_<random>.<ext>",
        // jadi bersihkan berkas baru yang muncul selama test berjalan
        // (bukti lama dari seeder/demo tidak boleh ikut terhapus).
        $sebelum = array_flip($this->buktiSebelumTest);

        foreach ($this->daftarBerkasBukti() as $file) {
            if (! isset($sebelum[$file])) {
                @unlink($file);
            }
        }

        parent::tearDown();
    }

    /**
     * @return array<int, string>
     */
    protected function daftarBerkasBukti(): array
    {
        if (! is_dir($this->folderBukti)) {
            return [];
        }

        return array_values(array_filter(glob($this->folderBukti.'/*') ?: [], 'is_file'));
    }

    // ---------------------------------------------------------------------
    // Helper data uji
    // ---------------------------------------------------------------------

    protected function pengguna(string $role = 'pelanggan'): User
    {
        return User::factory()->create(['role' => $role]);
    }

    protected function lapangan(array $atribut = []): Lapangan
    {
        return Lapangan::factory()->create($atribut);
    }

    protected function jadwal(?Lapangan $lapangan = null, array $atribut = []): Jadwal
    {
        return Jadwal::factory()->create(array_merge([
            'lapangan_id' => ($lapangan ?? $this->lapangan())->id,
        ], $atribut));
    }

    protected function reservasi(array $atribut = []): Reservasi
    {
        return Reservasi::factory()->create($atribut)->fresh();
    }

    /**
     * File gambar PNG asli (tanpa ekstensi GD) untuk simulasi unggah bukti.
     */
    protected function fileGambarPng(string $nama = 'bukti_test.png', int $lebar = 8, int $tinggi = 8): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($nama, $this->isiPng($lebar, $tinggi));
    }

    /**
     * Susun PNG berwarna polos tanpa library GD.
     */
    protected function isiPng(int $lebar, int $tinggi): string
    {
        $baris = '';
        for ($y = 0; $y < $tinggi; $y++) {
            $baris .= "\x00"; // filter: none
            for ($x = 0; $x < $lebar; $x++) {
                $baris .= "\x89\xBD\xD0"; // RGB
            }
        }

        $chunk = function (string $tipe, string $data): string {
            return pack('N', strlen($data)).$tipe.$data.pack('N', crc32($tipe.$data));
        };

        return "\x89PNG\r\n\x1a\n"
            .$chunk('IHDR', pack('NNCCCCC', $lebar, $tinggi, 8, 2, 0, 0, 0))
            .$chunk('IDAT', gzcompress($baris, 9))
            .$chunk('IEND', '');
    }
}