<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perguruan_tinggi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_pt', 200);
            $table->text('alamat')->nullable();
            $table->string('pic_nama', 150)->nullable();
            $table->string('pic_email', 150)->nullable();
            $table->string('pic_telp', 30)->nullable();
            $table->string('dokumen_legalitas')->nullable();
            $table->enum('status_approval', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perguruan_tinggi');
    }
};
