<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;  // 🔥 TAMBAHKAN INI
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail  // 🔥 TAMBAHKAN "implements MustVerifyEmail"
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'username', 'password', 'role', 
        'id_dokter', 'poli', 'nik', 'tanggal_lahir'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Helper methods untuk cek role
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isDokter()
    {
        return $this->role === 'dokter';
    }

    public function isPerawat()
    {
        return $this->role === 'perawat';
    }

    public function isPasien()
    {
        return $this->role === 'pasien';
    }

    // Relasi
    public function rekamMedis()
    {
        return $this->hasMany(RekamMedis::class, 'dokter_id');
    }

    public function pasienDitangani()
    {
        return $this->hasMany(Pasien::class, 'dokter_id');
    }

    public function pasienDiinput()
    {
        return $this->hasMany(Pasien::class, 'perawat_id');
    }
}