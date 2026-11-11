<?php

namespace App\Http\Controllers;

use App\Models\LaporanKinerja;
use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AtasanLaporanController extends Controller
{
    public function index(Request $request)
    {
        $atasanId = Auth::id();
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $pegawaiId = $request->pegawai_id ?? 'all';
        $statusValidasi = $request->status_validasi ?? 'all';
        $search = $request->search ?? null;

        // Ambil daftar seluruh bawahan milik atasan ini
        $daftarBawahan = User::where('atasan_id', $atasanId)->orderBy('name', 'asc')->get();

        // A. Ambil Kombinasi Unik dari Laporan Kinerja
        $queryLaporan = LaporanKinerja::whereHas('user', fn($q) => $q->where('atasan_id', $atasanId))
            ->whereMonth('tanggal_tugas', $bulan)
            ->whereYear('tanggal_tugas', $tahun);

        if ($pegawaiId !== 'all' && !empty($pegawaiId)) {
            $queryLaporan->where('user_id', $pegawaiId);
        }

        if ($statusValidasi !== 'all' && !empty($statusValidasi)) {
            $queryLaporan->where('status_validasi', $statusValidasi);
        }

        if (!empty($search)) {
            $queryLaporan->where(function($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhere('uraian_tugas', 'like', "%{$search}%")
                  ->orWhere('aktivitas_kinerja', 'like', "%{$search}%");
            });
        }

        $groupsLaporan = $queryLaporan->select('user_id', 'tanggal_tugas')
            ->groupBy('user_id', 'tanggal_tugas')
            ->get()
            ->map(fn($item) => [
                'user_id' => $item->user_id,
                'tanggal_tugas' => $item->tanggal_tugas,
                'type' => 'laporan'
            ]);

        // B. Ambil Kombinasi Unik dari Absensi (Cuti, Sakit, Izin, Alpa)
        // Catatan: Jika user memfilter status validasi laporan selain 'all' atau 'pending', data absensi tanpa laporan bisa disembunyikan agar tabel akurat
        $groupsAbsensi = collect();
        if ($statusValidasi === 'all') {
            $queryAbsensi = Absensi::whereHas('user', fn($q) => $q->where('atasan_id', $atasanId))
                ->whereIn('status_kehadiran', ['cuti', 'sakit', 'izin', 'alpa'])
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun);

            if ($pegawaiId !== 'all' && !empty($pegawaiId)) {
                $queryAbsensi->where('user_id', $pegawaiId);
            }

            if (!empty($search)) {
                $queryAbsensi->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            }

            $groupsAbsensi = $queryAbsensi->select('user_id', 'tanggal as tanggal_tugas')
                ->groupBy('user_id', 'tanggal')
                ->get()
                ->map(fn($item) => [
                    'user_id' => $item->user_id,
                    'tanggal_tugas' => $item->tanggal_tugas,
                    'type' => 'absensi'
                ]);
        }

        // C. Gabungkan Kedua Grup
        $mergedGroups = collect();
        $allGroupsRaw = $groupsLaporan->concat($groupsAbsensi);

        foreach ($allGroupsRaw as $group) {
            $key = $group['user_id'] . '_' . $group['tanggal_tugas'];
            if (!$mergedGroups->has($key)) {
                $mergedGroups->put($key, (object) $group);
            }
        }

        // Urutkan berdasarkan user_id lalu tanggal_tugas
        $allGroups = $mergedGroups->sortBy([
            ['user_id', 'asc'],
            ['tanggal_tugas', 'asc']
        ])->values();

        // 2. Buat Manual LengthAwarePaginator (10 grup per halaman)
        $currentPage = Paginator::resolveCurrentPage();
        $perPage = 10;
        $currentGroupItems = $allGroups->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $laporanPaginated = new LengthAwarePaginator(
            $currentGroupItems,
            $allGroups->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        // 3. Ambil data detail (Laporan Kinerja & Data Absensi/Cuti) untuk grup halaman ini
        $laporanBawahan = collect();
        if ($currentGroupItems->isNotEmpty()) {
            foreach ($currentGroupItems as $group) {
                $key = $group->user_id . '_' . $group->tanggal_tugas;

                // Cek data laporan kinerja dengan menerapkan filter status validasi juga pada detailnya
                $itemsQuery = LaporanKinerja::where('user_id', $group->user_id)
                    ->where('tanggal_tugas', $group->tanggal_tugas);

                if ($statusValidasi !== 'all' && !empty($statusValidasi)) {
                    $itemsQuery->where('status_validasi', $statusValidasi);
                }

                $items = $itemsQuery->orderBy('created_at', 'asc')->get();

                // Cek data absensi/cuti
                $infoAbsensi = Absensi::where('user_id', $group->user_id)
                    ->where('tanggal', $group->tanggal_tugas)
                    ->whereIn('status_kehadiran', ['cuti', 'sakit', 'izin', 'alpa'])
                    ->first();

                // Jika difilter status validasi tertentu dan items kosong (misal karena absensi murni), skip atau masukkan
                if ($statusValidasi !== 'all' && $items->isEmpty()) {
                    continue;
                }

                $laporanBawahan->put($key, [
                    'items' => $items,
                    'user' => User::find($group->user_id),
                    'tanggal_tugas' => $group->tanggal_tugas,
                    'info_absensi' => $infoAbsensi
                ]);
            }
        }

        // Ambil semua ID pending untuk select-all global
        $allPendingIds = LaporanKinerja::whereHas('user', fn($q) => $q->where('atasan_id', $atasanId))
            ->where('status_validasi', 'pending')
            ->pluck('id')
            ->map(fn($id) => (string)$id)
            ->toArray();

        $pendingCount = LaporanKinerja::whereHas('user', fn($q) => $q->where('atasan_id', $atasanId))
            ->where('status_validasi', 'pending')->count();

        return view('atasan.laporan.index', compact('laporanBawahan', 'laporanPaginated', 'bulan', 'tahun', 'pegawaiId', 'search', 'daftarBawahan', 'pendingCount', 'allPendingIds', 'statusValidasi'));
    }

    public function validasi(Request $request, $id)
    {
        $request->validate([
            'status_validasi' => 'required|in:disetujui,ditolak',
            'catatan_atasan'  => 'required_if:status_validasi,ditolak|nullable|string'
        ], [
            'catatan_atasan.required_if' => 'Catatan atasan wajib diisi jika laporan ditolak!'
        ]);

        $laporan = LaporanKinerja::findOrFail($id);

        if ($laporan->user->atasan_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        // Simpan keputusan atasan beserta catatan
        $laporan->update([
            'status_validasi' => $request->status_validasi,
            'catatan_atasan'  => $request->catatan_atasan,
        ]);

        return redirect()->back()->with('success', 'Status laporan kinerja berhasil diperbarui.');
    }

    public function bulkValidasi(Request $request)
    {
        $request->validate([
            'laporan_ids'   => 'required|array',
            'laporan_ids.*' => 'exists:laporan_kinerjas,id',
        ], [
            'laporan_ids.required' => 'Pilih minimal satu laporan untuk disetujui!'
        ]);

        $atasanId = Auth::id();

        $count = LaporanKinerja::whereIn('id', $request->laporan_ids)
            ->whereHas('user', fn($q) => $q->where('atasan_id', $atasanId))
            ->where('status_validasi', 'pending')
            ->update([
                'status_validasi' => 'disetujui',
                'catatan_atasan'  => null,
            ]);

        return redirect()->back()->with('success', "Sebanyak {$count} laporan kinerja berhasil disetujui sekaligus.");
    }
}