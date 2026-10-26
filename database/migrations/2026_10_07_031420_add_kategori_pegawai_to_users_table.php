<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Menambahkan kolom kategori_pegawai dengan pilihan: PNS, P3K, PJLP
            $table->enum('kategori_pegawai', ['PNS', 'P3K', 'PJLP'])->default('PJLP')->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('kategori_pegawai');
        });
    }
};