<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianPerilaku extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'penilai_id',
        'bulan',
        'tahun',
        'nilai_kehadiran',
        'nilai_tanggung_jawab',
        'nilai_kepatuhan',
        'rata_rata',
        'kategori',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function penilai()
    {
        return $this->belongsTo(User::class, 'penilai_id');
    }
}