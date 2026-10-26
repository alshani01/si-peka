<?php

namespace App\Exports;

use App\Models\LaporanKinerja;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LaporanKinerjaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $bulan;
    protected $tahun;
    protected $atasanId;

    // Menerima parameter bulan dan tahun dari Controller
    public function __construct($bulan, $tahun, $atasanId)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->atasanId = $atasanId;
    }

    // Mengambil data dari database
    public function collection()
    {
        return LaporanKinerja::with('user')
            ->whereHas('user', function ($query) {
                // Hanya ambil data pegawai yang atasan_id-nya adalah atasan yang sedang login
                $query->where('atasan_id', $this->atasanId);
            })
            ->whereMonth('tanggal_tugas', $this->bulan)
            ->whereYear('tanggal_tugas', $this->tahun)
            ->where('status_validasi', 'disetujui') // Hanya ekspor yang sudah di-ACC
            ->orderBy('tanggal_tugas', 'asc')
            ->get();
    }

    // Membuat Judul Kolom (Header) di baris pertama Excel
    public function headings(): array
    {
        return [
            'ID PJLP',
            'Nama Pegawai',
            'Tanggal Tugas',
            'Jam Mulai',
            'Jam Selesai',
            'Uraian Tugas',
            'Status'
        ];
    }

    // Memetakan isi data ke masing-masing kolom
    public function map($laporan): array
    {
        return [
            $laporan->user->id_pjlp,
            $laporan->user->name,
            \Carbon\Carbon::parse($laporan->tanggal_tugas)->format('d-m-Y'),
            \Carbon\Carbon::parse($laporan->jam_mulai)->format('H:i'),
            \Carbon\Carbon::parse($laporan->jam_selesai)->format('H:i'),
            $laporan->uraian_tugas,
            strtoupper($laporan->status_validasi),
        ];
    }
}