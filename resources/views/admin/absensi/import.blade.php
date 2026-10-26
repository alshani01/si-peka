<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import Data Absensi Engine') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12 pb-28 md:pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-xl shadow-sm">
                    <p class="font-bold">Berhasil!</p>
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl shadow-sm">
                    <ul class="list-disc ml-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- TAB NAVIGASI DESKTOP (4 TAB) -->
            <div class="hidden sm:flex bg-white p-2 rounded-2xl shadow-sm border border-gray-100 space-x-1">
                <a href="{{ route('admin.pegawai.index') }}" class="flex-1 py-2.5 px-3 rounded-xl font-bold text-xs text-center text-gray-600 hover:bg-gray-50 transition">
                    Manajemen Pegawai
                </a>
                <a href="{{ route('admin.absensi.rekap') }}" class="flex-1 py-2.5 px-3 rounded-xl font-bold text-xs text-center bg-blue-600 text-white shadow-sm transition">
                    Absensi
                </a>
                <a href="{{ Route::has('admin.kinerja.rekap') ? route('admin.kinerja.rekap') : '#' }}" class="flex-1 py-2.5 px-3 rounded-xl font-bold text-xs text-center text-gray-600 hover:bg-gray-50 transition">
                    Rekap Kinerja
                </a>
                <a href="{{ route('admin.hari_libur.index') }}" class="flex-1 py-2.5 px-3 rounded-xl font-bold text-xs text-center text-gray-600 hover:bg-gray-50 transition">
                    Hari Libur Nasional
                </a>
            </div>

            <!-- FORM UPLOAD FILE ABSENSI -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 max-w-2xl mx-auto">
                <div class="mb-6 pb-4 border-b border-gray-100 text-center">
                    <div class="w-12 h-12 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-800 text-lg">Unggah Rekapitulasi Absensi</h3>
                    <p class="text-xs text-gray-500 mt-1">Unggah file (.xlsx / .csv) dari mesin absensi untuk memperbarui kehadiran pegawai secara otomatis.</p>
                </div>

                <form action="{{ route('admin.absensi.proses_import') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Pilih File Rekap Absensi</label>
                        <input type="file" name="file_excel" accept=".xlsx, .xls, .csv" required class="block w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-xl p-2 cursor-pointer focus:outline-none">
                        <p class="text-[11px] text-gray-400 mt-1">Format yang didukung: Excel (.xlsx / .xls) atau CSV. Ukuran maks: 2MB.</p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('admin.absensi.template') }}" class="inline-flex items-center text-xs font-bold text-blue-600 hover:underline">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Unduh Template File CSV
                        </a>
                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-green-600 text-white rounded-xl text-xs font-bold hover:bg-green-700 transition shadow">
                            Mulai Import Data
                        </button>
                    </div>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                    <p class="text-xs text-gray-500 mb-2">Mau melihat rekapitulasi kehadiran pegawai?</p>
                    <a href="{{ route('admin.absensi.rekap') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs font-bold rounded-xl transition">
                        Lihat Laporan Rekapitulasi Absensi &rarr;
                    </a>
                </div>
            </div>

            <!-- BOTTOM NAVIGATION BAR (MOBILE ADMIN) -->
            <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-xl z-[90] px-2 py-2 flex justify-around items-center">
                <a href="{{ route('admin.pegawai.index') }}" class="flex flex-col items-center text-gray-500 hover:text-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span class="text-[9px] mt-1">Pegawai</span>
                </a>
                <a href="{{ route('admin.absensi.rekap') }}" class="flex flex-col items-center text-blue-600 font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-[9px] mt-1">Absensi</span>
                </a>
                <a href="{{ Route::has('admin.kinerja.rekap') ? route('admin.kinerja.rekap') : '#' }}" class="flex flex-col items-center text-gray-500 hover:text-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-[9px] mt-1">Kinerja</span>
                </a>
                <a href="{{ route('admin.hari_libur.index') }}" class="flex flex-col items-center text-gray-500 hover:text-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-[9px] mt-1">Hari Libur</span>
                </a>
            </div>

        </div>
    </div>
</x-app-layout>