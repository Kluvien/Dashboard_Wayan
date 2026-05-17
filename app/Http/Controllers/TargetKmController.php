<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TargetKm;
use Illuminate\Support\Facades\DB;

class TargetKmController extends Controller
{
    public function index()
    {
        $targets = TargetKm::all(); 
        return view('ketuakk.target', compact('targets'));
    }

    // Menampilkan halaman form
    public function create()
    {
        return view('ketuakk.target_create');
    }

    // Memproses data dari form ke database
    public function store(Request $request)
    {
        $request->validate([
            'indikator' => 'required',
            'target' => 'required'
        ]);

        $km = DB::table('kontrak_manajemen')->first();
        
        if (!$km) {
            $id_km = DB::table('kontrak_manajemen')->insertGetId([
                'id_dosen'   => auth()->user()->id_dosen,
                'tahun_km'   => date('Y'),
                'status_km'  => 'Draft', // <--- INI SUDAH DITAMBAHKAN AGAR TIDAK ERROR
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $id_km = $km->id_km;
        }

        TargetKm::create([
            'id_km'     => $id_km,
            'indikator' => $request->indikator,
            'target'    => $request->target
        ]);

        return redirect('/ketuakk/target-km')->with('success', 'Target KM berhasil ditambahkan!');
    }
}