#!/usr/bin/env bash
# E2E Tahap 7: alur penuh aplikasi + otorisasi + anti double-booking + cleanup.
set -u
cd /d/laragon/www/FreebuffFadel/ai-agent-app
export PATH="/d/laragon/bin/php/php-8.3.33-Win32-vs16-x64:$HOME/bin:$PATH"

BASE=http://127.0.0.1:8000
J=/tmp/cj_t7.txt
# Folder kerja uji: /tmp milik MSYS tidak terlihat oleh PHP Windows.
WORK=storage/uji_t7
mkdir -p "$WORK"
rm -f "$J"

PASS=0; FAIL=0
chk() { if [ "$2" = "$3" ]; then echo "  [OK]   $1 (= $3)"; PASS=$((PASS+1));
  else echo "  [FAIL] $1 | expected='$2' actual='$3'"; FAIL=$((FAIL+1)); fi; }
chk_has() { if grep -qi "$2" /tmp/p.html; then echo "  [OK]   $1 (ditemukan: $2)"; PASS=$((PASS+1));
  else echo "  [FAIL] $1 | '$2' tidak ditemukan"; FAIL=$((FAIL+1)); fi; }
chk_not() { if grep -qi "$2" /tmp/p.html; then echo "  [FAIL] $1 | '$2' seharusnya tidak ada"; FAIL=$((FAIL+1));
  else echo "  [OK]   $1 (tidak ada: $2)"; PASS=$((PASS+1)); fi; }
