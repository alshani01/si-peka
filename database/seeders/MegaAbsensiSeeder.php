<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Absensi;
use Carbon\Carbon;

class MegaAbsensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Cari User Mega Ayu Chartika Sary (atau buat baru jika tidak ditemukan)
        $mega = User::firstOrCreate(
            ['name' => 'Mega Ayu Chartika Sary'],
            [
                'email' => 'mega.80356859@example.com',
                'password' => bcrypt('password'), // default password
                'role' => 'pjlp',
                'jenis_pekerjaan' => 'Petugas Pendamping Jakpreneur', // JAKPRENEUR Pancoran
                // Asumsi field NIK/NIP ada, jika tidak ada hapus baris di bawah ini
                // 'nik' => '80356859', 
                'atasan_id' => 8, // PENTING: Ganti dengan ID Atasan yang Anda gunakan untuk login saat ini
            ]
        );

        // 2. Tentukan bulan saat ini (Oktober 2026 berdasarkan waktu saat ini)
        $bulanIni = Carbon::now('Asia/Jakarta')->format('Y-m');

        // 3. Siapkan 3 Data Dummy Absensi dengan Status "Pending"
        $dataAbsensi = [
            [
                'user_id' => $mega->id,
                'tanggal' => $bulanIni . '-02', // Tanggal 2 bulan ini
                'status_kehadiran' => 'sakit',
                'alasan_klarifikasi' => 'Mohon izin Bapak/Ibu, saya terkena demam berdarah dan dirawat di RSUD. Surat keterangan dokter menyusul saat saya masuk.',
                'status_klarifikasi' => 'pending',
            ],
            [
                'user_id' => $mega->id,
                'tanggal' => $bulanIni . '-05', // Tanggal 5 bulan ini
                'status_kehadiran' => 'terlambat',
                'alasan_klarifikasi' => 'Ban motor bocor di jalan raya Pasar Minggu saat perjalanan menuju kantor, sehingga saya terlambat 45 menit.',
                'status_klarifikasi' => 'pending',
            ],
            [
                'user_id' => $mega->id,
                'tanggal' => $bulanIni . '-08', // Tanggal 8 bulan ini
                'status_kehadiran' => 'alpa',
                'alasan_klarifikasi' => 'Lupa tap mesin absensi sidik jari saat jam masuk dan pulang karena ada instruksi giat mendadak langsung ke lapangan bersama tim Kecamatan.',
                'status_klarifikasi' => 'pending',
            ],
        ];

        // 4. Masukkan data ke tabel absensis
        foreach ($dataAbsensi as $data) {
            Absensi::updateOrCreate(
                [
                    'user_id' => $data['user_id'],
                    'tanggal' => $data['tanggal']
                ],
                $data
            );
        }

        $this->command->info('Data dummy absensi untuk Mega Ayu Chartika Sary berhasil dibuat!');
    }
}