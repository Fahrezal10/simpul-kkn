# 00 — Design System
## SIMPUL-KKN (Sistem Informasi Manajemen Pengabdian Unggulan dan Kolaborasi Kuliah Kerja Nyata)
**Kabupaten Indramayu — Bapperida**

Referensi: Lampiran Surat Plt. Kepala Bapperida Kabupaten Indramayu No. 000.9.1/172/Rida, 29 Juli 2026 (KAK SIMPUL-KKN)

---

## 1. Overview & Tujuan Sistem

SIMPUL-KKN adalah platform berbasis web yang menjadi media kolaborasi antara **Pemerintah Daerah, Perguruan Tinggi, Perangkat Daerah, Kecamatan, Desa, dan Masyarakat** dalam penyelenggaraan Kuliah Kerja Nyata (KKN) di Kabupaten Indramayu. Sistem ini menggantikan proses manual (surat-menyurat) menjadi satu platform digital terpadu, mulai dari pengajuan permohonan KKN, penentuan lokasi berbasis kecocokan (matching), pelaksanaan dan monitoring kegiatan, hingga evaluasi dampak terhadap masyarakat.

**Tujuan utama sistem:**
1. Meningkatkan efektivitas pelayanan permohonan KKN
2. Menyediakan basis data terpadu pelaksanaan KKN
3. Menyelaraskan tema/program KKN dengan prioritas pembangunan daerah
4. Menghindari tumpang tindih lokasi KKN
5. Memudahkan monitoring kegiatan mahasiswa
6. Mengukur dampak KKN terhadap masyarakat
7. Mendukung pengambilan kebijakan berbasis data

---

## 2. Arsitektur Sistem

### 2.1 Pola Arsitektur
**Monolithic MVC** menggunakan Laravel — dipilih karena tim pengembang kecil/menengah, kebutuhan *time-to-deploy* cepat, kemudahan maintenance oleh tim internal Bapperida atau vendor lokal ke depannya, dan skala pengguna yang belum masif (lihat asumsi skala di §2.4).

```
┌──────────────────────────────────────────────────────────┐
│                        CLIENT (Browser)                   │
│   Desktop & Mobile — Responsive Web (Bootstrap 5)          │
│   Blade Views + jQuery/AJAX + DataTables + Leaflet.js       │
└───────────────────────────┬─────────────────────────────┘
                             │ HTTPS
┌───────────────────────────▼─────────────────────────────┐
│                     LARAVEL APPLICATION                   │
│  ┌───────────┐  ┌────────────┐  ┌──────────────────────┐  │
│  │  Routes    │→ │ Controllers│→ │  Services / Actions   │  │
│  │ (web.php)  │  │            │  │ (Matching Engine, dll)│  │
│  └───────────┘  └────────────┘  └───────────┬──────────┘  │
│                                              │             │
│  ┌───────────────────┐   ┌──────────────────▼──────────┐  │
│  │ Middleware (Role/  │   │        Eloquent Models       │  │
│  │ Gate/Policy)        │   │                              │  │
│  └───────────────────┘   └──────────────────┬──────────┘  │
└──────────────────────────────────────────────┼────────────┘
                                                │
                             ┌──────────────────▼──────────┐
                             │        MySQL Database         │
                             └────────────────────────────┘
                             ┌────────────────────────────┐
                             │  Local Storage (uploads:      │
                             │  proposal, dokumentasi, dll)  │
                             └────────────────────────────┘
```

### 2.2 Alur Bisnis Inti (ringkas — detail di `04-flowchart.md`)

```
Perangkat Daerah/Desa → input isu strategis & kebutuhan
        ↓
Perguruan Tinggi → registrasi akun → ajukan permohonan KKN
        ↓
Bapperida → verifikasi permohonan
        ↓
Matching System → rekomendasi desa (skor: tema + bidang + prioritas + kebutuhan)
        ↓
Kecamatan → verifikasi kesiapan desa → rekomendasi lokasi
        ↓
Bapperida → persetujuan pelaksanaan (lokasi final)
        ↓
Mahasiswa → logbook harian → DPL approve → progress → laporan akhir
        ↓
Desa → evaluasi mahasiswa
        ↓
Bapperida → Dashboard Monitoring & Evaluasi (dampak, statistik)
```

### 2.3 Model Multi-Tenant Logis
Bukan multi-tenant fisik (satu database untuk semua), tapi **multi-tenant logis** menggunakan `perguruan_tinggi_id` sebagai penyekat data di level query (scoped query / global scope Eloquent). Setiap PT hanya melihat data mahasiswa & permohonannya sendiri; Bapperida memiliki akses lintas-tenant (melihat semua data).

