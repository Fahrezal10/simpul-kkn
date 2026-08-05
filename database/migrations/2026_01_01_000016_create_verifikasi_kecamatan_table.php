<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifikasi_kecamatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_kkn_id')->constrained('kelompok_kkn')->cascadeOnDelete();
            $table->foreignId('kecamatan_id')->constrained('kecamatan')->restrictOnDelete();
            $table->foreignId('desa_id')->constrained('desa')->restrictOnDelete();
            $table->enum('status', ['siap', 'tidak_siap'])->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikasi_kecamatan');
    }
};
