<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Data Absensi Pegawai') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12 pb-28 md:pb-12" x-data="rekapAbsensiPage()">
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

            <!-- TAB NAVIGASI DESKTOP (4 TAB) -->
            <div class="hidden sm:flex bg-white p-2 rounded-2xl shadow-sm border border-gray-100 space-x-1">
                <a href="{{ route('admin.pegawai.index') }}" class="flex-1 py-2.5 px-3 rounded-xl font-bold text-xs text-center {{ request()->routeIs('admin.pegawai.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    Manajemen Pegawai
                </a>
                <a href="{{ route('admin.absensi.rekap') }}" class="flex-1 py-2.5 px-3 rounded-xl font-bold text-xs text-center {{ request()->routeIs('admin.absensi.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    Rekap Absensi
                </a>
                <a href="{{ route('admin.kinerja.rekap') }}" class="flex-1 py-2.5 px-3 rounded-xl font-bold text-xs text-center {{ request()->routeIs('admin.kinerja.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    Rekap Kinerja
                </a>
                <a href="{{ route('admin.penilaian.rekap') }}" class="flex-1 py-2.5 px-3 rounded-xl font-bold text-xs text-center {{ request()->routeIs('admin.penilaian.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    Penilaian Perilaku
                </a>
            </div>

            <!-- FILTER PERIODE & PENCARIAN -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
                <form id="form-filter-absensi" @submit.prevent="filterData" method="GET" action="{{ route('admin.absensi.rekap') }}" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Cari Pegawai / ID</label>
                            <input type="text" name="search" value="{{ $search ?? request('search') }}" placeholder="Nama / ID PJLP..." class="w-full border-gray-300 rounded-xl text-xs p-2.5 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Bulan</label>
                            <select name="bulan" @change="filterData" class="w-full border-gray-300 rounded-xl text-xs p-2.5 focus:ring-blue-500 focus:border-blue-500">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ sprintf('%02d', $m) }}" {{ ($bulan ?? request('bulan', date('m'))) == sprintf('%02d', $m) ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tahun</label>
                            <select name="tahun" @change="filterData" class="w-full border-gray-300 rounded-xl text-xs p-2.5 focus:ring-blue-500 focus:border-blue-500">
                                @for ($y = date('Y'); $y >= 2024; $y--)
                                    <option value="{{ $y }}" {{ ($tahun ?? request('tahun', date('Y'))) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="resetFilter" class="px-4 py-2.5 bg-gray-200 text-gray-700 font-bold text-xs rounded-xl hover:bg-gray-300 transition">
                            Reset
                        </button>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white font-bold text-xs rounded-xl hover:bg-blue-700 transition shadow">
                            Filter Data
                        </button>
                    </div>
                </form>
            </div>

            <!-- KONTAINER REKAPITULASI -->
            <div id="area-tabel-rekap" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-100 bg-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 class="font-bold text-gray-800 text-sm md:text-base">Rekapitulasi Kehadiran PJLP</h3>
                            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
                                {{ $bulan ?? date('m') }}/{{ $tahun ?? date('Y') }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">Suku Dinas Ketahanan Pangan, Kelautan, dan Pertanian Jakarta Selatan</p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        {{-- HANYA ADMIN YANG DAPAT MENGELOLA LIBUR DAN IMPORT --}}
                        @if (auth()->user()->role === 'admin')
                            <button type="button" @click="openHariLiburModal = true" class="px-4 py-2.5 bg-amber-500 text-white font-bold text-xs rounded-xl hover:bg-amber-600 transition shadow flex items-center justify-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Kelola Hari Libur
                            </button>

                            <button type="button" @click="openImportModal = true" class="px-4 py-2.5 bg-emerald-600 text-white font-bold text-xs rounded-xl hover:bg-emerald-700 transition shadow flex items-center justify-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Import Data Absen
                            </button>
                        @endif

                        {{-- DAPAT DIAKSES ADMIN & PIMPINAN --}}
                        <a href="{{ route('admin.absensi.pdf', ['bulan' => $bulan ?? request('bulan', date('m')), 'tahun' => $tahun ?? request('tahun', date('Y'))]) }}" target="_blank" class="px-4 py-2.5 bg-rose-600 text-white font-bold text-xs rounded-xl hover:bg-rose-700 transition shadow flex items-center justify-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Unduh Laporan PDF
                        </a>
                    </div>
                </div>
                
                <!-- TAMPILAN DESKTOP -->
                <div class="hidden md:block p-4 sm:p-6 overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-100 border-b border-gray-200 text-gray-700 uppercase font-bold text-[10px] tracking-wider">
                                <th class="p-3">ID PJLP</th>
                                <th class="p-3">Nama Pegawai</th>
                                <th class="p-3">Jabatan / Unit</th>
                                <th class="p-3 text-center text-emerald-600">Hadir</th>
                                <th class="p-3 text-center text-amber-600">Terlambat</th>
                                <th class="p-3 text-center text-orange-600">Pulang Cepat</th>
                                <th class="p-3 text-center text-blue-600">Izin / Sakit</th>
                                <th class="p-3 text-center text-rose-600">Alpa</th>
                                <th class="p-3 text-center">Pending Validasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($rekapData as $row)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="p-3 font-mono font-bold text-gray-700">{{ $row['id_pjlp'] ?? '-' }}</td>
                                    <td class="p-3 font-bold text-gray-800 whitespace-nowrap">{{ $row['nama'] ?? '-' }}</td>
                                    <td class="p-3 text-gray-500 whitespace-nowrap">
                                        <p class="font-medium text-gray-700">{{ $row['jenis_pekerjaan'] ?? '-' }}</p>
                                        <p class="text-[10px] text-gray-400">{{ $row['unit_kerja'] ?? '-' }}</p>
                                    </td>
                                    <td class="p-3 text-center font-extrabold text-emerald-600 bg-emerald-50/30">{{ $row['hadir'] ?? 0 }} Hari</td>
                                    <td class="p-3 text-center font-bold text-amber-600 bg-amber-50/30">
                                        {{ isset($row['menit_terlambat']) && $row['menit_terlambat'] > 0 ? $row['menit_terlambat'] . ' Min' : '-' }}
                                    </td>
                                    <td class="p-3 text-center font-bold text-orange-600 bg-orange-50/30">
                                        {{ isset($row['menit_pulang_cepat']) && $row['menit_pulang_cepat'] > 0 ? $row['menit_pulang_cepat'] . ' Min' : '-' }}
                                    </td>
                                    <td class="p-3 text-center font-bold text-blue-600 bg-blue-50/30">{{ $row['izin_sakit'] ?? 0 }} Hari</td>
                                    <td class="p-3 text-center font-extrabold text-rose-600 bg-rose-50/30">{{ $row['alpa'] ?? 0 }} Hari</td>
                                    <td class="p-3 text-center whitespace-nowrap">
                                        @if(($row['pending_validasi'] ?? 0) > 0)
                                            <button type="button" @click="showPendingDetail('{{ addslashes($row['nama'] ?? '') }}', {{ json_encode($row['detail_pending'] ?? []) }})" class="px-2.5 py-1 bg-amber-100 hover:bg-amber-200 text-amber-800 font-extrabold rounded-full text-[10px] transition shadow-sm animate-pulse cursor-pointer inline-flex items-center gap-1">
                                                <span>{{ $row['pending_validasi'] }} Klarifikasi</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </button>
                                        @else
                                            <span class="text-emerald-600 font-bold text-[11px] flex items-center justify-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Tervalidasi
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="p-8 text-center text-gray-400 italic">Belum ada data rekapitulasi absensi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- TAMPILAN MOBILE -->
                <div class="md:hidden p-3 space-y-3 bg-gray-50/50">
                    @forelse ($rekapData as $row)
                        <div class="border border-gray-200 bg-white rounded-xl overflow-hidden shadow-sm transition">
                            <div class="p-3.5 flex items-start justify-between cursor-pointer" @click="expandedRow = expandedRow === {{ $loop->index }} ? null : {{ $loop->index }}">
                                <div class="flex-1">
                                    <p class="font-bold text-gray-900 text-sm">{{ $row['nama'] ?? '-' }}</p>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <span class="text-[10px] font-mono text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200">{{ $row['id_pjlp'] ?? '-' }}</span>
                                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">
                                            {{ $row['hadir'] ?? 0 }} Hadir
                                        </span>
                                        @if(($row['alpa'] ?? 0) > 0)
                                            <span class="text-[10px] font-bold text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-100">
                                                {{ $row['alpa'] }} Alpa
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mt-2.5">
                                        @if(($row['pending_validasi'] ?? 0) > 0)
                                            <button type="button" @click.stop="showPendingDetail('{{ addslashes($row['nama'] ?? '') }}', {{ json_encode($row['detail_pending'] ?? []) }})" class="text-[10px] font-extrabold text-amber-800 bg-amber-100 px-2 py-1 rounded-full flex items-center gap-1 shadow-sm">
                                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ $row['pending_validasi'] }} Klarifikasi (Lihat Alasan)
                                            </button>
                                        @elseif(($row['alpa'] ?? 0) > 0)
                                            <span class="text-[10px] font-extrabold text-rose-500 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                Perhatian: Terdapat Alpa
                                            </span>
                                        @else
                                            <span class="text-[10px] font-bold text-emerald-500 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Tervalidasi
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 mt-1 transform transition-transform" :class="{'rotate-180': expandedRow === {{ $loop->index }}}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>

                            <div x-show="expandedRow === {{ $loop->index }}" x-cloak style="display: none;" class="border-t border-gray-100 bg-gray-50/80 p-3.5 space-y-3">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Jabatan / Unit</p>
                                    <p class="text-xs text-gray-800 font-medium">{{ $row['jenis_pekerjaan'] ?? '-' }} &bull; <span class="text-gray-500">{{ $row['unit_kerja'] ?? '-' }}</span></p>
                                </div>

                                <div class="grid grid-cols-3 gap-2 text-xs">
                                    <div class="bg-white p-2 rounded-lg border border-gray-100 shadow-sm text-center">
                                        <p class="text-[10px] text-gray-500 font-bold mb-1">Izin/Sakit</p>
                                        <p class="text-sm font-extrabold text-blue-600">{{ $row['izin_sakit'] ?? 0 }} <span class="text-[9px] font-normal text-blue-400">Hari</span></p>
                                    </div>
                                    <div class="bg-white p-2 rounded-lg border border-gray-100 shadow-sm text-center">
                                        <p class="text-[10px] text-gray-500 font-bold mb-1">Terlambat</p>
                                        <p class="text-sm font-extrabold text-amber-600">{{ isset($row['menit_terlambat']) && $row['menit_terlambat'] > 0 ? $row['menit_terlambat'] : '-' }} <span class="text-[9px] font-normal text-amber-500">{{ ($row['menit_terlambat'] ?? 0) > 0 ? 'Min' : '' }}</span></p>
                                    </div>
                                    <div class="bg-white p-2 rounded-lg border border-gray-100 shadow-sm text-center">
                                        <p class="text-[10px] text-gray-500 font-bold mb-1">Pulang Cpt</p>
                                        <p class="text-sm font-extrabold text-orange-600">{{ isset($row['menit_pulang_cepat']) && $row['menit_pulang_cepat'] > 0 ? $row['menit_pulang_cepat'] : '-' }} <span class="text-[9px] font-normal text-orange-400">{{ ($row['menit_pulang_cepat'] ?? 0) > 0 ? 'Min' : '' }}</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400 text-sm bg-white rounded-xl border border-gray-200">Belum ada data rekapitulasi absensi.</div>
                    @endforelse
                </div>
            </div>

            <!-- MODAL DETAIL PENJELASAN KLARIFIKASI PENDING -->
            <div x-show="openDetailModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="display: none;">
                <div x-show="openDetailModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openDetailModal = false"></div>
                
                <div x-show="openDetailModal" x-transition class="relative bg-white rounded-2xl p-6 max-w-lg w-full shadow-2xl z-10 border border-gray-100 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 text-sm">Pengajuan Klarifikasi Pending</h3>
                                <p class="text-xs text-blue-600 font-semibold" x-text="selectedEmployeeName"></p>
                            </div>
                        </div>
                        <button type="button" @click="openDetailModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold">&times;</button>
                    </div>

                    <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
                        <template x-for="(item, idx) in selectedPendingList" :key="idx">
                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-3.5 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-gray-800" x-text="item.tanggal"></span>
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-extrabold text-[10px] rounded-full" x-text="item.status_kehadiran"></span>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Alasan Pegawai:</p>
                                    <p class="text-xs text-gray-700 italic bg-white p-2 rounded-lg border border-gray-100 mt-0.5" x-text="item.alasan"></p>
                                </div>
                                <template x-if="item.bukti">
                                    <a :href="item.bukti" target="_blank" class="inline-flex items-center text-[11px] font-bold text-blue-600 hover:underline">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Lihat File Lampiran Bukti
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex justify-end">
                        <button type="button" @click="openDetailModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200 transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>

            {{-- MODAL HARI LIBUR & IMPORT HANYA DITAMPILKAN UNTUK ROLE ADMIN --}}
            @if (auth()->user()->role === 'admin')
                <!-- MODAL KELOLA HARI LIBUR NASIONAL -->
                <div x-show="openHariLiburModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="display: none;">
                    <div x-show="openHariLiburModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openHariLiburModal = false"></div>
                    
                    <div x-show="openHariLiburModal" x-transition class="relative bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl z-10 border border-gray-100 space-y-5">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 text-base">Kelola Hari Libur Nasional</h3>
                                    <p class="text-xs text-gray-500">Penetapan libur resmi untuk rekap absensi</p>
                                </div>
                            </div>
                            <button type="button" @click="openHariLiburModal = false" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 font-bold transition">&times;</button>
                        </div>

                        <form action="{{ route('admin.hari_libur.store') }}" method="POST" class="bg-amber-50/60 p-4 rounded-2xl border border-amber-200/60 space-y-3">
                            @csrf
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold text-amber-900 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    Tambah Hari Libur Baru
                                </p>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[10px] font-bold text-amber-800 uppercase tracking-wider mb-1">Tanggal</label>
                                    <input type="date" name="tanggal" required class="w-full text-xs rounded-xl border-amber-200 bg-white p-2.5 focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-amber-800 uppercase tracking-wider mb-1">Keterangan Hari Libur</label>
                                    <input type="text" name="keterangan" placeholder="Contoh: Idul Fitri" required class="w-full text-xs rounded-xl border-amber-200 bg-white p-2.5 focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                                </div>
                            </div>
                            <div class="flex justify-end pt-1">
                                <button type="submit" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center gap-1.5">
                                    <span>+ Simpan Hari Libur</span>
                                </button>
                            </div>
                        </form>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between px-1">
                                <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Daftar Libur Terdaftar</p>
                                <span class="text-[10px] bg-amber-100 text-amber-800 font-extrabold px-2 py-0.5 rounded-full">{{ count($liburs ?? []) }} Hari</span>
                            </div>

                            <div class="max-h-56 overflow-y-auto divide-y divide-gray-100 border border-gray-200 rounded-2xl bg-white shadow-inner">
                                @forelse($liburs ?? [] as $libur)
                                    <div x-data="{ confirmDelete: false }" class="p-3 flex items-center justify-between hover:bg-gray-50/80 transition">
                                        <div>
                                            <p class="font-bold text-xs text-gray-800">{{ $libur->keterangan }}</p>
                                            <p class="text-[10px] text-amber-600 font-semibold mt-0.5 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                {{ \Carbon\Carbon::parse($libur->tanggal)->translatedFormat('d F Y') }}
                                            </p>
                                        </div>

                                        <div>
                                            <template x-if="!confirmDelete">
                                                <button type="button" @click="confirmDelete = true" class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition" title="Hapus Hari Libur">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </template>

                                            <template x-if="confirmDelete">
                                                <div class="flex items-center gap-1">
                                                    <form action="{{ route('admin.hari_libur.destroy', $libur->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white font-bold text-[10px] rounded-lg transition shadow-sm">
                                                            Ya, Hapus
                                                        </button>
                                                    </form>
                                                    <button type="button" @click="confirmDelete = false" class="px-2 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold text-[10px] rounded-lg transition">
                                                        Batal
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-xs text-gray-400 italic">Belum ada data hari libur nasional terdaftar.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-100 flex justify-end">
                            <button type="button" @click="openHariLiburModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200 transition">
                                Selesai
                            </button>
                        </div>
                    </div>
                </div>

                <!-- MODAL POPUP UNTUK IMPORT EXCEL ABSENSI -->
                <div x-show="openImportModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="display: none;">
                    <div x-show="openImportModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openImportModal = false"></div>
                    <div x-show="openImportModal" x-transition class="relative bg-white rounded-2xl p-6 md:p-8 max-w-lg w-full shadow-2xl z-10">
                        <div class="text-center mb-6">
                            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            </div>
                            <h3 class="font-bold text-gray-800 text-lg">Unggah File Absensi Mesin</h3>
                            <p class="text-xs text-gray-500 mt-1">Unggah file rekap (.xlsx / .csv) hasil olahan operator mesin absensi.</p>
                        </div>

                        <form action="{{ route('admin.absensi.proses_import') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Pilih File Rekap Absensi</label>
                                <input type="file" name="file_excel" accept=".xlsx, .xls, .csv" required class="block w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-xl p-2 cursor-pointer focus:outline-none">
                                <p class="text-[11px] text-gray-400 mt-1">Format: Excel (.xlsx / .xls) atau CSV. Maksimal 2MB.</p>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <a href="{{ route('admin.absensi.template') }}" class="inline-flex items-center text-xs font-bold text-blue-600 hover:underline">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Unduh Template
                                </a>
                                <div class="flex space-x-2">
                                    <button type="button" @click="openImportModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200">Batal</button>
                                    <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-700 transition shadow">Import Data</button>
                                </div>
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
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
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
            Alpine.data('rekapAbsensiPage', () => ({
                openImportModal: false,
                openDetailModal: false,
                openHariLiburModal: false,
                expandedRow: null,
                selectedEmployeeName: '',
                selectedPendingList: [],

                showPendingDetail(nama, pendingList) {
                    this.selectedEmployeeName = nama;
                    this.selectedPendingList = pendingList;
                    this.openDetailModal = true;
                },

                fetchAndSwap(targetUrl) {
                    fetch(targetUrl)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newContent = doc.getElementById('area-tabel-rekap');
                            if (newContent) {
                                document.getElementById('area-tabel-rekap').innerHTML = newContent.innerHTML;
                            }
                            window.history.pushState({}, '', targetUrl);
                        })
                        .catch(error => console.error('Gagal memperbarui tabel rekap:', error));
                },

                filterData() {
                    const form = document.getElementById('form-filter-absensi');
                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData).toString();
                    const targetUrl = form.action + '?' + params;
                    this.fetchAndSwap(targetUrl);
                },

                resetFilter() {
                    const targetUrl = "{{ route('admin.absensi.rekap') }}";
                    this.fetchAndSwap(targetUrl);
                }
            }));
        });
    </script>
</x-app-layout>