# Sistem Informasi Reservasi Lapangan Futsal

Aplikasi web untuk mengelola pemesanan lapangan futsal: pelanggan memilih slot
waktu, mengunggah bukti transfer, petugas memverifikasi pembayaran, dan pemilik
membaca laporan pendapatan serta tingkat_okupansi lapangan.

Dibangun dengan **Laravel 12** dan **MySQL** (XAMPP/Laragon), tanpa framework
CSS eksternal (tampilan memakai Blade + CSS inline).

---

## Fitur

### Publik (tanpa login)
- Beranda aplikasi
- Daftar lapangan aktif + detail lapangan beserta jadwal dan status ketersediaan

### Pelanggan
- Registrasi (otomatis berperan `pelanggan`) dan login/logout
- Melihat riwayat reservasi miliknya saja
- Memesan slot jadwal yang masih kosong
- Mengunggah bukti transfer (JPG/PNG, maksimal 2 MB)
- Mengunggah ulang bukti ketika petugas menandainya tidak valid

### Admin / Petugas
- Dashboard ringkasan (reservasi per status, pelanggan, lapangan, jadwal)
- CRUD lapangan dan jadwal (jadwal yang sudah dipesan terkunci dari ubah/hapus)
- Daftar & detail pelanggan beserta riwayat reservasinya
- Verifikasi bukti pembayaran: **valid** (reservasi dikonfirmasi) atau
  **tidak valid** (wajib catatan, pelanggan diminta unggah ulang)
- Ubah status reservasi manual; status `dibatalkan`/`ditolak` melepas slot jadwal

### Pemilik
- Dashboard laporan pendapatan (default bulan berjalan, bisa difilter `?dari=&sampai=`)
- Laporan penggunaan lapangan: jumlah jadwal, jumlah terpakai, okupansi (%)
- Daftar seluruh reservasi + filter status

### Keamanan
- Password di-hash (`bcrypt`), sesi di-regenerate saat login/register
- Otorisasi berbasis role lewat middleware `role` (`app/Http/Middleware/RoleMiddleware.php`)
- Anti double-booking berlapis: pemeriksaan ketersediaan di aplikasi **dan**
  constraint `UNIQUE (jadwal_id)` di database sebagai penjaga terakhir
- Validasi file unggahan (tipe gambar + batas ukuran) dan pesan validasi Bahasa Indonesia

---

## Kebutuhan Sistem

- PHP **8.2+** dengan ekstensi `pdo_mysql` (XAMPP sudah termasuk)
- Composer 2
- MySQL 8 / MariaDB 10.4+ (XAMPP atau Laragon)

---

## Cara Instalasi

```bash
# 1. Ambil dependensi
composer install

# 2. Siapkan konfigurasi
cp .env.example .env
php artisan key:generate

# 3. Buat database (lewat phpMyAdmin atau SQL)
#    CREATE DATABASE reservasi_futsal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
#    lalu sesuaikan DB_USERNAME / DB_PASSWORD di .env

# 4. Buat tabel + isi data contoh
php artisan migrate --seed

# 5. Jalankan
php artisan serve
```

Buka `http://127.0.0.1:8000`.

> Folder `public/uploads/bukti/` sudah ada di repository (berisi `.gitignore`
> saja) sehingga aplikasi langsung bisa menerima unggahan bukti.

---

## Akun Demo

| Peran | Email | Password |
| --- | --- | --- |
| Admin / Petugas | `admin@futsal.test` | `admin12345` |
| Pemilik | `pemilik@futsal.test` | `pemilik12345` |
| Pelanggan | `budi@futsal.test` | `password123` |
| Pelanggan | `sari@futsal.test` | `password123` |
| Pelanggan | `andi@futsal.test` | `password123` |

Data contoh dari seeder: 2 lapangan, 49 jadwal (7 hari ke depan), dan 4 reservasi
yang mencakup keempat status pembayaran.

---

## Menjalankan Test

Test otomatis memakai **MySQL** (bukan SQLite) supaya perilaku constraint
seperti production benar-benar teruji. Buat dulu database khusus test:

```sql
CREATE DATABASE reservasi_futsal_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Kredensialnya ada di `.env.testing` (sesuaikan bila perlu). Database ini **wajib
berbeda** dari database aplikasi karena tabelnya dibangun ulang setiap test.

```bash
php artisan test
```

Rincian cakupan test (74 test / 272 assertion):

| Berkas | Cakupan |
| --- | --- |
| `tests/Feature/AuthTest.php` | Registrasi, login tiap role, validasi, logout, redirect tamu |
| `tests/Feature/RoleAuthorizationTest.php` | 403 untuk role yang salah, termasuk aksi tulis admin |
| `tests/Feature/ReservasiTest.php` | Pemesanan slot, harga = tarif × durasi, anti double-booking, isolasi data pelanggan |
| `tests/Feature/UploadBuktiTest.php` | Unggah JPG/PNG, tolak PDF & berkas > 2 MB, unggah ulang menghapus berkas lama |
| `tests/Feature/AdminVerifikasiPembayaranTest.php` | Verifikasi valid/tidak_valid, catatan wajib, perubahan status & pelepasan slot |
| `tests/Feature/AdminKelolaTest.php` | CRUD lapangan & jadwal, penguncian jadwal terpesan, hapus pelanggan |
| `tests/Feature/PemilikLaporanTest.php` | Pendapatan, okupansi, filter periode (termasuk parameter tak sah) |
| `tests/Unit/JadwalTest.php` | Perhitungan durasi jam & status ketersediaan slot |

### Uji manual end-to-end

`uji-e2e.sh` menjalankan 116 pemeriksaan HTTP terhadap server yang sedang hidup
(alur pemesanan → unggah bukti → verifikasi admin → laporan pemilik → otorisasi
3 role → pembersihan data uji). Jalankan setelah `php artisan serve` aktif:

```bash
php artisan serve            # terminal 1
bash uji-e2e.sh              # terminal 2
```

Skrip ini membuat data uji berawalan `uji7*@futsal.test` lalu membersihkannya di
bagian akhir; data demo tidak diubah.

---

## Struktur Proyek

```
app/
  Http/Controllers/          Auth, Lapangan, Reservasi + folder Admin/ & Pemilik/
  Http/Middleware/           RoleMiddleware
  Models/                    User, Lapangan, Jadwal, Reservasi, Pembayaran
database/
  migrations/                7 migration (users, lapangan, jadwal, reservasi, pembayaran, cache, jobs)
  seeders/                   User, Lapangan, Reservasi
  factories/                 Factory untuk seluruh model
resources/views/             layouts, auth, lapangan, reservasi, admin/, pemilik/, errors/
routes/web.php               Seluruh definisi route + middleware
tests/                       Unit & Feature test
```

---

## Catatan Teknis

- **`jadwal_id` nullable + unique** — kolom ini sengaja dibuat nullable supaya
  slot yang dibatalkan/ditolak bisa dilepas (`NULL` tidak dihitung `UNIQUE` di MySQL).
- **Bukti transfer disimpan di `public/uploads/bukti/`** — bukan
  `storage/app/public`, supaya tidak perlu `php artisan storage:link` (symlink
  merepotkan di Windows).
- **Tanpa GD** — seeder membuat contoh gambar PNG secara manual (`gzcompress` +
  CRC32) sehingga tidak perlu ekstensi GD; sama juga dipakai helper test.
- **Tampilan** memakai CSS inline di `layouts/app.blade.php`, jadi tidak ada
  proses build Vite yang perlu dijalankan.