### 2.4 Asumsi Skala (dasar untuk NFR & load testing)
| Parameter | Asumsi |
|---|---|
| Jumlah Perguruan Tinggi | 12–30 PT terdaftar |
| Mahasiswa per periode/tahun | 1.000–3.000 |
| Jumlah desa (lokasi potensial) | ±309 desa/kelurahan |
| Pola beban | Bukan real-time tinggi; puncak beban musiman (masa pendaftaran & upload laporan akhir) |
| Concurrent user puncak | Estimasi awal 100–300 user aktif bersamaan |

---

## 3. Tech Stack

| Layer | Teknologi | Catatan |
|---|---|---|
| Backend Framework | **Laravel 11.x** (PHP 8.2+) | LTS terbaru saat pengembangan, disarankan cek versi stabil terkini |
| Database | **MySQL 8.0** | Via XAMPP untuk development |
| Environment Dev | **XAMPP** | Local development |
| Frontend Templating | **Blade** | Native Laravel |
| CSS Framework | **Bootstrap 5** | Komponen siap pakai, familiar untuk tim, kompatibel dengan AdminLTE-style dashboard |
| Interaktivitas | **jQuery + AJAX** | Untuk tabel dinamis & form tanpa reload |
| Tabel Data | **DataTables (server-side processing)** | Untuk performa di tabel besar (ribuan baris mahasiswa/desa) |
| Peta Interaktif | **Leaflet.js + OpenStreetMap** | Gratis, ringan, tanpa API key |
| Autentikasi | **Laravel Breeze** (session-based) | Cocok untuk single-portal multi-role, ringan dibanding Jetstream |
| Otorisasi | **Laravel Gate & Policy** + Middleware role | Role-based access control (RBAC) |
| Upload File | **Laravel Filesystem (local disk)** | Proposal, dokumentasi, laporan akhir |
| Notifikasi | **Laravel Database Notifications** | In-app notification (bell icon), fase awal |
| Icon | **Bootstrap Icons / Font Awesome** | — |
| Font | **Poppins/Plus Jakarta Sans** (heading), **Inter/Nunito Sans** (body) | Via Google Fonts atau self-hosted |

---

## 4. Role & Permission Matrix

Sistem menggunakan **single portal, single login**, dengan redirect dashboard otomatis sesuai role setelah autentikasi (via middleware).

| Modul / Fitur | Bapperida (Admin) | Perguruan Tinggi | Mahasiswa | DPL (Dosen) | Perangkat Daerah | Kecamatan | Desa |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Registrasi akun PT | Approve | Create (self) | – | – | – | – | – |
| Pengajuan permohonan KKN | View all | Create/Edit (milik sendiri) | – | – | – | – | – |
| Input data mahasiswa & DPL | View all | Create/Edit | View (self) | View (self) | – | – | – |
| Verifikasi permohonan | **Verify/Reject** | View status | – | – | – | – | – |
| Matching & rekomendasi lokasi | **Run/Override** | View hasil | – | – | – | – | – |
| Verifikasi kesiapan desa | View | – | – | – | – | **Verify** | Input kesiapan |
| Persetujuan pelaksanaan | **Approve** | View | – | – | – | – | – |
| Input isu strategis daerah | View all | – | – | – | **Create/Edit** | – | – |
| Profil, potensi, permasalahan desa | View all | – | – | – | – | – | **Create/Edit** |
| Logbook harian | View all | View (rekap) | **Create** | Approve | – | – | – |
| Upload dokumentasi & laporan akhir | View all | View | **Create** | Approve | – | – | – |
| Evaluasi mahasiswa | View all | View | – | Input (kelompok) | – | – | Input |
| Dashboard GIS | Full | Own PT lokasi | – | – | – | View kecamatan | View desa |
| Dashboard Monitoring | Full | Own PT | Own progress | Own mahasiswa | – | View kecamatan | View desa |
| Dashboard Evaluasi | Full | Own PT | – | – | View | – | – |
| Manajemen master data (desa, kecamatan, PT) | **Full CRUD** | – | – | – | – | – | – |

> Catatan: Superadmin/IT Admin dapat ditambahkan sebagai role ke-8 khusus untuk manajemen teknis (user management, master data), terpisah dari Bapperida sebagai role fungsional/bisnis — akan dibahas lebih lanjut saat ERD.

---

## 5. Pemetaan Modul KAK → Struktur Aplikasi

