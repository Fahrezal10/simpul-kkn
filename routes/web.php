<?php

use App\Http\Controllers\Auth\PerguruanTinggiRegistrationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Bapperida\ApprovalFinalController;
use App\Http\Controllers\Bapperida\DesaController;
use App\Http\Controllers\Bapperida\MonitoringController;
use App\Http\Controllers\Bapperida\MatchingController;
use App\Http\Controllers\Bapperida\PerguruanTinggiApprovalController;
use App\Http\Controllers\Bapperida\PermohonanVerificationController;
use App\Http\Controllers\Bapperida\PenutupanPeriodeController;
use App\Http\Controllers\Desa\EvaluasiDesaController;
use App\Http\Controllers\Desa\ProfilDesaController;
use App\Http\Controllers\Dosen\EvaluasiDplController;
use App\Http\Controllers\Dosen\LaporanVerifikasiController;
use App\Http\Controllers\Dosen\LogbookApprovalController;
use App\Http\Controllers\Kecamatan\VerifikasiKecamatanController;
use App\Http\Controllers\Shared\ActivityLogController;
use App\Http\Controllers\Mahasiswa\LaporanAkhirController;
use App\Http\Controllers\Mahasiswa\LogbookController;
use App\Http\Controllers\PerguruanTinggi\PermohonanController;
use App\Http\Controllers\PerangkatDaerah\IsuStrategisController;
use App\Http\Controllers\Shared\DashboardController;
use App\Http\Controllers\Shared\DashboardGisController;
use App\Http\Controllers\Shared\MasterDataController;
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

    /* UC-09: Dashboard GIS (peta Leaflet) */
    Route::get('/gis', [DashboardGisController::class, 'index'])->name('dashboard.gis');
    Route::get('/gis/data', [DashboardGisController::class, 'data'])->name('dashboard.gis.data');

    /* SYS-01: Notifikasi in-app */
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/ajax/{id}/read', [NotificationController::class, 'markAsReadAjax'])->name('notifications.mark-as-read-ajax');

    /* SYS-02: Aktivitas sistem (audit trail) — khusus Bapperida */
    Route::get('/activity-log', [ActivityLogController::class, 'index'])
        ->middleware('role:bapperida,superadmin')->name('activity-log.index');
    Route::get('/activity-log/data', [ActivityLogController::class, 'data'])
        ->middleware('role:bapperida,superadmin')->name('activity-log.data');

    /* ===== UC-08: Kelola Master Data (CRUD generik oleh Bapperida) ===== */
    Route::prefix('master-data')->middleware('role:bapperida,superadmin')->group(function () {
        Route::get('/', [MasterDataController::class, 'index'])->name('master-data.index');
        Route::get('/{jenis}', [MasterDataController::class, 'index'])->name('master-data.list');
        Route::get('/{jenis}/data', [MasterDataController::class, 'data'])->name('master-data.data');
        Route::post('/{jenis}', [MasterDataController::class, 'store'])->name('master-data.store');
        Route::put('/{jenis}/{id}', [MasterDataController::class, 'update'])->name('master-data.update');
        Route::delete('/{jenis}/{id}', [MasterDataController::class, 'destroy'])->name('master-data.destroy');
    });

    // Dashboard per-role → lewat DashboardController agar statistik & variabel
    // view (roleSlug, roleLabel, stats) selalu lengkap.
    Route::get('/bapperida', [DashboardController::class, 'index'])
        ->middleware('role:bapperida,superadmin')->name('bapperida.dashboard');

    Route::get('/perguruan-tinggi', [DashboardController::class, 'index'])
        ->middleware('role:perguruan_tinggi,superadmin')->name('perguruan-tinggi.dashboard');

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

        /* ===== UC-12: Master Data Desa (CRUD oleh Bapperida) ===== */
        Route::get('desa', [DesaController::class, 'index'])->name('bapperida.desa.index');
        Route::get('desa/data', [DesaController::class, 'data'])->name('bapperida.desa.data');
        Route::get('desa/create', [DesaController::class, 'create'])->name('bapperida.desa.create');
        Route::post('desa', [DesaController::class, 'store'])->name('bapperida.desa.store');
        Route::get('desa/{desa}', [DesaController::class, 'show'])->name('bapperida.desa.show');
        Route::get('desa/{desa}/edit', [DesaController::class, 'edit'])->name('bapperida.desa.edit');
        Route::put('desa/{desa}', [DesaController::class, 'update'])->name('bapperida.desa.update');
        Route::delete('desa/{desa}', [DesaController::class, 'destroy'])->name('bapperida.desa.destroy');

        /* ===== UC-09: Dashboard Monitoring & Evaluasi ===== */
        Route::get('monitoring', [MonitoringController::class, 'index'])->name('bapperida.monitoring.index');

        /* ===== Penutupan periode: kelompok aktif → selesai ===== */
        Route::get('penutupan-periode', [PenutupanPeriodeController::class, 'index'])->name('bapperida.penutupan-periode.index');
        Route::post('penutupan-periode', [PenutupanPeriodeController::class, 'store'])->name('bapperida.penutupan-periode.store');

        /* ===== UC-07: Persetujuan akhir pelaksanaan KKN ===== */
        Route::get('approval-final', [ApprovalFinalController::class, 'index'])->name('bapperida.approval-final.index');
        Route::get('approval-final/data', [ApprovalFinalController::class, 'data'])->name('bapperida.approval-final.data');
        Route::get('approval-final/{kelompokKkn}', [ApprovalFinalController::class, 'show'])->name('bapperida.approval-final.show');
        Route::post('approval-final/{kelompokKkn}/approve', [ApprovalFinalController::class, 'approve'])->name('bapperida.approval-final.approve');
        Route::post('approval-final/{kelompokKkn}/tolak', [ApprovalFinalController::class, 'tolak'])->name('bapperida.approval-final.tolak');
    });

    /* ===== UC-02/03/04: Permohonan KKN oleh PT ===== */
    Route::prefix('pt')->middleware('role:perguruan_tinggi,superadmin')->group(function () {
        Route::get('permohonan', [PermohonanController::class, 'index'])->name('perguruan-tinggi.permohonan.index');
        Route::get('permohonan/data', [PermohonanController::class, 'data'])->name('perguruan-tinggi.permohonan.data');
        Route::get('permohonan/create', [PermohonanController::class, 'create'])->name('perguruan-tinggi.permohonan.create');
        Route::post('permohonan', [PermohonanController::class, 'store'])->name('perguruan-tinggi.permohonan.store');
        Route::get('permohonan/{permohonan}', [PermohonanController::class, 'show'])->name('perguruan-tinggi.permohonan.show');
    });

    /* ===== UC-12: Kelola profil & potensi desa oleh Operator Desa ===== */
    Route::prefix('desa')->middleware('role:desa,superadmin')->group(function () {
        Route::get('profil', [ProfilDesaController::class, 'index'])->name('desa.profil.index');
        Route::get('profil/edit', [ProfilDesaController::class, 'edit'])->name('desa.profil.edit');
        Route::put('profil', [ProfilDesaController::class, 'update'])->name('desa.profil.update');
        Route::post('potensi', [ProfilDesaController::class, 'potensiStore'])->name('desa.profil.potensi.store');
        Route::delete('potensi/{potensi}', [ProfilDesaController::class, 'potensiDestroy'])->name('desa.profil.potensi.destroy');
        Route::post('permasalahan', [ProfilDesaController::class, 'permasalahanStore'])->name('desa.profil.permasalahan.store');
        Route::delete('permasalahan/{permasalahan}', [ProfilDesaController::class, 'permasalahanDestroy'])->name('desa.profil.permasalahan.destroy');
        Route::post('kebutuhan', [ProfilDesaController::class, 'kebutuhanStore'])->name('desa.profil.kebutuhan.store');
        Route::delete('kebutuhan/{kebutuhan}', [ProfilDesaController::class, 'kebutuhanDestroy'])->name('desa.profil.kebutuhan.destroy');

        /* UC-13: Evaluasi kelompok oleh desa */
        Route::get('evaluasi', [EvaluasiDesaController::class, 'index'])->name('desa.evaluasi.index');
        Route::get('evaluasi/data', [EvaluasiDesaController::class, 'data'])->name('desa.evaluasi.data');
        Route::get('evaluasi/{kelompokKkn}', [EvaluasiDesaController::class, 'show'])->name('desa.evaluasi.show');
        Route::post('evaluasi/{kelompokKkn}', [EvaluasiDesaController::class, 'store'])->name('desa.evaluasi.store');
    });

    /* ===== UC-10: Input isu strategis oleh Operator Perangkat Daerah ===== */
    Route::prefix('perangkat-daerah')->middleware('role:perangkat_daerah,superadmin')->group(function () {
        Route::get('isu-strategis', [IsuStrategisController::class, 'index'])->name('perangkat-daerah.isu-strategis.index');
        Route::get('isu-strategis/data', [IsuStrategisController::class, 'data'])->name('perangkat-daerah.isu-strategis.data');
        Route::post('isu-strategis', [IsuStrategisController::class, 'store'])->name('perangkat-daerah.isu-strategis.store');
        Route::delete('isu-strategis/{isu}', [IsuStrategisController::class, 'destroy'])->name('perangkat-daerah.isu-strategis.destroy');
    });

    /* ===== UC-11: Verifikasi kesiapan desa oleh Operator Kecamatan ===== */
    Route::prefix('kecamatan')->middleware('role:kecamatan,superadmin')->group(function () {
        Route::get('verifikasi', [VerifikasiKecamatanController::class, 'index'])->name('kecamatan.verifikasi.index');
        Route::get('verifikasi/data', [VerifikasiKecamatanController::class, 'data'])->name('kecamatan.verifikasi.data');
        Route::get('verifikasi/{kelompokKkn}', [VerifikasiKecamatanController::class, 'show'])->name('kecamatan.verifikasi.show');
        Route::post('verifikasi/{kelompokKkn}', [VerifikasiKecamatanController::class, 'store'])->name('kecamatan.verifikasi.store');
    });

    // Dashboard mahasiswa/dosen → lewat DashboardController (variabel $stats lengkap).
    Route::get('/mahasiswa', [DashboardController::class, 'index'])
        ->middleware('role:mahasiswa,superadmin')->name('mahasiswa.dashboard');

    Route::get('/dosen', [DashboardController::class, 'index'])
        ->middleware('role:dosen,superadmin')->name('dosen.dashboard');

    /* ===== UC-14: Logbook harian oleh Mahasiswa ===== */
    Route::prefix('mahasiswa')->middleware('role:mahasiswa,superadmin')->group(function () {
        Route::get('logbook', [LogbookController::class, 'index'])->name('mahasiswa.logbook.index');
        Route::get('logbook/data', [LogbookController::class, 'data'])->name('mahasiswa.logbook.data');
        Route::post('logbook', [LogbookController::class, 'store'])->name('mahasiswa.logbook.store');

        /* UC-15: Laporan akhir */
        Route::get('laporan-akhir', [LaporanAkhirController::class, 'index'])->name('mahasiswa.laporan-akhir.index');
        Route::get('laporan-akhir/data', [LaporanAkhirController::class, 'data'])->name('mahasiswa.laporan-akhir.data');
        Route::post('laporan-akhir', [LaporanAkhirController::class, 'store'])->name('mahasiswa.laporan-akhir.store');
    });

    /* ===== UC-16: Approval logbook oleh DPL ===== */
    Route::prefix('dosen')->middleware('role:dosen,superadmin')->group(function () {
        Route::get('logbook', [LogbookApprovalController::class, 'index'])->name('dosen.logbook.index');
        Route::get('logbook/data', [LogbookApprovalController::class, 'data'])->name('dosen.logbook.data');
        Route::get('logbook/{logbook}', [LogbookApprovalController::class, 'show'])->name('dosen.logbook.show');
        Route::post('logbook/{logbook}/approve', [LogbookApprovalController::class, 'approve'])->name('dosen.logbook.approve');
        Route::post('logbook/{logbook}/revisi', [LogbookApprovalController::class, 'revisi'])->name('dosen.logbook.revisi');

        /* UC-15 (verifikasi): laporan akhir */
        Route::get('laporan-akhir', [LaporanVerifikasiController::class, 'index'])->name('dosen.laporan-akhir.index');
        Route::get('laporan-akhir/data', [LaporanVerifikasiController::class, 'data'])->name('dosen.laporan-akhir.data');
        Route::get('laporan-akhir/{laporan}', [LaporanVerifikasiController::class, 'show'])->name('dosen.laporan-akhir.show');
        Route::post('laporan-akhir/{laporan}/approve', [LaporanVerifikasiController::class, 'approve'])->name('dosen.laporan-akhir.approve');
        Route::post('laporan-akhir/{laporan}/revisi', [LaporanVerifikasiController::class, 'revisi'])->name('dosen.laporan-akhir.revisi');

        /* UC-17: Evaluasi kelompok oleh DPL */
        Route::get('evaluasi', [EvaluasiDplController::class, 'index'])->name('dosen.evaluasi.index');
        Route::get('evaluasi/data', [EvaluasiDplController::class, 'data'])->name('dosen.evaluasi.data');
        Route::get('evaluasi/{kelompokKkn}', [EvaluasiDplController::class, 'show'])->name('dosen.evaluasi.show');
        Route::post('evaluasi/{kelompokKkn}', [EvaluasiDplController::class, 'store'])->name('dosen.evaluasi.store');
    });

    // Dashboard per-role → lewat DashboardController agar statistik & variabel
    // view (roleSlug, roleLabel, stats) selalu lengkap.
    Route::get('/perangkat-daerah', [DashboardController::class, 'index'])
        ->middleware('role:perangkat_daerah,superadmin')->name('perangkat-daerah.dashboard');

    Route::get('/kecamatan', [DashboardController::class, 'index'])
        ->middleware('role:kecamatan,superadmin')->name('kecamatan.dashboard');

    Route::get('/desa', [DashboardController::class, 'index'])
        ->middleware('role:desa,superadmin')->name('desa.dashboard');
});
