<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_log';

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'aksi',
        'deskripsi',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}