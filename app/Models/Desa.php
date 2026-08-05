<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Desa extends Model
{
    use SoftDeletes;

    protected $table = 'desa';

    protected $fillable = [
        'user_id',
        'kecamatan_id',
        'nama_desa',
        'kode_wilayah',
        'jumlah_penduduk',
        'luas_wilayah',
        'latitude',
        'longitude',
        'profil_umum',
    ];

    protected $casts = [
        'jumlah_penduduk' => 'integer',
        'luas_wilayah' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function potensi()
    {
        return $this->hasMany(DesaPotensi::class);
    }

    public function permasalahan()
    {
        return $this->hasMany(DesaPermasalahan::class);
    }

    public function kebutuhan()
    {
        return $this->hasMany(DesaKebutuhan::class);
    }

    public function kelompokKkn()
    {
        return $this->hasMany(KelompokKkn::class);
    }
}