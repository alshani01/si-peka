<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Daftar Pegawai</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9pt; margin: 0; padding: 0; }
        
        .header-table { width: 100%; border-collapse: collapse; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 12px; }
        .header-title { text-align: center; line-height: 1.3; }
        
        .judul-laporan {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .section-title {
            font-size: 10pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 6px;
            text-transform: uppercase;
            color: #1e3a8a;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        
        .footer {
            margin-top: 15px;
            width: 100%;
            font-size: 8pt;
            text-align: right;
            color: #555;
        }
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

    <div class="judul-laporan">
        LAPORAN DAFTAR PEGAWAI<br>
        <span style="font-size: 9pt; font-weight: normal; text-transform: none;">Keadaan per {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</span>
    </div>

    <!-- 1. TABEL DAFTAR ATASAN & PIMPINAN -->
    <div class="section-title">I. DAFTAR PIMPINAN & ATASAN</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">NO</th>
                <th width="26%">NAMA PEGAWAI</th>
                <th width="20%">NRK / NIP</th>
                <th width="15%">KATEGORI</th>
                <th width="20%">JABATAN</th>
                <th width="15%">UNIT KERJA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($daftarAtasan as $index => $user)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td class="text-center">{{ $user->id_pjlp ?? '-' }}</td>
                    <td class="text-center">{{ strtoupper($user->kategori_pegawai ?? ($user->role === 'admin' ? 'PIMPINAN' : $user->role)) }}</td>
                    <td>{{ $user->jenis_pekerjaan ?? '-' }}</td>
                    <td>{{ $user->unit_kerja ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data pimpinan / atasan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 2. TABEL DAFTAR PEGAWAI P3K -->
    <div class="section-title">II. DAFTAR PEGAWAI P3K</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">NO</th>
                <th width="23%">NAMA PEGAWAI</th>
                <th width="18%">NIP / ID P3K</th>
                <th width="18%">JABATAN</th>
                <th width="17%">UNIT KERJA</th>
                <th width="20%">ATASAN PENILAI</th>
            </tr>
        </thead>
        <tbody>
            @forelse($daftarP3k as $index => $user)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td class="text-center">{{ $user->id_pjlp ?? '-' }}</td>
                    <td>{{ $user->jenis_pekerjaan ?? '-' }}</td>
                    <td>{{ $user->unit_kerja ?? '-' }}</td>
                    <td>{{ $user->atasan->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data pegawai P3K.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 3. TABEL DAFTAR PEGAWAI PJLP -->
    <div class="section-title">III. DAFTAR PEGAWAI PJLP</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">NO</th>
                <th width="23%">NAMA PEGAWAI</th>
                <th width="18%">ID PJLP</th>
                <th width="18%">JABATAN</th>
                <th width="17%">UNIT KERJA</th>
                <th width="20%">ATASAN PENILAI</th>
            </tr>
        </thead>
        <tbody>
            @forelse($daftarPjlp as $index => $user)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td class="text-center">{{ $user->id_pjlp ?? '-' }}</td>
                    <td>{{ $user->jenis_pekerjaan ?? '-' }}</td>
                    <td>{{ $user->unit_kerja ?? '-' }}</td>
                    <td>{{ $user->atasan->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data pegawai PJLP.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }} | SI-PEKA - Sistem Informasi Pelaporan Kinerja Pegawai
    </div>

</body>
</html>