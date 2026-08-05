<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerangkatDaerah extends Model
{
    protected $table = 'perangkat_daerah';

    protected $fillable = [
        'user_id',
        'nama_opd',
        'bidang_tugas',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isuStrategis()
    {
        return $this->hasMany(IsuStrategis::class);
    }
}