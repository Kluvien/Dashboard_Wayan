<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RealisasiKm;
use App\Models\TargetKm; 
use Illuminate\Support\Facades\Auth;

class AnggotaController extends Controller
{
    // 1. Menampilkan daftar realisasi di tabel
    public function indexRealisasi()
    {
        $realisasis = RealisasiKm::join('target_km', 'realisasi_km.id_target', '=', 'target_km.id_target')
                        ->where('realisasi_km.id_dosen', Auth::user()->id_dosen)
                        ->get();

        return view('anggota.realisasi', compact('realisasis'));
    }

    // 2. Menampilkan form edit realisasi
    public function editRealisasi($id)
    {
        $realisasi = RealisasiKm::join('target_km', 'realisasi_km.id_target', '=', 'target_km.id_target')
                        ->where('realisasi_km.id_realisasi', $id)
                        ->firstOrFail();
                        
        return view('anggota.realisasi_edit', compact('realisasi'));
    }

    // 3. Memproses data inputan realisasi baru dari form ke database
    public function updateRealisasi(Request $request, $id)
    {
        $request->validate([
            'realisasi' => 'required|integer|min:0'
        ]);

        $realisasi = RealisasiKm::findOrFail($id);
        $realisasi->realisasi = $request->realisasi;

        // Logika Otomatis: Cek apakah capaian sudah memenuhi target
        $target = TargetKm::findOrFail($realisasi->id_target);
        
        if ($request->realisasi >= $target->target) {
            $realisasi->status_realisasi = 'Tercapai';
        } else {
            $realisasi->status_realisasi = 'Belum Tercapai';
        }

        $realisasi->save();

        return redirect('/anggota/realisasi-km')->with('success', 'Progress capaian berhasil diupdate!');
    }
}