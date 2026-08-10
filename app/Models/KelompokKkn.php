<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KelompokKkn extends Model
{
    use SoftDeletes;

    protected $table = 'kelompok_kkn';

    protected $fillable = [
        'permohonan_kkn_id',
        'dosen_id',
        'desa_id',
        'kode_kelompok',
        'tema',
        'bidang_keilmuan',
        'jumlah_mahasiswa',
        'status',
    ];

    protected $casts = [
        'jumlah_mahasiswa' => 'integer',
        'status' => 'string',
    ];

    public function permohonanKkn()
    {
        return $this->belongsTo(PermohonanKkn::class);
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class);
    }

    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class);
    }

    public function riwayatMatching()
    {
        return $this->hasMany(RiwayatMatching::class);
    }

    public function logbook()
    {
        return $this->hasMany(Logbook::class);
    }

    public function verifikasiKecamatan()
    {
        return $this->hasMany(VerifikasiKecamatan::class);
    }

    public function evaluasiDesa()
    {
        return $this->hasMany(EvaluasiDesa::class);
    }

    public function evaluasiDpl()
    {
        return $this->hasMany(EvaluasiDpl::class);
    }
}