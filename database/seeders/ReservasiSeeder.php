<?php

namespace Database\Seeders;

use App\Models\Jadwal;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReservasiSeeder extends Seeder
{
    /**
     * Contoh reservasi pada berbagai status, lengkap dengan bukti pembayaran.
     */
    public function run(): void
    {
        if (Reservasi::count() > 0) {
            return; // data reservasi sudah ada, tidak diulang
        }

        $admin = User::where('role', 'admin')->first();
        $budi = User::where('email', 'budi@futsal.test')->first();
        $sari = User::where('email', 'sari@futsal.test')->first();
        $andi = User::where('email', 'andi@futsal.test')->first();

        if (! $admin || ! $budi || ! $sari || ! $andi) {
            return; // akun demo belum ada, jalankan UserSeeder dulu
        }

        // Empat slot di hari berbeda yang masih kosong.
        $slots = Jadwal::where('tanggal', '>=', now()->toDateString())
            ->whereDoesntHave('reservasi')
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get()
            ->unique(fn (Jadwal $j) => $j->tanggal->toDateString())
            ->take(4)
            ->values();

        $contoh = [
            // Sudah diverifikasi & dikonfirmasi (terhitung di laporan pendapatan).
            ['user' => $budi, 'status' => 'dikonfirmasi', 'bayar' => 'valid', 'bukti' => true, 'catatan' => null],
            // Bukti sudah diunggah, menunggu verifikasi petugas.
            ['user' => $sari, 'status' => 'menunggu_verifikasi', 'bayar' => 'menunggu_verifikasi', 'bukti' => true, 'catatan' => null],
            // Belum membayar sama sekali.
            ['user' => $andi, 'status' => 'menunggu_pembayaran', 'bayar' => 'menunggu_pembayaran', 'bukti' => false, 'catatan' => null],
            // Bukti ditandai tidak valid, pelanggan diminta unggah ulang.
            ['user' => $budi, 'status' => 'menunggu_pembayaran', 'bayar' => 'tidak_valid', 'bukti' => true, 'catatan' => 'Bukti tidak terbaca, mohon unggah ulang.'],
        ];

        foreach ($contoh as $i => $c) {
            if (! isset($slots[$i])) {
                break;
            }

            $jadwal = $slots[$i];
            $total = $jadwal->lapangan->harga_per_jam * $jadwal->durasiJam();

            $reservasi = Reservasi::create([
                'user_id' => $c['user']->id,
                'jadwal_id' => $jadwal->id,
                'total_harga' => $total,
                'status' => $c['status'],
            ]);

            $pembayaran = Pembayaran::create([
                'reservasi_id' => $reservasi->id,
                'jumlah' => $total,
                'nama_pengirim' => $c['user']->name,
                'bukti_transfer' => $c['bukti'] ? $this->simpanBuktiDemo($i + 1) : null,
                'status' => $c['bayar'],
                'catatan' => $c['catatan'],
            ]);

            if (in_array($c['bayar'], ['valid', 'tidak_valid'], true)) {
                $pembayaran->update([
                    'diverifikasi_oleh' => $admin->id,
                    'diverifikasi_pada' => now(),
                ]);
            }
        }
    }

    /**
     * Gambar PNG sederhana sebagai contoh bukti transfer (tanpa library GD).
     */
    private function simpanBuktiDemo(int $nomor): string
    {
        $relatif = "uploads/bukti/bukti_demo_{$nomor}.png";
        $absolut = public_path($relatif);

        if (is_file($absolut)) {
            return $relatif;
        }

        if (! is_dir(dirname($absolut))) {
            mkdir(dirname($absolut), 0755, true);
        }

        file_put_contents($absolut, $this->pngSolid(480, 240, [204, 225, 255]));

        return $relatif;
    }

    /**
     * Susun file PNG berwarna polos tanpa ekstensi GD.
     *
     * @param  array{0: int, 1: int, 2: int}  $warna
     */
    private function pngSolid(int $lebar, int $tinggi, array $warna): string
    {
        $baris = '';
        for ($y = 0; $y < $tinggi; $y++) {
            $baris .= "\x00"; // filter: none
            for ($x = 0; $x < $lebar; $x++) {
                $baris .= chr($warna[0]).chr($warna[1]).chr($warna[2]);
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
