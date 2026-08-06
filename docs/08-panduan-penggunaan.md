# 08 — Panduan Penggunaan (Sementara)
## SIMPUL-KKN

> ⚠️ **Status: DRAFT / cheat sheet internal pengembang.** Diperbarui seiring perkembangan fitur. Bukan manual resmi pengguna akhir (itu menyusul di fase finalisasi).
>
> Tujuan: pengingat cepat — akun login, cara menjalankan, dan checklist hal yang perlu dicek saat menguji.

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

Semua akun dibuat oleh seeder. Password ditulis sesuai seeder saat ini.

| Role | Email | Password | Keterangan |
|---|---|---|---|
| Bapperida (Admin) | `admin@bapperida-indramayu.go.id` | `ubah-password-ini` | ⚠️ Ganti password setelah login pertama di produksi |
| Perguruan Tinggi (disetujui) | `pt@uin.ac.id` | `password` | Universitas Indramayu — bisa login & ajukan permohonan |
| Perguruan Tinggi (menunggu approval) | `pt-menunggu@uin.ac.id` | `password` | STIKes Sehat Jaya — untuk demo persetujuan akun |
| Mahasiswa (kelompok 1) | `andi@uin.ac.id` | `password` | Andi Pratama (2024-01001) |
| Mahasiswa (kelompok 2) | `dewi@uin.ac.id` | `password` | Dewi Lestari (2024-02001) |

> ⚠️ **Karena pengguna juga sering lupa password admin:** jika lupa, reset dengan:
> ```bash
> php artisan tinker
> >>> App\Models\User::where('email','admin@bapperida-indramayu.go.id')->update(['password'=>bcrypt('password-baru')]);
> ```

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

> **Dashboard mahasiswa:** akun `andi@uin.ac.id` / `dewi@uin.ac.id` sudah bisa login, dashboard + bell notifikasi berfungsi (dropdown kosong sampai ada pemicu notifikasi mahasiswa — logbook, dll. masuk **Fase 3**).

---

## 4. Checklist Cepat (Hal yang Sering Diperiksa Saat Lupa)

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

## 5. Catatan Teknis Ringkas

- **Laravel 12 + MySQL** (XAMPP). CSS/JS manual di `public/css/app.css` & `public/js/app.js` (bukan build Vite).
- **Notifikasi in-app** disimpan ke tabel `notifications` **sinkron** (tanpa queue worker) — sesuai desain fase awal.
- **Pagination tabel list** memakai **jQuery/AJAX server-side** (endpoint `.../data`).
- Konvensi commit: prefix `feat:` / `docs:` / `style:` / `chore:`.

---

*Dokumen berikutnya: `07-load-testing.md` merujuk ke panduan ini.*
