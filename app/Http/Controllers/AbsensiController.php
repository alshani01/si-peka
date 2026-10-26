<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    // Menampilkan halaman rekap absen untuk PJLP dengan Filter Periode & Ringkasan
    public function index(Request $request)
    {
        if (Auth::user()->role !== 'pjlp') {
            abort(403);
        }

        // Ambil filter bulan & tahun dari URL (default bulan & tahun saat ini)
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        // Ambil data absensi milik user sesuai periode bulan & tahun
        $riwayatAbsensi = Absensi::where('user_id', Auth::id())
                            ->whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->orderBy('tanggal', 'asc')
                            ->get();

        // Hitung Ringkasan / Rekapitulasi Statistik Kehadiran pada Periode Tersebut
        $rekapStatistik = [
            'total_sakit'        => $riwayatAbsensi->where('status_kehadiran', 'sakit')->count(),
            'total_alpa'         => $riwayatAbsensi->where('status_kehadiran', 'alpa')->count(),
            'total_izin'         => $riwayatAbsensi->where('status_kehadiran', 'izin')->count(),
            'sisa_cuti'          => Auth::user()->sisa_cuti ?? '-',
            'total_terlambat'    => $riwayatAbsensi->sum('menit_terlambat'),
            'total_pulang_cepat' => $riwayatAbsensi->sum('menit_pulang_cepat'),
        ];

        return view('pjlp.absensi.index', compact('riwayatAbsensi', 'bulan', 'tahun', 'rekapStatistik'));
    }

    // Memproses form klarifikasi dari PJLP
    public function klarifikasi(Request $request, $id)
    {
        $request->validate([
            'alasan_klarifikasi' => 'required|string|max:500',
            'file_bukti_absensi' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ], [
            'file_bukti_absensi.max' => 'Ukuran file bukti tidak boleh lebih dari 2 MB.',
        ]);

        $absensi = Absensi::findOrFail($id);

        if ($absensi->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $namaFile = $absensi->file_bukti_absensi;

        if ($request->hasFile('file_bukti_absensi')) {
            $file = $request->file('file_bukti_absensi');
            $namaLengkapFile = Auth::id() . '_absen_' . time() . '_' . $file->getClientOriginalName();
            $file->storeAs('bukti_absensi', $namaLengkapFile, 'public');
            $namaFile = $namaLengkapFile;
        }

        $absensi->update([
            'alasan_klarifikasi' => $request->alasan_klarifikasi,
            'file_bukti_absensi' => $namaFile,
            'status_klarifikasi' => 'pending', 
        ]);

        return redirect()->back()->with('success', 'Klarifikasi absensi berhasil dikirim dan menunggu validasi Atasan.');
    }
}