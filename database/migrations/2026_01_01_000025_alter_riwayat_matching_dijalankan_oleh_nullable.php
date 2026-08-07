<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * UC-06 — Membuat kolom `dijalankan_oleh` pada riwayat_matching boleh NULL.
     *
     * Alasan: matching bisa dijalankan otomatis/sistem (tanpa user konteks
     * yang valid), mis. saat batch/re-run. FK tetap terjaga bila ada user,
     * namun tidak lagi memaksa id user yang selalu ada.
     */
    public function up(): void
    {
        Schema::table('riwayat_matching', function (Blueprint $table) {
            $table->unsignedBigInteger('dijalankan_oleh')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Kembalikan menjadi NOT NULL (hanya bila semua baris sudah berisi nilai valid).
        Schema::table('riwayat_matching', function (Blueprint $table) {
            $table->unsignedBigInteger('dijalankan_oleh')->nullable(false)->change();
        });
    }
};
