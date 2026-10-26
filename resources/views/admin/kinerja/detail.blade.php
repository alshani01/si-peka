<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Laporan Kinerja Pegawai') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12 pb-28 md:pb-12" x-data="detailKinerjaPage()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- NOTIFIKASI SUKSES / ERROR -->
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

            <!-- TOMBOL KEMBALI & INFORMASI PEGAWAI -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 md:p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <a href="{{ route('admin.kinerja.rekap', ['tipe_filter' => $tipeFilter ?? 'bulanan', 'tanggal' => $tanggal ?? date('Y-m-d'), 'bulan' => $bulan ?? date('m'), 'tahun' => $tahun ?? date('Y')]) }}" class="inline-flex items-center text-xs font-bold text-blue-600 hover:underline mb-2">
                        &larr; Kembali ke Rekap Kinerja
                    </a>
                    <h3 class="text-lg font-extrabold text-gray-800">{{ $pegawai->name }}</h3>
                    <p class="text-xs text-gray-500">
                        ID PJLP: <span class="font-mono font-bold text-gray-700">{{ $pegawai->id_pjlp ?? '-' }}</span> | 
                        Jabatan: <span class="font-medium text-gray-700">{{ $pegawai->jenis_pekerjaan ?? '-' }}</span> | 
                        Lokasi Tugas: <span class="font-medium text-gray-700">{{ $pegawai->lokasi_tugas ?? '-' }}</span>
                    </p>
                </div>
                <span class="px-3 py-1.5 bg-blue-50 text-blue-700 font-bold text-xs rounded-full border border-blue-100">
                    @if(isset($tipeFilter) && $tipeFilter === 'harian')
                        Periode: {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                    @else
                        Periode: {{ \Carbon\Carbon::createFromDate($tahun ?? date('Y'), $bulan ?? date('m'), 1)->translatedFormat('F Y') }}
                    @endif
                </span>
            </div>

            <!-- ACTION BAR BULK DELETE (KHUSUS ADMIN) -->
            @if (auth()->user()->role === 'admin')
                <div x-show="selectedIds.length > 0" x-cloak class="bg-rose-50 border border-rose-200 p-4 rounded-2xl flex items-center justify-between shadow-sm transition">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span class="text-xs font-bold text-rose-800">
                            Terpilih <span x-text="selectedIds.length"></span> laporan untuk dihapus
                        </span>
                    </div>
                    <form id="form-bulk-delete" action="{{ route('admin.kinerja.laporan.bulk_delete') }}" method="POST" @submit.prevent="confirmBulkDelete($event)">
                        @csrf
                        @method('DELETE')
                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl transition shadow flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus Terpilih
                        </button>
                    </form>
                </div>
            @endif

            <!-- DAFTAR LAPORAN KINERJA (ACCORDION PER TANGGAL) -->
            <div id="area-tabel-detail" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                
                <!-- HEADER ATAS TABEL -->
                <div class="p-4 sm:p-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        @if (auth()->user()->role === 'admin' && $laporans->count() > 0)
                            <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-700 cursor-pointer select-none">
                                <input type="checkbox" 
                                       @change="toggleSelectAllMaster($event)" 
                                       :checked="isMasterAllSelected()" 
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4">
                                <span>Pilih Semua Laporan</span>
                            </label>
                        @endif
                        <h4 class="font-bold text-gray-800 text-sm md:text-base">Daftar Rincian Laporan Kerja Harian</h4>
                    </div>
                    <span class="text-xs text-gray-500 font-medium bg-white px-3 py-1 rounded-full border border-gray-200 shadow-xs">
                        Total: <strong class="text-gray-800">{{ $laporans->count() }}</strong> Laporan ({{ $groupedLaporans->count() }} Hari)
                    </span>
                </div>

                <!-- LIST ACCORDION TANGGAL -->
                <div class="divide-y divide-gray-100">
                    @forelse ($groupedLaporans as $dateKey => $items)
                        @php
                            $formattedDate = \Carbon\Carbon::parse($dateKey)->translatedFormat('d M Y');
                            $countItems = $items->count();
                        @endphp
                        <div class="border-b border-gray-100 last:border-b-0 transition">
                            
                            <!-- ACCORDION HEADER BARIS TANGGAL -->
                            <div class="p-4 md:p-5 flex items-start md:items-center justify-between gap-3 bg-white hover:bg-gray-50/80 cursor-pointer select-none transition"
                                 @click="toggleDateAccordion('{{ $dateKey }}')">
                                
                                <div class="flex items-start md:items-center gap-3 flex-1 min-w-0">
                                    <!-- Checkbox Tanggal (Admin Only) -->
                                    @if (auth()->user()->role === 'admin')
                                        <div @click.stop class="pt-0.5 md:pt-0 flex-shrink-0">
                                            <input type="checkbox" 
                                                   @change="toggleSelectDateGroup('{{ $dateKey }}', $event)" 
                                                   :checked="isDateSelected('{{ $dateKey }}')" 
                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4">
                                        </div>
                                    @endif

                                    <!-- Informasi Tanggal & Ringkasan Aktivitas -->
                                    <div class="flex-1 min-w-0 space-y-1.5 md:space-y-0 md:flex md:items-center md:gap-6">
                                        <!-- Tanggal -->
                                        <div class="flex items-center gap-2 flex-shrink-0 min-w-[150px]">
                                            <svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span class="font-extrabold text-gray-800 text-xs md:text-sm">
                                                {{ $formattedDate }}
                                            </span>
                                        </div>

                                        <!-- Preview Ringkasan Tugas (Bullet List) -->
                                        <div class="text-xs text-gray-600 space-y-0.5 min-w-0 flex-1">
                                            @foreach ($items->take(2) as $item)
                                                <p class="truncate font-normal text-gray-600">
                                                    <span class="text-gray-400">&bull;</span> {{ $item->uraian_tugas }}
                                                </p>
                                            @endforeach
                                            @if ($countItems > 2)
                                                <p class="text-[11px] font-bold text-blue-600">
                                                    + {{ $countItems - 2 }} aktivitas lainnya...
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Kanan: Badge Jumlah Laporan & Panah Accordion -->
                                <div class="flex items-center gap-3 flex-shrink-0">
                                    <span class="px-3 py-1 bg-blue-50 text-blue-700 font-bold rounded-full text-xs border border-blue-100/80 whitespace-nowrap">
                                        {{ $countItems }} Laporan
                                    </span>
                                    <button type="button" class="p-1 text-gray-400 hover:text-gray-600 rounded-lg transition">
                                        <svg class="w-5 h-5 transform transition-transform duration-200" 
                                             :class="{'rotate-180': isDateOpen('{{ $dateKey }}')}" 
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- ACCORDION CONTENT (RINCIAN LAPORAN TANGGAL INI) -->
                            <div x-show="isDateOpen('{{ $dateKey }}')" 
                                 x-cloak 
                                 style="display: none;"
                                 class="bg-slate-50/70 p-4 md:p-6 border-t border-gray-100 space-y-3">
                                
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">
                                        RINCIAN LAPORAN PEGAWAI &mdash; {{ strtoupper($formattedDate) }}
                                    </span>
                                </div>

                                <!-- Kartu Masing-masing Laporan -->
                                <div class="space-y-3">
                                    @foreach ($items as $laporan)
                                        <div class="bg-white rounded-2xl p-4 border border-gray-200/80 shadow-sm hover:shadow transition relative flex flex-col md:flex-row md:items-start justify-between gap-4">
                                            
                                            <!-- Kiri: Checkbox, Waktu & Uraian Tugas -->
                                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                                @if (auth()->user()->role === 'admin')
                                                    <div class="pt-0.5 flex-shrink-0">
                                                        <input type="checkbox" 
                                                               :value="{{ $laporan->id }}" 
                                                               x-model="selectedIds" 
                                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4">
                                                    </div>
                                                @endif

                                                <div class="flex-1 min-w-0 space-y-1.5">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="text-[10px] font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded border border-gray-200 flex items-center gap-1">
                                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            {{ \Carbon\Carbon::parse($laporan->created_at ?? $laporan->tanggal_tugas)->format('H:i') }} WIB
                                                        </span>
                                                    </div>

                                                    <h5 class="font-bold text-gray-800 text-xs md:text-sm leading-relaxed">
                                                        {{ $laporan->uraian_tugas }}
                                                    </h5>

                                                    @if (filled($laporan->catatan_atasan))
                                                        <div class="p-2.5 bg-amber-50/80 rounded-xl border border-amber-200/60 text-xs text-amber-900 italic flex items-start gap-1.5 mt-2">
                                                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                                            <div>
                                                                <span class="font-bold not-italic">Catatan Atasan:</span> {{ $laporan->catatan_atasan }}
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Kanan: Status, Bukti Dukung & Hapus -->
                                            <div class="flex md:flex-col items-center md:items-end justify-between md:justify-start gap-2.5 flex-shrink-0 pt-2.5 md:pt-0 border-t md:border-t-0 border-gray-100">
                                                <!-- Status Validasi -->
                                                <div>
                                                    @if(in_array(strtolower($laporan->status_validasi), ['disetujui', 'acc']))
                                                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-extrabold rounded-full text-[10px] inline-flex items-center gap-1">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Disetujui
                                                        </span>
                                                    @elseif(in_array(strtolower($laporan->status_validasi), ['direvisi', 'ditolak', 'revisi']))
                                                        <span class="px-2.5 py-1 bg-rose-100 text-rose-800 font-extrabold rounded-full text-[10px] inline-flex items-center gap-1">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Direvisi
                                                        </span>
                                                    @else
                                                        <span class="px-2.5 py-1 bg-amber-100 text-amber-800 font-extrabold rounded-full text-[10px] inline-flex items-center gap-1">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- Tombol File Bukti & Hapus -->
                                                <div class="flex items-center gap-2">
                                                    @if($laporan->file_bukti)
                                                        <button type="button" 
                                                                @click="openModal('{{ \Illuminate\Support\Facades\Storage::url($laporan->file_bukti) }}', '{{ addslashes($laporan->uraian_tugas) }}')" 
                                                                class="px-2.5 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 font-bold rounded-xl text-xs inline-flex items-center gap-1 transition cursor-pointer">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                            Lihat File
                                                        </button>
                                                    @else
                                                        <span class="text-gray-400 italic text-[11px]">- Tidak Ada File -</span>
                                                    @endif

                                                    @if (auth()->user()->role === 'admin')
                                                        <form action="{{ route('admin.kinerja.laporan.destroy', $laporan->id) }}" method="POST" class="inline" @submit.prevent="confirmSingleDelete($event, '{{ \Carbon\Carbon::parse($laporan->tanggal_tugas)->translatedFormat('d M Y') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-xl transition cursor-pointer" title="Hapus Laporan Ini">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                    @endforeach
                                </div>

                            </div>

                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400 text-xs italic bg-white">
                            Tidak ada laporan kinerja yang dibuat oleh pegawai ini pada periode terpilih.
                        </div>
                    @endforelse
                </div>

            </div>

            <!-- BOTTOM NAVIGATION BAR (MOBILE ADMIN - 4 MENU) -->
            <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-xl z-[90] px-2 py-2 flex justify-around items-center">
                <a href="{{ route('admin.pegawai.index') }}" class="flex flex-col items-center text-gray-500 hover:text-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span class="text-[9px] mt-1">Pegawai</span>
                </a>
                <a href="{{ route('admin.absensi.rekap') }}" class="flex flex-col items-center text-gray-500 hover:text-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-[9px] mt-1">Absensi</span>
                </a>
                <a href="{{ route('admin.kinerja.rekap') }}" class="flex flex-col items-center text-blue-600 font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-[9px] mt-1">Kinerja</span>
                </a>
                <a href="{{ route('admin.penilaian.rekap') }}" class="flex flex-col items-center text-gray-500 hover:text-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    <span class="text-[9px] mt-1">Perilaku</span>
                </a>
            </div>

        </div>

        <!-- MODAL CUSTOM KONFIRMASI HAPUS -->
        <div x-show="showDeleteModal" 
             x-cloak 
             @keydown.escape.window="closeDeleteModal()" 
             class="fixed inset-0 z-[110] flex items-center justify-center p-4"
             style="display: none;">
            
            <!-- Backdrop Blur -->
            <div x-show="showDeleteModal" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" 
                 @click="closeDeleteModal()"></div>

            <!-- Content Card Modal -->
            <div x-show="showDeleteModal" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl z-10 border border-gray-100 text-center space-y-4">
                
                <!-- Icon Warning Red Circle -->
                <div class="w-14 h-14 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center mx-auto shadow-sm">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>

                <!-- Title & Description -->
                <div>
                    <h3 class="font-extrabold text-gray-800 text-base md:text-lg">Konfirmasi Hapus Laporan</h3>
                    <p class="text-xs text-gray-500 mt-2 leading-relaxed" x-text="deleteModalMessage"></p>
                </div>

                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200/60 text-[11px] text-amber-800 font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Tindakan ini permanen dan tidak dapat dibatalkan.</span>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="closeDeleteModal()" class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                        Batal
                    </button>
                    <button type="button" @click="executeDelete()" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl transition shadow-md flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Ya, Hapus Data
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL PREVIEW BUKTI DUKUNG INLINE -->
        <div x-show="showModal" 
             x-cloak 
             @keydown.escape.window="closeModal()" 
             class="fixed inset-0 z-[100] overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.away="closeModal()" 
                 class="bg-white rounded-2xl max-w-3xl w-full overflow-hidden shadow-2xl transform transition-all flex flex-col max-h-[90vh]">
                
                <!-- Modal Header -->
                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <div>
                        <h3 class="font-bold text-gray-800 text-sm">Bukti Dukung Laporan</h3>
                        <p class="text-xs text-gray-500 line-clamp-1 mt-0.5" x-text="fileName || 'Pratinjau Berkas'"></p>
                    </div>
                    <button @click="closeModal()" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-200 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="p-4 overflow-y-auto flex-1 flex items-center justify-center bg-gray-900/5 min-h-[300px]">
                    <template x-if="isImage">
                        <img :src="fileUrl" alt="Bukti Dukung" class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-sm">
                    </template>
                    <template x-if="!isImage">
                        <iframe :src="fileUrl" class="w-full h-[60vh] rounded-lg border-0"></iframe>
                    </template>
                </div>

                <!-- Modal Footer -->
                <div class="p-3 border-t border-gray-100 flex justify-between items-center bg-white">
                    <a :href="fileUrl" target="_blank" download class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Buka / Unduh Berkas
                    </a>
                    <button @click="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-700 font-bold text-xs rounded-xl hover:bg-gray-300 transition">
                        Tutup
                    </button>
                </div>

            </div>
        </div>

    </div>

    <!-- LOGIKA ALPINE.JS UNTUK ACCORDION, BULK DELETE, & MODAL PREVIEW -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('detailKinerjaPage', () => ({
                openDates: {},
                selectedIds: [],
                
                // State Modal Preview
                showModal: false,
                fileUrl: '',
                fileName: '',
                isImage: false,

                // State Modal Delete
                showDeleteModal: false,
                deleteModalMessage: '',
                targetForm: null,

                // Array ID Keseluruhan
                allIds: @json($laporans->pluck('id')->values()),

                // Pemetaan ID per Tanggal
                dateGroupIds: @json($groupedLaporans->map(fn($group) => $group->pluck('id')->values())),

                init() {
                    // Buka tanggal pertama secara default jika ada data
                    const dateKeys = Object.keys(this.dateGroupIds);
                    if (dateKeys.length > 0) {
                        this.openDates[dateKeys[0]] = true;
                    }
                },

                toggleDateAccordion(dateKey) {
                    this.openDates[dateKey] = !this.openDates[dateKey];
                },

                isDateOpen(dateKey) {
                    return !!this.openDates[dateKey];
                },

                // --- LOGIKA BULK / SELECT ALL ---
                isMasterAllSelected() {
                    return this.allIds.length > 0 && this.selectedIds.length === this.allIds.length;
                },

                toggleSelectAllMaster(event) {
                    if (event.target.checked) {
                        this.selectedIds = [...this.allIds];
                    } else {
                        this.selectedIds = [];
                    }
                },

                isDateSelected(dateKey) {
                    const ids = this.dateGroupIds[dateKey] || [];
                    if (ids.length === 0) return false;
                    return ids.every(id => this.selectedIds.includes(id));
                },

                toggleSelectDateGroup(dateKey, event) {
                    const ids = this.dateGroupIds[dateKey] || [];
                    if (event.target.checked) {
                        const newSet = new Set([...this.selectedIds, ...ids]);
                        this.selectedIds = Array.from(newSet);
                    } else {
                        this.selectedIds = this.selectedIds.filter(id => !ids.includes(id));
                    }
                },

                // --- LOGIKA MODAL DELETE ---
                confirmSingleDelete(event, tanggalFormatted) {
                    this.targetForm = event.target;
                    this.deleteModalMessage = `Apakah Anda yakin ingin menghapus laporan kinerja tanggal ${tanggalFormatted}?`;
                    this.showDeleteModal = true;
                },

                confirmBulkDelete(event) {
                    if (this.selectedIds.length === 0) return;
                    this.targetForm = event.target;
                    this.deleteModalMessage = `Apakah Anda yakin ingin menghapus ${this.selectedIds.length} laporan kinerja yang telah dipilih secara bersamaan?`;
                    this.showDeleteModal = true;
                },

                executeDelete() {
                    if (this.targetForm) {
                        this.targetForm.submit();
                    }
                    this.closeDeleteModal();
                },

                closeDeleteModal() {
                    this.showDeleteModal = false;
                    this.targetForm = null;
                    this.deleteModalMessage = '';
                },

                // --- LOGIKA MODAL PREVIEW BUKTI ---
                openModal(url, name = '') {
                    this.fileUrl = url;
                    this.fileName = name;
                    this.isImage = /\.(jpg|jpeg|png|webp|gif)$/i.test(url);
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                    this.fileUrl = '';
                    this.fileName = '';
                }
            }));
        });
    </script>
</x-app-layout>