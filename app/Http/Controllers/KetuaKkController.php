<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaboratoriumRiset;
use App\Models\User;
use App\Models\RealisasiKm;
use App\Models\TargetKm;

class KetuaKkController extends Controller
{
    public function dashboard()
    {
        // 1. Hitung Total Laboratorium (dari tabel laboratorium_riset)
        $totalLab = LaboratoriumRiset::count();

        // 2. Hitung Total Dosen Anggota
        $totalDosen = User::where('role', 'Anggota')->count();

        // 3. Hitung Rata-rata Capaian KM Keseluruhan
        $realisasis = RealisasiKm::join('target_km', 'realisasi_km.id_target', '=', 'target_km.id_target')->get();
        
        $totalTasks = $realisasis->count();
        $totalPercentage = 0;

        if ($totalTasks > 0) {
            foreach ($realisasis as $r) {
                $targetVal = $r->target > 0 ? $r->target : 1; 
                $percent = ($r->realisasi / $targetVal) * 100;
                
                if ($percent > 100) {
                    $percent = 100; 
                }
                $totalPercentage += $percent;
            }
            $rataCapaian = round($totalPercentage / $totalTasks);
        } else {
            $rataCapaian = 0;
        }

        return view('ketuakk.dashboard', compact('totalLab', 'totalDosen', 'rataCapaian'));
    }
}