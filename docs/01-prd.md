# 01 — Product Requirement Document (PRD)
## SIMPUL-KKN (Sistem Informasi Manajemen Pengabdian Unggulan dan Kolaborasi Kuliah Kerja Nyata)
**Kabupaten Indramayu — Bapperida**

Referensi: KAK SIMPUL-KKN, Lampiran Surat Plt. Kepala Bapperida No. 000.9.1/172/Rida, 29 Juli 2026
Dokumen sebelumnya: `00-design-system.md`

---

## 1. Latar Belakang

Setiap tahun, Pemerintah Kabupaten Indramayu menerima ratusan hingga ribuan mahasiswa KKN dari berbagai Perguruan Tinggi (12 PT aktif per Juli 2026). Proses pengajuan, koordinasi lokasi, sinkronisasi program kerja, hingga pelaporan hasil masih dilakukan manual melalui surat-menyurat dan komunikasi informal. Kondisi ini menimbulkan masalah:

- Tidak ada basis data terpadu pelaksanaan KKN
- Potensi tumpang tindih lokasi maupun tema KKN
- Program kerja mahasiswa belum tentu selaras dengan prioritas pembangunan daerah
- Belum ada mekanisme evaluasi dampak KKN terhadap masyarakat

SIMPUL-KKN dikembangkan untuk menjawab masalah ini melalui satu sistem informasi digital terpadu.

---

## 2. Tujuan Produk

1. Meningkatkan efektivitas pelayanan permohonan KKN (paperless, terpusat)
2. Menyediakan basis data pelaksanaan KKN yang akurat dan real-time
3. Menyelaraskan tema/program KKN dengan prioritas pembangunan daerah (via Matching System)
4. Mencegah tumpang tindih lokasi KKN antar-PT
5. Memudahkan monitoring kegiatan mahasiswa oleh Bapperida, PT, dan DPL
6. Mengukur dampak KKN terhadap masyarakat secara terstruktur
7. Mendukung pengambilan kebijakan daerah berbasis data

---

## 3. Target Pengguna & Persona

| Persona | Peran dalam Sistem | Kebutuhan Utama | Perangkat Utama |
|---|---|---|---|
| **Admin Bapperida** | Verifikator & pengambil keputusan pusat | Melihat seluruh data, memverifikasi, approve, dashboard evaluasi | Desktop |
| **Operator Perguruan Tinggi** | Pengaju permohonan KKN | Mendaftarkan mahasiswa, mengajukan tema, memantau status | Desktop/Laptop |
| **Mahasiswa** | Pelaksana kegiatan lapangan | Isi logbook harian, upload dokumentasi, lihat progress | **Mobile** (lapangan) |
| **Dosen Pembimbing Lapangan (DPL)** | Pembimbing & evaluator kelompok | Approve logbook, monitoring mahasiswa bimbingan | Mobile/Desktop |
| **Perangkat Daerah (OPD)** | Pemberi input kebutuhan sektoral | Input isu strategis, rekomendasi tema | Desktop |
| **Operator Kecamatan** | Verifikator kesiapan wilayah | Verifikasi kesiapan desa, rekomendasi lokasi | Desktop/Mobile |
| **Operator Desa** | Pemilik data wilayah & penerima manfaat | Update profil desa, potensi, evaluasi mahasiswa | Mobile/Desktop |

> Sesuai kesepakatan di `00-design-system.md`: prioritas UX **merata untuk semua kalangan**, sistem harus sepenuhnya responsif desktop & mobile.

---

## 4. Ruang Lingkup (Scope)

### 4.1 In-Scope (Fase Pengembangan Ini)
Seluruh modul A–G sesuai KAK (lihat detail user stories di §6), Matching System rule-based, Dashboard GIS (Leaflet), Dashboard Monitoring, Dashboard Evaluasi, notifikasi in-app.

### 4.2 Out-of-Scope (Fase Ini)
- Integrasi tanda tangan elektronik (BSrE) untuk surat resmi
- Notifikasi Email/WhatsApp Gateway
- Single Sign-On (SSO) dengan sistem pemda lain
- Mobile native app (Android/iOS) — cukup responsive web
- Matching System berbasis Machine Learning
- Pembayaran/keuangan (sistem ini murni administratif-koordinatif, tidak menangani anggaran)

---

## 5. Alur Pengguna Utama (User Journey Ringkas)

**Journey 1 — Perguruan Tinggi mengajukan KKN:**
Registrasi akun → login → isi form permohonan (tema, jumlah mahasiswa, DPL, jadwal) → upload surat permohonan & proposal → submit → pantau status verifikasi.

**Journey 2 — Bapperida memproses permohonan:**
Login → lihat daftar permohonan masuk → verifikasi kelengkapan → jalankan Matching System → lihat rekomendasi desa → assign/override lokasi → kirim ke Kecamatan untuk verifikasi kesiapan → approve final.