get() { curl -s -o /tmp/p.html -w '%{http_code}' -b "$J" -c "$J" -D /tmp/hdr.txt "$1"; }
# post URL [curl-args...]  -> POST
# post METHOD URL [curl-args...]
post() {
  local m u
  if [ $# -ge 2 ] && printf '%s' "$1" | grep -qiE '^(GET|POST|PUT|PATCH|DELETE|HEAD)$'; then
    m="$1"; u="$2"; shift 2
  else
    m="POST"; u="$1"; shift
  fi
  curl -s -o /tmp/p.html -w '%{http_code}' -b "$J" -c "$J" -D /tmp/hdr.txt -X "$m" "$u" "$@"
}
token() { grep -o 'name="_token" value="[^"]*"' /tmp/p.html | head -1 | sed 's/.*value="//;s/"$//'; }
# Unggah berkas: CSRF token wajib ikut sebagai field multipart (-F),
# kalau tidak body-nya bukan multipart dan Laravel menolak.
upload() {
  local u="$1" tok="$2" f="$3"
  curl -s -o /tmp/p.html -w '%{http_code}' -b "$J" -c "$J" -D /tmp/hdr.txt \
    -F "_token=$tok" -F "bukti=@$f" "$u"
}
loc() { grep -i '^location:' /tmp/hdr.txt | tail -1 | tr -d '\r' | sed -E 's|.*://[^/]*||'; }
q() { php -r "\$p=new PDO('mysql:host=127.0.0.1;dbname=reservasi_futsal','root','');\$r=\$p->query(\"$1\")->fetchColumn();echo \$r===false?'ERR':\$r;"; }
# ID reservasi uji yang berhasil dibuat (dipakai di query yang butuh angka).
idlist() { echo "$RES1${RES2:+,$RES2}${RES3:+,$RES3}" | sed 's/^,//;s/,,*/,/g' | grep -q '[0-9]' || echo 0; }
sqlval() { php -r "\$p=new PDO('mysql:host=127.0.0.1;dbname=reservasi_futsal','root','');\$s=\$p->prepare(\"$1\");\$s->execute([$2]);echo \$s->fetchColumn();"; }

login() {
  get $BASE/login > /dev/null
  if ! grep -q 'name="_token"' /tmp/p.html; then
    get $BASE/dashboard > /dev/null
    local to=$(token); [ -n "$to" ] && curl -s -o /dev/null -b "$J" -c "$J" -X POST "$BASE/logout" --data-urlencode "_token=$to"
    get $BASE/login > /dev/null
  fi
  local t=$(token)
  curl -s -o /dev/null -b "$J" -c "$J" -X POST "$BASE/login" \
    --data-urlencode "_token=$t" --data-urlencode "email=$1" --data-urlencode "password=$2"
}
logout() { get $BASE/dashboard > /dev/null; local t=$(token); [ -n "$t" ] && curl -s -o /dev/null -b "$J" -c "$J" -X POST "$BASE/logout" --data-urlencode "_token=$t"; }

EMAIL1=uji7a@futsal.test
EMAIL2=uji7b@futsal.test
RES1=""; RES2=""

echo "=== 1. Tamu tidak bisa akses halaman privat ==="
chk "GET /dashboard -> 302" "302" "$(get $BASE/dashboard)"
chk "redirect ke /login" "/login" "$(loc)"
chk "GET /admin/dashboard -> 302" "302" "$(get $BASE/admin/dashboard)"
chk "GET /pemilik/dashboard -> 302" "302" "$(get $BASE/pemilik/dashboard)"

echo "=== 2. Registrasi 2 pelanggan baru ==="
get $BASE/register > /dev/null; T=$(token)
chk "registrasi uji7a" "302" "$(post $BASE/register --data-urlencode "_token=$T" --data-urlencode "name=Uji Tujuh A" --data-urlencode "email=$EMAIL1" --data-urlencode "telepon=081200000001" --data-urlencode "password=ujitujuh123" --data-urlencode "password_confirmation=ujitujuh123")"
chk "user uji7a dibuat sbg pelanggan" "pelanggan" "$(q "select role from users where email='$EMAIL1'")"
logout

get $BASE/register > /dev/null; T=$(token)
chk "registrasi uji7b" "302" "$(post $BASE/register --data-urlencode "_token=$T" --data-urlencode "name=Uji Tujuh B" --data-urlencode "email=$EMAIL2" --data-urlencode "telepon=081200000002" --data-urlencode "password=ujitujuh123" --data-urlencode "password_confirmation=ujitujuh123")"
logout

echo "=== 3. Validasi registrasi ==="
get $BASE/register > /dev/null; T=$(token)
chk "email duplikat ditolak" "302" "$(post $BASE/register --data-urlencode "_token=$T" --data-urlencode "name=Dup" --data-urlencode "email=$EMAIL1" --data-urlencode "password=ujitujuh123" --data-urlencode "password_confirmation=ujitujuh123")"
get $BASE/register > /dev/null; chk_has "pesan email sudah terdaftar" "sudah terdaftar" /dev/null
get $BASE/register > /dev/null; T=$(token)
post $BASE/register --data-urlencode "_token=$T" --data-urlencode "name=Salah" --data-urlencode "email=uji7salah@futsal.test" --data-urlencode "password=123" --data-urlencode "password_confirmation=123" > /dev/null
get $BASE/register > /dev/null; chk_has "pesan password minimal 8" "minimal 8 karakter" /dev/null

echo "=== 4. Pelanggan A memesan slot (alur pemesanan) ==="
login "$EMAIL1" "ujitujuh123"
chk "dashboard pelanggan" "200" "$(get $BASE/dashboard)"
# ambil ID lapangan + jadwal kosong di masa depan
LP=$(php -r '$p=new PDO("mysql:host=127.0.0.1;dbname=reservasi_futsal","root","");echo $p->query("select lapangan.id from lapangan where status=\"aktif\" order by id limit 1")->fetchColumn();')
JP=$(php -r "\$p=new PDO('mysql:host=127.0.0.1;dbname=reservasi_futsal','root','');echo \$p->query('select j.id from jadwal j left join reservasi r on r.jadwal_id=j.id where j.lapangan_id=$LP and j.tanggal >= curdate() and r.id is null order by j.tanggal, j.jam_mulai limit 1')->fetchColumn();")
HARGALP=$(q "select harga_per_jam from lapangan where id=$LP")
DURASI=$(php -r "\$p=new PDO('mysql:host=127.0.0.1;dbname=reservasi_futsal','root','');\$r=\$p->query('select jam_mulai,jam_selesai from jadwal where id=$JP')->fetch();echo max(1,(int)substr(\$r['jam_selesai'],0,2)-(int)substr(\$r['jam_mulai'],0,2));")
TOTAL=$((HARGALP*DURASI))
echo "  (lapangan=$LP jadwal=$JP durasi=${DURASI}j harga=$HARGALP total=$TOTAL)"

get $BASE/lapangan/$LP > /dev/null; T=$(token)
chk "form reservasi terkirim" "302" "$(post $BASE/lapangan/$LP/jadwal/$JP/reservasi --data-urlencode "_token=$T")"
RES1=$(q "select id from reservasi where jadwal_id=$JP")
[ -n "$RES1" ] && echo "  (reservasi A dibuat: #$RES1)" || echo "  (GAGAL: reservasi A tidak dibuat)"
chk "status reservasi A" "menunggu_pembayaran" "$(q "select status from reservasi where id=$RES1")"
chk "total harga A = harga x durasi" "$TOTAL" "$(q "select total_harga from reservasi where id=$RES1")"
chk "pembayaran A dibuat" "1" "$(q "select count(*) from pembayaran where reservasi_id=$RES1")"
chk "jumlah bayar A = total" "$TOTAL" "$(q "select jumlah from pembayaran where reservasi_id=$RES1")"
chk "slot A terpakai (tidak tersedia lagi)" "1" "$(q "select count(*) from jadwal where id=$JP and exists(select 1 from reservasi where jadwal_id=$JP)")"

echo "=== 5. Pelanggan B gagal pesan slot yang sama (anti double-booking) ==="
logout
login "$EMAIL2" "ujitujuh123"
get $BASE/lapangan/$LP > /dev/null; T=$(token)
chk "booking slot terpental ditolak" "302" "$(post $BASE/lapangan/$LP/jadwal/$JP/reservasi --data-urlencode "_token=$T")"
chk "reservasi B tidak dibuat" "0" "$(q "select count(*) from reservasi where jadwal_id=$JP and user_id=(select id from users where email='$EMAIL2')")"
# pesan slot lain untuk lanjut alur
JP2=$(php -r "\$p=new PDO('mysql:host=127.0.0.1;dbname=reservasi_futsal','root','');echo \$p->query('select j.id from jadwal j left join reservasi r on r.jadwal_id=j.id where j.lapangan_id=$LP and j.tanggal >= curdate() and r.id is null and j.id<>$JP order by j.tanggal, j.jam_mulai limit 1')->fetchColumn();")
get $BASE/lapangan/$LP > /dev/null; T=$(token)
post $BASE/lapangan/$LP/jadwal/$JP2/reservasi --data-urlencode "_token=$T" > /dev/null
RES2=$(q "select id from reservasi where jadwal_id=$JP2")
echo "  (reservasi B dibuat: #$RES2 dari slot #$JP2)"
logout

echo "=== 6. Validasi bukti pembayaran ==="
login "$EMAIL1" "ujitujuh123"
# PDF ditolak
get $BASE/reservasi/$RES1 > /dev/null; T=$(token)
echo "dummy-pdf" > "$WORK/bukti.pdf"
chk "upload PDF ditolak" "302" "$(upload $BASE/reservasi/$RES1/pembayaran "$T" "$WORK/bukti.pdf")"
chk "bukti masih kosong" "" "$(q "select ifnull(bukti_transfer,'') from pembayaran where reservasi_id=$RES1")"
# >2MB PNG ditolak
get $BASE/reservasi/$RES1 > /dev/null; T=$(token)
php -r '$f=fopen("storage/uji_t7/bukti_besar.png","wb");fwrite($f,str_repeat("x",2200*1024));fclose($f);'
chk "upload >2MB ditolak" "302" "$(upload $BASE/reservasi/$RES1/pembayaran "$T" "$WORK/bukti_besar.png")"
chk "bukti masih kosong (2)" "" "$(q "select ifnull(bukti_transfer,'') from pembayaran where reservasi_id=$RES1")"

echo "=== 7. Upload bukti PNG valid ==="
get $BASE/reservasi/$RES1 > /dev/null; T=$(token)
php -r '$baris="";for($y=0;$y<40;$y++){$baris.="\x00";for($x=0;$x<80;$x++){$baris.=chr(140).chr(190).chr(240);}}$c=fn($t,$d)=>pack("N",strlen($d)).$t.$d.pack("N",crc32($t.$d));file_put_contents("storage/uji_t7/bukti_uji.png","\x89PNG\r\n\x1a\n".$c("IHDR",pack("NNCCCCC",80,40,8,2,0,0,0)).$c("IDAT",gzcompress($baris,9)).$c("IEND",""));'
chk "upload PNG valid" "302" "$(upload $BASE/reservasi/$RES1/pembayaran "$T" "$WORK/bukti_uji.png")"
chk "pembayaran -> menunggu_verifikasi" "menunggu_verifikasi" "$(q "select status from pembayaran where reservasi_id=$RES1")"
chk "reservasi -> menunggu_verifikasi" "menunggu_verifikasi" "$(q "select status from reservasi where id=$RES1")"
BUKTI1=$(q "select bukti_transfer from pembayaran where reservasi_id=$RES1")
chk "berkas bukti tersimpan" "1" "$([ -f "public/$BUKTI1" ] && echo 1 || echo 0)"
chk "ekstensi .png" "png" "$(echo "$BUKTI1" | sed 's/.*\.//')"

echo "=== 8. Akses silang antar pelanggan ==="
logout
login "$EMAIL2" "ujitujuh123"
get $BASE/reservasi/$RES1 > /dev/null; T=$(token)
chk "B tak bisa buka detail reservasi A" "403" "$(get $BASE/reservasi/$RES1)"
chk "B tak bisa upload ke reservasi A" "403" "$(upload $BASE/reservasi/$RES1/pembayaran "$T" "$WORK/bukti_uji.png")"
chk "detail reservasi A utuh" "menunggu_verifikasi" "$(q "select status from pembayaran where reservasi_id=$RES1")"
logout

echo "=== 9. Verifikasi admin: tidak valid -> unggah ulang -> valid ==="
login "admin@futsal.test" "admin12345"
chk "dashboard admin" "200" "$(get $BASE/admin/dashboard)"
chk "daftar reservasi admin" "200" "$(get $BASE/admin/reservasi)"
chk_has "reservasi A muncul di daftar admin" "Uji Tujuh A" /dev/null
get $BASE/admin/reservasi/$RES1 > /dev/null
chk "detail reservasi oleh admin" "200" "$(get $BASE/admin/reservasi/$RES1)"
T=$(token)
chk "tidak_valid tanpa catatan ditolak" "302" "$(post $BASE/admin/reservasi/$RES1/verifikasi --data-urlencode "_token=$T" --data-urlencode "keputusan=tidak_valid")"
chk "status tak berubah" "menunggu_verifikasi" "$(q "select status from pembayaran where reservasi_id=$RES1")"
get $BASE/admin/reservasi/$RES1 > /dev/null; T=$(token)
post $BASE/admin/reservasi/$RES1/verifikasi --data-urlencode "_token=$T" --data-urlencode "keputusan=tidak_valid" --data-urlencode "catatan=Bukti kurang terbaca, mohon unggah ulang." > /dev/null
chk "pembayaran -> tidak_valid" "tidak_valid" "$(q "select status from pembayaran where reservasi_id=$RES1")"
chk "catatan tersimpan" "Bukti kurang terbaca, mohon unggah ulang." "$(q "select catatan from pembayaran where reservasi_id=$RES1")"
chk "diverifikasi_oleh = admin" "1" "$(q "select (diverifikasi_oleh is not null) from pembayaran where reservasi_id=$RES1")"
chk "reservasi -> menunggu_pembayaran" "menunggu_pembayaran" "$(q "select status from reservasi where id=$RES1")"

echo "=== 10. Pelanggan mengunggah ulang bukti ==="
login "$EMAIL1" "ujitujuh123"
get $BASE/reservasi/$RES1 > /dev/null
chk_has "form upload ulang tampil" "Unggah Bukti Pembayaran" /dev/null
chk_has "petunjuk rekening tampil" "1234567890" /dev/null
T=$(token)
chk "unggah ulang bukti" "302" "$(upload $BASE/reservasi/$RES1/pembayaran "$T" "$WORK/bukti_uji.png")"
chk "pembayaran -> menunggu_verifikasi" "menunggu_verifikasi" "$(q "select status from pembayaran where reservasi_id=$RES1")"
BUKTI2=$(q "select bukti_transfer from pembayaran where reservasi_id=$RES1")
chk "bukti lama terhapus" "1" "$([ -f "public/$BUKTI1" ] && echo 0 || echo 1)"
chk "bukti baru ada" "1" "$([ -f "public/$BUKTI2" ] && echo 1 || echo 0)"
chk "nama berkas berbeda" "1" "$([ "$BUKTI1" = "$BUKTI2" ] && echo 0 || echo 1)"
logout

echo "=== 11. Admin verifikasi valid -> dikonfirmasi ==="
login "admin@futsal.test" "admin12345"
get $BASE/admin/reservasi/$RES1 > /dev/null; T=$(token)
chk "verifikasi valid" "302" "$(post $BASE/admin/reservasi/$RES1/verifikasi --data-urlencode "_token=$T" --data-urlencode "keputusan=valid")"
chk "pembayaran -> valid" "valid" "$(q "select status from pembayaran where reservasi_id=$RES1")"
chk "reservasi -> dikonfirmasi" "dikonfirmasi" "$(q "select status from reservasi where id=$RES1")"
chk "verifikasi dicatat" "1" "$(q "select (diverifikasi_pada is not null) from pembayaran where reservasi_id=$RES1")"
# verifikasi ulang harus ditolak
get $BASE/admin/reservasi/$RES1 > /dev/null; T=$(token)
chk "verifikasi kedua ditolak" "302" "$(post $BASE/admin/reservasi/$RES1/verifikasi --data-urlencode "_token=$T" --data-urlencode "keputusan=valid")"
chk "status tetap valid" "valid" "$(q "select status from pembayaran where reservasi_id=$RES1")"
logout

echo "=== 12. Otorisasi role ==="
for R in "budi@futsal.test password123" "sari@futsal.test password123" "$EMAIL1 ujitujuh123" "$EMAIL2 ujitujuh123"; do
  set -- $R
  login "$1" "$2"
  chk "pelanggan $1: /admin/dashboard 403" "403" "$(get $BASE/admin/dashboard)"
  chk "pelanggan $1: /admin/reservasi 403" "403" "$(get $BASE/admin/reservasi)"
  chk "pelanggan $1: /pemilik/dashboard 403" "403" "$(get $BASE/pemilik/dashboard)"
  logout
done
login "pemilik@futsal.test" "pemilik12345"
chk "pemilik: /admin/dashboard 403" "403" "$(get $BASE/admin/dashboard)"
chk "pemilik: /admin/lapangan 403" "403" "$(get $BASE/admin/lapangan)"
chk "pemilik: /pemilik/dashboard 200" "200" "$(get $BASE/pemilik/dashboard)"
chk "pemilik: /pemilik/reservasi 200" "200" "$(get $BASE/pemilik/reservasi)"
logout
login "admin@futsal.test" "admin12345"
chk "admin: /admin/dashboard 200" "200" "$(get $BASE/admin/dashboard)"
chk "admin: /pemilik/dashboard 403" "403" "$(get $BASE/pemilik/dashboard)"
logout

echo "=== 13. Admin kelola jadwal & lapangan ==="
login "admin@futsal.test" "admin12345"
TANGGAL=$(php -r 'echo date("Y-m-d", strtotime("+30 days"));')
get $BASE/admin/jadwal/create > /dev/null; T=$(token)
chk "tambah jadwal valid" "302" "$(post $BASE/admin/jadwal --data-urlencode "_token=$T" --data-urlencode "lapangan_id=$LP" --data-urlencode "tanggal=$TANGGAL" --data-urlencode "jam_mulai=19:00" --data-urlencode "jam_selesai=21:00")"
JD=$(q "select id from jadwal where lapangan_id=$LP and tanggal='$TANGGAL' and jam_mulai='19:00'")
chk "jadwal tersimpan" "1" "$([ -n "$JD" ] && echo 1 || echo 0)"
get $BASE/admin/jadwal/create > /dev/null; T=$(token)
chk "slot duplikat ditolak" "302" "$(post $BASE/admin/jadwal --data-urlencode "_token=$T" --data-urlencode "lapangan_id=$LP" --data-urlencode "tanggal=$TANGGAL" --data-urlencode "jam_mulai=19:00" --data-urlencode "jam_selesai=20:00")"
chk "jadwal tidak bertambah" "1" "$(q "select count(*) from jadwal where lapangan_id=$LP and tanggal='$TANGGAL' and jam_mulai='19:00'")"
get $BASE/admin/jadwal/create > /dev/null; T=$(token)
post $BASE/admin/jadwal --data-urlencode "_token=$T" --data-urlencode "lapangan_id=$LP" --data-urlencode "tanggal=$TANGGAL" --data-urlencode "jam_mulai=21:00" --data-urlencode "jam_selesai=20:00" > /dev/null
get $BASE/admin/jadwal/create > /dev/null; chk_has "jam selesai harus setelah jam mulai" "setelah jam mulai" /dev/null
# jadwal terpesan tidak boleh diubah/dihapus
get $BASE/admin/jadwal/$JD/edit > /dev/null; T=$(token)
chk "ubah jadwal dipesan ditolak" "302" "$(post PUT $BASE/admin/jadwal/$JD --data-urlencode "_token=$T" --data-urlencode "lapangan_id=$LP" --data-urlencode "tanggal=$TANGGAL" --data-urlencode "jam_mulai=19:00" --data-urlencode "jam_selesai=22:00")"
chk "jadwal dipesan tak terhapus" "1" "$(q "select count(*) from jadwal where id=$JD")"
# lapangan
get $BASE/admin/lapangan/create > /dev/null; T=$(token)
chk "tambah lapangan" "302" "$(post $BASE/admin/lapangan --data-urlencode "_token=$T" --data-urlencode "nama=Lapangan Uji 7" --data-urlencode "deskripsi=Dibuat oleh E2E tahap 7" --data-urlencode "harga_per_jam=65000" --data-urlencode "status=aktif")"
LNAMA=$(q "select nama from lapangan where nama='Lapangan Uji 7'")
chk "lapangan tersimpan" "1" "$([ -n "$LNAMA" ] && echo 1 || echo 0)"
chk "halaman publik tampil" "200" "$(get $BASE/lapangan)"
chk_has "nama lapangan tampil publik" "Lapangan Uji 7" /dev/null
# lapangan tanpa nama ditolak
get $BASE/admin/lapangan/create > /dev/null; T=$(token)
post $BASE/admin/lapangan --data-urlencode "_token=$T" --data-urlencode "harga_per_jam=1000" --data-urlencode "status=aktif" > /dev/null
get $BASE/admin/lapangan/create > /dev/null; chk_has "nama wajib diisi" "Nama lapangan wajib diisi" /dev/null
logout

echo "=== 14. Pembatalan oleh admin melepas slot ==="
login "admin@futsal.test" "admin12345"
get $BASE/admin/reservasi/$RES2 > /dev/null; T=$(token)
chk "batalkan reservasi B" "302" "$(post $BASE/admin/reservasi/$RES2/status --data-urlencode "_token=$T" --data-urlencode "status=dibatalkan")"
chk "reservasi B dibatalkan" "dibatalkan" "$(q "select status from reservasi where id=$RES2")"
chk "jadwal_id dilepas" "" "$(q "select ifnull(jadwal_id,'') from reservasi where id=$RES2")"
logout
# slot JP2 harus bisa dipesan ulang
login "$EMAIL1" "ujitujuh123"
get $BASE/lapangan/$LP > /dev/null; T=$(token)
chk "slotFormer bisa dipesan ulang" "302" "$(post $BASE/lapangan/$LP/jadwal/$JP2/reservasi --data-urlencode "_token=$T")"
RES3=$(q "select id from reservasi where jadwal_id=$JP2 and user_id=(select id from users where email='$EMAIL1')")
chk "reservasi baru dibuat" "1" "$([ -n "$RES3" ] && echo 1 || echo 0)"
logout

echo "=== 15. Laporan pemilik ==="
login "pemilik@futsal.test" "pemilik12345"
chk "dashboard pemilik" "200" "$(get $BASE/pemilik/dashboard)"
chk_has "ada bagian pendapatan" "Pendapatan" /dev/null
chk_has "ada tabel okupansi lapangan" "Okupansi" /dev/null
# filter periode: bulan depan tidak boleh memuat reservasi A (tanggal main hari ini/besok)
BULAN_INI=$(php -r 'echo date("Y-m-01");'); AKHIR=$(php -r 'echo date("Y-m-t");')
chk "filter periode bulan ini" "200" "$(get "$BASE/pemilik/dashboard?dari=$BULAN_INI&sampai=$AKHIR")"
chk "pendapatan periode > 0" "1" "$([ "$(q "select coalesce(sum(total_harga),0) from reservasi where status='dikonfirmasi' and jadwal_id in (select id from jadwal where tanggal between '$BULAN_INI' and '$AKHIR')")" -gt 0 ] && echo 1 || echo 0)"
DIKONF=$(q "select coalesce(sum(total_harga),0) from reservasi where status='dikonfirmasi' and jadwal_id in (select id from jadwal where tanggal between '$BULAN_INI' and '$AKHIR')")
SEMUA=$(q "select coalesce(sum(total_harga),0) from reservasi where jadwal_id in (select id from jadwal where tanggal between '$BULAN_INI' and '$AKHIR')")
get "$BASE/pemilik/dashboard?dari=$BULAN_INI&sampai=$AKHIR" > /dev/null
# Format ribuan ala Indonesia: 300000 -> "300.000"
fmt_ribuan() { printf '%d' "$1" | sed -E ':a;s/([0-9])([0-9]{3})($|\.)/\1.\2\3/;ta'; }
chk_has "dashboard menampilkan angka pendapatan terkonfirmasi" "Rp $(fmt_ribuan $((DIKONF/1000)))" /dev/null
chk "pendapatan terkonfirmasi lebih kecil dari total semua reservasi" "1" "$([ "$DIKONF" -lt "$SEMUA" ] && echo 1 || echo 0)"
echo "  (dikonfirmasi=$DIKONF < semua=$SEMUA)"
chk "filter periode tak sah tetap 200" "200" "$(get "$BASE/pemilik/dashboard?dari=abc&sampai=xyz")"
chk "filter terbalik tetap 200" "200" "$(get "$BASE/pemilik/dashboard?dari=$AKHIR&sampai=$BULAN_INI")"
chk "daftar reservasi pemilik" "200" "$(get $BASE/pemilik/reservasi)"
chk "filter status pemilik" "200" "$(get "$BASE/pemilik/reservasi?status=dikonfirmasi")"
logout

echo "=== 16. Error pages ==="
chk "404 halaman tak dikenal" "404" "$(get $BASE/halaman-tidak-ada)"
chk "404 lapangan tak dikenal" "404" "$(get $BASE/lapangan/999999)"
login "$EMAIL1" "ujitujuh123"
chk "403 halaman privat pelanggan" "403" "$(get $BASE/admin/dashboard)"
logout
get $BASE/login > /dev/null; T=$(token)
chk "login password salah -> 302" "302" "$(post $BASE/login --data-urlencode "_token=$T" --data-urlencode "email=$EMAIL1" --data-urlencode "password=salah-total")"
get $BASE/login > /dev/null
chk_has "flash error kredensial salah" "Email atau password salah" /dev/null

echo "=== 17. Logout terakhir ==="
login "$EMAIL1" "ujitujuh123"
get $BASE/dashboard > /dev/null; T=$(token)
chk "logout" "302" "$(post $BASE/logout --data-urlencode "_token=$T")"
chk "setelah logout akses dashboard ditolak" "302" "$(get $BASE/dashboard)"

echo "=== 18. Cleanup data uji ==="
php -r "
\$p = new PDO('mysql:host=127.0.0.1;dbname=reservasi_futsal','root','');
// Bersihkan semua sisa data uji (run sebelumnya bisa gagal di tengah jalan).
\$ids = \$p->query(\"select id from users where email like 'uji7%@futsal.test'\")->fetchAll(PDO::FETCH_COLUMN);
if (\$ids) {
    \$in = implode(',', array_map('intval', \$ids));
    \$resIds = \$p->query(\"select id from reservasi where user_id in (\$in)\")->fetchAll(PDO::FETCH_COLUMN);
    if (\$resIds) {
        \$rin = implode(',', array_map('intval', \$resIds));
        \$bukti = \$p->query(\"select bukti_transfer from pembayaran where reservasi_id in (\$rin) and bukti_transfer is not null\")->fetchAll(PDO::FETCH_COLUMN);
        foreach (\$bukti as \$f) { if (is_file('public/'.\$f)) { unlink('public/'.\$f); } }
        \$p->exec('delete from pembayaran where reservasi_id in ('.\$rin.')');
        \$p->exec('delete from reservasi where id in ('.\$rin.')');
    }
    \$p->exec('delete from users where id in ('.\$in.')');
}
\$p->prepare('delete from jadwal where tanggal = ? and jam_mulai = ?')->execute(['$TANGGAL', '19:00:00']);
\$lp = \$p->query(\"select id from lapangan where nama = 'Lapangan Uji 7'\")->fetchAll(PDO::FETCH_COLUMN);
if (\$lp) {
    \$lpin = implode(',', array_map('intval', \$lp));
    \$p->exec('delete from jadwal where lapangan_id in ('.\$lpin.')');
    \$p->exec('delete from lapangan where id in ('.\$lpin.')');
}
echo 'cleanup done';"
chk "users uji terhapus" "0" "$(q "select count(*) from users where email like 'uji7%@futsal.test'")"
chk "reservasi uji terhapus" "0" "$(q "select count(*) from reservasi r join users u on u.id=r.user_id where u.email like 'uji7%@futsal.test'")"
chk "lapangan uji terhapus" "0" "$(q "select count(*) from lapangan where nama='Lapangan Uji 7'")"
chk "jadwal uji terhapus" "0" "$(q "select count(*) from jadwal where tanggal='$TANGGAL' and jam_mulai='19:00:00'")"
chk "data demo utuh: 5 user" "5" "$(q "select count(*) from users")"
chk "data demo utuh: 2 lapangan" "2" "$(q "select count(*) from lapangan")"
chk "data demo utuh: 4 reservasi" "4" "$(q "select count(*) from reservasi")"
chk "data demo utuh: 49 jadwal" "49" "$(q "select count(*) from jadwal")"
chk "bukti demo tetap ada" "3" "$(ls public/uploads/bukti/ | grep -c bukti_demo)"
rm -rf "$WORK"

echo ""
echo "==================================="
echo "  PASS: $PASS   FAIL: $FAIL"
echo "==================================="
[ "$FAIL" -eq 0 ]