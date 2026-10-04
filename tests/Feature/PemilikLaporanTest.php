<?php

namespace Tests\Feature;

use App\Models\Reservasi;
use Tests\TestCase;

class PemilikLaporanTest extends TestCase
{
    public function test_dashboard_pemilik_menampilkan_ringkasan_dan_pendapatan(): void
    {
        $pemilik = $this->pengguna('pemilik');

        // Reservasi dikonfirmasi di bulan berjalan: masuk laporan pendapatan.
        $jadwalBulanIni = $this->jadwal($this->lapangan(['harga_per_jam' => 100000]), [
            'tanggal' => now()->startOfMonth()->addDay()->toDateString(),
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
        ]);
        $dikonfirmasi = Reservasi::factory()->dikonfirmasi()->create([
            'jadwal_id' => $jadwalBulanIni->id,
            'total_harga' => 200000,
            'status' => 'dikonfirmasi',
        ]);

        // Reservasi menunggu: tidak menambah pendapatan.
        $jadwalMenunggu = $this->jadwal($this->lapangan(['harga_per_jam' => 50000]), [
            'tanggal' => now()->startOfMonth()->addDay()->toDateString(),
            'jam_mulai' => '13:00',
            'jam_selesai' => '14:00',
        ]);
        Reservasi::factory()->create([
            'jadwal_id' => $jadwalMenunggu->id,
            'total_harga' => 50000,
            'status' => 'menunggu_pembayaran',
        ]);

        $response = $this->actingAs($pemilik)->get('/pemilik/dashboard');

        $response->assertOk();
        $response->assertViewHas('pendapatanPeriode', 200000);
        $response->assertViewHas('jumlahPeriode', 1);
        $response->assertViewHas('ringkasan', function ($ringkasan) {
            return $ringkasan['reservasi'] === 2
                && $ringkasan['dikonfirmasi'] === 1
                && $ringkasan['menunggu'] === 1
                && $ringkasan['pelanggan'] >= 1;
        });

        $response->assertSee('200.000');
        unset($dikonfirmasi);
    }

    public function test_laporan_bisa_difilter_periode(): void
    {
        $pemilik = $this->pengguna('pemilik');

        $jadwalLama = $this->jadwal($this->lapangan(['harga_per_jam' => 100000]), [
            'tanggal' => now()->subMonth()->startOfMonth()->addDay()->toDateString(),
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
        ]);
        Reservasi::factory()->dikonfirmasi()->create([
            'jadwal_id' => $jadwalLama->id,
            'total_harga' => 200000,
            'status' => 'dikonfirmasi',
        ]);

        // Default: bulan berjalan -> reservasi bulan lalu tidak dihitung.
        $this->actingAs($pemilik)
            ->get('/pemilik/dashboard')
            ->assertViewHas('pendapatanPeriode', 0);

        // Filter eksplisit mencakup bulan lalu.
        $dari = now()->subMonth()->startOfMonth()->toDateString();
        $sampai = now()->endOfMonth()->toDateString();

        $this->actingAs($pemilik)
            ->get('/pemilik/dashboard?dari='.$dari.'&sampai='.$sampai)
            ->assertOk()
            ->assertViewHas('pendapatanPeriode', 200000)
            ->assertViewHas('dari', $dari)
            ->assertViewHas('sampai', $sampai);
    }

    public function test_okupansi_lapangan_dihitung_dari_jadwal_terpakai(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $tanggal = now()->startOfMonth()->addDay()->toDateString();

        $lapangan = $this->lapangan(['nama' => 'Lapangan A', 'harga_per_jam' => 50000]);
        $this->jadwal($lapangan, ['tanggal' => $tanggal, 'jam_mulai' => '08:00', 'jam_selesai' => '09:00']);
        $jadwalTerpakai = $this->jadwal($lapangan, ['tanggal' => $tanggal, 'jam_mulai' => '09:00', 'jam_selesai' => '10:00']);
        $this->jadwal($lapangan, ['tanggal' => $tanggal, 'jam_mulai' => '10:00', 'jam_selesai' => '11:00']);
        $this->jadwal($lapangan, ['tanggal' => $tanggal, 'jam_mulai' => '11:00', 'jam_selesai' => '12:00']);

        Reservasi::factory()->dikonfirmasi()->create([
            'jadwal_id' => $jadwalTerpakai->id,
            'total_harga' => 50000,
            'status' => 'dikonfirmasi',
        ]);

        $this->actingAs($pemilik)->get('/pemilik/dashboard')->assertOk()
            ->assertViewHas('laporan', function ($laporan) {
                $baris = $laporan->firstWhere('nama', 'Lapangan A');

                return $baris !== null
                    && $baris['jadwal'] === 4
                    && $baris['terpakai'] === 1
                    && $baris['okupansi'] === 25
                    && $baris['pendapatan'] === 50000;
            });
    }

    public function test_daftar_reservasi_pemilik_bisa_difilter_status(): void
    {
        $pemilik = $this->pengguna('pemilik');
        Reservasi::factory()->dikonfirmasi()->create(['status' => 'dikonfirmasi']);
        Reservasi::factory()->create(['status' => 'menunggu_pembayaran']);

        $this->actingAs($pemilik)
            ->get('/pemilik/reservasi?status=menunggu_pembayaran')
            ->assertOk()
            ->assertViewHas('reservasis', fn ($r) => $r->count() === 1 && $r->first()->status === 'menunggu_pembayaran');
    }

    public function test_parameter_periode_tidak_sah_diabaikan_dan_diurutkan(): void
    {
        $pemilik = $this->pengguna('pemilik');

        // Tanggal terbalik harus ditukar, bukan error.
        $this->actingAs($pemilik)
            ->get('/pemilik/dashboard?dari=2026-12-31&sampai=2026-01-01')
            ->assertOk()
            ->assertViewHas('dari', '2026-01-01')
            ->assertViewHas('sampai', '2026-12-31');

        // Format salah -> jatuh ke default bulan berjalan.
        $this->actingAs($pemilik)
            ->get('/pemilik/dashboard?dari=bukan-tanggal')
            ->assertOk()
            ->assertViewHas('dari', now()->startOfMonth()->toDateString());
    }
}