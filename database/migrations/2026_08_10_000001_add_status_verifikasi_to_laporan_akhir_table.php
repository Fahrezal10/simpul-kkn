<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * UC-15 — Tambah status verifikasi pada laporan akhir.
     *
     * Laporan akhir di-upload mahasiswa → DPL/PT/Bapperida dapat melihat.
     * Kolom status mengikuti pola logbook: menunggu/disetujui/revisi.
     */
    public function up(): void
    {
        Schema::table('laporan_akhir', function (Blueprint $table) {
            $table->enum('status', ['menunggu', 'disetujui', 'revisi'])->default('menunggu');
            $table->text('catatan_verifikasi')->nullable();
            $table->foreignId('verified_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('laporan_akhir', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['status', 'catatan_verifikasi', 'verified_at']);
        });
    }
};
