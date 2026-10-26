<div {{ $attributes->merge(['class' => 'flex items-center space-x-3']) }}>
    <!-- Logo Gambar Dinas KPKP -->
    <img src="{{ asset('images/logo-kpkp.png') }}" alt="Logo Dinas KPKP" class="h-10 w-auto object-contain shrink-0" />
    
    <!-- Teks Judul & Subjudul Aplikasi -->
    <div class="flex flex-col text-left">
        <span class="font-bold text-gray-900 text-base leading-none tracking-tight">SI-PEKA</span>
        <span class="text-[10px] text-gray-500 font-semibold leading-tight mt-1">Sistem Pelaporan Kinerja</span>
    </div>
</div>