<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\LaporanKinerja;
use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\PenilaianPerilaku;
use App\Models\User;

class NavigationComposer
{
    public function compose(View $view)
    {
        $user = Auth::user();
        if (!$user) return;

        $pendingLaporanCount = 0;
        $pendingAbsensiCount = 0;
        $pendingCutiCount = 0;
        $pendingPenilaianCount = 0;

        $bulanIni = (int) date('m');
        $tahunIni = (int) date('Y');

        if ($user->role === 'atasan') {
            $bawahanIds = User::where('atasan_id', $user->id)->pluck('id');
            $pendingLaporanCount = LaporanKinerja::whereIn('user_id', $bawahanIds)->where('status_validasi', 'pending')->count();
            $pendingAbsensiCount = Absensi::whereIn('user_id', $bawahanIds)->where('status_klarifikasi', 'pending')->count();
            $pendingCutiCount = Cuti::whereIn('user_id', $bawahanIds)->where('status_validasi', 'pending')->count();

            if (class_exists(PenilaianPerilaku::class)) {
                $pendingPenilaianCount = User::where('atasan_id', $user->id)
                    ->whereDoesntHave('penilaianPerilaku', fn($q) => $q->where('bulan', $bulanIni)->where('tahun', $tahunIni))
                    ->count();
            }
        } elseif ($user->role === 'admin') {
            $pendingLaporanCount = LaporanKinerja::where('status_validasi', 'pending')->count();
            $pendingAbsensiCount = Absensi::where('status_klarifikasi', 'pending')->count();
            $pendingCutiCount = Cuti::where('status_validasi', 'pending')->count();
        } elseif ($user->role === 'pjlp') {
            $pendingLaporanCount = LaporanKinerja::where('user_id', $user->id)->where('status_validasi', 'ditolak')->count();
            $pendingAbsensiCount = Absensi::where('user_id', $user->id)->where('status_klarifikasi', 'ditolak')->count();
            $pendingCutiCount = Cuti::where('user_id', $user->id)->where('status_validasi', 'ditolak')->count();
        }

        $totalNotificationCount = $pendingLaporanCount + $pendingAbsensiCount + $pendingCutiCount + $pendingPenilaianCount;

        $view->with([
            'pendingLaporanCount'   => $pendingLaporanCount,
            'pendingAbsensiCount'   => $pendingAbsensiCount,
            'pendingCutiCount'      => $pendingCutiCount,
            'pendingPenilaianCount' => $pendingPenilaianCount,
            'totalNotificationCount'=> $totalNotificationCount,
        ]);
    }
}