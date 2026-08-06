<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 1 — Alur Pengajuan (UC-03).
     *
     * Alur input mahasiswa & DPL bisa berlangsung sebelum kelompok KKN final
     * dibentuk (kelompok auto-generate per DPL saat permohonan disimpan).
     * Karena itu kelompok_kkn_id pada mahasiswa diizinkan nullable.
     */
    public function up(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->foreignId('kelompok_kkn_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->foreignId('kelompok_kkn_id')->change();
        });
    }
};
