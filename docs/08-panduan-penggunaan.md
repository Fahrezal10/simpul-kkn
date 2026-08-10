# 08 — Panduan Penggunaan
## SIMPUL-KKN

> **Status: Panduan penggunaan fungsional** (Fase 1–4). Diperbarui seiring perkembangan fitur. Akun & alur di bawah adalah contoh demo yang dibuat seeder (`php artisan migrate:fresh --seed`).

Dokumen sebelumnya: `00-design-system.md` s.d. `07-load-testing.md`

---

## 1. Menjalankan Aplikasi

### Lokal (XAMPP)

```bash
# 1. Pastikan MySQL & Apache XAMPP aktif (Apache opsional, cukup MySQL).
# 2. Jalankan server Laravel (mode development):
php artisan serve
# buka http://127.0.0.1:8000

# Atau akses via http://localhost (Apache) bila docroot mengarah ke project ini.
```

### Reset database ke kondisi awal contoh

```bash
php artisan migrate:fresh --seed
```

Ini menghapus semua data & membuat ulang dari nol (tabel + seeder). Gunakan bila data terlanjur kotor/berubah saat uji coba.

---

## 2. Daftar Akun Login (Sementara)

Semua akun dibuat oleh seeder. **Semua password adalah `password`** (konvensi akun contoh).

| Role | Email | Password | Keterangan |
|---|---|---|---|
| Bapperida (Admin) | `admin@bapperida-indramayu.go.id` | `password` | Admin/superadmin — bisa login & verifikasi |
| Perguruan Tinggi (disetujui) | `pt@uin.ac.id` | `password` | Universitas Indramayu — bisa login & ajukan permohonan |
| Perguruan Tinggi (menunggu approval) | `pt-menunggu@uin.ac.id` | `password` | STIKes Sehat Jaya — untuk demo persetujuan akun |
| Mahasiswa (kelompok 1) | `andi@uin.ac.id` | `password` | Andi Pratama (2024-01001) |
| Mahasiswa (kelompok 2) | `dewi@uin.ac.id` | `password` | Dewi Lestari (2024-02001) |
| DPL (kelompok 1) | `siti@uin.ac.id` | `password` | DPL kelompok 01 — approve logbook/laporan |
| DPL (kelompok 2) | `ahmad@uin.ac.id` | `password` | DPL kelompok 02 |
| Operator Kecamatan Haurgeulis | `kec@haurgeulis.go.id` | `password` | Verifikasi kesiapan desa (UC-11) |
| Operator Kecamatan Jatibarang | `kec@jatibarang.go.id` | `password` | Verifikasi kesiapan desa (UC-11) |
| Operator Desa Wanakaya | `desa@wanakaya.go.id` | `password` | Profil desa, evaluasi kelompok (UC-13) |
| Operator OPD | `opd@bappeda.go.id` | `password` | Input isu strategis (UC-10) |

> ⚠️ **Jika lupa/mau reset password akun mana pun:** reset dengan tinker:
> ```bash
> php artisan tinker
> >>> App\Models\User::where('email','admin@bapperida-indramayu.go.id')->update(['password'=>bcrypt('password')]);
> ```
> Ganti email sesuai akun yang dimaksud.

---

## 3. Alur Uji Coba Fase 1 (Checklist)

Alur inti pengajuan → verifikasi. Jalankan berurutan:

### 3.1 Persetujuan Akun PT (UC-01)
- [ ] Login `admin@bapperida-indramayu.go.id` → menu **Bapperida → Persetujuan PT**
- [ ] Filter **Menunggu** → ada `STIKes Sehat Jaya`
- [ ] Klik **Detail** → lihat data institusi & PIC
- [ ] **Setujui** → status jadi *Disetujui*, notifikasi dikirim ke akun PT tsb
- [ ] (Opsional) Tolak dengan catatan → status *Ditolak*, catatan tampil di detail

### 3.2 Registrasi PT Baru (UC-01)
- [ ] Buka `/register-pt` (atau link "Daftarkan Institusi Anda" di halaman login)
- [ ] Isi data institusi + akun login + PIC → submit
- [ ] Muncul flash sukses "menunggu persetujuan"
- [ ] Login Bapperida → lihat PT baru di daftar persetujuan

### 3.3 Ajukan Permohonan (UC-02/03)
- [ ] Login `pt@uin.ac.id` → menu **Perguruan Tinggi → Permohonan KKN** → **Ajukan Permohonan Baru**
- [ ] Isi periode, tanggal, **tema**, **bidang keilmuan**, upload surat & proposal (PDF)
- [ ] Tambah baris mahasiswa (NIM, nama, prodi, no HP) + pilih DPL (atau "+ DPL Baru")
- [ ] Submit → flash sukses "menunggu verifikasi"
- [ ] Cek DB (opsional): `permohonan_kkn` status `diajukan`, `kelompok_kkn` auto-generate per DPL, `mahasiswa` terisi

### 3.4 Verifikasi Permohonan (UC-05)
- [ ] Login Bapperida → **Verifikasi Permohonan** → **Review**
- [ ] Periksa detail: dokumen, kelompok, mahasiswa
- [ ] **Verifikasi** → status *Terverifikasi*, notifikasi ke PT
- [ ] (Opsional) **Tolak** dengan catatan → status *Ditolak*, notifikasi ke PT

