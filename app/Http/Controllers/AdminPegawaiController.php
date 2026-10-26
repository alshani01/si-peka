<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LaporanKinerja;
use App\Models\Absensi;
use App\Models\Cuti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminPegawaiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $roleFilter = $request->input('role', 'all');
        $statusFilter = $request->input('status', 'all');

        $query = User::with('atasan');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('id_pjlp', 'like', "%{$search}%")
                  ->orWhere('jenis_pekerjaan', 'like', "%{$search}%")
                  ->orWhere('unit_kerja', 'like', "%{$search}%")
                  ->orWhere('lokasi_tugas', 'like', "%{$search}%");
            });
        }

        if ($roleFilter !== 'all' && !empty($roleFilter)) {
            if ($roleFilter === 'p3k') {
                $query->where('kategori_pegawai', 'P3K');
            } elseif ($roleFilter === 'pjlp') {
                $query->where('role', 'pjlp')->where(function($q) {
                    $q->whereNull('kategori_pegawai')
                      ->orWhere('kategori_pegawai', '!=', 'P3K');
                });
            } elseif ($roleFilter === 'atasan') {
                $query->where('role', 'atasan');
            } elseif ($roleFilter === 'pimpinan') {
                $query->whereIn('role', ['pimpinan', 'admin']);
            } else {
                $query->where('role', $roleFilter);
            }
        }

        if ($statusFilter !== 'all' && !empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        $users = $query->orderByRaw("COALESCE(urutan_hirarki, 999) ASC")
            ->orderByRaw("FIELD(role, 'admin', 'pimpinan', 'atasan', 'p3k', 'pjlp')")
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        $totalPegawai = User::where(function($q) {
            $q->whereIn('role', ['pjlp', 'p3k'])
              ->orWhere('kategori_pegawai', 'P3K');
        })->count();
        
        $totalAtasan = User::whereIn('role', ['admin', 'atasan', 'pimpinan'])->count();

        $bulanSekarang = date('m');
        $tahunSekarang = date('Y');

        $laporanPending = LaporanKinerja::where('status_validasi', 'pending')
            ->whereMonth('tanggal_tugas', $bulanSekarang)
            ->whereYear('tanggal_tugas', $tahunSekarang)
            ->count();

        $klarifikasiPending = Absensi::where('status_klarifikasi', 'pending')
            ->whereMonth('tanggal', $bulanSekarang)
            ->whereYear('tanggal', $tahunSekarang)
            ->count();

        $cutiPending = Cuti::where('status_validasi', 'pending')
            ->where(function ($q) use ($bulanSekarang, $tahunSekarang) {
                $q->whereMonth('tanggal_mulai', $bulanSekarang)->whereYear('tanggal_mulai', $tahunSekarang)
                ->orWhere(function ($q2) use ($bulanSekarang, $tahunSekarang) {
                    $q2->whereMonth('tanggal_selesai', $bulanSekarang)->whereYear('tanggal_selesai', $tahunSekarang);
                });
            })
            ->count();

        $daftarAtasan = User::whereIn('role', ['admin', 'atasan', 'pimpinan'])
            ->where('status', 'aktif')
            ->orderByRaw("COALESCE(urutan_hirarki, 999) ASC")
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.pegawai.index', compact(
            'users', 
            'totalPegawai', 
            'totalAtasan', 
            'laporanPending', 
            'klarifikasiPending',
            'cutiPending',
            'search',
            'roleFilter',
            'statusFilter',
            'daftarAtasan'
        ));
    }

    public function exportPdf(Request $request)
    {
        // Menggunakan urutan_hirarki acuan urutan pejabat
        $daftarAtasan = User::whereIn('role', ['admin', 'pimpinan', 'atasan'])
            ->orderByRaw("COALESCE(urutan_hirarki, 999) ASC")
            ->orderByRaw("FIELD(role, 'pimpinan', 'admin', 'atasan')")
            ->orderBy('name', 'asc')
            ->get();

        $daftarP3k = User::with('atasan')
            ->where('kategori_pegawai', 'P3K')
            ->orderByRaw("COALESCE(urutan_hirarki, 999) ASC")
            ->orderBy('name', 'asc')
            ->get();

        $daftarPjlp = User::with('atasan')
            ->whereNotIn('role', ['admin', 'pimpinan', 'atasan'])
            ->where(function($q) {
                $q->where('kategori_pegawai', 'PJLP')
                  ->orWhereNull('kategori_pegawai')
                  ->orWhere('kategori_pegawai', '!=', 'P3K');
            })
            ->orderByRaw("COALESCE(urutan_hirarki, 999) ASC")
            ->orderBy('name', 'asc')
            ->get();

        $pdf = Pdf::loadView('admin.pegawai.pdf', compact('daftarAtasan', 'daftarP3k', 'daftarPjlp'))
                ->setPaper('a4', 'landscape');

        return $pdf->download('LAPORAN_DAFTAR_PEGAWAI_' . date('F_Y') . '.pdf');
    }

    public function create()
    {
        $daftarAtasan = User::whereIn('role', ['admin', 'atasan', 'pimpinan'])->where('status', 'aktif')->get();
        return view('admin.pegawai.create', compact('daftarAtasan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'id_pjlp' => 'required|string|unique:users,id_pjlp',
            'password' => 'required|string|min:6',
            'role' => 'required|in:pimpinan,atasan,p3k,pjlp',
            'kategori_pegawai' => 'required|in:PNS,P3K,P3K Paruh Waktu,PJLP',
            'jenis_pekerjaan' => 'nullable|string|max:255',
            'unit_kerja' => 'nullable|string|max:255',
            'lokasi_tugas' => 'nullable|string|max:255',
            'sisa_cuti' => 'required|integer|min:0|max:100',
            'urutan_hirarki' => 'nullable|integer|min:1',
            'atasan_id' => 'nullable|required_if:role,pjlp,p3k|exists:users,id',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ], [
            'id_pjlp.required' => 'NRK / NIP / ID PJLP wajib diisi sebagai ID Login pegawai.',
            'id_pjlp.unique' => 'NRK / NIP / ID PJLP ini sudah terdaftar.',
            'sisa_cuti.required' => 'Kuota cuti awal wajib diisi.',
            'atasan_id.required_if' => 'Atasan penilai wajib dipilih untuk pegawai PJLP/P3K.'
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_profil')) {
            $fotoPath = $request->file('foto_profil')->store('foto_profil', 'public');
        }

        $isBawahan = in_array($request->role, ['pjlp', 'p3k']);

        User::create([
            'name' => $request->name,
            'id_pjlp' => $request->id_pjlp,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'kategori_pegawai' => $request->kategori_pegawai,
            'jenis_pekerjaan' => $request->jenis_pekerjaan,
            'unit_kerja' => $request->unit_kerja,
            'lokasi_tugas' => $request->lokasi_tugas,
            'sisa_cuti' => $request->sisa_cuti,
            'urutan_hirarki' => $request->urutan_hirarki,
            'atasan_id' => $isBawahan ? $request->atasan_id : null,
            'foto_profil' => $fotoPath,
            'status' => 'aktif',
        ]);

        return redirect()->route('admin.pegawai.index')->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $daftarAtasan = User::whereIn('role', ['admin', 'atasan', 'pimpinan'])->where('status', 'aktif')->where('id', '!=', $id)->get();
        
        return view('admin.pegawai.edit', compact('user', 'daftarAtasan'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'id_pjlp' => 'required|string|unique:users,id_pjlp,' . $id,
            'role' => 'required|in:pimpinan,atasan,p3k,pjlp',
            'kategori_pegawai' => 'required|in:PNS,P3K,P3K Paruh Waktu,PJLP',
            'jenis_pekerjaan' => 'nullable|string|max:255',
            'unit_kerja' => 'nullable|string|max:255',
            'lokasi_tugas' => 'nullable|string|max:255',
            'sisa_cuti' => 'required|integer|min:0|max:100',
            'urutan_hirarki' => 'nullable|integer|min:1',
            'atasan_id' => 'nullable|required_if:role,pjlp,p3k|exists:users,id|not_in:' . $id,
            'status' => 'required|in:aktif,nonaktif',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'pengganti_atasan_id' => 'nullable|exists:users,id'
        ], [
            'id_pjlp.required' => 'NRK / NIP / ID PJLP wajib diisi.',
            'atasan_id.not_in' => 'Pegawai tidak dapat menjadi atasan bagi dirinya sendiri.'
        ]);

        $isBawahan = in_array($request->role, ['pjlp', 'p3k']);

        if (in_array($user->role, ['atasan', 'pimpinan']) && ($request->status === 'nonaktif' || $isBawahan)) {
            $bawahanCount = User::where('atasan_id', $user->id)->count();
            
            if ($bawahanCount > 0) {
                if ($request->filled('pengganti_atasan_id')) {
                    User::where('atasan_id', $user->id)->update(['atasan_id' => $request->pengganti_atasan_id]);
                } else {
                    return redirect()->back()->withErrors([
                        'status' => "Gagal mengubah! Pegawai ini masih memiliki {$bawahanCount} bawahan. Anda harus memilih Atasan Pengganti terlebih dahulu."
                    ])->withInput();
                }
            }
        }

        $data = [
            'name' => $request->name,
            'id_pjlp' => $request->id_pjlp,
            'role' => $request->role,
            'kategori_pegawai' => $request->kategori_pegawai,
            'status' => $request->status,
            'jenis_pekerjaan' => $request->jenis_pekerjaan,
            'unit_kerja' => $request->unit_kerja,
            'lokasi_tugas' => $request->lokasi_tugas,
            'sisa_cuti' => $request->sisa_cuti,
            'urutan_hirarki' => $request->urutan_hirarki,
        ];

        if ($request->hasFile('foto_profil')) {
            if ($user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
                Storage::disk('public')->delete($user->foto_profil);
            }
            $data['foto_profil'] = $request->file('foto_profil')->store('foto_profil', 'public');
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($isBawahan) {
            $data['atasan_id'] = $request->atasan_id;
        } else {
            $data['atasan_id'] = null;
        }

        $user->update($data);

        return redirect()->route('admin.pegawai.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        
        $user->update([
            'password' => Hash::make('password')
        ]);

        return redirect()->back()->with('success', "Password untuk akun {$user->name} berhasil di-reset menjadi: password");
    }

    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return redirect()->back()->withErrors(['Akses ditolak: Tidak dapat menghapus akun Admin.']);
        }

        if (in_array($user->role, ['atasan', 'pimpinan'])) {
            $bawahanCount = User::where('atasan_id', $user->id)->count();
            
            if ($bawahanCount > 0) {
                if ($request->filled('pengganti_atasan_id')) {
                    User::where('atasan_id', $user->id)->update(['atasan_id' => $request->pengganti_atasan_id]);
                } else {
                    return redirect()->back()->withErrors([
                        'hapus_error' => "Gagal menghapus! Akun ini masih membawahi {$bawahanCount} pegawai. Silakan pilih 'Atasan Pengganti' pada modal hapus."
                    ]);
                }
            }
        }

        if ($user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
            Storage::disk('public')->delete($user->foto_profil);
        }

        $user->delete();

        return redirect()->route('admin.pegawai.index')->with('success', 'Akun pegawai berhasil dihapus.');
    }
}