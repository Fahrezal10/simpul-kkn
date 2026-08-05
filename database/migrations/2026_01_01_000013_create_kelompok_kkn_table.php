<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelompok_kkn', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_kkn_id')->constrained('permohonan_kkn')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('dosen')->restrictOnDelete();
            $table->foreignId('desa_id')->nullable()->constrained('desa')->nullOnDelete();
            // desa_id nullable: baru terisi setelah matching + verifikasi kecamatan + approval selesai
            $table->string('kode_kelompok', 50)->unique();
            $table->string('tema', 255);
            $table->string('bidang_keilmuan', 150)->nullable();
            $table->integer('jumlah_mahasiswa')->default(0);
            $table->enum('status', [
                'menunggu_matching',
                'menunggu_verifikasi_kecamatan',
                'menunggu_persetujuan',
                'aktif',
                'selesai',
            ])->default('menunggu_matching');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelompok_kkn');
    }
};
