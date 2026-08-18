# 08 — Panduan Penggunaan Lengkap (Manual Book)
## SIMPUL-KKN — Sistem Informasi Pengelolaan KKN Kabupaten Indramayu

> **Manual book pengguna** mencakup seluruh alur: pendaftaran institusi → pengajuan permohonan → verifikasi → matching → verifikasi kecamatan → approval final → pelaksanaan (logbook, laporan, evaluasi) → monitoring & penutupan periode. Semua akun & data di bawah adalah contoh hasil seeder (`php artisan migrate:fresh --seed`). **Semua password akun contoh: `password`.**

Dokumen ini menggantikan versi ringkas sebelumnya dan menjadi acuan operasional harian.

---

## Daftar Isi

1. [Pengenalan & Peran (Role)](#1-pengenalan--peran-role)
2. [Menjalankan Aplikasi](#2-menjalankan-aplikasi)
3. [Daftar Akun Login](#3-daftar-akun-login)
4. [Peta Alur Utama (End-to-End)](#4-peta-alur-utama-end-to-end)
5. [Alur 1 — Registrasi & Persetujuan PT](#5-alur-1--registrasi--persetujuan-pt-uc-01)
6. [Alur 2 — Pengajuan Permohonan KKN](#6-alur-2--pengajuan-permohonan-kkn-uc-0203)
7. [Alur 3 — Verifikasi Permohonan Bapperida](#7-alur-3--verifikasi-permohonan-bapperida-uc-05)
8. [Alur 4 — Matching Engine](#8-alur-4--matching-engine-uc-06)
9. [Alur 5 — Verifikasi Kecamatan & Approval Final](#9-alur-5--verifikasi-kecamatan--approval-final-uc-1107)
10. [Alur 6 — Pelaksanaan: Logbook, Laporan, Evaluasi](#10-alur-6--pelaksanaan-logbook-laporan-evaluasi)
11. [Modul Per Pendukung (Master Data, GIS, Monitoring, Audit)](#11-modul-per-pendukung-master-data-gis-monitoring-audit)
12. [Notifikasi](#12-notifikasi)
13. [Checklist Cepat & Pemecahan Masalah](#13-checklist-cepat--pemecahan-masalah)
14. [Catatan Teknis Ringkas](#14-catatan-teknis-ringkas)

---

## 1. Pengenalan & Peran (Role)

Sistem menghubungkan **8 peran (role)** dengan tanggung jawab berbeda. Setiap role melihat menu yang sesuai; halaman akses dibatasi sistem.

| No | Role | Singkatan | Siapa | Tanggung Jawab Utama |
|---|---|---|---|---|
| 1 | **Bapperida** | BP | Staf Bappeda Kab. Indramayu | Persetujuan akun PT, verifikasi permohonan, menjalankan matching, approval final lokasi, master data, monitoring, GIS, penutupan periode |
| 2 | **Perguruan Tinggi** | PT | Admin Universitas / Institut | Daftar institusi, ajukan permohonan KKN + daftar mahasiswa & DPL |
| 3 | **Mahasiswa** | MHS | Mahasiswa peserta KKN | Isi logbook harian, upload laporan akhir |
| 4 | **Dosen (DPL)** | DPL | Dosen Pembimbing Lapangan | Approve logbook, verifikasi laporan akhir, isi evaluasi |
| 5 | **Desa** | DSA | Perangkat Desa | Kelola profil desa (potensi/permasalahan/kebutuhan), evaluasi kelompok |
| 6 | **Perangkat Daerah** | OPD | Staf Dinas Kabupaten | Input isu strategis (prioritas daerah) untuk matching |
| 7 | **Kecamatan** | KEC | Operator Kecamatan | Verifikasi kesiapan desa |
| 8 | **Superadmin** | SA | Admin teknis sistem | Role khusus sistem (saat ini tidak ada menu tersendiri) |

> **Perjalanan tipikal alur:** PT (daftar + ajukan) → **Bapperida** (verifikasi & matching) → **Kecamatan** (verifikasi kesiapan desa) → **Bapperida** (approval final → kelompok *Aktif*) → **Mahasiswa + DPL** (logbook & laporan) → **Desa + DPL** (evaluasi) → **Bapperida** (monitoring & penutupan periode).

---

## 2. Menjalankan Aplikasi

### 2.1 Prasyarat

- PHP 8.2+ dan MySQL (XAMPP).
- Composer untuk instalasi dependensi.

### 2.2 Menjalankan di Lokal (XAMPP)

```bash
# 1. Nyalakan MySQL (dan Apache bila mau akses via http://localhost). 
#    Gunakan XAMPP Control Panel → "Start MySQL".
# 2. Pastikan file .env sudah benar (DB_HOST, DB_DATABASE, DB_USERNAME).
# 3. Jalankan server Laravel (mode development):
php artisan serve
# buka http://127.0.0.1:8000
```

- Bila akses via Apache: pastikan docroot mengarah ke project ini, akses `http://localhost`.
- File upload (surat/proposal/laporan/foto logbook) diakses lewat rute aman `files/...` (lihat §10.5). Pastikan sudah:
  ```bash
  php artisan storage:link
  ```

### 2.3 Membangun Database Awal (reset ke kondisi contoh)

```bash
php artisan migrate:fresh --seed
```

Perintah ini **menghapus semua data** dan membuat ulang tabel + data contoh dari nol. Gunakan saat data terlanjur kotor/berubah saat uji coba — bukan di produksi.

### 2.4 Membuka Website

- Halaman pembuka (`/`): menampilkan info sistem, dengan tombol **Masuk** dan **Daftar Institusi (PT)**.
- Rute utama:
  - Login → `/login`
  - Registrasi PT → `/register-pt` (link "Daftarkan Institusi Anda")
  - Setelah login, setiap role diarahkan ke dashboard masing-masing.

---

## 3. Daftar Akun Login

> ⚠️ Semua akun dibuat otomatis oleh seeder. **Password semua: `password`.** Daftar di bawah persis dengan isi database setelah `migrate:fresh --seed`.

| Role | Email | Nama (Login) | Keterangan |
|---|---|---|---|
| Bapperida | `admin@bapperida-indramayu.go.id` | Admin Bapperida | Akun utama admin — verifikasi, matching, approval, monitoring |
| Perguruan Tinggi (disetujui) | `pt@uin.ac.id` | Universitas Indramayu | Bisa ajukan permohonan |
| Perguruan Tinggi (menunggu) | `pt-menunggu@uin.ac.id` | STIKes Sehat Jaya | Dipakai demo persetujuan akun PT |
| Mahasiswa — Kelompok 1 | `andi@uin.ac.id` | Andi Pratama | NIM 2024-01001 |
| Mahasiswa — Kelompok 2 | `dewi@uin.ac.id` | Dewi Lestari | NIM 2024-02001 |
| Dosen (DPL) — Kelompok 1 | `siti@uin.ac.id` | Dr. Siti Rahmawati | Approve logbook & laporan kelompok 1 |
| Dosen (DPL) — Kelompok 2 | `ahmad@uin.ac.id` | Ahmad Fauzi, M.Pd. | Approve logbook & laporan kelompok 2 |
| Operator Desa Wanakaya (Haurgeulis) | `desa@wanakaya.go.id` | Operator Desa Wanakaya | Profil desa & evaluasi (UC-13) |
| Operator Desa Jatibarang | `desa@jatibarang.go.id` | Operator Desa Jatibarang | Profil desa & evaluasi (UC-13) |
| Perangkat Daerah (Diskominfo) | `opd@kominfo.go.id` | Operator Dinas Komunikasi dan Informatika | Input isu strategis (UC-10) |
| Perangkat Daerah (Dinas Pertanian) | `opd@ketapang.go.id` | Operator Dinas Ketahanan Pangan & Pertanian | Input isu strategis (UC-10) |
| Kecamatan Haurgeulis | `kec@haurgeulis.go.id` | Operator Kecamatan Haurgeulis | Verifikasi kesiapan desa (UC-11) |
| Kecamatan Jatibarang | `kec@jatibarang.go.id` | Operator Kecamatan Jatibarang | Verifikasi kesiapan desa (UC-11) |

### 3.1 Reset Password Akun Apa Pun

```bash
php artisan tinker
>>> App\Models\User::where('email','admin@bapperida-indramayu.go.id')->update(['password'=>bcrypt('password')]);
```
Ganti email sesuai akun yang dimaksud.

---

## 4. Peta Alur Utama (End-to-End)

```
[PT] Daftar Institusi ──► [BP] Setujui Akun PT
      │                        (Alur 1)
      ▼
[PT] Ajukan Permohonan (periode, tema, mahasiswa, DPL, dokumen)
      │                        (Alur 2)
      ▼
[BP] Verifikasi Permohonan ── (Setuju/Tolak)
      │                        (Alur 3)
      ▼
[BP] Jalankan Matching Engine ──► Ranking desa per kelompok
      │  (bobot 30/25/25/20)      (Alur 4)
      ▼
[KEC] Verifikasi Kesiapan Desa   (Alur 5)
      ▼
[BP] Approval Final / Assign Lokasi ──► Kelompok status "Aktif"
      │                        (Alur 5)
      ▼
[PELAKSANAAN]
  [MHS] Isi Logbook Harian ──► [DPL] Approve/Revisi   (Alur 6)
  [MHS] Upload Laporan Akhir ─► [DPL] Verifikasi        (Alur 6)
  [DESA] Evaluasi Desa ───────► [DPL] Evaluasi DPL      (Alur 6)
      │
      ▼
[BP] Monitoring, GIS, Penutupan Periode (Alur 7 & 11)
```

> Panduan check-in setelah setiap langkah ada di bawah. Kerjakan berurutan; setiap status permohonan/kelompok tampil sebagai badge real-time.

---

## 5. Alur 1 — Registrasi & Persetujuan PT (UC-01)

Tujuan: institusi PT mendaftar, lalu disetujui admin Bapperida sebelum bisa mengajukan permohonan.

### 5.1 Registrasi Institusi Baru (halaman publik)

1. Buka **`/register-pt`** (atau tombol **"Daftarkan Institusi Anda"** di halaman login).
2. Isi lengkap:
   - **Data institusi:** nama PT, alamat.
   - **Akun login:** email + password (dipakai untuk login sebagai PT).
   - **Data PIC (Penanggung Jawab):** nama, email, telepon.
3. Klik **Daftar**.
4. Muncul pesan sukses "menunggu persetujuan" — status PT = **Menunggu**.

### 5.2 Persetujuan Akun PT oleh Bapperida

1. Login `admin@bapperida-indramayu.go.id`.
2. Menu **Bapperida → Persetujuan PT** (`/bapperida/perguruan-tinggi`).
3. Filter **Menunggu** → ada `STIKes Sehat Jaya` (atau PT baru yang barusan didaftar di §5.1).
4. Klik **Detail** → lihat data institusi & PIC.
5. Klik **Setujui** → status jadi **Disetujui**, sistem mengirim notifikasi ke akun PT tsb.
6. (Opsional) Klik **Tolak** dengan catatan → status **Ditolak**, catatan tampil di detail.

---

## 6. Alur 2 — Pengajuan Permohonan KKN (UC-02/03)

Tujuan: PT mengisi permohonan 1 periode + daftar kelompok, mahasiswa, dan DPL.

1. Login `pt@uin.ac.id` (Universitas Indramayu).
2. Menu **Perguruan Tinggi → Permohonan KKN** → **Ajukan Permohonan Baru** (`/pt/permohonan/create`).
3. Isi:
   - **Periode** (mis. `Ganjil 2026/2027`), **tanggal mulai** & **selesai**.
   - **Tema** & **bidang keilmuan** (relevan untuk matching).
   - **Upload surat permohonan & proposal** (PDF, maks 5 MB).
4. Tambah kelompok via **tambah baris mahasiswa**:
   - Isi NIM, nama, prodi, no HP.
   - Pilih **DPL** dari daftar, atau buat **"+ DPL Baru"** (nama, NIP, no HP, email).
   - Setiap DPL otomatis menjadi 1 kelompok KKN.
5. Klik **Submit** → flash sukses "menunggu verifikasi".
6. Cek (opsional) di database: `permohonan_kkn` status `diajukan`, `kelompok_kkn` ter-generate per DPL, `mahasiswa` terisi.

> **Catatan:** import Excel mahasiswa adalah fitur pasca-launch; saat ini input manual satu-satu.

---

## 7. Alur 3 — Verifikasi Permohonan Bapperida (UC-05)

Tujuan: Bapperida memeriksa kelengkapan permohonan → status **Terverifikasi** (siap di-match).

1. Login Bapperida → **Verifikasi Permohonan** (`/bapperida/permohonan`).
2. Klik **Review** pada permohonan status `diajukan`.
3. Periksa detail: dokumen, kelompok, mahasiswa.
4. **Verifikasi** → status **Terverifikasi**, notifikasi ke PT.
5. (Opsional) **Tolak** dengan catatan → status **Ditolak**, notifikasi ke PT, PT bisa revisi & kirim ulang.

---

## 8. Alur 4 — Matching Engine (UC-06)

Tujuan: sistem memberi **ranking rekomendasi desa** untuk tiap kelompok KKN berdasarkan skor.

### 8.1 Cara Kerja Skor

Sistem membandingkan **tema/bidang keilmuan kelompok** dengan **kebutuhan & potensi desa** dan **isu strategis OPD**, dengan bobot default:

| Komponen | Bobot |
|---|---|
| Kecocokan kebutuhan desa ↔ tema | 30% |
| Potensi desa ↔ bidang keilmuan | 25% |
| Kesesuaian isu strategis OPD | 25% |
| Kesiapan desa / kelayakan | 20% |

### 8.2 Menjalankan Matching

1. Login Bapperida → **Matching** (`/bapperida/matching`).
2. Pilih kelompok yang statusnya **menunggu matching**.
3. Klik **Jalankan Matching** (`POST bapperida/matching/{kelompok}/run`) → sistem menghitung skor untuk seluruh desa yang layak.
4. Hasil tampil sebagai **ranking + alasan** per desa (nilai tiap komponen).
5. **Pilih** desa terbaik, atau **Override** lokasi secara manual bila perlu.
6. (Opsional) **Batal Pilih** untuk mengulang pilihan.
7. Hasil disimpan di tabel `riwayat_matching`; desa dengan tumpang tindih diberi flag.

> **Jika desa ditolak di verifikasi kecamatan** (Alur 5), desa itu tidak muncul lagi sebagai kandidat matching.

---

## 9. Alur 5 — Verifikasi Kecamatan & Approval Final (UC-11/07)

Tujuan rangkaian: desa dinyatakan **siap**, lalu Bapperida menetapkan lokasi → kelompok **Aktif**.

### 9.1 Verifikasi Kesiapan Desa oleh Kecamatan

1. Login operator kecamatan, mis. `kec@haurgeulis.go.id`.
2. Menu **Kecamatan → Verifikasi Kecamatan** (`/kecamatan/verifikasi`).
3. Buka detail kelompok yang mengusulkan desa di wilayahnya (mis. **Wanakaya**).
4. Periksa kesiapan desa → **Verifikasi "Siap"** → status desa jadi layak ditugaskan.
5. Bila dinyatakan tidak siap → desa kembali ke kandidat matching (§8.2 catatan).

> Setiap kecamatan **hanya melihat desa di wilayahnya sendiri** (otorisasi otomatis).

### 9.2 Approval Final oleh Bapperida

1. Login Bapperida → **Approval Final** (`/bapperida/approval-final`).
2. Pilih kelompok yang sudah selesai verifikasi kecamatan.
3. Konfirmasi **assign lokasi** desa → **Setujui** → status kelompok KKN menjadi **Aktif**.
4. Alternatif: **Tolak** → kembali ke matching.

> Setelah **Aktif**, mahasiswa kelompok tsb bisa mulai mengisi logbook, dan operator desa bisa mengisi profil & evaluasi.

---

## 10. Alur 6 — Pelaksanaan: Logbook, Laporan, Evaluasi

### 10.1 Logbook Harian Mahasiswa (UC-14)

1. Login mahasiswa, mis. `andi@uin.ac.id`.
2. Menu **Mahasiswa → Logbook** (`/mahasiswa/logbook`).
3. **Isi Logbook Baru**: deskripsi kegiatan harian + (opsional) foto. Boleh diisi dari HP.
4. Simpan → status logbook **Menunggu Persetujuan** DPL.

### 10.2 Persetujuan Logbook oleh DPL (UC-16)

1. Login DPL kelompok tsb, mis. `siti@uin.ac.id`.
2. Menu **Dosen → Logbook** (`/dosen/logbook`).
3. Buka entri logbook mahasiswa → **Approve** (status **Disetujui**), atau **Revisi** dengan catatan (status **Diajukan Kembali** oleh mahasiswa).

### 10.3 Laporan Akhir (UC-15)

1. Login mahasiswa → **Mahasiswa → Laporan Akhir** (`/mahasiswa/laporan-akhir`).
2. **Upload laporan akhir & luaran** (PDF, maks sesuai validasi). Satu laporan per kelompok.
3. DPL melihat di **Dosen → Laporan Akhir** (`/dosen/laporan-akhir`):
   - **Approve** → status **Disetujui**.
   - **Revisi** → status dikembalikan; **mahasiswa dapat submit ulang**.

### 10.4 Evaluasi (UC-13 & UC-17)

- **Evaluasi Desa (oleh operator desa):** login `desa@wanakaya.go.id` → **Desa → Evaluasi** (`/desa/evaluasi`). Isi skor 1–5: kualitas program, manfaat, kedisiplinan + catatan.
- **Evaluasi DPL (oleh dosen):** login DPL → **Dosen → Evaluasi** (`/dosen/evaluasi`). Isi nilai 0–100 + catatan kinerja kelompok.

### 10.5 Akses Dokumen yang Di-upload

File (surat, proposal, laporan, foto logbook) **tidak** diakses lewat path publik langsung. Semua lewat rute aman `files/{jenis}/{path}` (`/files/...`) yang:
- menuntut login **dan**
- memeriksa otorisasi per jenis file:
  - `permohonan` / `legalitas` — PT & Bapperida
  - `laporan-akhir` — mahasiswa/DPL/PT/Bapperida terkait
  - `logbook` — mahasiswa/DPL terkait

Akses tidak sah → **403**.

---

## 11. Modul Pendukung (Master Data, GIS, Monitoring, Audit)

### 11.1 Dashboard Per-Role

- Bapperida → `/bapperida` — statistik pengajuan, matching, kelompok aktif.
- PT, Mahasiswa, DPL, Desa, Kecamatan, OPD → masing-masing `/dashboard`, `/pt`, `/mahasiswa`, `/dosen`, `/desa`, `/kecamatan`, `/perangkat-daerah` — ringkasan peran masing-masing.

### 11.2 Master Data Generik (UC-08) — Kecamatan & OPD

Bapperida → **Master Data** (`/master-data`). Dikelola generik:
- **Kecamatan** (nama, kode wilayah) dan **Perangkat Daerah / OPD** (nama, bidang tugas).
- **Tambah / Edit / Hapus** via satu controller. Coba:
  - **Tambah** data baru → muncul di tabel.
  - **Edit** → modal terisi nilai lama → simpan.
  - **Hapus**: kecamatan yang masih punya desa **ditolak sistem** (ada peringatan); data yang tidak direferensikan bisa dihapus.
- **Kode wilayah kecamatan wajib unik** (validasi).
- Jenis yang punya modul khusus **tidak** diduplikasi di sini: desa (CRUD kaya di `bapperida/desa`), PT (alur persetujuan), isu strategis (input OPD).

### 11.3 CRUD Desa Kaya (Bapperida)

Login Bapperida → **Bapperida → Desa** (`/bapperida/desa`). Kelola profil desa lengkap: identitas, **kecamatan**, **kode wilayah**, jumlah penduduk, luas, **latitude/longitude** (untuk GIS), plus **potensi / permasalahan / kebutuhan** desa (dijadikan input matching).

### 11.4 Isu Strategis oleh OPD (UC-10)

Login OPD (`opd@kominfo.go.id`) → **Perangkat Daerah → Isu Strategis** (`/perangkat-daerah/isu-strategis`). Tambah/hapus isu prioritas daerah (kategori, deskripsi, wilayah terdampak, rekomendasi tema) — dipakai bobot 25% matching.

### 11.5 Profil & Potensi Desa (Operator Desa)

Login `desa@wanakaya.go.id` → **Desa → Profil Desa** (`/desa/profil`):
- Lihat/ubah profil (edit mode).
- Kelola **Potensi**, **Permasalahan**, dan **Kebutuhan** desa (tambah/hapus) — memengaruhi rekomendasi matching.

### 11.6 Dashboard GIS (Peta) — UC-09

Login Bapperida → menu **Peta (GIS)** (`/gis`):
- Peta **Leaflet** dengan marker desa (dari `desa.latitude/longitude`).
- Klik marker → popup info desa: nama, kecamatan, jumlah kelompok KKN aktif.
- Data peta dari endpoint `/gis/data` (GeoJSON `FeatureCollection`).

### 11.7 Dashboard Monitoring & Evaluasi — UC-09

Login Bapperida → **Monitoring & Evaluasi** (`/bapperida/monitoring`):
- Statistik: jumlah PT, permohonan, mahasiswa, kelompok aktif, desa aktif.
- Distribusi kelompok per status; kelompok aktif per PT; rata-rata evaluasi desa & evaluasi DPL.
- Data agregasi di-*cache* 120 detik, di-invalidasi otomatis setelah approval/evaluasi/penutupan periode.

### 11.8 Penutupan Periode KKN

Login Bapperida → **Penutupan Periode** (`/bapperida/penutupan-periode`):
1. Daftar kelompok **Aktif** tampil (kode, PT, tema, desa lokasi).
2. Klik **Tutup Periode** → konfirmasi → semua kelompok aktif jadi **Selesai**.
3. Mahasiswa (`andi@uin.ac.id`) yang mencoba isi logbook → **ditolak** (kelompok sudah selesai).
4. DPL (`siti@uin.ac.id`) menerima notifikasi "periode KKN ditutup".

### 11.9 Aktivitas Sistem (Audit Trail)

Bapperida → **Aktivitas Sistem** (`/activity-log`):
- Jejak aksi: waktu, pengguna, role, aksi, deskripsi, IP.
- Cari/filter aksi (mis. `tutup_periode`, `setujui_pelaksanaan`, verifikasi, matching).
- Aksi terbaru tampil paling atas.

---

## 12. Notifikasi

- **Bell di navbar** → dropdown popup (bukan halaman baru).
- Klik satu notifikasi → ditandai **dibaca** (badge berkurang) → **diarahkan ke halaman sumber** (bila notif punya URL).
- Halaman penuh: menu user → **Notifikasi** (`/notifications`). Di sana ada tombol **Tandai Semua Dibaca**.
- Simpan: tabel `notifications`, dikirim **sinkron** (tanpa queue worker).
- Notifikasi lama tanpa `url` (dibuat sebelum fitur URL) fallback ke halaman notifikasi — buat ulang via aksi nyata bila ingin URL.

---

## 13. Checklist Cepat & Pemecahan Masalah

### 13.1 Checklist Verifikasi Lengkap

> Jalankan berurutan dengan akun di §3 untuk memastikan seluruh sistem sehat:

| # | Langkah | Akun | Hasil yang Diharapkan |
|---|---|---|---|
| 1 | Setujui PT `STIKes Sehat Jaya` | `admin@...` | Status → Disetujui |
| 2 | Ajukan permohonan baru | `pt@uin.ac.id` | Muncul flash sukses |
| 3 | Verifikasi permohonan | `admin@...` | Status → Terverifikasi |
| 4 | Jalankan matching | `admin@...` | Muncul ranking desa |
| 5 | Verifikasi kesiapan desa | `kec@haurgeulis.go.id` | Desa → Siap |
| 6 | Approval final | `admin@...` | Kelompok → Aktif |
| 7 | Isi logbook | `andi@uin.ac.id` | Tersimpan, status menunggu |
| 8 | Approve logbook | `siti@uin.ac.id` | Status → Disetujui |
| 9 | Upload laporan akhir | `andi@uin.ac.id` | Tersimpan |
| 10 | Verifikasi laporan | `siti@uin.ac.id` | Status → Disetujui |
| 11 | Isi evaluasi | `desa@wanakaya.go.id` & `siti@uin.ac.id` | Tersimpan |
| 12 | Cek monitoring & GIS | `admin@...` | Angka & peta terisi |
| 13 | Tutup periode | `admin@...` | Kelompok → Selesai |

### 13.2 Hal yang Sering Membingungkan

| Hal | Di mana | Catatan |
|---|---|---|
| Akun lupa | §3 tabel akun | Reset via tinker (§3.1) |
| Data kotor / mau bersih | §2.3 `migrate:fresh --seed` | Hapus semua & seed ulang |
| Notifikasi tidak muncul | tabel `notifications` & `data['url']` | Notifikasi sinkron; cek baris notifikasi ada |
| Upload file tidak tampil | `public/storage` | Pastikan `php artisan storage:link` sudah jalan |
| File PDF > 5MB / bukan PDF | validasi form | Ditolak otomatis, periksa pesan error |
| Akses menu tidak ada | sidebar per-role | Halaman dibatasi `role:` — login dengan role yang benar |
| Route `data` (AJAX) 404/405 | `routes/web.php` | Route `data` harus dideklarasikan sebelum route `{model}` |
| File download 403 | §10.5 | Otorisasi per jenis file & role |
| `php artisan serve` error koneksi DB | MySQL XAMPP | Pastikan MySQL hidup (XAMPP Control Panel) |

---

## 14. Catatan Teknis Ringkas

- **Stack:** Laravel 12 + MySQL (XAMPP). CSS/JS manual di `public/css/app.css` & `public/js/app.js` (bukan build Vite).
- **Autentikasi:** Laravel Breeze kustom + middleware role (`role:`). Rute `/` mengarahkan sesuai status login (guest → halaman landing, login → dashboard).
- **Notifikasi in-app:** tabel `notifications`, **sinkron** (tanpa queue worker) — sesuai desain awal.
- **Pagination tabel list:** jQuery/AJAX **server-side** via endpoint `.../data`.
- **Cache dashboard monitoring:** `Cache::remember` 120 detik; di-invalidasi setelah approval, evaluasi, dan penutupan periode.
- **Activity log:** seluruh aksi penting ditulis ke `activity_log` → menu **Aktivitas Sistem**.
- **File aman:** dokumen tidak diekspos via symlink publik, melainkan rute `files/...` ber-otorisasi.
- **Data master contoh:** 31 kecamatan & 318 desa (sumber resmi indramayukab.go.id); koordinat GIS & data penduduk menunggu data resmi Bapperida.
- **Konvensi commit:** prefix `feat:` / `fix:` / `docs:` / `style:` / `chore:`.

---

*Dokumen berikutnya: `07-load-testing.md`.*