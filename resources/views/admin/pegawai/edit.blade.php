<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Data Pegawai') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12 pb-28 md:pb-12" x-data="{ 
        role: '{{ old('role', $user->role) }}', 
        status: '{{ old('status', $user->status) }}',
        initialRole: '{{ $user->role }}',
        showResetModal: false
    }">
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
                <!-- HEADER KARTU FORMULIR DENGAN NAVIGASI KEMBALI & RESET PASSWORD -->
                <div class="p-6 sm:p-8 border-b border-gray-100 bg-gray-50 flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-3.5">
                        <a href="{{ route('admin.pegawai.index') }}" class="inline-flex items-center justify-center w-10 h-10 bg-white border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-100 hover:text-blue-600 transition shadow-sm flex-shrink-0" title="Kembali ke Daftar Pegawai">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                        </a>
                        <div>
                            <h3 class="font-bold text-gray-800 text-base">Perbarui Data Pegawai</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Edit kredensial akun dan status penugasan dari <strong class="text-gray-800">{{ $user->name }}</strong>.</p>
                        </div>
                    </div>

                    <!-- TOMBOL RESET PASSWORD (PEMICU MODAL) -->
                    <button type="button" @click="showResetModal = true" class="px-3.5 py-2 bg-red-50 text-red-700 border border-red-200 rounded-xl text-xs font-bold hover:bg-red-100 transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        Reset Password
                    </button>
                </div>

                <div class="p-6 sm:p-8">
                    <form action="{{ route('admin.pegawai.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        @method('PUT')
                        
                        <!-- PREVIEW FOTO LAMA & GANTI FOTO -->
                        <div class="flex items-center gap-4 bg-gray-50 p-4 rounded-2xl border border-gray-200">
                            <div class="shrink-0">
                                @if($user->foto_profil)
                                    <img src="{{ asset('storage/' . $user->foto_profil) }}" alt="{{ $user->name }}" class="w-14 h-14 rounded-2xl object-cover border-2 border-white shadow-sm">
                                @else
                                    <div class="w-14 h-14 rounded-2xl bg-blue-600 text-white font-bold text-lg flex items-center justify-center shadow-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Ganti Foto Profil (Opsional)</label>
                                <input type="file" name="foto_profil" accept="image/png, image/jpeg, image/jpg" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 cursor-pointer focus:outline-none">
                            </div>
                        </div>

                        <!-- Nama Lengkap & ID Login -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">NRK / NIP / ID PJLP (ID Login)</label>
                                <input type="text" name="id_pjlp" value="{{ old('id_pjlp', $user->id_pjlp) }}" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm" required>
                            </div>
                        </div>

                        <!-- Role, Kategori & Status -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-gray-100 pt-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Role (Peran Aplikasi)</label>
                                <select name="role" x-model="role" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm bg-gray-50" required>
                                    <option value="pjlp" {{ old('role', $user->role) == 'pjlp' ? 'selected' : '' }}>Pegawai (PJLP)</option>
                                    <option value="p3k" {{ old('role', $user->role) == 'p3k' ? 'selected' : '' }}>Pegawai (P3K)</option>
                                    <option value="atasan" {{ old('role', $user->role) == 'atasan' ? 'selected' : '' }}>Atasan (Kasatpel / Kasie / PPTK)</option>
                                    <option value="pimpinan" {{ old('role', $user->role) == 'pimpinan' ? 'selected' : '' }}>Pimpinan (Kasudin / Kasubag)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Kategori Status Pegawai</label>
                                <select name="kategori_pegawai" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm bg-white" required>
                                    @php $kat = old('kategori_pegawai', $user->kategori_pegawai); @endphp
                                    <option value="PNS" {{ $kat == 'PNS' ? 'selected' : '' }}>PNS</option>
                                    <option value="P3K" {{ $kat == 'P3K' ? 'selected' : '' }}>P3K</option>
                                    <option value="P3K Paruh Waktu" {{ $kat == 'P3K Paruh Waktu' ? 'selected' : '' }}>P3K Paruh Waktu</option>
                                    <option value="PJLP" {{ $kat == 'PJLP' ? 'selected' : '' }}>PJLP</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Status Akun</label>
                                <select name="status" x-model="status" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm bg-white" required>
                                    <option value="aktif" {{ old('status', $user->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="nonaktif" {{ old('status', $user->status) == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <!-- Kuota Cuti & Urutan Hirarki -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Sisa Kuota Cuti (Hari)</label>
                                <input type="number" name="sisa_cuti" value="{{ old('sisa_cuti', $user->sisa_cuti) }}" min="0" max="100" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Urutan Hirarki / Tampil (Opsional)</label>
                                <input type="number" name="urutan_hirarki" value="{{ old('urutan_hirarki', $user->urutan_hirarki) }}" min="1" placeholder="Contoh: 1, 2, 3..." class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                <p class="text-[11px] text-gray-500 mt-1">*Nomor urut posisi untuk laporan rekap & PDF (1 = tertinggi).</p>
                            </div>
                        </div>

                        <!-- Jabatan & Unit Kerja -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Jenis Pekerjaan / Jabatan</label>
                                <select name="jenis_pekerjaan" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm bg-white">
                                    <option value="">-- Pilih Pekerjaan --</option>
                                    @php $jp = old('jenis_pekerjaan', $user->jenis_pekerjaan); @endphp
                                    <optgroup label="Manajerial">
                                        <option value="Kepala Suku Dinas" {{ $jp == 'Kepala Suku Dinas' ? 'selected' : '' }}>Kepala Suku Dinas</option>
                                        <option value="Kepala Subbagian" {{ $jp == 'Kepala Subbagian' ? 'selected' : '' }}>Kepala Subbagian</option>
                                        <option value="Kepala Seksi" {{ $jp == 'Kepala Seksi' ? 'selected' : '' }}>Kepala Seksi</option>
                                        <option value="PPTK" {{ $jp == 'PPTK' ? 'selected' : '' }}>PPTK</option>
                                        <option value="Kasatpel" {{ $jp == 'Kasatpel' ? 'selected' : '' }}>Kasatpel (Kepala Satuan Pelaksana)</option>
                                    </optgroup>
                                    <optgroup label="Operasional & Teknis">
                                        <option value="Pendamping Jakpreneur" {{ $jp == 'Pendamping Jakpreneur' ? 'selected' : '' }}>Pendamping Jakpreneur</option>
                                        <option value="Petugas Keamanan" {{ $jp == 'Petugas Keamanan' ? 'selected' : '' }}>Petugas Keamanan</option>
                                        <option value="Petugas Kebersihan" {{ $jp == 'Petugas Kebersihan' ? 'selected' : '' }}>Petugas Kebersihan</option>
                                        <option value="Petugas Kebun" {{ $jp == 'Petugas Kebun' ? 'selected' : '' }}>Petugas Kebun</option>
                                        <option value="Teknis Kesehatan Hewan" {{ $jp == 'Teknis Kesehatan Hewan' ? 'selected' : '' }}>Teknis Kesehatan Hewan</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Unit Kerja / Bidang</label>
                                <select name="unit_kerja" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm bg-white">
                                    <option value="">-- Pilih Unit Kerja --</option>
                                    @php $uk = old('unit_kerja', $user->unit_kerja); @endphp
                                    <option value="Subbagian Tata Usaha" {{ $uk == 'Subbagian Tata Usaha' ? 'selected' : '' }}>Subbagian Tata Usaha</option>
                                    <option value="Seksi Ketahanan Pangan dan Pertanian" {{ $uk == 'Seksi Ketahanan Pangan dan Pertanian' ? 'selected' : '' }}>Seksi Ketahanan Pangan dan Pertanian</option>
                                    <option value="Seksi Peternakan dan Kesehatan Hewan" {{ $uk == 'Seksi Peternakan dan Kesehatan Hewan' ? 'selected' : '' }}>Seksi Peternakan dan Kesehatan Hewan</option>
                                    <option value="Satuan Pelaksana Kecamatan" {{ $uk == 'Satuan Pelaksana Kecamatan' ? 'selected' : '' }}>Satuan Pelaksana Kecamatan</option>
                                    <option value="Agro Edu Wisata (AEW) Ragunan" {{ $uk == 'Agro Edu Wisata (AEW) Ragunan' ? 'selected' : '' }}>Agro Edu Wisata (AEW) Ragunan</option>
                                </select>
                            </div>
                        </div>

                        <!-- Lokasi Tugas -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Lokasi Tugas (Kecamatan)</label>
                            <select name="lokasi_tugas" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-blue-500 focus:border-blue-500 shadow-sm bg-white">
                                <option value="">-- Pilih Lokasi --</option>
                                @php $lt = old('lokasi_tugas', $user->lokasi_tugas); @endphp
                                <option value="Suku Dinas (Jakarta Selatan)" {{ $lt == 'Suku Dinas (Jakarta Selatan)' ? 'selected' : '' }}> Suku Dinas (Jakarta Selatan)</option>
                                <option value="Kecamatan Kebayoran Baru" {{ $lt == 'Kecamatan Kebayoran Baru' ? 'selected' : '' }}>Kecamatan Kebayoran Baru</option>
                                <option value="Kecamatan Kebayoran Lama" {{ $lt == 'Kecamatan Kebayoran Lama' ? 'selected' : '' }}>Kecamatan Kebayoran Lama</option>
                                <option value="Kecamatan Pesanggrahan" {{ $lt == 'Kecamatan Pesanggrahan' ? 'selected' : '' }}>Kecamatan Pesanggrahan</option>
                                <option value="Kecamatan Cilandak" {{ $lt == 'Kecamatan Cilandak' ? 'selected' : '' }}>Kecamatan Cilandak</option>
                                <option value="Kecamatan Pasar Minggu" {{ $lt == 'Kecamatan Pasar Minggu' ? 'selected' : '' }}>Kecamatan Pasar Minggu</option>
                                <option value="Kecamatan Jagakarsa" {{ $lt == 'Kecamatan Jagakarsa' ? 'selected' : '' }}>Kecamatan Jagakarsa</option>
                                <option value="Kecamatan Mampang Prapatan" {{ $lt == 'Kecamatan Mampang Prapatan' ? 'selected' : '' }}>Kecamatan Mampang Prapatan</option>
                                <option value="Kecamatan Pancoran" {{ $lt == 'Kecamatan Pancoran' ? 'selected' : '' }}>Kecamatan Pancoran</option>
                                <option value="Kecamatan Tebet" {{ $lt == 'Kecamatan Tebet' ? 'selected' : '' }}>Kecamatan Tebet</option>
                                <option value="Kecamatan Setiabudi" {{ $lt == 'Kecamatan Setiabudi' ? 'selected' : '' }}>Kecamatan Setiabudi</option>
                                <option value="AEW Ragunan" {{ $lt == 'AEW Ragunan' ? 'selected' : '' }}>AEW Ragunan</option>
                            </select>
                        </div>

                        <!-- ATASAN PENGGANTI (JIKA STATUS NONAKTIF ATAU DITURUNKAN ROLE-NYA) -->
                        <div x-show="['atasan', 'pimpinan'].includes(initialRole) && (status === 'nonaktif' || ['pjlp', 'p3k'].includes(role))" x-transition class="p-4 bg-amber-50 border border-amber-200 rounded-2xl space-y-2">
                            <label class="block text-xs font-bold text-amber-900 uppercase">
                                Pilih Atasan Pengganti Untuk Bawahan
                            </label>
                            <p class="text-[11px] text-amber-700">Pegawai ini sebelumnya berperan sebagai Atasan/Pimpinan. Pilih Atasan Pengganti untuk mengalihkan seluruh bawahannya.</p>
                            <select name="pengganti_atasan_id" class="w-full border-amber-300 rounded-xl text-xs md:text-sm p-3 focus:ring-amber-500 focus:border-amber-500 shadow-sm bg-white">
                                <option value="">-- Pilih Atasan Pengganti --</option>
                                @foreach($daftarAtasan as $atasan)
                                    <option value="{{ $atasan->id }}" {{ old('pengganti_atasan_id') == $atasan->id ? 'selected' : '' }}>{{ $atasan->name }} - {{ $atasan->jenis_pekerjaan ?? 'Atasan' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- BLOK KHUSUS BAWAHAN (PJLP & P3K) -->
                        <div x-show="['pjlp', 'p3k'].includes(role)" x-transition class="space-y-4 bg-orange-50/60 p-5 rounded-2xl border border-orange-200/60" style="display: none;">
                            <h4 class="font-bold text-orange-900 text-xs uppercase tracking-wider mb-2">Penetapan Atasan Penilai</h4>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pilih Atasan Langsung</label>
                                <select name="atasan_id" class="w-full border-gray-300 rounded-xl text-xs md:text-sm p-3 focus:ring-orange-500 focus:border-orange-500 shadow-sm bg-white">
                                    <option value="">-- Pilih Atasan --</option>
                                    @foreach($daftarAtasan as $atasan)
                                        <option value="{{ $atasan->id }}" {{ old('atasan_id', $user->atasan_id) == $atasan->id ? 'selected' : '' }}>{{ $atasan->name }} - {{ $atasan->jenis_pekerjaan ?? 'Atasan' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="pt-5 border-t border-gray-100 flex items-center justify-end space-x-3">
                            <a href="{{ route('admin.pegawai.index') }}" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-50 transition shadow-sm">Batal</a>
                            <button type="submit" class="px-6 py-2.5 bg-yellow-500 text-white text-xs font-bold rounded-xl hover:bg-yellow-600 transition shadow">Update Pegawai</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MODAL KONFIRMASI RESET PASSWORD -->
            <div x-show="showResetModal" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
                 style="display: none;">
                
                <div @click.away="showResetModal = false" 
                     class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 space-y-5 text-center">
                    
                    <div class="w-14 h-14 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center mx-auto shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-base font-bold text-gray-800">Reset Password Akun?</h3>
                        <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                            Password untuk pegawai <strong class="text-gray-800">{{ $user->name }}</strong> akan dikembalikan ke password standar:
                        </p>
                        <div class="mt-2.5">
                            <span class="inline-block px-3 py-1 bg-gray-100 border border-gray-200 text-red-600 font-mono font-bold rounded-xl text-xs shadow-inner">
                                password
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 pt-2">
                        <button type="button" @click="showResetModal = false" class="w-1/2 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <form action="{{ route('admin.pegawai.reset_password', $user->id) }}" method="POST" class="w-1/2">
                            @csrf
                            <button type="submit" class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-md hover:shadow-lg transition cursor-pointer">
                                Ya, Reset
                            </button>
                        </form>
                    </div>
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