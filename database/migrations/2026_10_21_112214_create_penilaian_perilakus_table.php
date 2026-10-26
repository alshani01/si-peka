<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian_perilakus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('penilai_id')->constrained('users')->onDelete('cascade');
            $table->integer('bulan');
            $table->integer('tahun');
            $table->integer('nilai_kehadiran')->default(0);
            $table->integer('nilai_tanggung_jawab')->default(0);
            $table->integer('nilai_kepatuhan')->default(0);
            $table->decimal('rata_rata', 5, 2)->default(0);
            $table->string('kategori')->default('Kurang');
            $table->timestamps();

            // Mencegah penilaian ganda untuk pegawai yang sama dalam bulan & tahun yang sama
            $table->unique(['user_id', 'bulan', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_perilakus');
    }
};