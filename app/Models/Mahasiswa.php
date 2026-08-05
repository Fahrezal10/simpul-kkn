<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mahasiswa extends Model
{
    use SoftDeletes;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'user_id',
        'kelompok_kkn_id',
        'nim',
        'nama',
        'prodi',
        'no_hp',
        'foto',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kelompokKkn()
    {
        return $this->belongsTo(KelompokKkn::class);
    }

    public function logbook()
    {
        return $this->hasMany(Logbook::class);
    }
}