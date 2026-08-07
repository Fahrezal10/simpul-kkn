# 06 — Rencana Fase Pengembangan (Phase Plan)
## SIMPUL-KKN

Dokumen sebelumnya: `00-design-system.md` s.d. `05-activity-diagram.md`

**Target:** Sistem full-feature dalam **1 bulan (±22 hari kerja)**, tim **2 developer**.

> ⚠️ **Catatan realitas:** Target ini agresif untuk cakupan 17 use case + Matching Engine + GIS + 7 role + Dashboard Evaluasi. Rencana di bawah disusun untuk **mengejar target tersebut**, dengan fitur inti (alur pengajuan → matching → pelaksanaan) selalu selesai lebih dulu dan solid, sementara fitur pendukung (analitik lanjutan, kustomisasi bobot matching, dsb.) diletakkan di fase akhir sebagai yang **pertama dikorbankan** bila waktu tidak cukup. Tiap fase ditandai tingkat risiko jadwal (🟢 Aman / 🟡 Ketat / 🔴 Berisiko meleset).

---

## 1. Tim, Model Kerja & Skema Branch

### 1.1 Tim

| Developer | Peran |
|---|---|
| 👤 **ical** | **Fullstack Developer** — bertanggung jawab penuh atas fitur yang dipegang (DB, backend logic, blade, styling, uji). |
| 👤 **anti** | **Fullstack Developer** — identik: bertanggung jawab penuh atas fitur yang dipegang. |

### 1.2 Model Kerja — Pembagian Fitur Vertikal (Fullstack)

> Bukan pembagian per lapisan (bukan "satu orang backend, satu orang frontend"). Setiap **fitur berdiri sendiri** dan dikerjakan **end-to-end oleh satu orang**:

- **1 fitur = 1 branch = 1 PIC (fullstack).** PIC menyelesaikan backend + frontend fitur itu dari awal sampai tuntas, lalu smoke test dan merge.
- Tidak ada dua orang mengerjakan/membantu di branch yang sama — konflik di dalam satu fitur mustahil.
- Di akhir tiap branch/fase, PIC **pair-check** (review kode) oleh rekan — tim kecil tanpa QA terpisah.

### 1.3 Skema Penamaan Branch

| Jenis | Pola | Contoh |
|---|---|---|
| Fitur baru | `feat/fase<N>-<nama-fitur>-<pic>` | `feat/fase2-modul-desa-anti`, `feat/fase2-dashboard-integrasi-anti` |
| Perbaikan bug | `fix/fase<N>-<deskripsi>-<pic>` | `fix/fase2-bug-approval-final-ical` |
| Refactor/kecil | `chore/fase<N>-<deskripsi>-<pic>` | `chore/fase3-optimasi-query-ical` |

Aturan:
- `<N>` = nomor fase (0, 1, 2, …). `<pic>` = `ical` atau `anti` (pemilik branch).
- Semua branch dibuat dari `main` yang **paling terbaru** (`git pull` dulu).
- Detail branch per sub-fase ada di **Bagian 6**.

### 1.4 Strategi Minimalisir Merge Conflict (ringkas)

1. **Isolasi folder per role.** Folder `app/Http/Controllers/<Role>/` dan `resources/views/<role>/` sudah terpisah per role — tiap fitur menyentuh folder yang berbeda, sehingga overlapping hampir mustahil.
2. **Urutan dependensi eksplisit.** Modul pondasi dikerjakan lebih dulu sebelum dibagikan (lihat Bagian 2 & 6).
3. **File bersama dikontrol.** `layouts/app.blade.php`, `routes/web.php`, `app.css`/`app.js`, dan `DatabaseSeeder.php` punya aturan kepemilikan khusus (matriks di §6.1).
4. **Mode default: berurutan.** Satu branch hidup → selesai → merge → branch berikutnya. Dua branch boleh hidup bersamaan **hanya** bila folder/rolenya benar-benar terisolasi (lihat matriks §6.1).

---

## 2. Rincian Fase Pengembangan

### 🔹 Fase 0 — Persiapan (✅ SELESAI)
**Estimasi: 2-3 hari** · 🟢 *Aman*

Setup project Laravel + XAMPP, migration seluruh tabel `03-erd.md`, autentikasi (Laravel Breeze) + role middleware, layout utama, seeder master data (role, kecamatan, desa).

