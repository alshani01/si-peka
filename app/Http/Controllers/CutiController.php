<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CutiController extends Controller
{
    // 1. Menampilkan Halaman Cuti PJLP dengan Filter Tahun & Hitung Otomatis Kuota
    public function index(Request $request)
    {
        if (Auth::user()->role !== 'pjlp') {
            abort(403);
        }

        // Ambil filter tahun dari URL (default tahun berjalan saat ini)
        $tahun = $request->input('tahun', date('Y'));

        // Ambil riwayat cuti pegawai yang sedang login sesuai tahun yang dipilih
        $riwayatCuti = Cuti::where('user_id', Auth::id())
                        ->whereYear('tanggal_mulai', $tahun)
                        ->orderBy('created_at', 'desc')
                        ->get();

        // --- MENGHITUNG SISA CUTI TAHUNAN SESUAI TAHUN YANG DIPILIH ---
        $cutiTahunanDisetujui = Cuti::where('user_id', Auth::id())
                                ->where('jenis_cuti', 'Tahunan')
                                ->where('status_validasi', 'disetujui')
                                ->whereYear('tanggal_mulai', $tahun)
                                ->get();

        $daftarLibur = HariLibur::pluck('tanggal')->toArray();
        $totalCutiTerpakai = 0;

        foreach ($cutiTahunanDisetujui as $itemCuti) {
            $mulai = Carbon::parse($itemCuti->tanggal_mulai);
            $selesai = Carbon::parse($itemCuti->tanggal_selesai);
            
            while ($mulai->lte($selesai)) {
                $isWeekend = $mulai->isWeekend();
                $isLiburNasional = in_array($mulai->format('Y-m-d'), $daftarLibur);
                
                if (!$isWeekend && !$isLiburNasional) {
                    $totalCutiTerpakai++;
                }
                $mulai->addDay();
            }
        }

        $kuotaTahun = 12;
        $sisaCuti = max(0, $kuotaTahun - $totalCutiTerpakai);

        // Mengirimkan $daftarLibur ke view untuk kalkulasi real-time via JS
        return view('pjlp.cuti.index', compact('riwayatCuti', 'sisaCuti', 'tahun', 'daftarLibur'));
    }

    // 2. Memproses Pengajuan Cuti
    public function store(Request $request)
    {
        $request->validate([
            'jenis_cuti' => 'required|in:Tahunan,Sakit,Melahirkan,Alasan Penting',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string',
            'file_bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'file_bukti.max' => 'Ukuran file bukti tidak boleh lebih dari 2 MB.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        $mulai = Carbon::parse($request->tanggal_mulai);
        $selesai = Carbon::parse($request->tanggal_selesai);

        // =========================================================
        // 1. PENCEGAHAN TANGGAL BENTROK (OVERLAPPING DATE CHECK)
        // =========================================================
        $isOverlapping = Cuti::where('user_id', Auth::id())
            ->whereIn('status_validasi', ['pending', 'disetujui'])
            ->where(function ($query) use ($request) {
                $query->whereBetween('tanggal_mulai', [$request->tanggal_mulai, $request->tanggal_selesai])
                      ->orWhereBetween('tanggal_selesai', [$request->tanggal_mulai, $request->tanggal_selesai])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('tanggal_mulai', '<=', $request->tanggal_mulai)
                            ->where('tanggal_selesai', '>=', $request->tanggal_selesai);
                      });
            })->exists();

        if ($isOverlapping) {
            return redirect()->back()->withErrors([
                'tanggal_bentrok' => 'Anda sudah memiliki pengajuan cuti (pending/disetujui) pada rentang tanggal tersebut!'
            ])->withInput();
        }

        // --- MENGHITUNG HARI KERJA AKTUAL ---
        $daftarLibur = HariLibur::pluck('tanggal')->toArray();
        $jumlahHariCuti = 0;
        $tanggalBerjalan = $mulai->copy();

        while ($tanggalBerjalan->lte($selesai)) {
            $isWeekend = $tanggalBerjalan->isWeekend();
            $isLiburNasional = in_array($tanggalBerjalan->format('Y-m-d'), $daftarLibur);
            
            if (!$isWeekend && !$isLiburNasional) {
                $jumlahHariCuti++;
            }
            
            $tanggalBerjalan->addDay();
        }

        // Cek Kuota Cuti Tahunan Berdasarkan Tahun dari Tanggal Pengajuan
        if ($request->jenis_cuti === 'Tahunan') {
            $tahunPengajuan = $mulai->format('Y');

            $cutiTahunanDisetujui = Cuti::where('user_id', Auth::id())
                                    ->where('jenis_cuti', 'Tahunan')
                                    ->where('status_validasi', 'disetujui')
                                    ->whereYear('tanggal_mulai', $tahunPengajuan)
                                    ->get();

            $totalTerpakai = 0;
            foreach ($cutiTahunanDisetujui as $itemCuti) {
                $tMulai = Carbon::parse($itemCuti->tanggal_mulai);
                $tSelesai = Carbon::parse($itemCuti->tanggal_selesai);
                while ($tMulai->lte($tSelesai)) {
                    if (!$tMulai->isWeekend() && !in_array($tMulai->format('Y-m-d'), $daftarLibur)) {
                        $totalTerpakai++;
                    }
                    $tMulai->addDay();
                }
            }

            $sisaCutiTahunPengajuan = max(0, 12 - $totalTerpakai);

            if ($jumlahHariCuti > $sisaCutiTahunPengajuan) {
                return redirect()->back()->withErrors([
                    'sisa_cuti' => 'Sisa cuti tahunan Anda di tahun ' . $tahunPengajuan . ' tidak cukup! (Sisa: ' . $sisaCutiTahunPengajuan . ' hari, dibutuhkan: ' . $jumlahHariCuti . ' hari kerja).'
                ])->withInput();
            }
        }

        // Proses unggah file bukti jika ada
        $namaFile = null;
        if ($request->hasFile('file_bukti')) {
            $file = $request->file('file_bukti');
            $namaFile = Auth::id() . '_cuti_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('bukti_cuti', $namaFile, 'public');
        }

        // Simpan ke database
        Cuti::create([
            'user_id' => Auth::id(),
            'jenis_cuti' => $request->jenis_cuti,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => $request->alasan,
            'file_bukti' => $namaFile,
            'status_validasi' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Pengajuan cuti (' . $jumlahHariCuti . ' hari kerja) berhasil dikirim dan menunggu validasi Atasan.');
    }
}