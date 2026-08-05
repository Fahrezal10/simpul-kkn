<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesaPermasalahan extends Model
{
    protected $table = 'desa_permasalahan';

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