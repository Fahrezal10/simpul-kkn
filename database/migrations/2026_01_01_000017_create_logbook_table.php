<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbook', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_kkn_id')->constrained('kelompok_kkn')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->date('tanggal');
            $table->text('deskripsi_kegiatan');
            $table->string('foto')->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'revisi'])->default('menunggu');
            $table->text('catatan_dpl')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['mahasiswa_id', 'tanggal']); // cegah logbook ganda per hari
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook');
    }
};
