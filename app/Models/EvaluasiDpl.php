<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluasiDpl extends Model
{
    protected $table = 'evaluasi_dpl';

    protected $fillable = [
        'kelompok_kkn_id',
        'dosen_id',
        'nilai',
        'catatan',
    ];

    public function kelompokKkn()
    {
        return $this->belongsTo(KelompokKkn::class);
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }
}