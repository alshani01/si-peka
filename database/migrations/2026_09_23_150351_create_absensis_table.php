<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Data utama dari mesin absen (yang nantinya diimpor Admin)
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            
            // Status Kehadiran (Termasuk opsi 'pulang_cepat' dan 'cuti')
            $table->enum('status_kehadiran', [
                'hadir', 
                'terlambat', 
                'alpa', 
                'izin', 
                'sakit', 
                'pulang_cepat', 
                'cuti'
            ])->default('alpa');
            
            // Hitungan durasi keterlambatan & pulang cepat (dalam menit)
            $table->integer('menit_terlambat')->default(0);
            $table->integer('menit_pulang_cepat')->default(0);
            
            // Kolom untuk fitur Klarifikasi oleh PJLP / Atasan
            $table->text('alasan_klarifikasi')->nullable();
            $table->string('file_bukti_absensi')->nullable(); // Untuk upload surat sakit/bukti pendukung
            $table->enum('status_klarifikasi', ['tidak_perlu', 'pending', 'disetujui', 'ditolak'])->default('tidak_perlu');
            $table->text('catatan_atasan')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};