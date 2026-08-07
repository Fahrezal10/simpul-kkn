<?php

use App\Http\Controllers\Auth\PerguruanTinggiRegistrationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Bapperida\PerguruanTinggiApprovalController;
use App\Http\Controllers\Bapperida\PermohonanVerificationController;
use App\Http\Controllers\Bapperida\MatchingController;
use App\Http\Controllers\PerguruanTinggi\PermohonanController;
use App\Http\Controllers\Shared\DashboardController;
use App\Http\Controllers\Shared\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Single portal multi-role: login → redirect dashboard sesuai role.
| Detail alur & role matrix ada di docs/00-design-system.md §4.
*/

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

/* ===== Autentikasi ===== */
Route::get('/login', [AuthController::class, 'showLoginForm'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware(['guest', 'throttle:10,1']); // cegah brute-force login

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/* ===== UC-01: Registrasi akun Perguruan Tinggi (guest) ===== */
Route::get('/register-pt', [PerguruanTinggiRegistrationController::class, 'showRegistrationForm'])
    ->middleware('guest')
    ->name('register-pt.form');

Route::post('/register-pt', [PerguruanTinggiRegistrationController::class, 'register'])
    ->middleware('guest')
    ->name('register-pt.store');

/* ===== Dashboard (redirect per-role) ===== */
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /* SYS-01: Notifikasi in-app */
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/ajax/{id}/read', [NotificationController::class, 'markAsReadAjax'])->name('notifications.mark-as-read-ajax');

    // Placeholder modul per role — diisi bertahap sesuai fase pengembangan.
    // Contoh: Bapperida memverifikasi permohonan (Fase 1), dsb.
    Route::get('/bapperida', function () {
        return view('dashboard.index', ['roleSlug' => 'bapperida', 'roleLabel' => 'Bapperida']);
    })->middleware('role:bapperida,superadmin')->name('bapperida.dashboard');

    Route::get('/perguruan-tinggi', function () {
        return view('dashboard.index', ['roleSlug' => 'perguruan-tinggi', 'roleLabel' => 'Perguruan Tinggi']);
    })->middleware('role:perguruan_tinggi,superadmin')->name('perguruan-tinggi.dashboard');

    /* ===== UC-01: Persetujuan akun PT oleh Bapperida ===== */
    Route::prefix('bapperida')->middleware('role:bapperida,superadmin')->group(function () {
        Route::get('perguruan-tinggi', [PerguruanTinggiApprovalController::class, 'index'])
            ->name('bapperida.pt.index');
        Route::get('perguruan-tinggi/data', [PerguruanTinggiApprovalController::class, 'data'])
            ->name('bapperida.pt.data');
        Route::get('perguruan-tinggi/{perguruanTinggi}', [PerguruanTinggiApprovalController::class, 'show'])
            ->name('bapperida.pt.show');
        Route::post('perguruan-tinggi/{perguruanTinggi}/approve', [PerguruanTinggiApprovalController::class, 'approve'])
            ->name('bapperida.pt.approve');
        Route::post('perguruan-tinggi/{perguruanTinggi}/reject', [PerguruanTinggiApprovalController::class, 'reject'])
            ->name('bapperida.pt.reject');

        Route::get('permohonan', [PermohonanVerificationController::class, 'index'])
            ->name('bapperida.permohonan.index');
        Route::get('permohonan/data', [PermohonanVerificationController::class, 'data'])
            ->name('bapperida.permohonan.data');
        Route::get('permohonan/{permohonan}', [PermohonanVerificationController::class, 'show'])
            ->name('bapperida.permohonan.show');
        Route::post('permohonan/{permohonan}/verify', [PermohonanVerificationController::class, 'verify'])
            ->name('bapperida.permohonan.verify');
        Route::post('permohonan/{permohonan}/reject', [PermohonanVerificationController::class, 'reject'])
            ->name('bapperida.permohonan.reject');

        /* ===== UC-06: Matching Engine (rekomendasi desa per kelompok) ===== */
        Route::get('matching', [MatchingController::class, 'index'])->name('bapperida.matching.index');
        Route::get('matching/data', [MatchingController::class, 'data'])->name('bapperida.matching.data');
        Route::get('matching/{kelompokKkn}', [MatchingController::class, 'show'])->name('bapperida.matching.show');
        Route::post('matching/{kelompokKkn}/run', [MatchingController::class, 'run'])->name('bapperida.matching.run');
        Route::post('matching/{kelompokKkn}/override', [MatchingController::class, 'override'])->name('bapperida.matching.override');
        Route::post('matching/{kelompokKkn}/batal-pilih', [MatchingController::class, 'batalPilih'])->name('bapperida.matching.batal-pilih');
    });

    /* ===== UC-02/03/04: Permohonan KKN oleh PT ===== */
    Route::prefix('pt')->middleware('role:perguruan_tinggi,superadmin')->group(function () {
        Route::get('permohonan', [PermohonanController::class, 'index'])->name('perguruan-tinggi.permohonan.index');
        Route::get('permohonan/data', [PermohonanController::class, 'data'])->name('perguruan-tinggi.permohonan.data');
        Route::get('permohonan/create', [PermohonanController::class, 'create'])->name('perguruan-tinggi.permohonan.create');
        Route::post('permohonan', [PermohonanController::class, 'store'])->name('perguruan-tinggi.permohonan.store');
        Route::get('permohonan/{permohonan}', [PermohonanController::class, 'show'])->name('perguruan-tinggi.permohonan.show');
    });

    Route::get('/mahasiswa', function () {
        return view('dashboard.index', ['roleSlug' => 'mahasiswa', 'roleLabel' => 'Mahasiswa']);
    })->middleware('role:mahasiswa,superadmin')->name('mahasiswa.dashboard');

    Route::get('/dosen', function () {
        return view('dashboard.index', ['roleSlug' => 'dosen', 'roleLabel' => 'Dosen']);
    })->middleware('role:dosen,superadmin')->name('dosen.dashboard');

    Route::get('/perangkat-daerah', function () {
        return view('dashboard.index', ['roleSlug' => 'perangkat-daerah', 'roleLabel' => 'Perangkat Daerah']);
    })->middleware('role:perangkat_daerah,superadmin')->name('perangkat-daerah.dashboard');

    Route::get('/kecamatan', function () {
        return view('dashboard.index', ['roleSlug' => 'kecamatan', 'roleLabel' => 'Kecamatan']);
    })->middleware('role:kecamatan,superadmin')->name('kecamatan.dashboard');

    Route::get('/desa', function () {
        return view('dashboard.index', ['roleSlug' => 'desa', 'roleLabel' => 'Desa']);
    })->middleware('role:desa,superadmin')->name('desa.dashboard');
});
