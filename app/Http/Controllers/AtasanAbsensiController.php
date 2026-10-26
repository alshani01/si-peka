<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\LaporanKinerja;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AtasanAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $atasanId = Auth::id();
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $pegawaiId = $request->input('pegawai_id', 'all');
        $statusKlarifikasi = $request->input('status_klarifikasi', 'all');
        $search = $request->input('search');

        // Ambil daftar bawahan untuk dropdown filter
        $daftarBawahan = User::where('atasan_id', $atasanId)->orderBy('name', 'asc')->get();

        // Base Query Rekap Absensi dengan Join Tabel Users agar dapat diurutkan/dikelompokkan per Pegawai
        $query = Absensi::with('user')
            ->join('users', 'absensis.user_id', '=', 'users.id')
            ->select('absensis.*')
            ->where('users.atasan_id', $atasanId)
            ->whereMonth('absensis.tanggal', $bulan)
            ->whereYear('absensis.tanggal', $tahun);

        if ($pegawaiId !== 'all' && !empty($pegawaiId)) {
            $query->where('absensis.user_id', $pegawaiId);
        }

        if ($statusKlarifikasi !== 'all' && !empty($statusKlarifikasi)) {
            $query->where('absensis.status_klarifikasi', $statusKlarifikasi);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('absensis.alasan_klarifikasi', 'like', "%{$search}%")
                  ->orWhere('absensis.status_kehadiran', 'like', "%{$search}%");
            });
        }

        // Ambil seluruh ID bernilai 'pending' sesuai filter untuk fitur Select All
        $allPendingIds = Absensi::whereHas('user', fn($q) => $q->where('atasan_id', $atasanId))
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('status_klarifikasi', 'pending')
            ->when($pegawaiId !== 'all' && !empty($pegawaiId), fn($q) => $q->where('user_id', $pegawaiId))
            ->when(!empty($search), function($q) use ($search) {
                $q->where(function($s) use ($search) {
                    $s->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                      ->orWhere('alasan_klarifikasi', 'like', "%{$search}%")
                      ->orWhere('status_kehadiran', 'like', "%{$search}%");
                });
            })
            ->pluck('id')
            ->map(fn($id) => (string)$id)
            ->toArray();

        // URUTAN DATA: 
        // 1. Pengajuan 'Pending' diutamakan di paling atas
        // 2. Dipisah / Dikelompokkan berdasarkan Nama Pegawai (A-Z)
        // 3. Diurutkan secara kronologis berdasarkan Tanggal per Pegawai
        $absensiBawahan = $query->orderByRaw("CASE WHEN absensis.status_klarifikasi = 'pending' THEN 1 ELSE 2 END")
            ->orderBy('users.name', 'asc')
            ->orderBy('absensis.tanggal', 'asc')
            ->paginate(10)
            ->withQueryString();

        $pendingCount = Absensi::whereHas('user', fn($q) => $q->where('atasan_id', $atasanId))
            ->where('status_klarifikasi', 'pending')
            ->count();

        return view('atasan.absensi.index', compact(
            'absensiBawahan', 'daftarBawahan', 'bulan', 'tahun', 
            'pegawaiId', 'statusKlarifikasi', 'search', 'pendingCount', 'allPendingIds'
        ));
    }

    public function validasi(Request $request, $id)
    {
        $request->validate(['status_klarifikasi' => 'required|in:disetujui,ditolak']);
        $absensi = Absensi::findOrFail($id);

        if ($absensi->user->atasan_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $absensi->update(['status_klarifikasi' => $request->status_klarifikasi]);
        $this->syncLaporanKinerja($absensi, $request->status_klarifikasi);

        return redirect()->back()->with('success', 'Status klarifikasi absensi berhasil diperbarui.');
    }

    public function bulkValidasi(Request $request)
    {
        $request->validate([
            'absensi_ids' => 'required|array|min:1',
            'absensi_ids.*' => 'exists:absensis,id',
            'status_klarifikasi' => 'required|in:disetujui,ditolak'
        ]);

        $absensis = Absensi::whereIn('id', $request->absensi_ids)
            ->whereHas('user', fn($q) => $q->where('atasan_id', Auth::id()))
            ->where('status_klarifikasi', 'pending')
            ->get();

        $count = 0;
        foreach ($absensis as $absensi) {
            $absensi->update(['status_klarifikasi' => $request->status_klarifikasi]);
            $this->syncLaporanKinerja($absensi, $request->status_klarifikasi);
            $count++;
        }

        return redirect()->back()->with('success', "Sebanyak {$count} pengajuan klarifikasi berhasil diproses.");
    }

    private function syncLaporanKinerja($absensi, $status_klarifikasi)
    {
        if ($status_klarifikasi === 'disetujui') {
            $statusKehadiran = strtolower($absensi->status_kehadiran); 
            $keteranganUraian = match ($statusKehadiran) {
                'sakit' => 'Sakit (Klarifikasi Absensi Disetujui: ' . ($absensi->alasan_klarifikasi ?? '-') . ')',
                'izin'  => 'Izin (Klarifikasi Absensi Disetujui: ' . ($absensi->alasan_klarifikasi ?? '-') . ')',
                'alpa'  => 'Alpa / Tanpa Keterangan (Hasil Rekapitulasi Akhir Bulan)',
                default => 'Klarifikasi Absensi (' . ucfirst($statusKehadiran) . '): ' . ($absensi->alasan_klarifikasi ?? '-')
            };

            LaporanKinerja::updateOrCreate(
                [
                    'user_id'       => $absensi->user_id, 
                    'tanggal_tugas' => $absensi->tanggal
                ],
                [
                    'uraian_tugas'    => $keteranganUraian,
                    'file_bukti'      => $absensi->file_bukti_absensi ?? '',
                    'status_validasi' => 'disetujui',
                    'catatan_atasan'  => 'Diisi otomatis oleh sistem (Sinkronisasi Validasi Absensi: ' . ucfirst($statusKehadiran) . ')',
                ]
            );
        }
    }
}