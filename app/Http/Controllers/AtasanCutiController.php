<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use App\Models\Absensi;
use App\Models\HariLibur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AtasanCutiController extends Controller
{
    public function index(Request $request)
    {
        $atasanId = Auth::id();
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $pegawaiId = $request->input('pegawai_id', 'all');
        $statusValidasi = $request->input('status_validasi', 'all');
        $search = $request->input('search');

        // Ambil daftar bawahan untuk dropdown filter
        $daftarBawahan = User::where('atasan_id', $atasanId)->orderBy('name', 'asc')->get();

        // Query Utama dengan Join Tabel Users agar dapat diurutkan/dikelompokkan per Pegawai
        $query = Cuti::with('user')
            ->join('users', 'cutis.user_id', '=', 'users.id')
            ->select('cutis.*')
            ->where('users.atasan_id', $atasanId)
            ->where(function ($q) use ($bulan, $tahun) {
                $q->whereMonth('cutis.tanggal_mulai', $bulan)->whereYear('cutis.tanggal_mulai', $tahun)
                  ->orWhere(function ($q2) use ($bulan, $tahun) {
                      $q2->whereMonth('cutis.tanggal_selesai', $bulan)->whereYear('cutis.tanggal_selesai', $tahun);
                  });
            });

        if ($pegawaiId !== 'all' && !empty($pegawaiId)) {
            $query->where('cutis.user_id', $pegawaiId);
        }

        if ($statusValidasi !== 'all' && !empty($statusValidasi)) {
            $query->where('cutis.status_validasi', $statusValidasi);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('cutis.alasan', 'like', "%{$search}%")
                  ->orWhere('cutis.jenis_cuti', 'like', "%{$search}%");
            });
        }

        // Ambil seluruh ID bernilai 'pending' sesuai filter untuk fitur Select All (Bulk Action)
        $allPendingIds = Cuti::whereHas('user', fn($q) => $q->where('atasan_id', $atasanId))
            ->where('status_validasi', 'pending')
            ->where(function ($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal_mulai', $bulan)->whereYear('tanggal_mulai', $tahun)
                  ->orWhere(function ($q2) use ($bulan, $tahun) {
                      $q2->whereMonth('tanggal_selesai', $bulan)->whereYear('tanggal_selesai', $tahun);
                  });
            })
            ->when($pegawaiId !== 'all' && !empty($pegawaiId), fn($q) => $q->where('user_id', $pegawaiId))
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($s) use ($search) {
                    $s->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                      ->orWhere('alasan', 'like', "%{$search}%")
                      ->orWhere('jenis_cuti', 'like', "%{$search}%");
                });
            })
            ->pluck('id')
            ->map(fn($id) => (string)$id)
            ->toArray();

        // HITUNG HARI KERJA CUTI UNTUK TIAP DATA CUTI (PENGABAIAN WEEKEND & HARI LIBUR)
        $daftarLibur = HariLibur::pluck('tanggal')->toArray();

        // URUTAN DATA: 
        // 1. Status 'pending' paling atas
        // 2. Dipisah / Dikelompokkan berdasarkan Nama Pegawai (A-Z)
        // 3. Diurutkan berdasarkan Tanggal Mulai Cuti
        $cutiBawahan = $query->orderByRaw("CASE WHEN cutis.status_validasi = 'pending' THEN 1 ELSE 2 END")
            ->orderBy('users.name', 'asc')
            ->orderBy('cutis.tanggal_mulai', 'asc')
            ->paginate(10)
            ->through(function ($item) use ($daftarLibur) {
                $mulai = Carbon::parse($item->tanggal_mulai);
                $selesai = Carbon::parse($item->tanggal_selesai);
                $hariKerja = 0;
                $tmp = $mulai->copy();
                while ($tmp->lte($selesai)) {
                    if (!$tmp->isWeekend() && !in_array($tmp->format('Y-m-d'), $daftarLibur)) {
                        $hariKerja++;
                    }
                    $tmp->addDay();
                }
                $item->durasi_hari_kerja = $hariKerja;
                return $item;
            })
            ->withQueryString();

        $pendingCount = Cuti::whereHas('user', fn($q) => $q->where('atasan_id', $atasanId))
            ->where('status_validasi', 'pending')
            ->count();

        return view('atasan.cuti.index', compact(
            'cutiBawahan', 'daftarBawahan', 'bulan', 'tahun', 
            'pegawaiId', 'statusValidasi', 'search', 'pendingCount', 'allPendingIds'
        ));
    }

    public function validasi(Request $request, $id)
    {
        $request->validate([
            'status_validasi' => 'required|in:disetujui,ditolak',
            'catatan_atasan' => 'required_if:status_validasi,ditolak|nullable|string',
        ], [
            'catatan_atasan.required_if' => 'Catatan atasan wajib diisi jika pengajuan cuti ditolak!'
        ]);

        $cuti = Cuti::findOrFail($id);

        if ($cuti->user->atasan_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $result = $this->processValidasiCuti($cuti, $request->status_validasi, $request->catatan_atasan);

        if (!$result['success']) {
            return redirect()->back()->withErrors(['sisa_cuti' => $result['message']]);
        }

        return redirect()->back()->with('success', 'Status pengajuan cuti berhasil diperbarui dan terintegrasi dengan rekap absensi.');
    }

    public function bulkValidasi(Request $request)
    {
        $request->validate([
            'cuti_ids' => 'required|array|min:1',
            'cuti_ids.*' => 'exists:cutis,id',
            'status_validasi' => 'required|in:disetujui,ditolak'
        ]);

        $cutis = Cuti::whereIn('id', $request->cuti_ids)
            ->whereHas('user', fn($q) => $q->where('atasan_id', Auth::id()))
            ->where('status_validasi', 'pending')
            ->get();

        $count = 0;
        $errors = [];

        foreach ($cutis as $cuti) {
            $res = $this->processValidasiCuti($cuti, $request->status_validasi);
            if ($res['success']) {
                $count++;
            } else {
                $errors[] = $res['message'];
            }
        }

        if (count($errors) > 0 && $count === 0) {
            return redirect()->back()->withErrors(['sisa_cuti' => implode(' ', $errors)]);
        }

        return redirect()->back()->with('success', "Sebanyak {$count} pengajuan cuti berhasil diproses.");
    }

    /**
     * Helper Fungsi untuk Memproses Validasi, Potong/Refund Sisa Cuti, dan Sinkronisasi ke Tabel Absensi
     */
    private function processValidasiCuti($cuti, $statusValidasi, $catatanAtasan = null)
    {
        $oldStatus = $cuti->status_validasi;
        $mulai = Carbon::parse($cuti->tanggal_mulai);
        $selesai = Carbon::parse($cuti->tanggal_selesai);
        $daftarLibur = HariLibur::pluck('tanggal')->toArray();

        // Hitung durasi hari kerja
        $jumlahHariKerja = 0;
        $tanggalBerjalan = $mulai->copy();
        while ($tanggalBerjalan->lte($selesai)) {
            if (!$tanggalBerjalan->isWeekend() && !in_array($tanggalBerjalan->format('Y-m-d'), $daftarLibur)) {
                $jumlahHariKerja++;
            }
            $tanggalBerjalan->addDay();
        }

        $user = $cuti->user;

        // LOGIKA SISA CUTI TAHUNAN:
        // 1. Jika Status Baru DISETUJUI dan Status Lama BUKAN DISETUJUI -> Potong Sisa Cuti
        if ($statusValidasi === 'disetujui' && $oldStatus !== 'disetujui') {
            if ($cuti->jenis_cuti === 'Tahunan') {
                if ($user->sisa_cuti < $jumlahHariKerja) {
                    return [
                        'success' => false,
                        'message' => "Sisa cuti pegawai {$user->name} tidak mencukupi ({$user->sisa_cuti} hari tersisa, dibutuhkan {$jumlahHariKerja} hari)."
                    ];
                }
                $user->sisa_cuti -= $jumlahHariKerja;
                $user->save();
            }
        } 
        // 2. Jika Status Baru DITOLAK dan Status Lama DISETUJUI -> Kembalikan Sisa Cuti (Reversal)
        elseif ($statusValidasi === 'ditolak' && $oldStatus === 'disetujui') {
            if ($cuti->jenis_cuti === 'Tahunan') {
                $user->sisa_cuti += $jumlahHariKerja;
                $user->save();
            }
        }

        // Update data Cuti
        $cuti->update([
            'status_validasi' => $statusValidasi,
            'catatan_atasan'  => $statusValidasi === 'ditolak' ? $catatanAtasan : null,
        ]);

        // SINKRONISASI KE TABEL ABSENSIS
        $tanggalBerjalan = $mulai->copy();
        if ($statusValidasi === 'disetujui') {
            while ($tanggalBerjalan->lte($selesai)) {
                $isWeekend = $tanggalBerjalan->isWeekend();
                $isLiburNasional = in_array($tanggalBerjalan->format('Y-m-d'), $daftarLibur);

                if (!$isWeekend && !$isLiburNasional) {
                    Absensi::updateOrCreate(
                        [
                            'user_id' => $cuti->user_id,
                            'tanggal' => $tanggalBerjalan->format('Y-m-d'),
                        ],
                        [
                            'jam_masuk'          => null,
                            'jam_pulang'         => null,
                            'status_kehadiran'   => 'cuti',
                            'menit_terlambat'    => 0,
                            'menit_pulang_cepat' => 0,
                            'alasan_klarifikasi' => 'Cuti ' . $cuti->jenis_cuti . ' (Disetujui Atasan)',
                            'status_klarifikasi' => 'tidak_perlu',
                        ]
                    );
                }
                $tanggalBerjalan->addDay();
            }
        } elseif ($statusValidasi === 'ditolak') {
            while ($tanggalBerjalan->lte($selesai)) {
                Absensi::where('user_id', $cuti->user_id)
                    ->where('tanggal', $tanggalBerjalan->format('Y-m-d'))
                    ->where('status_kehadiran', 'cuti')
                    ->delete();
                $tanggalBerjalan->addDay();
            }
        }

        return ['success' => true, 'message' => 'Validasi berhasil diproses.'];
    }
}