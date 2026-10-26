<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LaporanKinerja;
use Carbon\Carbon;

class LaporanKinerjaDummySeeder extends Seeder
{
    public function run()
    {
        // 1. Cari user Kinen Purwanti berdasarkan id_pjlp atau name
        $user = User::where('id_pjlp', '198903122025212140')
                    ->orWhere('name', 'LIKE', '%Kinen Purwanti%')
                    ->first();

        if (!$user) {
            $this->command->error('Gagal: User Kinen Purwanti tidak ditemukan di database!');
            return;
        }

        // 2. Daftar Aktivitas (Khusus Petugas Pendamping Jakpreneur)
        $listAktivitas = [
            'Pendampingan pelaku usaha',
            'Kunjungan/pembinaan UMKM',
            'Monitoring perkembangan usaha',
            'Konsultasi usaha',
            'Pendataan pelaku usaha',
            'Pendampingan administrasi usaha',
            'Pendampingan pemasaran'
        ];

        // 3. Setup Waktu: Mulai dari 1 Oktober 2026
        $startDate = Carbon::create(2026, 10, 1);
        $totalData = 0;

        // 4. Looping 20 Hari Kerja (Setiap hari ada 2 aktivitas = Total 40 Laporan)
        for ($hari = 0; $hari < 20; $hari++) {
            $tanggalTugas = $startDate->copy()->addDays($hari);

            for ($item = 1; $item <= 2; $item++) {
                LaporanKinerja::create([
                    'user_id'           => $user->id,
                    'tanggal_tugas'     => $tanggalTugas->format('Y-m-d'),
                    'aktivitas_kinerja' => $listAktivitas[array_rand($listAktivitas)],
                    'uraian_tugas'      => "Data Dummy Uji Pagination ke-" . ($totalData + 1) . " - Melakukan pembinaan dan pendataan UMKM binaan Jakpreneur di wilayah tugas " . ($user->lokasi_tugas ?? 'Pancoran') . ".",
                    'file_bukti'        => 'bukti_kinerja/dummy.jpg', // Diisi path dummy agar lolos kriteria NOT NULL
                    'status_validasi'   => 'pending',
                    'catatan_atasan'    => null,
                    'created_at'        => $tanggalTugas->copy()->addHours(8 + ($item * 2)), 
                    'updated_at'        => $tanggalTugas->copy()->addHours(8 + ($item * 2)),
                ]);
                $totalData++;
            }
        }

        $this->command->info("Sukses! {$totalData} data dummy Laporan Kinerja berhasil dibuat untuk {$user->name} (Tersebar di 20 Tanggal).");
    }
}