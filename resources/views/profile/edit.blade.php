<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profil Saya') }}
        </h2>
    </x-slot>

    <!-- KUNCI PADDING BAWAH: pb-28 md:pb-12 agar tidak tertutup Bottom Nav Bar di HP -->
    <div class="py-6 md:py-12 pb-28 md:pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- KARTU RINGKASAN PROFIL PEGAWAI -->
            <div class="bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
                <!-- Ornaments Decorative Glow -->
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 z-10 relative">
                    <!-- Avatar / Foto Resmi Pegawai -->
                    <div class="relative shrink-0">
                        @if(Auth::user()->foto_profil)
                            <img src="{{ asset('storage/' . Auth::user()->foto_profil) }}" alt="Foto {{ Auth::user()->name }}" class="h-20 w-20 rounded-2xl object-cover shadow-md border-2 border-blue-200" />
                        @else
                            <div class="h-20 w-20 rounded-2xl bg-white text-blue-700 flex items-center justify-center font-extrabold text-3xl shadow-md border-2 border-blue-200">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <!-- Informasi Identitas Pegawai -->
                    <div class="text-center sm:text-left flex-1">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h3 class="text-2xl font-bold">{{ Auth::user()->name }}</h3>
                                <p class="text-blue-200 text-sm mt-0.5">{{ Auth::user()->email }}</p>
                            </div>
                            <span class="inline-block px-3 py-1 bg-green-500/20 text-green-300 border border-green-400/30 text-xs font-bold rounded-full w-fit mx-auto sm:mx-0">
                                Status: {{ ucfirst(Auth::user()->status ?? 'Aktif') }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4 pt-4 border-t border-white/15 text-xs text-blue-100">
                            <div>
                                <span class="text-blue-300 font-semibold block">Jenis Pekerjaan / Jabatan:</span>
                                <span class="font-bold text-sm text-white">{{ Auth::user()->jenis_pekerjaan ?? 'Petugas PJLP' }}</span>
                            </div>
                            <div>
                                <span class="text-blue-300 font-semibold block">Unit / Satuan Kerja:</span>
                                <span class="font-bold text-sm text-white">{{ Auth::user()->unit_kerja ?? 'Dinas KPKP DKI Jakarta' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORM 1: PERBAHARUI INFORMASI PROFIL -->
            <div class="p-6 sm:p-8 bg-white shadow-sm border border-gray-100 rounded-2xl">
                <div class="max-w-xl">
                    <div class="mb-4 pb-3 border-b border-gray-100">
                        <h3 class="text-base font-bold text-gray-800">Informasi Pribadi & Akun</h3>
                        <p class="text-xs text-gray-500">Perbarui informasi profil dan alamat email akun Anda.</p>
                    </div>
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- FORM 2: PERBAHARUI KATA SANDI (PASSWORD) -->
            <div class="p-6 sm:p-8 bg-white shadow-sm border border-gray-100 rounded-2xl">
                <div class="max-w-xl">
                    <div class="mb-4 pb-3 border-b border-gray-100">
                        <h3 class="text-base font-bold text-gray-800">Pembaruan Kata Sandi</h3>
                        <p class="text-xs text-gray-500">Pastikan akun Anda menggunakan kata sandi yang kuat dan aman.</p>
                    </div>
                    @include('profile.partials.update-password-form')
                </div>
            </div>

        </div>
    </div>

    <!-- BOTTOM NAVIGATION BAR DINAMIS BERDASARKAN ROLE -->
    <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-xl z-40 px-3 py-2 flex justify-around items-center">
        @if(auth()->user()->role === 'atasan')
            <!-- Menu Khusus ATASAN -->
            <a href="{{ route('atasan.laporan.index') }}" class="flex flex-col items-center {{ request()->routeIs('atasan.laporan.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="text-[10px] font-semibold mt-1">Laporan</span>
            </a>
            <a href="{{ route('atasan.absensi.index') }}" class="flex flex-col items-center {{ request()->routeIs('atasan.absensi.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-[10px] font-semibold mt-1">Absensi</span>
            </a>
            <a href="{{ route('atasan.cuti.index') }}" class="flex flex-col items-center {{ request()->routeIs('atasan.cuti.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-[10px] font-semibold mt-1">Cuti</span>
            </a>
        @elseif(auth()->user()->role === 'pjlp')
            <!-- Menu Khusus PJLP -->
            <a href="{{ route('pjlp.dashboard') }}" class="flex flex-col items-center {{ request()->routeIs('pjlp.dashboard') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="text-[10px] font-semibold mt-1">Dashboard</span>
            </a>
            <a href="{{ route('pjlp.laporan.create') }}" class="flex flex-col items-center {{ request()->routeIs('pjlp.laporan.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="text-[10px] font-semibold mt-1">Laporan</span>
            </a>
            <a href="{{ route('pjlp.absensi.index') }}" class="flex flex-col items-center {{ request()->routeIs('pjlp.absensi.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-[10px] font-semibold mt-1">Absensi</span>
            </a>
            <a href="{{ route('pjlp.cuti.index') }}" class="flex flex-col items-center {{ request()->routeIs('pjlp.cuti.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-[10px] font-semibold mt-1">Cuti</span>
            </a>
        @elseif(auth()->user()->role === 'admin')
            <!-- Menu Khusus ADMIN -->
            <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center text-blue-600 font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="text-[10px] font-semibold mt-1">Dashboard Admin</span>
            </a>
        @endif
    </div>
</x-app-layout>