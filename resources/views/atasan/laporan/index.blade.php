<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Validasi Laporan Kinerja Pegawai') }}
        </h2>
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </x-slot>

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

    <div id="wrapper-laporan" x-data="laporanAtasanData({{ json_encode($allPendingIds ?? []) }})" class="py-6 md:py-12 pb-28 md:pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            @if (session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded-xl shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <div>
                        <p class="font-bold text-sm">Berhasil!</p>
                        <p class="text-xs">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 rounded-xl shadow-sm">
                    <ul class="list-disc ml-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- TAB DESKTOP -->
            <div class="hidden sm:flex bg-white p-2 rounded-2xl shadow-sm border border-gray-100 space-x-1">
                <a href="{{ route('atasan.laporan.index') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center bg-blue-600 text-white shadow-sm flex items-center justify-center gap-2">
                    <span>Validasi Laporan</span>
                    @if($countLaporanPending > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-600 text-white shadow-sm">{{ $countLaporanPending }}</span>
                    @endif
                </a>
                <a href="{{ route('atasan.absensi.index') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center text-gray-600 hover:bg-gray-50 flex items-center justify-center gap-2">
                    <span>Klarifikasi Absensi</span>
                    @if($countAbsensiPending > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-600 text-white shadow-sm">{{ $countAbsensiPending }}</span>
                    @endif
                </a>
                <a href="{{ route('atasan.cuti.index') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center text-gray-600 hover:bg-gray-50 flex items-center justify-center gap-2">
                    <span>Pengajuan Cuti</span>
                    @if($countCutiPending > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-600 text-white shadow-sm">{{ $countCutiPending }}</span>
                    @endif
                </a>
                <a href="{{ route('penilaian.index') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center text-gray-600 hover:bg-gray-50 flex items-center justify-center gap-2">
                    <span>Penilaian Perilaku</span>
                    @if($countPenilaianPending > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-600 text-white shadow-sm">{{ $countPenilaianPending }}</span>
                    @endif
                </a>
            </div>

            <!-- FORM BULK ACTION & KONTEN TABEL -->
            <form id="formBulkValidasi" action="{{ route('atasan.laporan.bulkValidasi') }}" method="POST">
                @csrf
                <div id="area-tabel-laporan" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-opacity duration-200">
                    
                    <!-- HEADER & FILTER -->
                    <div class="p-4 sm:p-6 border-b border-gray-100 bg-gray-50 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm md:text-base">Daftar Laporan Kinerja Pegawai</h3>
                            <p class="text-xs text-gray-500">Filter data berdasarkan pegawai, periode bulan, dan tahun.</p>
                        </div>
                        
                        <div id="form-filter-atasan" class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
                            <!-- Input Search -->
                            <div class="relative w-full sm:w-48">
                                <input type="text" id="filter_search" name="search" value="{{ $search }}" placeholder="Cari nama pegawai, laporan..." class="border-gray-300 rounded-xl text-xs p-2.5 pl-8 w-full focus:ring-blue-500 focus:border-blue-500">
                                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>

                            <select id="filter_pegawai" name="pegawai_id" class="border-gray-300 rounded-xl text-xs p-2.5 w-full sm:w-44 focus:ring-blue-500 focus:border-blue-500">
                                <option value="all" {{ $pegawaiId == 'all' ? 'selected' : '' }}>-- Semua Pegawai --</option>
                                @foreach($daftarBawahan as $bawahan)
                                    <option value="{{ $bawahan->id }}" {{ $pegawaiId == $bawahan->id ? 'selected' : '' }}>
                                        {{ $bawahan->name }}
                                    </option>
                                @endforeach
                            </select>

                            <select id="filter_bulan" name="bulan" class="border-gray-300 rounded-xl text-xs p-2.5 w-full sm:w-32 focus:ring-blue-500 focus:border-blue-500">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ sprintf('%02d', $m) }}" {{ $bulan == sprintf('%02d', $m) ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>

                            <select id="filter_tahun" name="tahun" class="border-gray-300 rounded-xl text-xs p-2.5 w-full sm:w-28 focus:ring-blue-500 focus:border-blue-500">
                                @foreach(range(date('Y'), 2024) as $y)
                                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- BARIS AKSI BULK APPROVE -->
                    <div x-show="selectedIds.length > 0" x-cloak class="p-3 bg-blue-50 border-b border-blue-100 flex items-center justify-between px-6 transition-all">
                        <span class="text-xs font-bold text-blue-900">
                            Terpilih: <span x-text="selectedIds.length" class="text-blue-700"></span> laporan
                        </span>
                        <button type="button" onclick="konfirmasiBulkSubmit()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Setujui Laporan Terpilih
                        </button>
                    </div>

                    <!-- TABEL KELOMPOK -->
                    <div class="p-3 sm:p-6 overflow-x-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600">
                                    <th class="p-3 w-10 text-center">
                                        <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                    </th>
                                    <th class="p-3">Pegawai & Tanggal</th>
                                    <th class="p-3 hidden sm:table-cell">Ringkasan Aktivitas</th>
                                    <th class="p-3 text-center">Jumlah</th>
                                    <th class="p-3 text-right">Detail</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs sm:text-sm">
                                @forelse($laporanBawahan as $groupKey => $groupData)
                                    @php
                                        $items = $groupData['items'];
                                        $user = $groupData['user'];
                                        $namaPegawai = $user->name ?? 'Pegawai';
                                        $tanggalTugas = $groupData['tanggal_tugas'];
                                        $infoAbsensi = $groupData['info_absensi'];
                                        $formattedDate = \Carbon\Carbon::parse($tanggalTugas)->translatedFormat('d M Y');
                                        
                                        $groupPendingIds = $items->where('status_validasi', 'pending')->pluck('id')->map(fn($id) => (string)$id)->toArray();
                                    @endphp
                                    
                                    <!-- PARENT ROW -->
                                    <tr class="hover:bg-blue-50/50 transition cursor-pointer {{ $infoAbsensi ? 'bg-amber-50/30' : '' }}" @click="toggleGroup('{{ $groupKey }}')">
                                        <td class="p-3 text-center align-middle" @click.stop>
                                            @if(!empty($groupPendingIds))
                                                <input type="checkbox" 
                                                       value="{{ $groupKey }}"
                                                       @change="toggleGroupCheckbox('{{ $groupKey }}', {{ json_encode($groupPendingIds) }})"
                                                       :checked="isGroupChecked('{{ json_encode($groupPendingIds) }}')"
                                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                            @else
                                                <span class="text-gray-300 text-xs">-</span>
                                            @endif
                                        </td>

                                        <td class="p-3 align-middle">
                                            <div class="flex items-center gap-2">
                                                <p class="font-bold text-gray-800">{{ $namaPegawai }}</p>
                                                @if($infoAbsensi)
                                                    <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full uppercase
                                                        {{ $infoAbsensi->status_kehadiran == 'cuti' ? 'bg-amber-100 text-amber-800 border border-amber-300' : '' }}
                                                        {{ $infoAbsensi->status_kehadiran == 'sakit' ? 'bg-blue-100 text-blue-800 border border-blue-300' : '' }}
                                                        {{ $infoAbsensi->status_kehadiran == 'izin' ? 'bg-purple-100 text-purple-800 border border-purple-300' : '' }}
                                                        {{ $infoAbsensi->status_kehadiran == 'alpa' ? 'bg-rose-100 text-rose-800 border border-rose-300' : '' }}">
                                                        🚫 {{ $infoAbsensi->status_kehadiran }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-blue-600 font-semibold mt-0.5">🗓️ {{ $formattedDate }}</p>
                                        </td>

                                        <td class="p-3 align-middle hidden sm:table-cell">
                                            @if($items->count() > 0)
                                                <div class="space-y-1">
                                                    @foreach($items->take(2) as $act)
                                                        <p class="font-medium text-gray-700 truncate max-w-xs sm:max-w-md">• {{ $act->aktivitas_kinerja }}</p>
                                                    @endforeach
                                                    @if($items->count() > 2)
                                                        <p class="text-[11px] text-blue-600 font-bold">+ {{ $items->count() - 2 }} aktivitas lainnya...</p>
                                                    @endif
                                                </div>
                                            @else
                                                <p class="text-xs text-amber-700 italic font-semibold">
                                                    {{ $infoAbsensi->alasan_klarifikasi ?? 'Pegawai tidak menginput laporan kinerja (' . strtoupper($infoAbsensi->status_kehadiran ?? 'ketidakhadiran') . ')' }}
                                                </p>
                                            @endif
                                        </td>

                                        <td class="p-3 text-center align-middle whitespace-nowrap">
                                            @if($items->count() > 0)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                                    {{ $items->count() }} <span class="hidden sm:inline ml-1">Laporan</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                                    0 Laporan
                                                </span>
                                            @endif
                                        </td>

                                        <td class="p-3 text-right align-middle whitespace-nowrap">
                                            <button type="button" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition">
                                                <svg class="w-5 h-5 transform transition-transform duration-200" :class="{'rotate-180 text-blue-800': expandedGroup === '{{ $groupKey }}'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- ACCORDION SUB-DETAIL -->
                                    <tr x-show="expandedGroup === '{{ $groupKey }}'" x-cloak transition class="bg-gray-50/80">
                                        <td colspan="5" class="p-3 sm:p-5">
                                            <div class="bg-white rounded-xl border border-gray-200 p-3.5 sm:p-4 space-y-3 shadow-sm">
                                                <p class="text-xs font-bold text-gray-500 uppercase border-b pb-2">Rincian Laporan Pegawai - {{ $formattedDate }}</p>

                                                @if($items->count() > 0)
                                                    <div class="space-y-3">
                                                        @foreach($items as $subItem)
                                                            @php
                                                                $jamInput = \Carbon\Carbon::parse($subItem->created_at)->format('H.i');
                                                            @endphp
                                                            <div class="p-3.5 rounded-xl border bg-gray-50 border-gray-200/80 space-y-2">
                                                                <div class="flex items-start justify-between gap-2">
                                                                    <div class="flex items-center gap-2.5">
                                                                        @if($subItem->status_validasi == 'pending')
                                                                            <input type="checkbox" name="laporan_ids[]" value="{{ (string)$subItem->id }}" x-model="selectedIds" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer mt-0.5">
                                                                        @endif
                                                                        <div>
                                                                            <span class="text-[11px] font-bold text-gray-400">🕒 {{ $jamInput }} WIB</span>
                                                                            <h5 class="font-bold text-xs sm:text-sm text-blue-800 mt-0.5">{{ $subItem->aktivitas_kinerja }}</h5>
                                                                        </div>
                                                                    </div>

                                                                    <div>
                                                                        @if($subItem->status_validasi == 'pending')
                                                                            <button type="button" 
                                                                                    @click="openModal = true; modalData = {
                                                                                        nama: '{{ addslashes($subItem->user->name) }}',
                                                                                        file_bukti: '{{ $subItem->file_bukti ? asset('storage/bukti_kinerja/' . $subItem->file_bukti) : '' }}',
                                                                                        actionUrl: '{{ route('atasan.validasi', $subItem->id) }}'
                                                                                    }" 
                                                                                    class="px-3 py-1.5 bg-yellow-500 text-white rounded-xl text-xs font-bold hover:bg-yellow-600 transition">
                                                                                Tinjau Laporan
                                                                            </button>
                                                                        @elseif($subItem->status_validasi == 'disetujui')
                                                                            <span class="px-2.5 py-1 text-[11px] font-bold uppercase rounded-full bg-green-100 text-green-800">Disetujui</span>
                                                                        @else
                                                                            <span class="px-2.5 py-1 text-[11px] font-bold uppercase rounded-full bg-red-100 text-red-800">Direvisi</span>
                                                                        @endif
                                                                    </div>
                                                                </div>

                                                                <p class="text-xs sm:text-sm text-gray-700 leading-relaxed">{{ $subItem->uraian_tugas }}</p>

                                                                @if($subItem->catatan_atasan)
                                                                    <div class="p-2.5 bg-blue-50 border-l-2 border-blue-400 rounded-r-lg text-xs text-blue-700 mt-2">
                                                                        <strong>Catatan Revisi:</strong> {{ $subItem->catatan_atasan }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="p-4 rounded-xl border bg-amber-50/60 border-amber-200 text-amber-900 space-y-1">
                                                        <div class="flex items-center gap-2">
                                                            <span class="font-bold text-xs uppercase px-2 py-0.5 bg-amber-200 text-amber-900 rounded">
                                                                {{ $infoAbsensi->status_kehadiran ?? 'Cuti' }}
                                                            </span>
                                                            <span class="text-xs font-bold">Pegawai Tidak Mengisi Laporan Kinerja</span>
                                                        </div>
                                                        <p class="text-xs text-amber-800 mt-1">
                                                            <strong>Keterangan:</strong> {{ $infoAbsensi->alasan_klarifikasi ?? 'Pegawai terdaftar Cuti/Sakit/Izin yang telah disetujui.' }}
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-gray-400 text-xs sm:text-sm">
                                            Belum ada data laporan kinerja untuk filter yang dipilih.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION LARAVEL -->
                    @if(isset($laporanPaginated) && $laporanPaginated->total() > 0)
                        <div class="p-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600">
                            <div>
                                Menampilkan <span class="font-semibold">{{ $laporanPaginated->firstItem() ?? 0 }}</span> s/d <span class="font-semibold">{{ $laporanPaginated->lastItem() ?? 0 }}</span> dari <span class="font-semibold">{{ $laporanPaginated->total() }}</span> hari kerja
                            </div>
                            <div>
                                {{ $laporanPaginated->links() }}
                            </div>
                        </div>
                    @endif

                </div>
            </form>

            <!-- MODAL GLOBAL LAPORAN -->
            <div x-show="openModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="display: none;">
                <div x-show="openModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openModal = false"></div>
                
                <div x-show="openModal" x-transition class="relative bg-white rounded-2xl p-6 max-w-md w-full text-left shadow-2xl z-10">
                    <button type="button" @click="openModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <h3 class="text-base font-bold text-gray-800 mb-1">Tinjau Laporan Kinerja</h3>
                    <p class="text-xs text-gray-500 mb-4">Pegawai: <strong class="text-gray-800" x-text="modalData.nama"></strong></p>
                    
                    <div class="mb-4 text-xs text-gray-700 bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <p class="font-semibold text-gray-600">Bukti Lampiran:</p>
                        <template x-if="modalData.file_bukti">
                            <a :href="modalData.file_bukti" target="_blank" class="inline-flex items-center text-blue-600 font-bold hover:underline mt-1">
                                Lihat Dokumen / Foto Bukti
                            </a>
                        </template>
                        <template x-if="!modalData.file_bukti">
                            <p class="text-gray-400 italic mt-0.5">Tidak ada file terlampir</p>
                        </template>
                    </div>

                    <form :action="modalData.actionUrl" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Keputusan Atasan</label>
                            <select name="status_validasi" class="w-full border-gray-300 rounded-xl text-sm p-3" required>
                                <option value="disetujui">Setujui Laporan</option>
                                <option value="direvisi">Tolak / Minta Revisi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Catatan Atasan (Wajib Jika Direvisi)</label>
                            <textarea name="catatan_atasan" rows="3" class="w-full border-gray-300 rounded-xl text-sm p-3" placeholder="Contoh: Uraian tugas mohon diperjelas..."></textarea>
                        </div>
                        <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100">
                            <button type="button" @click="openModal = false" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 text-xs font-bold rounded-xl">Batal</button>
                            <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white text-xs font-bold rounded-xl shadow">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- BOTTOM NAVIGATION BAR MOBILE -->
            <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-xl z-[90] px-2 py-2 flex justify-around items-center">
                <a href="{{ route('atasan.laporan.index') }}" class="flex flex-col items-center relative text-blue-600 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-[10px] mt-1">Laporan</span>
                    @if($countLaporanPending > 0)
                        <span class="absolute -top-1.5 -right-2 px-1.5 py-0.2 bg-rose-600 text-white text-[9px] font-extrabold rounded-full shadow-sm">
                            {{ $countLaporanPending }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('atasan.absensi.index') }}" class="flex flex-col items-center relative text-gray-500 hover:text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-[10px] mt-1">Absensi</span>
                    @if($countAbsensiPending > 0)
                        <span class="absolute -top-1.5 -right-2 px-1.5 py-0.2 bg-rose-600 text-white text-[9px] font-extrabold rounded-full shadow-sm">
                            {{ $countAbsensiPending }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('atasan.cuti.index') }}" class="flex flex-col items-center relative text-gray-500 hover:text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-[10px] mt-1">Cuti</span>
                    @if($countCutiPending > 0)
                        <span class="absolute -top-1.5 -right-2 px-1.5 py-0.2 bg-rose-600 text-white text-[9px] font-extrabold rounded-full shadow-sm">
                            {{ $countCutiPending }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('penilaian.index') }}" class="flex flex-col items-center relative text-gray-500 hover:text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    <span class="text-[10px] mt-1">Perilaku</span>
                    @if($countPenilaianPending > 0)
                        <span class="absolute -top-1.5 -right-2 px-1.5 py-0.2 bg-rose-600 text-white text-[9px] font-extrabold rounded-full shadow-sm">
                            {{ $countPenilaianPending }}
                        </span>
                    @endif
                </a>
            </div>

        </div>
    </div>

    <!-- Script SweetAlert2 & jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script>
        function konfirmasiBulkSubmit() {
            Swal.fire({
                title: 'Konfirmasi Persetujuan',
                text: "Apakah Anda yakin ingin menyetujui seluruh laporan yang dicentang?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Setujui Laporan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('formBulkValidasi').submit();
                }
            })
        }

        function laporanAtasanData(allPendingIds) {
            return {
                openModal: false,
                modalData: {},
                selectedIds: [],
                selectAll: false,
                expandedGroup: null,
                pendingIds: allPendingIds || [],

                toggleGroup(groupKey) {
                    this.expandedGroup = this.expandedGroup === groupKey ? null : groupKey;
                },

                toggleSelectAll() {
                    if (this.selectAll) {
                        this.selectedIds = [...this.pendingIds];
                    } else {
                        this.selectedIds = [];
                    }
                },

                isGroupChecked(idsJson) {
                    let ids = JSON.parse(idsJson);
                    if (ids.length === 0) return false;
                    return ids.every(id => this.selectedIds.includes(id));
                },

                toggleGroupCheckbox(groupKey, ids) {
                    let allChecked = ids.every(id => this.selectedIds.includes(id));
                    if (allChecked) {
                        this.selectedIds = this.selectedIds.filter(id => !ids.includes(id));
                    } else {
                        let combined = new Set([...this.selectedIds, ...ids]);
                        this.selectedIds = Array.from(combined);
                    }
                },

                updatePendingIds(newPendingIds) {
                    this.pendingIds = newPendingIds;
                }
            }
        }

        $(document).ready(function() {
            function loadRiwayatAjax(url) {
                $('#area-tabel-laporan').css('opacity', '0.5');

                $.get(url, function(data) {
                    let newContent = $(data).find('#area-tabel-laporan').html();
                    $('#area-tabel-laporan').html(newContent).css('opacity', '1');

                    let newPendingIds = [];
                    $('#area-tabel-laporan input[name="laporan_ids[]"]').each(function() {
                        newPendingIds.push($(this).val());
                    });

                    if (window.Alpine) {
                        let wrapperEl = document.getElementById('wrapper-laporan');
                        if (wrapperEl && wrapperEl._x_dataStack) {
                            wrapperEl._x_dataStack[0].updatePendingIds(newPendingIds);
                        }
                    }

                    window.history.pushState({}, '', url);
                }).fail(function() {
                    $('#area-tabel-laporan').css('opacity', '1');
                    alert('Gagal memuat data laporan.');
                });
            }

            function triggerFilter() {
                let search = $('#filter_search').val();
                let pegawai = $('#filter_pegawai').val();
                let bulan = $('#filter_bulan').val();
                let tahun = $('#filter_tahun').val();
                let targetUrl = "{{ route('atasan.laporan.index') }}?pegawai_id=" + pegawai + "&bulan=" + bulan + "&tahun=" + tahun + "&search=" + encodeURIComponent(search);
                loadRiwayatAjax(targetUrl);
            }

            $(document).on('change', '#filter_pegawai, #filter_bulan, #filter_tahun', function() {
                triggerFilter();
            });

            let searchTimer;
            $(document).on('input', '#filter_search', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    triggerFilter();
                }, 500);
            });

            $(document).on('click', '#area-tabel-laporan a[href*="page="]', function(e) {
                e.preventDefault();
                let targetUrl = $(this).attr('href');
                if (targetUrl && targetUrl !== '#') {
                    loadRiwayatAjax(targetUrl);
                }
            });
        });
    </script>
</x-app-layout>