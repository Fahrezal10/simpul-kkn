<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatMatching extends Model
{
    protected $table = 'riwayat_matching';

    protected $fillable = [
        'kelompok_kkn_id',
        'desa_id',
        'skor_tema',
        'skor_bidang',
        'skor_prioritas',
        'skor_kebutuhan',
        'skor_total',
        'flag_tumpang_tindih',
        'status',
        'dijalankan_oleh',
    ];

    protected $casts = [
        'skor_tema' => 'decimal:2',
        'skor_bidang' => 'decimal:2',
        'skor_prioritas' => 'decimal:2',
        'skor_kebutuhan' => 'decimal:2',
        'skor_total' => 'decimal:2',
        'flag_tumpang_tindih' => 'boolean',
        'status' => 'string',
    ];

    public function kelompokKkn()
    {
        return $this->belongsTo(KelompokKkn::class);
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'dijalankan_oleh');
    }
}