**Status:** Sudah terimplementasi (commit `ef1158a`, `b168957`).

---

### 🔹 Fase 1 — Alur Pengajuan (Modul PT + Verifikasi Bapperida) (✅ SELESAI)
**Estimasi: 6-7 hari** · 🟢 *Aman*

Registrasi & approval PT (UC-01), pengajuan permohonan + input mahasiswa/DPL (UC-02/03), verifikasi Bapperida (UC-05), notifikasi in-app (SYS-01), status permohonan (UC-04).

**Status:** Sudah terimplementasi & teruji end-to-end (commit `5f4fada`, `80a78da`). Import Excel mahasiswa ditunda ke Fase 3 / pasca-launch.

---

### 🔹 Fase 2 — Matching, Kecamatan, Desa
**Estimasi: 6-7 hari** · 🟡 *Ketat — Matching Engine adalah bagian paling kompleks*

> **Urutan pengerjaan (dependensi):** `modul-desa` (**fondasi data**, harus dulu) → `matching-engine` → `verifikasi-kecamatan` → `dashboard-integrasi`. Detail di Bagian 6.

| Sub-fase (fitur) | PIC | Branch | Lingkup inti (backend + frontend) |
|---|---|---|---|
| Modul Desa & OPD — UC-12, UC-10 | 👤 anti | `feat/fase2-modul-desa-anti` | CRUD desa (profil, lat/long), potensi, permasalahan, kebutuhan; Perangkat Daerah & Isu Strategis |
| Matching Engine — UC-06 | 👤 ical | `feat/fase2-matching-engine-ical` | Hitung skor (bobot default 30/25/25/20), simpan `riwayat_matching`, flag tumpang tindih, halaman ranking + alasan |
| Verifikasi Kecamatan — UC-11 & Approval Final — UC-07 | 👤 ical | `feat/fase2-verifikasi-kecamatan-ical` | Verifikasi kesiapan desa; assign/override lokasi → status kelompok KKN **"Aktif"** |
| Dashboard Integrasi | 👤 anti | `feat/fase2-dashboard-integrasi-anti` | Ringkasan statistik per-role, integrasi alur pengajuan → matching → aktif |

**Definition of Done:** Data desa & isu strategis dapat diinput → matching menghasilkan ranking → kecamatan verifikasi → Bapperida approve → status kelompok KKN jadi "Aktif".

> 🔴 **Titik risiko tertinggi jadwal:** jika bobot skor & algoritma matching perlu banyak iterasi/uji coba bersama Bapperida, fase ini paling mungkin molor. Sarannya: kunci dulu bobot default (30/25/25/20 dari `01-prd.md`), buat **konfigurasi bobot editable di fase pasca-launch**, bukan sekarang.

---

### 🔹 Fase 3 — Pelaksanaan (Mahasiswa & DPL)
**Estimasi: 6-7 hari** · 🟢 *Aman — fitur relatif sederhana, volume kerja tinggi tapi tidak kompleks*

> **Urutan pengerjaan (dependensi):** `logbook` → `laporan-akhir` → `evaluasi` → `optimasi-responsif` (**paling akhir**, karena menyentuh banyak file lintas modul). Detail di Bagian 6.

| Sub-fase (fitur) | PIC | Branch | Lingkup inti (backend + frontend) |
|---|---|---|---|
| Logbook & Approval — UC-14, UC-16 | 👤 anti | `feat/fase3-logbook-anti` | Logbook harian mobile-friendly (form deskripsi/foto), DPL approve, daftar/status per kelompok |
| Laporan Akhir — UC-15 | 👤 ical | `feat/fase3-laporan-akhir-ical` | Upload laporan & luaran + status; DPL lihat/verifikasi |
| Evaluasi — UC-13, UC-17 | 👤 ical | `feat/fase3-evaluasi-ical` | Evaluasi Desa (UC-13) & DPL (UC-17) |
| Optimasi & Uji Responsif | 👤 anti | `feat/fase3-optimasi-responsif-anti` | Optimasi query (index, eager loading — logbook berpotensi ribuan baris), uji responsif HP, konsistensi UI |

