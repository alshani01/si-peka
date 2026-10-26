<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF;

class AdminAbsensiImportController extends Controller
{
    public function index()
    {
        return view('admin.absensi.import');
    }

    public function rekap(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $search = $request->input('search');

        $rekapData = $this->getRekapData($bulan, $tahun, $search);
        $liburs = HariLibur::orderBy('tanggal', 'desc')->get();

        return view('admin.absensi.rekap', compact('rekapData', 'bulan', 'tahun', 'search', 'liburs'));
    }

    public function downloadPdf(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        
        $rekapData = $this->getRekapData($bulan, $tahun);
        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F');

        $pdf = PDF::loadView('admin.absensi.pdf_rekap', compact('rekapData', 'bulan', 'tahun', 'namaBulan'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("LAPORAN_REKAPITULASI_ABSENSI_KPKP_{$namaBulan}_{$tahun}.pdf");
    }

    public function prosesImport(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:2048',
        ], [
            'file_excel.required' => 'Silakan pilih file terlebih dahulu.',
            'file_excel.mimes' => 'Format file harus berupa excel (.xlsx/.xls) atau .csv',
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\AbsensiImport, $request->file('file_excel'));
            return redirect()->route('admin.absensi.rekap')->with('success', 'Data absensi berhasil diimpor! Berikut rekapitulasi terbarunya.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Template_Import_Absensi_SI_PEKA.csv"',
        ];

        $columns = ['id_pjlp', 'tanggal', 'jam_masuk', 'jam_pulang', 'status_kehadiran', 'menit_terlambat', 'menit_pulang_cepat'];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            fputcsv($file, ['80123456', '2026-10-01', '07:25:00', '16:00:00', 'hadir', '0', '0']);
            fputcsv($file, ['80123456', '2026-10-02', '07:45:00', '16:00:00', 'terlambat', '15', '0']);
            fputcsv($file, ['80123456', '2026-10-03', '', '', 'alpa', '0', '0']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getRekapData($bulan, $tahun, $search = null)
    {
        $query = User::whereIn('role', ['pjlp', 'p3k']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('id_pjlp', 'like', "%{$search}%");
            });
        }

        // Diurutkan berdasarkan hirarki
        $pegawais = $query->orderByRaw("COALESCE(urutan_hirarki, 999) ASC")
            ->orderBy('name', 'asc')
            ->get();

        return $pegawais->map(function ($pegawai) use ($bulan, $tahun) {
            $absensis = Absensi::where('user_id', $pegawai->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->get();

            $hadir = $absensis->where('status_kehadiran', 'hadir')->count();
            $menitTerlambat = $absensis->sum('menit_terlambat');
            $menitPulangCepat = $absensis->sum('menit_pulang_cepat');

            $izinSakit = $absensis->whereIn('status_kehadiran', ['izin', 'sakit'])
                ->where('status_klarifikasi', 'disetujui')
                ->count();

            $alpa = $absensis->where('status_kehadiran', 'alpa')
                ->whereIn('status_klarifikasi', ['tidak_perlu', 'ditolak', 'pending'])
                ->count();

            $pendingList = $absensis->where('status_klarifikasi', 'pending')->map(function ($item) {
                return [
                    'tanggal' => Carbon::parse($item->tanggal)->translatedFormat('d F Y'),
                    'status_kehadiran' => ucfirst($item->status_kehadiran ?? 'Tanpa Keterangan'),
                    'alasan' => $item->alasan_klarifikasi ?? 'Tidak menyertakan alasan.',
                    'bukti' => $item->file_bukti_absensi ? asset('storage/' . $item->file_bukti_absensi) : null,
                ];
            })->values()->all();

            return [
                'id_pjlp' => $pegawai->id_pjlp ?? '-',
                'nama' => $pegawai->name,
                'jenis_pekerjaan' => $pegawai->jenis_pekerjaan ?? '-',
                'unit_kerja' => $pegawai->unit_kerja ?? '-',
                'hadir' => $hadir,
                'menit_terlambat' => $menitTerlambat,
                'menit_pulang_cepat' => $menitPulangCepat,
                'izin_sakit' => $izinSakit,
                'alpa' => $alpa,
                'pending_validasi' => count($pendingList),
                'detail_pending' => $pendingList,
            ];
        });
    }
}