<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Validasi Pengajuan Cuti Bawahan') }}
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
    @endphp

    <div x-data="cutiPage()" class="py-4 sm:py-6 md:py-12 pb-32 md:pb-12 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            @if (session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <div><p class="font-bold text-sm">Berhasil!</p><p class="text-xs">{{ session('success') }}</p></div>
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
                <a href="{{ route('atasan.cuti.index') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center bg-blue-600 text-white shadow-sm flex items-center justify-center gap-2 transition">
                    <span>Pengajuan Cuti</span>
                    @if($countCutiPending > 0) <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-600 text-white shadow-sm">{{ $countCutiPending }}</span> @endif
                </a>
                <a href="{{ route('penilaian.index') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center text-gray-600 hover:bg-gray-50 flex items-center justify-center gap-2 transition">
                    <span>Penilaian Perilaku</span>
                    @if($countPenilaianPending > 0) <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-600 text-white shadow-sm">{{ $countPenilaianPending }}</span> @endif
                </a>
            </div>

            <!-- KARTU UTAMA -->
            <div id="area-tabel-cuti" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative" :class="{'opacity-60 pointer-events-none': isLoading}">
                
                <!-- LOADING SPINNER OVERLAY -->
                <div x-show="isLoading" x-cloak class="absolute inset-0 z-20 bg-white/50 backdrop-blur-[1px] flex items-center justify-center">
                    <div class="flex items-center gap-2 bg-gray-900 text-white px-4 py-2 rounded-xl shadow-lg text-xs font-semibold">
                        <svg class="animate-spin h-4 w-4 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memuat data...
                    </div>
                </div>

                <!-- FILTER TERSTRUKTUR -->
                <div class="p-4 sm:p-6 border-b border-gray-100 bg-gray-50/80 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm md:text-base">Daftar Pengajuan Cuti Bawahan</h3>
                        </div>
                        @if($pendingCount > 0)
                            <span class="px-3 py-1 bg-amber-100 text-amber-900 border border-amber-300 font-bold text-xs rounded-xl flex items-center gap-1.5 w-fit">
                                🔔 {{ $pendingCount }} Menunggu Persetujuan
                            </span>
                        @endif
                    </div>

                    <form id="form-filter-cuti" @submit.prevent="applyFilter" method="GET" action="{{ route('atasan.cuti.index') }}" class="space-y-3">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <div class="relative flex-1">
                                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama pegawai, alasan, jenis cuti..." class="w-full border-gray-300 rounded-xl text-xs sm:text-sm p-2.5 pl-9 focus:ring-blue-500">
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-blue-600 text-white text-xs sm:text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-blue-700 transition flex items-center justify-center gap-1">
                                    <span>Filter</span>
                                </button>
                                <button type="button" @click="resetFilter" class="bg-gray-200 text-gray-700 text-xs sm:text-sm font-bold px-3 py-2.5 rounded-xl hover:bg-gray-300 transition flex items-center justify-center">Reset</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-1">Pegawai</label>
                                <select name="pegawai_id" @change="applyFilter" class="w-full border-gray-300 rounded-xl text-xs p-2.5">
                                    <option value="all">-- Semua --</option>
                                    @foreach($daftarBawahan as $bawahan)
                                        <option value="{{ $bawahan->id }}" {{ $pegawaiId == $bawahan->id ? 'selected' : '' }}>{{ $bawahan->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-1">Status Validasi</label>
                                <select name="status_validasi" @change="applyFilter" class="w-full border-gray-300 rounded-xl text-xs p-2.5">
                                    <option value="all" {{ $statusValidasi == 'all' ? 'selected' : '' }}>-- Semua --</option>
                                    <option value="pending" {{ $statusValidasi == 'pending' ? 'selected' : '' }}>⌛ Menunggu Persetujuan</option>
                                    <option value="disetujui" {{ $statusValidasi == 'disetujui' ? 'selected' : '' }}>✅ Disetujui</option>
                                    <option value="ditolak" {{ $statusValidasi == 'ditolak' ? 'selected' : '' }}>❌ Ditolak</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-1">Bulan</label>
                                <select name="bulan" @change="applyFilter" class="w-full border-gray-300 rounded-xl text-xs p-2.5">
                                    @foreach(range(1, 12) as $m) 
                                        <option value="{{ sprintf('%02d', $m) }}" {{ $bulan == sprintf('%02d', $m) ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option> 
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-1">Tahun</label>
                                <select name="tahun" @change="applyFilter" class="w-full border-gray-300 rounded-xl text-xs p-2.5">
                                    @foreach(range(date('Y'), 2024) as $y) 
                                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option> 
                                    @endforeach
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
                                <th class="p-3 w-10 text-center border-b border-gray-200">
                                    <input type="checkbox" @change="toggleAll" :checked="isAllSelected" class="rounded border-gray-300 text-blue-600 cursor-pointer">
                                </th>
                                <th class="p-3 border-b border-gray-200">Pegawai</th>
                                <th class="p-3 border-b border-gray-200">Jenis & Periode Cuti</th>
                                <th class="p-3 border-b border-gray-200 min-w-[200px]">Alasan Cuti</th>
                                <th class="p-3 text-center border-b border-gray-200">Status / Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($cutiBawahan as $cuti)
                                @php
                                    $isPending = ($cuti->status_validasi === 'pending');
                                @endphp

                                <tr class="transition {{ $isPending ? 'bg-amber-50/70 hover:bg-amber-100/60 border-l-4 border-amber-400' : 'hover:bg-gray-50 border-l-4 border-transparent' }}">
                                    <td class="p-3 text-center align-middle">
                                        @if($isPending)
                                            <input type="checkbox" x-model="selectedIds" value="{{ $cuti->id }}" class="rounded border-gray-300 text-blue-600 cursor-pointer">
                                        @endif
                                    </td>
                                    <td class="p-3 align-middle whitespace-nowrap">
                                        <p class="font-bold text-gray-900">{{ $cuti->user->name }}</p>
                                        <p class="text-[11px] text-gray-500">Sisa Cuti: <strong class="text-blue-700 font-bold">{{ $cuti->user->sisa_cuti }} Hari</strong></p>
                                    </td>
                                    <td class="p-3 align-middle whitespace-nowrap">
                                        <span class="inline-block px-2.5 py-0.5 text-xs font-bold rounded-full bg-blue-100 text-blue-800">{{ $cuti->jenis_cuti }}</span>
                                        <p class="text-xs text-gray-800 font-semibold mt-1">
                                            {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->translatedFormat('d M Y') }}
                                            <span class="text-gray-400 mx-1">s/d</span>
                                            {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->translatedFormat('d M Y') }}
                                        </p>
                                        <p class="text-[11px] text-emerald-700 font-bold mt-0.5">⏱️ {{ $cuti->durasi_hari_kerja }} Hari Kerja</p>
                                    </td>
                                    <td class="p-3 align-middle">
                                        <p class="text-xs text-gray-700 italic">"{{ $cuti->alasan }}"</p>
                                        @if($cuti->file_bukti)
                                            <a href="{{ asset('storage/bukti_cuti/' . $cuti->file_bukti) }}" target="_blank" class="text-[11px] text-blue-600 font-bold hover:underline mt-1 inline-block">📄 Lihat Dokumen</a>
                                        @endif
                                        @if($cuti->status_validasi === 'ditolak' && $cuti->catatan_atasan)
                                            <p class="text-[11px] text-rose-600 mt-1 font-semibold">Catatan: {{ $cuti->catatan_atasan }}</p>
                                        @endif
                                    </td>
                                    <td class="p-3 align-middle text-center whitespace-nowrap">
                                        @if($isPending)
                                            <button type="button" @click="openReviewModal({{ json_encode($cuti) }}, '{{ addslashes($cuti->user->name) }}', '{{ $cuti->user->sisa_cuti }}')" class="px-3 py-1.5 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 transition shadow-sm animate-pulse">
                                                Tinjau Cuti
                                            </button>
                                        @elseif($cuti->status_validasi === 'disetujui')
                                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-emerald-100 text-emerald-800">✅ DISETUJUI</span>
                                        @elseif($cuti->status_validasi === 'ditolak')
                                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-rose-100 text-rose-800">❌ DITOLAK</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="p-8 text-center text-gray-400 text-sm">Tidak ada data pengajuan cuti.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- 2. TAMPILAN MOBILE -->
                <div class="md:hidden p-3 space-y-3">
                    @forelse ($cutiBawahan as $cuti)
                        @php
                            $isPending = ($cuti->status_validasi === 'pending');
                        @endphp

                        <div class="border {{ $isPending ? 'border-amber-300 bg-amber-50/60' : 'border-gray-200 bg-white' }} rounded-xl overflow-hidden shadow-sm transition">
                            <div class="p-3.5 flex items-center justify-between cursor-pointer" @click="expandedRow = expandedRow === '{{ $cuti->id }}' ? null : '{{ $cuti->id }}'">
                                <div class="flex items-start gap-2.5">
                                    @if($isPending)
                                        <input type="checkbox" x-model="selectedIds" value="{{ $cuti->id }}" @click.stop class="mt-1 rounded border-gray-300 text-blue-600">
                                    @endif
                                    <div>
                                        <p class="font-bold text-gray-900 text-sm">{{ $cuti->user->name }}</p>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <span class="px-2 py-0.5 text-[9px] font-extrabold rounded-full bg-blue-100 text-blue-800">{{ $cuti->jenis_cuti }}</span>
                                            <span class="text-xs text-gray-600 font-medium">{{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->translatedFormat('d M') }} - {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->translatedFormat('d M Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($isPending) <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> @endif
                                    <svg class="w-5 h-5 text-gray-400 transform transition-transform" :class="{'rotate-180': expandedRow === '{{ $cuti->id }}'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>

                            <div x-show="expandedRow === '{{ $cuti->id }}'" x-cloak x-collapse class="border-t {{ $isPending ? 'border-amber-200 bg-amber-50' : 'border-gray-100 bg-gray-50' }} p-3.5 space-y-3">
                                <div class="text-xs space-y-1">
                                    <p class="text-gray-500 font-semibold">Status Validasi Atasan:</p>
                                    @if($isPending) <span class="text-amber-700 font-bold">⌛ Menunggu Persetujuan</span>
                                    @elseif($cuti->status_validasi === 'disetujui') <span class="text-emerald-700 font-bold">✅ Disetujui</span>
                                    @else <span class="text-rose-700 font-bold">❌ Ditolak</span>
                                    @endif
                                </div>

                                <div class="p-2.5 rounded-xl bg-white border border-gray-200 text-xs space-y-1">
                                    <p class="text-gray-500 font-semibold">Alasan Pegawai:</p>
                                    <p class="text-gray-800 italic">"{{ $cuti->alasan }}"</p>
                                    <p class="text-blue-700 font-bold mt-1">Sisa Cuti Pegawai: {{ $cuti->user->sisa_cuti }} Hari</p>
                                    <p class="text-emerald-700 font-bold">Durasi Hari Kerja: {{ $cuti->durasi_hari_kerja }} Hari</p>
                                </div>

                                <div class="flex items-center justify-between pt-2">
                                    @if($cuti->file_bukti)
                                        <a href="{{ asset('storage/bukti_cuti/' . $cuti->file_bukti) }}" target="_blank" class="text-xs text-blue-600 font-bold underline">Lihat Lampiran</a>
                                    @else
                                        <span class="text-xs text-gray-400">Tanpa Lampiran</span>
                                    @endif

                                    @if($isPending)
                                        <button type="button" @click.stop="openReviewModal({{ json_encode($cuti) }}, '{{ addslashes($cuti->user->name) }}', '{{ $cuti->user->sisa_cuti }}')" class="px-3.5 py-1.5 bg-amber-500 text-white rounded-xl text-xs font-bold shadow-sm">Tinjau Cuti</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400 text-sm">Tidak ada data pengajuan cuti.</div>
                    @endforelse
                </div>

                <!-- PAGINATION -->
                @if(isset($cutiBawahan) && $cutiBawahan->total() > 0)
                    <div id="pagination-wrapper" class="p-4 border-t border-gray-100 bg-gray-50/50 flex justify-center sm:justify-end text-xs">
                        {{ $cutiBawahan->links() }}
                    </div>
                @endif
            </div>

            <!-- MODAL TINJAU CUTI -->
            <div x-show="openModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div x-show="openModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openModal = false"></div>
                
                <div x-show="openModal" x-transition class="relative bg-white rounded-2xl p-6 max-w-md w-full text-left shadow-2xl z-10 border border-gray-100">
                    <button type="button" @click="openModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <h3 class="text-base font-bold text-gray-800 mb-1">Tinjau Pengajuan Cuti</h3>
                    <p class="text-xs text-gray-500 mb-4">Pegawai: <strong class="text-gray-800" x-text="modalData.nama"></strong></p>
                    
                    <div class="mb-4 text-xs space-y-2 bg-gray-50 p-3.5 rounded-xl border border-gray-200">
                        <p><span class="text-gray-500 font-semibold">Jenis Cuti:</span> <br> <span class="font-bold text-blue-600" x-text="modalData.jenis_cuti"></span></p>
                        <p><span class="text-gray-500 font-semibold">Periode & Durasi:</span> <br> <span class="font-bold text-gray-800" x-text="modalData.periode + ' (' + modalData.durasi + ' Hari Kerja)'"></span></p>
                        <p><span class="text-gray-500 font-semibold">Sisa Cuti Pegawai:</span> <br> <span class="font-bold text-blue-700" x-text="modalData.sisa_cuti + ' Hari'"></span></p>
                        <p><span class="text-gray-500 font-semibold">Alasan Cuti:</span> <br> <span class="text-gray-800 italic" x-text="'&quot;' + modalData.alasan + '&quot;'"></span></p>
                        <p>
                            <span class="text-gray-500 font-semibold">Dokumen Lampiran:</span> <br>
                            <template x-if="modalData.file_bukti"><a :href="modalData.file_bukti" target="_blank" class="text-blue-600 font-bold hover:underline">📄 Lihat Dokumen</a></template>
                            <template x-if="!modalData.file_bukti"><span class="text-gray-400 italic">Tidak ada dokumen</span></template>
                        </p>
                    </div>

                    <form :action="modalData.actionUrl" method="POST" class="space-y-4" x-data="{ decisionStatus: 'disetujui' }">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Keputusan Atasan</label>
                            <select name="status_validasi" x-model="decisionStatus" class="w-full border-gray-300 rounded-xl text-sm p-3 focus:ring-blue-500" required>
                                <option value="disetujui">Setujui Cuti</option>
                                <option value="ditolak">Tolak Cuti</option>
                            </select>
                        </div>

                        <div x-show="decisionStatus === 'ditolak'">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Catatan Atasan <span class="text-rose-500">*</span></label>
                            <textarea name="catatan_atasan" rows="3" class="w-full border-gray-300 rounded-xl text-sm p-3 focus:ring-blue-500" placeholder="Jelaskan alasan penolakan..." :required="decisionStatus === 'ditolak'"></textarea>
                        </div>

                        <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100">
                            <button type="button" @click="openModal = false" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200">Batal</button>
                            <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white text-xs font-bold rounded-xl shadow hover:bg-blue-700">Simpan Keputusan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MODAL KONFIRMASI BULK ACTION -->
            <div x-show="openBulkModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div x-show="openBulkModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openBulkModal = false"></div>
                
                <div x-show="openBulkModal" x-transition class="relative bg-white rounded-2xl p-6 max-w-sm w-full text-left shadow-2xl z-10 border border-gray-100 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" :class="bulkStatus === 'disetujui' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'">
                            <template x-if="bulkStatus === 'disetujui'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="bulkStatus === 'ditolak'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </template>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Konfirmasi Aksi Massal</h3>
                            <p class="text-xs text-gray-500">Proses beberapa pengajuan cuti</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200 text-xs text-gray-700 leading-relaxed">
                        Apakah Anda yakin ingin memproses <strong class="text-gray-900" x-text="selectedIds.length + ' pengajuan'"></strong> cuti dengan keputusan status:
                        <span class="block mt-1.5 font-bold uppercase text-xs" :class="bulkStatus === 'disetujui' ? 'text-emerald-600' : 'text-rose-600'" x-text="bulkStatus === 'disetujui' ? '✅ DISETUJUI' : '❌ DITOLAK'"></span>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="openBulkModal = false" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200 transition">Batal</button>
                        <button type="button" @click="executeBulkSubmit()" class="px-4 py-2.5 text-white text-xs font-bold rounded-xl shadow transition" :class="bulkStatus === 'disetujui' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'">
                            Ya, Proses Sekarang
                        </button>
                    </div>
                </div>
            </div>

            <!-- BULK ACTION BAR -->
            <div x-show="selectedIds.length > 0" x-transition.translate.y.bottom class="fixed bottom-20 md:bottom-6 left-0 right-0 z-[95] flex justify-center px-4 pointer-events-none" x-cloak>
                <div class="bg-gray-900 text-white rounded-2xl shadow-2xl p-3.5 px-5 flex items-center justify-between gap-4 pointer-events-auto w-full max-w-lg border border-gray-700 relative">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-sm text-amber-400" x-text="selectedIds.length + ' Cuti Dipilih'"></span>
                        </div>
                        <p class="text-[11px] text-gray-300 mt-0.5">Pilih keputusan persetujuan untuk data ini</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="openBulkConfirm('ditolak')" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-500 rounded-xl text-xs font-bold transition shadow">Tolak</button>
                        <button type="button" @click="openBulkConfirm('disetujui')" class="px-3.5 py-2 bg-emerald-500 hover:bg-emerald-400 rounded-xl text-xs font-bold transition shadow">Setujui</button>
                        
                        <button type="button" @click="selectedIds = []" class="p-1.5 ml-1 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition" title="Batal Pilih">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form Tersembunyi Bulk Action -->
            <form id="bulk-form" method="POST" action="{{ route('atasan.cuti.bulk_validasi') }}" class="hidden">
                @csrf
                <input type="hidden" name="status_validasi" id="bulk-status">
            </form>

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
                <a href="{{ route('atasan.cuti.index') }}" class="flex flex-col items-center relative text-blue-600 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-[10px] mt-1">Cuti</span>
                    @if($countCutiPending > 0) <span class="absolute -top-1.5 -right-2 px-1.5 py-0.2 bg-rose-600 text-white text-[9px] font-extrabold rounded-full">{{ $countCutiPending }}</span> @endif
                </a>
                <a href="{{ route('penilaian.index') }}" class="flex flex-col items-center relative text-gray-500 hover:text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    <span class="text-[10px] mt-1">Perilaku</span>
                    @if($countPenilaianPending > 0) <span class="absolute -top-1.5 -right-2 px-1.5 py-0.2 bg-rose-600 text-white text-[9px] font-extrabold rounded-full">{{ $countPenilaianPending }}</span> @endif
                </a>
            </div>

        </div>
    </div>

    <!-- ALPINE JS LOGIC -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('cutiPage', () => ({
                expandedRow: null,
                openModal: false,
                modalData: {},
                selectedIds: [],
                allPendingIds: @json($allPendingIds),
                isLoading: false,

                openBulkModal: false,
                bulkStatus: '',

                init() {
                    // Delegation listener untuk pagination link agar tetap AJAX
                    document.addEventListener('click', (e) => {
                        const target = e.target.closest('#pagination-wrapper a');
                        if (target) {
                            e.preventDefault();
                            this.fetchData(target.href);
                        }
                    });
                },

                get isAllSelected() {
                    return this.allPendingIds.length > 0 && this.selectedIds.length === this.allPendingIds.length;
                },

                toggleAll() {
                    if (this.isAllSelected) {
                        this.selectedIds = [];
                    } else {
                        this.selectedIds = [...this.allPendingIds];
                    }
                },

                applyFilter() {
                    const form = document.getElementById('form-filter-cuti');
                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData);
                    const url = `${form.action}?${params.toString()}`;
                    
                    this.fetchData(url);
                },

                resetFilter() {
                    const form = document.getElementById('form-filter-cuti');
                    this.fetchData(form.action);
                },

                fetchData(url) {
                    this.isLoading = true;
                    this.selectedIds = []; // reset checked items saat berpindah/filter

                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newContainer = doc.getElementById('area-tabel-cuti');
                        
                        if (newContainer) {
                            document.getElementById('area-tabel-cuti').innerHTML = newContainer.innerHTML;
                        }

                        // Update URL browser tanpa re-load halaman
                        window.history.pushState({}, '', url);
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    })
                    .finally(() => {
                        this.isLoading = false;
                    });
                },

                openBulkConfirm(status) {
                    this.bulkStatus = status;
                    this.openBulkModal = true;
                },

                executeBulkSubmit() {
                    let form = document.getElementById('bulk-form');
                    form.querySelectorAll('input[name="cuti_ids[]"]').forEach(el => el.remove());

                    this.selectedIds.forEach(id => {
                        let input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'cuti_ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });

                    document.getElementById('bulk-status').value = this.bulkStatus;
                    form.submit();
                },

                openReviewModal(cuti, namaLengkap, sisaCuti) {
                    let tglMulai = new Date(cuti.tanggal_mulai).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'});
                    let tglSelesai = new Date(cuti.tanggal_selesai).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'});
                    
                    this.modalData = {
                        nama: namaLengkap,
                        jenis_cuti: cuti.jenis_cuti,
                        periode: tglMulai + ' s/d ' + tglSelesai,
                        durasi: cuti.durasi_hari_kerja || 0,
                        sisa_cuti: sisaCuti,
                        alasan: cuti.alasan || '-',
                        file_bukti: cuti.file_bukti ? '/storage/bukti_cuti/' + cuti.file_bukti : '',
                        actionUrl: '{{ route("atasan.cuti.validasi", ":id") }}'.replace(':id', cuti.id)
                    };
                    this.openModal = true;
                }
            }));
        });
    </script>
</x-app-layout>