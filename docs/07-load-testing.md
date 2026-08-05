# 07 — Skenario & Checklist Load Testing
## SIMPUL-KKN

Dokumen sebelumnya: `00-design-system.md` s.d. `06-phase-plan.md`

**Pendekatan:** Mengingat tim kecil (2 developer) dan keterbatasan waktu, load testing di dokumen ini **tidak menggunakan tool formal** (JMeter/k6/Locust), melainkan **skenario manual + checklist** yang bisa dijalankan tim sendiri menggunakan cara sederhana (banyak tab/browser/perangkat sekaligus, dibantu anggota tim atau volunteer internal Bapperida saat UAT).

---

## 1. Asumsi Beban (dihitung ulang dari `00-design-system.md`)

| Parameter Dasar | Nilai |
|---|---|
| Jumlah PT terdaftar | 12–30 |
| Mahasiswa per periode | 1.000–3.000 |
| Jumlah desa | ±309 |

Dari angka dasar ini, diturunkan **estimasi beban konkuren** di titik-titik puncak:

| Momen Puncak | Perhitungan Kasar | Estimasi Beban Konkuren |
|---|---|---|
| **Masa pengajuan permohonan** (PT submit bersamaan, biasanya window ±2 minggu) | 12-30 PT, realistis tidak submit di detik yang sama | **5-10 submission konkuren** |
| **Verifikasi Bapperida** | 1 admin/beberapa staf mengerjakan berurutan | **1-5 user aktif konkuren** |
| **Isi logbook harian mahasiswa** (terkonsentrasi malam hari, jam 18.00-21.00) | Asumsi 50% dari 1.000-3.000 mhs aktif di window 3 jam = 500-1.500 submit tersebar; dalam burst 10 menit tersibuk | **50-150 submission konkuren** |
| **Upload laporan akhir** (menjelang deadline, H-2 s.d. H-1) | Asumsi 30-40% dari total kelompok (bukan individu mahasiswa, laporan per kelompok) upload di 2 hari terakhir | **20-60 upload konkuren**, ukuran file lebih besar dari logbook |
| **Akses Dashboard GIS/Monitoring** (saat rapat evaluasi/monitoring) | Beberapa staf Bapperida + sesekali PT/Kecamatan cek bersamaan | **10-30 user konkuren** |

> Catatan: angka mahasiswa jauh lebih besar dari PT, tapi karena **logbook adalah aksi ringan** (insert 1 baris + upload 1 foto), bukan proses berat, beban di titik ini lebih ke arah **volume database** (banyak baris) daripada kompleksitas komputasi — kecuali di Dashboard GIS/Monitoring yang melakukan agregasi.

---

## 2. Skenario Testing & Checklist Manual

### Skenario 1 — Submit Permohonan KKN Bersamaan
**Tujuan:** Pastikan tidak ada race condition saat beberapa PT submit di waktu berdekatan (mis. rebutan kode kelompok, atau constraint unique gagal).

**Cara uji manual:**
1. Siapkan 5-10 akun PT dummy (bisa dibuat via seeder)
2. Buka 5-10 tab/browser berbeda (atau minta 5-10 anggota tim/volunteer login bersamaan)
3. Isi form permohonan lengkap di masing-masing tab
4. Klik submit **secara bersamaan** (hitung mundur "3-2-1-submit" antar tester)
5. Cek: semua data masuk dengan benar? Ada duplikat kode kelompok? Ada error 500?

**Target:** Semua submission berhasil tanpa error, tidak ada data tertimpa/hilang, waktu respons < 3 detik per submission.

---

### Skenario 2 — Logbook Harian Massal (Beban Tertinggi)
**Tujuan:** Ini skenario **paling penting** karena volume mahasiswa jauh lebih besar dari aktor lain.

**Cara uji manual:**
1. Seeder data dummy: buat 100-150 akun mahasiswa (mewakili estimasi burst 10 menit tersibuk)
2. Gunakan script sederhana (boleh minta bantuan Dev A buat script kecil pakai Laravel Artisan command atau `curl` loop) untuk mensimulasikan submit logbook dari banyak "user" secara berurutan cepat — **ini satu-satunya bagian yang boleh pakai script kecil, bukan tool load-testing formal**, sekadar loop `for` berisi HTTP request
3. Amati waktu respons rata-rata dan cek log Laravel (`storage/logs/laravel.log`) apakah ada error/timeout
4. Cek query yang berjalan — aktifkan **Laravel Debugbar** (dev only) untuk melihat apakah ada N+1 query saat insert logbook + trigger notifikasi

**Target:** Waktu respons submit logbook < 2 detik meski dijalankan berturutan cepat; tidak ada N+1 query; storage upload foto tidak menyebabkan disk penuh mendadak (cek kompresi/validasi ukuran file jalan).

---

### Skenario 3 — Upload Laporan Akhir (File Besar, Konkuren)
**Tujuan:** File laporan akhir jauh lebih besar dari foto logbook (bisa sampai 15MB), rawan menyumbat bandwidth server kecil.

**Cara uji manual:**
1. Siapkan 20-30 akun kelompok dummy dengan file PDF ±10-15MB
2. Minta beberapa anggota tim upload bersamaan (atau buka banyak tab)
3. Amati: apakah server (XAMPP/Apache lokal atau server staging) tetap responsif untuk request lain saat upload besar berlangsung?
4. Cek konfigurasi PHP (`upload_max_filesize`, `post_max_size`, `max_execution_time`) sudah sesuai kebutuhan (minimal 20MB & 120 detik disarankan)

**Target:** Upload berhasil tanpa timeout; halaman lain tetap bisa diakses user lain selama proses upload berlangsung (tidak blocking).

