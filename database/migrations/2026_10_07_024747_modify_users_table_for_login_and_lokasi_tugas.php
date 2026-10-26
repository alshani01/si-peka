<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 1. Hapus kolom email & email_verified_at
            $table->dropColumn(['email', 'email_verified_at']);
            
            // 2. Tambah kolom lokasi_tugas (menampung kecamatan/AEW Ragunan/Sudin)
            $table->string('lokasi_tugas')->nullable()->after('unit_kerja');
        });
        
        // Perintah $table->unique('id_pjlp'); telah dihapus dari sini.
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('lokasi_tugas');
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
        });
    }
};