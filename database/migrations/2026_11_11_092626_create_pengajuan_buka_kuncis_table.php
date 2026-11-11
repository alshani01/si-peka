<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Utama Pengajuan Buka Kunci
        Schema::create('pengajuan_buka_kunci', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal_terlewat');
            $table->text('alasan');
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            
            // Reff Admin yang ACC/Tolak
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamp('expired_at')->nullable(); // Otomatis diset ke Akhir Bulan Berjalan (23:59:59)
            
            $table->timestamps();
        });

        // 2. Tambah Kolom Nullable pada Tabel laporan_kinerjas
        Schema::table('laporan_kinerjas', function (Blueprint $table) {
            $table->foreignId('pengajuan_buka_kunci_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('pengajuan_buka_kunci')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_kinerjas', function (Blueprint $table) {
            $table->dropForeign(['pengajuan_buka_kunci_id']);
            $table->dropColumn('pengajuan_buka_kunci_id');
        });
        Schema::dropIfExists('pengajuan_buka_kunci');
    }
};