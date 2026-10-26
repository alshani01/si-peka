<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LaporanKinerja;
use App\Models\Absensi;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DummyLaporanAbsensiSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil HANYA pengguna dengan role PJLP
        $pjlpUsers = User::where('role', 'pjlp')->get();

        if ($pjlpUsers->isEmpty()) {
            $this->command->info('Tidak ada user PJLP ditemukan.');
            return;
        }

        $uraianPekerjaan = [
            'Monitoring dan pendampingan UMKM Binaan dalam pendaftaran NIB dan Sertifikasi Halal.',
            'Melakukan inspeksi keamanan dan kelayakan sarana prasarana.',
            'Pembersihan dan pemeliharaan rutin area sarana umum.',
            'Piket jaga operasional komunikasi dan penerimaan laporan.',
            'Penginputan data rekapitulasi capaian kegiatan harian.',
        ];

        $alasanKlarifikasi = [
            'Mesin absensi sidik jari eror saat jam pulang.',
            'Menjalankan tugas lapangan mendadak sesuai perintah atasan.',
            'Kendala gangguan jaringan pada sistem mesin absensi.',
            'Urusan keluarga mendesak yang telah diinformasikan ke atasan.'
        ];

        $statusList = ['pending', 'disetujui', 'direvisi']; // Untuk Laporan Kinerja
        $statusKlarifikasiList = ['pending', 'disetujui', 'ditolak']; // Untuk Klarifikasi Absensi

        // Bulan & Tahun Target (Oktober 2026)
        $tahun = 2026;
        $bulan = 10;
        $jumlahHari = Carbon::createFromDate($tahun, $bulan)->daysInMonth;

        foreach ($pjlpUsers as $user) {
            
            // Hapus data dummy sebelumnya di bulan ini agar bersih saat dire-seed
            LaporanKinerja::where('user_id', $user->id)->whereYear('tanggal_tugas', $tahun)->whereMonth('tanggal_tugas', $bulan)->delete();
            Absensi::where('user_id', $user->id)->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan)->delete();

            for ($hari = 1; $hari <= $jumlahHari; $hari++) {
                $tanggal = Carbon::createFromDate($tahun, $bulan, $hari);

                // Lewati hari Sabtu dan Minggu
                if ($tanggal->isWeekend()) {
                    continue;
                }

                $tanggalStr = $tanggal->format('Y-m-d');

                // Tentukan variasi skenario absensi (hadir, terlambat, pulang_cepat, cuti, sakit, izin, alpa)
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

                // --- 1. SEED LAPORAN KINERJA (Kecuali saat Alpa, Sakit, Izin) ---
                if (!in_array($statusKehadiran, ['alpa', 'sakit', 'izin'])) {
                    LaporanKinerja::create([
                        'user_id'         => $user->id,
                        'tanggal_tugas'   => $tanggalStr,
                        'uraian_tugas'    => $statusKehadiran === 'cuti' ? 'CUTI TAHUNAN (Disetujui Atasan)' : $uraianPekerjaan[array_rand($uraianPekerjaan)],
                        'status_validasi' => $statusKehadiran === 'cuti' ? 'disetujui' : $statusList[array_rand($statusList)],
                        'catatan_atasan'  => rand(0, 1) ? 'Pekerjaan dilaksanakan dengan baik.' : null,
                        'file_bukti'      => 'bukti_kinerja/dummy_bukti.jpg',
                    ]);
                }

                // --- 2. SEED REKAP ABSENSI ---
                $needKlarifikasi = in_array($statusKehadiran, ['terlambat', 'pulang_cepat', 'alpa']);

                $jamMasuk = match($statusKehadiran) {
                    'hadir', 'pulang_cepat' => '07:25:00',
                    'terlambat'             => '08:15:00',
                    default                 => null,
                };

                $jamPulang = match($statusKehadiran) {
                    'hadir', 'terlambat' => '16:05:00',
                    'pulang_cepat'        => '14:30:00',
                    default              => null,
                };

                Absensi::create([
                    'user_id'             => $user->id,
                    'tanggal'             => $tanggalStr,
                    'jam_masuk'           => $jamMasuk,
                    'jam_pulang'          => $jamPulang,
                    'status_kehadiran'    => $statusKehadiran,
                    'menit_terlambat'     => $statusKehadiran === 'terlambat' ? 45 : 0,
                    'menit_pulang_cepat'  => $statusKehadiran === 'pulang_cepat' ? 90 : 0,
                    'alasan_klarifikasi'  => $needKlarifikasi ? $alasanKlarifikasi[array_rand($alasanKlarifikasi)] : ($statusKehadiran === 'cuti' ? 'Cuti Tahunan' : null),
                    'status_klarifikasi'  => $needKlarifikasi ? $statusKlarifikasiList[array_rand($statusKlarifikasiList)] : 'tidak_perlu',
                ]);
            }
        }

        $this->command->info('Data Laporan & Absensi Oktober 2026 berhasil disuntikkan untuk SELURUH PJLP!');
    }
}