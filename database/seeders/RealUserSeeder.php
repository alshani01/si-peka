<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RealUserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('123456');

        // --- 1. DATA ADMIN (PNS) ---
        $dataAdmin = [
            ['name' => 'Ridho Sosro Yudyantoro, S.Pt., M.E.', 'id_pjlp' => '123744', 'jenis' => 'Kepala Suku Dinas KPKP', 'unit' => 'Suku Dinas KPKP Jakarta Selatan', 'lokasi' => 'Sudin KPKP Jakarta Selatan'],
            ['name' => 'Surtiati', 'id_pjlp' => '181601', 'jenis' => 'Kepala Subbagian Tata Usaha', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'Sudin KPKP Jakarta Selatan'],
            ['name' => 'Ida Masni Wati Manik', 'id_pjlp' => '183001', 'jenis' => 'Kepala Seksi Ketahanan Pangan dan Pertanian', 'unit' => 'Seksi Ketahanan Pangan dan Pertanian', 'lokasi' => 'Sudin KPKP Jakarta Selatan'],
        ];

        foreach ($dataAdmin as $admin) {
            User::updateOrCreate(
                ['id_pjlp' => $admin['id_pjlp']],
                [
                    'name'             => $admin['name'],
                    'password'         => $defaultPassword,
                    'role'             => 'admin',
                    'kategori_pegawai' => 'PNS',
                    'jenis_pekerjaan'  => $admin['jenis'],
                    'unit_kerja'       => $admin['unit'],
                    'lokasi_tugas'     => $admin['lokasi'],
                    'sisa_cuti'        => 12,
                    'status'           => 'aktif',
                ]
            );
        }

        // --- 2. DATA ATASAN (PNS) ---
        $dataAtasan = [
            ['name' => 'Ian Sebastian', 'id_pjlp' => '199278', 'jenis' => 'PPTK Subbagian Tata Usaha', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'Sudin KPKP Jakarta Selatan', 'key' => 'TU'],
            ['name' => 'Irawati Harry Artharini', 'id_pjlp' => '177782', 'jenis' => 'PPTK Seksi Peternakan', 'unit' => 'Seksi Peternakan dan Kesehatan Hewan', 'lokasi' => 'Sudin KPKP Jakarta Selatan', 'key' => 'Peternakan'],
            ['name' => 'Meilani Indarwati', 'id_pjlp' => '193532', 'jenis' => 'Kasatpel Kebayoran Baru', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Kebayoran Baru', 'key' => 'Kebayoran Baru'],
            ['name' => 'Alhamdania', 'id_pjlp' => '177633', 'jenis' => 'Kasatpel Pasar Minggu', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Pasar Minggu', 'key' => 'Pasar Minggu'],
            ['name' => 'Anton', 'id_pjlp' => '179712', 'jenis' => 'Kasatpel Pancoran', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Pancoran', 'key' => 'Pancoran'],
            ['name' => 'Tomy Suprayitno', 'id_pjlp' => '186502', 'jenis' => 'Kasatpel Jagakarsa', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Jagakarsa', 'key' => 'Jagakarsa'],
            ['name' => 'Hegar Budi Santoso', 'id_pjlp' => '112777', 'jenis' => 'Plh Kasatpel Cilandak', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Cilandak', 'key' => 'Cilandak'],
            ['name' => 'Hegar Budi Santoso', 'id_pjlp' => '193522', 'jenis' => 'Kasatpel Kebayoran Lama', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Kebayoran Lama', 'key' => 'Kebayoran Lama'],
            ['name' => 'Irma Malini', 'id_pjlp' => '193523', 'jenis' => 'Kasatpel Tebet', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Tebet', 'key' => 'Tebet'],
            ['name' => 'Ernawati', 'id_pjlp' => '120197', 'jenis' => 'Kasatpel Mampang Prapatan', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Mampang Prapatan', 'key' => 'Mampang Prapatan'],
            ['name' => 'Lidya Erika C.G', 'id_pjlp' => '123717', 'jenis' => 'Kasatpel Setiabudi', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Setiabudi', 'key' => 'Setiabudi'],
            ['name' => 'Hairul Ihsan', 'id_pjlp' => '193520', 'jenis' => 'Kasatpel Pesanggrahan', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Pesanggrahan', 'key' => 'Pesanggrahan'],
        ];

        $atasanMap = [];
        foreach ($dataAtasan as $atasan) {
            $user = User::updateOrCreate(
                ['id_pjlp' => $atasan['id_pjlp']],
                [
                    'name'             => $atasan['name'],
                    'password'         => $defaultPassword,
                    'role'             => 'atasan',
                    'kategori_pegawai' => 'PNS',
                    'jenis_pekerjaan'  => $atasan['jenis'],
                    'unit_kerja'       => $atasan['unit'],
                    'lokasi_tugas'     => $atasan['lokasi'],
                    'sisa_cuti'        => 12,
                    'status'           => 'aktif',
                ]
            );

            $atasanMap[$atasan['key']] = $user->id;
        }

        // --- 3. DATA PJLP & P3K PARUH WAKTU ---
        $dataPjlp = [
            // JAKPRENEUR (Diubah menjadi 'Petugas Pendamping Jakpreneur')
            ['name' => 'Rifha Asti Hardinawanti', 'id_pjlp' => '80320143', 'kategori' => 'PJLP', 'jenis' => 'Petugas Pendamping Jakpreneur', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Kebayoran Baru', 'atasan_key' => 'Kebayoran Baru'],
            ['name' => 'Irsya hadi', 'id_pjlp' => '198309072025211134', 'kategori' => 'P3K', 'jenis' => 'Petugas Pendamping Jakpreneur', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Kebayoran Baru', 'atasan_key' => 'Kebayoran Baru'],
            ['name' => 'Nadya Rahmah', 'id_pjlp' => '80356887', 'kategori' => 'PJLP', 'jenis' => 'Petugas Pendamping Jakpreneur', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Jagakarsa', 'atasan_key' => 'Jagakarsa'],
            ['name' => 'Arman Gaffar', 'id_pjlp' => '80280604', 'kategori' => 'PJLP', 'jenis' => 'Petugas Pendamping Jakpreneur', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Jagakarsa', 'atasan_key' => 'Jagakarsa'],
            ['name' => 'Russmawati', 'id_pjlp' => '80276367', 'kategori' => 'PJLP', 'jenis' => 'Petugas Pendamping Jakpreneur', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Pasar Minggu', 'atasan_key' => 'Pasar Minggu'],
            ['name' => 'Mega Ayu Chartika Sary', 'id_pjlp' => '80356859', 'kategori' => 'PJLP', 'jenis' => 'Petugas Pendamping Jakpreneur', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Pancoran', 'atasan_key' => 'Pancoran'],
            ['name' => 'Kinen Purwanti', 'id_pjlp' => '198903122025212140', 'kategori' => 'P3K', 'jenis' => 'Petugas Pendamping Jakpreneur', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Pancoran', 'atasan_key' => 'Pancoran'],
            ['name' => 'Irfan Dadi', 'id_pjlp' => '80111546', 'kategori' => 'PJLP', 'jenis' => 'Petugas Pendamping Jakpreneur', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Cilandak', 'atasan_key' => 'Cilandak'],
            ['name' => 'Ahmad Rezal Plafi', 'id_pjlp' => '80275875', 'kategori' => 'PJLP', 'jenis' => 'Petugas Pendamping Jakpreneur', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Tebet', 'atasan_key' => 'Tebet'],
            ['name' => 'Irmawaty Latief', 'id_pjlp' => '80275792', 'kategori' => 'PJLP', 'jenis' => 'Petugas Pendamping Jakpreneur', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Mampang Prapatan', 'atasan_key' => 'Mampang Prapatan'],
            ['name' => 'Epi Nurhayati', 'id_pjlp' => '197804172025212040', 'kategori' => 'P3K', 'jenis' => 'Petugas Pendamping Jakpreneur', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Pesanggrahan', 'atasan_key' => 'Pesanggrahan'],
            ['name' => 'Jaminatun', 'id_pjlp' => '197201052025212032', 'kategori' => 'P3K', 'jenis' => 'Petugas Pendamping Jakpreneur', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Setiabudi', 'atasan_key' => 'Setiabudi'],
            ['name' => 'Romi Kistaraga', 'id_pjlp' => '198908062025211166', 'kategori' => 'P3K', 'jenis' => 'Petugas Pendamping Jakpreneur', 'unit' => 'Satuan Pelaksana Kecamatan', 'lokasi' => 'Kebayoran Lama', 'atasan_key' => 'Kebayoran Lama'],

            // KEBERSIHAN
            ['name' => 'Agus Salim', 'id_pjlp' => '80039010', 'kategori' => 'PJLP', 'jenis' => 'Petugas Kebersihan', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'Sudin KPKP Jakarta Selatan', 'atasan_key' => 'TU'],
            ['name' => 'Putut Wiratno', 'id_pjlp' => '196910302025211022', 'kategori' => 'P3K', 'jenis' => 'Petugas Kebersihan', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'Sudin KPKP Jakarta Selatan', 'atasan_key' => 'TU'],

            // KEBUN AEW RAGUNAN
            ['name' => 'Banih', 'id_pjlp' => '80039554', 'kategori' => 'PJLP', 'jenis' => 'Petugas Kebun', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'AEW Ragunan', 'atasan_key' => 'TU'],
            ['name' => 'Didi', 'id_pjlp' => '80039039', 'kategori' => 'PJLP', 'jenis' => 'Petugas Kebun', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'AEW Ragunan', 'atasan_key' => 'TU'],
            ['name' => 'Hamdani', 'id_pjlp' => '80039528', 'kategori' => 'PJLP', 'jenis' => 'Petugas Kebun', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'AEW Ragunan', 'atasan_key' => 'TU'],
            ['name' => 'Iqbal Juliyansyah', 'id_pjlp' => '80039541', 'kategori' => 'PJLP', 'jenis' => 'Petugas Kebun', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'AEW Ragunan', 'atasan_key' => 'TU'],
            ['name' => 'Fran Tripria Cayadi', 'id_pjlp' => '80605150', 'kategori' => 'PJLP', 'jenis' => 'Petugas Kebun', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'AEW Ragunan', 'atasan_key' => 'TU'],
            ['name' => 'Mudin', 'id_pjlp' => '80665444', 'kategori' => 'PJLP', 'jenis' => 'Petugas Kebun', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'AEW Ragunan', 'atasan_key' => 'TU'],
            ['name' => 'Suwandi', 'id_pjlp' => '80038989', 'kategori' => 'PJLP', 'jenis' => 'Petugas Kebun', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'AEW Ragunan', 'atasan_key' => 'TU'],
            ['name' => 'Yadi Al Wakhid', 'id_pjlp' => '80667092', 'kategori' => 'PJLP', 'jenis' => 'Petugas Kebun', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'AEW Ragunan', 'atasan_key' => 'TU'],
            ['name' => 'Rifki Ananta', 'id_pjlp' => '198401092025211094', 'kategori' => 'P3K', 'jenis' => 'Petugas Kebun', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'AEW Ragunan', 'atasan_key' => 'TU'],

            // KEAMANAN AEW RAGUNAN
            ['name' => 'Yanto', 'id_pjlp' => '80275400', 'kategori' => 'PJLP', 'jenis' => 'Petugas Keamanan', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'AEW Ragunan', 'atasan_key' => 'TU'],
            ['name' => 'Jemi', 'id_pjlp' => '80275430', 'kategori' => 'PJLP', 'jenis' => 'Petugas Keamanan', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'AEW Ragunan', 'atasan_key' => 'TU'],
            ['name' => 'Haris Efendi', 'id_pjlp' => '80336577', 'kategori' => 'PJLP', 'jenis' => 'Petugas Keamanan', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'AEW Ragunan', 'atasan_key' => 'TU'],
            ['name' => 'Agus Abdillah Ari', 'id_pjlp' => '80670980', 'kategori' => 'PJLP', 'jenis' => 'Petugas Keamanan', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'AEW Ragunan', 'atasan_key' => 'TU'],
            ['name' => 'Muhammad Rohim', 'id_pjlp' => '80270303', 'kategori' => 'PJLP', 'jenis' => 'Petugas Keamanan', 'unit' => 'Subbagian Tata Usaha', 'lokasi' => 'AEW Ragunan', 'atasan_key' => 'TU'],

            // TEKNIS KESEHATAN HEWAN
            ['name' => 'drh. Bagus Parmuning', 'id_pjlp' => '80018044', 'kategori' => 'PJLP', 'jenis' => 'Petugas Teknis Kesehatan Hewan', 'unit' => 'Seksi Peternakan dan Kesehatan Hewan', 'lokasi' => 'Sudin KPKP Jakarta Selatan', 'atasan_key' => 'Peternakan'],
            ['name' => 'Samsul Aripin', 'id_pjlp' => '80041433', 'kategori' => 'PJLP', 'jenis' => 'Petugas Teknis Kesehatan Hewan', 'unit' => 'Seksi Peternakan dan Kesehatan Hewan', 'lokasi' => 'Sudin KPKP Jakarta Selatan', 'atasan_key' => 'Peternakan'],
        ];

        foreach ($dataPjlp as $pjlp) {
            User::updateOrCreate(
                ['id_pjlp' => $pjlp['id_pjlp']],
                [
                    'name'             => $pjlp['name'],
                    'password'         => $defaultPassword,
                    'role'             => 'pjlp',
                    'kategori_pegawai' => $pjlp['kategori'],
                    'id_pjlp'          => $pjlp['id_pjlp'],
                    'jenis_pekerjaan'  => $pjlp['jenis'],
                    'unit_kerja'       => $pjlp['unit'],
                    'lokasi_tugas'     => $pjlp['lokasi'],
                    'sisa_cuti'        => 12,
                    'atasan_id'        => $atasanMap[$pjlp['atasan_key']] ?? null,
                    'status'           => 'aktif',
                ]
            );
        }

        $this->command->info('Seeder RealUser berhasil di-update!');
    }
}