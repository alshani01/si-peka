<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Rekapitulasi Presensi / Absensi') }}
        </h2>
        <!-- CSS DataTables & Ekstensi Responsive -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
        <style>
            .dataTables_wrapper .dataTables_filter input {
                border: 1px solid #d1d5db; border-radius: 0.5rem; padding: 0.25rem 0.5rem; outline: none;
            }
            .dataTables_wrapper .dataTables_filter input:focus {
                border-color: #3b82f6; ring: 1px solid #3b82f6;
            }
            .dataTables_wrapper .dataTables_length select {
                border: 1px solid #d1d5db; border-radius: 0.5rem; padding: 0.25rem 2rem 0.25rem 0.5rem;
            }
        </style>
    </x-slot>

    <!-- Bungkus seluruh halaman dengan Alpine data untuk mengontrol Modal secara Global -->
    <div class="py-6 md:py-12 pb-24 md:pb-12" x-data="{ activeModal: null, activeData: {} }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Notifikasi Sukses -->
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

            <!-- Notifikasi Error -->
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

            <!-- TAB MENU NAVIGATION -->
            <div class="bg-white p-2 rounded-2xl border border-gray-100 shadow-sm overflow-x-auto">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 min-w-[500px] sm:min-w-0">
                    <!-- Dashboard -->
                    <a href="{{ route('pjlp.dashboard') }}" 
                       class="text-center py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ request()->routeIs('pjlp.dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50/50' }}">
                        Dashboard
                    </a>

                    <!-- Laporan Kinerja -->
                    <a href="{{ route('pjlp.laporan.create') }}" 
                       class="text-center py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ request()->routeIs('pjlp.laporan.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50/50' }}">
                        Laporan Kinerja
                    </a>

                    <!-- Rekap Absensi -->
                    <a href="{{ route('pjlp.absensi.index') }}" 
                       class="text-center py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ request()->routeIs('pjlp.absensi.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50/50' }}">
                        Rekap Absensi
                    </a>

                    <!-- Pengajuan Cuti -->
                    <a href="{{ route('pjlp.cuti.index') }}" 
                       class="text-center py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ request()->routeIs('pjlp.cuti.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50/50' }}">
                        Pengajuan Cuti
                    </a>
                </div>
            </div>

            <!-- KARTU STATISTIK RINGKAS -->
            <div id="area-statistik-absensi">
                @isset($rekapStatistik)
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2.5">
                            <div class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 text-sm">REKAPITULASI PRESENSI</h3>
                                <p class="text-[10px] text-gray-400">Akumulasi periode terpilih.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                        <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100 text-center">
                            <span class="text-sm">💊</span>
                            <p class="text-[9px] font-bold text-gray-400 uppercase mt-0.5">Sakit</p>
                            <p class="text-sm font-extrabold text-gray-800">{{ $rekapStatistik['total_sakit'] ?? 0 }}</p>
                        </div>
                        <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100 text-center">
                            <span class="text-sm">⚠️</span>
                            <p class="text-[9px] font-bold text-gray-400 uppercase mt-0.5">Alpa</p>
                            <p class="text-sm font-extrabold text-rose-600">{{ $rekapStatistik['total_alpa'] ?? 0 }}</p>
                        </div>
                        <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100 text-center">
                            <span class="text-sm">📄</span>
                            <p class="text-[9px] font-bold text-gray-400 uppercase mt-0.5">Ijin</p>
                            <p class="text-sm font-extrabold text-gray-800">{{ $rekapStatistik['total_izin'] ?? 0 }}</p>
                        </div>
                        <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100 text-center">
                            <span class="text-sm">🌴</span>
                            <p class="text-[9px] font-bold text-gray-400 uppercase mt-0.5">Sisa Cuti</p>
                            <p class="text-sm font-extrabold text-gray-800">{{ $rekapStatistik['sisa_cuti'] ?? '-' }}</p>
                        </div>
                        <div class="bg-amber-50/50 p-2.5 rounded-xl border border-amber-100 text-center">
                            <span class="text-sm">⏰</span>
                            <p class="text-[9px] font-bold text-amber-700 uppercase mt-0.5">Telat</p>
                            <p class="text-sm font-extrabold text-amber-800">{{ $rekapStatistik['total_terlambat'] ?? 0 }}m</p>
                        </div>
                        <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100 text-center">
                            <span class="text-sm">🏃</span>
                            <p class="text-[9px] font-bold text-gray-400 uppercase mt-0.5">Pulang Cepat</p>
                            <p class="text-sm font-extrabold text-gray-800">{{ $rekapStatistik['total_pulang_cepat'] ?? 0 }}m</p>
                        </div>
                    </div>
                </div>
                @endisset
            </div>

            <!-- AREA TABEL DETAIL PRESENSI -->
            <div id="area-tabel-absensi" class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 transition-opacity duration-200">
                
                <!-- Header & Form Filter -->
                <div class="p-4 sm:p-6 border-b border-gray-100 bg-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-gray-800 text-base">Detail Rekap Presensi / Absensi</h3>
                        <p class="text-xs text-gray-500">Rincian kehadiran harian lengkap dengan status dan catatan klarifikasi.</p>
                    </div>
                    
                    @php
                        $namaBulan = [
                            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                        ];
                    @endphp

                    <!-- Form Filter Bulan & Tahun -->
                    <form id="form-filter-absensi" method="GET" action="{{ route('pjlp.absensi.index') }}" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <select name="bulan" class="border-gray-300 rounded-lg text-sm p-2 w-full sm:w-36 focus:ring-blue-500 focus:border-blue-500">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ sprintf('%02d', $m) }}" {{ (isset($bulan) ? $bulan : date('m')) == sprintf('%02d', $m) ? 'selected' : '' }}>
                                    {{ $namaBulan[$m] }}
                                </option>
                            @endforeach
                        </select>
                        <select name="tahun" class="border-gray-300 rounded-lg text-sm p-2 w-full sm:w-28 focus:ring-blue-500 focus:border-blue-500">
                            @foreach(range(date('Y'), 2024) as $y)
                                <option value="{{ $y }}" {{ (isset($tahun) ? $tahun : date('Y')) == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-gray-800 text-white text-sm font-bold py-2 px-4 rounded-lg hover:bg-gray-700 w-full sm:w-auto transition">
                            Filter
                        </button>
                    </form>
                </div>

                <div class="p-4 sm:p-6">
                    <table id="tabelAbsensi" class="w-full text-left border-collapse display responsive nowrap text-sm" style="width:100%">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="p-3 font-semibold text-gray-600" data-priority="1">#</th>
                                <th class="p-3 font-semibold text-gray-600" data-priority="1">Tanggal</th>
                                <th class="p-3 font-semibold text-gray-600" data-priority="2">Keterangan</th>
                                <th class="p-3 font-semibold text-gray-600" data-priority="5">Jam Datang</th>
                                <th class="p-3 font-semibold text-gray-600" data-priority="6">Jam Pulang</th>
                                <th class="p-3 font-semibold text-gray-600" data-priority="4">Terlambat</th>
                                <th class="p-3 font-semibold text-gray-600" data-priority="7">Pulang Cepat</th>
                                <th class="p-3 font-semibold text-gray-600 text-center" data-priority="3">Aksi Klarifikasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($riwayatAbsensi as $index => $absen)
                                @php
                                    $carbonDate = \Carbon\Carbon::parse($absen->tanggal);
                                    $searchString = $carbonDate->format('Y-m-d') . ' ' . $carbonDate->format('d M Y') . ' ' . $carbonDate->translatedFormat('d M Y');
                                @endphp
                                <tr class="hover:bg-gray-50 transition {{ in_array($absen->status_kehadiran, ['libur', 'alpa']) ? 'bg-rose-50/20' : '' }}">
                                    <!-- 1. Nomor -->
                                    <td class="p-3 text-gray-500">{{ $index + 1 }}</td>
                                    
                                    <!-- 2. Tanggal -->
                                    <td class="p-3 font-bold text-gray-800 whitespace-nowrap" data-sort="{{ $absen->tanggal }}" data-search="{{ $searchString }}">
                                        {{ $carbonDate->translatedFormat('d M Y') }}
                                    </td>
                                    
                                    <!-- 3. Status Keterangan -->
                                    <td class="p-3">
                                        @if($absen->status_kehadiran == 'hadir')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Hadir</span>
                                        @elseif($absen->status_kehadiran == 'terlambat')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Terlambat</span>
                                        @elseif($absen->status_kehadiran == 'pulang_cepat')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-800">Pulang Cepat</span>
                                        @elseif($absen->status_kehadiran == 'cuti')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800">Cuti</span>
                                        @elseif($absen->status_kehadiran == 'sakit')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">Sakit</span>
                                        @elseif($absen->status_kehadiran == 'izin')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">Izin</span>
                                        @elseif($absen->status_kehadiran == 'alpa')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Alpa</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800 capitalize">{{ $absen->status_kehadiran }}</span>
                                        @endif
                                    </td>
                                    
                                    <!-- 4. Jam Datang -->
                                    <td class="p-3 text-gray-700 font-medium">
                                        {{ $absen->jam_masuk ? \Carbon\Carbon::parse($absen->jam_masuk)->format('H:i') . ' WIB' : '-' }}
                                    </td>

                                    <!-- 5. Jam Pulang -->
                                    <td class="p-3 text-gray-700 font-medium">
                                        {{ $absen->jam_pulang ? \Carbon\Carbon::parse($absen->jam_pulang)->format('H:i') . ' WIB' : '-' }}
                                    </td>

                                    <!-- 6. Menit Terlambat -->
                                    <td class="p-3">
                                        @if($absen->menit_terlambat > 0)
                                            <span class="text-amber-600 font-bold">{{ $absen->menit_terlambat }} menit</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <!-- 7. Menit Pulang Cepat -->
                                    <td class="p-3">
                                        @if($absen->menit_pulang_cepat > 0)
                                            <span class="text-orange-600 font-bold">{{ $absen->menit_pulang_cepat }} menit</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <!-- 8. Aksi Klarifikasi -->
                                    <td class="p-3 text-center whitespace-nowrap">
                                        @if(in_array($absen->status_kehadiran, ['terlambat', 'alpa', 'pulang_cepat', 'izin', 'sakit']))
                                            @if($absen->status_klarifikasi == 'pending')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    ⏳ Menunggu
                                                </span>
                                            @elseif($absen->status_klarifikasi == 'disetujui')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    ✓ Disetujui
                                                </span>
                                            @else
                                                <button @click="activeModal = '{{ $absen->id }}'; activeData = { tanggal: '{{ $carbonDate->translatedFormat('d F Y') }}', status: '{{ strtoupper($absen->status_kehadiran) }}', alasan: '{{ addslashes($absen->alasan_klarifikasi) }}', catatan: '{{ addslashes($absen->catatan_atasan) }}', actionUrl: '{{ route('pjlp.absensi.klarifikasi', $absen->id) }}' }" class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-xs transition shadow-sm">
                                                    {{ $absen->status_klarifikasi == 'ditolak' ? '⚠️ Klarifikasi Ulang' : '✏️ Ajukan Klarifikasi' }}
                                                </button>
                                            @endif
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- GLOBAL MODAL KLARIFIKASI -->
        <div x-show="activeModal !== null" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="activeModal !== null" x-transition.opacity class="fixed inset-0 transition-opacity" @click="activeModal = null">
                    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="activeModal !== null" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    
                    <form :action="activeData.actionUrl" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-6 pt-6 pb-4 relative">
                            <!-- Tombol Silang -->
                            <button type="button" @click="activeModal = null" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>

                            <h3 class="text-base sm:text-lg font-bold text-blue-800 border-b pb-3 mb-4 pr-8 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Form Klarifikasi Kehadiran
                            </h3>
                            
                            <!-- Rincian Tanggal & Status -->
                            <div class="mb-4 bg-blue-50/60 p-3.5 rounded-xl text-xs sm:text-sm text-left border border-blue-100 space-y-1">
                                <p><strong>Tanggal Presensi:</strong> <span x-text="activeData.tanggal" class="text-gray-800 font-semibold"></span></p>
                                <p><strong>Status Terdeteksi:</strong> <span class="uppercase text-rose-600 font-bold" x-text="activeData.status"></span></p>
                            </div>

                            <!-- Catatan Penolakan Atasan -->
                            <template x-if="activeData.catatan">
                                <div class="mb-4 p-3 bg-amber-50 border-l-4 border-amber-400 rounded-r-xl text-xs text-amber-900">
                                    <p class="font-bold flex items-center">⚠️ Catatan Penolakan Atasan:</p>
                                    <p class="mt-0.5 italic text-amber-800" x-text="'&quot;' + activeData.catatan + '&quot;'"></p>
                                </div>
                            </template>
                            
                            <!-- Input Alasan Klarifikasi -->
                            <div class="mb-4 text-left">
                                <label class="block text-gray-700 text-xs font-bold uppercase tracking-wider mb-2">Alasan / Keterangan Kendala <span class="text-rose-500">*</span></label>
                                <textarea name="alasan_klarifikasi" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-xs sm:text-sm border-gray-300 rounded-xl p-3" required placeholder="Jelaskan alasan keterlambatan / tidak hadir secara spesifik..." x-model="activeData.alasan"></textarea>
                            </div>
                            
                            <!-- Input File Unggah Bukti -->
                            <div class="mb-2 text-left">
                                <label class="block text-gray-700 text-xs font-bold uppercase tracking-wider mb-2">Lampiran Bukti (Surat / Foto Tangkapan Layar)</label>
                                <input type="file" name="file_bukti_absensi" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-xl cursor-pointer bg-gray-50">
                                <p class="text-[10px] text-gray-400 mt-1">*Format: JPG, PNG, PDF (Maksimal 2MB)</p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-2">
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-blue-600 text-xs sm:text-sm font-bold text-white hover:bg-blue-700 focus:outline-none transition">Kirim Klarifikasi</button>
                            <button type="button" @click="activeModal = null" class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-5 py-2.5 bg-white text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none transition">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <!-- BOTTOM NAVIGATION BAR (Khusus Mobile) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40 px-4 py-2 flex justify-around items-center">
        <a href="{{ route('pjlp.dashboard') }}" class="flex flex-col items-center {{ request()->routeIs('pjlp.dashboard') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="text-[10px] font-semibold mt-1">Dashboard</span>
        </a>
        <a href="{{ route('pjlp.laporan.create') }}" class="flex flex-col items-center {{ request()->routeIs('pjlp.laporan.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span class="text-[10px] font-semibold mt-1">Laporan</span>
        </a>
        <a href="{{ route('pjlp.absensi.index') }}" class="flex flex-col items-center {{ request()->routeIs('pjlp.absensi.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-[10px] font-semibold mt-1">Absensi</span>
        </a>
        <a href="{{ route('pjlp.cuti.index') }}" class="flex flex-col items-center {{ request()->routeIs('pjlp.cuti.*') ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="text-[10px] font-semibold mt-1">Cuti</span>
        </a>
    </div>

    <!-- Script Library DataTables & AJAX -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    
    <script>
        $(document).ready(function() {
            function initDataTables() {
                if ($.fn.DataTable.isDataTable('#tabelAbsensi')) {
                    $('#tabelAbsensi').DataTable().destroy();
                }
                
                $('#tabelAbsensi').DataTable({
                    responsive: true,
                    order: [[1, "asc"]],
                    columnDefs: [
                        { targets: "_all", defaultContent: "" }
                    ],
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampil _MENU_",
                        info: "_START_ - _END_ dari _TOTAL_ data",
                        infoEmpty: "Kosong",
                        zeroRecords: "Belum ada data absensi pada periode ini.",
                        paginate: { next: ">>", previous: "<<" }
                    }
                });
            }

            initDataTables();

            // AJAX FILTER PERIODE
            $(document).on('submit', '#form-filter-absensi', function(e) {
                e.preventDefault();
                let targetUrl = $(this).attr('action') + '?' + $(this).serialize();
                loadAbsensiAjax(targetUrl);
            });

            function loadAbsensiAjax(url) {
                $('#area-statistik-absensi, #area-tabel-absensi').css('opacity', '0.5');

                $.get(url, function(data) {
                    if ($.fn.DataTable.isDataTable('#tabelAbsensi')) {
                        $('#tabelAbsensi').DataTable().destroy();
                    }

                    let newStatistik = $(data).find('#area-statistik-absensi').html();
                    let newTabel = $(data).find('#area-tabel-absensi').html();
                    
                    $('#area-statistik-absensi').html(newStatistik).css('opacity', '1');
                    $('#area-tabel-absensi').html(newTabel).css('opacity', '1');

                    initDataTables();
                    window.history.pushState({}, '', url);
                }).fail(function() {
                    $('#area-statistik-absensi, #area-tabel-absensi').css('opacity', '1');
                    alert('Gagal memuat data filter absensi.');
                });
            }
        });
    </script>
</x-app-layout>