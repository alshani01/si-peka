<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Data Pegawai') }}
        </h2>
    </x-slot>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div x-data="pegawaiPage()" class="py-4 sm:py-6 md:py-12 pb-32 md:pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            @if (session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <div>
                        <p class="font-bold text-sm">Berhasil!</p>
                        <p class="text-xs">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 rounded-2xl shadow-sm space-y-1">
                    <p class="font-bold text-sm">Terjadi Kesalahan!</p>
                    <ul class="list-disc ml-5 text-xs space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- KARTU STATISTIK ADMINISTRATOR -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                <a href="{{ route('admin.pegawai.index', ['role' => 'pjlp']) }}" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-blue-500 hover:shadow-md hover:bg-blue-50 transition cursor-pointer">
                    <p class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider">Total PJLP / P3K</p>
                    <p class="text-lg md:text-2xl font-extrabold text-gray-800 mt-1">{{ $totalPegawai }} <span class="text-xs font-normal text-gray-500">Orang</span></p>
                </a>

                <a href="{{ route('admin.pegawai.index', ['role' => 'atasan']) }}" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-indigo-500 hover:shadow-md hover:bg-indigo-50 transition cursor-pointer">
                    <p class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Atasan</p>
                    <p class="text-lg md:text-2xl font-extrabold text-gray-800 mt-1">{{ $totalAtasan }} <span class="text-xs font-normal text-gray-500">Orang</span></p>
                </a>

                <a href="#" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-amber-500 hover:shadow-md transition">
                    <p class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider">Laporan Pending</p>
                    <p class="text-lg md:text-2xl font-extrabold text-amber-600 mt-1">{{ $laporanPending }} <span class="text-[10px] font-normal text-gray-400 block sm:inline">({{ date('M Y') }})</span></p>
                </a>

                <a href="{{ route('admin.absensi.rekap', ['status' => 'pending']) }}" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-rose-500 hover:shadow-md hover:bg-rose-50 transition cursor-pointer">
                    <p class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider">Absen Pending</p>
                    <p class="text-lg md:text-2xl font-extrabold text-rose-600 mt-1">{{ $klarifikasiPending }} <span class="text-[10px] font-normal text-gray-400 block sm:inline">({{ date('M Y') }})</span></p>
                </a>

                <a href="#" class="col-span-2 sm:col-span-1 bg-white p-4 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-purple-500 hover:shadow-md transition">
                    <p class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider">Cuti Pending</p>
                    <p class="text-lg md:text-2xl font-extrabold text-purple-600 mt-1">{{ $cutiPending }} <span class="text-[10px] font-normal text-gray-400 block sm:inline">({{ date('M Y') }})</span></p>
                </a>
            </div>

            <!-- TAB NAVIGASI DESKTOP (4 TAB) -->
            <div class="hidden sm:flex bg-white p-2 rounded-2xl shadow-sm border border-gray-100 space-x-1">
                <a href="{{ route('admin.pegawai.index') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center {{ request()->routeIs('admin.pegawai.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    Manajemen Pegawai
                </a>
                <a href="{{ route('admin.absensi.rekap') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center {{ request()->routeIs('admin.absensi.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    Rekap Absensi
                </a>
                <a href="{{ route('admin.kinerja.rekap') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center {{ request()->routeIs('admin.kinerja.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    Rekap Kinerja
                </a>
                <a href="{{ route('admin.penilaian.rekap') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center {{ request()->routeIs('admin.penilaian.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    Penilaian Perilaku
                </a>
            </div>

            <!-- KONTEN DAFTAR PEGAWAI -->
            <div id="area-tabel-pegawai" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-100 bg-gray-50/80 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm md:text-base">Daftar Akun Pengguna</h3>
                            <p class="text-xs text-gray-500">Kelola informasi akun, role, jabatan, dan status pegawai.</p>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.pegawai.export_pdf') }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2.5 bg-rose-600 text-white rounded-xl text-xs font-bold hover:bg-rose-700 transition shadow w-fit">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Unduh Daftar Pegawai
                            </a>

                            {{-- HANYA DITAMPILKAN UNTUK ROLE ADMIN --}}
                            @if (auth()->user()->role === 'admin')
                                <a href="{{ route('admin.pegawai.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition shadow w-fit">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Tambah Pegawai
                                </a>
                            @endif
                        </div>
                    </div>

                    <form id="form-filter-pegawai" @submit.prevent="filterTable" method="GET" action="{{ route('admin.pegawai.index') }}" class="space-y-3 pt-2">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <div class="relative flex-1">
                                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NRK/NIP/ID PJLP, jabatan..." class="w-full border-gray-300 rounded-xl text-xs sm:text-sm p-2.5 pl-9 focus:ring-blue-500">
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-blue-600 text-white text-xs sm:text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-blue-700 transition flex items-center justify-center">Cari</button>
                                <button type="button" @click="resetFilter" class="bg-gray-200 text-gray-700 text-xs sm:text-sm font-bold px-3 py-2.5 rounded-xl hover:bg-gray-300 transition flex items-center justify-center">Reset</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-1">Kategori Pegawai</label>
                                <select name="role" @change="filterTable" class="w-full border-gray-300 rounded-xl text-xs p-2.5">
                                    <option value="all" {{ $roleFilter == 'all' ? 'selected' : '' }}>Semua Kategori</option>
                                    <option value="pimpinan" {{ $roleFilter == 'pimpinan' ? 'selected' : '' }}>Pimpinan</option>
                                    <option value="atasan" {{ $roleFilter == 'atasan' ? 'selected' : '' }}>Atasan</option>
                                    <option value="p3k" {{ $roleFilter == 'p3k' ? 'selected' : '' }}>P3K</option>
                                    <option value="pjlp" {{ $roleFilter == 'pjlp' ? 'selected' : '' }}>PJLP</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-1">Status Akun</label>
                                <select name="status" @change="filterTable" class="w-full border-gray-300 rounded-xl text-xs p-2.5">
                                    <option value="all" {{ $statusFilter == 'all' ? 'selected' : '' }}>Semua Status</option>
                                    <option value="aktif" {{ $statusFilter == 'aktif' ? 'selected' : '' }}>● Aktif</option>
                                    <option value="nonaktif" {{ $statusFilter == 'nonaktif' ? 'selected' : '' }}>● Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- 1. TAMPILAN DESKTOP -->
                <div class="hidden md:block overflow-x-auto p-4">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-xs font-bold uppercase tracking-wider">
                                <th class="p-3 border-b border-gray-200">Pegawai</th>
                                <th class="p-3 border-b border-gray-200">Role & Status</th>
                                <th class="p-3 border-b border-gray-200">Jabatan / Unit Kerja</th>
                                <th class="p-3 border-b border-gray-200">Atasan Penilai</th>
                                {{-- HEADER AKSI HANYA TAMPIL UNTUK ADMIN --}}
                                @if (auth()->user()->role === 'admin')
                                    <th class="p-3 text-center border-b border-gray-200">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($users as $user)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-3 align-middle whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            @if($user->foto_profil)
                                                <img src="{{ asset('storage/' . $user->foto_profil) }}" alt="{{ $user->name }}" class="w-9 h-9 rounded-xl object-cover border border-gray-200 shadow-sm">
                                            @else
                                                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center border border-blue-200">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-bold text-gray-900 text-sm">{{ $user->name }}</p>
                                                @if($user->id_pjlp)
                                                    <span class="text-[10px] text-blue-600 font-semibold block">ID/NIP: {{ $user->id_pjlp }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3 align-middle whitespace-nowrap">
                                        <span class="px-2.5 py-1 text-[10px] font-extrabold uppercase rounded-full 
                                            {{ $user->role === 'pimpinan' ? 'bg-purple-100 text-purple-800' : 
                                              ($user->role === 'atasan' ? 'bg-indigo-100 text-indigo-800' : 
                                              ($user->role === 'p3k' ? 'bg-teal-100 text-teal-800' : 'bg-blue-100 text-blue-800')) }}">
                                            {{ strtoupper($user->kategori_pegawai ?? $user->role) }}
                                        </span>
                                        <span class="block mt-1 text-xs font-semibold {{ $user->status === 'aktif' ? 'text-emerald-600' : 'text-rose-500' }}">
                                            ● {{ ucfirst($user->status ?? 'aktif') }}
                                        </span>
                                    </td>
                                    <td class="p-3 align-middle">
                                        <p class="text-xs font-bold text-gray-800">{{ $user->jenis_pekerjaan ?? '-' }}</p>
                                        <p class="text-[11px] text-gray-500 mt-0.5">{{ $user->unit_kerja ?? '-' }}</p>
                                    </td>
                                    <td class="p-3 align-middle whitespace-nowrap">
                                        @if(in_array($user->role, ['pjlp', 'p3k']) && $user->atasan)
                                            <p class="text-xs font-bold text-gray-800">{{ $user->atasan->name }}</p>
                                            <p class="text-[10px] text-gray-400">Atasan Penilai</p>
                                        @else
                                            <span class="text-xs text-gray-400 italic">-</span>
                                        @endif
                                    </td>
                                    {{-- TOMBOL EDIT & HAPUS HANYA TAMPIL UNTUK ADMIN --}}
                                    @if (auth()->user()->role === 'admin')
                                        <td class="p-3 align-middle text-center whitespace-nowrap space-x-1">
                                            <a href="{{ route('admin.pegawai.edit', $user->id) }}" class="inline-block px-3 py-1.5 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 transition shadow-sm">
                                                Edit
                                            </a>
                                            <button type="button" @click="confirmDelete('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->role }}')" class="px-3 py-1.5 bg-rose-600 text-white rounded-xl text-xs font-bold hover:bg-rose-700 transition shadow-sm">
                                                Hapus
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->role === 'admin' ? 5 : 4 }}" class="p-8 text-center text-gray-400 text-sm">Data pegawai tidak ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- 2. TAMPILAN MOBILE -->
                <div class="md:hidden p-3 space-y-3">
                    @forelse ($users as $user)
                        <div class="border border-gray-200 bg-white rounded-xl overflow-hidden shadow-sm transition">
                            <div class="p-3.5 flex items-center justify-between cursor-pointer" @click="expandedRow = expandedRow === '{{ $user->id }}' ? null : '{{ $user->id }}'">
                                <div class="flex items-center gap-3">
                                    @if($user->foto_profil)
                                        <img src="{{ asset('storage/' . $user->foto_profil) }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-xl object-cover border border-gray-200">
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center border border-blue-200">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-bold text-gray-900 text-sm">{{ $user->name }}</p>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="px-2 py-0.5 text-[9px] font-extrabold rounded-full {{ in_array($user->role, ['atasan','pimpinan']) ? 'bg-indigo-100 text-indigo-800' : ($user->role === 'p3k' ? 'bg-teal-100 text-teal-800' : 'bg-blue-100 text-blue-800') }}">
                                                {{ strtoupper($user->kategori_pegawai ?? $user->role) }}
                                            </span>
                                            <span class="text-[11px] font-semibold {{ $user->status === 'aktif' ? 'text-emerald-600' : 'text-rose-500' }}">
                                                ● {{ ucfirst($user->status ?? 'aktif') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 transform transition-transform" :class="{'rotate-180': expandedRow === '{{ $user->id }}'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>

                            <div x-show="expandedRow === '{{ $user->id }}'" x-cloak x-collapse class="border-t border-gray-100 bg-gray-50 p-3.5 space-y-2.5 text-xs">
                                @if($user->id_pjlp)
                                    <div>
                                        <p class="text-gray-400 font-semibold">ID / NRK / NIP Pegawai:</p>
                                        <p class="text-blue-700 font-bold">{{ $user->id_pjlp }}</p>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-gray-400 font-semibold">Jabatan & Unit Kerja:</p>
                                    <p class="text-gray-800 font-bold">{{ $user->jenis_pekerjaan ?? '-' }}</p>
                                    <p class="text-gray-500">{{ $user->unit_kerja ?? '-' }}</p>
                                </div>
                                @if(in_array($user->role, ['pjlp', 'p3k']) && $user->atasan)
                                    <div>
                                        <p class="text-gray-400 font-semibold">Atasan Langsung:</p>
                                        <p class="text-gray-800 font-bold">{{ $user->atasan->name }}</p>
                                    </div>
                                @endif

                                {{-- AKSI MOBILE HANYA UNTUK ADMIN --}}
                                @if (auth()->user()->role === 'admin')
                                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-200">
                                        <a href="{{ route('admin.pegawai.edit', $user->id) }}" class="px-3 py-1.5 bg-amber-500 text-white rounded-xl font-bold shadow-sm">Edit Akun</a>
                                        <button type="button" @click="confirmDelete('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->role }}')" class="px-3 py-1.5 bg-rose-600 text-white rounded-xl font-bold shadow-sm">Hapus</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400 text-sm">Data pegawai tidak ditemukan.</div>
                    @endforelse
                </div>

                @if(isset($users) && $users->total() > 0)
                    <div class="p-4 border-t border-gray-100 bg-gray-50/50 flex justify-center sm:justify-end text-xs">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>

            <!-- MODAL KONFIRMASI HAPUS AKUN (HANYA DI-RENDER JIKA ADMIN) -->
            @if (auth()->user()->role === 'admin')
                <div x-show="openDeleteModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                    <div x-show="openDeleteModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openDeleteModal = false"></div>
                    
                    <div x-show="openDeleteModal" x-transition class="relative bg-white rounded-2xl p-6 max-w-sm w-full text-left shadow-2xl z-10 border border-gray-100 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-800">Hapus Akun Pegawai?</h3>
                                <p class="text-xs text-gray-500">Konfirmasi penghapusan data</p>
                            </div>
                        </div>

                        <p class="text-xs text-gray-600 leading-relaxed bg-gray-50 p-3 rounded-xl border border-gray-200">
                            Apakah Anda yakin ingin menghapus akun <strong class="text-gray-900" x-text="deleteUserName"></strong>? Tindakan ini bersifat permanen.
                        </p>

                        <form :action="deleteActionUrl" method="POST" class="space-y-4 pt-2">
                            @csrf
                            @method('DELETE')
                            
                            <template x-if="deleteUserRole === 'atasan' || deleteUserRole === 'pimpinan'">
                                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl">
                                    <label class="block text-xs font-bold text-amber-800 mb-1">
                                        <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Atasan Pengganti (Wajib jika ada bawahan)
                                    </label>
                                    <p class="text-[10px] text-amber-700 mb-2 leading-tight">Jika pegawai ini memiliki bawahan, pilih atasan pengganti untuk memindahkan mereka.</p>
                                    <select name="pengganti_atasan_id" class="w-full text-xs rounded-lg border-amber-300 focus:ring-amber-500 focus:border-amber-500 p-2">
                                        <option value="">-- Pilih Atasan Pengganti (Opsional) --</option>
                                        @foreach($daftarAtasan as $atasan)
                                            <option value="{{ $atasan->id }}">{{ $atasan->name }} - {{ $atasan->jenis_pekerjaan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </template>

                            <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                                <button type="button" @click="openDeleteModal = false" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200 transition">Batal</button>
                                <button type="submit" class="px-4 py-2.5 bg-rose-600 text-white text-xs font-bold rounded-xl shadow hover:bg-rose-700 transition">Ya, Hapus Akun</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- BOTTOM NAVIGATION BAR (MOBILE ADMIN - 4 MENU) -->
            <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-xl z-[90] px-2 py-2 flex justify-around items-center">
                <a href="{{ route('admin.pegawai.index') }}" class="flex flex-col items-center {{ request()->routeIs('admin.pegawai.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span class="text-[9px] mt-1">Pegawai</span>
                </a>
                <a href="{{ route('admin.absensi.rekap') }}" class="flex flex-col items-center {{ request()->routeIs('admin.absensi.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-[9px] mt-1">Absensi</span>
                </a>
                <a href="{{ route('admin.kinerja.rekap') }}" class="flex flex-col items-center {{ request()->routeIs('admin.kinerja.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-[9px] mt-1">Kinerja</span>
                </a>
                <a href="{{ route('admin.penilaian.rekap') }}" class="flex flex-col items-center {{ request()->routeIs('admin.penilaian.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    <span class="text-[9px] mt-1">Perilaku</span>
                </a>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('pegawaiPage', () => ({
                expandedRow: null,
                openDeleteModal: false,
                deleteUserName: '',
                deleteUserRole: '',
                deleteActionUrl: '',

                confirmDelete(id, name, role) {
                    this.deleteUserName = name;
                    this.deleteUserRole = role;
                    this.deleteActionUrl = '/admin/pegawai/' + id;
                    this.openDeleteModal = true;
                },

                fetchAndSwap(targetUrl) {
                    fetch(targetUrl)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newContent = doc.getElementById('area-tabel-pegawai');
                            if (newContent) {
                                document.getElementById('area-tabel-pegawai').innerHTML = newContent.innerHTML;
                            }
                            window.history.pushState({}, '', targetUrl);
                        })
                        .catch(error => console.error('Gagal memperbarui tabel:', error));
                },

                filterTable() {
                    const form = document.getElementById('form-filter-pegawai');
                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData).toString();
                    const targetUrl = form.action + '?' + params;
                    this.fetchAndSwap(targetUrl);
                },

                resetFilter() {
                    const targetUrl = "{{ route('admin.pegawai.index') }}";
                    this.fetchAndSwap(targetUrl);
                },

                init() {
                    document.getElementById('area-tabel-pegawai').addEventListener('click', (e) => {
                        const link = e.target.closest('a');
                        if (
                            link && 
                            link.href && 
                            link.href.includes('/admin/pegawai') && 
                            !link.href.includes('export-pdf') && 
                            !link.href.includes('tambah') && 
                            !link.href.includes('create') && 
                            !link.href.includes('edit')
                        ) {
                            e.preventDefault();
                            this.fetchAndSwap(link.href);
                        }
                    });
                }
            }));
        });
    </script>
</x-app-layout>