<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class KetuaKkController extends Controller
{
    public function dashboard()
    {
        $tahun = now()->year;

        $kategoriDefault = [
            'Pendidikan',
            'Penelitian',
            'Publikasi',
            'Pengabdian',
            'Penunjang',
        ];

        $totalLab = DB::table('laboratorium_riset')->count();
        $totalDosen = DB::table('dosen')->count();

        $totalTargetKm = DB::table('target_km')
            ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
            ->where('kontrak_manajemen.tahun_km', $tahun)
            ->where('kontrak_manajemen.status_km', 'Aktif')
            ->sum('target');

        $totalRealisasiKm = DB::table('aktivitas_km')->count();

        $rataCapaian = $totalTargetKm > 0
            ? round(($totalRealisasiKm / $totalTargetKm) * 100)
            : 0;

        $targetPerKategori = DB::table('target_km')
            ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
            ->select('target_km.indikator', DB::raw('SUM(target_km.target) as total_target'))
            ->where('kontrak_manajemen.tahun_km', $tahun)
            ->where('kontrak_manajemen.status_km', 'Aktif')
            ->groupBy('target_km.indikator')
            ->pluck('total_target', 'indikator');

        $realisasiPerKategori = DB::table('aktivitas_km')
            ->select('kategori_km', DB::raw('COUNT(*) as total_realisasi'))
            ->groupBy('kategori_km')
            ->pluck('total_realisasi', 'kategori_km');

        $rekapKategori = [];

        foreach ($kategoriDefault as $kategori) {
            $target = $targetPerKategori[$kategori] ?? 0;
            $realisasi = $realisasiPerKategori[$kategori] ?? 0;
            $persentase = $target > 0 ? round(($realisasi / $target) * 100) : 0;

            $rekapKategori[] = [
                'kategori' => $kategori,
                'target' => $target,
                'realisasi' => $realisasi,
                'persentase' => min($persentase, 100),
                'status' => $target > 0 && $realisasi >= $target ? 'Tercapai' : 'Belum Tercapai',
            ];
        }

        $labRiset = DB::table('laboratorium_riset')
            ->orderBy('id_lab')
            ->get();

        $rekapLab = [];

        foreach ($labRiset as $lab) {
            $dosenIds = DB::table('dosen')
                ->where('id_lab', $lab->id_lab)
                ->pluck('id_dosen');

            $targetLab = 0;

            if ($dosenIds->count() > 0) {
                $targetLab = DB::table('target_km')
                    ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                    ->whereIn('kontrak_manajemen.id_dosen', $dosenIds)
                    ->where('kontrak_manajemen.tahun_km', $tahun)
                    ->where('kontrak_manajemen.status_km', 'Aktif')
                    ->sum('target');
            }

            $realisasiLab = DB::table('aktivitas_km')
                ->where('id_lab', $lab->id_lab)
                ->count();

            $persentaseLab = $targetLab > 0
                ? round(($realisasiLab / $targetLab) * 100)
                : 0;

            $rekapLab[] = [
                'id_lab' => $lab->id_lab,
                'nama_lab' => $lab->nama_lab,
                'jumlah_dosen' => $dosenIds->count(),
                'target' => $targetLab,
                'realisasi' => $realisasiLab,
                'persentase' => min($persentaseLab, 100),
                'status' => $targetLab > 0 && $realisasiLab >= $targetLab ? 'Tercapai' : 'Belum Tercapai',
            ];
        }

        $kontrakMasuk = DB::table('kontrak_manajemen')
            ->leftJoin('dosen', 'kontrak_manajemen.id_dosen', '=', 'dosen.id_dosen')
            ->leftJoin('laboratorium_riset', 'dosen.id_lab', '=', 'laboratorium_riset.id_lab')
            ->select(
                'kontrak_manajemen.id_km',
                'kontrak_manajemen.tahun_km',
                'kontrak_manajemen.status_km',
                'dosen.nama_dosen',
                'laboratorium_riset.nama_lab',
                'kontrak_manajemen.created_at'
            )
            ->orderBy('kontrak_manajemen.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('ketuakk.dashboard', compact(
            'tahun',
            'totalLab',
            'totalDosen',
            'totalTargetKm',
            'totalRealisasiKm',
            'rataCapaian',
            'rekapKategori',
            'rekapLab',
            'kontrakMasuk'
        ));
    }
}