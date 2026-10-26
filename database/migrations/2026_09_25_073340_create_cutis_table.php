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
        Schema::create('cutis', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke pegawai (PJLP) yang mengajukan cuti
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); 
            
            // Data pengajuan cuti
            $table->enum('jenis_cuti', ['Tahunan', 'Sakit', 'Melahirkan', 'Alasan Penting']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('alasan');
            
            // Bukti pendukung (misal: surat keterangan dokter untuk cuti sakit)
            $table->string('file_bukti')->nullable(); 
            
            // Status persetujuan atasan
            $table->enum('status_validasi', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->text('catatan_atasan')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cutis');
    }
};
