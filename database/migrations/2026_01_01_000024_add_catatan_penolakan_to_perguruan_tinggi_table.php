<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 1 — UC-01 (Approval akun PT).
     *
     * Alasan penolakan registrasi PT dicatat di entity perguruan_tinggi
     * (bukan hanya ActivityLog) agar bisa ditampilkan/diisi ulang oleh PT
     * yang mengajukan ulang. Nullable karena hanya terisi saat ditolak.
     */
    public function up(): void
    {
        Schema::table('perguruan_tinggi', function (Blueprint $table) {
            $table->text('catatan_penolakan')->nullable()->after('status_approval');
        });
    }

    public function down(): void
    {
        Schema::table('perguruan_tinggi', function (Blueprint $table) {
            $table->dropColumn('catatan_penolakan');
        });
    }
};
