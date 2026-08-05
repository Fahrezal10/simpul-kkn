<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    protected $table = 'dosen';

    protected $fillable = [
        'user_id',
        'perguruan_tinggi_id',
        'nama',
        'nip_niy',
        'no_hp',
        'email',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function perguruanTinggi()
    {
        return $this->belongsTo(PerguruanTinggi::class);
    }

    public function kelompokKkn()
    {
        return $this->hasMany(KelompokKkn::class);
    }
}