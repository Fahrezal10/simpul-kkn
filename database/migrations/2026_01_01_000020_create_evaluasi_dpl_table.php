<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluasi_dpl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_kkn_id')->constrained('kelompok_kkn')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('dosen')->cascadeOnDelete();
            $table->unsignedTinyInteger('nilai'); // 0-100
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['kelompok_kkn_id', 'dosen_id']); // cegah evaluasi ganda
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluasi_dpl');
    }
};
