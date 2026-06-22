<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TargetKm;
use App\Models\User;
use App\Models\RealisasiKm;

class KetuaLabController extends Controller
{
    public function dashboard()
        {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $tahun = now()->year;
            $idLab = $user->id_lab;

            $lab = DB::table('laboratorium_riset')
                ->where('id_lab', $idLab)
                ->first();

            $anggota = DB::table('users')
                ->where('role', 'Anggota')
                ->where('id_lab', $idLab)
                ->get();

            $jumlahAnggota = $anggota->count();

            $targetLabPerKategori = [
                'Pendidikan' => 0,
                'Penelitian' => 0,
                'Publikasi' => 0,
                'Pengabdian' => 0,
                'Penunjang' => 0,
            ];

            foreach ($anggota as $item) {
                $targets = DB::table('target_km')
                    ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                    ->where('kontrak_manajemen.id_dosen', $item->id_dosen)
                    ->where('kontrak_manajemen.tahun_km', $tahun)
                    ->where('kontrak_manajemen.status_km', 'Aktif')
                    ->pluck('target', 'indikator')
                    ->toArray();

                foreach ($targets as $kategori => $target) {
                    if (!isset($targetLabPerKategori[$kategori])) {
                        $targetLabPerKategori[$kategori] = 0;
                    }

                    $targetLabPerKategori[$kategori] += $target;
                }
            }

    $aktivitasPerKategori = DB::table('aktivitas_km')
        ->select('kategori_km', DB::raw('COUNT(*) as total'))
        ->where('id_lab', $idLab)
        ->groupBy('kategori_km')
        ->pluck('total', 'kategori_km');

    $rekapKategori = [];

    foreach ($targetLabPerKategori as $kategori => $target) {
        $realisasi = $aktivitasPerKategori[$kategori] ?? 0;
        $persentase = $target > 0 ? round(($realisasi / $target) * 100) : 0;

        $rekapKategori[] = [
            'kategori' => $kategori,
            'target' => $target,
            'realisasi' => $realisasi,
            'persentase' => min($persentase, 100),
            'status' => $target > 0 && $realisasi >= $target ? 'Tercapai' : 'Belum Tercapai',
        ];
    }

    $totalTargetLab = array_sum($targetLabPerKategori);
    $totalRealisasiLab = array_sum($aktivitasPerKategori->toArray());

    $persentaseTotal = $totalTargetLab > 0
        ? round(($totalRealisasiLab / $totalTargetLab) * 100)
        : 0;

    $aktivitasTerbaru = DB::table('aktivitas_km')
        ->leftJoin('users', 'aktivitas_km.id_user', '=', 'users.id_user')
        ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
        ->where('aktivitas_km.id_lab', $idLab)
        ->select(
            'aktivitas_km.kategori_km',
            'aktivitas_km.judul_aktivitas',
            'aktivitas_km.tanggal_mulai',
            'aktivitas_km.tanggal_selesai',
            'users.username',
            'dosen.nama_dosen'
        )
        ->orderBy('aktivitas_km.created_at', 'desc')
        ->limit(5)
        ->get();

    return view('ketualab.dashboard', compact(
        'tahun',
        'lab',
        'jumlahAnggota',
        'totalTargetLab',
        'totalRealisasiLab',
        'persentaseTotal',
        'rekapKategori',
        'aktivitasTerbaru'
    ));
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