| Ruang Lingkup KAK | Modul Aplikasi (Laravel) |
|---|---|
| A. Modul Perguruan Tinggi | `Modules/PerguruanTinggi` — registrasi, pengajuan, upload proposal, monitoring status |
| B. Modul Bapperida | `Modules/Bapperida` — verifikasi, matching, approval, dashboard admin |
| C. Modul Perangkat Daerah | `Modules/PerangkatDaerah` — input isu strategis, rekomendasi tema |
| D. Modul Kecamatan | `Modules/Kecamatan` — verifikasi kesiapan desa, rekomendasi lokasi |
| E. Modul Desa | `Modules/Desa` — profil, potensi, permasalahan, evaluasi mahasiswa |
| F. Modul Mahasiswa | `Modules/Mahasiswa` — logbook, dokumentasi, progress, laporan akhir |
| G. Modul Dosen (DPL) | `Modules/Dosen` — monitoring mahasiswa, approval logbook, evaluasi kelompok |
| Fitur Utama: Matching System | `App/Services/MatchingService.php` |
| Fitur Utama: Dashboard GIS | `Modules/Shared/GIS` — peta Leaflet lintas-role |
| Fitur Utama: Dashboard Monitoring/Evaluasi | `Modules/Shared/Dashboard` |

---

## 6. Struktur Folder Proyek (Laravel)

```
simpul-kkn/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Bapperida/
│   │   │   ├── PerguruanTinggi/
│   │   │   ├── Mahasiswa/
│   │   │   ├── Dosen/
│   │   │   ├── PerangkatDaerah/
│   │   │   ├── Kecamatan/
│   │   │   ├── Desa/
│   │   │   └── Shared/          (Dashboard, GIS, Notifikasi)
│   │   └── Middleware/
│   │       └── RoleMiddleware.php
│   ├── Models/
│   ├── Services/
│   │   └── MatchingService.php
│   ├── Policies/
│   └── Notifications/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── layouts/            (layout per role/shared)
│   │   ├── components/         (card, badge status, dsb — reusable Blade components)
│   │   └── {role}/{fitur}/
│   ├── js/                     (jQuery custom scripts)
│   └── css/                    (Bootstrap override, tema warna)
├── public/
│   └── uploads/                (symlink storage)
└── routes/
    └── web.php                 (grouped by role prefix & middleware)
```

---

## 7. Desain Database

Detail ERD, entitas, relasi, dan kamus data dibahas terpisah di **`03-erd.md`**. Poin arsitektural yang relevan di sini:

- Skema disederhanakan dengan pendekatan **satu database, banyak tabel**, disekat logis via foreign key `perguruan_tinggi_id`, `desa_id`, `kecamatan_id`.
- Tabel inti yang diperkirakan: `users`, `roles`, `perguruan_tinggi`, `mahasiswa`, `dosen`, `desa`, `kecamatan`, `permohonan_kkn`, `matching_score`, `logbook`, `laporan_akhir`, `isu_strategis`, `evaluasi`, `notifications`.

---

## 8. Non-Functional Requirements (NFR)

| Kategori | Requirement |
|---|---|
| **Performa** | Halaman utama & dashboard load < 3 detik pada koneksi standar; tabel besar (>1000 baris) wajib server-side processing (DataTables AJAX) |
| **Keamanan** | CSRF protection (default Laravel), validasi & sanitasi input, role middleware di setiap route, validasi tipe/ukuran file upload (PDF/JPG/PNG max 5MB), password hashing (bcrypt) |
| **Skalabilitas** | Arsitektur monolith cukup untuk asumsi skala §2.4; migrasi ke queue (Laravel Queue) disiapkan untuk proses berat (notifikasi massal, export laporan) di fase lanjut |
| **Ketersediaan** | Target uptime 99% (bukan sistem kritis 24/7 real-time, dapat maintenance window terjadwal) |
| **Aksesibilitas** | Responsive di breakpoint mobile (≥360px), tablet, desktop; kontras warna memenuhi WCAG AA minimal untuk teks utama |
| **Audit Trail** | Log aktivitas untuk aksi kritis (verifikasi, approval, perubahan status) — tabel `activity_log` |
| **Backup** | Backup database berkala (harian/mingguan) — kebijakan operasional, di luar cakupan aplikasi |

---

## 9. Integrasi Eksternal

| Kebutuhan | Solusi Fase Ini | Kemungkinan Fase Lanjut |
|---|---|---|
| Peta interaktif | Leaflet.js + OpenStreetMap tiles (gratis) | Upgrade ke basemap khusus jika dibutuhkan |
| Notifikasi | In-app (Laravel Database Notifications) | Email (SMTP) / WhatsApp Gateway |
| Autentikasi | Native Laravel (email/password) | SSO dengan sistem pemda lain (jika ada) |
| Tanda tangan elektronik surat | Belum dicakup (di luar ruang lingkup awal) | Integrasi BSrE jika diperlukan untuk dokumen resmi |

