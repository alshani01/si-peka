<?php

namespace App\Http\Controllers;

use App\Models\PenilaianPerilaku;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AtasanPenilaianController extends Controller
{
    public function index(Request $request)
    {
        $atasanId = Auth::id();
        $bulan = (int) ($request->bulan ?? date('m'));
        $tahun = (int) ($request->tahun ?? date('Y'));
        $search = $request->search ?? null;
        $status = $request->status ?? 'semua';

        // Ambil data bawahan milik atasan ini
        $queryBawahan = User::where('atasan_id', $atasanId)->orderBy('name', 'asc');

        // Filter Pencarian Nama / ID PJLP
        if (!empty($search)) {
            $queryBawahan->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('id_pjlp', 'like', "%{$search}%");
            });
        }

        // Filter Berdasarkan Status Penilaian (Sudah / Belum Dinilai pada bulan & tahun terpilih)
        if ($status === 'sudah') {
            $queryBawahan->whereHas('penilaianPerilaku', function ($q) use ($bulan, $tahun) {
                $q->where('bulan', $bulan)->where('tahun', $tahun);
            });
        } elseif ($status === 'belum') {
            $queryBawahan->whereDoesntHave('penilaianPerilaku', function ($q) use ($bulan, $tahun) {
                $q->where('bulan', $bulan)->where('tahun', $tahun);
            });
        }

        $bawahans = $queryBawahan->paginate(10)->withQueryString();

        // Ambil nilai yang sudah ada di database untuk bulan dan tahun terpilih
        $bawahanIds = $bawahans->pluck('id');
        $penilaians = PenilaianPerilaku::whereIn('user_id', $bawahanIds)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get()
            ->keyBy('user_id');

        return view('atasan.penilaian.index', compact('bawahans', 'penilaians', 'bulan', 'tahun', 'search', 'status'));
    }

    public function storeSingle(Request $request)
    {
        $request->validate([
            'user_id'              => 'required|exists:users,id',
            'bulan'                => 'required|integer|between:1,12',
            'tahun'                => 'required|integer',
            'nilai_kehadiran'      => 'required|numeric|between:0,100',
            'nilai_tanggung_jawab' => 'required|numeric|between:0,100',
            'nilai_kepatuhan'      => 'required|numeric|between:0,100',
        ]);

        $atasanId = Auth::id();

        // Verifikasi bahwa bawahan milik atasan ini
        $targetUser = User::where('id', $request->user_id)
            ->where('atasan_id', $atasanId)
            ->firstOrFail();

        $rataRata = round(($request->nilai_kehadiran + $request->nilai_tanggung_jawab + $request->nilai_kepatuhan) / 3, 2);
        $kategori = $this->getKategori($rataRata);

        $penilaian = PenilaianPerilaku::updateOrCreate(
            [
                'user_id' => $targetUser->id,
                'bulan'   => $request->bulan,
                'tahun'   => $request->tahun,
            ],
            [
                'penilai_id'           => $atasanId,
                'nilai_kehadiran'      => $request->nilai_kehadiran,
                'nilai_tanggung_jawab' => $request->nilai_tanggung_jawab,
                'nilai_kepatuhan'      => $request->nilai_kepatuhan,
                'rata_rata'            => $rataRata,
                'kategori'             => $kategori,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Penilaian berhasil disimpan.',
            'data'    => $penilaian,
        ]);
    }

    public function storeBulk(Request $request)
    {
        $request->validate([
            'bulan'                             => 'required|integer|between:1,12',
            'tahun'                             => 'required|integer',
            'penilaians'                        => 'required|array',
            'penilaians.*.user_id'              => 'required|exists:users,id',
            'penilaians.*.nilai_kehadiran'      => 'required|numeric|between:0,100',
            'penilaians.*.nilai_tanggung_jawab' => 'required|numeric|between:0,100',
            'penilaians.*.nilai_kepatuhan'      => 'required|numeric|between:0,100',
        ]);

        $atasanId = Auth::id();
        $savedCount = 0;

        foreach ($request->penilaians as $item) {
            $isSubordinate = User::where('id', $item['user_id'])->where('atasan_id', $atasanId)->exists();
            if (!$isSubordinate) continue;

            $rataRata = round(($item['nilai_kehadiran'] + $item['nilai_tanggung_jawab'] + $item['nilai_kepatuhan']) / 3, 2);
            $kategori = $this->getKategori($rataRata);

            PenilaianPerilaku::updateOrCreate(
                [
                    'user_id' => $item['user_id'],
                    'bulan'   => $request->bulan,
                    'tahun'   => $request->tahun,
                ],
                [
                    'penilai_id'           => $atasanId,
                    'nilai_kehadiran'      => $item['nilai_kehadiran'],
                    'nilai_tanggung_jawab' => $item['nilai_tanggung_jawab'],
                    'nilai_kepatuhan'      => $item['nilai_kepatuhan'],
                    'rata_rata'            => $rataRata,
                    'kategori'             => $kategori,
                ]
            );
            $savedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$savedCount} penilaian pegawai berhasil disimpan sekaligus.",
        ]);
    }

    private function getKategori($rataRata)
    {
        if ($rataRata >= 90) return 'Sangat Baik';
        if ($rataRata >= 76) return 'Baik';
        if ($rataRata >= 61) return 'Cukup';
        return 'Kurang';
    }
}