<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LaporanKinerja;
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

        // Panggil dengan pagination (default: true)
        $rekapKinerja = $this->getKinerjaData($tipeFilter, $tanggal, $bulan, $tahun, $search, $statusValidasi, true);

        return view('admin.kinerja.rekap', compact('rekapKinerja', 'tipeFilter', 'tanggal', 'bulan', 'tahun', 'search', 'statusValidasi'));
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
                // Filter pegawai yang TIDAK memiliki laporan sama sekali pada periode ini
                $query->whereDoesntHave('laporanKinerjas', function ($q) use ($tipeFilter, $tanggal, $bulan, $tahun) {
                    if ($tipeFilter === 'harian') {
                        $q->whereDate('tanggal_tugas', $tanggal);
                    } else {
                        $q->whereMonth('tanggal_tugas', $bulan)
                        ->whereYear('tanggal_tugas', $tahun);
                    }
                });
            } else {
                // Filter pegawai yang memilki laporan dengan status_validasi spesifik (pending, disetujui, direvisi)
                $query->whereHas('laporanKinerjas', function ($q) use ($tipeFilter, $tanggal, $bulan, $tahun, $statusValidasi) {
                    $q->where('status_validasi', $statusValidasi);
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

        if ($tipeFilter === 'harian') {
            $baseQuery->whereDate('tanggal_tugas', $tanggal);
        } else {
            $baseQuery->whereMonth('tanggal_tugas', $bulan)
                    ->whereYear('tanggal_tugas', $tahun);
        }

        $allLaporans = $baseQuery->orderBy('tanggal_tugas', 'asc')->get();

        $filteredLaporans = $allLaporans;
        if (filled($statusValidasi) && $statusValidasi !== 'belum_kirim') {
            $filteredLaporans = $allLaporans->where('status_validasi', $statusValidasi);
        }

        return [
            'id' => $pegawai->id,
            'id_pjlp' => $pegawai->id_pjlp ?? '-',
            'nama' => $pegawai->name,
            'jenis_pekerjaan' => $pegawai->jenis_pekerjaan ?? '-',
            'lokasi_tugas' => $pegawai->lokasi_tugas ?? '-',
            'total_laporan' => $statusValidasi === 'belum_kirim' ? 0 : $filteredLaporans->count(),
            'laporan_acc' => $allLaporans->where('status_validasi', 'disetujui')->count(),
            'laporan_ditolak' => $allLaporans->where('status_validasi', 'direvisi')->count(),
            'laporan_pending' => $allLaporans->where('status_validasi', 'pending')->count(),
            'nilai_perilaku' => $pegawai->nilai_perilaku ?? 0,
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

        // Mengambil seluruh data tanpa pagination untuk cetak PDF
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

        if ($tipeFilter === 'harian') {
            $laporansQuery->whereDate('tanggal_tugas', $tanggal);
        } else {
            $laporansQuery->whereMonth('tanggal_tugas', $bulan)
                         ->whereYear('tanggal_tugas', $tahun);
        }

        // Filter status validasi pada halaman detail jika ada
        if (filled($statusValidasi)) {
            $laporansQuery->where('status_validasi', $statusValidasi);
        }

        // Ambil laporan dan kelompokkan berdasarkan tanggal_tugas
        $laporans = $laporansQuery->orderBy('tanggal_tugas', 'desc')
                                  ->orderBy('id', 'asc')
                                  ->get();

        $groupedLaporans = $laporans->groupBy(function ($item) {
            return Carbon::parse($item->tanggal_tugas)->format('Y-m-d');
        });

        return view('admin.kinerja.detail', compact('pegawai', 'laporans', 'groupedLaporans', 'tipeFilter', 'tanggal', 'bulan', 'tahun', 'statusValidasi'));
    }

    /**
     * Hapus 1 Laporan Kinerja (Khusus Admin)
     */
    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses untuk menghapus laporan kinerja.');
        }

        $laporan = LaporanKinerja::findOrFail($id);
        $laporan->delete();

        return redirect()->back()->with('success', 'Laporan kinerja berhasil dihapus.');
    }

    /**
     * Hapus Massal (Bulk Delete) Laporan Kinerja (Khusus Admin)
     */
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