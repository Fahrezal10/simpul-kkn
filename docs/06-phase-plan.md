# 06 — Rencana Fase Pengembangan (Phase Plan)
## SIMPUL-KKN

Dokumen sebelumnya: `00-design-system.md` s.d. `05-activity-diagram.md`

**Target:** Sistem full-feature dalam **1 bulan (±22 hari kerja)**, tim **2 developer**.

> ⚠️ **Catatan realitas:** Target ini agresif untuk cakupan 17 use case + Matching Engine + GIS + 7 role + Dashboard Evaluasi. Rencana di bawah disusun untuk **mengejar target tersebut**, dengan fitur inti (alur pengajuan → matching → pelaksanaan) selalu selesai lebih dulu dan solid, sementara fitur pendukung (analitik lanjutan, kustomisasi bobot matching, dsb.) diletakkan di fase akhir sebagai yang **pertama dikorbankan** bila waktu tidak cukup. Tiap fase ditandai tingkat risiko jadwal (🟢 Aman / 🟡 Ketat / 🔴 Berisiko meleset).

---

## 1. Pembagian Tim

Dengan 2 developer, pembagian yang paling efisien adalah **per lapisan**, bukan per modul (menghindari bottleneck saling tunggu):

| Developer | Fokus |
|---|---|
| **Dev A (Backend-lead)** | Database & migration, auth & role middleware, Matching Engine, seluruh Controller/Service logic, API/endpoint AJAX |
| **Dev B (Frontend-lead)** | Blade layout & komponen, integrasi DataTables, integrasi Leaflet GIS, styling (Bootstrap + design system), form validasi client-side |

Kedua developer tetap saling review kode satu sama lain di setiap akhir fase (pair-check), karena tim kecil tidak punya QA terpisah.

---

## 2. Rincian Fase Pengembangan

### 🔹 Fase 0 — Persiapan
**Estimasi: 2-3 hari** · 🟢 *Aman*

- Setup project Laravel + XAMPP environment kedua developer
- Migration seluruh tabel dari `03-erd.md`
- Setup autentikasi (Laravel Breeze) + role middleware dasar
- Setup layout utama Blade (sidebar, navbar, tema warna dari `00-design-system.md`)
- Seeder master data awal: role, kecamatan, desa (data riil dari Bapperida — **minta di awal, jangan tunggu**)

**Definition of Done:** Developer bisa login, redirect ke dashboard kosong sesuai role, struktur DB sudah lengkap.

---

### 🔹 Fase 1 — Alur Pengajuan (Modul PT + Bapperida bagian verifikasi)
**Estimasi: 6-7 hari** · 🟢 *Aman — ini fondasi, harus solid*

| Pekerjaan | Dev A (Backend) | Dev B (Frontend) |
|---|---|---|
| Registrasi & Approval PT | CRUD `perguruan_tinggi`, logic UC-01 | Form registrasi, halaman approval Bapperida |
| Pengajuan Permohonan | Logic UC-02 & UC-03 (permohonan + input mahasiswa/DPL, termasuk import Excel) | Form pengajuan permohonan, tabel mahasiswa dinamis (jQuery add-row) |
| Verifikasi | Logic UC-05 verifikasi permohonan | Halaman daftar permohonan masuk + detail (Bapperida) |
| Notifikasi & Status | Notifikasi in-app (Laravel Database Notifications) | UC-04 halaman status permohonan (badge status) |

**Definition of Done:** PT bisa registrasi → login → ajukan permohonan lengkap dengan mahasiswa & DPL → Bapperida bisa lihat, verifikasi/tolak → PT lihat status berubah real-time.

---

### 🔹 Fase 2 — Matching, Kecamatan, Desa
**Estimasi: 6-7 hari** · 🟡 *Ketat — Matching Engine adalah bagian paling kompleks*

