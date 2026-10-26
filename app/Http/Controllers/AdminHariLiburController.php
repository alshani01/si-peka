<?php

namespace App\Http\Controllers;

use App\Models\HariLibur;
use Illuminate\Http\Request;

class AdminHariLiburController extends Controller
{
    public function index()
    {
        $liburs = HariLibur::orderBy('tanggal', 'desc')->get();
        return view('admin.hari_libur.index', compact('liburs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:hari_liburs,tanggal',
            'keterangan' => 'required|string|max:255',
        ], [
            'tanggal.unique' => 'Tanggal libur ini sudah ada di database.'
        ]);

        HariLibur::create([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        HariLibur::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Hari libur berhasil dihapus.');
    }
}