<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status_kehadiran',
        'alasan_klarifikasi',
        'file_bukti_absensi',
        'status_klarifikasi',
        'menit_terlambat',
        'menit_pulang_cepat',
        'catatan_atasan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}