**Definition of Done:** Mahasiswa isi logbook harian dari HP → DPL approve → di akhir periode mahasiswa upload laporan akhir → Desa & DPL isi evaluasi.

---

### 🔹 Fase 4 — Dashboard, GIS, Testing, Deploy
**Estimasi: 6-7 hari** · 🔴 *Berisiko — banyak hal dikompres di fase terakhir*

> **Urutan pengerjaan (dependensi):** `dashboard-monitoring` → `dashboard-gis` → `master-data` → `uat-deploy` (**paling akhir**). Detail di Bagian 6.

| Sub-fase (fitur) | PIC | Branch | Lingkup inti (backend + frontend) |
|---|---|---|---|
| Dashboard Monitoring & Evaluasi | 👤 ical | `feat/fase4-dashboard-monitoring-ical` | Query agregasi dashboard monitoring/evaluasi (jumlah mahasiswa, kelompok aktif, desa aktif, statistik ringkas) |
| Dashboard GIS | 👤 anti | `feat/fase4-dashboard-gis-anti` | Integrasi Leaflet.js, marker desa dari `desa` (lat/long), klik marker → info desa |
| Master Data — UC-08 | 👤 anti | `feat/fase4-master-data-anti` | CRUD generik master data (kecamatan/desa/…) via satu controller generik |
| UAT, Deploy & Finalisasi | 👤 ical | `feat/fase4-uat-deploy-ical` | UAT bersama Bapperida, perbaikan bug, load test, dokumentasi manual, deploy staging/produksi |

**Definition of Done:** Seluruh modul terintegrasi, dashboard GIS & monitoring berjalan, sistem sudah di-UAT minimal oleh Bapperida, siap pakai.

> 🔴 Kalau ada slippage dari Fase 2 (Matching), yang **pertama dipangkas** di Fase 4: fitur "Dashboard Evaluasi" versi lengkap (dampak ekonomi/sosial/inovasi) bisa disederhanakan jadi angka ringkas dulu, statistik detail menyusul pasca-launch.

---

## 3. Fitur yang Diusulkan Ditunda ke Fase Pasca-Launch (jika waktu mepet)

Berdasarkan prioritas Should/Could di `01-prd.md`, ini kandidat pertama yang **aman ditunda** tanpa merusak alur inti:

| Fitur | Alasan Aman Ditunda |
|---|---|
| Import Excel data mahasiswa (PT-04) | Input manual satu-satu tetap berfungsi sebagai fallback |
| Rekomendasi tema otomatis dari OPD (OPD-03) | Bisa manual dulu, OPD tetap isi isu strategis (wajib untuk matching) |
| Evaluasi OPD (OPD-04) | Tidak masuk alur inti KKN |
| Konfigurasi bobot matching via UI | Hardcode dulu di kode, admin belum bisa ubah dari dashboard |
| Statistik lanjutan (dampak ekonomi/sosial/inovasi) | Bisa tampil versi ringkas dulu |
| Export laporan (PDF/Excel) dari dashboard | Fitur nice-to-have, tidak disebut eksplisit di KAK sebagai wajib |

---

## 4. Checklist Go-Live

- [ ] Seluruh Must-priority user story (`01-prd.md` §6) berfungsi end-to-end
- [ ] Data master (desa, kecamatan, PT) sudah diisi data riil dari Bapperida — bukan dummy
- [ ] Role & permission teruji untuk ketujuh role
- [ ] Responsif teruji di minimal 1 device desktop + 1 device mobile
- [ ] Backup database awal (sebelum go-live) sudah dibuat
- [ ] UAT bersama minimal perwakilan Bapperida dan 1 PT dilakukan
- [ ] Manual pengguna dasar tersedia (boleh ringkas, PDF/markdown)

---

## 5. Risiko Utama & Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Data desa (koordinat, profil) belum siap dari Bapperida | GIS & matching tidak bisa diuji penuh | Minta data di **Fase 0**, bukan menunggu Fase 2 |
| Iterasi bobot matching berulang-ulang | Fase 2 molor, efek domino ke fase berikutnya | Kunci bobot default, buka opsi ubah bobot di fase pasca-launch |
| Dua developer menyentuh file yang sama saat branch paralel | Merge conflict & saling menimpa | Patuhi matriks kepemilikan file (§6.1); default kerja berurutan |
| Hanya 2 developer, tidak ada QA dedicated | Bug lolos ke production | Alokasikan hari buffer tiap fase (sudah dimasukkan di jadwal), saling review kode |
| Testing baru intensif di Fase 4 | Load testing/UAT terburu-buru | Idealnya smoke test manual dilakukan tiap akhir fase, bukan ditumpuk di akhir (lihat `07-load-testing.md` untuk skenario ringan yang bisa dijalankan progresif) |

