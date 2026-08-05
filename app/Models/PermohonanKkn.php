<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermohonanKkn extends Model
{
    use SoftDeletes;

    protected $table = 'permohonan_kkn';

    protected $fillable = [
        'perguruan_tinggi_id',
        'verified_by',
        'periode',
        'tanggal_mulai',
        'tanggal_selesai',
        'file_surat_permohonan',
        'file_proposal',
        'status',
        'catatan_verifikasi',
        'verified_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'status' => 'string',
        'verified_at' => 'datetime',
    ];

    public function perguruanTinggi()
    {
        return $this->belongsTo(PerguruanTinggi::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function kelompokKkn()
    {
        return $this->hasMany(KelompokKkn::class);
    }
}