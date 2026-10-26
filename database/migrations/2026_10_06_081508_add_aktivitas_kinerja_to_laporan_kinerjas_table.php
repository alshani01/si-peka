<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_kinerjas', function (Blueprint $table) {
            $table->string('aktivitas_kinerja')->after('tanggal_tugas')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('laporan_kinerjas', function (Blueprint $table) {
            $table->dropColumn('aktivitas_kinerja');
        });
    }
};