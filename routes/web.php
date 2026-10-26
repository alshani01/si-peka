<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LaporanKinerjaController;
use App\Http\Controllers\AtasanLaporanController;
use App\Http\Controllers\AtasanAbsensiController;
use App\Http\Controllers\AtasanCutiController;
use App\Http\Controllers\AtasanPenilaianController;
use App\Http\Controllers\AdminPegawaiController;
use App\Http\Controllers\AdminAbsensiImportController;
use App\Http\Controllers\AdminHariLiburController;
use App\Http\Controllers\AdminKinerjaController;
use App\Http\Controllers\AdminPenilaianController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Pengalihan cerdas setelah Login berdasarkan Role
Route::get('/dashboard', function () {
    $role = auth()->user()->role;
    
    if (in_array($role, ['admin', 'pimpinan'])) {
        return redirect()->route('admin.dashboard');
    } elseif ($role === 'atasan') {
        return redirect()->route('atasan.dashboard');
    } else {
        return redirect()->route('pjlp.dashboard');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// --- GRUP ROUTE ADMIN & PIMPINAN ---
Route::middleware(['auth', 'role:admin,pimpinan'])->prefix('admin')->name('admin.')->group(function () {
    
    // Redirect otomatis ke Manajemen Pegawai
    Route::get('/dashboard', function () {
        return redirect()->route('admin.pegawai.index');
    })->name('dashboard');

    // 1. Modul Manajemen Pegawai (Tampilan & PDF - Admin & Pimpinan)
    Route::get('/pegawai', [AdminPegawaiController::class, 'index'])->name('pegawai.index');
    Route::get('/pegawai/export-pdf', [AdminPegawaiController::class, 'exportPdf'])->name('pegawai.export_pdf'); 

    // 2. Rekap Absensi (Admin & Pimpinan)
    Route::get('/absensi/rekap', [AdminAbsensiImportController::class, 'rekap'])->name('absensi.rekap');
    Route::get('/absensi/rekap/pdf', [AdminAbsensiImportController::class, 'downloadPdf'])->name('absensi.pdf'); 

    // 3. Rekap Kinerja (Admin & Pimpinan)
    Route::get('/kinerja/rekap', [AdminKinerjaController::class, 'index'])->name('kinerja.rekap');
    Route::get('/kinerja/rekap/pdf', [AdminKinerjaController::class, 'exportPdf'])->name('kinerja.rekap.pdf');
    Route::get('/kinerja/detail/{id}', [AdminKinerjaController::class, 'detail'])->name('kinerja.detail');

    // 4. Rekap Penilaian Perilaku (Admin & Pimpinan)
    Route::get('/penilaian/rekap', [AdminPenilaianController::class, 'rekap'])->name('penilaian.rekap');
    Route::get('/penilaian/rekap/pdf', [AdminPenilaianController::class, 'exportPdf'])->name('penilaian.pdf');
    Route::get('/penilaian/trend/{id}', [AdminPenilaianController::class, 'trendTahunan'])->name('penilaian.trend');

    // --- KHUSUS ROLE ADMIN (MUTASI DATA / CRUD) ---
    Route::middleware(['role:admin'])->group(function () {
        // Kelola Pegawai
        Route::get('/pegawai/tambah', [AdminPegawaiController::class, 'create'])->name('pegawai.create');
        Route::post('/pegawai', [AdminPegawaiController::class, 'store'])->name('pegawai.store');
        Route::get('/pegawai/{id}/edit', [AdminPegawaiController::class, 'edit'])->name('pegawai.edit');
        Route::put('/pegawai/{id}', [AdminPegawaiController::class, 'update'])->name('pegawai.update');
        Route::delete('/pegawai/{id}', [AdminPegawaiController::class, 'destroy'])->name('pegawai.destroy');
        Route::post('/pegawai/{id}/reset-password', [AdminPegawaiController::class, 'resetPassword'])->name('pegawai.reset_password');

        // Import Absensi
        Route::get('/absensi/import', [AdminAbsensiImportController::class, 'index'])->name('absensi.import');
        Route::post('/absensi/import', [AdminAbsensiImportController::class, 'prosesImport'])->name('absensi.proses_import');
        Route::get('/absensi/download-template', [AdminAbsensiImportController::class, 'downloadTemplate'])->name('absensi.template');
        
        // Kelola Hari Libur
        Route::get('/hari-libur', [AdminHariLiburController::class, 'index'])->name('hari_libur.index');
        Route::post('/hari-libur', [AdminHariLiburController::class, 'store'])->name('hari_libur.store');
        Route::delete('/hari-libur/{id}', [AdminHariLiburController::class, 'destroy'])->name('hari_libur.destroy');

        // Hapus Laporan Kinerja (Tunggal & Massal/Bulk)
        Route::delete('/kinerja/laporan/bulk-delete', [AdminKinerjaController::class, 'bulkDestroy'])->name('kinerja.laporan.bulk_delete');
        Route::delete('/kinerja/laporan/{id}', [AdminKinerjaController::class, 'destroy'])->name('kinerja.laporan.destroy');

        // Hapus Penilaian Perilaku (Tunggal & Massal/Bulk)
        Route::delete('/penilaian/bulk-delete', [AdminPenilaianController::class, 'bulkDestroy'])->name('penilaian.bulk_delete');
        Route::delete('/penilaian/{id}', [AdminPenilaianController::class, 'destroy'])->name('penilaian.destroy');
    });
});

// --- GRUP ROUTE ATASAN ---
Route::middleware(['auth', 'role:atasan'])->prefix('atasan')->name('atasan.')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('atasan.laporan.index');
    })->name('dashboard');

    Route::get('/laporan', [AtasanLaporanController::class, 'index'])->name('laporan.index');
    Route::post('/validasi-laporan/{id}', [AtasanLaporanController::class, 'validasi'])->name('validasi');
    Route::post('/laporan/bulk-validasi', [AtasanLaporanController::class, 'bulkValidasi'])->name('laporan.bulkValidasi');

    Route::get('/absensi', [AtasanAbsensiController::class, 'index'])->name('absensi.index');
    Route::post('/validasi-absensi/{id}', [AtasanAbsensiController::class, 'validasi'])->name('validasi_absensi');
    Route::post('/absensi/bulk-validasi', [AtasanAbsensiController::class, 'bulkValidasi'])->name('absensi.bulk_validasi');
    
    Route::get('/cuti', [AtasanCutiController::class, 'index'])->name('cuti.index');
    Route::post('/cuti/bulk-validasi', [AtasanCutiController::class, 'bulkValidasi'])->name('cuti.bulk_validasi');
    Route::post('/cuti/{id}/validasi', [AtasanCutiController::class, 'validasi'])->name('cuti.validasi');

    Route::get('/penilaian', [AtasanPenilaianController::class, 'index'])->name('penilaian.index');
    Route::post('/penilaian/simpan-single', [AtasanPenilaianController::class, 'storeSingle'])->name('penilaian.store_single');
    Route::post('/penilaian/simpan-bulk', [AtasanPenilaianController::class, 'storeBulk'])->name('penilaian.store_bulk');
});

