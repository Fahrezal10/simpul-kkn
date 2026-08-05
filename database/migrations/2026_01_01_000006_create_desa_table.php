<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('desa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('kecamatan_id')->constrained('kecamatan')->restrictOnDelete();
            $table->string('nama_desa', 100);
            $table->string('kode_wilayah', 20)->unique();
            $table->integer('jumlah_penduduk')->nullable();
            $table->decimal('luas_wilayah', 10, 2)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('profil_umum')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desa');
    }
};
