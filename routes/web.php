<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Shared\DashboardController;
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
    ->middleware('guest');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/* ===== Dashboard (redirect per-role) ===== */
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Placeholder modul per role — diisi bertahap sesuai fase pengembangan.
    // Contoh: Bapperida memverifikasi permohonan (Fase 1), dsb.
    Route::get('/bapperida', function () {
        return view('dashboard.index', ['roleSlug' => 'bapperida', 'roleLabel' => 'Bapperida']);
    })->middleware('role:bapperida,superadmin')->name('bapperida.dashboard');

    Route::get('/perguruan-tinggi', function () {
        return view('dashboard.index', ['roleSlug' => 'perguruan-tinggi', 'roleLabel' => 'Perguruan Tinggi']);
    })->middleware('role:perguruan_tinggi,superadmin')->name('perguruan-tinggi.dashboard');

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