---

## 6. Rencana Eksekusi per Sub-Fase (Fase 2+) — Branch, PIC & Isolasi File

> **Update (pasca-Fase 1):** bagian ini adalah **panduan eksekusi harian** untuk Fase 2 dan seterusnya (Bagian 2 di atas tetap berlaku sebagai target & ringkasan). Diterapkan berdasarkan instruksi terbaru:
>
> **Tim:** 👤 **ical** + 👤 **anti** (dua-duanya **Fullstack**).
> **Skema:** **1 fitur = 1 branch = 1 PIC (fullstack)**. PIC mengerjakan **backend + frontend** fitur itu end-to-end — tidak ada pembagian FE/BE dalam satu fitur, tidak ada yang membantu di branch orang lain.
> **Nama branch:** `feat/fase<N>-<fitur>-<pic>` (mis. `feat/fase2-modul-desa-anti`), dan `fix/...` untuk perbaikan bug.
> **Alur:** branch dibuat dari `main` terbaru → dikerjakan oleh satu PIC → smoke test → merge → mulai branch berikutnya sesuai urutan dependensi di tiap fase.

### 6.1 Matriks Kepemilikan File (bacaan wajib sebelum mulai)

Inti strategi: **isolasi folder per role** membuat tiap fitur menyentuh direktori berbeda. Folder yang membatasi domain tiap fitur:

| Area | Folder | Siapa yang mengubah |
|---|---|---|
| Controller per role | `app/Http/Controllers/{Bapperida,Desa,Kecamatan,Mahasiswa,Dosen,Shared}/…` | Hanya PIC fitur yang memegang role tersebut (lihat tiap sub-fase) |
| View per role | `resources/views/{bapperida,desa,kecamatan,mahasiswa,dosen}/…` | Hanya PIC fitur yang memegang role tersebut |
| Layanan & model | `app/Services/…`, `app/Models/…` | Hanya PIC fitur yang membuat/memiliki file tersebut |

File/folder **bersama** yang butuh koordinasi (bukan milik satu orang):

| File/folder bersama | Aturan koordinasi |
|---|---|
| `resources/views/layouts/app.blade.php` | Satu-satunya layout (navbar + sidebar). Sebisa mungkin hanya diubah oleh pemilik branch yang fiturnya memang butuh menu/asset baru. Yang lain meminta menu ditambahkan oleh pemilik branch aktif, atau menunggu giliran. |
| `routes/web.php` | Semua boleh menyentuh, **tapi hanya di blok `prefix` milik modulnya sendiri** (mis. blok `desa`, `kecamatan`, `mahasiswa`, `dosen`, `bapperida`). Jangan edit blok modul lain. |
| `public/css/app.css` & `public/js/app.js` | Sebisa mungkin **tidak diubah siapa pun**; pakai `@push('styles')` / `@push('scripts')` di blade masing-masing. Asset global baru dikoordinasikan dulu. |
| `database/seeders/DatabaseSeeder.php` | **Satu pemilik per fase** = pemilik branch pertama fase itu (Fase 2: anti; Fase 3: anti; Fase 4: ical). Yang lain membuat seeder sendiri lalu minta dipanggil dari `DatabaseSeeder.php`. |
| `resources/views/dashboard/…` & `app/Http/Controllers/Shared/…` | Fase 2: anti (`dashboard-integrasi`). Fase 4: ical (`dashboard-monitoring`) lalu anti (`dashboard-gis`) — dikerjakan berurutan, tidak paralel. |

**Kapan boleh paralel?** Dua branch boleh hidup bersamaan **hanya** jika seluruh file yang disentuh tidak tumpang tindih (cek matriks di atas) **dan** tidak ada yang menyentuh file bersama di waktu yang sama. Bila ragu, default tetap **berurutan**.

---