---

### Skenario 4 — Matching Engine dengan Data Desa Penuh (309 Desa)
**Tujuan:** Matching Engine harus tetap cepat meski menghitung skor untuk seluruh 309 desa sekaligus.

**Cara uji manual:**
1. Pastikan seluruh 309 desa dummy/riil sudah ada di database dengan data potensi/kebutuhan lengkap
2. Jalankan "Jalankan Matching" pada satu permohonan
3. Ukur waktu dari klik sampai hasil ranking tampil (stopwatch manual cukup)
4. Cek query yang dijalankan — pastikan perhitungan skor **tidak melakukan query per-desa di dalam loop** (N+1 query klasik), sebaiknya satu query agregat lalu dihitung di memori PHP

**Target:** Hasil ranking tampil < 5 detik untuk 309 desa. Kalau lebih lambat, prioritas optimasi: eager loading, index pada kolom yang difilter/dijoin.

---

### Skenario 5 — Dashboard GIS dengan Marker Penuh
**Tujuan:** Peta Leaflet harus tetap ringan meski menampilkan ratusan marker (semua lokasi KKN aktif, potensial ratusan titik).

**Cara uji manual:**
1. Isi data dummy KKN aktif di 100-200 desa berbeda
2. Buka Dashboard GIS, amati waktu render peta & marker
3. Coba filter (per PT/tema/status) dan amati responsivitas
4. Uji di koneksi lambat (bisa simulasi via Chrome DevTools → Network → Throttling "Slow 3G")

**Target:** Peta tetap render < 3 detik pada koneksi normal. Untuk koneksi lambat, pertimbangkan **marker clustering** (plugin Leaflet.markercluster) jika marker >100 — dicatat sebagai potensi optimasi lanjutan bila hasil uji menunjukkan lambat.

---

### Skenario 6 — Akses Dashboard Monitoring Bersamaan (Rapat Evaluasi)
**Tujuan:** Beberapa staf Bapperida/PT sering cek dashboard bersamaan saat momen tertentu (rapat, laporan bulanan).

**Cara uji manual:**
1. Buka dashboard di 10-15 tab/device berbeda secara bersamaan
2. Amati waktu render statistik agregat (jumlah mahasiswa, PT, progres)
3. Refresh berulang untuk simulasi beban nyata

**Target:** Waktu render < 3 detik meski diakses 10-15 user bersamaan. Jika lambat, pertimbangkan **caching** hasil agregasi (Laravel Cache, refresh tiap beberapa menit bukan real-time query tiap request) — sesuai NFR di `00-design-system.md` yang memang tidak mensyaratkan real-time ketat.

---

## 3. Kapan Testing Dijalankan (Terhubung ke `06-phase-plan.md`)

Testing **tidak ditumpuk di akhir**, tapi dijalankan progresif mengikuti fase:

| Fase | Skenario yang Relevan Dijalankan |
|---|---|
| Fase 1 (Alur Pengajuan) | Skenario 1 (submit permohonan bersamaan) — smoke test ringan di akhir fase |
| Fase 2 (Matching, Kecamatan, Desa) | Skenario 4 (Matching Engine) — **wajib diuji sebelum lanjut ke Fase 3** karena ini titik risiko tertinggi |
| Fase 3 (Pelaksanaan) | Skenario 2 (logbook massal), Skenario 3 (upload laporan) |
| Fase 4 (Dashboard, GIS, Testing, Deploy) | Skenario 5 (Dashboard GIS), Skenario 6 (Dashboard Monitoring) — pengujian akhir menyeluruh sebelum go-live |

---

## 4. Checklist Optimasi Teknis Pendukung

Bukan skenario testing, tapi hal-hal teknis yang perlu dicek berdampingan dengan testing di atas:

- [ ] Index database pada seluruh kolom foreign key dan kolom `status` (lihat catatan di `03-erd.md` §3)
- [ ] Aktifkan **query caching**/eager loading (`with()`) di semua query yang menampilkan data relasi (hindari N+1)
- [ ] Kompresi/resize otomatis foto yang diupload (logbook, dokumentasi) sebelum disimpan — mengurangi beban storage & bandwidth
- [ ] Gunakan **queue** (Laravel Queue, driver `database` cukup untuk skala ini) untuk proses non-kritis seperti kirim notifikasi, supaya tidak memperlambat response saat submit
- [ ] Set `pagination`/server-side processing di **semua** tabel yang berpotensi besar (mahasiswa, logbook, desa) — bukan hanya yang sudah direncanakan pakai DataTables
- [ ] Cek konfigurasi PHP di server produksi: `memory_limit`, `upload_max_filesize`, `post_max_size`, `max_execution_time` disesuaikan dari default XAMPP

---

## 5. Ringkasan Target Performa (NFR Recap)

| Aksi | Target Waktu Respons |
|---|---|
| Login & redirect dashboard | < 2 detik |
| Submit form (permohonan, logbook, evaluasi) | < 2-3 detik |
| Jalankan Matching System (309 desa) | < 5 detik |
| Render Dashboard GIS | < 3 detik (normal), tetap dapat diakses di koneksi lambat |
| Render Dashboard Monitoring (10-15 user konkuren) | < 3 detik |
| Upload file (max 15MB) | Berhasil tanpa timeout, tidak blocking user lain |

---

*Seluruh dokumen SIMPUL-KKN selesai: `00-design-system.md` → `01-prd.md` → `02-use-case.md` → `03-erd.md` → `04-flowchart.md` → `05-activity-diagram.md` → `06-phase-plan.md` → `07-load-testing.md`*
