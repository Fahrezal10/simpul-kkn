<?php

namespace App\Http\Controllers\PerguruanTinggi;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Dosen;
use App\Models\KelompokKkn;
use App\Models\Mahasiswa;
use App\Models\PermohonanKkn;
use App\Models\User;
use App\Notifications\PermohonanStatusNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PermohonanController extends Controller
{
    /**
     * UC-04 — Daftar permohonan milik PT sendiri (badge status real-time).
     */
    public function index(): View
    {
        return view('perguruan-tinggi.permohonan.index');
    }

    /**
     * Sumber data AJAX (server-side) untuk tabel index — paginate JSON.
     */
    public function data(Request $request): JsonResponse
    {
        $permohonan = $this->perguruanTinggi()
            ->permohonanKkn()
            ->with('kelompokKkn')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $rows = $permohonan->getCollection()->map(function ($p) {
            return [
                'periode'        => e($p->periode),
                'tanggal'        => $p->tanggal_mulai?->format('d M Y').' – '.$p->tanggal_selesai?->format('d M Y'),
                'kelompok'       => $p->kelompokKkn->count(),
                'mahasiswa'      => $p->kelompokKkn->sum('jumlah_mahasiswa'),
                'status'         => view('components.status-badge', ['status' => $p->status])->render(),
                'aksi'           => '<a href="'.route('perguruan-tinggi.permohonan.show', $p).'" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i> Detail</a>',
                'status_raw'     => $p->status,
            ];
        });

        return response()->json([
            'data'       => $rows,
            'from'       => $permohonan->firstItem(),
            'per_page'   => $permohonan->perPage(),
            'total'      => $permohonan->total(),
            'current_page' => $permohonan->currentPage(),
            'last_page'  => $permohonan->lastPage(),
        ]);
    }

    /**
     * UC-02 — Form ajukan permohonan baru + input mahasiswa & DPL (UC-03).
     * Hanya PT dengan status_approval 'disetujui' yang boleh mengajukan.
     */
    public function create(): View|RedirectResponse
    {
        $pt = $this->perguruanTinggi();

        if ($pt->status_approval !== 'disetujui') {
            return redirect()->route('perguruan-tinggi.dashboard')
                ->with('error', 'Akun institusi Anda belum disetujui Bapperida. Silakan tunggu persetujuan sebelum mengajukan permohonan KKN.');
        }

        $dosen = Dosen::where('perguruan_tinggi_id', $pt->id)->orderBy('nama')->get();

        return view('perguruan-tinggi.permohonan.create', [
            'dosen' => $dosen,
        ]);
    }

    /**
     * UC-02 & UC-03 — Simpan permohonan beserta kelompok & mahasiswa.
     *
     * Alur transaksi:
     *  1. Buat PermohonanKkn (status 'diajukan').
     *  2. Resolve DPL: id dosen yang sudah ada / atau buat dosen baru bila
     *     baris memilih "DPL baru".
     *  3. Kelompokisasi per DPL: satu KelompokKkn per DPL terpilih dengan
     *     kode_kelompok unik (status menunggu_matching).
     *  4. Buat Mahasiswa terikat ke kelompoknya, isi jumlah_mahasiswa kelompok.
     */
    public function store(Request $request): RedirectResponse
    {
        $pt = $this->perguruanTinggi();

        // Authorization: hanya PT dengan status disetujui yang boleh mengajukan
        // (sama seperti create()). Cek di sini mencegah bypass via POST langsung.
        if ($pt->status_approval !== 'disetujui') {
            return redirect()->route('perguruan-tinggi.dashboard')
                ->with('error', 'Akun institusi Anda belum disetujui Bapperida. Silakan tunggu persetujuan sebelum mengajukan permohonan KKN.');
        }

        $validated = $request->validate([
            'periode'              => ['required', 'string', 'max:50'],
            'tanggal_mulai'        => ['required', 'date'],
            'tanggal_selesai'      => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'tema'                 => ['required', 'string', 'max:255'],
            'bidang_keilmuan'      => ['required', 'string', 'max:150'],
            'file_surat_permohonan'=> ['required', 'file', 'mimes:pdf', 'max:5120'],
            'file_proposal'        => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'mahasiswa'            => ['required', 'array', 'min:1'],
            'mahasiswa.*.nim'      => ['required', 'string', 'max:30', 'distinct'],
            'mahasiswa.*.nama'     => ['required', 'string', 'max:150'],
            'mahasiswa.*.prodi'    => ['nullable', 'string', 'max:100'],
            'mahasiswa.*.no_hp'    => ['nullable', 'string', 'max:30'],
            'mahasiswa.*.dpl_id'   => ['required', 'integer'],
            'mahasiswa.*.dpl_baru_nama'   => ['nullable', 'string', 'max:150'],
            'mahasiswa.*.dpl_baru_nip_niy'=> ['nullable', 'string', 'max:50'],
            'mahasiswa.*.dpl_baru_no_hp'  => ['nullable', 'string', 'max:30'],
        ]);

        // Validasi tambahan: setiap baris dengan dpl_id < 0 (DPL baru) WAJIB
        // mengisi dpl_baru_nama. required_if hanya menangani nilai persis -1,
        // sehingga marker negatif lain (mis. -5) bisa lolos & membuat dosen
        // bernama null. Cegah di sini dengan cek eksplisit per baris.
        $validator = Validator::make($validated, []);
        foreach ($validated['mahasiswa'] as $idx => $m) {
            if ((int) $m['dpl_id'] < 0 && empty($m['dpl_baru_nama'] ?? '')) {
                $validator->errors()->add("mahasiswa.{$idx}.dpl_baru_nama", 'Nama DPL baru wajib diisi.');
            }
        }
        if ($validator->errors()->any()) {
            throw new ValidationException($validator);
        }

        try {
            $permohonan = DB::transaction(function () use ($validated, $pt) {
                $permohonan = PermohonanKkn::create([
                    'perguruan_tinggi_id' => $pt->id,
                    'periode'             => $validated['periode'],
                    'tanggal_mulai'       => $validated['tanggal_mulai'],
                    'tanggal_selesai'     => $validated['tanggal_selesai'],
                    'file_surat_permohonan' => $this->storeFile($validated['file_surat_permohonan']),
                    'file_proposal'       => $this->storeFile($validated['file_proposal']),
                    'status'              => 'diajukan',
                ]);

                // Resolve DPL per baris → real id dosen.
                $dosenMap = $this->resolveDosen($validated['mahasiswa'], $pt);

                // Kelompok per DPL unik.
                $kelompokPerDosen = [];
                foreach (array_unique($dosenMap) as $dosenId) {
                    $kelompokPerDosen[$dosenId] = KelompokKkn::create([
                        'permohonan_kkn_id' => $permohonan->id,
                        'dosen_id'          => $dosenId,
                        'kode_kelompok'     => $this->generateKodeKelompok($pt, $permohonan),
                        'tema'              => $validated['tema'],
                        'bidang_keilmuan'   => $validated['bidang_keilmuan'],
                        'status'            => 'menunggu_matching',
                    ]);
                }

                // Buat mahasiswa + hitung jumlah per kelompok.
                $countPerDosen = [];
                foreach ($validated['mahasiswa'] as $idx => $m) {
                    $dosenId = $dosenMap[$idx];
                    Mahasiswa::create([
                        'kelompok_kkn_id' => $kelompokPerDosen[$dosenId]->id,
                        'nim'             => $m['nim'],
                        'nama'            => $m['nama'],
                        'prodi'           => $m['prodi'] ?? null,
                        'no_hp'           => $m['no_hp'] ?? null,
                    ]);
                    $countPerDosen[$dosenId] = ($countPerDosen[$dosenId] ?? 0) + 1;
                }
                foreach ($countPerDosen as $dosenId => $count) {
                    $kelompokPerDosen[$dosenId]->update(['jumlah_mahasiswa' => $count]);
                }

                return $permohonan;
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Gagal menyimpan permohonan. Periksa kembali data yang diisikan.');
        }

        // Notifikasi ke seluruh user Bapperida aktif (SYS-01).
        $bapperidaUsers = User::whereHas('role', fn ($q) => $q->where('nama_role', 'bapperida'))
            ->where('status_aktif', true)
            ->get();
        foreach ($bapperidaUsers as $bapperida) {
            $bapperida->notify(new PermohonanStatusNotification($permohonan));
        }

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'aksi'       => 'ajukan_permohonan',
            'deskripsi'  => "Mengajukan permohonan KKN periode {$validated['periode']}.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('perguruan-tinggi.permohonan.index')
            ->with('success', 'Permohonan KKN berhasil diajukan dan menunggu verifikasi Bapperida.');
    }

    /**
     * UC-04 — Detail permohonan (PT lihat status & data peserta).
     */
    public function show(PermohonanKkn $permohonan): View
    {
        $this->authorizeOwner($permohonan);

        $permohonan->load(['kelompokKkn.dosen', 'kelompokKkn.mahasiswa']);

        return view('perguruan-tinggi.permohonan.show', ['permohonan' => $permohonan]);
    }

    /* ===== helper internal ===== */

    private function perguruanTinggi(): \App\Models\PerguruanTinggi
    {
        // Defensif: user role perguruan_tinggi dijamin punya record PT oleh seeder,
        // tapi jika tidak (mis. akun superadmin yang memakai group route PT),
        // tolak akses daripada fatal null->method().
        $pt = Auth::user()->perguruanTinggi;

        if (! $pt) {
            abort(403, 'Akun Anda tidak terhubung ke data institusi perguruan tinggi.');
        }

        return $pt;
    }

    private function authorizeOwner(PermohonanKkn $permohonan): void
    {
        if ($permohonan->perguruan_tinggi_id !== $this->perguruanTinggi()->id) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }
    }

    /**
     * Resolve pilihan DPL tiap baris mahasiswa menjadi id dosen riil.
     * dpl_id > 0 → dosen yang sudah terdaftar milik PT.
     * dpl_id < 0 → buat dosen baru dari field dpl_baru_*.
     * dpl_id == 0 → tidak valid (placeholder), lempar error validasi.
     *
     * @return array<int,int> index baris => dosen id
     */
    private function resolveDosen(array $mahasiswa, \App\Models\PerguruanTinggi $pt): array
    {
        $map = [];      // key (id dosen / marker) => real dosen id
        $result = [];   // index baris => real dosen id

        foreach ($mahasiswa as $idx => $m) {
            $marker = (int) $m['dpl_id'];

            if ($marker > 0) {
                $key = $marker;
                if (! isset($map[$key])) {
                    $dosen = Dosen::where('perguruan_tinggi_id', $pt->id)->find($marker);
                    if (! $dosen) {
                        throw ValidationException::withMessages([
                            "mahasiswa.{$idx}.dpl_id" => 'DPL terpilih tidak valid.',
                        ]);
                    }
                    $map[$key] = $dosen->id;
                }
            } elseif ($marker < 0) {
                // DPL baru — buat record dosen milik PT.
                // Key dedup memakai kombinasi marker + identitas DPL baru, agar
                // baris "+ DPL Baru" yang isinya SAMA berbagi satu record,
                // sedangkan baris dengan data berbeda dibuatkan record terpisah
                // (mencegah data baris kedua tertimpa/reuse salah).
                $key = $marker.'|'.trim((string) ($m['dpl_baru_nama'] ?? '')).'|'.trim((string) ($m['dpl_baru_nip_niy'] ?? ''));
                if (! isset($map[$key])) {
                    $dosen = Dosen::create([
                        'perguruan_tinggi_id' => $pt->id,
                        'nama'                => $m['dpl_baru_nama'],
                        'nip_niy'             => $m['dpl_baru_nip_niy'] ?? null,
                        'no_hp'               => $m['dpl_baru_no_hp'] ?? null,
                    ]);
                    $map[$key] = $dosen->id;
                }
            } else {
                // dpl_id == 0 → tidak valid (placeholder tidak boleh terkirim).
                throw ValidationException::withMessages([
                    "mahasiswa.{$idx}.dpl_id" => 'Silakan pilih DPL atau isi data DPL baru.',
                ]);
            }

            $result[$idx] = $map[$key];
        }

        return $result;
    }

    private function generateKodeKelompok(\App\Models\PerguruanTinggi $pt, PermohonanKkn $permohonan): string
    {
        $tahun = $permohonan->tanggal_mulai?->year ?? now()->year;
        // Urutan per permohonan. id permohonan disertakan agar kode unik GLOBAL
        // (kode_kelompok punya unique index) meski PT sama mengajukan >1 permohonan
        // pada tahun yang sama.
        $urutan = KelompokKkn::where('permohonan_kkn_id', $permohonan->id)->count() + 1;

        return sprintf('KKN-%s-%04d-%02d', $tahun, $permohonan->id, $urutan);
    }

    private function storeFile(\Illuminate\Http\UploadedFile $file): string
    {
        // H1: simpan ke disk private (local) — diakses via route file.download terproteksi.
        return $file->store('permohonan');
    }
}