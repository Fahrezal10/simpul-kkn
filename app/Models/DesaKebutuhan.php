<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesaKebutuhan extends Model
{
    protected $table = 'desa_kebutuhan';

    protected $fillable = [
        'desa_id',
        'kategori',
        'deskripsi',
        'prioritas',
    ];

    protected $casts = [
        'prioritas' => 'string',
    ];

    public function desa()
    {
        return $this->belongsTo(Desa::class);
    }
}