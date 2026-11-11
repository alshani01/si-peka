<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Rekapitulasi Laporan Kinerja') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12 pb-28 md:pb-12" x-data="rekapKinerjaPage()">
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

            <!-- TAB NAVIGASI DESKTOP (4 TAB UTAMA) -->
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
                <form id="form-filter-kinerja" @submit.prevent="filterData" method="GET" action="{{ route('admin.kinerja.rekap') }}" class="space-y-4">
                    <input type="hidden" name="tab" value="rekap">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Cari Pegawai / ID</label>
                            <input type="text" name="search" value="{{ $search ?? request('search') }}" placeholder="Nama / ID PJLP..." class="w-full border-gray-300 rounded-xl text-xs p-2.5 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Status Validasi / Laporan</label>
                            <select name="status_validasi" @change="filterData" class="w-full border-gray-300 rounded-xl text-xs p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <option value="">Semua Status</option>
                                <option value="belum_kirim" {{ ($statusValidasi ?? request('status_validasi')) === 'belum_kirim' ? 'selected' : '' }}>Belum Mengirim Laporan</option>
                                <option value="pending" {{ ($statusValidasi ?? request('status_validasi')) === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                                <option value="disetujui" {{ ($statusValidasi ?? request('status_validasi')) === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                <option value="ditolak" {{ in_array(($statusValidasi ?? request('status_validasi')), ['ditolak', 'direvisi']) ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tipe Periode</label>
                            <select name="tipe_filter" x-model="tipeFilter" @change="filterData" class="w-full border-gray-300 rounded-xl text-xs p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 font-bold">
                                <option value="bulanan">Rekap Bulanan</option>
                                <option value="harian">Rekap Harian</option>
                            </select>
                        </div>

                        <!-- Opsi Bulanan -->
                        <div x-show="tipeFilter === 'bulanan'" class="grid grid-cols-2 gap-2" x-cloak>
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

                        <!-- Opsi Harian -->
                        <div x-show="tipeFilter === 'harian'" x-cloak>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pilih Tanggal</label>
                            <input type="date" name="tanggal" value="{{ $tanggal ?? request('tanggal', date('Y-m-d')) }}" @change="filterData" class="w-full border-gray-300 rounded-xl text-xs p-2.5 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="resetFilter" class="px-4 py-2.5 bg-gray-200 text-gray-700 font-bold text-xs rounded-xl hover:bg-gray-300 transition">
                            Reset
                        </button>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white font-bold text-xs rounded-xl hover:bg-blue-700 transition shadow">
                            Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- KONTAINER REKAPITULASI -->
            <div id="area-tabel-kinerja" @click="handlePaginationClick($event)" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6">
                <div class="p-4 sm:p-6 border-b border-gray-100 bg-gray-50 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 class="font-bold text-gray-800 text-sm md:text-base">Data Rekapitulasi Kinerja</h3>
                            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">
                                @if(isset($tipeFilter) && $tipeFilter === 'harian')
                                    {{ \Carbon\Carbon::parse($tanggal ?? date('Y-m-d'))->translatedFormat('d F Y') }}
                                @else
                                    {{ \Carbon\Carbon::createFromDate($tahun ?? date('Y'), $bulan ?? date('m'), 1)->translatedFormat('F Y') }}
                                @endif
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">Suku Dinas Ketahanan Pangan, Kelautan, dan Pertanian Jakarta Selatan</p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">

                        <!-- TOMBOL BUKA KUNCI (HANYA ADMIN) -->
                        @if(auth()->user()->role === 'admin')
                            <button type="button" @click="showBukaKunciModal = true" class="relative px-4 py-2.5 bg-amber-500 text-white font-bold text-xs rounded-xl hover:bg-amber-600 transition shadow flex items-center justify-center w-full sm:w-auto gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Buka Kunci Input</span>
                                @if(isset($bukaKunciPendingCount) && $bukaKunciPendingCount > 0)
                                    <span class="px-1.5 py-0.5 bg-rose-600 text-white font-extrabold rounded-full text-[10px] animate-pulse ml-1">
                                        {{ $bukaKunciPendingCount }}
                                    </span>
                                @endif
                            </button>
                        @endif

                        @php
                            $pdfParams = [
                                'tipe_filter' => $tipeFilter ?? 'bulanan',
                                'tanggal' => $tanggal ?? date('Y-m-d'),
                                'bulan' => $bulan ?? date('m'),
                                'tahun' => $tahun ?? date('Y'),
                                'search' => $search ?? request('search', ''),
                                'status_validasi' => $statusValidasi ?? request('status_validasi', '')
                            ];
                        @endphp
                        <a href="{{ route('admin.kinerja.rekap.pdf', $pdfParams) }}" target="_blank" class="px-4 py-2.5 bg-rose-600 text-white font-bold text-xs rounded-xl hover:bg-rose-700 transition shadow flex items-center justify-center w-full sm:w-auto">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Unduh Rekap PDF
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
                                <th class="p-3">Jabatan / Lokasi Tugas</th>
                                <th class="p-3 text-center">Total Lap.</th>
                                <th class="p-3 text-center text-emerald-600">Disetujui</th>
                                <th class="p-3 text-center text-rose-600">Ditolak</th>
                                <th class="p-3 text-center text-amber-600">Menunggu</th>
                                <th class="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($rekapKinerja as $row)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="p-3 font-mono font-bold text-gray-700">{{ $row['id_pjlp'] }}</td>
                                    <td class="p-3 font-bold text-gray-800 whitespace-nowrap">{{ $row['nama'] }}</td>
                                    <td class="p-3 text-gray-500 whitespace-nowrap">
                                        <p class="font-medium text-gray-700">{{ $row['jenis_pekerjaan'] }}</p>
                                        <p class="text-[10px] text-gray-400">{{ $row['lokasi_tugas'] }}</p>
                                    </td>

                                    <td class="p-3 text-center font-bold text-gray-800 bg-gray-50/50">{{ $row['total_laporan'] }}</td>
                                    <td class="p-3 text-center font-extrabold text-emerald-600 bg-emerald-50/30">{{ $row['laporan_acc'] }}</td>
                                    <td class="p-3 text-center font-extrabold text-rose-600 bg-rose-50/30">{{ $row['laporan_ditolak'] }}</td>
                                    <td class="p-3 text-center font-extrabold text-amber-600 bg-amber-50/30">
                                        @if($row['laporan_pending'] > 0)
                                            <span class="inline-flex items-center gap-1 animate-pulse">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ $row['laporan_pending'] }}
                                            </span>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td class="p-3 text-center whitespace-nowrap">
                                        <a href="{{ route('admin.kinerja.detail', ['id' => $row['id'], 'tipe_filter' => $tipeFilter ?? 'bulanan', 'tanggal' => $tanggal ?? request('tanggal', date('Y-m-d')), 'bulan' => $bulan ?? request('bulan', date('m')), 'tahun' => $tahun ?? request('tahun', date('Y')), 'status_validasi' => $statusValidasi ?? request('status_validasi')]) }}" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-sm text-[11px] inline-flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Periksa
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="p-8 text-center text-gray-400 italic">Belum ada data kinerja pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- TAMPILAN MOBILE REKAP -->
                <div class="md:hidden p-3 space-y-3 bg-gray-50/50">
                    @forelse ($rekapKinerja as $row)
                        <div class="border border-gray-200 bg-white rounded-xl overflow-hidden shadow-sm transition">
                            <div class="p-3.5 flex items-start justify-between cursor-pointer" @click="expandedRow = expandedRow === {{ $loop->index }} ? null : {{ $loop->index }}">
                                <div class="flex-1">
                                    <p class="font-bold text-gray-900 text-sm">{{ $row['nama'] }}</p>
                                    <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                        <span class="text-[10px] font-mono text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200">{{ $row['id_pjlp'] }}</span>
                                        @if($row['laporan_pending'] > 0)
                                            <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-100 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                                {{ $row['laporan_pending'] }} Menunggu
                                            </span>
                                        @else
                                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">
                                                {{ $row['laporan_acc'] }} Selesai
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 mt-1 transform transition-transform" :class="{'rotate-180': expandedRow === {{ $loop->index }}}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>

                            <div x-show="expandedRow === {{ $loop->index }}" x-cloak style="display: none;" class="border-t border-gray-100 bg-gray-50/80 p-3.5 space-y-3">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Jabatan / Lokasi Tugas</p>
                                    <p class="text-xs text-gray-800 font-medium">{{ $row['jenis_pekerjaan'] }} &bull; <span class="text-gray-500">{{ $row['lokasi_tugas'] }}</span></p>
                                </div>

                                <div class="grid grid-cols-4 gap-2 text-xs">
                                    <div class="bg-white p-2 rounded-lg border border-gray-100 shadow-sm text-center">
                                        <p class="text-[9px] text-gray-500 font-bold mb-1">Total</p>
                                        <p class="text-sm font-extrabold text-gray-800">{{ $row['total_laporan'] }}</p>
                                    </div>
                                    <div class="bg-white p-2 rounded-lg border border-gray-100 shadow-sm text-center">
                                        <p class="text-[9px] text-gray-500 font-bold mb-1">ACC</p>
                                        <p class="text-sm font-extrabold text-emerald-600">{{ $row['laporan_acc'] }}</p>
                                    </div>
                                    <div class="bg-white p-2 rounded-lg border border-gray-100 shadow-sm text-center">
                                        <p class="text-[9px] text-rose-600 font-bold mb-1">Ditolak</p>
                                        <p class="text-sm font-extrabold text-rose-600">{{ $row['laporan_ditolak'] }}</p>
                                    </div>
                                    <div class="bg-white p-2 rounded-lg border border-gray-100 shadow-sm text-center bg-amber-50/50">
                                        <p class="text-[9px] text-amber-600 font-bold mb-1">Pending</p>
                                        <p class="text-sm font-extrabold text-amber-600">{{ $row['laporan_pending'] }}</p>
                                    </div>
                                </div>

                                <a href="{{ route('admin.kinerja.detail', ['id' => $row['id'], 'tipe_filter' => $tipeFilter ?? 'bulanan', 'tanggal' => $tanggal ?? request('tanggal', date('Y-m-d')), 'bulan' => $bulan ?? request('bulan', date('m')), 'tahun' => $tahun ?? request('tahun', date('Y')), 'status_validasi' => $statusValidasi ?? request('status_validasi')]) }}" class="w-full flex items-center justify-center gap-1.5 px-4 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-sm text-xs mt-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Buka Halaman Pemeriksaan
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400 text-sm bg-white rounded-xl border border-gray-200">Belum ada data kinerja pada periode ini.</div>
                    @endforelse
                </div>

                <div class="p-4 border-t border-gray-100 bg-white">
                    {{ $rekapKinerja->links() }}
                </div>
            </div>

            <!-- MODAL BUKA KUNCI INPUT (HANYA MUNCUL DI PERAN ADMIN) -->
            @if(auth()->user()->role === 'admin')
                <div x-show="showBukaKunciModal" x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-[90] flex items-center justify-center p-3 sm:p-6 bg-slate-900/60 backdrop-blur-sm">

                    <div @click.away="showBukaKunciModal = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                        class="bg-white rounded-3xl w-full max-w-5xl max-h-[90vh] shadow-2xl flex flex-col overflow-hidden border border-gray-100">

                        <!-- Header Modal -->
                        <div class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-amber-50/60 via-white to-white">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold shadow-2xs flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-gray-800 text-sm sm:text-base">Daftar Permohonan Buka Kunci Input</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Periode Terfilter:
                                        <span class="font-bold text-amber-700">
                                            @if(($tipeFilter ?? 'bulanan') === 'harian')
                                                {{ \Carbon\Carbon::parse($tanggal ?? date('Y-m-d'))->translatedFormat('d F Y') }}
                                            @else
                                                {{ \Carbon\Carbon::createFromDate($tahun ?? date('Y'), $bulan ?? date('m'), 1)->translatedFormat('F Y') }}
                                            @endif
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <button @click="showBukaKunciModal = false" class="p-2 text-gray-400 hover:text-rose-600 bg-gray-50 hover:bg-rose-50 rounded-xl transition border border-gray-200/60 shadow-2xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Body Modal (Scrollable) -->
                        <div id="area-modal-buka-kunci" @click="handleModalPaginationClick($event)" class="p-4 sm:p-6 overflow-y-auto space-y-5 flex-1 bg-slate-50/50">

                            <!-- FILTER BUKA KUNCI -->
                            <div class="bg-white rounded-2xl shadow-2xs border border-gray-100 p-4 sm:p-5">
                                <form method="GET" action="{{ route('admin.kinerja.rekap') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                                    <input type="hidden" name="tab" value="buka_kunci">

                                    <!-- HIDDEN INPUT UNTUK MEMBAWA FILTER PERIODE HALAMAN UTAMA -->
                                    <input type="hidden" name="tipe_filter" value="{{ $tipeFilter ?? 'bulanan' }}">
                                    <input type="hidden" name="bulan" value="{{ $bulan ?? date('m') }}">
                                    <input type="hidden" name="tahun" value="{{ $tahun ?? date('Y') }}">
                                    <input type="hidden" name="tanggal" value="{{ $tanggal ?? date('Y-m-d') }}">
                                    <input type="hidden" name="search" value="{{ $search ?? '' }}">
                                    <input type="hidden" name="status_validasi" value="{{ $statusValidasi ?? '' }}">

                                    <div class="sm:col-span-5">
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Cari Pegawai / ID</label>
                                        <div class="relative">
                                            <input type="text" name="search_buka_kunci" value="{{ $searchBukaKunci ?? '' }}" placeholder="Nama / ID PJLP..." class="w-full border-gray-200 rounded-xl text-xs pl-9 pr-3 py-2.5 focus:ring-amber-500 focus:border-amber-500 bg-gray-50/50 focus:bg-white transition">
                                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-4">
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Status Pengajuan</label>
                                        <select name="status_buka_kunci" onchange="this.form.submit()" class="w-full border-gray-200 rounded-xl text-xs py-2.5 px-3 focus:ring-amber-500 focus:border-amber-500 bg-gray-50/50 focus:bg-white font-semibold transition">
                                            <option value="pending" {{ ($statusBukaKunci ?? '') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                                            <option value="disetujui" {{ ($statusBukaKunci ?? '') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                            <option value="ditolak" {{ ($statusBukaKunci ?? '') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                            <option value="semua" {{ ($statusBukaKunci ?? '') === 'semua' ? 'selected' : '' }}>Semua Status</option>
                                        </select>
                                    </div>

                                    <div class="sm:col-span-3 flex items-center gap-2">
                                        <button type="submit" class="flex-1 py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl transition shadow-xs flex items-center justify-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                            Filter
                                        </button>
                                        <a href="{{ route('admin.kinerja.rekap', ['tab' => 'buka_kunci', 'tipe_filter' => $tipeFilter ?? 'bulanan', 'bulan' => $bulan ?? date('m'), 'tahun' => $tahun ?? date('Y'), 'tanggal' => $tanggal ?? date('Y-m-d'), 'search' => $search ?? '', 'status_validasi' => $statusValidasi ?? '']) }}" class="py-2.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition text-center">
                                            Reset
                                        </a>
                                    </div>
                                </form>
                            </div>

                            <!-- TABEL DESKTOP & CARDS ACCORDION MOBILE BUKA KUNCI -->
                            <div class="bg-white rounded-2xl shadow-2xs border border-gray-100 overflow-hidden">

                                <!-- DESKTOP TABLE VIEW -->
                                <div class="hidden md:block overflow-x-auto">
                                    <table class="w-full text-left border-collapse text-xs">
                                        <thead>
                                            <tr class="bg-gray-100/70 text-gray-700 uppercase font-bold text-[10px] tracking-wider border-b border-gray-200">
                                                <th class="p-3.5 pl-4">Pegawai</th>
                                                <th class="p-3.5">Tanggal Terkunci</th>
                                                <th class="p-3.5">Alasan Keterlambatan</th>
                                                <th class="p-3.5 text-center">Status</th>
                                                <th class="p-3.5 text-center pr-4">Aksi Persetujuan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @forelse ($pengajuanBukaKunci as $item)
                                                <tr class="hover:bg-amber-50/20 transition">
                                                    <td class="p-3.5 pl-4 whitespace-nowrap">
                                                        <p class="font-bold text-gray-800">{{ $item->user->name ?? '-' }}</p>
                                                        <p class="text-[10px] text-gray-400 font-mono mt-0.5">ID: {{ $item->user->id_pjlp ?? '-' }}</p>
                                                    </td>
                                                    <td class="p-3.5 whitespace-nowrap">
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 text-amber-800 font-bold text-[11px] rounded-lg border border-amber-200/60">
                                                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                            {{ \Carbon\Carbon::parse($item->tanggal_terlewat)->translatedFormat('d F Y') }}
                                                        </span>
                                                    </td>
                                                    <td class="p-3.5">
                                                        <p class="text-gray-700 leading-relaxed max-w-md font-medium text-[11px]">{{ $item->alasan }}</p>
                                                        @if($item->catatan_admin)
                                                            <div class="mt-1.5 p-2 bg-rose-50 rounded-lg border border-rose-100 text-[10px] text-rose-700 font-medium">
                                                                <span class="font-bold">Catatan Admin:</span> "{{ $item->catatan_admin }}"
                                                            </div>
                                                        @endif
                                                        <span class="text-[9px] text-gray-400 block mt-1">Diajukan: {{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y, H:i') }} WIB</span>
                                                    </td>
                                                    <td class="p-3.5 text-center whitespace-nowrap">
                                                        @if($item->status === 'pending')
                                                            <span class="px-2.5 py-1 bg-amber-100 text-amber-800 font-extrabold rounded-full text-[10px] border border-amber-200 inline-flex items-center gap-1 animate-pulse">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                                Pending
                                                            </span>
                                                        @elseif($item->status === 'disetujui')
                                                            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-extrabold rounded-full text-[10px] border border-emerald-200 inline-flex items-center gap-1">
                                                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                                Disetujui
                                                            </span>
                                                        @else
                                                            <span class="px-2.5 py-1 bg-rose-100 text-rose-800 font-extrabold rounded-full text-[10px] border border-rose-200 inline-flex items-center gap-1">
                                                                <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                Ditolak
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="p-3.5 text-center whitespace-nowrap pr-4">
                                                        @if($item->status === 'pending')
                                                            <div class="flex items-center justify-center gap-1.5">
                                                                <button type="button" @click="openApproveModal('{{ route('admin.kinerja.buka_kunci.approve', $item->id) }}', '{{ addslashes($item->user->name ?? '') }}', '{{ \Carbon\Carbon::parse($item->tanggal_terlewat)->translatedFormat('d F Y') }}')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] rounded-xl transition shadow-2xs flex items-center gap-1">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                                    Setujui
                                                                </button>

                                                                <button type="button" @click="openRejectModal('{{ $item->id }}', '{{ addslashes($item->user->name ?? '') }}', '{{ \Carbon\Carbon::parse($item->tanggal_terlewat)->translatedFormat('d F Y') }}')" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-[11px] rounded-xl transition shadow-2xs flex items-center gap-1">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                    Tolak
                                                                </button>
                                                            </div>
                                                        @else
                                                            <span class="text-[11px] text-gray-400 italic">Selesai Diverifikasi</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="p-8 text-center text-gray-400 italic">Tidak ada permohonan buka kunci pada periode ini.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- MOBILE ACCORDION VIEW (GROUPED PER PEGAWAI) -->
                                <div class="md:hidden p-3 space-y-3 bg-gray-50/50">
                                    @php
                                        $groupedBukaKunci = $pengajuanBukaKunci->groupBy('user_id');
                                    @endphp

                                    @forelse ($groupedBukaKunci as $userId => $items)
                                        @php
                                            $firstItem = $items->first();
                                            $userName = $firstItem->user->name ?? '-';
                                            $userIdPjlp = $firstItem->user->id_pjlp ?? '-';
                                            $pendingCount = $items->where('status', 'pending')->count();
                                            $totalCount = $items->count();
                                        @endphp

                                        <div class="border border-gray-200 bg-white rounded-2xl overflow-hidden shadow-2xs transition">
                                            <!-- Accordion Header -->
                                            <div class="p-3.5 flex items-center justify-between cursor-pointer bg-white hover:bg-gray-50/80 transition" @click="expandedBukaKunciUser = expandedBukaKunciUser === {{ $userId }} ? null : {{ $userId }}">
                                                <div class="flex-1 pr-2">
                                                    <div class="flex flex-wrap items-center gap-1.5">
                                                        <p class="font-extrabold text-gray-900 text-xs">{{ $userName }}</p>
                                                        <span class="text-[9px] font-mono text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200">ID: {{ $userIdPjlp }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2 mt-1.5">
                                                        <span class="text-[10px] text-gray-500 font-semibold">{{ $totalCount }} Pengajuan Buka Kunci</span>
                                                        @if($pendingCount > 0)
                                                            <span class="text-[9px] font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full border border-amber-200 animate-pulse">
                                                                {{ $pendingCount }} Menunggu
                                                            </span>
                                                        @else
                                                            <span class="text-[9px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-200">
                                                                Selesai
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <svg class="w-4 h-4 text-gray-400 transform transition-transform flex-shrink-0" :class="{'rotate-180': expandedBukaKunciUser === {{ $userId }}}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </div>

                                            <!-- Accordion Content List -->
                                            <div x-show="expandedBukaKunciUser === {{ $userId }}" x-cloak style="display: none;" class="border-t border-gray-100 bg-gray-50/80 p-3 space-y-3">
                                                @foreach($items as $item)
                                                    <div class="bg-white rounded-xl border border-gray-200 p-3 space-y-2.5 shadow-2xs">
                                                        <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-2">
                                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-200/60">
                                                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                                {{ \Carbon\Carbon::parse($item->tanggal_terlewat)->translatedFormat('d F Y') }}
                                                            </span>
                                                            @if($item->status === 'pending')
                                                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-extrabold rounded-full text-[10px] border border-amber-200">
                                                                    Pending
                                                                </span>
                                                            @elseif($item->status === 'disetujui')
                                                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-extrabold rounded-full text-[10px] border border-emerald-200">
                                                                    Disetujui
                                                                </span>
                                                            @else
                                                                <span class="px-2 py-0.5 bg-rose-100 text-rose-800 font-extrabold rounded-full text-[10px] border border-rose-200">
                                                                    Ditolak
                                                                </span>
                                                            @endif
                                                        </div>

                                                        <div class="space-y-1 text-xs">
                                                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Alasan Keterlambatan</span>
                                                            <p class="text-gray-700 bg-gray-50 p-2 rounded-lg border border-gray-100 text-[11px] leading-relaxed">{{ $item->alasan }}</p>
                                                            @if($item->catatan_admin)
                                                                <div class="p-2 bg-rose-50 rounded-lg border border-rose-100 text-[10px] text-rose-700 mt-1">
                                                                    <span class="font-bold">Catatan Admin:</span> "{{ $item->catatan_admin }}"
                                                                </div>
                                                            @endif
                                                            <span class="text-[9px] text-gray-400 block pt-0.5">Diajukan: {{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y, H:i') }} WIB</span>
                                                        </div>

                                                        @if($item->status === 'pending')
                                                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100">
                                                                <button type="button" @click="openApproveModal('{{ route('admin.kinerja.buka_kunci.approve', $item->id) }}', '{{ addslashes($userName) }}', '{{ \Carbon\Carbon::parse($item->tanggal_terlewat)->translatedFormat('d F Y') }}')" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] rounded-xl transition shadow-2xs flex items-center justify-center gap-1">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                                    Setujui
                                                                </button>

                                                                <button type="button" @click="openRejectModal('{{ $item->id }}', '{{ addslashes($userName) }}', '{{ \Carbon\Carbon::parse($item->tanggal_terlewat)->translatedFormat('d F Y') }}')" class="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-[11px] rounded-xl transition shadow-2xs flex items-center justify-center gap-1">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                    Tolak
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-6 text-center text-gray-400 text-xs italic bg-white rounded-xl border border-gray-200">Tidak ada permohonan buka kunci pada periode ini.</div>
                                    @endforelse
                                </div>

                                <!-- PAGINATION MODAL -->
                                <div class="p-4 border-t border-gray-100 bg-white">
                                    {{ $pengajuanBukaKunci->appends([
                                        'tab' => 'buka_kunci',
                                        'tipe_filter' => $tipeFilter ?? 'bulanan',
                                        'bulan' => $bulan ?? date('m'),
                                        'tahun' => $tahun ?? date('Y'),
                                        'tanggal' => $tanggal ?? date('Y-m-d'),
                                        'search' => $search ?? '',
                                        'status_validasi' => $statusValidasi ?? '',
                                        'search_buka_kunci' => request('search_buka_kunci'),
                                        'status_buka_kunci' => request('status_buka_kunci')
                                    ])->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL PERSETUJUAN PENGAJUAN BUKA KUNCI (SUB-MODAL APPROVE) -->
                <div x-show="showApproveModal" x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">

                    <div @click.away="showApproveModal = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                        class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-gray-100 space-y-4">

                        <div class="flex items-center gap-3 border-b border-gray-100 pb-3">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold shadow-2xs flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-gray-800">Setujui Permohonan Buka Kunci</h3>
                                <p class="text-[11px] text-gray-500">Konfirmasi persetujuan akses pengisian</p>
                            </div>
                        </div>

                        <p class="text-xs text-gray-600 leading-relaxed">Apakah Anda yakin ingin menyetujui permohonan buka kunci untuk pegawai <strong class="text-gray-900" x-text="approvePegawaiName"></strong> pada tanggal terkunci <strong class="text-amber-700" x-text="approveTanggal"></strong>?</p>

                        <form :action="approveActionUrl" method="POST" class="pt-2">
                            @csrf
                            <div class="flex justify-end gap-2 border-t border-gray-100 pt-3">
                                <button type="button" @click="showApproveModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold text-xs rounded-xl hover:bg-gray-200 transition">Batal</button>
                                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white font-bold text-xs rounded-xl hover:bg-emerald-700 transition shadow-xs flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Ya, Setujui
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- MODAL PENOLAKAN PENGAJUAN BUKA KUNCI (SUB-MODAL REJECT) -->
                <div x-show="showRejectModal" x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">

                    <div @click.away="showRejectModal = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                        class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-gray-100 space-y-4">

                        <div class="flex items-center gap-3 border-b border-gray-100 pb-3">
                            <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold shadow-2xs flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-gray-800">Tolak Permohonan Buka Kunci</h3>
                                <p class="text-[11px] text-gray-500">Konfirmasi penolakan permohonan</p>
                            </div>
                        </div>

                        <p class="text-xs text-gray-600 leading-relaxed">Berikan catatan alasan penolakan untuk pegawai <strong class="text-gray-900" x-text="rejectPegawaiName"></strong> pada tanggal terkunci <strong class="text-amber-700" x-text="rejectTanggal"></strong>.</p>

                        <form :action="rejectActionUrl" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Catatan Penolakan Admin</label>
                                <textarea name="catatan_admin" required rows="3" class="w-full border-gray-300 rounded-xl text-xs p-3 focus:ring-rose-500 focus:border-rose-500 bg-gray-50/50 focus:bg-white transition" placeholder="Contoh: Pengajuan ditolak karena alasan tidak sesuai dengan ketentuan."></textarea>
                            </div>

                            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                                <button type="button" @click="showRejectModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold text-xs rounded-xl hover:bg-gray-200 transition">Batal</button>
                                <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold text-xs rounded-xl hover:bg-rose-700 transition shadow-xs flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Kirim Penolakan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- BOTTOM NAVIGATION BAR (MOBILE ADMIN - 4 MENU) -->
            <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-[0_-4px_15px_rgba(0,0,0,0.05)] z-[80] px-2 py-2 flex justify-around items-center">
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
            Alpine.data('rekapKinerjaPage', () => ({
                expandedRow: null,
                expandedBukaKunciUser: null,
                tipeFilter: '{{ $tipeFilter ?? "bulanan" }}',

                showBukaKunciModal: '{{ $tab ?? "rekap" }}' === 'buka_kunci',

                showRejectModal: false,
                rejectPegawaiName: '',
                rejectTanggal: '',
                rejectActionUrl: '',

                showApproveModal: false,
                approvePegawaiName: '',
                approveTanggal: '',
                approveActionUrl: '',

                openApproveModal(actionUrl, name, tanggal) {
                    this.approvePegawaiName = name;
                    this.approveTanggal = tanggal;
                    this.approveActionUrl = actionUrl;
                    this.showApproveModal = true;
                },

                openRejectModal(id, name, tanggal) {
                    this.rejectPegawaiName = name;
                    this.rejectTanggal = tanggal;
                    this.rejectActionUrl = '/admin/kinerja/buka-kunci/' + id + '/reject';
                    this.showRejectModal = true;
                },

                fetchAndSwap(targetUrl) {
                    fetch(targetUrl)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');

                            // Swap tabel rekap kinerja utama
                            const newContent = doc.getElementById('area-tabel-kinerja');
                            if (newContent) {
                                document.getElementById('area-tabel-kinerja').innerHTML = newContent.innerHTML;
                            }

                            // SWAP JUGA ISI MODAL BUKA KUNCI AGAR SELALU SINKRON DENGAN PERIODE YANG DIFIILTER
                            const newModalContent = doc.getElementById('area-modal-buka-kunci');
                            if (newModalContent) {
                                document.getElementById('area-modal-buka-kunci').innerHTML = newModalContent.innerHTML;
                            }

                            window.history.pushState({}, '', targetUrl);
                        })
                        .catch(error => console.error('Gagal memuat rekap kinerja:', error));
                },

                fetchAndSwapModal(targetUrl) {
                    fetch(targetUrl)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newModalContent = doc.getElementById('area-modal-buka-kunci');
                            if (newModalContent) {
                                document.getElementById('area-modal-buka-kunci').innerHTML = newModalContent.innerHTML;
                            }
                            window.history.pushState({}, '', targetUrl);
                        })
                        .catch(error => console.error('Gagal memuat modal buka kunci:', error));
                },

                handlePaginationClick(event) {
                    const link = event.target.closest('a');
                    if (link && link.href && link.closest('.pagination, nav')) {
                        event.preventDefault();
                        this.fetchAndSwap(link.href);
                    }
                },

                handleModalPaginationClick(event) {
                    const link = event.target.closest('a');
                    if (link && link.href && link.closest('.pagination, nav')) {
                        event.preventDefault();
                        this.fetchAndSwapModal(link.href);
                    }
                },

                filterData() {
                    const form = document.getElementById('form-filter-kinerja');
                    const formData = new FormData(form);

                    if (this.tipeFilter === 'bulanan') {
                        formData.delete('tanggal');
                    } else {
                        formData.delete('bulan');
                        formData.delete('tahun');
                    }

                    const params = new URLSearchParams(formData).toString();
                    const targetUrl = form.action + '?' + params;
                    this.fetchAndSwap(targetUrl);
                },

                resetFilter() {
                    this.tipeFilter = 'bulanan';
                    const targetUrl = "{{ route('admin.kinerja.rekap') }}";
                    this.fetchAndSwap(targetUrl);
                }
            }));
        });
    </script>
</x-app-layout>