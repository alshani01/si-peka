<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kinerja Bulanan PJLP</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9px; margin: 0; padding: 0; color: #333; }
        .header-table { width: 100%; border-collapse: collapse; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 12px; }
        .header-title { text-align: center; line-height: 1.3; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9px; }
        .info-table td { padding: 3px 4px; vertical-align: top; }
        .info-label { font-weight: bold; width: 15%; uppercase; }
        .info-separator { width: 2%; text-align: center; font-weight: bold; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 5px 4px; text-align: left; vertical-align: top; }
        table.data-table th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; font-size: 8px; text-align: center; }
        
        .badge-acc { color: #059669; font-weight: bold; }
        .badge-revisi { color: #dc2626; font-weight: bold; }
        .badge-pending { color: #d97706; font-weight: bold; }
        
        .signature-table { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; }
        .signature-table td { text-align: center; vertical-align: top; width: 50%; font-size: 9px; }
        
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

    <div style="text-align: center; font-weight: bold; font-size: 12px; margin-bottom: 12px; text-transform: uppercase;">
        LAPORAN KINERJA BULANAN PEGAWAI PJLP
    </div>

    <!-- METADATA & IDENTITAS PEGAWAI -->
    <table class="info-table">
        <tr>
            <td class="info-label">NAMA PEGAWAI</td>
            <td class="info-separator">:</td>
            <td width="33%"><strong>{{ strtoupper($user->name) }}</strong></td>
            <td class="info-label">PERIODE</td>
            <td class="info-separator">:</td>
            <td><strong>{{ strtoupper($namaBulan) }} {{ $tahun }}</strong></td>
        </tr>
        <tr>
            <td class="info-label">No Pegawai</td>
            <td class="info-separator">:</td>
            <td>{{ $user->id_pjlp ?? '-' }}</td>
            <td class="info-label">LOKASI TUGAS</td>
            <td class="info-separator">:</td>
            <td>{{ $user->lokasi_tugas ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">JABATAN</td>
            <td class="info-separator">:</td>
            <td>{{ $user->jenis_pekerjaan ?? '-' }}</td>
            <td class="info-label">ATASAN PENILAI</td>
            <td class="info-separator">:</td>
            <td>{{ $user->atasan->name ?? '-' }}</td>
        </tr>
    </table>

    <!-- RINGKASAN CAPAIAN STATISTIK -->
    <table class="data-table" style="margin-bottom: 12px;">
        <thead>
            <tr>
                <th width="25%">TOTAL LAPORAN</th>
                <th width="25%">DISETUJUI (ACC)</th>
                <th width="25%">REVISI</th>
                <th width="25%">MENUNGGU</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: bold;">{{ $totalLaporan }}</td>
                <td style="text-align: center; font-weight: bold; color: #059669;">{{ $laporanAcc }}</td>
                <td style="text-align: center; font-weight: bold; color: #dc2626;">{{ $laporanRevisi }}</td>
                <td style="text-align: center; font-weight: bold; color: #d97706;">{{ $laporanPending }}</td>
            </tr>
        </tbody>
    </table>

    <!-- TABEL RINCIAN KEGIATAN HARIAN -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">NO</th>
                <th width="12%">TANGGAL</th>
                <th width="22%">AKTIVITAS KINERJA</th>
                <th width="38%">URAIAN TUGAS / PEKERJAAN</th>
                <th width="12%">VALIDASI</th>
                <th width="12%">CATATAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporans as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center;">
                        {{ \Carbon\Carbon::parse($item->tanggal_tugas)->translatedFormat('d M Y') }}
                    </td>
                    <td><strong>{{ $item->aktivitas_kinerja }}</strong></td>
                    <td>{{ $item->uraian_tugas }}</td>
                    <td style="text-align: center;">
                        @if(in_array(strtolower($item->status_validasi), ['disetujui', 'acc']))
                            <span class="badge-acc">Disetujui</span>
                        @elseif(in_array(strtolower($item->status_validasi), ['direvisi', 'ditolak', 'revisi']))
                            <span class="badge-revisi">Revisi</span>
                        @else
                            <span class="badge-pending">Pending</span>
                        @endif
                    </td>
                    <td>{{ $item->catatan_atasan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; font-style: italic; color: #777; padding: 10px;">
                        Belum ada laporan kinerja yang diinput untuk bulan ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- LEMBAR PENGESAHAN -->
    <table class="signature-table">
        <tr>
            <td></td>
            <td>Jakarta, {{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td style="padding-top: 5px;">Pegawai,</td>
            <td style="padding-top: 5px;">Atasan Langsung / Penilai,</td>
        </tr>
        <tr>
            <td style="height: 60px;"></td>
            <td style="height: 60px;"></td>
        </tr>
        <tr>
            <td>
                <strong><u>{{ strtoupper($user->name) }}</u></strong><br>
                 {{ $user->id_pjlp ?? '-' }}
            </td>
            <td>
                <strong><u>{{ strtoupper($user->atasan->name ?? '...................................') }}</u></strong><br>
                 {{ $user->atasan->id_pjlp ?? '-' }}
            </td>
        </tr>
    </table>

    <div class="footer">
        Tanggal Cetak: {{ date('d-m-Y H:i:s') }} | SiPADU - Sistem Informasi Pelaporan Kinerja PJLP Dinas KPKP
    </div>

</body>
</html>