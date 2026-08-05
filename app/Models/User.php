<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'nama',
        'email',
        'password',
        'status_aktif',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'status_aktif' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /** Akun login multi-role dimiliki oleh 1 role. */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /* Relasi one-to-one ke data spesifik per role (nullable). */
    public function perguruanTinggi()  { return $this->hasOne(PerguruanTinggi::class); }
    public function dosen()            { return $this->hasOne(Dosen::class); }
    public function mahasiswa()        { return $this->hasOne(Mahasiswa::class); }
    public function perangkatDaerah()  { return $this->hasOne(PerangkatDaerah::class); }
    public function kecamatan()        { return $this->hasOne(Kecamatan::class); }
    public function desa()             { return $this->hasOne(Desa::class); }

    /** Cek apakah user memegang role tertentu (nama_role). */
    public function hasRole(string $role): bool
    {
        return $this->role?->nama_role === $role;
    }

    /** Slug role untuk routing/redirect (contoh: "perguruan_tinggi" → "perguruan-tinggi"). */
    public function roleSlug(): string
    {
        return str_replace('_', '-', (string) $this->role?->nama_role);
    }
}