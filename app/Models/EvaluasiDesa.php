<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluasiDesa extends Model
{
    protected $table = 'evaluasi_desa';

    protected $fillable = [
        'kelompok_kkn_id',
        'desa_id',
        'skor_kualitas_program',
        'skor_manfaat',
        'skor_kedisiplinan',
        'catatan',
    ];

    public function kelompokKkn()
    {
        return $this->belongsTo(KelompokKkn::class);
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class);
    }
}