<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan_kkn', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perguruan_tinggi_id')->constrained('perguruan_tinggi')->restrictOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('periode', 50); // mis. "Ganjil 2026/2027"
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('file_surat_permohonan')->nullable();
            $table->string('file_proposal')->nullable();
            $table->enum('status', ['diajukan', 'terverifikasi', 'ditolak', 'disetujui', 'selesai'])
                  ->default('diajukan');
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_kkn');
    }
};
