<?php

namespace App\Imports;

use App\Models\Absensi;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Membaca baris pertama sebagai header

class AbsensiImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Cari user (PJLP) berdasarkan id_pjlp yang ada di kolom Excel/CSV
        $user = User::where('id_pjlp', $row['id_pjlp'])->first();

        // 2. Jika user ditemukan, simpan/update data absensi menggunakan user_id
        if ($user) {
            return Absensi::updateOrCreate(
                [
                    'user_id' => $user->id,          // Foreign Key yang merujuk ke id di tabel users
                    'tanggal' => $row['tanggal'],    // Format tanggal: YYYY-MM-DD
                ],
                [
                    'jam_masuk' => $row['jam_masuk'] ?? null,
                    'jam_pulang' => $row['jam_pulang'] ?? null,
                    'status_kehadiran' => str_replace(' ', '_', strtolower($row['status_kehadiran'] ?? 'alpa')),
                    'status_klarifikasi' => 'tidak_perlu',
                    'menit_terlambat' => $row['menit_terlambat'] ?? 0,
                    'menit_pulang_cepat' => $row['menit_pulang_cepat'] ?? 0,
                ]
            );
        }

        // 3. Jika id_pjlp tidak ditemukan di tabel users, lewati baris tersebut
        return null;
    }
}