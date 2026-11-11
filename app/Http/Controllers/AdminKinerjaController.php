<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LaporanKinerja;
use App\Models\Absensi;
use App\Models\PengajuanBukaKunci; // Import Model PengajuanBukaKunci
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminKinerjaController extends Controller
{
    public function index(Request $request)
    {
        $tipeFilter = $request->get('tipe_filter', 'bulanan');
        $tanggal = $request->get('tanggal', date('Y-m-d'));
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $search = $request->get('search');
        $statusValidasi = $request->get('status_validasi');

        // Parameter Fitur Buka Kunci (Berfungsi sebagai trigger State Modal Terbuka)
        $tab = $request->get('tab', 'rekap');
        $statusBukaKunci = $request->get('status_buka_kunci', 'pending');
        $searchBukaKunci = $request->get('search_buka_kunci');

        // Data Rekap Kinerja Pegawai
        $rekapKinerja = $this->getKinerjaData($tipeFilter, $tanggal, $bulan, $tahun, $search, $statusValidasi, true);

        // Data Pengajuan Buka Kunci
        $bukaKunciQuery = PengajuanBukaKunci::with('user');

        // Filter periode pengajuan berdasarkan tipe filter (Harian vs Bulanan)
        if ($tipeFilter === 'harian') {
            $bukaKunciQuery->whereDate('tanggal_terlewat', $tanggal);
        } else {
            $bukaKunciQuery->whereMonth('tanggal_terlewat', $bulan)
                           ->whereYear('tanggal_terlewat', $tahun);
        }

        if ($statusBukaKunci && $statusBukaKunci !== 'semua') {
            $bukaKunciQuery->where('status', $statusBukaKunci);
        }

        if ($searchBukaKunci) {
            $bukaKunciQuery->whereHas('user', function ($q) use ($searchBukaKunci) {
                $q->where('name', 'like', "%{$searchBukaKunci}%")
                  ->orWhere('id_pjlp', 'like', "%{$searchBukaKunci}%");
            });
        }

        $pengajuanBukaKunci = $bukaKunciQuery->orderBy('created_at', 'asc')
            ->paginate(10, ['*'], 'buka_kunci_page')
            ->withQueryString();

        $bukaKunciPendingCount = PengajuanBukaKunci::where('status', 'pending')->count();

        return view('admin.kinerja.rekap', compact(
            'rekapKinerja', 'tipeFilter', 'tanggal', 'bulan', 'tahun', 'search', 'statusValidasi',
            'pengajuanBukaKunci', 'bukaKunciPendingCount', 'tab', 'statusBukaKunci', 'searchBukaKunci'
        ));
    }

    /**
     * Menyetujui Pengajuan Buka Kunci Laporan Terkunci
     */
    public function approveBukaKunci($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Hanya Admin yang dapat menyetujui pengajuan buka kunci.');
        }

        $pengajuan = PengajuanBukaKunci::findOrFail($id);

        // Disetujui dan berlaku sampai akhir bulan berjalan
        $pengajuan->update([
            'status'      => 'disetujui',
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now('Asia/Jakarta'),
            'expired_at'  => Carbon::now('Asia/Jakarta')->endOfMonth(),
        ]);

        return redirect()->back()->with('success', 'Pengajuan buka kunci berhasil disetujui. Pegawai dapat menginput laporan susulan.');
    }

    /**
     * Menolak Pengajuan Buka Kunci Laporan Terkunci
     */
    public function rejectBukaKunci(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Hanya Admin yang dapat menolak pengajuan buka kunci.');
        }

        $request->validate([
            'catatan_admin' => 'required|string|max:500',
        ], [
            'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $pengajuan = PengajuanBukaKunci::findOrFail($id);
        $pengajuan->update([
            'status'        => 'ditolak',
            'catatan_admin' => $request->catatan_admin,
            'approved_by'   => auth()->id(),
            'approved_at'   => Carbon::now('Asia/Jakarta'),
        ]);

        return redirect()->back()->with('success', 'Pengajuan buka kunci telah ditolak.');
    }

    private function getKinerjaData($tipeFilter, $tanggal, $bulan, $tahun, $search = null, $statusValidasi = null, $paginate = true)
    {
        $query = User::whereIn('role', ['pjlp', 'pppk', 'p3k']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('id_pjlp', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan Status Validasi / Kondisi Laporan
        if (filled($statusValidasi)) {
            if ($statusValidasi === 'belum_kirim') {
                $query->whereDoesntHave('laporanKinerjas', function ($q) use ($tipeFilter, $tanggal, $bulan, $tahun) {
                    if ($tipeFilter === 'harian') {
                        $q->whereDate('tanggal_tugas', $tanggal);
                    } else {
                        $q->whereMonth('tanggal_tugas', $bulan)
                          ->whereYear('tanggal_tugas', $tahun);
                    }
                });
            } else {
                $query->whereHas('laporanKinerjas', function ($q) use ($tipeFilter, $tanggal, $bulan, $tahun, $statusValidasi) {
                    if ($statusValidasi === 'ditolak') {
                        $q->whereIn('status_validasi', ['ditolak', 'direvisi']);
                    } else {
                        $q->where('status_validasi', $statusValidasi);
                    }

                    if ($tipeFilter === 'harian') {
                        $q->whereDate('tanggal_tugas', $tanggal);
                    } else {
                        $q->whereMonth('tanggal_tugas', $bulan)
                          ->whereYear('tanggal_tugas', $tahun);
                    }
                });
            }
        }

        $query->orderByRaw("COALESCE(urutan_hirarki, 999) ASC")->orderBy('name', 'asc');

        if ($paginate) {
            $pegawais = $query->paginate(10)->withQueryString();
            return $pegawais->through(function ($pegawai) use ($tipeFilter, $tanggal, $bulan, $tahun, $statusValidasi) {
                return $this->mapPegawaiKinerja($pegawai, $tipeFilter, $tanggal, $bulan, $tahun, $statusValidasi);
            });
        }

        $pegawais = $query->get();
        return $pegawais->map(function ($pegawai) use ($tipeFilter, $tanggal, $bulan, $tahun, $statusValidasi) {
            return $this->mapPegawaiKinerja($pegawai, $tipeFilter, $tanggal, $bulan, $tahun, $statusValidasi);
        });
    }

    private function mapPegawaiKinerja($pegawai, $tipeFilter, $tanggal, $bulan, $tahun, $statusValidasi = null)
    {
        $baseQuery = LaporanKinerja::where('user_id', $pegawai->id);

        // Query Absensi untuk mendeteksi status kehadiran/cuti/sakit
        $absensiQuery = Absensi::where('user_id', $pegawai->id)
            ->whereIn('status_kehadiran', ['cuti', 'sakit', 'izin', 'alpa']);

        if ($tipeFilter === 'harian') {
            $baseQuery->whereDate('tanggal_tugas', $tanggal);
            $absensiQuery->whereDate('tanggal', $tanggal);
        } else {
            $baseQuery->whereMonth('tanggal_tugas', $bulan)
                      ->whereYear('tanggal_tugas', $tahun);
            $absensiQuery->whereMonth('tanggal', $bulan)
                         ->whereYear('tanggal', $tahun);
        }

        $allLaporans = $baseQuery->orderBy('tanggal_tugas', 'asc')->get();
        $allAbsensis = $absensiQuery->get();

        $filteredLaporans = $allLaporans;
        if (filled($statusValidasi) && $statusValidasi !== 'belum_kirim') {
            if ($statusValidasi === 'ditolak') {
                $filteredLaporans = $allLaporans->whereIn('status_validasi', ['ditolak', 'direvisi']);
            } else {
                $filteredLaporans = $allLaporans->where('status_validasi', $statusValidasi);
            }
        }

        return [
            'id' => $pegawai->id,
            'id_pjlp' => $pegawai->id_pjlp ?? '-',
            'nama' => $pegawai->name,
            'jenis_pekerjaan' => $pegawai->jenis_pekerjaan ?? '-',
            'lokasi_tugas' => $pegawai->lokasi_tugas ?? '-',
            'total_laporan' => $statusValidasi === 'belum_kirim' ? 0 : $filteredLaporans->count(),
            'laporan_acc' => $allLaporans->where('status_validasi', 'disetujui')->count(),
            'laporan_ditolak' => $allLaporans->whereIn('status_validasi', ['ditolak', 'direvisi'])->count(),
            'laporan_pending' => $allLaporans->where('status_validasi', 'pending')->count(),
            'nilai_perilaku' => $pegawai->nilai_perilaku ?? 0,

            // Data Absensi untuk diolah di view rekap
            'total_absensi' => $allAbsensis->count(),
            'info_absensi_harian' => $tipeFilter === 'harian' ? $allAbsensis->first() : null,
            'detail_kegiatan' => $filteredLaporans,
        ];
    }

    public function exportPdf(Request $request)
    {
        $tipeFilter = $request->get('tipe_filter', 'bulanan');
        $tanggal = $request->get('tanggal', date('Y-m-d'));
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $search = $request->get('search');
        $statusValidasi = $request->get('status_validasi');

        $rekapKinerja = $this->getKinerjaData($tipeFilter, $tanggal, $bulan, $tahun, $search, $statusValidasi, false);
        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F');

        if ($tipeFilter === 'harian') {
            $periodeTeks = Carbon::parse($tanggal)->translatedFormat('d F Y');
            $view = 'admin.kinerja.pdf_rekap_harian';
        } else {
            $periodeTeks = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');
            $view = 'admin.kinerja.pdf_rekap_bulanan';
        }

        $pdf = Pdf::loadView($view, compact('rekapKinerja', 'periodeTeks', 'tipeFilter', 'bulan', 'tahun', 'namaBulan', 'tanggal', 'statusValidasi'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("LAPORAN_KINERJA_KPKP_{$periodeTeks}.pdf");
    }

    public function detail(Request $request, $id)
    {
        $tipeFilter = $request->get('tipe_filter', 'bulanan');
        $tanggal = $request->get('tanggal', date('Y-m-d'));
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $statusValidasi = $request->get('status_validasi');

        $pegawai = User::findOrFail($id);

        $laporansQuery = LaporanKinerja::where('user_id', $pegawai->id);
        $absensiQuery = Absensi::where('user_id', $pegawai->id)
            ->whereIn('status_kehadiran', ['cuti', 'sakit', 'izin', 'alpa']);

        if ($tipeFilter === 'harian') {
            $laporansQuery->whereDate('tanggal_tugas', $tanggal);
            $absensiQuery->whereDate('tanggal', $tanggal);
        } else {
            $laporansQuery->whereMonth('tanggal_tugas', $bulan)
                          ->whereYear('tanggal_tugas', $tahun);
            $absensiQuery->whereMonth('tanggal', $bulan)
                         ->whereYear('tanggal', $tahun);
        }

        if (filled($statusValidasi)) {
            if ($statusValidasi === 'ditolak') {
                $laporansQuery->whereIn('status_validasi', ['ditolak', 'direvisi']);
            } else {
                $laporansQuery->where('status_validasi', $statusValidasi);
            }
        }

        // Diurutkan secara ASC (dari tanggal 1 ke atas)
        $laporans = $laporansQuery->orderBy('tanggal_tugas', 'asc')
                                  ->orderBy('id', 'asc')
                                  ->get();

        $absensis = $absensiQuery->get();

        // Gabungkan tanggal unik dari Laporan Kinerja & Absensi
        $datesLaporan = $laporans->pluck('tanggal_tugas')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'));
        $datesAbsensi = $absensis->pluck('tanggal')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'));

        // Diurutkan secara ascending (dari tanggal 1 / terlama ke terbaru)
        $allDates = $datesLaporan->concat($datesAbsensi)
            ->unique()
            ->sort()
            ->values();

        $groupedLaporans = $laporans->groupBy(function ($item) {
            return Carbon::parse($item->tanggal_tugas)->format('Y-m-d');
        });

        $infoAbsensi = $absensis->keyBy(function ($item) {
            return Carbon::parse($item->tanggal)->format('Y-m-d');
        });

        return view('admin.kinerja.detail', compact(
            'pegawai', 'laporans', 'allDates', 'groupedLaporans', 'infoAbsensi',
            'tipeFilter', 'tanggal', 'bulan', 'tahun', 'statusValidasi'
        ));
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses untuk menghapus laporan kinerja.');
        }

        $laporan = LaporanKinerja::findOrFail($id);
        $laporan->delete();

        return redirect()->back()->with('success', 'Laporan kinerja berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses untuk menghapus laporan kinerja.');
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:laporan_kinerjas,id',
        ], [
            'ids.required' => 'Silakan pilih minimal satu laporan untuk dihapus.',
        ]);

        $jumlah = count($request->ids);
        LaporanKinerja::whereIn('id', $request->ids)->delete();

        return redirect()->back()->with('success', "{$jumlah} laporan kinerja berhasil dihapus.");
    }
}