<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Mengubah pilihan ENUM untuk memasukkan 'pulang_cepat'
        DB::statement("ALTER TABLE absensis MODIFY COLUMN status_kehadiran ENUM('hadir', 'terlambat', 'alpa', 'izin', 'sakit', 'pulang_cepat') DEFAULT 'alpa'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE absensis MODIFY COLUMN status_kehadiran ENUM('hadir', 'terlambat', 'alpa', 'izin', 'sakit') DEFAULT 'alpa'");
    }
};