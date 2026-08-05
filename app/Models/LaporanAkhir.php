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
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function kelompokKkn()
    {
        return $this->belongsTo(KelompokKkn::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}