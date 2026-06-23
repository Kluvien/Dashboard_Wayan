<?php

namespace App\Http\Controllers;

use App\Models\RealisasiKm;
use App\Models\TargetKm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnggotaController extends Controller
{
    // 1. FUNGSI UNTUK MENAMPILKAN DASHBOARD DINAMIS
    public function dashboard()
    {
        /** @var User $user */
        $user = auth()->user();

        $tahun = now()->year;
        $idUser = $user->id_user;
        $idDosen = $user->id_dosen;

        $dosen = null;

        if ($idDosen) {
            $dosen = DB::table('dosen')
                ->leftJoin('laboratorium_riset', 'dosen.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('dosen.id_dosen', $idDosen)
                ->select(
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'laboratorium_riset.nama_lab'
                )
                ->first();
        }

        $kategoriDefault = [
            'Pendidikan',
            'Penelitian',
            'Publikasi',
            'Pengabdian',
            'Penunjang',
        ];

        $targets = DB::table('target_km')
            ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
            ->where('kontrak_manajemen.id_dosen', $idDosen)
            ->where('kontrak_manajemen.tahun_km', $tahun)
            ->where('kontrak_manajemen.status_km', 'Aktif')
            ->pluck('target', 'indikator')
            ->toArray();

        $aktivitasPerKategori = DB::table('aktivitas_km')
            ->select('kategori_km', DB::raw('COUNT(*) as total'))
            ->where('id_user', $idUser)
            ->groupBy('kategori_km')
            ->pluck('total', 'kategori_km');

        $progress = [];

        foreach ($kategoriDefault as $kategori) {
            $target = $targets[$kategori] ?? 0;
            $realisasi = $aktivitasPerKategori[$kategori] ?? 0;
            $persentase = $target > 0 ? round(($realisasi / $target) * 100) : 0;

            $progress[] = [
                'kategori' => $kategori,
                'target' => $target,
                'realisasi' => $realisasi,
                'persentase' => min($persentase, 100),
                'status' => $target > 0 && $realisasi >= $target ? 'Tercapai' : 'Belum Tercapai',
            ];
        }

        $totalTarget = array_sum($targets);
        $totalRealisasi = array_sum($aktivitasPerKategori->toArray());

        $persentaseTotal = $totalTarget > 0
            ? round(($totalRealisasi / $totalTarget) * 100)
            : 0;

        $kategoriTerbaik = collect($progress)
            ->sortByDesc('persentase')
            ->first();

        $aktivitasTerbaru = DB::table('aktivitas_km')
            ->where('id_user', $idUser)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('anggota.dashboard', compact(
            'tahun',
            'user',
            'dosen',
            'progress',
            'totalTarget',
            'totalRealisasi',
            'persentaseTotal',
            'kategoriTerbaik',
            'aktivitasTerbaru'
        ));
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
            'realisasi' => 'required|integer|min:0',
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