### 🔹 Fase 2 — Matching, Kecamatan, Desa

> **Urutan (dependensi):** `modul-desa` → `matching-engine` → `verifikasi-kecamatan` → `dashboard-integrasi`.
> **Modul pondasi:** `modul-desa` (anti) **wajib lebih dulu** — menyediakan data `Desa`, `DesaPotensi`, `DesaPermasalahan`, `DesaKebutuhan`, `PerangkatDaerah`, `IsuStrategis`, `Kecamatan` yang menjadi **input matching**. Bila data riil belum tersedia, anti mengisi dummy lewat seeder sebelum modul-matching diambil alih.

#### `feat/fase2-modul-desa-anti` — PIC: 👤 **anti** (fullstack)
**Fitur:** CRUD data desa (profil, lat/long), potensi (UC-12), permasalahan, kebutuhan; CRUD Perangkat Daerah & Isu Strategis (UC-10).

**Kerjaan (backend + frontend):**
- Backend: controller `app/Http/Controllers/Desa/…` & `PerangkatDaerah/IsuStrategisController.php` *(baru)*, model `Desa`, `DesaPotensi`, `DesaPermasalahan`, `DesaKebutuhan`, `PerangkatDaerah`, `IsuStrategis`, `Kecamatan`, route blok `prefix('desa')` & `prefix('perangkat-daerah')` *(blok baru)*, `DatabaseSeeder.php` (**pemilik: anti** di fase ini)
- Frontend: `resources/views/desa/…` & `resources/views/perangkat-daerah/…` *(baru)*

> ⚠️ **Catatan `DatabaseSeeder.php`:** anti yang memegang file ini selama Fase 2. Bila ical (modul `matching-engine`) butuh menambah data contoh, ia membuat seeder sendiri lalu minta anti memanggilnya dari `DatabaseSeeder.php`.

#### `feat/fase2-matching-engine-ical` — PIC: 👤 **ical** (fullstack)
**Fitur:** Hitung skor rekomendasi desa per kelompok KKN (rule-based, bobot default 30/25/25/20 dari `01-prd.md` §7), simpan ke `riwayat_matching`, tandai flag tumpang tindih, tampilkan hasil ranking + alasan.

**Kerjaan (backend + frontend):**
- Backend: `app/Services/MatchingService.php` *(baru — inti algoritma)*, `app/Http/Controllers/Bapperida/MatchingController.php` *(baru)*, model `RiwayatMatching`/`KelompokKkn`, route blok `bapperida` (`matching.{index,data,show,run,override}`), seeder contoh `riwayat_matching`
- Frontend: `resources/views/bapperida/matching/index.blade.php` & `show.blade.php` *(baru)*, style/script via `@push`

> **Dependensi:** baca model & data dari `modul-desa` (sudah ada saat ical mulai). Jangan membuat ulang model `Desa`/`IsuStrategis` — itu milik anti.

#### `feat/fase2-verifikasi-kecamatan-ical` — PIC: 👤 **ical** (fullstack)
**Fitur:** Kecamatan verifikasi kesiapan desa (UC-11) — isi/verifikasi `verifikasi_kecamatan` → status desa "siap ditugaskan"; Approval final Bapperida (UC-07) — assign/override lokasi → status kelompok KKN jadi **"Aktif"** (butuh hasil matching).

**Kerjaan (backend + frontend):**
- Backend: `app/Http/Controllers/Kecamatan/VerifikasiKecamatanController.php` & `Bapperida/ApprovalFinalController.php` *(baru)*, model `VerifikasiKecamatan`, `KelompokKkn` (status Aktif), `Desa` (status lokasi), route blok `prefix('kecamatan')` *(baru)* & baris route approval-final di blok `bapperida`, seeder/activity log status
- Frontend: `resources/views/kecamatan/verifikasi/…` & `bapperida/approval-final/…` *(baru)*

> Jika butuh kolom/status baru pada `desa`, koordinasikan dengan anti (pemilik modul desa) supaya tidak bentrok.

#### `feat/fase2-dashboard-integrasi-anti` — PIC: 👤 **anti** (fullstack)
**Fitur:** Dashboard & ringkasan statistik per-role (jumlah permohonan, kelompok aktif, desa terverifikasi), integrasi hasil seluruh modul Fase 2 agar alur pengajuan → matching → aktif utuh.

