<?php

namespace App\Http\Controllers;

use App\Models\LaporanKinerja;
use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\PengajuanBukaKunci; // Import Model PengajuanBukaKunci
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanKinerjaController extends Controller
{
    private function getDaftarAktivitas()
    {
        return [
            'Keamanan' => [
                'Menerima tugas jaga dari petugas sebelumnya',
                'Serah terima tugas jaga kepada petugas berikutnya',
                'Kontrol/patroli area AEW',
                'Kontrol keliling area AEW',
                'Memantau kondisi keamanan area',
                'Memberikan makan/pakan sapi',
                'Memberikan rumput sapi',
                'Menyalakan lampu area kerja',
                'Mematikan lampu area kerja',
                'Memeriksa kondisi area pada jam tertentu',
                'Pengamanan/monitoring area saat kunjungan sekolah',
                'Memastikan situasi area aman dan kondusif',
                'Membantu tupoksi Suku Dinas Lainnya',
            ],
            'Kebersihan' => [
                'Membersihkan area kerja',
                'Menyapu area',
                'Mengepel area',
                'Membersihkan fasilitas umum',
                'Membersihkan toilet/fasilitas sanitasi',
                'Mengangkut dan menata sampah',
                'Membersihkan area sekitar AEW',
                'Menjaga kebersihan lingkungan',
                'Melakukan pembersihan setelah kegiatan',
                'Melaporkan kondisi kebersihan area',
                'Membantu tupoksi Suku Dinas Lainnya',
            ],
            'Kebun' => [
                'Penyiraman tanaman',
                'Pemeliharaan tanaman',
                'Pemangkasan tanaman',
                'Penyiangan gulma/rumput',
                'Pembersihan area kebun',
                'Pemupukan',
                'Penataan tanaman',
                'Perawatan taman',
                'Pengangkutan sisa tanaman/sampah kebun',
                'Pemeliharaan sarana/prasarana kebun',
                'Pemantauan kondisi tanaman',
                'Melaporkan kondisi kebun',
                'Membantu tupoksi Suku Dinas Lainnya',
            ],
            'Teknis Kesehatan Hewan' => [
                'Pemeriksaan kesehatan hewan',
                'Pemantauan kondisi kesehatan hewan',
                'Penanganan hewan sakit',
                'Pelayanan kesehatan hewan',
                'Pemberian obat/tindakan sesuai kebutuhan',
                'Pengawasan kesehatan hewan',
                'Pencatatan hasil pemeriksaan',
                'Pemantauan sanitasi dan kondisi kandang',
                'Penanganan kasus kesehatan hewan',
                'Pelaporan kegiatan teknis kesehatan hewan',
                'Membantu tupoksi Suku Dinas Lainnya',
            ],
            'Pendamping Jakpreneur' => [
                'Pendampingan pelaku usaha',
                'Kunjungan/pembinaan UMKM',
                'Monitoring perkembangan usaha',
                'Konsultasi usaha',
                'Pendataan pelaku usaha',
                'Pendampingan administrasi usaha',
                'Pendampingan pemasaran',
                'Pendampingan pengembangan usaha',
                'Fasilitasi kegiatan kewirausahaan',
                'Dokumentasi kegiatan pendampingan',
                'Penyusunan laporan hasil pendampingan',
                'Koordinasi dengan stakeholder wilayah',
                'Membantu tupoksi Suku Dinas Lainnya',
            ]
        ];
    }

    public function create(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'pjlp') {
            abort(403, 'Hanya personil operasional yang dapat mengakses halaman ini.');
        }

        $bulan = sprintf('%02d', (int) $request->input('bulan', Carbon::now('Asia/Jakarta')->format('m')));
        $tahun = (int) $request->input('tahun', Carbon::now('Asia/Jakarta')->format('Y'));
        $statusValidasi = $request->input('status_validasi', 'all');

        $masterAktivitas = $this->getDaftarAktivitas();
        $jenisPekerjaanUser = $user->jenis_pekerjaan ?? 'Petugas Pendamping Jakpreneur';

        $jenisLower = strtolower($jenisPekerjaanUser);
        $keyAktivitas = 'Pendamping Jakpreneur';

        if (str_contains($jenisLower, 'keamanan')) {
            $keyAktivitas = 'Keamanan';
        } elseif (str_contains($jenisLower, 'kebersihan')) {
            $keyAktivitas = 'Kebersihan';
        } elseif (str_contains($jenisLower, 'kebun')) {
            $keyAktivitas = 'Kebun';
        } elseif (str_contains($jenisLower, 'hewan') || str_contains($jenisLower, 'kesehatan')) {
            $keyAktivitas = 'Teknis Kesehatan Hewan';
        } elseif (str_contains($jenisLower, 'pendamping') || str_contains($jenisLower, 'kewirausahaan') || str_contains($jenisLower, 'jakpreneur')) {
            $keyAktivitas = 'Pendamping Jakpreneur';
        }

        $listAktivitas = $masterAktivitas[$keyAktivitas] ?? $masterAktivitas['Pendamping Jakpreneur'];

        // Ambil Pengajuan Buka Kunci yang Disetujui Admin & Masih Berlaku
        $pengajuanBukaKunciDisetujui = PengajuanBukaKunci::where('user_id', $user->id)
            ->where('status', 'disetujui')
            ->whereNotNull('expired_at')
            ->where('expired_at', '>=', Carbon::now('Asia/Jakarta'))
            ->orderBy('tanggal_terlewat', 'desc')
            ->get();

        $datesLaporan = LaporanKinerja::where('user_id', $user->id)
            ->whereMonth('tanggal_tugas', $bulan)
            ->whereYear('tanggal_tugas', $tahun)
            ->when($statusValidasi !== 'all', function ($q) use ($statusValidasi) {
                return $q->where('status_validasi', $statusValidasi);
            })
            ->select('tanggal_tugas as tanggal')
            ->groupBy('tanggal_tugas')
            ->get();

        if ($statusValidasi === 'ditolak') {
            $datesAbsensi = collect();
        } else {
            $datesAbsensi = Absensi::where('user_id', $user->id)
                ->whereIn('status_kehadiran', ['cuti', 'sakit', 'izin', 'alpa'])
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->select('tanggal')
                ->groupBy('tanggal')
                ->get();
        }

        $allDatesCombined = $datesLaporan->concat($datesAbsensi)
            ->pluck('tanggal')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->sort()
            ->values();

        $currentPage = Paginator::resolveCurrentPage();
        $perPage = 10;
        $currentDateItems = $allDatesCombined->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $laporanPaginated = new LengthAwarePaginator(
            $currentDateItems,
            $allDatesCombined->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $riwayatGrouped = LaporanKinerja::with('pengajuanBukaKunci')
            ->where('user_id', $user->id)
            ->whereIn('tanggal_tugas', $currentDateItems->toArray())
            ->orderBy('tanggal_tugas', 'asc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->tanggal_tugas)->format('Y-m-d');
            });

        $infoAbsensi = Absensi::where('user_id', $user->id)
            ->whereIn('status_kehadiran', ['cuti', 'sakit', 'izin', 'alpa'])
            ->whereIn('tanggal', $currentDateItems->toArray())
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->tanggal)->format('Y-m-d');
            });

        return view('pjlp.laporan.create', compact(
            'riwayatGrouped',
            'infoAbsensi',
            'laporanPaginated',
            'bulan',
            'tahun',
            'listAktivitas',
            'jenisPekerjaanUser',
            'statusValidasi',
            'pengajuanBukaKunciDisetujui'
        ));
    }

    /**
     * Menyimpan Pengajuan Buka Kunci Laporan Terkunci ke Admin
     */
    public function storeBukaKunci(Request $request)
    {
        $request->validate([
            'tanggal_pengajuan' => 'required|date|before_or_equal:today',
            'alasan'            => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        if ($user->role !== 'pjlp') {
            abort(403, 'Hanya personil operasional yang dapat melakukan pengajuan.');
        }

        $tanggalPengajuan = $request->tanggal_pengajuan;

        // 1. Cek apakah user sedang Cuti (berdasarkan tabel Cuti yang disetujui)
        $sedangCuti = Cuti::where('user_id', $user->id)
            ->where('status_validasi', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $tanggalPengajuan)
            ->whereDate('tanggal_selesai', '>=', $tanggalPengajuan)
            ->exists();

        if ($sedangCuti) {
            return back()->withErrors([
                'tanggal_pengajuan' => 'Pengajuan buka kunci ditolak. Anda tercatat sedang CUTI yang disetujui pada tanggal tersebut.'
            ])->withInput();
        }

        // 2. Cek apakah ada record Absensi non-hadir (cuti, sakit, izin, alpa)
        $cekAbsensi = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', $tanggalPengajuan)
            ->whereIn('status_kehadiran', ['cuti', 'sakit', 'izin', 'alpa'])
            ->first();

        if ($cekAbsensi) {
            $statusAbsen = strtoupper($cekAbsensi->status_kehadiran);
            return back()->withErrors([
                'tanggal_pengajuan' => "Pengajuan buka kunci ditolak. Status absensi Anda pada tanggal tersebut adalah {$statusAbsen}."
            ])->withInput();
        }

        // 3. Cek jika sudah ada pengajuan pending untuk tanggal yang sama
        $existsPending = PengajuanBukaKunci::where('user_id', $user->id)
            ->whereDate('tanggal_terlewat', $tanggalPengajuan)
            ->where('status', 'pending')
            ->exists();

        if ($existsPending) {
            return back()->withErrors([
                'tanggal_pengajuan' => 'Pengajuan buka kunci untuk tanggal ini sudah pernah dikirim dan masih menunggu persetujuan Admin.'
            ])->withInput();
        }

        // Simpan pengajuan jika lolos semua validasi
        PengajuanBukaKunci::create([
            'user_id'          => $user->id,
            'tanggal_terlewat' => $tanggalPengajuan,
            'alasan'           => $request->alasan,
            'status'           => 'pending',
        ]);

        return back()->with('success', 'Pengajuan buka kunci berhasil dikirim ke Admin. Silakan menunggu persetujuan.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_tugas'     => 'required|date|before_or_equal:' . Carbon::now('Asia/Jakarta')->toDateString(),
            'aktivitas_kinerja' => 'required|string',
            'uraian_tugas'      => 'required|string',
            'file_bukti'        => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $user = auth()->user();
        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $jamSekarang = Carbon::now('Asia/Jakarta')->format('H:i');
        $pengajuanBukaKunciId = null;

        // Cek Logika Pengisian (Hari Ini vs Susulan)
        if ($request->tanggal_tugas === $today) {
            if ($jamSekarang < '06:00' || $jamSekarang > '18:00') {
                return back()->withErrors([
                    'tanggal_tugas' => 'Pengisian laporan harian reguler ditutup. Form hanya dapat diisi antara pukul 06:00 WIB sampai 18:00 WIB.'
                ])->withInput();
            }
        } else {
            // Pengisian Tanggal Susulan (Harus ada ACC Admin yang aktif)
            $pengajuanBukaKunci = PengajuanBukaKunci::where('user_id', $user->id)
                ->whereDate('tanggal_terlewat', $request->tanggal_tugas)
                ->where('status', 'disetujui')
                ->whereNotNull('expired_at')
                ->where('expired_at', '>=', Carbon::now('Asia/Jakarta'))
                ->first();

            if (!$pengajuanBukaKunci) {
                return back()->withErrors([
                    'tanggal_tugas' => 'Tanggal tugas tersebut terkunci. Silakan ajukan buka kunci terlebih dahulu dan tunggu persetujuan Admin.'
                ])->withInput();
            }

            $pengajuanBukaKunciId = $pengajuanBukaKunci->id;
        }

        $sedangCuti = Cuti::where('user_id', $user->id)
            ->where('status_validasi', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $request->tanggal_tugas)
            ->whereDate('tanggal_selesai', '>=', $request->tanggal_tugas)
            ->exists();

        if ($sedangCuti) {
            return back()->withErrors([
                'tanggal_tugas' => 'Anda tidak dapat menginput laporan kinerja pada tanggal ini karena status Anda sedang Cuti yang disetujui.'
            ])->withInput();
        }

        $filePath = null;
        if ($request->hasFile('file_bukti')) {
            $filePath = $request->file('file_bukti')->store('bukti_kinerja', 'public');
        }

        LaporanKinerja::create([
            'user_id'                 => $user->id,
            'pengajuan_buka_kunci_id' => $pengajuanBukaKunciId,
            'tanggal_tugas'           => $request->tanggal_tugas,
            'aktivitas_kinerja'       => $request->aktivitas_kinerja,
            'uraian_tugas'            => $request->uraian_tugas,
            'file_bukti'              => $filePath,
            'status_validasi'         => 'pending',
        ]);

        return redirect()->route('pjlp.laporan.create')->with('success', 'Aktivitas kinerja berhasil ditambahkan.');
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'pjlp') {
            abort(403, 'Hanya personil operasional yang dapat mengakses fungsi ini.');
        }

        $user->load('atasan');

        $bulan = (int) ($request->input('bulan', Carbon::now('Asia/Jakarta')->format('m')));
        $tahun = (int) ($request->input('tahun', Carbon::now('Asia/Jakarta')->format('Y')));

        $laporans = LaporanKinerja::where('user_id', $user->id)
            ->whereMonth('tanggal_tugas', $bulan)
            ->whereYear('tanggal_tugas', $tahun)
            ->orderBy('tanggal_tugas', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $totalLaporan = $laporans->count();
        $laporanAcc = $laporans->whereIn('status_validasi', ['disetujui', 'acc'])->count();
        $laporanDitolak = $laporans->whereIn('status_validasi', ['ditolak', 'rejected'])->count();
        $laporanRevisi = $laporanDitolak;
        $laporanPending = $laporans->where('status_validasi', 'pending')->count();

        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->translatedFormat('F');

        $pdf = Pdf::loadView('pjlp.laporan.pdf', compact(
            'user', 'laporans', 'bulan', 'tahun', 'namaBulan',
            'totalLaporan', 'laporanAcc', 'laporanDitolak', 'laporanRevisi', 'laporanPending'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("Laporan_Kinerja_PJLP_{$user->id_pjlp}_{$bulan}_{$tahun}.pdf");
    }
}