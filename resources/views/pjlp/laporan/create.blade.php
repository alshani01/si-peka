<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Formulir Laporan Kinerja Harian') }}
        </h2>
    </x-slot>
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <!-- Ruang bawah lega (pb-28 md:pb-12) agar tidak tertutup Bottom Nav di HP -->
    <div class="py-4 sm:py-6 md:py-12 pb-28 md:pb-12" x-data="laporanPage()">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- TAB NAVIGASI DESKTOP PJLP -->
            <div class="hidden sm:flex bg-white p-2 rounded-2xl shadow-sm border border-gray-100 space-x-1">
                <a href="{{ route('pjlp.dashboard') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center text-gray-600 hover:bg-gray-50 transition">
                    Dashboard
                </a>
                <a href="{{ route('pjlp.laporan.create') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center bg-blue-600 text-white shadow-sm transition">
                    Laporan Kinerja
                </a>
                <a href="{{ route('pjlp.absensi.index') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center text-gray-600 hover:bg-gray-50 transition">
                    Rekap Absensi
                </a>
                <a href="{{ route('pjlp.cuti.index') }}" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-xs text-center text-gray-600 hover:bg-gray-50 transition">
                    Pengajuan Cuti
                </a>
            </div>

            <!-- Notifikasi Sukses (Alert) -->
            @if (session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded-2xl shadow-sm flex items-center justify-between" role="alert">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <div>
                            <p class="font-bold text-sm">Berhasil!</p>
                            <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Notifikasi Error (Alert) -->
            @if ($errors->any())
                <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 rounded-2xl shadow-sm" role="alert">
                    <p class="font-bold text-sm">Oops, ada kesalahan:</p>
                    <ul class="list-disc ml-5 mt-1 text-xs text-rose-700 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- KARTU UTAMA FORMULIR -->
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100">
                <div class="p-5 sm:p-6 text-gray-900">
                    <h3 class="text-base sm:text-lg font-bold mb-4 text-blue-800 border-b pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Buat Laporan Baru
                        </span>
                        <span class="text-xs font-semibold px-3 py-1 bg-blue-50 text-blue-700 rounded-lg w-fit">
                            Jam Input Laporan : 06:00 - 18:00 WIB
                        </span>
                    </h3>

                    @php
                        $jamSekarang = \Carbon\Carbon::now('Asia/Jakarta')->format('H:i');
                        $isOpen = ($jamSekarang >= '06:00' && $jamSekarang <= '18:00');
                    @endphp

                    @if(!$isOpen)
                        <!-- Warning jika dibuka di luar jam operasional -->
                        <div class="mb-5 bg-amber-50 border-l-4 border-amber-500 text-amber-800 p-4 rounded-xl text-xs sm:text-sm">
                            <p class="font-bold flex items-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Form Laporan Ditutup
                            </p>
                            <p class="mt-1">Pengisian laporan harian hanya dibuka pada pukul <strong>06:00 s/d 18:00 WIB</strong> di hari berjalan.</p>
                        </div>
                    @endif
                    
                    <form action="{{ route('pjlp.laporan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 sm:space-y-5">
                        @csrf
                        
                        <!-- Input Tanggal (Dikunci Hanya Hari Ini) -->
                        <div>
                            <label for="tanggal_tugas" class="block text-sm font-bold text-gray-700 mb-1">Tanggal Tugas</label>
                            <input type="date" name="tanggal_tugas" id="tanggal_tugas" 
                                value="{{ \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d') }}" 
                                min="{{ \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d') }}" 
                                max="{{ \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d') }}" 
                                class="block w-full border-gray-300 bg-gray-50 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm p-3 cursor-not-allowed" 
                                readonly required>
                            <p class="text-[11px] text-gray-500 mt-1">*Dikunci otomatis tanggal hari ini ({{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y') }}).</p>
                        </div>

                        <!-- Dropdown Aktivitas Kinerja -->
                        <div>
                            <label for="aktivitas_kinerja" class="block text-sm font-bold text-gray-700 mb-1">
                                Aktivitas Kinerja <span class="text-xs text-blue-600">({{ $jenisPekerjaanUser ?? '' }})</span>
                            </label>
                            <select name="aktivitas_kinerja" id="aktivitas_kinerja" class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm p-3" required {{ !$isOpen ? 'disabled' : '' }}>
                                <option value="">-- Pilih Aktivitas Kinerja --</option>
                                @if(isset($listAktivitas))
                                    @foreach($listAktivitas as $aktivitas)
                                        <option value="{{ $aktivitas }}" {{ old('aktivitas_kinerja') == $aktivitas ? 'selected' : '' }}>
                                            {{ $aktivitas }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        
                        <!-- Input Uraian Tugas -->
                        <div>
                            <label for="uraian_tugas" class="block text-sm font-bold text-gray-700 mb-1">Uraian Tugas / Pekerjaan</label>
                            <textarea name="uraian_tugas" id="uraian_tugas" rows="3" 
                                placeholder="Deskripsikan pekerjaan yang dilakukan secara rinci dan spesifik..." 
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm p-3 disabled:bg-gray-100 disabled:cursor-not-allowed" 
                                {{ !$isOpen ? 'disabled' : '' }} required>{{ old('uraian_tugas') }}</textarea>
                        </div>

                        <!-- Input File Upload (Foto/PDF) dengan Preview -->
                        <div>
                            <label for="file_bukti" class="block text-sm font-bold text-gray-700 mb-1">Bukti Kinerja (Foto / PDF)</label>
                            
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:bg-gray-50 transition cursor-pointer relative" 
                                @if($isOpen) onclick="document.getElementById('file_bukti').click()" @endif>
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-10 w-10 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <span class="font-medium text-blue-600 hover:text-blue-500">Unggah file</span>
                                        <p class="pl-1">atau seret dan lepas</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, PDF (Foto otomatis dikompres)</p>
                                </div>
                                <input id="file_bukti" name="file_bukti" type="file" accept=".jpg,.jpeg,.png,.pdf" class="sr-only" {{ !$isOpen ? 'disabled' : '' }} required>
                            </div>

                            <!-- TAMPILAN PRATINJAU FILE -->
                            <div id="file-name-display" class="mt-2 text-xs sm:text-sm text-emerald-700 font-semibold hidden bg-emerald-50 p-3 rounded-xl border border-emerald-200 flex items-center justify-between">
                                <span id="file-status-text"></span>
                            </div>
                        </div>

                        <!-- Tombol Kirim -->
                        <div class="pt-4 border-t border-gray-100">
                            <button type="submit" 
                                class="w-full inline-flex justify-center items-center px-6 py-3.5 border border-transparent rounded-xl font-bold text-white text-xs sm:text-sm tracking-widest transition ease-in-out duration-150 shadow-md {{ $isOpen ? 'bg-blue-600 hover:bg-blue-700 active:bg-blue-800' : 'bg-gray-400 cursor-not-allowed' }}" 
                                {{ !$isOpen ? 'disabled' : '' }}>
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                {{ $isOpen ? 'KIRIM LAPORAN' : 'FORM DITUTUP (06:00 - 18:00 WIB)' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- BAGIAN: RIWAYAT LAPORAN TERAKHIR (TERKELOMPOK PER TANGGAL) -->
            <div id="area-riwayat-laporan" class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 transition-opacity duration-200">
                
                <!-- HEADER & FORM FILTER -->
                <div class="p-4 sm:p-6 border-b border-gray-100 bg-gray-50 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-gray-800 text-sm sm:text-base">Riwayat Laporan Kinerja</h3>
                        <p class="text-xs text-gray-500">Daftar laporan harian dikelompokkan berdasarkan tanggal.</p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
                        <!-- Form Filter Bulan/Tahun -->
                        <form id="form-filter-laporan" method="GET" action="{{ route('pjlp.laporan.create') }}" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                            <select id="filter_bulan" name="bulan" class="border-gray-300 rounded-xl text-xs sm:text-sm p-2.5 w-full sm:w-36 focus:ring-blue-500 focus:border-blue-500">
                                @foreach(range(1, 12) as $m)
                                    @php $mStr = sprintf('%02d', $m); @endphp
                                    <option value="{{ $mStr }}" {{ (request('bulan', $bulan) == $mStr) ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>

                            <select id="filter_tahun" name="tahun" class="border-gray-300 rounded-xl text-xs sm:text-sm p-2.5 w-full sm:w-28 focus:ring-blue-500 focus:border-blue-500">
                                @foreach(range(date('Y'), 2024) as $y)
                                    <option value="{{ $y }}" {{ (request('tahun', $tahun) == $y) ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold py-2.5 px-4 rounded-xl transition w-full sm:w-auto">
                                Filter
                            </button>
                        </form>

                        <!-- TOMBOL DOWNLOAD REKAP PDF -->
                        <a id="btn-download-pdf" 
                           href="{{ route('pjlp.laporan.export_pdf', ['bulan' => request('bulan', $bulan), 'tahun' => request('tahun', $tahun)]) }}" 
                           target="_blank" 
                           class="inline-flex items-center justify-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-sm transition gap-1.5 w-full sm:w-auto" 
                           title="Download Rekap Laporan Bulanan (PDF)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Unduh Laporan Kinerja</span>
                        </a>
                    </div>
                </div>

                <!-- TABEL GROUPED (RESPONSIF MOBILE & DESKTOP) -->
                <div class="p-3 sm:p-6 overflow-x-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600">
                                <th class="p-3">Tanggal</th>
                                <th class="p-3 hidden sm:table-cell">Ringkasan Aktivitas</th>
                                <th class="p-3 text-center">Jumlah</th>
                                <th class="p-3 text-right">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs sm:text-sm">
                            @forelse($laporanPaginated as $tanggal)
                                @php
                                    $formattedDate = \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y');
                                    $items = $riwayatGrouped->get($tanggal, collect());
                                    $absensiItem = $infoAbsensi->get($tanggal);
                                @endphp
                                
                                <!-- PARENT ROW -->
                                <tr class="hover:bg-blue-50/50 transition cursor-pointer" @click="toggleGroup('{{ $tanggal }}')">
                                    <!-- 1. Tanggal -->
                                    <td class="p-3 font-bold text-gray-800 whitespace-nowrap align-middle">
                                        <div class="flex items-center gap-2">
                                            <span>{{ $formattedDate }}</span>
                                            @if($absensiItem)
                                                <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-amber-100 text-amber-800 border border-amber-300">
                                                    🚫 {{ strtoupper($absensiItem->status_kehadiran) }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- 2. Ringkasan Teks -->
                                    <td class="p-3 align-middle hidden sm:table-cell">
                                        @if($items->isNotEmpty())
                                            <div class="space-y-1">
                                                @foreach($items->take(2) as $act)
                                                    <p class="font-medium text-gray-700 truncate max-w-xs sm:max-w-md">• {{ $act->aktivitas_kinerja }}</p>
                                                @endforeach
                                                @if($items->count() > 2)
                                                    <p class="text-[11px] text-blue-600 font-bold">+ {{ $items->count() - 2 }} aktivitas lainnya...</p>
                                                @endif
                                            </div>
                                        @else
                                            <p class="text-amber-700 font-semibold italic text-xs">
                                                Anda berstatus {{ strtoupper($absensiItem->status_kehadiran ?? 'CUTI') }} (Tidak ada inputan laporan)
                                            </p>
                                        @endif
                                    </td>

                                    <!-- 3. Badge Jumlah Aktivitas -->
                                    <td class="p-3 text-center align-middle whitespace-nowrap">
                                        @if($items->isNotEmpty())
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                                {{ $items->count() }} <span class="hidden sm:inline ml-1">Aktivitas</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                0 Aktivitas
                                            </span>
                                        @endif
                                    </td>

                                    <!-- 4. Tombol Chevron -->
                                    <td class="p-3 text-right align-middle whitespace-nowrap">
                                        <button type="button" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition" title="Lihat Detail">
                                            <svg class="w-5 h-5 transform transition-transform duration-200" :class="{'rotate-180 text-blue-800': expandedGroup === '{{ $tanggal }}'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>

                                <!-- ACCORDION SUB-DETAIL -->
                                <tr x-show="expandedGroup === '{{ $tanggal }}'" x-cloak transition class="bg-gray-50/80">
                                    <td colspan="4" class="p-3 sm:p-5">
                                        <div class="bg-white rounded-xl border border-gray-200 p-3.5 sm:p-4 space-y-3 shadow-sm">
                                            <p class="text-xs font-bold text-gray-500 uppercase border-b pb-2">Rincian Aktivitas Tanggal {{ $formattedDate }}</p>

                                            @if($absensiItem)
                                                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center justify-between gap-3">
                                                    <div>
                                                        <p class="font-bold">⚠️ Status Absensi Hari Ini: <span class="uppercase text-amber-800">{{ $absensiItem->status_kehadiran }}</span></p>
                                                        <p class="text-[11px] text-amber-700 mt-0.5">{{ $absensiItem->alasan_klarifikasi ?? 'Anda tidak menginput laporan kinerja harian karena berstatus ' . $absensiItem->status_kehadiran }}</p>
                                                    </div>
                                                    <span class="px-2.5 py-1 bg-amber-200 text-amber-900 font-extrabold text-[10px] rounded-lg uppercase">Terblok Otomatis</span>
                                                </div>
                                            @endif

                                            <div class="space-y-3">
                                                @forelse($items as $index => $subItem)
                                                    @php
                                                        $isOtomatis = \Illuminate\Support\Str::contains($subItem->catatan_atasan ?? '', ['Sinkronisasi', 'Diisi otomatis']);
                                                    @endphp
                                                    <div class="p-3 rounded-xl border space-y-2 {{ $isOtomatis ? 'bg-blue-50/40 border-blue-200' : 'bg-gray-50 border-gray-200/80' }}">
                                                        <div class="flex items-start justify-between gap-2">
                                                            <div>
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-[10px] font-bold text-gray-400">#{{ $items->count() - $index }} ({{ \Carbon\Carbon::parse($subItem->created_at)->format('H:i') }} WIB)</span>
                                                                    @if($isOtomatis)
                                                                        <span class="px-1.5 py-0.5 text-[9px] font-bold bg-blue-100 text-blue-700 rounded-md">
                                                                            Sinkronisasi Sistem
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                <h5 class="font-bold text-xs sm:text-sm text-blue-800">{{ $subItem->aktivitas_kinerja }}</h5>
                                                            </div>

                                                            <!-- Status Badge -->
                                                            @if($subItem->status_validasi == 'pending')
                                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">MENUNGGU</span>
                                                            @elseif($subItem->status_validasi == 'disetujui')
                                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">DISETUJUI</span>
                                                            @else
                                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">REVISI</span>
                                                            @endif
                                                        </div>

                                                        <!-- Uraian Pekerjaan -->
                                                        <p class="text-xs sm:text-sm text-gray-700 leading-relaxed">{{ $subItem->uraian_tugas }}</p>

                                                        <!-- Catatan Atasan -->
                                                        @if($subItem->catatan_atasan)
                                                            <div class="p-2.5 bg-amber-50 border-l-4 border-amber-400 rounded-r-lg text-xs text-amber-900 mt-2">
                                                                <p class="font-bold flex items-center">
                                                                    <span class="mr-1">⚠️</span> Catatan Atasan:
                                                                </p>
                                                                <p class="mt-0.5 italic text-amber-800">"{{ $subItem->catatan_atasan }}"</p>
                                                            </div>
                                                        @endif

                                                        <!-- Tombol Lihat Bukti -->
                                                        <div class="pt-2 flex justify-end border-t border-gray-200/60">
                                                            @if($subItem->file_bukti)
                                                                <button type="button" @click="openModal('{{ \Illuminate\Support\Facades\Storage::url($subItem->file_bukti) }}')" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                                    Lihat Bukti
                                                                </button>
                                                            @else
                                                                <span class="text-xs text-gray-400">Tidak ada file</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @empty
                                                    @if(!$absensiItem)
                                                        <p class="text-xs text-gray-400 italic">Tidak ada rincian laporan.</p>
                                                    @endif
                                                @endforelse
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-gray-400 text-xs sm:text-sm">
                                        Belum ada riwayat laporan kinerja untuk periode bulan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- KOMPONEN PAGINATION LARAVEL -->
                @if(isset($laporanPaginated) && $laporanPaginated->total() > 0)
                    <div id="pagination-wrapper" class="p-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600">
                        <div>
                            Menampilkan <span class="font-semibold">{{ $laporanPaginated->firstItem() ?? 0 }}</span> s/d <span class="font-semibold">{{ $laporanPaginated->lastItem() ?? 0 }}</span> dari <span class="font-semibold">{{ $laporanPaginated->total() }}</span> hari kerja
                        </div>
                        <div>
                            {{ $laporanPaginated->links() }}
                        </div>
                    </div>
                @endif

            </div>

            <!-- MODAL PREVIEW BUKTI -->
            <div x-show="showModal" x-cloak 
                 @keydown.escape.window="closeModal()" 
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm transition-opacity">
                
                <div class="relative w-full max-w-3xl bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-200 transform transition-all" @click.away="closeModal()">
                    
                    <div class="flex items-center justify-between p-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-sm font-bold text-gray-800">Pratinjau Bukti Kinerja</h3>
                        <button type="button" @click="closeModal()" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-full transition focus:outline-none" title="Tutup">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-4 flex items-center justify-center max-h-[75vh] overflow-y-auto bg-gray-900/5">
                        <template x-if="modalFileType === 'image'">
                            <img :src="modalUrl" class="max-h-[65vh] w-auto object-contain rounded-xl shadow-md">
                        </template>
                        <template x-if="modalFileType === 'pdf'">
                            <iframe :src="modalUrl" class="w-full h-[65vh] rounded-xl border border-gray-200"></iframe>
                        </template>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- BOTTOM NAVIGATION BAR (Khusus Mobile) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40 px-4 py-2 flex justify-around items-center">
        <a href="{{ route('pjlp.dashboard') }}" class="flex flex-col items-center {{ request()->routeIs('pjlp.dashboard') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="text-[10px] mt-1">Dashboard</span>
        </a>
        <a href="{{ route('pjlp.laporan.create') }}" class="flex flex-col items-center {{ request()->routeIs('pjlp.laporan.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span class="text-[10px] mt-1">Laporan</span>
        </a>
        <a href="{{ route('pjlp.absensi.index') }}" class="flex flex-col items-center {{ request()->routeIs('pjlp.absensi.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-[10px] mt-1">Absensi</span>
        </a>
        <a href="{{ route('pjlp.cuti.index') }}" class="flex flex-col items-center {{ request()->routeIs('pjlp.cuti.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="text-[10px] mt-1">Cuti</span>
        </a>
    </div>

    <!-- Script Library Browser Image Compression -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.1/dist/browser-image-compression.js"></script>
    
    <!-- LOGIKA ALPINE.JS & VANILLA JS AJAX (MURNI TANPA JQUERY) -->
    <script>
        function laporanPage() {
            return {
                expandedGroup: null,
                showModal: false,
                modalUrl: '',
                modalFileType: 'image',

                toggleGroup(groupKey) {
                    this.expandedGroup = this.expandedGroup === groupKey ? null : groupKey;
                },

                openModal(url) {
                    this.modalUrl = url;
                    this.modalFileType = url.toLowerCase().endsWith('.pdf') ? 'pdf' : 'image';
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                    this.modalUrl = '';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // ==========================================
            // 1. FITUR BUKTI KINERJA (KOMPRESI GAMBAR & DISPLAY)
            // ==========================================
            const fileInput = document.getElementById('file_bukti');
            const fileNameDisplay = document.getElementById('file-name-display');
            const fileStatusText = document.getElementById('file-status-text');

            if (fileInput) {
                fileInput.addEventListener('change', async function (event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    fileStatusText.textContent = "📄 File terpilih: " + file.name;
                    fileNameDisplay.classList.remove('hidden');

                    if (!file.type.startsWith('image/')) {
                        if (file.size > 2 * 1024 * 1024) {
                            alert('Dokumen PDF maksimal berukuran 2 MB.');
                            event.target.value = ''; 
                            fileNameDisplay.classList.add('hidden');
                        } else {
                            fileStatusText.textContent = "📄 Dokumen PDF siap diunggah: " + file.name + " (" + (file.size / 1024).toFixed(1) + " KB)";
                        }
                        return;
                    }

                    const options = { maxSizeMB: 0.8, maxWidthOrHeight: 1920, useWebWorker: true };
                    try {
                        let originalName = file.name;
                        fileStatusText.textContent = "⏳ Mengompres gambar... mohon tunggu sejenak.";
                        
                        const compressedFile = await imageCompression(file, options);
                        
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(new File([compressedFile], originalName, { type: file.type }));
                        fileInput.files = dataTransfer.files;

                        fileStatusText.textContent = "✓ Gambar siap diunggah: " + originalName + " (" + (compressedFile.size / 1024).toFixed(1) + " KB)";
                    } catch (error) {
                        console.error('Terjadi kesalahan saat kompresi:', error);
                        alert('Gagal mengompres gambar otomatis. Silakan coba pilih file gambar lain.');
                        event.target.value = ''; 
                        fileNameDisplay.classList.add('hidden');
                    }
                });
            }

            // ==========================================
            // 2. FITUR FETCH API FILTER PERIODE & PAGINATION
            // ==========================================
            document.addEventListener('submit', function(e) {
                if (e.target && e.target.id === 'form-filter-laporan') {
                    e.preventDefault();
                    const form = e.target;
                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData);
                    const url = form.action + '?' + params.toString();
                    
                    // Update tautan Download PDF secara real-time saat filter disubmit
                    updatePdfLink(params.get('bulan'), params.get('tahun'));
                    loadRiwayatFetch(url);
                }
            });

            document.addEventListener('click', function(e) {
                const target = e.target.closest('#area-riwayat-laporan a[href*="page="]');
                if (target) {
                    e.preventDefault();
                    const url = target.getAttribute('href');
                    if (url && url !== '#') {
                        loadRiwayatFetch(url);
                    }
                }
            });

            function updatePdfLink(bulan, tahun) {
                const pdfBtn = document.getElementById('btn-download-pdf');
                if (pdfBtn && bulan && tahun) {
                    const baseUrl = "{{ route('pjlp.laporan.export_pdf') }}";
                    pdfBtn.setAttribute('href', baseUrl + '?bulan=' + bulan + '&tahun=' + tahun);
                }
            }

            function loadRiwayatFetch(url) {
                const container = document.getElementById('area-riwayat-laporan');
                if (container) {
                    container.style.opacity = '0.5';
                }

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newEl = doc.getElementById('area-riwayat-laporan');

                    if (newEl && container) {
                        if (window.Alpine) {
                            Alpine.destroyTree(container);
                        }

                        const importedEl = document.importNode(newEl, true);
                        importedEl.style.opacity = '1';
                        container.replaceWith(importedEl);

                        window.history.pushState({}, '', url);

                        if (window.Alpine) {
                            Alpine.initTree(importedEl);
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (container) container.style.opacity = '1';
                    alert('Gagal memuat data riwayat laporan.');
                });
            }
        });
    </script>
</x-app-layout>