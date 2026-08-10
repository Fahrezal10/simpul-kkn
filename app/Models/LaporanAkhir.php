<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanAkhir extends Model
{
    protected $table = 'laporan_akhir';

    protected $fillable = [
        'kelompok_kkn_id',
        'file_laporan',
        'file_luaran',
        'uploaded_by',
        'uploaded_at',
        'status',
        'catatan_verifikasi',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'status'      => 'string',
        'verified_at' => 'datetime',
    ];

    public function kelompokKkn()
    {
        return $this->belongsTo(KelompokKkn::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}