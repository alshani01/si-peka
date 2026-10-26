<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Beranda') }}
        </h2>
    </x-slot>

    <!-- Ruang bawah lega (pb-28) agar tidak tertutup Bottom Navigation Bar di HP -->
    <div class="py-4 sm:py-6 md:py-12 pb-28 md:pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- TAB NAVIGASI DESKTOP PJLP -->
            <div class="hidden sm:flex bg-white p-2 rounded-2xl shadow-sm border border-gray-100 space-x-1">
                <a href="{{ route('pjlp.dashboard') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center bg-blue-600 text-white shadow-sm transition">
                    Dashboard
                </a>
                <a href="{{ route('pjlp.laporan.create') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center text-gray-600 hover:bg-gray-50 transition">
                    Laporan Kinerja
                </a>
                <a href="{{ route('pjlp.absensi.index') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center text-gray-600 hover:bg-gray-50 transition">
                    Rekap Absensi
                </a>
                <a href="{{ route('pjlp.cuti.index') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center text-gray-600 hover:bg-gray-50 transition">
                    Pengajuan Cuti
                </a>
            </div>

            <!-- PROFIL SINGKAT & SAPAAN (KARTU UTAMA) -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl shadow-lg p-5 sm:p-6 text-white flex items-center justify-between">
                <div class="min-w-0 flex-1 mr-3">
                    <h3 class="text-lg sm:text-xl md:text-2xl font-bold truncate">Halo, {{ $user->name }}! 👋</h3>
                    
                    <!-- DOKUMEN IDENTITAS (ID PJLP / NRK / NIP) -->
                    <p class="text-xs sm:text-sm font-medium text-blue-100 mt-0.5 opacity-90 truncate">
                         <span class="font-bold text-white">{{ $user->id_pjlp ?? $user->nrk ?? $user->nip ?? '-' }}</span>
                    </p>

                    <!-- KATEGORI, JENIS PEKERJAAN & LOKASI TUGAS -->
                    <p class="text-xs sm:text-sm opacity-90 mt-1 flex flex-wrap items-center gap-1">
                        <span class="inline-block px-2 py-0.5 bg-white/20 backdrop-blur-sm rounded-md font-semibold text-white uppercase text-[10px]">
                            {{ Auth::user()->kategori_pegawai ?? 'PJLP' }}
                        </span>
                        <span class="truncate">{{ Auth::user()->jenis_pekerjaan }} | {{ Auth::user()->lokasi_tugas ?? Auth::user()->unit_kerja }}</span>
                    </p>
                </div>

                <!-- FOTO PROFIL PEGAWAI -->
                <div class="flex-shrink-0">
                    @if(isset($user->foto_profil) && $user->foto_profil)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($user->foto_profil) }}" 
                             alt="{{ $user->name }}" 
                             class="h-12 w-12 sm:h-16 sm:w-16 rounded-full object-cover shadow-inner border-2 border-white/80">
                    @else
                        <!-- Fallback jika foto profil belum diunggah -->
                        <div class="h-12 w-12 sm:h-16 sm:w-16 rounded-full bg-white/20 backdrop-blur-md text-white flex items-center justify-center font-bold text-xl sm:text-2xl shadow-inner border-2 border-white/40">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- GRID STATISTIK KINERJA & CUTI (4 KOLOM) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                
                <!-- Card 1: Status Pegawai -->
                <div class="bg-white p-3.5 sm:p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center items-center text-center">
                    <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl bg-green-100 flex items-center justify-center mb-1.5 sm:mb-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status Pegawai</p>
                    <p class="text-sm sm:text-base font-bold text-gray-800 mt-0.5">{{ ucfirst($user->status ?? 'Aktif') }}</p>
                </div>

                <!-- Card 2: Laporan Bulan Ini -->
                <div class="bg-white p-3.5 sm:p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center items-center text-center">
                    <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl bg-blue-100 flex items-center justify-center mb-1.5 sm:mb-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Laporan (Bulan Ini)</p>
                    <p class="text-sm sm:text-base font-bold text-gray-800 mt-0.5">{{ $laporanBulanIni ?? 0 }} <span class="text-xs font-normal text-gray-500">Laporan</span></p>
                </div>

                <!-- Card 3: Menunggu Validasi (Belum di-ACC) -->
                <div class="bg-white p-3.5 sm:p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center items-center text-center">
                    <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl bg-yellow-100 flex items-center justify-center mb-1.5 sm:mb-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Belum di-ACC</p>
                    <p class="text-sm sm:text-base font-bold text-gray-800 mt-0.5">{{ $laporanPending ?? 0 }} <span class="text-xs font-normal text-gray-500">Laporan</span></p>
                </div>

                <!-- Card 4: Sisa Cuti -->
                <div class="bg-white p-3.5 sm:p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center items-center text-center">
                    <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl bg-purple-100 flex items-center justify-center mb-1.5 sm:mb-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sisa Cuti</p>
                    <p class="text-sm sm:text-base font-bold text-gray-800 mt-0.5">{{ $user->sisa_cuti ?? 12 }} <span class="text-xs font-normal text-gray-500">Hari</span></p>
                </div>

            </div>

            <!-- KARTU RINGKASAN ABSENSI BULAN INI -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4 border-b border-gray-50 pb-3">
                    <div>
                        <h3 class="font-bold text-gray-800 text-sm sm:text-base">Kehadiran Absensi</h3>
                        <p class="text-xs text-gray-500">Rekapitulasi absensi otomatis bulan {{ date('F Y') }}</p>
                    </div>
                    <a href="{{ route('pjlp.absensi.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center transition">
                        Detail <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>

                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="bg-emerald-50/70 p-3 rounded-xl border border-emerald-100">
                        <p class="text-[10px] sm:text-xs font-bold text-emerald-700 uppercase">Hadir Tepat Waktu</p>
                        <p class="text-lg sm:text-xl font-bold text-emerald-800 mt-1">{{ $totalHadir ?? 0 }} <span class="text-xs font-normal text-emerald-600">Hari</span></p>
                    </div>
                    <div class="bg-amber-50/70 p-3 rounded-xl border border-amber-100">
                        <p class="text-[10px] sm:text-xs font-bold text-amber-700 uppercase">Terlambat</p>
                        <p class="text-lg sm:text-xl font-bold text-amber-800 mt-1">{{ $totalTerlambat ?? 0 }} <span class="text-xs font-normal text-amber-600">Hari</span></p>
                    </div>
                    <div class="bg-rose-50/70 p-3 rounded-xl border border-rose-100">
                        <p class="text-[10px] sm:text-xs font-bold text-rose-700 uppercase">Alpa / Tanpa Ket.</p>
                        <p class="text-lg sm:text-xl font-bold text-rose-800 mt-1">{{ $totalAlpa ?? 0 }} <span class="text-xs font-normal text-rose-600">Hari</span></p>
                    </div>
                </div>
            </div>

            <!-- AKSES CEPAT -->
            <div>
                <h3 class="text-gray-800 font-bold mb-3 sm:mb-4 px-1 text-sm sm:text-base">Akses Cepat</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    
                    <!-- Tombol Laporan Kinerja -->
                    <a href="{{ route('pjlp.laporan.create') }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-md transition-all border border-gray-100 p-3.5 sm:p-4 flex items-center justify-between">
                        <div class="flex items-center min-w-0 pr-2">
                            <div class="bg-blue-50 group-hover:bg-blue-100 p-2.5 sm:p-3 rounded-xl mr-3 flex-shrink-0 transition-colors">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </div>
                            <div class="truncate">
                                <h4 class="font-bold text-gray-800 text-xs sm:text-sm truncate">Isi Laporan Harian</h4>
                                <p class="text-[11px] sm:text-xs text-gray-500 truncate">Catat aktivitas kerja hari ini</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-300 group-hover:text-blue-500 flex-shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>

                    <!-- Tombol Rekap Absensi -->
                    <a href="{{ route('pjlp.absensi.index') }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-md transition-all border border-gray-100 p-3.5 sm:p-4 flex items-center justify-between">
                        <div class="flex items-center min-w-0 pr-2">
                            <div class="bg-purple-50 group-hover:bg-purple-100 p-2.5 sm:p-3 rounded-xl mr-3 flex-shrink-0 transition-colors">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="truncate">
                                <h4 class="font-bold text-gray-800 text-xs sm:text-sm truncate">Rekap Absensi</h4>
                                <p class="text-[11px] sm:text-xs text-gray-500 truncate">Cek rekapitulasi kehadiran</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-300 group-hover:text-purple-500 flex-shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>

                    <!-- Tombol Pengajuan Cuti -->
                    <a href="{{ route('pjlp.cuti.index') }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-md transition-all border border-gray-100 p-3.5 sm:p-4 flex items-center justify-between">
                        <div class="flex items-center min-w-0 pr-2">
                            <div class="bg-green-50 group-hover:bg-green-100 p-2.5 sm:p-3 rounded-xl mr-3 flex-shrink-0 transition-colors">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div class="truncate">
                                <h4 class="font-bold text-gray-800 text-xs sm:text-sm truncate">Pengajuan Cuti</h4>
                                <p class="text-[11px] sm:text-xs text-gray-500 truncate">Ajukan libur terencana</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-300 group-hover:text-green-500 flex-shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>

                </div>
            </div>

        </div>
    </div>

    <!-- BOTTOM NAVIGATION BAR (KHUSUS TAMPILAN MOBILE / HP) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40 px-4 py-2 flex justify-around items-center">
        <!-- Dashboard -->
        <a href="{{ route('pjlp.dashboard') }}" class="flex flex-col items-center {{ request()->routeIs('pjlp.dashboard') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="text-[10px] mt-1">Dashboard</span>
        </a>
        <!-- Laporan Kinerja -->
        <a href="{{ route('pjlp.laporan.create') }}" class="flex flex-col items-center {{ request()->routeIs('pjlp.laporan.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span class="text-[10px] mt-1">Laporan</span>
        </a>
        <!-- Rekap Absensi -->
        <a href="{{ route('pjlp.absensi.index') }}" class="flex flex-col items-center {{ request()->routeIs('pjlp.absensi.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-[10px] mt-1">Absensi</span>
        </a>
        <!-- Pengajuan Cuti -->
        <a href="{{ route('pjlp.cuti.index') }}" class="flex flex-col items-center {{ request()->routeIs('pjlp.cuti.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="text-[10px] mt-1">Cuti</span>
        </a>
    </div>
</x-app-layout>