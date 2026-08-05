# 04 — Flowchart Proses Bisnis Utama
## SIMPUL-KKN

Dokumen sebelumnya: `00-design-system.md`, `01-prd.md`, `02-use-case.md`, `03-erd.md`

Flowchart dipecah menjadi **4 tahap** mengikuti siklus hidup KKN dalam sistem, masing-masing digambarkan dengan **swimlane per aktor** (subgraph Mermaid) dan mencakup **jalur alternatif** (ditolak/revisi), bukan hanya happy path.

```
Tahap 1: Pengajuan & Verifikasi  →  Tahap 2: Matching & Penentuan Lokasi
       →  Tahap 3: Pelaksanaan  →  Tahap 4: Evaluasi
```

---

## Tahap 1 — Pengajuan & Verifikasi

**Aktor:** Perguruan Tinggi, Bapperida
**Ref use case:** UC-01, UC-02, UC-03, UC-04, UC-05

```mermaid
flowchart TB
    subgraph PT1["🏫 Perguruan Tinggi"]
        direction TB
        A1([Mulai]) --> A2[Registrasi Akun Institusi]
        A2 --> A3{Status Approval Akun}
        A3 -->|Ditolak| A3b[Terima Catatan Penolakan] --> A2
        A3 -->|Disetujui| A4[Login ke Sistem]
        A4 --> A5[Isi Form Permohonan KKN:<br/>tema, periode, jumlah mhs]
        A5 --> A6[Input Data Mahasiswa & DPL]
        A6 --> A7[Upload Surat Permohonan & Proposal]
        A7 --> A8[Submit Permohonan]
        A8 --> A9{Hasil Verifikasi Bapperida}
        A9 -->|Ditolak| A10[Terima Notifikasi + Catatan Perbaikan] --> A5
        A9 -->|Terverifikasi| A11([Lanjut ke Tahap 2: Matching])
    end

    subgraph BP1["🏛️ Bapperida"]
        direction TB
        B1[Terima Pengajuan Akun PT] --> B2{Cek Kelengkapan Data}
        B2 -->|Tidak Lengkap| B3[Tolak + Beri Catatan]
        B2 -->|Lengkap| B4[Approve Akun PT]
        B5[Terima Permohonan KKN] --> B6{Cek Kelengkapan Dokumen}
        B6 -->|Tidak Lengkap/Tidak Sesuai| B7[Tolak + Beri Catatan]
        B6 -->|Lengkap & Sesuai| B8[Set Status: Terverifikasi]
    end

    A2 -.kirim data.-> B1
    B3 -.notifikasi.-> A3
    B4 -.notifikasi.-> A3
    A8 -.kirim data.-> B5
    B7 -.notifikasi.-> A9
    B8 -.notifikasi.-> A9
```

**Catatan alur:**
- Ada **2 titik keputusan** di tahap ini: approval akun PT (sekali di awal) dan verifikasi permohonan KKN (bisa berulang tiap periode pengajuan).
- Jalur penolakan **tidak mematikan proses** — PT selalu bisa merevisi dan mengajukan ulang tanpa membuat akun/permohonan baru dari nol.

---

## Tahap 2 — Matching System & Penentuan Lokasi

**Aktor:** Bapperida, Kecamatan
**Ref use case:** UC-06, UC-07, UC-11
**Prasyarat:** Data `isu_strategis` (dari Perangkat Daerah) dan `desa_kebutuhan` (dari Desa) sudah tersedia sebagai parameter skor.

```mermaid
flowchart TB
    subgraph BP2["🏛️ Bapperida"]
        direction TB
        M1([Permohonan Berstatus Terverifikasi]) --> M2[Jalankan Matching System]
        M2 --> M3[Sistem Hitung Skor Tiap Desa Kandidat:<br/>tema + bidang + prioritas daerah + kebutuhan desa]
        M3 --> M4[Tampilkan Ranking Desa<br/>+ Flag Peringatan Tumpang Tindih]
        M4 --> M5{Pilih Desa}
        M5 -->|Ambil dari Ranking Teratas| M6[Pilih Desa Rekomendasi]
        M5 -->|Override Manual| M7[Pilih Desa Lain Secara Manual]
        M6 --> M8[Kirim ke Kecamatan untuk Verifikasi Kesiapan]
        M7 --> M8
        M12{Hasil Verifikasi Kecamatan}
        M12 -->|Tidak Siap| M13[Terima Notifikasi Tidak Siap] --> M4
        M12 -->|Siap| M14[Review Rekomendasi Lokasi Final]
        M14 --> M15{Setujui Lokasi?}
        M15 -->|Tidak Setuju| M4
        M15 -->|Setuju| M16[Approve Pelaksanaan KKN]
        M16 --> M17([Notifikasi ke PT, Mahasiswa, DPL, Desa<br/>→ Lanjut ke Tahap 3: Pelaksanaan])
    end

    subgraph KC2["🏘️ Kecamatan"]
        direction TB
        K1[Terima Permintaan Verifikasi] --> K2[Cek Kesiapan Desa<br/>koordinasi dengan aparat desa]
        K2 --> K3{Desa Siap Menerima KKN?}
        K3 -->|Tidak| K4[Set Status: Tidak Siap + Catatan]
        K3 -->|Ya| K5[Set Status: Siap + Rekomendasi Lokasi]
    end

    M8 -.kirim permintaan.-> K1
    K4 -.kirim hasil.-> M12
    K5 -.kirim hasil.-> M12
```

