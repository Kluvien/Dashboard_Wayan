<?php

namespace App\Http\Controllers;

use App\Models\TargetKm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TargetKmController extends Controller
{
    public function index()
    {
        $targets = TargetKm::all();

        return view('ketuakk.target', compact('targets'));
    }

    public function create()
    {
        return view('ketuakk.target_create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'indikator' => 'required',
            'target' => 'required',
        ]);

        $km = DB::table('kontrak_manajemen')->first();

        if (! $km) {
            $id_km = DB::table('kontrak_manajemen')->insertGetId([
                'id_dosen' => auth()->user()->id_dosen,
                'tahun_km' => date('Y'),
                'status_km' => 'Draft',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $id_km = $km->id_km;
        }

        TargetKm::create([
            'id_km' => $id_km,
            'indikator' => $request->indikator,
            'target' => $request->target,
        ]);

        return redirect('/ketuakk/target-km')->with('success', 'Target KM berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $target = TargetKm::findOrFail($id);

        return view('ketuakk.target_edit', compact('target'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'indikator' => 'required',
            'target' => 'required|integer',
        ]);

        $target = TargetKm::findOrFail($id);
        $target->update([
            'indikator' => $request->indikator,
            'target' => $request->target,
        ]);

        return redirect('/ketuakk/target-km')->with('success', 'Target KM berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $target = TargetKm::findOrFail($id);
        $target->delete();

        return redirect('/ketuakk/target-km')->with('success', 'Target KM berhasil dihapus!');
    }
}
