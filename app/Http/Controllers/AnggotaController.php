<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RealisasiKm;
use App\Models\TargetKm; 
use Illuminate\Support\Facades\Auth;

class AnggotaController extends Controller
{
    // 1. FUNGSI UNTUK MENAMPILKAN DASHBOARD DINAMIS
    public function dashboard()
    {
        // Ambil semua tugas milik dosen yang login
        $realisasis = RealisasiKm::join('target_km', 'realisasi_km.id_target', '=', 'target_km.id_target')
                        ->where('realisasi_km.id_dosen', Auth::user()->id_dosen)
                        ->get();

        $totalTasks = $realisasis->count();
        $totalPercentage = 0;

        // Hitung rata-rata persentase penyelesaian
        if ($totalTasks > 0) {
            foreach ($realisasis as $r) {
                // Cegah pembagian dengan nol jika targetnya 0
                $targetVal = $r->target > 0 ? $r->target : 1; 
                $percent = ($r->realisasi / $targetVal) * 100;
                
                // Batasi maksimal 100% per tugas
                if ($percent > 100) {
                    $percent = 100; 
                }
                $totalPercentage += $percent;
            }
            $averagePercentage = round($totalPercentage / $totalTasks);
        } else {
            $averagePercentage = 0;
        }

        return view('anggota.dashboard', compact('averagePercentage', 'totalTasks'));
    }

    // 2. FUNGSI UNTUK MENAMPILKAN TABEL REALISASI
    public function indexRealisasi()
    {
        $realisasis = RealisasiKm::join('target_km', 'realisasi_km.id_target', '=', 'target_km.id_target')
                        ->where('realisasi_km.id_dosen', Auth::user()->id_dosen)
                        ->get();

        return view('anggota.realisasi', compact('realisasis'));
    }

    // 3. FUNGSI UNTUK MENAMPILKAN FORM EDIT
    public function editRealisasi($id)
    {
        $realisasi = RealisasiKm::join('target_km', 'realisasi_km.id_target', '=', 'target_km.id_target')
                        ->where('realisasi_km.id_realisasi', $id)
                        ->firstOrFail();
                        
        return view('anggota.realisasi_edit', compact('realisasi'));
    }

    // 4. FUNGSI UNTUK MENYIMPAN PROGRESS KE DATABASE
    public function updateRealisasi(Request $request, $id)
    {
        $request->validate([
            'realisasi' => 'required|integer|min:0'
        ]);

        $realisasi = RealisasiKm::findOrFail($id);
        $realisasi->realisasi = $request->realisasi;

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