**Catatan alur:**
- Ini adalah **tahap paling kompleks** dan bisa **berulang (loop)** — jika desa hasil rekomendasi ternyata tidak siap, atau Bapperida tidak setuju hasil verifikasi kecamatan, sistem kembali ke langkah "tampilkan ranking" (M4) untuk memilih desa alternatif.
- **Flag tumpang tindih** muncul di M4 sebagai peringatan visual (bukan pemblokiran otomatis) — keputusan akhir tetap di tangan Bapperida, sesuai kesepakatan bahwa matching bersifat rule-based rekomendasi, bukan keputusan otomatis mutlak.

---

## Tahap 3 — Pelaksanaan Kegiatan

**Aktor:** Mahasiswa, DPL
**Ref use case:** UC-14, UC-15, UC-16
**Prasyarat:** Tahap 2 selesai, `kelompok_kkn.status = aktif`

```mermaid
flowchart TB
    subgraph MHS3["🎓 Mahasiswa"]
        direction TB
        P1([KKN Berstatus Aktif]) --> P2[Isi Logbook Harian + Upload Foto]
        P2 --> P3[Submit Logbook]
        P3 --> P4{Status Review DPL}
        P4 -->|Perlu Revisi| P5[Perbaiki Logbook Sesuai Catatan] --> P3
        P4 -->|Disetujui| P6{Periode KKN Masih Berjalan?}
        P6 -->|Ya, Lanjut Hari Berikutnya| P2
        P6 -->|Tidak, Mendekati/Sudah Selesai| P7[Susun Laporan Akhir & Luaran Kegiatan]
        P7 --> P8[Upload Laporan Akhir]
        P8 --> P9([Lanjut ke Tahap 4: Evaluasi])
    end

    subgraph DPL3["👨‍🏫 DPL"]
        direction TB
        D1[Terima Logbook Masuk] --> D2{Review Isi & Dokumentasi}
        D2 -->|Tidak Sesuai| D3[Tolak + Catatan Revisi]
        D2 -->|Sesuai| D4[Approve Logbook]
        D5[Pantau Progress Kelompok Bimbingan] -.berjalan paralel.-> D1
    end

    P3 -.kirim logbook.-> D1
    D3 -.notifikasi.-> P4
    D4 -.notifikasi.-> P4
```

**Catatan alur:**
- Loop harian (`P2 → P3 → D2 → P4 → P2`) adalah **siklus utama masa KKN**, berjalan berulang selama periode penugasan berlangsung.
- Modul Desa **tidak aktif memberi approval** di tahap ini (sesuai KAK, Desa hanya mengevaluasi di akhir — lihat Tahap 4), tapi dapat memantau progress secara pasif via Dashboard Monitoring.

---

## Tahap 4 — Evaluasi

**Aktor:** Desa, DPL, Bapperida
**Ref use case:** UC-13, UC-17, UC-09 (Dashboard Evaluasi)
**Prasyarat:** Tahap 3 selesai (laporan akhir sudah di-upload)

```mermaid
flowchart TB
    subgraph DS4["🏘️ Desa"]
        direction TB
        E1([Periode KKN Selesai]) --> E2[Isi Form Evaluasi Mahasiswa:<br/>kualitas program, manfaat, kedisiplinan]
        E2 --> E3[Submit Evaluasi]
    end

    subgraph DPL4["👨‍🏫 DPL"]
        direction TB
        F1([Periode KKN Selesai]) --> F2[Isi Form Evaluasi Kelompok]
        F2 --> F3[Submit Evaluasi]
    end

    subgraph BP4["🏛️ Bapperida"]
        direction TB
        G1[Data Evaluasi Terkumpul] --> G2[Sistem Hitung & Agregasi Statistik]
        G2 --> G3[Tingkat Keberhasilan Program]
        G2 --> G4[Kepuasan Desa]
        G2 --> G5[Dampak Ekonomi & Sosial]
        G2 --> G6[Inovasi yang Dihasilkan]
        G3 & G4 & G5 & G6 --> G7([Tersedia sebagai Bahan<br/>Evaluasi Kebijakan Daerah])
    end

    E3 -.kirim data.-> G1
    F3 -.kirim data.-> G1
```

**Catatan alur:**
- Tahap ini **tidak punya jalur penolakan** — evaluasi bersifat input satu arah, tidak melalui proses approval berjenjang seperti tahap sebelumnya.
- Evaluasi dari Desa dan DPL bisa **submit secara paralel** (tidak saling menunggu), keduanya menjadi input agregat Dashboard Evaluasi Bapperida.
- Output tahap ini adalah **muara dari seluruh siklus KKN** — datanya menjawab langsung Tujuan #6 dan #7 di KAK (mengukur dampak & mendukung kebijakan berbasis data).

---

## Ringkasan Keterhubungan Antar Tahap

```mermaid
flowchart LR
    T1[Tahap 1<br/>Pengajuan & Verifikasi] -->|permohonan terverifikasi| T2[Tahap 2<br/>Matching & Lokasi]
    T2 -->|lokasi disetujui| T3[Tahap 3<br/>Pelaksanaan]
    T3 -->|laporan akhir ter-upload| T4[Tahap 4<br/>Evaluasi]
    T4 -.data historis.-> T2
```

> Panah putus-putus T4 → T2: data evaluasi periode sebelumnya (mis. desa dengan evaluasi buruk, atau desa yang sudah "jenuh" menerima KKN tema sama) dapat menjadi pertimbangan tambahan pada proses matching periode berikutnya — pengembangan lanjutan dari Matching System, di luar skor dasar yang sudah disepakati.

---

*Dokumen berikutnya: `05-activity-diagram.md` — Activity Diagram per Modul*
