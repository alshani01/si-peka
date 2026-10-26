<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'lokasi_tugas',
        'password',
        'role',
        'id_pjlp',
        'kategori_pegawai',
        'jenis_pekerjaan',
        'unit_kerja',
        'atasan_id',
        'sisa_cuti',
        'status',
        'foto_profil',
        'urutan_hirarki', // DITAMBAHKAN
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
            'password' => 'hashed',
        ];
    }

    public function penilaianPerilaku()
    {
        return $this->hasMany(PenilaianPerilaku::class, 'user_id');
    }

    public function laporanKinerjas()
    {
        return $this->hasMany(LaporanKinerja::class);
    }

    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    public function bawahan()
    {
        return $this->hasMany(User::class, 'atasan_id');
    }

    public function absensis() 
    { 
        return $this->hasMany(Absensi::class); 
    }

    public function cutis()
    {
        return $this->hasMany(Cuti::class);
    }
}