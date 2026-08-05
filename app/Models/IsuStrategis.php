<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IsuStrategis extends Model
{
    protected $table = 'isu_strategis';

    protected $fillable = [
        'perangkat_daerah_id',
        'kategori_isu',
        'deskripsi',
        'wilayah_terdampak',
        'rekomendasi_tema',
    ];

    public function perangkatDaerah()
    {
        return $this->belongsTo(PerangkatDaerah::class);
    }
}