**Kerjaan (backend + frontend):**
- Backend: `app/Http/Controllers/Shared/DashboardController.php` (query agregasi)
- Frontend: `resources/views/dashboard/index.blade.php` (konten statistik), menu sidebar modul yang sudah selesai di `layouts/app.blade.php`

> ⚠️ Karena pemilik branch ini adalah anti dan layout hanya diubah satu orang, menu yang ditambahkan di layout dikerjakan di branch ini — pastikan tidak ada branch lain yang hidup bersamaan.

---

### 🔹 Fase 3 — Pelaksanaan (Mahasiswa & DPL)

> **Urutan (dependensi):** `logbook` → `laporan-akhir` → `evaluasi` → `optimasi-responsif` (**terakhir**, karena menyentuh banyak file lintas modul).
> **Perhatian:** `logbook` (anti) & `laporan-akhir` (ical) sama-sama menambah route di blok `mahasiswa`/`dosen` → keduanya **harus berurutan** (logbook dulu), jangan paralel.

#### `feat/fase3-logbook-anti` — PIC: 👤 **anti** (fullstack)
**Fitur:** Logbook harian mahasiswa (UC-14) mobile-friendly (form deskripsi/foto), DPL approve (UC-16), daftar/status per kelompok.

**Kerjaan (backend + frontend):**
- Backend: `app/Http/Controllers/Mahasiswa/LogbookController.php` & `Dosen/LogbookApprovalController.php` *(baru)*, model `Logbook`, route blok `prefix('mahasiswa')` & `prefix('dosen')` *(blok baru)*, seeder contoh logbook
- Frontend: `resources/views/mahasiswa/logbook/…` & `dosen/logbook/…` *(baru)*, menu di layout

#### `feat/fase3-laporan-akhir-ical` — PIC: 👤 **ical** (fullstack)
**Fitur:** Mahasiswa upload laporan & luaran (UC-15) + status; DPL lihat/verifikasi.

**Kerjaan (backend + frontend):**
- Backend: `app/Http/Controllers/Mahasiswa/LaporanAkhirController.php` & `Dosen/…` *(baru)*, model `LaporanAkhir`, route blok `prefix('mahasiswa')` & `prefix('dosen')`
- Frontend: `resources/views/mahasiswa/laporan-akhir/…` & `dosen/laporan-akhir/…` *(baru)*

#### `feat/fase3-evaluasi-ical` — PIC: 👤 **ical** (fullstack)
**Fitur:** Evaluasi dari Desa (UC-13) & DPL (UC-17).

**Kerjaan (backend + frontend):**
- Backend: `app/Http/Controllers/Desa/EvaluasiDesaController.php` & `Dosen/EvaluasiDplController.php` *(baru)*, model `EvaluasiDesa`/`EvaluasiDpl`, route blok `prefix('desa')` & `prefix('dosen')`
- Frontend: `resources/views/desa/evaluasi/…` & `dosen/evaluasi/…` *(baru)*

#### `feat/fase3-optimasi-responsif-anti` — PIC: 👤 **anti** (fullstack)
**Fitur:** Uji responsif HP, optimasi query (index, eager loading — logbook berpotensi ribuan baris), perbaikan konsistensi UI. Menyentuh banyak file lintas modul → dikerjakan **paling akhir** (setelah semua modul Fase 3 di-main), di atas main yang bersih.

---

### 🔹 Fase 4 — Dashboard, GIS, Testing, Deploy (dipangkas bila waktu)

> **Urutan (dependensi):** `dashboard-monitoring` → `dashboard-gis` → `master-data` → `uat-deploy` (**paling akhir**).
> **Perhatian:** `dashboard-monitoring` (ical) & `dashboard-gis` (anti) sama-sama menyentuh `resources/views/dashboard/`, `app/Http/Controllers/Shared/`, dan layout → **berurutan**, jangan paralel.

#### `feat/fase4-dashboard-monitoring-ical` — PIC: 👤 **ical** (fullstack)
**Fitur:** Query agregasi dashboard monitoring & evaluasi (jumlah mahasiswa, kelompok aktif, desa aktif, statistik ringkas).

