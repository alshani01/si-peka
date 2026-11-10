<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'urutan_hirarki')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('urutan_hirarki')->nullable()->after('atasan_id');
            });
        }
    }

    public function down(): void
    {
        // Sengaja tidak menghapus kolom.
        // Kolom ini mungkin sudah berisi data penting di production.
    }
};
