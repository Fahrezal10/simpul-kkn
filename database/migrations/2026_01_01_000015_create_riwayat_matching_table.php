<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_matching', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_kkn_id')->constrained('kelompok_kkn')->cascadeOnDelete();
            $table->foreignId('desa_id')->constrained('desa')->cascadeOnDelete();
            $table->decimal('skor_tema', 5, 2)->default(0);
            $table->decimal('skor_bidang', 5, 2)->default(0);
            $table->decimal('skor_prioritas', 5, 2)->default(0);
            $table->decimal('skor_kebutuhan', 5, 2)->default(0);
            $table->decimal('skor_total', 5, 2)->default(0);
            $table->boolean('flag_tumpang_tindih')->default(false);
            $table->enum('status', ['kandidat', 'dipilih', 'ditolak'])->default('kandidat');
            $table->foreignId('dijalankan_oleh')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['kelompok_kkn_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_matching');
    }
};