Route::middleware(['auth', 'role:atasan'])->get('/atasan/penilaian-alias', [AtasanPenilaianController::class, 'index'])->name('penilaian.index');

// --- GRUP ROUTE PJLP ---
Route::middleware(['auth', 'role:pjlp'])->group(function () {
    Route::get('/pjlp/dashboard', function () {
        $user = auth()->user();
        $bulanIni = date('m');
        $tahunIni = date('Y');

        $laporanBulanIni = \App\Models\LaporanKinerja::where('user_id', $user->id)
            ->whereMonth('tanggal_tugas', $bulanIni)
            ->whereYear('tanggal_tugas', $tahunIni)
            ->count();

        $laporanPending = \App\Models\LaporanKinerja::where('user_id', $user->id)
            ->where('status_validasi', 'pending')
            ->count();
            
        $totalHadir = \App\Models\Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->whereIn('status_kehadiran', ['hadir', 'tepat_waktu'])
            ->count();

        $totalTerlambat = \App\Models\Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->where('status_kehadiran', 'terlambat')
            ->count();

        $totalAlpa = \App\Models\Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->whereIn('status_kehadiran', ['alpa', 'tanpa_keterangan'])
            ->count();

        return view('pjlp.dashboard', compact(
            'user', 
            'laporanBulanIni', 
            'laporanPending', 
            'totalHadir', 
            'totalTerlambat', 
            'totalAlpa'
        ));
    })->name('pjlp.dashboard');
    
    Route::get('/pjlp/laporan', [LaporanKinerjaController::class, 'create'])->name('pjlp.laporan.create');
    Route::post('/pjlp/laporan', [LaporanKinerjaController::class, 'store'])->name('pjlp.laporan.store');
    Route::get('/pjlp/laporan/pdf', [LaporanKinerjaController::class, 'exportPdf'])->name('pjlp.laporan.export_pdf');

    Route::get('/pjlp/absensi', [\App\Http\Controllers\AbsensiController::class, 'index'])->name('pjlp.absensi.index');
    Route::post('/pjlp/absensi/{id}/klarifikasi', [\App\Http\Controllers\AbsensiController::class, 'klarifikasi'])->name('pjlp.absensi.klarifikasi');

    Route::get('/pjlp/cuti', [\App\Http\Controllers\CutiController::class, 'index'])->name('pjlp.cuti.index');
    Route::post('/pjlp/cuti', [\App\Http\Controllers\CutiController::class, 'store'])->name('pjlp.cuti.store');
});

// Profil Bawaan Breeze
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';