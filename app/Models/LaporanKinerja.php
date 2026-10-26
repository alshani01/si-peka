<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanKinerja extends Model
{
    use HasFactory;

    // Mengizinkan penyimpanan data secara massal ke kolom-kolom ini
    protected $fillable = [
        'user_id',
        'tanggal_tugas',
        'aktivitas_kinerja',
        'uraian_tugas',
        'file_bukti',
        'status_validasi',
        'catatan_atasan'
    ];

    // Relasi: Setiap laporan dimiliki oleh 1 User (PJLP)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}