### 3.5 Notifikasi & Status (UC-04 / SYS-01)
- [ ] Klik **bell di navbar** → dropdown popup notifikasi (bukan halaman baru)
- [ ] Klik satu notifikasi → tandai dibaca (badge berkurang) → **lari ke halaman sumber**
- [ ] Di menu PT **Permohonan KKN**, status permohonan tampil sebagai badge real-time
- [ ] Halaman notifikasi penuh: menu user → **Notifikasi**

> **Dashboard mahasiswa:** akun `andi@uin.ac.id` / `dewi@uin.ac.id` sudah bisa login, dashboard + bell notifikasi berfungsi (dropdown kosong sampai ada pemicu notifikasi mahasiswa).

---

## 4. Alur Uji Coba Fase 4 (Checklist)

### 4.1 Dashboard GIS — Peta Sebaran (UC-09)
- [ ] Login Bapperida → menu **Peta (GIS)** (atau `/gis`)
- [ ] Peta Leaflet tampil dengan marker desa (dari `desa.latitude/longitude`)
- [ ] Klik marker → popup info desa: nama, kecamatan, jumlah kelompok KKN aktif
- [ ] Data peta dari endpoint `/gis/data` (GeoJSON `FeatureCollection`)

### 4.2 Dashboard Monitoring & Evaluasi (UC-09)
- [ ] Bapperida → **Monitoring & Evaluasi** (atau `/bapperida/monitoring`)
- [ ] Statistik: jumlah PT, permohonan, mahasiswa, kelompok aktif, desa aktif
- [ ] Distribusi kelompok per status + kelompok aktif per PT + rata-rata evaluasi desa & DPL

### 4.3 Master Data Generik (UC-08)
- [ ] Bapperida → **Master Data** → pilih jenis **Kecamatan** / **Perangkat Daerah**
- [ ] **Tambah** data baru → muncul di tabel
- [ ] **Edit** via ikon pensil → modal terisi nilai lama → simpan
- [ ] **Hapus**: kecamatan yang punya desa ditolak sistem (peringatan), kecamatan kosong berhasil dihapus
- [ ] Kode wilayah kecamatan harus unik (validasi)

### 4.4 Penutupan Periode KKN
- [ ] Login Bapperida → **Penutupan Periode**
- [ ] Daftar kelompok **Aktif** tampil (kode, PT, tema, desa lokasi)
- [ ] **Tutup Periode** → konfirmasi → semua kelompok aktif jadi **Selesai**
- [ ] Login mahasiswa `andi@uin.ac.id` → coba isi logbook → **ditolak** (kelompok sudah selesai)
- [ ] DPL `siti@uin.ac.id` menerima notifikasi "periode KKN ditutup"

### 4.5 Aktivitas Sistem (Audit Trail)
- [ ] Bapperida → **Aktivitas Sistem** (atau `/activity-log`)
- [ ] Daftar jejak aksi tampil: waktu, pengguna, role, aksi, deskripsi, IP
- [ ] Cari / filter aksi (mis. `tutup_periode`, `setujui_pelaksanaan`)
- [ ] Aksi terbaru tampil paling atas

---

## 5. Checklist Cepat (Hal yang Sering Diperiksa Saat Lupa)

| Hal | Di mana | Catatan |
|---|---|---|
| Akun lupa | §2 tabel akun | Reset via tinker bila perlu |
| Data kotor / mau bersih | §1 `migrate:fresh --seed` | Hapus semua & seed ulang |
| Notifikasi tidak muncul | `queue` di-`database`, worker tidak jalan | Notifikasi **sinkron** (tanpa queue) — jika tidak muncul cek tabel `notifications` & `data['url']` |
| Notifikasi lama tanpa `url` | detail notifikasi | Notif lama (sebelum fitur url) fallback ke halaman notifikasi; buat ulang via aksi riil |
| Upload file tidak tampil | `public/storage` | Pastikan `php artisan storage:link` sudah jalan (symlink) |
| File PDF > 5MB / bukan PDF | validasi form | Ditolak otomatis, periksa pesan error |
| Akses menu tidak ada | sidebar per-role | Halaman dibatasi `role:` — pastikan login dengan role yang benar |
| Route `data` (AJAX) 404/405 | `routes/web.php` | Route `data` harus sebelum route `{model}` |

---

## 6. Catatan Teknis Ringkas

- **Laravel 12 + MySQL** (XAMPP). CSS/JS manual di `public/css/app.css` & `public/js/app.js` (bukan build Vite).
- **Notifikasi in-app** disimpan ke tabel `notifications` **sinkron** (tanpa queue worker) — sesuai desain fase awal.
- **Pagination tabel list** memakai **jQuery/AJAX server-side** (endpoint `.../data`).
- **Cache dashboard monitoring**: agregasi di-`Cache::remember` 120 detik; di-invalidasi otomatis setelah approval, evaluasi, dan penutupan periode.
- **Activity log**: seluruh aksi penting (tambah/ubah/hapus, matching, verifikasi, persetujuan, tutup periode) ditulis ke `activity_log` dan tampil di menu **Aktivitas Sistem**.
- Konvensi commit: prefix `feat:` / `docs:` / `style:` / `chore:`.

---

*Dokumen berikutnya: `07-load-testing.md` merujuk ke panduan ini.*
