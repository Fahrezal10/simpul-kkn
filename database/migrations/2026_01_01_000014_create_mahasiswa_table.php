<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('kelompok_kkn_id')->constrained('kelompok_kkn')->cascadeOnDelete();
            $table->string('nim', 30);
            $table->string('nama', 150);
            $table->string('prodi', 100)->nullable();
            $table->string('no_hp', 30)->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['kelompok_kkn_id', 'nim']); // cegah NIM duplikat dalam 1 kelompok
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
