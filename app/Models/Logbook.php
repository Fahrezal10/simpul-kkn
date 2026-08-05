<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logbook extends Model
{
    protected $table = 'logbook';

    protected $fillable = [
        'kelompok_kkn_id',
        'mahasiswa_id',
        'tanggal',
        'deskripsi_kegiatan',
        'foto',
        'status',
        'catatan_dpl',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'status' => 'string',
        'approved_at' => 'datetime',
    ];

    public function kelompokKkn()
    {
        return $this->belongsTo(KelompokKkn::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}