<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Rekapitulasi Laporan Kinerja Pegawai') }}
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
                <form id="form-filter-kinerja" @submit.prevent="filterData" method="GET" action="{{ route('admin.kinerja.rekap') }}" class="space-y-4">
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
                                <option value="direvisi" {{ ($statusValidasi ?? request('status_validasi')) === 'direvisi' ? 'selected' : '' }}>Direvisi</option>
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
            <div id="area-tabel-kinerja" @click="handlePaginationClick($event)" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-100 bg-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 class="font-bold text-gray-800 text-sm md:text-base">Data Rekapitulasi Kinerja</h3>
                            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">
                                @if(isset($tipeFilter) && $tipeFilter === 'harian')
                                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                                @else
                                    {{ \Carbon\Carbon::createFromDate($tahun ?? date('Y'), $bulan ?? date('m'), 1)->translatedFormat('F Y') }}
                                @endif
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">Suku Dinas Ketahanan Pangan, Kelautan, dan Pertanian Jakarta Selatan</p>
                    </div>

                    @php
                        $pdfParams = [
                            'tipe_filter' => $tipeFilter ?? 'bulanan', 
                            'tanggal' => $tanggal ?? date('Y-m-d'), 
                            'bulan' => $bulan ?? date('m'), 
                            'tahun' => $tahun ?? date('Y'), 
                            'search' => $search ?? '',
                            'status_validasi' => $statusValidasi ?? ''
                        ];
                    @endphp
                    <a href="{{ route('admin.kinerja.rekap.pdf', $pdfParams) }}" target="_blank" class="px-4 py-2.5 bg-rose-600 text-white font-bold text-xs rounded-xl hover:bg-rose-700 transition shadow flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Unduh Rekap PDF
                    </a>
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
                                <th class="p-3 text-center text-rose-600">Revisi</th>
                                <th class="p-3 text-center text-amber-600">Menunggu</th>
                                <th class="p-3 text-center">Perilaku</th>
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
                                        @php
                                            $nilai = $row['nilai_perilaku'];
                                            if ($nilai >= 90) {
                                                $badgeClass = 'bg-emerald-100 text-emerald-800';
                                                $predikat = 'Sangat Baik';
                                            } elseif ($nilai >= 76) {
                                                $badgeClass = 'bg-blue-100 text-blue-800';
                                                $predikat = 'Baik';
                                            } elseif ($nilai >= 61) {
                                                $badgeClass = 'bg-amber-100 text-amber-800';
                                                $predikat = 'Cukup';
                                            } elseif ($nilai > 0) {
                                                $badgeClass = 'bg-rose-100 text-rose-800';
                                                $predikat = 'Kurang';
                                            } else {
                                                $badgeClass = 'bg-gray-100 text-gray-500';
                                                $predikat = 'Belum Dinilai';
                                            }
                                        @endphp
                                        <span class="px-2.5 py-1 font-bold rounded-full text-[10px] {{ $badgeClass }}">
                                            {{ $nilai > 0 ? $nilai . ' (' . $predikat . ')' : $predikat }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-center whitespace-nowrap">
                                        <a href="{{ route('admin.kinerja.detail', ['id' => $row['id'], 'tipe_filter' => $tipeFilter ?? 'bulanan', 'tanggal' => $tanggal, 'bulan' => $bulan, 'tahun' => $tahun, 'status_validasi' => $statusValidasi]) }}" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-sm text-[11px] inline-flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Periksa
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="p-8 text-center text-gray-400 italic">Belum ada data kinerja pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- TAMPILAN MOBILE -->
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
                                        <p class="text-[9px] text-gray-500 font-bold mb-1">Revisi</p>
                                        <p class="text-sm font-extrabold text-rose-600">{{ $row['laporan_ditolak'] }}</p>
                                    </div>
                                    <div class="bg-white p-2 rounded-lg border border-gray-100 shadow-sm text-center bg-amber-50/50">
                                        <p class="text-[9px] text-amber-600 font-bold mb-1">Pending</p>
                                        <p class="text-sm font-extrabold text-amber-600">{{ $row['laporan_pending'] }}</p>
                                    </div>
                                </div>

                                <a href="{{ route('admin.kinerja.detail', ['id' => $row['id'], 'tipe_filter' => $tipeFilter ?? 'bulanan', 'tanggal' => $tanggal, 'bulan' => $bulan, 'tahun' => $tahun, 'status_validasi' => $statusValidasi]) }}" class="w-full flex items-center justify-center gap-1.5 px-4 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-sm text-xs mt-2">
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

            <!-- BOTTOM NAVIGATION BAR (MOBILE ADMIN - 4 MENU) -->
            <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-[0_-4px_15px_rgba(0,0,0,0.05)] z-[90] px-2 py-2 flex justify-around items-center">
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
            Alpine.data('rekapKinerjaPage', () => ({
                expandedRow: null,
                tipeFilter: '{{ $tipeFilter ?? "bulanan" }}',

                fetchAndSwap(targetUrl) {
                    fetch(targetUrl)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newContent = doc.getElementById('area-tabel-kinerja');
                            if (newContent) {
                                document.getElementById('area-tabel-kinerja').innerHTML = newContent.innerHTML;
                            }
                            window.history.pushState({}, '', targetUrl);
                        })
                        .catch(error => console.error('Gagal memuat rekap kinerja:', error));
                },

                handlePaginationClick(event) {
                    const link = event.target.closest('a');
                    if (link && link.href && link.closest('.pagination, nav')) {
                        event.preventDefault();
                        this.fetchAndSwap(link.href);
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