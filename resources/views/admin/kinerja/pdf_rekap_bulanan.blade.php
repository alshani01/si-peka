<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Rekapitulasi Kinerja PJLP Dinas KPKP</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9px; margin: 0; padding: 0; }
        .header-table { width: 100%; border-collapse: collapse; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 12px; }
        .header-title { text-align: center; line-height: 1.3; }
        .info-table { width: 100%; margin-bottom: 10px; font-size: 9px; font-weight: bold; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 5px 4px; text-align: center; }
        table.data-table th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; font-size: 8px; }
        table.data-table td.left { text-align: left; }
        
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
        REKAPITULASI LAPORAN KINERJA PEGAWAI
    </div>

    <!-- METADATA LAPORAN -->
    <table class="info-table">
        <tr>
            <td width="12%">PERIODE</td>
            <td width="2%">:</td>
            <td>01 {{ strtoupper($namaBulan) }} {{ $tahun }} s/d {{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->format('d') }} {{ strtoupper($namaBulan) }} {{ $tahun }}</td>
        </tr>
    </table>

    <!-- TABEL REKAPITULASI KINERJA -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="10%">ID PJLP</th>
                <th width="20%">Nama Pegawai</th>
                <th width="22%">Jabatan / Lokasi Tugas</th>
                <th width="9%">Total Laporan</th>
                <th width="9%">Disetujui (ACC)</th>
                <th width="9%">Revisi</th>
                <th width="9%">Menunggu</th>
                <th width="12%">Nilai Perilaku</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekapKinerja as $row)
                <tr>
                    <td>{{ $row['id_pjlp'] }}</td>
                    <td class="left"><strong>{{ strtoupper($row['nama']) }}</strong></td>
                    <td class="left" style="font-size: 8px;">
                        {{ $row['jenis_pekerjaan'] }}<br>
                        <span style="color: #666;">{{ $row['lokasi_tugas'] }}</span>
                    </td>
                    <td>{{ $row['total_laporan'] }}</td>
                    <td>{{ $row['laporan_acc'] }}</td>
                    <td>{{ $row['laporan_ditolak'] }}</td>
                    <td>{{ $row['laporan_pending'] }}</td>
                    <td>{{ $row['nilai_perilaku'] > 0 ? $row['nilai_perilaku'] : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="padding: 10px; font-style: italic; color: #777;">Belum ada data rekapitulasi kinerja.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Tanggal Cetak: {{ date('d-m-Y H:i:s') }} | SiPADU - Sistem Informasi Pelaporan Kinerja PJLP Dinas KPKP
    </div>

</body>
</html>