**Kerjaan (backend + frontend):**
- Backend: `app/Http/Controllers/Shared/DashboardController.php` (query eager loading)
- Frontend: `resources/views/dashboard/index.blade.php` (konten statistik)

#### `feat/fase4-dashboard-gis-anti` — PIC: 👤 **anti** (fullstack)
**Fitur:** Peta Leaflet, marker desa dari `desa` (lat/long sudah ada dari modul desa), klik marker → info desa.

**Kerjaan (backend + frontend):**
- Frontend: `resources/views/dashboard/gis.blade.php` *(baru)*, inisialisasi Leaflet, CDN Leaflet di `layouts/app.blade.php`
- Backend: `app/Http/Controllers/Shared/DashboardGisController.php` (endpoint data desa JSON)

#### `feat/fase4-master-data-anti` — PIC: 👤 **anti** (fullstack)
**Fitur:** CRUD generik master data (kecamatan/desa/…) via satu controller generik (`MasterDataController`).

**Kerjaan (backend + frontend):**
- Backend: `app/Http/Controllers/Shared/MasterDataController.php` *(baru)*, route blok `prefix('master-data')` *(baru)*
- Frontend: View CRUD generik `resources/views/shared/master-data/…` *(baru)*

> ⚠️ Hati-hati terhadap blok route `kecamatan`/`desa` yang sudah ada dari Fase 2 — jangan duplikasi.

#### `feat/fase4-uat-deploy-ical` — PIC: 👤 **ical** (fullstack)
**Fitur:** UAT bersama Bapperida, perbaikan bug hasil UAT, load test (lihat `07-load-testing.md`), dokumentasi manual, deploy staging/produksi.

**Kerjaan (backend + frontend):**
- Backend: konfigurasi environment / deploy, perbaikan bug lintas modul
- Frontend: dokumentasi manual pengguna, uji responsif final

---

### 6.5 Strategi Anti-Konflik & Checklist Sebelum Kerja

Ringkasan tips agar kerja tetap aman:
- **1 fitur = 1 PIC fullstack.** Jangan mengerjakan/membantu di branch milik orang lain — itu satu-satunya cara mencegah dua orang menyentuh file yang sama.
- **`git pull` di `main` sebelum mulai**, dan mulai branch baru dari `main` terbaru.
- **Ikuti urutan dependensi** di tiap fase (modul pondasi lebih dulu). Jangan lompati.
- **Boleh paralel hanya jika folder terisolasi** (cek §6.1). Bila ragu, berurutan.
- **Satu pemilik `DatabaseSeeder.php` per fase** (Fase 2 & 3: anti; Fase 4: ical). Orang lain minta tolong memanggilkan seeder-nya.
- **Layout & `app.css` & `app.js` adalah file bersama** — koordinasikan; sebisa mungkin pakai `@push`.
- **Sebelum merge:** smoke test (jalankan app, login role terkait) → baru merge ke `main`.
- **Checklist pra-kerja:** pastikan `git status` bersih, `git log --oneline -5` sinkron.

---

### 6.6 Ringkasan Siapa PIC Apa (Snapshot)

| PIC | Branch yang dipegang (fullstack) | Modul |
|---|---|---|
| 👤 ical | `feat/fase2-matching-engine-ical`, `feat/fase2-verifikasi-kecamatan-ical`, `feat/fase3-laporan-akhir-ical`, `feat/fase3-evaluasi-ical`, `feat/fase4-dashboard-monitoring-ical`, `feat/fase4-uat-deploy-ical` | Matching, verifikasi kecamatan + approval final, laporan akhir, evaluasi, dashboard monitoring, UAT/deploy |
| 👤 anti | `feat/fase2-modul-desa-anti`, `feat/fase2-dashboard-integrasi-anti`, `feat/fase3-logbook-anti`, `feat/fase3-optimasi-responsif-anti`, `feat/fase4-dashboard-gis-anti`, `feat/fase4-master-data-anti` | Modul desa/OPD, integrasi dashboard, logbook, optimasi responsif, GIS, master data |

> Pembagian seimbang (6:6). Karena tiap fitur dikerjakan oleh satu PIC dan mengikuti urutan dependensi, tidak akan ada dua orang di branch yang sama, dan overlap file praktis nol.

---

*Dokumen berikutnya: `07-load-testing.md` — Skenario & Target Load Testing*
