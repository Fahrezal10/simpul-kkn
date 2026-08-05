<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    protected $table = 'kecamatan';

    protected $fillable = [
        'user_id',
        'nama_kecamatan',
        'kode_wilayah',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function desa()
    {
        return $this->hasMany(Desa::class);
    }
}