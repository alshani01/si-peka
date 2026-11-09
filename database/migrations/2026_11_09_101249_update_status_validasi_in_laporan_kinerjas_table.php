
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Perluas sementara opsi ENUM agar menerima 'ditolak'
        // tanpa menghapus 'direvisi' terlebih dahulu.
        DB::statement("ALTER TABLE laporan_kinerjas MODIFY COLUMN status_validasi ENUM('pending', 'disetujui', 'direvisi', 'ditolak') NOT NULL DEFAULT 'pending'");

        // 2. Migrasikan seluruh data lama dari 'direvisi' ke 'ditolak'.
        DB::table('laporan_kinerjas')
            ->where('status_validasi', 'direvisi')
            ->update(['status_validasi' => 'ditolak']);

        // 3. Kunci opsi ENUM final dengan menghapus 'direvisi'.
        DB::statement("ALTER TABLE laporan_kinerjas MODIFY COLUMN status_validasi ENUM('pending', 'disetujui', 'ditolak') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Kembalikan dukungan ENUM untuk 'direvisi',
        // tanpa mengubah data yang sudah berstatus 'ditolak'.
        DB::statement("ALTER TABLE laporan_kinerjas MODIFY COLUMN status_validasi ENUM('pending', 'disetujui', 'direvisi', 'ditolak') NOT NULL DEFAULT 'pending'");
    }
};
