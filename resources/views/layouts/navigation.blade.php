<nav x-data="{ open: false, openNotif: false }" class="bg-white/95 backdrop-blur-md border-b border-gray-200/80 shadow-sm sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            
            <!-- SISI KIRI: Logo Aplikasi + Breadcrumb Dinamis -->
            <div class="flex items-center space-x-3">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="focus:outline-none transition hover:opacity-90">
                        <x-application-logo />
                    </a>
                </div>

                <!-- Pembatas Garis Tipis (Desktop Only) -->
                <div class="hidden sm:block h-5 w-px bg-gray-200"></div>

                <!-- BREADCRUMB DINAMIS (DESKTOP) -->
                <nav class="hidden sm:flex items-center space-x-2 text-xs font-semibold text-gray-500">
                    <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 uppercase tracking-wider font-extrabold text-[10px]">
                        {{ strtoupper(Auth::user()->role) }}
                    </span>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    
                    <span class="text-gray-900 font-bold">
                        @if(Auth::user()->role === 'admin')
                            @if(request()->routeIs('admin.pegawai.*')) Manajemen Pegawai
                            @elseif(request()->routeIs('admin.absensi.rekap')) Rekap Absensi
                            @elseif(request()->routeIs('admin.absensi.import')) Import Absensi
                            @elseif(request()->routeIs('admin.hari_libur.*')) Hari Libur Nasional
                            @else Dashboard Admin @endif
                        @elseif(Auth::user()->role === 'atasan')
                            @if(request()->routeIs('atasan.laporan.*')) Validasi Laporan
                            @elseif(request()->routeIs('atasan.absensi.*')) Klarifikasi Absensi
                            @elseif(request()->routeIs('atasan.cuti.*')) Pengajuan Cuti
                            @elseif(request()->routeIs('atasan.penilaian.*')) Penilaian Perilaku
                            @else Dashboard Atasan @endif
                        @else
                            @if(request()->routeIs('pjlp.laporan.*')) Laporan Kinerja
                            @elseif(request()->routeIs('pjlp.absensi.*')) Rekap Absensi
                            @elseif(request()->routeIs('pjlp.cuti.*')) Pengajuan Cuti
                            @else Dashboard @endif
                        @endif
                    </span>
                </nav>
            </div>

            <!-- SISI KANAN: Lonceng Notifikasi & Profile Dropdown -->
            <div class="flex items-center space-x-2 sm:space-x-3">
                
                <!-- 1. IKON LONCENG NOTIFIKASI (PENDING TASKS) -->
                <div class="relative" @click.outside="openNotif = false">
                    <button @click="openNotif = !openNotif" 
                        class="relative p-2 rounded-xl text-gray-500 hover:text-blue-600 hover:bg-blue-50/80 border border-gray-200/80 focus:outline-none transition shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        
                        <!-- BADGE INDIKATOR MERAH -->
                        @if(($totalNotificationCount ?? 0) > 0)
                            <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-600 text-[9px] font-extrabold text-white ring-2 ring-white animate-pulse">
                                {{ $totalNotificationCount > 9 ? '9+' : $totalNotificationCount }}
                            </span>
                        @endif
                    </button>

                    <!-- DROPDOWN POPUP NOTIFIKASI -->
                    <div x-show="openNotif" x-cloak x-transition.origin.top.right 
                        class="absolute right-0 mt-2 w-72 sm:w-80 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 overflow-hidden">
                        
                        <div class="px-4 py-2.5 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                            <span class="text-xs font-bold text-gray-800">Notifikasi & Pending</span>
                            <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-blue-100 text-blue-700">
                                {{ $totalNotificationCount ?? 0 }} Total
                            </span>
                        </div>

                        <div class="divide-y divide-gray-100 text-xs max-h-64 overflow-y-auto">
                            @if(Auth::user()->role === 'atasan' || Auth::user()->role === 'admin')
                                <a href="{{ Auth::user()->role === 'atasan' ? route('atasan.laporan.index') : route('admin.pegawai.index') }}" class="p-3 flex items-start gap-3 hover:bg-gray-50 transition">
                                    <div class="p-2 rounded-xl bg-amber-100 text-amber-600 flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-bold text-gray-800">Laporan Kinerja Pending</p>
                                        <p class="text-gray-500 text-[11px] mt-0.5">{{ $pendingLaporanCount ?? 0 }} laporan menunggu validasi.</p>
                                    </div>
                                </a>

                                <a href="{{ Auth::user()->role === 'atasan' ? route('atasan.absensi.index') : route('admin.absensi.rekap') }}" class="p-3 flex items-start gap-3 hover:bg-gray-50 transition">
                                    <div class="p-2 rounded-xl bg-rose-100 text-rose-600 flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-bold text-gray-800">Klarifikasi Absen Pending</p>
                                        <p class="text-gray-500 text-[11px] mt-0.5">{{ $pendingAbsensiCount ?? 0 }} absensi perlu persetujuan.</p>
                                    </div>
                                </a>

                                <a href="{{ Auth::user()->role === 'atasan' ? route('atasan.cuti.index') : route('admin.pegawai.index') }}" class="p-3 flex items-start gap-3 hover:bg-gray-50 transition">
                                    <div class="p-2 rounded-xl bg-purple-100 text-purple-600 flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-bold text-gray-800">Pengajuan Cuti Pending</p>
                                        <p class="text-gray-500 text-[11px] mt-0.5">{{ $pendingCutiCount ?? 0 }} cuti belum diproses.</p>
                                    </div>
                                </a>

                                @if(Auth::user()->role === 'atasan')
                                    <a href="{{ route('atasan.penilaian.index') }}" class="p-3 flex items-start gap-3 hover:bg-gray-50 transition">
                                        <div class="p-2 rounded-xl bg-blue-100 text-blue-600 flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-bold text-gray-800">Penilaian Perilaku Pending</p>
                                            <p class="text-gray-500 text-[11px] mt-0.5">{{ $pendingPenilaianCount ?? 0 }} pegawai belum dinilai bulan ini.</p>
                                        </div>
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('pjlp.laporan.create') }}" class="p-3 flex items-start gap-3 hover:bg-gray-50 transition">
                                    <div class="p-2 rounded-xl bg-amber-100 text-amber-600 flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-bold text-gray-800">Revisi Laporan Kinerja</p>
                                        <p class="text-gray-500 text-[11px] mt-0.5">
                                            {{ ($pendingLaporanCount ?? 0) > 0 ? $pendingLaporanCount . ' laporan ditolak / perlu revisi.' : 'Tidak ada laporan yang ditolak.' }}
                                        </p>
                                    </div>
                                </a>

                                <a href="{{ route('pjlp.absensi.index') }}" class="p-3 flex items-start gap-3 hover:bg-gray-50 transition">
                                    <div class="p-2 rounded-xl bg-rose-100 text-rose-600 flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-bold text-gray-800">Klarifikasi Absen Ditolak</p>
                                        <p class="text-gray-500 text-[11px] mt-0.5">
                                            {{ ($pendingAbsensiCount ?? 0) > 0 ? $pendingAbsensiCount . ' pengajuan klarifikasi ditolak.' : 'Tidak ada absensi yang ditolak.' }}
                                        </p>
                                    </div>
                                </a>

                                <a href="{{ route('pjlp.cuti.index') }}" class="p-3 flex items-start gap-3 hover:bg-gray-50 transition">
                                    <div class="p-2 rounded-xl bg-purple-100 text-purple-600 flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-bold text-gray-800">Pengajuan Cuti Ditolak</p>
                                        <p class="text-gray-500 text-[11px] mt-0.5">
                                            {{ ($pendingCutiCount ?? 0) > 0 ? $pendingCutiCount . ' pengajuan cuti ditolak.' : 'Tidak ada cuti yang ditolak.' }}
                                        </p>
                                    </div>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 2. PROFILE DROPDOWN DESKTOP -->
                <div class="hidden sm:flex sm:items-center">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-gray-200 text-xs font-bold rounded-xl text-gray-700 bg-gray-50/80 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 focus:outline-none transition shadow-sm">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1.5 text-gray-400">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- 3. HAMBURGER BUTTON (MOBILE SLIDE DRAWER) -->
                <div class="-me-2 flex items-center sm:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-gray-500 hover:text-blue-600 hover:bg-blue-50 focus:outline-none transition border border-gray-200">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

            </div>

        </div>
    </div>

    <!-- RESPONSIVE MOBILE DRAWER (SLIDE DOWN) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-b border-gray-200 bg-white">
        <div class="pt-2 pb-3 space-y-1">
            @if(Auth::user()->role === 'admin')
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Dashboard Admin</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.pegawai.index')" :active="request()->routeIs('admin.pegawai.*')">Manajemen Pegawai</x-responsive-nav-link>
            @elseif(Auth::user()->role === 'atasan')
                <x-responsive-nav-link :href="route('atasan.laporan.index')" :active="request()->routeIs('atasan.laporan.*')">Validasi Laporan</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('atasan.absensi.index')" :active="request()->routeIs('atasan.absensi.*')">Klarifikasi Absensi</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('atasan.cuti.index')" :active="request()->routeIs('atasan.cuti.*')">Pengajuan Cuti</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('atasan.penilaian.index')" :active="request()->routeIs('atasan.penilaian.*')">Penilaian Perilaku</x-responsive-nav-link>
            @elseif(Auth::user()->role === 'pjlp')
                <x-responsive-nav-link :href="route('pjlp.dashboard')" :active="request()->routeIs('pjlp.dashboard')">Dashboard</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('pjlp.laporan.create')" :active="request()->routeIs('pjlp.laporan.*')">Laporan Kinerja</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('pjlp.absensi.index')" :active="request()->routeIs('pjlp.absensi.*')">Rekap Absensi</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('pjlp.cuti.index')" :active="request()->routeIs('pjlp.cuti.*')">Pengajuan Cuti</x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-4 pb-3 border-t border-gray-200 bg-gray-50/50">
            <div class="px-4">
                <div class="font-bold text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-xs text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Profile</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>