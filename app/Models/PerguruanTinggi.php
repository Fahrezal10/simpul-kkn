<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerguruanTinggi extends Model
{
    use SoftDeletes;

    protected $table = 'perguruan_tinggi';

    protected $fillable = [
        'user_id',
        'nama_pt',
        'alamat',
        'pic_nama',
        'pic_email',
        'pic_telp',
        'dokumen_legalitas',
        'status_approval',
        'catatan_penolakan',
    ];

    protected $casts = [
        'status_approval' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dosen()
    {
        return $this->hasMany(Dosen::class);
    }

    public function permohonanKkn()
    {
        return $this->hasMany(PermohonanKkn::class);
    }
}