**Journey 3 — Mahasiswa menjalani KKN:**
Login → lihat lokasi & tema yang di-assign → isi logbook harian (dengan foto) → DPL approve → update progress kegiatan → di akhir periode, upload laporan akhir & luaran kegiatan.

**Journey 4 — Desa memberi input & evaluasi:**
Login → lengkapi profil desa (potensi, permasalahan, kebutuhan) → terima notifikasi ada mahasiswa ditempatkan → pantau kegiatan → isi evaluasi di akhir periode.

---

## 6. Functional Requirements per Modul (User Stories)

### 6.1 Modul Perguruan Tinggi
| ID | User Story | Prioritas |
|---|---|---|
| PT-01 | Sebagai operator PT, saya bisa registrasi akun institusi agar dapat mengakses sistem | Must |
| PT-02 | Sebagai operator PT, saya bisa mengajukan permohonan KKN baru (tema, jadwal, jumlah mahasiswa) | Must |
| PT-03 | Sebagai operator PT, saya bisa upload surat permohonan & proposal (PDF) | Must |
| PT-04 | Sebagai operator PT, saya bisa input data mahasiswa peserta secara massal (import Excel) atau satu-satu | Should |
| PT-05 | Sebagai operator PT, saya bisa input data DPL dan menautkannya ke kelompok mahasiswa | Must |
| PT-06 | Sebagai operator PT, saya bisa memantau status permohonan (diajukan/diverifikasi/disetujui/ditolak) secara real-time | Must |
| PT-07 | Sebagai operator PT, saya bisa melihat rekap seluruh mahasiswa yang sedang/telah KKN dari institusinya | Should |

### 6.2 Modul Bapperida
| ID | User Story | Prioritas |
|---|---|---|
| BP-01 | Sebagai admin Bapperida, saya bisa melihat seluruh permohonan KKN masuk dari semua PT | Must |
| BP-02 | Sebagai admin Bapperida, saya bisa memverifikasi/menolak permohonan dengan catatan | Must |
| BP-03 | Sebagai admin Bapperida, saya bisa menjalankan Matching System untuk mendapat rekomendasi desa | Must |
| BP-04 | Sebagai admin Bapperida, saya bisa meng-override rekomendasi lokasi secara manual | Must |
| BP-05 | Sebagai admin Bapperida, saya bisa memberi persetujuan akhir pelaksanaan KKN | Must |
| BP-06 | Sebagai admin Bapperida, saya bisa melihat dashboard seluruh KKN yang berjalan (peta, statistik) | Must |
| BP-07 | Sebagai admin Bapperida, saya bisa memonitor progress & logbook seluruh kelompok KKN | Should |
| BP-08 | Sebagai admin Bapperida, saya bisa melihat statistik & analisis persebaran lokasi KKN | Should |
| BP-09 | Sebagai admin Bapperida, saya bisa mengelola master data (desa, kecamatan, PT, tema prioritas) | Must |

### 6.3 Modul Perangkat Daerah (OPD)
| ID | User Story | Prioritas |
|---|---|---|
| OPD-01 | Sebagai operator OPD, saya bisa menginput isu strategis sesuai bidang tugas | Must |
| OPD-02 | Sebagai operator OPD, saya bisa menginput kebutuhan program terkait isu tersebut | Should |
| OPD-03 | Sebagai operator OPD, saya bisa memberi rekomendasi tema KKN berdasarkan isu strategis | Should |
| OPD-04 | Sebagai operator OPD, saya bisa memberikan evaluasi terhadap pelaksanaan KKN di bidangnya | Could |

### 6.4 Modul Kecamatan
| ID | User Story | Prioritas |
|---|---|---|
| KC-01 | Sebagai operator kecamatan, saya bisa memverifikasi kesiapan desa di wilayahnya untuk menerima KKN | Must |
| KC-02 | Sebagai operator kecamatan, saya bisa memberi rekomendasi lokasi/desa spesifik | Should |
| KC-03 | Sebagai operator kecamatan, saya bisa memonitor pelaksanaan KKN di wilayahnya | Should |

### 6.5 Modul Desa
| ID | User Story | Prioritas |
|---|---|---|
| DS-01 | Sebagai operator desa, saya bisa melengkapi profil desa (demografi, wilayah) | Must |
| DS-02 | Sebagai operator desa, saya bisa menginput permasalahan desa | Must |
| DS-03 | Sebagai operator desa, saya bisa menginput potensi desa | Must |
| DS-04 | Sebagai operator desa, saya bisa menginput kebutuhan masyarakat | Should |
| DS-05 | Sebagai operator desa, saya bisa memberikan evaluasi terhadap mahasiswa yang ditempatkan | Must |