---

## 10. UI/UX Design System

### 10.1 Filosofi Desain
**"Governance meets Growth"** — biru (kepercayaan, institusi) dipadukan teal (pembangunan, kolaborasi). Gaya: **formal, bersih, modern, padat informasi tapi tidak sesak** — prioritas keterbacaan dan efisiensi ruang, bukan dekorasi. Diterima semua kalangan: pejabat daerah, akademisi, mahasiswa, perangkat desa.

### 10.2 Palet Warna

| Peran | Nama | Hex | Kegunaan |
|---|---|---|---|
| Primary | Deep Blue | `#1B3A6B` | Header, sidebar aktif, tombol utama, link |
| Primary Hover | Deep Blue Dark | `#142C52` | State hover/active tombol primary |
| Secondary | Teal/Emerald | `#0F9B8E` | Aksen, badge "aktif", grafik, tombol sekunder |
| Accent | Amber | `#F2A93B` | Notifikasi, status "pending", highlight |
| Success | Green | `#2E9E5B` | Status selesai/approved, alert sukses |
| Danger | Red | `#D64545` | Status ditolak/error, alert gagal, tombol hapus |
| Info | Sky Blue | `#3B82C4` | Alert informasi netral, tooltip |
| Neutral 900 | Charcoal | `#1F2937` | Teks judul/utama |
| Neutral 600 | Slate | `#4B5563` | Teks sekunder/label |
| Neutral 400 | Gray | `#9CA3AF` | Placeholder, teks disabled |
| Neutral 100 | Off-white | `#F5F7FA` | Background halaman |
| Border | Light Gray | `#E2E8F0` | Garis, card border, table divider |
| Surface | White | `#FFFFFF` | Background card, modal, tabel |

**Kontras:** seluruh kombinasi teks-di-atas-background di atas sudah dicek memenuhi WCAG AA (rasio ≥4.5:1) untuk teks body.

### 10.3 Tipografi

| Elemen | Font | Ukuran | Weight |
|---|---|---|---|
| H1 (judul halaman) | Poppins/Plus Jakarta Sans | 24px | 600 |
| H2 (judul section) | Poppins/Plus Jakarta Sans | 20px | 600 |
| H3 (judul card/subsection) | Poppins/Plus Jakarta Sans | 16px | 600 |
| Body | Inter/Nunito Sans | 14px | 400 |
| Body kecil (caption, keterangan tabel) | Inter/Nunito Sans | 12px | 400 |
| Label form | Inter/Nunito Sans | 13px | 500 |
| Line-height | — | 1.4–1.5x ukuran font | — |

Font di-load via Google Fonts CDN atau self-host untuk kecepatan; fallback ke `system-ui, sans-serif`.

### 10.4 Spacing & Grid (kunci untuk tampilan padat-tapi-rapi)

Pakai skala spacing konsisten kelipatan 4px — hindari jarak acak yang membuat layout terasa longgar tanpa alasan:

| Token | Nilai | Kegunaan |
|---|---|---|
| `space-1` | 4px | Jarak antar elemen sangat rapat (icon+text) |
| `space-2` | 8px | Padding dalam badge, gap antar form inline |
| `space-3` | 12px | Padding dalam tombol, gap antar field form |
| `space-4` | 16px | Padding standar card, gap antar card dalam grid |
| `space-5` | 24px | Jarak antar section dalam satu halaman |
| `space-6` | 32px | Jarak antar section besar / margin halaman |

**Aturan praktis:** card padding `16px` (bukan 24-32px seperti dashboard SaaS pada umumnya) — cukup napas tanpa boros ruang. Container max-width `1320px` di desktop besar supaya tabel data tidak terlalu lebar dan sulit dipindai mata.

### 10.5 Komponen — Spesifikasi Ringkas

