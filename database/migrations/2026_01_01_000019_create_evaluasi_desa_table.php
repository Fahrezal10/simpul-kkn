<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluasi_desa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_kkn_id')->constrained('kelompok_kkn')->cascadeOnDelete();
            $table->foreignId('desa_id')->constrained('desa')->cascadeOnDelete();
            $table->unsignedTinyInteger('skor_kualitas_program'); // skala 1-5
            $table->unsignedTinyInteger('skor_manfaat');
            $table->unsignedTinyInteger('skor_kedisiplinan');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['kelompok_kkn_id', 'desa_id']); // cegah evaluasi ganda
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluasi_desa');
    }
};
