<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Penilaian Perilaku Pegawai (Bulanan)') }}
        </h2>
    </x-slot>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    @php
        $atasanId = Auth::id();
        $bulanIni = (int) date('m');
        $tahunIni = (int) date('Y');

        $countLaporanPending = \App\Models\LaporanKinerja::whereHas('user', fn($q) => $q->where('atasan_id', $atasanId))->where('status_validasi', 'pending')->count();
        $countAbsensiPending = \App\Models\Absensi::whereHas('user', fn($q) => $q->where('atasan_id', $atasanId))->where('status_klarifikasi', 'pending')->count();
        $countCutiPending = \App\Models\Cuti::whereHas('user', fn($q) => $q->where('atasan_id', $atasanId))->where('status_validasi', 'pending')->count();
        
        $countPenilaianPending = class_exists(\App\Models\PenilaianPerilaku::class) 
            ? \App\Models\User::where('atasan_id', $atasanId)
                ->whereDoesntHave('penilaianPerilaku', fn($q) => $q->where('bulan', $bulanIni)->where('tahun', $tahunIni))
                ->count()
            : 0;

        $initialData = $bawahans->map(function($bawahan) use ($penilaians) {
            $p = $penilaians->get($bawahan->id);
            return [
                'user_id' => $bawahan->id,
                'name' => $bawahan->name,
                'id_pjlp' => $bawahan->id_pjlp ?? '-',
                'jenis_pekerjaan' => $bawahan->jenis_pekerjaan ?? '-',
                'nilai_kehadiran' => $p ? (float)$p->nilai_kehadiran : 0,
                'nilai_tanggung_jawab' => $p ? (float)$p->nilai_tanggung_jawab : 0,
                'nilai_kepatuhan' => $p ? (float)$p->nilai_kepatuhan : 0,
                'is_saved' => $p ? true : false,
                'is_loading' => false,
                'is_dirty' => false,
            ];
        })->values();
    @endphp

    <div class="py-4 sm:py-6 md:py-12 pb-32 md:pb-12 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- TAB NAVIGASI DESKTOP -->
            <div class="hidden sm:flex bg-white p-2 rounded-2xl shadow-sm border border-gray-100 space-x-1">
                <a href="{{ route('atasan.laporan.index') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center text-gray-600 hover:bg-gray-50 flex items-center justify-center gap-2 transition">
                    <span>Validasi Laporan</span>
                    @if($countLaporanPending > 0) <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-600 text-white shadow-sm">{{ $countLaporanPending }}</span> @endif
                </a>
                <a href="{{ route('atasan.absensi.index') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center text-gray-600 hover:bg-gray-50 flex items-center justify-center gap-2 transition">
                    <span>Klarifikasi Absensi</span>
                    @if($countAbsensiPending > 0) <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-600 text-white shadow-sm">{{ $countAbsensiPending }}</span> @endif
                </a>
                <a href="{{ route('atasan.cuti.index') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center text-gray-600 hover:bg-gray-50 flex items-center justify-center gap-2 transition">
                    <span>Pengajuan Cuti</span>
                    @if($countCutiPending > 0) <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-600 text-white shadow-sm">{{ $countCutiPending }}</span> @endif
                </a>
                <a href="{{ route('atasan.penilaian.index') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center bg-blue-600 text-white shadow-sm flex items-center justify-center gap-2 transition">
                    <span>Penilaian Perilaku</span>
                    @if($countPenilaianPending > 0) <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-600 text-white shadow-sm">{{ $countPenilaianPending }}</span> @endif
                </a>
            </div>

            <!-- CONTAINER PENILAIAN PERILAKU -->
            <div id="area-tabel-penilaian" x-data="penilaianPage(@js($initialData), {{ $bulan }}, {{ $tahun }})" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative" :class="{'opacity-60 pointer-events-none': isLoading}">
                
                <!-- LOADING OVERLAY -->
                <div x-show="isLoading" x-cloak class="absolute inset-0 z-20 bg-white/50 backdrop-blur-[1px] flex items-center justify-center">
                    <div class="flex items-center gap-2 bg-gray-900 text-white px-4 py-2 rounded-xl shadow-lg text-xs font-semibold">
                        <svg class="animate-spin h-4 w-4 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memuat data...
                    </div>
                </div>

                <!-- HEADER & FILTER INLINE TERSTRUKTUR -->
                <div class="p-4 sm:p-6 border-b border-gray-100 bg-gray-50 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-gray-800 text-sm md:text-base">Daftar Penilaian Perilaku Bawahan</h3>
                        <p class="text-xs text-gray-500">Input dan kelola nilai kinerja bulanan pegawai bawahan (skala 0 - 100).</p>
                    </div>
                    
                    <form id="form-filter-penilaian" action="{{ route('atasan.penilaian.index') }}" @submit.prevent="applyFilter" class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
                        <div class="relative w-full sm:w-48">
                            <input type="text" id="filter_search" name="search" value="{{ $search }}" @input.debounce.500ms="applyFilter" placeholder="Cari nama, ID PJLP..." class="border-gray-300 rounded-xl text-xs p-2.5 pl-8 w-full focus:ring-blue-500 focus:border-blue-500">
                            <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>

                        <!-- DROPDOWN FILTER STATUS PENILAIAN -->
                        <select id="filter_status" name="status" @change="applyFilter" class="border-gray-300 rounded-xl text-xs p-2.5 w-full sm:w-36 focus:ring-blue-500 focus:border-blue-500">
                            <option value="semua" {{ $status == 'semua' ? 'selected' : '' }}>Semua Status</option>
                            <option value="belum" {{ $status == 'belum' ? 'selected' : '' }}>Belum Dinilai</option>
                            <option value="sudah" {{ $status == 'sudah' ? 'selected' : '' }}>Sudah Dinilai</option>
                        </select>

                        <select id="filter_bulan" name="bulan" @change="applyFilter" class="border-gray-300 rounded-xl text-xs p-2.5 w-full sm:w-36 focus:ring-blue-500 focus:border-blue-500">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>

                        <select id="filter_tahun" name="tahun" @change="applyFilter" class="border-gray-300 rounded-xl text-xs p-2.5 w-full sm:w-28 focus:ring-blue-500 focus:border-blue-500">
                            @foreach(range(date('Y') - 1, date('Y') + 1) as $y)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <!-- TAMPILAN DESKTOP (TABEL) -->
                <div class="hidden md:block overflow-x-auto p-4">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <!-- ROW MASTER ISI CEPAT DESKTOP -->
                            <tr class="bg-blue-50/70 border-b border-blue-100 text-blue-900 font-bold">
                                <td colspan="3" class="p-3 text-right text-xs">
                                    <span class="flex items-center justify-end gap-1.5">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Isi Cepat Per Halaman:
                                    </span>
                                </td>
                                <td class="p-2 text-center">
                                    <input type="number" min="0" max="100" x-model.number="bulkKehadiran" @input="clampBulk('bulkKehadiran')" placeholder="0" class="w-16 text-center text-xs rounded-xl border-blue-200 focus:ring-blue-500 p-1.5">
                                </td>
                                <td class="p-2 text-center">
                                    <input type="number" min="0" max="100" x-model.number="bulkTanggungJawab" @input="clampBulk('bulkTanggungJawab')" placeholder="0" class="w-16 text-center text-xs rounded-xl border-blue-200 focus:ring-blue-500 p-1.5">
                                </td>
                                <td class="p-2 text-center">
                                    <input type="number" min="0" max="100" x-model.number="bulkKepatuhan" @input="clampBulk('bulkKepatuhan')" placeholder="0" class="w-16 text-center text-xs rounded-xl border-blue-200 focus:ring-blue-500 p-1.5">
                                </td>
                                <td colspan="2" class="p-2">
                                    <button type="button" @click="applyBulk()" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                                        Terapkan
                                    </button>
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" @click="confirmSaveBulk()" :disabled="isBulkLoading" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm disabled:opacity-50">
                                        <span x-show="!isBulkLoading">Simpan Semua</span>
                                        <span x-show="isBulkLoading" x-cloak>Proses...</span>
                                    </button>
                                </td>
                            </tr>

                            <!-- HEADER TABEL -->
                            <tr class="bg-gray-100 border-b border-gray-200 text-gray-600 uppercase font-bold text-[10px] tracking-wider">
                                <th class="p-3 text-center" width="4%">No</th>
                                <th class="p-3" width="12%">ID PJLP</th>
                                <th class="p-3" width="22%">Nama Pegawai</th>
                                <th class="p-3 text-center" width="12%">Disiplin Kehadiran</th>
                                <th class="p-3 text-center" width="14%">Tanggung Jawab</th>
                                <th class="p-3 text-center" width="12%">Kepatuhan</th>
                                <th class="p-3 text-center" width="8%">Rata-Rata</th>
                                <th class="p-3 text-center" width="10%">Kategori</th>
                                <th class="p-3 text-center" width="6%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(item, index) in items" :key="item.user_id">
                                <tr class="hover:bg-gray-50/80 transition" :class="{'bg-amber-50/30': item.is_dirty}">
                                    <td class="p-3 text-center font-bold text-gray-500" x-text="(currentPage - 1) * perPage + index + 1"></td>
                                    <td class="p-3 font-mono font-bold text-gray-700" x-text="item.id_pjlp"></td>
                                    <td class="p-3 font-bold text-gray-800">
                                        <div x-text="item.name"></div>
                                        <div class="text-[10px] font-normal text-gray-500" x-text="item.jenis_pekerjaan"></div>
                                    </td>
                                    
                                    <td class="p-3 text-center">
                                        <input type="number" min="0" max="100" x-model.number="item.nilai_kehadiran" @input="clampScore(item, 'nilai_kehadiran')" class="w-16 text-center text-xs font-bold rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 p-2">
                                    </td>

                                    <td class="p-3 text-center">
                                        <input type="number" min="0" max="100" x-model.number="item.nilai_tanggung_jawab" @input="clampScore(item, 'nilai_tanggung_jawab')" class="w-16 text-center text-xs font-bold rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 p-2">
                                    </td>

                                    <td class="p-3 text-center">
                                        <input type="number" min="0" max="100" x-model.number="item.nilai_kepatuhan" @input="clampScore(item, 'nilai_kepatuhan')" class="w-16 text-center text-xs font-bold rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 p-2">
                                    </td>

                                    <td class="p-3 text-center font-extrabold text-xs text-blue-700" x-text="calcRataRata(item)"></td>

                                    <td class="p-3 text-center">
                                        <span class="px-2.5 py-1 font-extrabold rounded-full text-[10px]" :class="getBadgeClass(calcKategori(calcRataRata(item)))" x-text="calcKategori(calcRataRata(item))"></span>
                                    </td>

                                    <td class="p-3 text-center">
                                        <button type="button" @click="saveSingle(item)" :disabled="item.is_loading" class="p-2 rounded-xl transition cursor-pointer" :class="item.is_dirty ? 'bg-blue-600 text-white hover:bg-blue-700 shadow-sm' : 'bg-gray-100 text-gray-400 hover:bg-gray-200'">
                                            <svg x-show="!item.is_loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <svg x-show="item.is_loading" x-cloak class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <template x-if="items.length === 0">
                                <tr>
                                    <td colspan="9" class="p-8 text-center text-gray-400 italic">
                                        Tidak ada data pegawai bawahan yang ditemukan.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- TAMPILAN MOBILE (ACCORDION CARDS) -->
                <div class="md:hidden p-3 space-y-3 bg-gray-50/50">
                    <div class="bg-blue-50/80 rounded-xl border border-blue-200 p-4 space-y-3 shadow-sm">
                        <div class="flex justify-between items-center cursor-pointer" @click="showMobileBulk = !showMobileBulk">
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 bg-blue-600 text-white rounded-xl shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </span>
                                <h4 class="font-extrabold text-xs text-blue-900">Fitur Isi Cepat (Mobile)</h4>
                            </div>
                            <svg class="w-4 h-4 text-blue-600 transform transition-transform" :class="{'rotate-180': showMobileBulk}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>

                        <div x-show="showMobileBulk" x-cloak class="space-y-3 pt-2">
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <span class="text-[10px] font-bold text-gray-600 block mb-1">Kehadiran</span>
                                    <input type="number" min="0" max="100" x-model.number="bulkKehadiran" @input="clampBulk('bulkKehadiran')" class="w-full text-xs rounded-xl border-gray-300 text-center p-2">
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-gray-600 block mb-1">Tanggung Jawab</span>
                                    <input type="number" min="0" max="100" x-model.number="bulkTanggungJawab" @input="clampBulk('bulkTanggungJawab')" class="w-full text-xs rounded-xl border-gray-300 text-center p-2">
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-gray-600 block mb-1">Kepatuhan</span>
                                    <input type="number" min="0" max="100" x-model.number="bulkKepatuhan" @input="clampBulk('bulkKepatuhan')" class="w-full text-xs rounded-xl border-gray-300 text-center p-2">
                                </div>
                            </div>
                            <button type="button" @click="applyBulk()" class="w-full py-2.5 bg-blue-600 text-white font-bold text-xs rounded-xl hover:bg-blue-700 transition shadow-sm">
                                Terapkan ke Semua Pegawai
                            </button>
                        </div>
                    </div>

                    <template x-for="(item, index) in items" :key="item.user_id">
                        <div class="border border-gray-200 bg-white rounded-xl overflow-hidden shadow-sm transition" :class="{'border-amber-300 ring-1 ring-amber-300': item.is_dirty}">
                            
                            <div class="p-3.5 flex items-start justify-between cursor-pointer" @click="expandedRow = expandedRow === index ? null : index">
                                <div class="flex-1 pr-2">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-[10px] font-mono font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-lg border border-gray-200" x-text="'#' + ((currentPage - 1) * perPage + index + 1)"></span>
                                        <h5 class="font-bold text-gray-800 text-xs" x-text="item.name"></h5>
                                    </div>
                                    <p class="text-[11px] text-gray-500 font-medium">ID: <span class="font-mono font-bold" x-text="item.id_pjlp"></span></p>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="px-2 py-0.5 font-bold rounded-full text-[9px]" :class="getBadgeClass(calcKategori(calcRataRata(item)))" x-text="calcKategori(calcRataRata(item))"></span>
                                    <svg class="w-4 h-4 text-gray-400 transform transition-transform" :class="{'rotate-180': expandedRow === index}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>

                            <div x-show="expandedRow === index" x-cloak class="border-t border-gray-100 bg-gray-50/80 p-3.5 space-y-3">
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="text-[10px] font-bold text-gray-600 uppercase block mb-1">Kehadiran</label>
                                        <input type="number" min="0" max="100" x-model.number="item.nilai_kehadiran" @input="clampScore(item, 'nilai_kehadiran')" class="w-full text-center text-xs font-bold rounded-xl border-gray-300 p-2">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-bold text-gray-600 uppercase block mb-1">Tanggung Jawab</label>
                                        <input type="number" min="0" max="100" x-model.number="item.nilai_tanggung_jawab" @input="clampScore(item, 'nilai_tanggung_jawab')" class="w-full text-center text-xs font-bold rounded-xl border-gray-300 p-2">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-bold text-gray-600 uppercase block mb-1">Kepatuhan</label>
                                        <input type="number" min="0" max="100" x-model.number="item.nilai_kepatuhan" @input="clampScore(item, 'nilai_kepatuhan')" class="w-full text-center text-xs font-bold rounded-xl border-gray-300 p-2">
                                    </div>
                                </div>

                                <div class="flex justify-between items-center bg-white p-3 rounded-xl border border-gray-200">
                                    <div>
                                        <span class="text-[10px] text-gray-400 uppercase font-bold block">Nilai Rata-Rata</span>
                                        <span class="text-sm font-extrabold text-blue-700" x-text="calcRataRata(item)"></span>
                                    </div>

                                    <button type="button" 
                                            @click="saveSingle(item)" 
                                            :disabled="item.is_loading || (!item.is_dirty && item.is_saved)" 
                                            class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-sm"
                                            :class="{
                                                'bg-blue-600 text-white hover:bg-blue-700': item.is_dirty,
                                                'bg-emerald-100 text-emerald-800 border border-emerald-200': !item.is_dirty && item.is_saved,
                                                'bg-gray-100 text-gray-400': !item.is_dirty && !item.is_saved
                                            }">
                                        
                                        <template x-if="item.is_loading">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                Menyimpan...
                                            </span>
                                        </template>

                                        <template x-if="!item.is_loading && !item.is_dirty && item.is_saved">
                                            <span class="flex items-center gap-1 text-emerald-800 font-extrabold">
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                Tersimpan
                                            </span>
                                        </template>

                                        <template x-if="!item.is_loading && (item.is_dirty || !item.is_saved)">
                                            <span>Simpan Nilai</span>
                                        </template>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </template>
                </div>

                <!-- PAGINATION LARAVEL -->
                @if(isset($bawahans) && $bawahans->total() > 0)
                    <div id="pagination-wrapper" class="p-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600">
                        <div>
                            Menampilkan <span class="font-semibold">{{ $bawahans->firstItem() ?? 0 }}</span> s/d <span class="font-semibold">{{ $bawahans->lastItem() ?? 0 }}</span> dari <span class="font-semibold">{{ $bawahans->total() }}</span> pegawai
                        </div>
                        <div>
                            {{ $bawahans->links() }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- STICKY BOTTOM BAR MOBILE UNTUK SIMPAN SEMUA -->
            <div x-show="hasDirtyItems" x-cloak class="md:hidden fixed bottom-20 left-4 right-4 z-[85] bg-gray-900/95 backdrop-blur-md text-white p-3.5 rounded-2xl shadow-2xl flex justify-between items-center border border-gray-700">
                <span class="text-xs font-medium text-amber-300">Ada nilai yang belum disimpan</span>
                <button type="button" @click="confirmSaveBulk()" :disabled="isBulkLoading" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl transition shadow">
                    Simpan Semua
                </button>
            </div>

            <!-- BOTTOM NAVIGATION BAR MOBILE -->
            <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-xl z-[90] px-2 py-2 flex justify-around items-center">
                <a href="{{ route('atasan.laporan.index') }}" class="flex flex-col items-center relative text-gray-500 hover:text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-[10px] mt-1">Laporan</span>
                    @if($countLaporanPending > 0) <span class="absolute -top-1.5 -right-2 px-1.5 py-0.2 bg-rose-600 text-white text-[9px] font-extrabold rounded-full">{{ $countLaporanPending }}</span> @endif
                </a>
                <a href="{{ route('atasan.absensi.index') }}" class="flex flex-col items-center relative text-gray-500 hover:text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-[10px] mt-1">Absensi</span>
                    @if($countAbsensiPending > 0) <span class="absolute -top-1.5 -right-2 px-1.5 py-0.2 bg-rose-600 text-white text-[9px] font-extrabold rounded-full">{{ $countAbsensiPending }}</span> @endif
                </a>
                <a href="{{ route('atasan.cuti.index') }}" class="flex flex-col items-center relative text-gray-500 hover:text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-[10px] mt-1">Cuti</span>
                    @if($countCutiPending > 0) <span class="absolute -top-1.5 -right-2 px-1.5 py-0.2 bg-rose-600 text-white text-[9px] font-extrabold rounded-full">{{ $countCutiPending }}</span> @endif
                </a>
                <a href="{{ route('atasan.penilaian.index') }}" class="flex flex-col items-center relative text-blue-600 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    <span class="text-[10px] mt-1">Perilaku</span>
                    @if($countPenilaianPending > 0) <span class="absolute -top-1.5 -right-2 px-1.5 py-0.2 bg-rose-600 text-white text-[9px] font-extrabold rounded-full">{{ $countPenilaianPending }}</span> @endif
                </a>
            </div>

        </div>
    </div>

    <!-- SWEETALERT2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- LOGIKA ALPINE.JS REAKTIF MURNI (TANPA JQUERY) -->
    <script>
        // Event listener tunggal untuk delegation link pagination di level document
        if (!window.paginationListenerAttached) {
            window.paginationListenerAttached = true;
            document.addEventListener('click', (e) => {
                const target = e.target.closest('#pagination-wrapper a');
                if (target) {
                    e.preventDefault();
                    const container = document.getElementById('area-tabel-penilaian');
                    if (container && container._x_dataStack) {
                        const alpineComponent = container._x_dataStack[0];
                        if (alpineComponent && alpineComponent.checkAndFetch) {
                            alpineComponent.checkAndFetch(target.href);
                        }
                    }
                }
            });
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('penilaianPage', (initialItems, bulan, tahun) => ({
                items: initialItems,
                bulan: bulan,
                tahun: tahun,
                currentPage: {{ $bawahans->currentPage() }},
                perPage: {{ $bawahans->perPage() }},
                expandedRow: null,
                showMobileBulk: false,
                isBulkLoading: false,
                isLoading: false,

                bulkKehadiran: null,
                bulkTanggungJawab: null,
                bulkKepatuhan: null,

                get hasDirtyItems() {
                    return this.items.some(item => item.is_dirty);
                },

                clampScore(item, field) {
                    if (item[field] === '' || item[field] === null) return;
                    let val = parseInt(item[field]);
                    if (isNaN(val)) val = 0;
                    if (val > 100) val = 100;
                    if (val < 0) val = 0;
                    item[field] = val;
                    item.is_dirty = true;
                },

                clampBulk(field) {
                    if (this[field] === '' || this[field] === null) return;
                    let val = parseInt(this[field]);
                    if (isNaN(val)) val = 0;
                    if (val > 100) val = 100;
                    if (val < 0) val = 0;
                    this[field] = val;
                },

                calcRataRata(item) {
                    const h = parseFloat(item.nilai_kehadiran) || 0;
                    const t = parseFloat(item.nilai_tanggung_jawab) || 0;
                    const k = parseFloat(item.nilai_kepatuhan) || 0;
                    return ((h + t + k) / 3).toFixed(2);
                },

                calcKategori(rataRata) {
                    const val = parseFloat(rataRata);
                    if (val >= 90) return 'Sangat Baik';
                    if (val >= 76) return 'Baik';
                    if (val >= 61) return 'Cukup';
                    return 'Kurang';
                },

                getBadgeClass(kategori) {
                    switch (kategori) {
                        case 'Sangat Baik': return 'bg-emerald-100 text-emerald-800';
                        case 'Baik':        return 'bg-blue-100 text-blue-800';
                        case 'Cukup':       return 'bg-amber-100 text-amber-800';
                        default:            return 'bg-rose-100 text-rose-800';
                    }
                },

                applyBulk() {
                    this.items.forEach(item => {
                        if (this.bulkKehadiran !== null && this.bulkKehadiran !== '') item.nilai_kehadiran = this.bulkKehadiran;
                        if (this.bulkTanggungJawab !== null && this.bulkTanggungJawab !== '') item.nilai_tanggung_jawab = this.bulkTanggungJawab;
                        if (this.bulkKepatuhan !== null && this.bulkKepatuhan !== '') item.nilai_kepatuhan = this.bulkKepatuhan;
                        item.is_dirty = true;
                    });
                },

                applyFilter() {
                    const searchEl = document.getElementById('filter_search');
                    const statusEl = document.getElementById('filter_status');
                    const bulanEl = document.getElementById('filter_bulan');
                    const tahunEl = document.getElementById('filter_tahun');

                    const search = searchEl ? searchEl.value : '';
                    const status = statusEl ? statusEl.value : 'semua';
                    const bulan = bulanEl ? bulanEl.value : this.bulan;
                    const tahun = tahunEl ? tahunEl.value : this.tahun;

                    const baseUrl = "{{ route('atasan.penilaian.index') }}";
                    const params = new URLSearchParams({
                        bulan: bulan,
                        tahun: tahun,
                        status: status,
                        search: search
                    });

                    const url = baseUrl + '?' + params.toString();
                    this.checkAndFetch(url);
                },

                checkAndFetch(url) {
                    if (this.hasDirtyItems) {
                        Swal.fire({
                            title: 'Perubahan Belum Disimpan!',
                            text: 'Ada nilai yang telah diubah namun belum disimpan. Yakin ingin berpindah/memfilter?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc2626',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: 'Ya, Abaikan Perubahan',
                            cancelButtonText: 'Batal',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.fetchData(url);
                            }
                        });
                    } else {
                        this.fetchData(url);
                    }
                },

                fetchData(url) {
                    this.isLoading = true;

                    fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(res => res.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newEl = doc.getElementById('area-tabel-penilaian');

                        if (newEl) {
                            const oldEl = document.getElementById('area-tabel-penilaian');
                            if (oldEl) {
                                // Simpan posisi kursor/fokus input pencarian jika sedang aktif
                                const activeId = document.activeElement ? document.activeElement.id : null;
                                const cursorStart = document.activeElement?.selectionStart ?? null;
                                const cursorEnd = document.activeElement?.selectionEnd ?? null;

                                if (window.Alpine) {
                                    Alpine.destroyTree(oldEl);
                                }

                                const importedEl = document.importNode(newEl, true);
                                oldEl.replaceWith(importedEl);
                                window.history.pushState({}, '', url);

                                if (window.Alpine) {
                                    Alpine.initTree(importedEl);
                                }

                                // Kembalikan fokus ke input pencarian
                                if (activeId) {
                                    const restoredEl = document.getElementById(activeId);
                                    if (restoredEl) {
                                        restoredEl.focus();
                                        if (cursorStart !== null && cursorEnd !== null && restoredEl.setSelectionRange) {
                                            restoredEl.setSelectionRange(cursorStart, cursorEnd);
                                        }
                                    }
                                }
                            }
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Error', 'Gagal memuat data penilaian.', 'error');
                    })
                    .finally(() => {
                        this.isLoading = false;
                    });
                },

                confirmSaveBulk() {
                    Swal.fire({
                        title: 'Konfirmasi Penilaian',
                        text: "Apakah Anda yakin ingin menyimpan seluruh penilaian pegawai di halaman ini?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#059669',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Simpan Semua!',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.saveBulk();
                        }
                    });
                },

                saveSingle(item) {
                    item.is_loading = true;
                    fetch("{{ route('atasan.penilaian.store_single') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            user_id: item.user_id,
                            bulan: this.bulan,
                            tahun: this.tahun,
                            nilai_kehadiran: item.nilai_kehadiran,
                            nilai_tanggung_jawab: item.nilai_tanggung_jawab,
                            nilai_kepatuhan: item.nilai_kepatuhan,
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        item.is_loading = false;
                        if (data.success) {
                            item.is_dirty = false;
                            item.is_saved = true;
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Penilaian pegawai ' + item.name + ' berhasil disimpan.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Gagal', data.message || 'Periksa inputan Anda', 'error');
                        }
                    })
                    .catch(err => {
                        item.is_loading = false;
                        console.error(err);
                        Swal.fire('Error', 'Terjadi kesalahan koneksi.', 'error');
                    });
                },

                saveBulk() {
                    this.isBulkLoading = true;
                    const payload = this.items.map(item => ({
                        user_id: item.user_id,
                        nilai_kehadiran: item.nilai_kehadiran,
                        nilai_tanggung_jawab: item.nilai_tanggung_jawab,
                        nilai_kepatuhan: item.nilai_kepatuhan,
                    }));

                    fetch("{{ route('atasan.penilaian.store_bulk') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            bulan: this.bulan,
                            tahun: this.tahun,
                            penilaians: payload
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isBulkLoading = false;
                        if (data.success) {
                            this.items.forEach(item => {
                                item.is_dirty = false;
                                item.is_saved = true;
                            });
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                confirmButtonColor: '#2563eb'
                            });
                        } else {
                            Swal.fire('Gagal', 'Gagal menyimpan penilaian sekaligus.', 'error');
                        }
                    })
                    .catch(err => {
                        this.isBulkLoading = false;
                        console.error(err);
                        Swal.fire('Error', 'Terjadi kesalahan koneksi.', 'error');
                    });
                }
            }));
        });
    </script>
</x-app-layout>