### 6.6 Modul Mahasiswa
| ID | User Story | Prioritas |
|---|---|---|
| MHS-01 | Sebagai mahasiswa, saya bisa mengisi logbook harian kegiatan (teks + foto) | Must |
| MHS-02 | Sebagai mahasiswa, saya bisa upload dokumentasi kegiatan | Must |
| MHS-03 | Sebagai mahasiswa, saya bisa melihat progress kegiatan kelompok saya | Should |
| MHS-04 | Sebagai mahasiswa, saya bisa upload laporan akhir KKN | Must |
| MHS-05 | Sebagai mahasiswa, saya bisa mengunggah luaran kegiatan (produk, publikasi, dsb) | Should |
| MHS-06 | Sebagai mahasiswa, saya bisa mengakses sistem dengan nyaman dari HP saat di lapangan | Must |

### 6.7 Modul Dosen (DPL)
| ID | User Story | Prioritas |
|---|---|---|
| DPL-01 | Sebagai DPL, saya bisa memonitor seluruh mahasiswa bimbingan saya | Must |
| DPL-02 | Sebagai DPL, saya bisa menyetujui/menolak logbook harian mahasiswa dengan catatan | Must |
| DPL-03 | Sebagai DPL, saya bisa memberikan evaluasi kelompok di akhir periode | Must |

### 6.8 Fitur Lintas-Modul
| ID | User Story | Prioritas |
|---|---|---|
| SYS-01 | Sebagai pengguna manapun, saya menerima notifikasi in-app saat status permohonan/logbook berubah | Must |
| SYS-02 | Sebagai admin Bapperida/PT/Kecamatan/Desa, saya bisa melihat peta interaktif sebaran KKN (Dashboard GIS) | Must |
| SYS-03 | Sebagai admin Bapperida, saya bisa melihat dashboard evaluasi (tingkat keberhasilan, kepuasan desa, dampak ekonomi/sosial) | Should |
| SYS-04 | Sebagai pengguna, saya login melalui satu portal dan diarahkan ke dashboard sesuai role saya | Must |

---

## 7. Matching System — Spesifikasi Fungsional

Sesuai kesepakatan: **rule-based scoring**, bukan ML.

**Input yang dipertimbangkan:**
1. Tema KKN yang diajukan PT
2. Bidang keilmuan mahasiswa/kelompok
3. Prioritas pembangunan daerah (ditetapkan Bapperida/OPD — misal: stunting, UMKM, digitalisasi)
4. Kebutuhan desa (yang diinput operator desa)

**Mekanisme skor (diusulkan, detail final di ERD/desain algoritma):**
- Setiap parameter diberi bobot (contoh awal: Tema 30%, Bidang Keilmuan 25%, Prioritas Daerah 25%, Kebutuhan Desa 20% — dapat disesuaikan Bapperida)
- Sistem menghasilkan **ranking desa** per permohonan KKN, bukan satu jawaban mutlak
- Admin Bapperida tetap dapat override manual (BP-04)
- Sistem otomatis memberi **flag peringatan** jika desa yang direkomendasikan sudah/sedang menerima KKN tema serupa dari PT lain (cegah tumpang tindih)

---

## 8. Success Metrics (Indikator Keberhasilan)

Sesuai KAK, ditambah metrik operasional produk:

| Metrik | Target |
|---|---|
| Permohonan KKN via aplikasi | 100% dari total permohonan |
| Lokasi KKN terdokumentasi digital | 100% |
| Kesesuaian program kerja dengan prioritas daerah | Meningkat terukur (dibanding periode manual) |
| Tumpang tindih lokasi | 0 kasus (dicegah sistem) |
| Dashboard monitoring real-time | Tersedia & digunakan aktif oleh Bapperida |
| Laporan terdokumentasi dalam sistem | 100% |
| Adopsi oleh Perguruan Tinggi | Seluruh 12 PT aktif menggunakan sistem dalam 1 periode KKN |
| Waktu proses verifikasi permohonan | Berkurang signifikan dari proses manual sebelumnya |

---

## 9. Asumsi & Batasan (Assumptions & Constraints)

- Skala pengguna mengikuti asumsi di `00-design-system.md` (§2.4)
- Data wilayah administratif (kecamatan, desa) Kabupaten Indramayu akan disediakan/diverifikasi oleh Bapperida sebagai master data awal
- Pengguna memiliki akses internet minimal untuk mengisi logbook (termasuk di wilayah desa)
- Sistem tidak menangani proses keuangan/anggaran KKN
- Tidak ada kewajiban integrasi dengan sistem akademik internal masing-masing PT pada fase ini (SIMPUL-KKN berdiri independen)

---

## 10. Ketergantungan Antar Dokumen

- Detail entitas & relasi data → `03-erd.md`
- Detail aktor & interaksi sistem → `02-use-case.md`
- Detail alur proses per modul → `04-flowchart.md` & `05-activity-diagram.md`
- Rencana rilis bertahap (MVP → full feature) → `06-phase-plan.md`
- Target performa & skenario pengujian → `07-load-testing.md`

---

*Dokumen berikutnya: `02-use-case.md` — Use Case Diagram & Narasi*
