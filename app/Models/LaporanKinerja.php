<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanKinerja extends Model
{
    use HasFactory;

    // Konstanta Status Validasi Laporan
    const STATUS_PENDING   = 'pending';
    const STATUS_DISETUJUI = 'disetujui';
    const STATUS_DITOLAK   = 'ditolak';

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

    /**
     * Scope Query: Laporan Pending (Menunggu Validasi)
     */
    public function scopePending($query)
    {
        return $query->where('status_validasi', self::STATUS_PENDING);
    }

    /**
     * Scope Query: Laporan Disetujui
     */
    public function scopeDisetujui($query)
    {
        return $query->where('status_validasi', self::STATUS_DISETUJUI);
    }

    /**
     * Scope Query: Laporan Ditolak (termasuk kompatibilitas data histori 'direvisi')
     */
    public function scopeDitolak($query)
    {
        return $query->whereIn('status_validasi', [self::STATUS_DITOLAK, 'direvisi']);
    }
}