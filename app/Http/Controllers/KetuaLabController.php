<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TargetKm;
use App\Models\User;
use App\Models\RealisasiKm;

class KetuaLabController extends Controller
{
    public function dashboard()
    {
        // Menghitung jumlah dosen yang jabatannya Anggota
        $totalAnggota = User::where('role', 'Anggota')->count();
        
        // Menghitung total target KM yang sudah dibuat oleh Ketua KK
        $totalTarget = TargetKm::count();

        return view('ketualab.dashboard', compact('totalAnggota', 'totalTarget'));
    }

    public function penurunanKm()
    {
        $targets = TargetKm::all();
        return view('ketualab.penurunan', compact('targets'));
    }

    // Menampilkan form pilih dosen
    public function createPlot($id)
    {
        $target = TargetKm::findOrFail($id);
        // Ambil semua user yang role-nya 'Anggota'
        $anggotas = User::where('role', 'Anggota')->get(); 
        
        return view('ketualab.plot_create', compact('target', 'anggotas'));
    }

    // Menyimpan hasil plot ke database realisasi
    public function storePlot(Request $request, $id)
    {
        $request->validate([
            'id_dosen' => 'required'
        ]);

        RealisasiKm::create([
            'id_target' => $id,
            'id_dosen' => $request->id_dosen,
            'realisasi' => 0, 
            'status_realisasi' => 'Belum Tercapai'
        ]);

        return redirect('/ketualab/penurunan-km')->with('success', 'Target KM berhasil didistribusikan ke Anggota!');
    }
}