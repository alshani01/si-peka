<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Detail Kinerja Harian PJLP Dinas KPKP</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9px; margin: 0; padding: 0; }
        .header-table { width: 100%; border-collapse: collapse; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 12px; }
        .header-title { text-align: center; line-height: 1.3; }
        .info-table { width: 100%; margin-bottom: 12px; font-size: 9px; font-weight: bold; }
        
        .employee-box { margin-bottom: 15px; page-break-inside: avoid; }
        .employee-header { background-color: #e5e7eb; padding: 6px 8px; font-weight: bold; border: 1px solid #000; font-size: 9px; }
        
        table.activity-table { width: 100%; border-collapse: collapse; border-top: none; }
        table.activity-table th, table.activity-table td { border: 1px solid #000; padding: 5px 4px; text-align: left; vertical-align: top; }
        table.activity-table th { background-color: #f3f4f6; font-weight: bold; text-transform: uppercase; font-size: 8px; text-align: center; }
        
        .badge-acc { color: #059669; font-weight: bold; }
        .badge-revisi { color: #dc2626; font-weight: bold; }
        .badge-pending { color: #d97706; font-weight: bold; }
        
        .footer { margin-top: 15px; width: 100%; font-size: 8px; text-align: right; color: #555; }
    </style>
</head>
<body>

    <!-- KOP SURAT STANDAR DINAS KPKP -->
    <table class="header-table">
        <tr>
            <td class="header-title">
                <div style="font-weight: bold; font-size: 11px; text-transform: uppercase;">
                    DINAS KETAHANAN PANGAN, KELAUTAN DAN PERTANIAN
                </div>
                <div style="font-weight: bold; font-size: 10px; text-transform: uppercase;">
                    SUKU DINAS KETAHANAN PANGAN, KELAUTAN DAN PERTANIAN KOTA ADMINISTRASI JAKARTA SELATAN
                </div>
                <div style="font-size: 9px; font-weight: normal; margin-top: 2px;">
                    Jl. Prapanca Raya No.9 Blok A lt. 12, Kel. Petogogan, Kec. Kebayoran Baru, Jakarta Selatan.
                </div>
            </td>
        </tr>
    </table>

    <div style="text-align: center; font-weight: bold; font-size: 12px; margin-bottom: 10px; text-transform: uppercase;">
        LAPORAN DETAIL KINERJA HARIAN PEGAWAI
    </div>

    <!-- METADATA LAPORAN -->
    <table class="info-table">
        <tr>
            <td width="12%">PERIODE</td>
            <td width="2%">:</td>
            <td>
                @if(isset($tipeFilter) && $tipeFilter === 'harian')
                    {{ strtoupper(\Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y')) }}
                @else
                    01 {{ strtoupper($namaBulan) }} {{ $tahun }} s/d {{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->format('d') }} {{ strtoupper($namaBulan) }} {{ $tahun }}
                @endif
            </td>
        </tr>
    </table>

    <!-- RINCIAN KEGIATAN PER PEGAWAI -->
    @forelse($rekapKinerja as $row)
        <div class="employee-box">
            <div class="employee-header">
                [{{ $row['id_pjlp'] }}] {{ strtoupper($row['nama']) }} &bull; <span style="font-weight: normal;">{{ $row['jenis_pekerjaan'] }} ({{ $row['lokasi_tugas'] }})</span>
            </div>
            
            <table class="activity-table">
                <thead>
                    <tr>
                        <th width="4%">NO</th>
                        <th width="12%">TANGGAL</th>
                        <th width="50%">URAIAN TUGAS / PEKERJAAN</th>
                        <th width="16%">STATUS VALIDASI</th>
                        <th width="18%">CATATAN ATASAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($row['detail_kegiatan'] as $index => $kegiatan)
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td style="text-align: center;">
                                {{ \Carbon\Carbon::parse($kegiatan->tanggal_tugas ?? $kegiatan->tanggal ?? now())->translatedFormat('d M Y') }}
                            </td>
                            <td>
                                {{ $kegiatan->uraian_tugas ?? $kegiatan->deskripsi_kegiatan ?? $kegiatan->kegiatan ?? $kegiatan->judul_kegiatan ?? '-' }}
                            </td>
                            <td style="text-align: center;">
                                @if(in_array(strtolower($kegiatan->status_validasi), ['disetujui', 'acc']))
                                    <span class="badge-acc">Disetujui</span>
                                @elseif(in_array(strtolower($kegiatan->status_validasi), ['direvisi', 'ditolak', 'revisi']))
                                    <span class="badge-revisi">Revisi</span>
                                @else
                                    <span class="badge-pending">Pending</span>
                                @endif
                            </td>
                            <td>
                                {{ $kegiatan->catatan_atasan ?? $kegiatan->catatan ?? $kegiatan->alasan_revisi ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #6b7280; font-style: italic; padding: 6px;">
                                Tidak ada pengajuan laporan kegiatan pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @empty
        <div style="text-align: center; padding: 20px; color: #6b7280; font-style: italic;">
            Belum ada data pegawai terdaftar.
        </div>
    @endforelse

    <div class="footer">
        Tanggal Cetak: {{ date('d-m-Y H:i:s') }} | SiPADU - Sistem Informasi Pelaporan Kinerja PJLP Dinas KPKP
    </div>

</body>
</html>