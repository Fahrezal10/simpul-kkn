<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_akhir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_kkn_id')->constrained('kelompok_kkn')->cascadeOnDelete();
            $table->string('file_laporan');
            $table->string('file_luaran')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_akhir');
    }
};
