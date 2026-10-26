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
        Schema::create('laporan_kinerjas', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke pegawai (PJLP) yang membuat laporan
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); 
            
            // Data utama laporan
            $table->date('tanggal_tugas');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->text('uraian_tugas');
            
            // Kolom untuk menyimpan nama file bukti kerja (Foto/PDF)
            $table->string('file_bukti'); 
            
            // Status validasi berjenjang (Pending -> Disetujui/Direvisi)
            $table->enum('status_validasi', ['pending', 'disetujui', 'direvisi'])->default('pending');
            
            // Catatan dari atasan jika laporan ditolak/perlu direvisi
            $table->text('catatan_atasan')->nullable(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_kinerjas');
    }
};
