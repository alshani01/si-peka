<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Rekap & Monitoring Penilaian Perilaku') }}
        </h2>
    </x-slot>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="py-6 sm:py-8 pb-28 sm:pb-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" x-data="penilaianPage()">
        
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

        <!-- TAB NAVIGASI DESKTOP (ADMIN & PIMPINAN) -->
        @if (in_array(auth()->user()->role, ['admin', 'pimpinan']))
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
        @endif

        <!-- STATISTIK DASHBOARD -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="p-3 bg-blue-100 text-blue-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wide">Total Pegawai</p>
                    <p class="text-2xl font-extrabold text-gray-800">{{ $totalPjlp ?? 0 }}</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="p-3 bg-emerald-100 text-emerald-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wide">Sudah Dinilai</p>
                    <p class="text-2xl font-extrabold text-gray-800">{{ $totalSudahDinilai ?? 0 }}</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="p-3 bg-rose-100 text-rose-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wide">Belum Dinilai</p>
                    <p class="text-2xl font-extrabold {{ ($totalBelumDinilai ?? 0) > 0 ? 'text-rose-600' : 'text-gray-800' }}">{{ $totalBelumDinilai ?? 0 }}</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="p-3 bg-amber-100 text-amber-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wide">Rata-rata Nilai Pegawai</p>
                    <p class="text-2xl font-extrabold text-gray-800">{{ number_format($rataRataSektor ?? 0, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- FILTER CARD -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <form id="form-filter-penilaian" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5 block">Cari Pegawai / ID</label>
                        <input type="text" id="filter_search" name="search" value="{{ $search }}" placeholder="Nama / ID PJLP..." class="border-gray-300 rounded-xl text-xs p-2.5 w-full focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    @if (in_array(auth()->user()->role, ['admin', 'pimpinan']))
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5 block">Atasan / Penilai</label>
                            <select id="filter_atasan" name="atasan_id" class="border-gray-300 rounded-xl text-xs p-2.5 w-full focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Atasan</option>
                                @foreach ($atasans as $a)
                                    <option value="{{ $a->id }}" {{ $atasan_id == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5 block">Status / Kategori</label>
                        <select id="filter_kategori" name="kategori" class="border-gray-300 rounded-xl text-xs p-2.5 w-full focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Status</option>
                            <option value="belum" {{ $kategori == 'belum' ? 'selected' : '' }}>❌ Belum Dinilai</option>
                            <option value="Sangat Baik" {{ $kategori == 'Sangat Baik' ? 'selected' : '' }}>🌟 Sangat Baik</option>
                            <option value="Baik" {{ $kategori == 'Baik' ? 'selected' : '' }}>🟢 Baik</option>
                            <option value="Cukup" {{ $kategori == 'Cukup' ? 'selected' : '' }}>🟡 Cukup</option>
                            <option value="Kurang" {{ $kategori == 'Kurang' ? 'selected' : '' }}>🔴 Kurang</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5 block">Bulan</label>
                        <select id="filter_bulan" name="bulan" class="border-gray-300 rounded-xl text-xs p-2.5 w-full focus:ring-blue-500 focus:border-blue-500">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate((int)$tahun, (int)$m, 1)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5 block">Tahun</label>
                        <select id="filter_tahun" name="tahun" class="border-gray-300 rounded-xl text-xs p-2.5 w-full focus:ring-blue-500 focus:border-blue-500">
                            @foreach (range(date('Y') - 1, date('Y') + 1) as $y)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" id="btn-reset-filter" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                        Reset
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                        Filter Data
                    </button>
                </div>
            </form>
        </div>

        <!-- ACTION BAR BULK DELETE (KHUSUS ADMIN) -->
        @if (auth()->user()->role === 'admin')
            <div x-show="selectedIds.length > 0" x-cloak class="bg-rose-50 border border-rose-200 p-4 rounded-2xl flex items-center justify-between shadow-sm transition">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span class="text-xs font-bold text-rose-800">
                        Terpilih <span x-text="selectedIds.length"></span> penilaian untuk dihapus
                    </span>
                </div>
                <form id="form-bulk-delete" action="{{ route('admin.penilaian.bulk_delete') }}" method="POST" @submit.prevent="confirmBulkDelete($event)">
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

        <!-- BAR KETANGAHAN & EXPORT PDF -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="font-bold text-gray-800 text-sm sm:text-base">Rekapitulasi Penilaian Perilaku PJLP</h3>
                    <span id="badge-periode" class="px-2.5 py-0.5 bg-blue-50 text-blue-700 text-xs font-extrabold rounded-md border border-blue-100">
                        {{ str_pad($bulan, 2, '0', STR_PAD_LEFT) }}/{{$tahun }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">Sektor Kebon Jeruk - Dinas Penanggulangan Kebakaran dan Penyelamatan</p>
            </div>

            <a id="btn-export-pdf" href="{{ route('admin.penilaian.pdf', request()->query()) }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-sm cursor-pointer">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Unduh Laporan PDF
            </a>
        </div>

        <!-- CONTAINER TABEL UNTUK DITIMPA AJAX -->
        <div id="area-tabel-penilaian" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 uppercase font-bold text-[10px] tracking-wider">
                            @if (auth()->user()->role === 'admin')
                                <th class="p-4 text-center w-12">
                                    <input type="checkbox" @change="toggleSelectAll($event)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                                </th>
                            @endif
                            <th class="p-4 text-center">No</th>
                            <th class="p-4">Pegawai / ID PJLP</th>
                            @if (in_array(auth()->user()->role, ['admin', 'pimpinan']))
                                <th class="p-4">Atasan Langsung</th>
                            @endif
                            <th class="p-4 text-center">Kehadiran</th>
                            <th class="p-4 text-center">T. Jawab</th>
                            <th class="p-4 text-center">Kepatuhan</th>
                            <th class="p-4 text-center">Rata-Rata</th>
                            <th class="p-4 text-center">Kategori</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($bawahans as $index => $row)
                            @php 
                                $nilai = $row->penilaianPerilaku->first();
                                $rataRata = $nilai ? $nilai->rata_rata : null;
                                $kategoriVal = $nilai ? $nilai->kategori : 'Belum Dinilai';
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                @if (auth()->user()->role === 'admin')
                                    <td class="p-4 text-center">
                                        @if($nilai)
                                            <input type="checkbox" value="{{ $nilai->id }}" x-model="selectedIds" class="chk-penilaian rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endif
                                <td class="p-4 text-center font-bold text-gray-500">{{ $bawahans->firstItem() + $index }}</td>
                                <td class="p-4">
                                    <div class="font-bold text-gray-800">{{ $row->name }}</div>
                                    <div class="font-mono text-[10px] text-gray-500 mt-0.5">{{ $row->id_pjlp }}</div>
                                </td>
                                @if (in_array(auth()->user()->role, ['admin', 'pimpinan']))
                                    <td class="p-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 rounded-lg font-semibold text-gray-600 border border-gray-200">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            {{ $row->atasan->name ?? '-' }}
                                        </span>
                                    </td>
                                @endif
                                <td class="p-4 text-center font-bold text-gray-700">{{ $nilai ? $nilai->nilai_kehadiran : '-' }}</td>
                                <td class="p-4 text-center font-bold text-gray-700">{{ $nilai ? $nilai->nilai_tanggung_jawab : '-' }}</td>
                                <td class="p-4 text-center font-bold text-gray-700">{{ $nilai ? $nilai->nilai_kepatuhan : '-' }}</td>
                                <td class="p-4 text-center font-extrabold text-blue-700 text-sm">{{ $rataRata ?? '-' }}</td>
                                <td class="p-4 text-center">
                                    @if ($kategoriVal === 'Sangat Baik') <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-extrabold rounded-full text-[10px]">Sangat Baik</span>
                                    @elseif ($kategoriVal === 'Baik') <span class="px-2.5 py-1 bg-blue-100 text-blue-800 font-extrabold rounded-full text-[10px]">Baik</span>
                                    @elseif ($kategoriVal === 'Cukup') <span class="px-2.5 py-1 bg-amber-100 text-amber-800 font-extrabold rounded-full text-[10px]">Cukup</span>
                                    @elseif ($kategoriVal === 'Kurang') <span class="px-2.5 py-1 bg-rose-100 text-rose-800 font-extrabold rounded-full text-[10px]">Kurang</span>
                                    @else <span class="px-2.5 py-1 bg-gray-100 text-gray-500 font-extrabold rounded-full text-[10px]">Belum Dinilai</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" @click="openModal({{ $row->id }}, '{{ addslashes($row->name) }}', {{$tahun }})" class="p-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded-xl transition shadow-sm" title="Lihat Tren Tahunan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                                        </button>
                                        
                                        <!-- TOMBOL HAPUS (KHUSUS ADMIN) -->
                                        @if(auth()->user()->role === 'admin' && $nilai)
                                            <form action="{{ route('admin.penilaian.destroy', $nilai->id) }}" method="POST" class="inline" @submit.prevent="confirmSingleDelete($event, '{{ addslashes($row->name) }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-xl transition shadow-sm" title="Hapus Penilaian">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="p-8 text-center text-gray-400 italic">Data tidak ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- TAMPILAN MOBILE -->
            <div class="md:hidden divide-y divide-gray-100">
                @forelse ($bawahans as $index => $row)
                    @php 
                        $nilai = $row->penilaianPerilaku->first();
                        $kategoriVal = $nilai ? $nilai->kategori : 'Belum Dinilai';
                    @endphp
                    <div class="bg-white">
                        <div class="p-4 flex items-center justify-between cursor-pointer hover:bg-gray-50 transition" @click="expandedRow = expandedRow === {{ $index }} ? null : {{ $index }}">
                            <div>
                                <h5 class="font-bold text-gray-800 text-xs">{{ $row->name }}</h5>
                                <p class="text-[10px] text-gray-500 mt-0.5">Penilai: <span class="font-semibold">{{ $row->atasan->name ?? '-' }}</span></p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if ($kategoriVal === 'Belum Dinilai') <span class="px-2 py-0.5 bg-gray-100 text-gray-500 font-extrabold rounded-full text-[9px]">Belum</span>
                                @else <span class="px-2 py-0.5 bg-blue-100 text-blue-800 font-extrabold rounded-full text-[9px]">{{ $nilai->rata_rata }}</span> @endif
                                <svg class="w-4 h-4 text-gray-400 transform transition-transform" :class="{'rotate-180': expandedRow === {{ $index }}}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>

                        <div x-show="expandedRow === {{ $index }}" x-cloak style="display: none;" class="p-4 bg-gray-50/80 border-t border-gray-100 text-xs space-y-3">
                            <div class="grid grid-cols-3 gap-2 text-center border-b border-gray-200 pb-3">
                                <div><span class="block text-[10px] font-bold text-gray-400">Kehadiran</span><span class="font-bold">{{ $nilai ? $nilai->nilai_kehadiran : '-' }}</span></div>
                                <div><span class="block text-[10px] font-bold text-gray-400">Tanggung Jwb</span><span class="font-bold">{{ $nilai ? $nilai->nilai_tanggung_jawab : '-' }}</span></div>
                                <div><span class="block text-[10px] font-bold text-gray-400">Kepatuhan</span><span class="font-bold">{{ $nilai ? $nilai->nilai_kepatuhan : '-' }}</span></div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-gray-600">Kategori: <span class="text-gray-900">{{ $kategoriVal }}</span></span>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="openModal({{ $row->id }}, '{{ addslashes($row->name) }}', {{$tahun }})" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 text-[10px] font-bold rounded-lg border border-indigo-100">
                                        Lihat Tren
                                    </button>
                                    @if(auth()->user()->role === 'admin' && $nilai)
                                        <form action="{{ route('admin.penilaian.destroy', $nilai->id) }}" method="POST" class="inline" @submit.prevent="confirmSingleDelete($event, '{{ addslashes($row->name) }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-2 py-1.5 bg-rose-50 text-rose-700 text-[10px] font-bold rounded-lg border border-rose-100">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-gray-400 italic">Data tidak ditemukan.</div>
                @endforelse
            </div>

            @if ($bawahans->hasPages())
                <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $bawahans->links() }}
                </div>
            @endif
        </div>

        <!-- BOTTOM NAVIGATION BAR (MOBILE ADMIN & PIMPINAN) -->
        @if (in_array(auth()->user()->role, ['admin', 'pimpinan']))
            <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-xl z-[90] px-2 py-2 flex justify-around items-center">
                <a href="{{ route('admin.pegawai.index') }}" class="flex flex-col items-center text-gray-500 hover:text-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span class="text-[9px] mt-1">Pegawai</span>
                </a>
                <a href="{{ route('admin.absensi.rekap') }}" class="flex flex-col items-center text-gray-500 hover:text-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-[9px] mt-1">Absensi</span>
                </a>
                <a href="{{ route('admin.kinerja.rekap') }}" class="flex flex-col items-center text-gray-500 hover:text-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-[9px] mt-1">Kinerja</span>
                </a>
                <a href="{{ route('admin.penilaian.rekap') }}" class="flex flex-col items-center text-blue-600 font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    <span class="text-[9px] mt-1">Perilaku</span>
                </a>
            </div>
        @endif

        <!-- MODAL KONFIRMASI HAPUS (KUSTOM) -->
        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-[110] flex items-center justify-center p-4">
            <div x-show="showDeleteModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeDeleteModal()"></div>
            <div x-show="showDeleteModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl z-10 text-center space-y-4 border border-gray-100">
                <div class="w-14 h-14 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center mx-auto shadow-sm">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="font-extrabold text-gray-800 text-base md:text-lg">Konfirmasi Hapus</h3>
                    <p class="text-xs text-gray-500 mt-2" x-text="deleteModalMessage"></p>
                </div>
                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="closeDeleteModal()" class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">Batal</button>
                    <button type="button" @click="executeDelete()" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl transition shadow-md">Ya, Hapus</button>
                </div>
            </div>
        </div>

        <!-- MODAL TREN KINERJA -->
        <div x-show="isOpen" x-cloak class="fixed inset-0 z-[100] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:block sm:p-0">
                <div x-show="isOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="isOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="isOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-100">
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-sm font-bold text-gray-800">Tren Kinerja <span x-text="tahunTren" class="text-indigo-600"></span></h3>
                        <button type="button" @click="isOpen = false" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="px-4 py-5 sm:p-6 bg-white">
                        <p class="text-xs text-gray-500 mb-4">Nama Pegawai: <span class="font-bold text-gray-800" x-text="pegawaiName"></span></p>
                        <div x-show="isLoading" class="flex justify-center items-center py-8">
                            <svg class="w-8 h-8 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>
                        <div x-show="!isLoading" class="space-y-2 max-h-64 overflow-y-auto pr-2">
                            <template x-for="item in trendData" :key="item.id">
                                <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 bg-gray-50 hover:bg-gray-100 transition">
                                    <div class="flex items-center gap-3 w-1/3">
                                        <span class="w-8 h-8 flex items-center justify-center bg-white rounded-lg text-[10px] font-extrabold text-gray-500 shadow-sm border border-gray-100" x-text="getBulanName(item.bulan)"></span>
                                    </div>
                                    <div class="flex-1 px-3">
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 mb-1">
                                            <div class="bg-indigo-600 h-1.5 rounded-full" :style="'width: ' + item.rata_rata + '%'"></div>
                                        </div>
                                        <div class="text-[9px] font-bold text-gray-400">Rata-rata: <span class="text-gray-700" x-text="item.rata_rata"></span></div>
                                    </div>
                                    <div class="w-1/4 text-right">
                                        <span class="px-2 py-0.5 text-[9px] font-extrabold rounded-full" :class="getBadgeClass(item.kategori)" x-text="item.kategori"></span>
                                    </div>
                                </div>
                            </template>
                            <template x-if="trendData.length === 0">
                                <div class="text-center py-8 text-xs text-gray-400 italic">Belum ada riwayat penilaian di tahun ini.</div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('penilaianPage', () => ({
                expandedRow: null,
                selectedIds: [],
                
                // Data Tren
                isOpen: false,
                isLoading: false,
                pegawaiName: '',
                tahunTren: {{ date('Y') }},
                userId: null,
                trendData: [],

                // Data Delete Modal
                showDeleteModal: false,
                deleteModalMessage: '',
                targetForm: null,

                openModal(id, name, tahun) {
                    this.userId = id;
                    this.pegawaiName = name;
                    this.tahunTren = tahun;
                    this.isOpen = true;
                    this.fetchTrend();
                },

                fetchTrend() {
                    this.isLoading = true;
                    this.trendData = [];
                    fetch(`/admin/penilaian/trend/${this.userId}?tahun=${this.tahunTren}`)
                        .then(res => res.json())
                        .then(data => {
                            this.trendData = data;
                            this.isLoading = false;
                        })
                        .catch(err => {
                            console.error(err);
                            this.isLoading = false;
                        });
                },

                getBulanName(bulanAngka) {
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                    return months[bulanAngka - 1];
                },

                getBadgeClass(kategori) {
                    switch (kategori) {
                        case 'Sangat Baik': return 'bg-emerald-100 text-emerald-800';
                        case 'Baik':        return 'bg-blue-100 text-blue-800';
                        case 'Cukup':       return 'bg-amber-100 text-amber-800';
                        default:            return 'bg-rose-100 text-rose-800';
                    }
                },

                // Logic Checkbox & Delete
                toggleSelectAll(event) {
                    let checkboxes = document.querySelectorAll('.chk-penilaian');
                    if (event.target.checked) {
                        checkboxes.forEach(cb => {
                            if (!this.selectedIds.includes(cb.value)) this.selectedIds.push(cb.value);
                        });
                    } else {
                        checkboxes.forEach(cb => {
                            this.selectedIds = this.selectedIds.filter(id => id !== cb.value);
                        });
                    }
                },

                confirmSingleDelete(event, namaPegawai) {
                    this.targetForm = event.target;
                    this.deleteModalMessage = `Apakah Anda yakin ingin menghapus data penilaian perilaku pegawai (${namaPegawai}) secara permanen?`;
                    this.showDeleteModal = true;
                },

                confirmBulkDelete(event) {
                    if (this.selectedIds.length === 0) return;
                    this.targetForm = event.target;
                    this.deleteModalMessage = `Apakah Anda yakin ingin menghapus ${this.selectedIds.length} data penilaian perilaku yang telah dipilih secara bersamaan?`;
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
            }))
        });

        $(document).ready(function() {
            function loadDataAjax(url) {
                $('#area-tabel-penilaian').css('opacity', '0.5');

                $.get(url, function(data) {
                    let newTable = $(data).find('#area-tabel-penilaian').html();
                    $('#area-tabel-penilaian').html(newTable).css('opacity', '1');
                    
                    let queryParams = $('#form-filter-penilaian').serialize();
                    let newPdfUrl = "{{ route('admin.penilaian.pdf') }}?" + queryParams;
                    $('#btn-export-pdf').attr('href', newPdfUrl);

                    let bulanVal = String($('#filter_bulan').val()).padStart(2, '0');
                    let tahunVal = $('#filter_tahun').val();
                    $('#badge-periode').text(bulanVal + '/' + tahunVal);

                    window.history.pushState({}, '', url);
                }).fail(function() {
                    $('#area-tabel-penilaian').css('opacity', '1');
                });
            }

            $('#form-filter-penilaian').on('submit', function(e) {
                e.preventDefault();
                let formData = $(this).serialize();
                let targetUrl = "{{ route('admin.penilaian.rekap') }}?" + formData;
                loadDataAjax(targetUrl);
            });

            $('#btn-reset-filter').on('click', function() {
                $('#filter_search').val('');
                @if(in_array(auth()->user()->role, ['admin', 'pimpinan']))
                    $('#filter_atasan').val('');
                @endif
                $('#filter_kategori').val('');
                $('#filter_bulan').val('{{ date('n') }}');
                $('#filter_tahun').val('{{ date('Y') }}');
                $('#form-filter-penilaian').submit();
            });

            $(document).on('click', '#area-tabel-penilaian a[href*="page="]', function(e) {
                e.preventDefault();
                let targetUrl = $(this).attr('href');
                if (targetUrl && targetUrl !== '#') {
                    loadDataAjax(targetUrl);
                }
            });

            // TRIGGER UNDUH LANGSUNG BERKAS TANPA PINDAH / RELOAD
            $(document).on('click', '#btn-export-pdf', function(e) {
                e.preventDefault();
                let downloadUrl = $(this).attr('href');
                window.location.href = downloadUrl;
            });
        });
    </script>
</x-app-layout>