<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PerguruanTinggi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PerguruanTinggiRegistrationController extends Controller
{
    /**
     * UC-01 — Formulir registrasi akun institusi Perguruan Tinggi.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register-pt');
    }

    /**
     * UC-01 — Simpan registrasi akun PT.
     *
     * Akun dibuat aktif untuk login, namun status approval perguruan_tinggi
     * tetap 'menunggu' sampai Bapperida menyetujui (UC-01 alternate flow:
     * akun aktif setelah approve; jika ditolak PT menerima catatan penolakan).
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_pt'    => ['required', 'string', 'max:200'],
            'alamat'     => ['nullable', 'string'],
            'pic_nama'   => ['required', 'string', 'max:150'],
            'pic_email'  => ['required', 'email', 'max:150'],
            'pic_telp'   => ['nullable', 'string', 'max:30'],
            'email'      => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
            'dokumen_legalitas' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $roleId = Role::where('nama_role', 'perguruan_tinggi')->value('id');

        try {
            $user = DB::transaction(function () use ($validated, $roleId, $request) {
                $user = User::create([
                    'role_id'       => $roleId,
                    'nama'          => $validated['nama_pt'],
                    'email'         => $validated['email'],
                    'password'      => Hash::make($validated['password']),
                    'status_aktif'  => true,
                ]);

                $dokumenPath = null;
                if ($request->hasFile('dokumen_legalitas')) {
                    $dokumenPath = $request->file('dokumen_legalitas')
                        ->store('perguruan-tinggi/legalitas', 'public');
                }

                PerguruanTinggi::create([
                    'user_id'          => $user->id,
                    'nama_pt'          => $validated['nama_pt'],
                    'alamat'           => $validated['alamat'] ?? null,
                    'pic_nama'         => $validated['pic_nama'],
                    'pic_email'        => $validated['pic_email'],
                    'pic_telp'         => $validated['pic_telp'] ?? null,
                    'dokumen_legalitas'=> $dokumenPath,
                    'status_approval'  => 'menunggu',
                ]);

                return $user;
            });
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Gagal menyimpan pendaftaran. Silakan coba lagi.'])
                ->with('error', 'Terjadi kesalahan saat menyimpan pendaftaran.');
        }

        ActivityLog::create([
            'user_id'    => $user->id,
            'aksi'       => 'registrasi_pt',
            'deskripsi'  => "Registrasi akun Perguruan Tinggi: {$validated['nama_pt']}.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Pendaftaran berhasil. Akun Anda menunggu persetujuan Bapperida sebelum dapat mengajukan permohonan KKN.');
    }
}
