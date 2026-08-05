<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('isu_strategis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perangkat_daerah_id')->constrained('perangkat_daerah')->cascadeOnDelete();
            $table->string('kategori_isu', 100); // mis. stunting, UMKM, lingkungan
            $table->text('deskripsi');
            $table->string('wilayah_terdampak', 255)->nullable();
            $table->string('rekomendasi_tema', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('isu_strategis');
    }
};