| Komponen | Spesifikasi |
|---|---|
| **Card** | Radius `8px`, border `1px solid Border`, shadow tipis `0 1px 3px rgba(0,0,0,.06)` (bukan shadow tebal — kesan formal, bukan playful), padding `16px` |
| **Tombol Primary** | Background Primary, radius `6px`, padding `8px 16px`, font 14px weight 500, hover → Primary Hover |
| **Tombol Secondary/Outline** | Border Primary, teks Primary, background transparan, hover → background Primary 10% opacity |
| **Badge Status** | Radius penuh (`pill`), padding `2px 10px`, font 12px weight 600, warna solid sesuai status (lihat §10.6) |
| **Tabel (DataTables)** | Row height ringkas `40-44px` (bukan 56px+ seperti Material Design) — supaya lebih banyak baris terlihat tanpa scroll berlebihan; header sticky, font 13-14px, hover row highlight abu tipis |
| **Form Input** | Height `38px`, radius `6px`, border `1px solid Border`, focus → border Primary + ring tipis Primary 20% opacity |
| **Modal** | Radius `10px`, max-width `600px` (form standar) / `900px` (form kompleks, mis. detail permohonan) |
| **Sidebar** | Lebar `240px` desktop, collapsible ke `64px` (icon-only) atau off-canvas penuh di mobile |
| **Icon** | Bootstrap Icons, ukuran default `18px` inline dengan teks, `24px` untuk aksi utama |

### 10.6 Badge Status (konsisten di seluruh sistem)

| Status | Warna Badge | Contoh Pemakaian |
|---|---|---|
| Menunggu/Pending | Amber `#F2A93B` | Permohonan diajukan, logbook menunggu review |
| Terverifikasi/Disetujui/Aktif | Teal `#0F9B8E` | Permohonan terverifikasi, KKN aktif |
| Selesai | Deep Blue `#1B3A6B` | KKN selesai, laporan lengkap |
| Ditolak/Revisi | Red `#D64545` | Permohonan ditolak, logbook perlu revisi |
| Draft/Nonaktif | Neutral 400 `#9CA3AF` | Akun belum diaktifkan, data belum lengkap |

### 10.7 State Interaktif
- **Hover:** perubahan warna background/border 1 tingkat lebih gelap, transisi `0.15s ease`
- **Focus (aksesibilitas keyboard):** ring `2px` warna Primary 40% opacity di sekeliling elemen aktif
- **Disabled:** opacity `50%`, cursor `not-allowed`, tanpa hover effect
- **Loading:** skeleton screen abu-abu (bukan spinner penuh layar) untuk tabel/card yang sedang memuat data — kesan lebih cepat & modern
- **Empty state:** ilustrasi/icon sederhana + teks singkat + CTA jika relevan (mis. "Belum ada permohonan" + tombol "Ajukan Permohonan")

### 10.8 Layout & Responsivitas
- **Mobile-first**, breakpoint Bootstrap 5 (`sm` 576px, `md` 768px, `lg` 992px, `xl` 1200px)
- Sidebar collapsible desktop → hamburger/off-canvas mobile
- Dashboard: grid card ringkas (2 kolom mobile, 4 kolom desktop) di atas, tabel/grafik detail di bawah — bukan tabel padat langsung di mobile
- Tabel data: scroll horizontal di layar kecil, kolom prioritas (nama, status) sticky/tetap terlihat
- Form: 1 kolom di mobile, 2 kolom di desktop untuk field pendek (grid Bootstrap), textarea/upload tetap full-width
- Komponen reusable sebagai Blade Components: `<x-card>`, `<x-status-badge>`, `<x-page-header>`, `<x-empty-state>`, `<x-data-table>`

### 10.9 Framework CSS
**Bootstrap 5** (bukan Tailwind): kecepatan pengembangan tim kecil, komponen siap pakai (modal, tab, accordion, badge), kompatibel mulus dengan jQuery/DataTables, familiar untuk pola dashboard ala AdminLTE yang umum di sistem pemerintahan — memudahkan handover/maintenance jangka panjang oleh tim internal. Override variabel warna Bootstrap (`$primary`, `$secondary`, dst.) via SCSS agar konsisten dengan palet §10.2 tanpa menulis ulang komponen dari nol.

---

## 11. Ringkasan Keputusan Desain (Log Diskusi)

| Keputusan | Pilihan |
|---|---|
| Backend | Laravel + PHP + MySQL, dev via XAMPP |
| Frontend interaktif | jQuery + AJAX, DataTables server-side |
| Peta | Leaflet.js + OpenStreetMap |
| Model login | Single portal, redirect per role |
| Matching System | Rule-based scoring |
| Notifikasi | In-app (fase awal) |
| Skala asumsi | Menengah-kecil (12–30 PT, 1.000–3.000 mahasiswa/periode) |
| Target pengguna | Merata semua kalangan, responsif penuh |
| Gaya visual | Formal, bersih, modern |
| CSS Framework | Bootstrap 5 |

---

*Dokumen berikutnya: `01-prd.md` — Product Requirement Document*