| Pekerjaan | Dev A (Backend) | Dev B (Frontend) |
|---|---|---|
| Matching Engine | **UC-06:** hitung skor, simpan `riwayat_matching`, flag tumpang tindih | Halaman hasil ranking matching (tabel skor + alasan) |
| Verifikasi Kecamatan | Logic UC-11 verifikasi kecamatan | Halaman verifikasi kecamatan |
| Approval Final | Logic UC-07 approval final Bapperida | Halaman review & approve lokasi final |
| Modul Desa & OPD | UC-12 (profil/potensi/permasalahan/kebutuhan), UC-10 OPD isu strategis | Form-form modul Desa & OPD, termasuk input lat/long |

**Definition of Done:** Data desa & isu strategis dapat diinput → matching menghasilkan ranking → kecamatan verifikasi → Bapperida approve → status kelompok KKN jadi "Aktif".

> 🔴 **Titik risiko tertinggi jadwal:** jika bobot skor & algoritma matching perlu banyak iterasi/uji coba bersama Bapperida, fase ini paling mungkin molor. Sarannya: kunci dulu bobot default (30/25/25/20 dari `01-prd.md`), buat **konfigurasi bobot editable di fase pasca-launch**, bukan sekarang.

---

### 🔹 Fase 3 — Pelaksanaan (Mahasiswa & DPL)
**Estimasi: 6-7 hari** · 🟢 *Aman — fitur relatif sederhana, volume kerja tinggi tapi tidak kompleks*

| Pekerjaan | Dev A (Backend) | Dev B (Frontend) |
|---|---|---|
| Logbook & Approval | Logic UC-14 logbook + UC-16 approval DPL | Form logbook (mobile-friendly!), halaman review DPL |
| Laporan Akhir | Logic UC-15 upload laporan akhir | Form upload laporan & luaran |
| Evaluasi | Logic UC-13 & UC-17 evaluasi (desa & DPL) | Form evaluasi |
| Optimasi & Uji Responsif | Optimasi query (index, eager loading) — penting karena logbook berpotensi ribuan baris | Responsif testing di HP (karena mahasiswa akses dari lapangan) |
| Buffer | Perbaikan bug dari review fase sebelumnya | Perbaikan bug dari review fase sebelumnya |

**Definition of Done:** Mahasiswa isi logbook harian dari HP → DPL approve → di akhir periode mahasiswa upload laporan akhir → Desa & DPL isi evaluasi.

---

### 🔹 Fase 4 — Dashboard, GIS, Testing, Deploy
**Estimasi: 6-7 hari** · 🔴 *Berisiko — banyak hal dikompres di fase terakhir*

| Pekerjaan | Dev A (Backend) | Dev B (Frontend) |
|---|---|---|
| Dashboard & GIS | Query agregasi Dashboard Monitoring & Evaluasi | Integrasi Leaflet.js (Dashboard GIS), render marker desa |
| Master Data | UC-08 kelola master data (CRUD generik) | Styling final dashboard (card statistik, grafik ringkas) |
| Bug Fixing & Testing | Bug fixing lintas modul, load testing awal (lihat `07-load-testing.md`) | Bug fixing UI, uji responsif lintas device |
| Deployment | Deployment ke server (staging) | User Acceptance Test (UAT) bersama Bapperida |
| Finalisasi | Perbaikan hasil UAT, deploy production | Perbaikan hasil UAT, dokumentasi manual pengguna |

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
| Hanya 2 developer, tidak ada QA dedicated | Bug lolos ke production | Alokasikan hari buffer tiap fase (sudah dimasukkan di jadwal), saling review kode |
| Testing baru intensif di Fase 4 | Load testing/UAT terburu-buru | Idealnya smoke test manual dilakukan tiap akhir fase, bukan ditumpuk di akhir (lihat `07-load-testing.md` untuk skenario ringan yang bisa dijalankan progresif) |

---

*Dokumen berikutnya: `07-load-testing.md` — Skenario & Target Load Testing*
