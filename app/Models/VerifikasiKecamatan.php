<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifikasiKecamatan extends Model
{
    protected $table = 'verifikasi_kecamatan';

    protected $fillable = [
        'kelompok_kkn_id',
        'kecamatan_id',
        'desa_id',
        'status',
        'catatan',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'status' => 'string',
        'verified_at' => 'datetime',
    ];

    public function kelompokKkn()
    {
        return $this->belongsTo(KelompokKkn::class);
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}