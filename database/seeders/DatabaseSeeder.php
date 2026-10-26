<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@sipadu.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        // 2. Akun Atasan (Kasi)
        $atasan = User::create([
            'name' => 'Bapak Kasi',
            'email' => 'kasi@sipadu.com',
            'password' => Hash::make('password'),
            'role' => 'atasan',
            'status' => 'aktif',
        ]);

        // 3. Akun PJLP (Bawahan Bapak Kasi)
        User::create([
            'name' => 'MACHMUDI, S.AP',
            'email' => 'pjlp@sipadu.com',
            'password' => Hash::make('password'),
            'role' => 'pjlp',
            'id_pjlp' => '80128055',
            'jenis_pekerjaan' => 'Petugas Keamanan',
            'status' => 'aktif',
            'atasan_id' => $atasan->id,
        ]);
    }
}