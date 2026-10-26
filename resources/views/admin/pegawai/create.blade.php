<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Pegawai Baru') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12 pb-28 md:pb-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-2xl shadow-sm">
                    <ul class="list-disc ml-5 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- HEADER KARTU FORMULIR DENGAN NAVIGASI KEMBALI -->
                <div class="p-6 sm:p-8 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3.5">
                    <a href="{{ route('admin.pegawai.index') }}" class="inline-flex items-center justify-center w-10 h-10 bg-white border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-100 hover:text-blue-600 transition shadow-sm flex-shrink-0" title="Kembali ke Daftar Pegawai">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <div>
                        <h3 class="font-bold text-gray-800 text-base">Formulir Pendaftaran Akun Pegawai</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Lengkapi informasi kredensial, jabatan, dan lokasi penugasan pegawai baru.</p>
                    </div>
                </div>

                <div class="p-6 sm:p-8" x-data="{ role: '{{ old('role', 'pjlp') }}' }">
                    <form action="{{ route('admin.pegawai.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        
                        <!-- Nama Lengkap & Foto -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso, S.Kom" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Foto Profil (Opsional)</label>
                                <input type="file" name="foto_profil" accept="image/png, image/jpeg, image/jpg" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-xl p-1.5 cursor-pointer focus:outline-none">
                            </div>
                        </div>

                        <!-- Kredensial (NRK & Password) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">NRK / NIP / ID PJLP</label>
                                <input type="text" name="id_pjlp" value="{{ old('id_pjlp') }}" placeholder="Nomor ID untuk login" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Password Sementara</label>
                                <input type="password" name="password" placeholder="Minimal 6 karakter" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm" required>
                            </div>
                        </div>

                        <!-- Role & Kategori -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Role (Peran Aplikasi)</label>
                                <select name="role" x-model="role" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm bg-gray-50" required>
                                    <option value="pjlp">Pegawai (PJLP)</option>
                                    <option value="p3k">Pegawai (P3K)</option>
                                    <option value="atasan">Atasan (Kasatpel / Kasie / PPTK)</option>
                                    <option value="pimpinan">Pimpinan (Kasudin / Kasubag)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Kategori Status Pegawai</label>
                                <select name="kategori_pegawai" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm bg-white" required>
                                    <option value="">Pilih Status</option>
                                    <option value="PNS" {{ old('kategori_pegawai') == 'PNS' ? 'selected' : '' }}>PNS</option>
                                    <option value="P3K" {{ old('kategori_pegawai') == 'P3K' ? 'selected' : '' }}>P3K</option>
                                    <option value="PJLP" {{ old('kategori_pegawai') == 'PJLP' ? 'selected' : '' }}>PJLP</option>
                                </select>
                            </div>
                        </div>

                        <!-- Kuota Cuti Awal & Urutan Hirarki -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Kuota Cuti Awal (Hari)</label>
                                <input type="number" name="sisa_cuti" value="{{ old('sisa_cuti', 12) }}" min="0" max="100" placeholder="12" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm" required>
                                <p class="text-[11px] text-gray-500 mt-1">*Standar 12 hari, sesuaikan jika pegawai masuk pertengahan tahun.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Urutan Hirarki / Tampil (Opsional)</label>
                                <input type="number" name="urutan_hirarki" value="{{ old('urutan_hirarki') }}" min="1" placeholder="Contoh: 1, 2, 3..." class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                <p class="text-[11px] text-gray-500 mt-1">*Nomor urut posisi untuk laporan rekap & PDF (1 = tertinggi).</p>
                            </div>
                        </div>

                        <!-- Jabatan & Unit Kerja -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Jenis Pekerjaan / Jabatan</label>
                                <select name="jenis_pekerjaan" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm bg-white">
                                    <option value="">-- Pilih Pekerjaan --</option>
                                    <optgroup label="Manajerial">
                                        <option value="Kepala Suku Dinas">Kepala Suku Dinas</option>
                                        <option value="Kepala Subbagian">Kepala Subbagian</option>
                                        <option value="Kepala Seksi">Kepala Seksi</option>
                                        <option value="PPTK">PPTK</option>
                                        <option value="Kasatpel">Kasatpel (Kepala Satuan Pelaksana)</option>
                                    </optgroup>
                                    <optgroup label="Operasional & Teknis">
                                        <option value="Petugas Pendamping Jakpreneur">Petugas Pendamping Jakpreneur</option>
                                        <option value="Petugas Keamanan">Petugas Keamanan</option>
                                        <option value="Petugas Kebersihan">Petugas Kebersihan</option>
                                        <option value="Petugas Kebun">Petugas Kebun</option>
                                        <option value="Petugas Teknis Kesehatan Hewan">Petugas Teknis Kesehatan Hewan</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Unit Kerja / Bidang</label>
                                <select name="unit_kerja" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm bg-white">
                                    <option value="">-- Pilih Unit Kerja --</option>
                                    <option value="Subbagian Tata Usaha">Subbagian Tata Usaha</option>
                                    <option value="Seksi Ketahanan Pangan dan Pertanian">Seksi Ketahanan Pangan dan Pertanian</option>
                                    <option value="Seksi Peternakan dan Kesehatan Hewan">Seksi Peternakan dan Kesehatan Hewan</option>
                                    <option value="Satuan Pelaksana Kecamatan">Satuan Pelaksana Kecamatan</option>
                                    <option value="Agro Edu Wisata (AEW) Ragunan">Agro Edu Wisata (AEW) Ragunan</option>
                                </select>
                            </div>
                        </div>

                        <!-- Lokasi Tugas -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Lokasi Tugas (Kecamatan)</label>
                                <select name="lokasi_tugas" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm bg-white">
                                    <option value="">-- Pilih Lokasi --</option>
                                    <option value="Tingkat Suku Dinas (Pusat)">Tingkat Suku Dinas (Pusat)</option>
                                    <option value="Kecamatan Kebayoran Baru">Kecamatan Kebayoran Baru</option>
                                    <option value="Kecamatan Kebayoran Lama">Kecamatan Kebayoran Lama</option>
                                    <option value="Kecamatan Pesanggrahan">Kecamatan Pesanggrahan</option>
                                    <option value="Kecamatan Cilandak">Kecamatan Cilandak</option>
                                    <option value="Kecamatan Pasar Minggu">Kecamatan Pasar Minggu</option>
                                    <option value="Kecamatan Jagakarsa">Kecamatan Jagakarsa</option>
                                    <option value="Kecamatan Mampang Prapatan">Kecamatan Mampang Prapatan</option>
                                    <option value="Kecamatan Pancoran">Kecamatan Pancoran</option>
                                    <option value="Kecamatan Tebet">Kecamatan Tebet</option>
                                    <option value="Kecamatan Setiabudi">Kecamatan Setiabudi</option>
                                    <option value="AEW Ragunan">AEW Ragunan</option>
                                </select>
                            </div>
                        </div>

                        <!-- Penetapan Atasan Penilai (PJLP & P3K) -->
                        <div x-show="['pjlp', 'p3k'].includes(role)" x-transition class="space-y-4 bg-orange-50/60 p-5 rounded-2xl border border-orange-200/60" style="display: none;">
                            <h4 class="font-bold text-orange-900 text-xs uppercase tracking-wider mb-2">Penetapan Atasan Penilai</h4>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pilih Atasan Langsung</label>
                                <select name="atasan_id" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-orange-500 focus:border-orange-500 shadow-sm bg-white">
                                    <option value="">-- Pilih Atasan --</option>
                                    @foreach($daftarAtasan as $atasan)
                                        <option value="{{ $atasan->id }}" {{ old('atasan_id') == $atasan->id ? 'selected' : '' }}>{{ $atasan->name }} - {{ $atasan->jenis_pekerjaan ?? 'Atasan' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- TOMBOL AKSI -->
                        <div class="pt-5 border-t border-gray-100 flex items-center justify-end space-x-3">
                            <a href="{{ route('admin.pegawai.index') }}" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-50 transition shadow-sm">Batal</a>
                            <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 transition shadow">Simpan Pegawai</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- BOTTOM NAVIGATION BAR (MOBILE ADMIN) -->
            <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-xl z-[90] px-3 py-2 flex justify-around items-center">
                <a href="{{ route('admin.pegawai.index') }}" class="flex flex-col items-center text-blue-600 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span class="text-[10px] mt-1">Pegawai</span>
                </a>
                <a href="{{ route('admin.absensi.import') }}" class="flex flex-col items-center text-gray-500 hover:text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span class="text-[10px] mt-1">Import Absen</span>
                </a>
                <a href="{{ route('admin.hari_libur.index') }}" class="flex flex-col items-center text-gray-500 hover:text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-[10px] mt-1">Hari Libur</span>
                </a>
            </div>

        </div>
    </div>
</x-app-layout>