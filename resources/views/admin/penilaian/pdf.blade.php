<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Penilaian Perilaku PJLP</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9px; margin: 0; padding: 0; color: #333; }
        .header-table { width: 100%; border-collapse: collapse; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 12px; }
        .header-title { text-align: center; line-height: 1.3; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #ccc; padding: 5px 6px; text-align: left; vertical-align: middle; }
        table.data-table th { background-color: #f2f2f2; text-transform: uppercase; font-size: 8px; text-align: center; font-weight: bold; }
        
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .badge { padding: 3px 5px; border-radius: 4px; font-weight: bold; font-size: 8px; display: inline-block; }
        .badge-sangat-baik { background-color: #d1fae5; color: #065f46; }
        .badge-baik { background-color: #dbeafe; color: #1e40af; }
        .badge-cukup { background-color: #fef3c7; color: #92400e; }
        .badge-kurang { background-color: #ffe4e6; color: #9f1239; }
        .badge-belum { background-color: #f3f4f6; color: #6b7280; }
        
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
        REKAPITULASI PENILAIAN PERILAKU PJLP<br>
        <span style="font-size: 9px; font-weight: normal; text-transform: none;">
            Periode: {{ \Carbon\Carbon::createFromDate((int)$tahun, (int)$bulan, 1)->translatedFormat('F') }} {{ $tahun }}
        </span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="10%">ID PJLP</th>
                <th width="16%">Nama Pegawai</th>
                <th width="10%">Kat. Pegawai</th>
                <th width="13%">Jenis Pekerjaan</th>
                <th width="12%">Lokasi Tugas</th>
                <th width="7%">Kehadiran</th>
                <th width="7%">T. Jawab</th>
                <th width="7%">Kepatuhan</th>
                <th width="7%">Rata-rata</th>
                <th width="8%">Kategori</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($bawahans ?? $data ?? []) as $index => $row)
                @php
                    $nilai = $row->penilaianPerilaku->first();
                    $rataRata = $nilai ? $nilai->rata_rata : '-';
                    $kategoriVal = $nilai ? $nilai->kategori : 'Belum Dinilai';
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $row->id_pjlp ?? '-' }}</td>
                    <td class="font-bold">{{ $row->name }}</td>
                    <td class="text-center">{{ $row->kategori_pegawai ?? '-' }}</td>
                    <td>{{ $row->jenis_pekerjaan ?? '-' }}</td>
                    <td>{{ $row->lokasi_tugas ?? '-' }}</td>
                    <td class="text-center">{{ $nilai ? $nilai->nilai_kehadiran : '-' }}</td>
                    <td class="text-center">{{ $nilai ? $nilai->nilai_tanggung_jawab : '-' }}</td>
                    <td class="text-center">{{ $nilai ? $nilai->nilai_kepatuhan : '-' }}</td>
                    <td class="text-center font-bold">{{ $rataRata }}</td>
                    <td class="text-center">
                        @if($kategoriVal === 'Sangat Baik') <span class="badge badge-sangat-baik">Sangat Baik</span>
                        @elseif($kategoriVal === 'Baik') <span class="badge badge-baik">Baik</span>
                        @elseif($kategoriVal === 'Cukup') <span class="badge badge-cukup">Cukup</span>
                        @elseif($kategoriVal === 'Kurang') <span class="badge badge-kurang">Kurang</span>
                        @else <span class="badge badge-belum">Belum Dinilai</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 15px;">Tidak ada data penilaian untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Tanggal Cetak: {{ date('d-m-Y H:i:s') }} | SI-PEKA - Sistem Informasi Pelaporan Kinerja Pegawai
    </div>

</body>
</html>