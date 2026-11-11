<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengajuanBukaKunci extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_buka_kunci';

    protected $fillable = [
        'user_id',
        'tanggal_terlewat',
        'alasan',
        'status',
        'approved_by',
        'approved_at',
        'catatan_admin',
        'expired_at',
    ];

    protected $casts = [
        'tanggal_terlewat' => 'date',
        'approved_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    /**
     * Relasi ke Pegawai (PJLP) yang mengajukan buka kunci
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Admin yang menyetujui / menolak pengajuan
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relasi ke Laporan Kinerja yang diisi berdasarkan izin pengajuan ini
     */
    public function laporanKinerja(): HasMany
    {
        return $this->hasMany(LaporanKinerja::class, 'pengajuan_buka_kunci_id');
    }
}