<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Absensi;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DummyAbsensiKinenSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil spesifik user Kinen Purwanti
        $user = User::where('name', 'like', '%Kinen Purwanti%')->first();

        if (!$user) {
            $this->command->info('User Kinen Purwanti tidak ditemukan. Pastikan nama sudah benar.');
            return;
        }

        $alasanKlarifikasi = [
            'Mesin absensi sidik jari eror saat jam pulang.',
            'Menjalankan tugas lapangan mendadak sesuai perintah atasan.',
            'Kendala jaringan pada sistem mesin absensi wilayah.',
            'Urusan keluarga mendesak (telah lapor atasan).'
        ];

        $statusKlarifikasiList = ['pending', 'disetujui', 'ditolak'];

        // 2. Setup Periode Oktober 2026
        $tahun = 2026;
        $bulan = 10;
        $jumlahHari = Carbon::createFromDate($tahun, $bulan)->daysInMonth;

        // 3. Hapus data dummy lama khusus Kinen di bulan ini agar bersih
        Absensi::where('user_id', $user->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->delete();

        // 4. Looping pembuatan data harian
        for ($hari = 1; $hari <= $jumlahHari; $hari++) {
            $tanggal = Carbon::createFromDate($tahun, $bulan, $hari);

            // Lewati hari Sabtu dan Minggu
            if ($tanggal->isWeekend()) {
                continue;
            }

            $tanggalStr = $tanggal->format('Y-m-d');

            // Skenario probabilitas kehadiran
            $randVal = rand(1, 100);
            if ($randVal <= 60) {
                $statusKehadiran = 'hadir';
            } elseif ($randVal <= 72) {
                $statusKehadiran = 'terlambat';
            } elseif ($randVal <= 82) {
                $statusKehadiran = 'pulang_cepat';
            } elseif ($randVal <= 89) {
                $statusKehadiran = 'cuti';
            } elseif ($randVal <= 94) {
                $statusKehadiran = 'sakit';
            } elseif ($randVal <= 97) {
                $statusKehadiran = 'izin';
            } else {
                $statusKehadiran = 'alpa';
            }

            // Tentukan status yang butuh di-klarifikasi
            $needKlarifikasi = in_array($statusKehadiran, ['terlambat', 'pulang_cepat', 'alpa']);

            // Set Jam Masuk & Jam Pulang sesuai status
            $jamMasuk = match($statusKehadiran) {
                'hadir', 'pulang_cepat' => '07:25:00',
                'terlambat'             => '08:15:00',
                default                 => null, // cuti, sakit, izin, alpa kosong
            };

            $jamPulang = match($statusKehadiran) {
                'hadir', 'terlambat'  => '16:05:00',
                'pulang_cepat'        => '14:30:00',
                default               => null,
            };

            // 5. Insert ke tabel absensis
            Absensi::create([
                'user_id'             => $user->id,
                'tanggal'             => $tanggalStr,
                'jam_masuk'           => $jamMasuk,
                'jam_pulang'          => $jamPulang,
                'status_kehadiran'    => $statusKehadiran,
                'menit_terlambat'     => $statusKehadiran === 'terlambat' ? rand(15, 60) : 0,
                'menit_pulang_cepat'  => $statusKehadiran === 'pulang_cepat' ? rand(30, 90) : 0,
                'alasan_klarifikasi'  => $needKlarifikasi ? $alasanKlarifikasi[array_rand($alasanKlarifikasi)] : ($statusKehadiran === 'cuti' ? 'Cuti Tahunan Disetujui' : null),
                'file_bukti_absensi'  => null, // Dikosongkan untuk testing fitur upload nanti
                'status_klarifikasi'  => $needKlarifikasi ? $statusKlarifikasiList[array_rand($statusKlarifikasiList)] : 'tidak_perlu',
                'catatan_atasan'      => ($needKlarifikasi && rand(0,1)) ? 'Harap perhatikan jam kerja.' : null,
            ]);
        }

        $this->command->info('✅ Data Dummy Absensi Khusus "Kinen Purwanti" (Okt 2026) berhasil disuntikkan!');
    }
}