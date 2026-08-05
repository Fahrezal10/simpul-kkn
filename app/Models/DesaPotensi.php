<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesaPotensi extends Model
{
    protected $table = 'desa_potensi';

    protected $fillable = [
        'desa_id',
        'kategori',
        'deskripsi',
    ];

    public function desa()
    {
        return $this->belongsTo(Desa::class);
    }
}