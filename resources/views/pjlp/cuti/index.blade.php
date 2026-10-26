<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengajuan Cuti Pegawai') }}
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

    <div class="py-6 md:py-12 pb-24 md:pb-12">
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
                    <a href="{{ route('pjlp.dashboard') }}" class="text-center py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ request()->routeIs('pjlp.dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50/50' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('pjlp.laporan.create') }}" class="text-center py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ request()->routeIs('pjlp.laporan.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50/50' }}">
                        Laporan Kinerja
                    </a>
                    <a href="{{ route('pjlp.absensi.index') }}" class="text-center py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ request()->routeIs('pjlp.absensi.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50/50' }}">
                        Rekap Absensi
                    </a>
                    <a href="{{ route('pjlp.cuti.index') }}" class="text-center py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ request()->routeIs('pjlp.cuti.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50/50' }}">
                        Pengajuan Cuti
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Kolom Form Pengajuan -->
                <div class="lg:col-span-1 bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 h-fit">
                    <div class="p-5 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                        <div>
                            <h3 class="font-bold text-gray-800 text-base">Form Cuti</h3>
                            <p class="text-xs text-gray-500">Tahun {{ $tahun }}</p>
                        </div>
                        <span class="bg-blue-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">
                            Sisa: {{ $sisaCuti }} Hari
                        </span>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('pjlp.cuti.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Jenis Cuti</label>
                                <select name="jenis_cuti" class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm p-3" required>
                                    <option value="Tahunan">Cuti Tahunan</option>
                                    <option value="Sakit">Cuti Sakit</option>
                                    <option value="Melahirkan">Cuti Melahirkan</option>
                                    <option value="Alasan Penting">Cuti Alasan Penting</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Tgl Mulai</label>
                                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="block w-full border-gray-300 rounded-xl shadow-sm text-sm p-3 focus:ring-blue-500 focus:border-blue-500" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Tgl Selesai</label>
                                    <input type="date" id="tanggal_selesai" name="tanggal_selesai" class="block w-full border-gray-300 rounded-xl shadow-sm text-sm p-3 focus:ring-blue-500 focus:border-blue-500" required>
                                </div>
                            </div>

                            <!-- KOTAK ESTIMASI HARI KERJA REAL-TIME -->
                            <div id="box-estimasi-hari" class="hidden p-3 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-800">
                                <p class="font-bold flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Estimasi Cuti: <span id="text-jumlah-hari" class="ml-1 font-extrabold text-blue-900">0</span> Hari Kerja
                                </p>
                                <p class="text-[10px] text-blue-600 mt-0.5">*Telah dipotong Sabtu, Minggu & Hari Libur Nasional.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Alasan Lengkap</label>
                                <textarea name="alasan" rows="3" class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm p-3" required placeholder="Contoh: Menjenguk orang tua sakit..."></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Surat Bukti (Opsional)</label>
                                <input type="file" name="file_bukti" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-xl cursor-pointer bg-gray-50" accept=".jpg,.jpeg,.png,.pdf">
                                <p class="text-[10px] text-gray-400 mt-1">*Wajib diisi untuk pengajuan Cuti Sakit (JPG, PNG, PDF maks 2MB).</p>
                            </div>

                            <button type="submit" class="w-full mt-2 inline-flex justify-center items-center px-4 py-3 bg-blue-600 text-white font-bold text-sm rounded-xl shadow hover:bg-blue-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                Ajukan Cuti Sekarang
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Kolom Riwayat Cuti dengan Filter Tahun -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100">
                    <div class="p-5 bg-gray-50 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="font-bold text-gray-800 text-base">Riwayat Pengajuan Cuti</h3>
                            <p class="text-xs text-gray-500">Daftar permohonan cuti tahun {{ $tahun }}.</p>
                        </div>

                        <!-- Form Filter Tahun -->
                        <form method="GET" action="{{ route('pjlp.cuti.index') }}" class="flex items-center gap-2">
                            <select name="tahun" class="border-gray-300 rounded-xl text-xs p-2.5 focus:ring-blue-500 focus:border-blue-500">
                                @for ($y = date('Y'); $y >= 2024; $y--)
                                    <option value="{{ $y }}" {{ $tahun ==$y ? 'selected' : '' }}>
                                        Tahun {{ $y }}
                                    </option>
                                @endfor
                            </select>
                            <button type="submit" class="bg-gray-800 text-white text-xs font-bold py-2.5 px-4 rounded-xl hover:bg-gray-700 transition">
                                Filter
                            </button>
                        </form>
                    </div>
                    
                    <div class="p-6">
                        <table id="tabelCuti" class="w-full text-left border-collapse display responsive nowrap text-sm" style="width:100%">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="p-3 font-semibold text-gray-600">Jenis</th>
                                    <th class="p-3 font-semibold text-gray-600">Tanggal Pelaksanaan</th>
                                    <th class="p-3 font-semibold text-gray-600">Alasan</th>
                                    <th class="p-3 font-semibold text-gray-600 text-center">Status Atasan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($riwayatCuti as $cuti)
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="p-3 font-bold text-gray-800">{{ $cuti->jenis_cuti }}</td>
                                        <td class="p-3 text-gray-700 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->translatedFormat('d M Y') }} 
                                            <span class="text-gray-400 mx-1">s/d</span> 
                                            {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->translatedFormat('d M Y') }}
                                        </td>
                                        <td class="p-3 text-gray-700 whitespace-normal">{{ $cuti->alasan }}</td>
                                        <td class="p-3 text-center align-middle whitespace-nowrap">
                                            @if($cuti->status_validasi == 'pending')
                                                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full">Menunggu</span>
                                            @elseif($cuti->status_validasi == 'disetujui')
                                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full">Disetujui</span>
                                            @else
                                                <div class="flex flex-col items-center">
                                                    <span class="px-2.5 py-1 bg-rose-100 text-rose-800 text-xs font-bold rounded-full mb-1">Ditolak</span>
                                                    @if($cuti->catatan_atasan)
                                                        <span class="text-[10px] text-rose-500 cursor-help" title="{{ $cuti->catatan_atasan }}">(Lihat Catatan)</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script>
        const daftarLibur = @json($daftarLibur ?? []);

        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#tabelCuti')) {
                $('#tabelCuti').DataTable().destroy();
            }
            
            $('#tabelCuti').DataTable({
                responsive: true,
                order: [[1, "desc"]],
                columnDefs: [
                    { targets: "_all", defaultContent: "" }
                ],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampil _MENU_",
                    info: "_START_ - _END_ dari _TOTAL_ data",
                    infoEmpty: "Kosong",
                    zeroRecords: "Belum ada permohonan cuti pada tahun {{ $tahun }}.",
                    paginate: { next: ">>", previous: "<<" }
                }
            });

            $('#tanggal_mulai, #tanggal_selesai').on('change', function() {
                let tglMulaiStr = $('#tanggal_mulai').val();
                let tglSelesaiStr = $('#tanggal_selesai').val();

                if (tglMulaiStr && tglSelesaiStr) {
                    let start = new Date(tglMulaiStr + 'T00:00:00');
                    let end = new Date(tglSelesaiStr + 'T00:00:00');

                    if (start <= end) {
                        let totalHariKerja = 0;
                        let curr = new Date(start);

                        while (curr <= end) {
                            let dayOfWeek = curr.getDay(); 
                            
                            let year = curr.getFullYear();
                            let month = String(curr.getMonth() + 1).padStart(2, '0');
                            let day = String(curr.getDate()).padStart(2, '0');
                            let currStr = `${year}-${month}-${day}`;

                            let isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
                            let isLiburNasional = daftarLibur.includes(currStr);

                            if (!isWeekend && !isLiburNasional) {
                                totalHariKerja++;
                            }

                            curr.setDate(curr.getDate() + 1);
                        }

                        $('#text-jumlah-hari').text(totalHariKerja);
                        $('#box-estimasi-hari').removeClass('hidden');
                    } else {
                        $('#box-estimasi-hari').addClass('hidden');
                    }
                } else {
                    $('#box-estimasi-hari').addClass('hidden');
                }
            });
        });
    </script>
</x-app-layout>