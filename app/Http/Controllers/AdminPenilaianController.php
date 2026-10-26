<?php

namespace App\Http\Controllers;

use App\Models\PenilaianPerilaku;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AdminPenilaianController extends Controller
{
    public function rekap(Request $request)
    {
        $bulan = (int) ($request->bulan ?? date('m'));
        $tahun = (int) ($request->tahun ?? date('Y'));
        $search = $request->search;
        $atasan_id = $request->atasan_id;
        $kategori = $request->kategori;

        $userAuth = auth()->user();
        // Admin & Pimpinan dapat melihat seluruh data
        $canViewAll = in_array($userAuth->role, ['admin', 'pimpinan']);

        // Base Query Karyawan PJLP
        $query = User::with(['atasan', 'penilaianPerilaku' => function($q) use ($bulan, $tahun) {
            $q->where('bulan', $bulan)->where('tahun', $tahun);
        }])->where('role', 'pjlp');

        // Jika BUKAN Admin/Pimpinan, filter bawahan miliknya saja
        if (!$canViewAll) {
            $query->where('atasan_id', $userAuth->id);
            $atasan_id = $userAuth->id;
        }

        // 1. Dashboard Statistik Indikator
        $baseUserQuery = User::where('role', 'pjlp');
        if (!$canViewAll) {
            $baseUserQuery->where('atasan_id', $userAuth->id);
        }
        $totalPjlp = $baseUserQuery->count();

        $basePenilaianQuery = PenilaianPerilaku::where('bulan', $bulan)->where('tahun', $tahun);
        if (!$canViewAll) {
            $basePenilaianQuery->whereHas('user', function($q) use ($userAuth) {
                $q->where('atasan_id', $userAuth->id);
            });
        }
        
        $totalSudahDinilai = (clone $basePenilaianQuery)->count();
        $totalBelumDinilai = max(0, $totalPjlp - $totalSudahDinilai);
        $rataRataSektor = (clone $basePenilaianQuery)->avg('rata_rata') ?? 0;

        // 2. Cross-Filtering
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('id_pjlp', 'like', "%{$search}%");
            });
        }

        if ($canViewAll && !empty($atasan_id)) {
            $query->where('atasan_id', $atasan_id);
        }

        if (!empty($kategori)) {
            if ($kategori === 'belum') {
                $query->whereDoesntHave('penilaianPerilaku', function($q) use ($bulan, $tahun) {
                    $q->where('bulan', $bulan)->where('tahun', $tahun);
                });
            } else {
                $query->whereHas('penilaianPerilaku', function($q) use ($bulan, $tahun, $kategori) {
                    $q->where('bulan', $bulan)->where('tahun', $tahun)->where('kategori', $kategori);
                });
            }
        }

        // Ambil data paginasi
        $bawahans = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();
        
        // Data untuk Dropdown Filter Atasan
        $atasans = $canViewAll ? User::where('role', 'atasan')->orderBy('name', 'asc')->get() : collect();

        return view('admin.penilaian.index', compact(
            'bawahans', 'atasans', 'bulan', 'tahun', 'search', 'atasan_id', 'kategori',
            'totalPjlp', 'totalSudahDinilai', 'totalBelumDinilai', 'rataRataSektor'
        ));
    }

    public function pdf(Request $request)
    {
        $bulan = (int) ($request->input('bulan', date('m')));
        $tahun = (int) ($request->input('tahun', date('Y')));
        $search = $request->input('search');
        $atasan_id = $request->input('atasan_id');
        $kategori = $request->input('kategori');

        $userAuth = auth()->user();
        $canViewAll = in_array($userAuth->role, ['admin', 'pimpinan']);

        $query = User::where('role', 'pjlp')
            ->with(['atasan', 'penilaianPerilaku' => function ($q) use ($bulan, $tahun) {
                $q->where('bulan', $bulan)->where('tahun', $tahun);
            }]);

        if (!$canViewAll) {
            $query->where('atasan_id', $userAuth->id);
        } else {
            if (!empty($atasan_id)) {
                $query->where('atasan_id', $atasan_id);
            }
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('id_pjlp', 'like', "%{$search}%");
            });
        }

        if (!empty($kategori)) {
            if ($kategori === 'belum') {
                $query->whereDoesntHave('penilaianPerilaku', function ($q) use ($bulan, $tahun) {
                    $q->where('bulan', $bulan)->where('tahun', $tahun);
                });
            } else {
                $query->whereHas('penilaianPerilaku', function ($q) use ($bulan, $tahun, $kategori) {
                    $q->where('bulan', $bulan)->where('tahun', $tahun)->where('kategori', $kategori);
                });
            }
        }

        $bawahans = $query->orderBy('name', 'asc')->get();
        $data = $bawahans;

        $pdf = Pdf::loadView('admin.penilaian.pdf', compact('bawahans', 'data', 'bulan', 'tahun'))
                ->setPaper('a4', 'landscape');

        return $pdf->download("Rekap_Penilaian_Perilaku_{$bulan}_{$tahun}.pdf");
    }

    public function exportPdf(Request $request)
    {
        return $this->pdf($request);
    }

    public function trendTahunan(Request $request, $id)
    {
        $tahun = (int) ($request->tahun ?? date('Y'));
        $pegawai = User::findOrFail($id);
        $userAuth = auth()->user();
        $canViewAll = in_array($userAuth->role, ['admin', 'pimpinan']);

        if (!$canViewAll && $pegawai->atasan_id !== $userAuth->id) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $penilaians = PenilaianPerilaku::where('user_id', $id)
            ->where('tahun', $tahun)
            ->orderBy('bulan', 'asc')
            ->get();
            
        return response()->json($penilaians);
    }

    /**
     * Hapus 1 Data Penilaian Perilaku (Khusus Admin)
     */
    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses untuk menghapus penilaian perilaku.');
        }

        $penilaian = PenilaianPerilaku::findOrFail($id);
        $penilaian->delete();

        return redirect()->back()->with('success', 'Data penilaian perilaku berhasil dihapus.');
    }

    /**
     * Hapus Massal (Bulk Delete) Penilaian Perilaku (Khusus Admin)
     */
    public function bulkDestroy(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses untuk menghapus penilaian perilaku.');
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:penilaian_perilakus,id',
        ], [
            'ids.required' => 'Silakan pilih minimal satu data penilaian untuk dihapus.',
        ]);

        $jumlah = count($request->ids);
        PenilaianPerilaku::whereIn('id', $request->ids)->delete();

        return redirect()->back()->with('success', "{$jumlah} data penilaian perilaku berhasil dihapus.");
    }
}