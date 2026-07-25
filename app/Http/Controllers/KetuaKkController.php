<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class KetuaKkController extends Controller
{
    public function dashboard()
    {
        $tahun = now()->year;

        $kategoriDefault = [
            'Penelitian',
            'Publikasi',
            'Pengabdian',
            'Penunjang',
        ];

        $userLogin = auth()->user();

        $ketuaKk = DB::table('dosen')
            ->where('id_dosen', $userLogin->id_dosen)
            ->first();

        abort_unless($ketuaKk, 403, 'Data Ketua KK tidak ditemukan.');

        $idKk = (int) $ketuaKk->id_kk;

        /*
        |--------------------------------------------------------------------------
        | Lab dan anggota pada Kelompok Keahlian
        |--------------------------------------------------------------------------
        */
        $labs = DB::table('laboratorium_riset')
            ->where('id_kk', $idKk)
            ->orderBy('id_lab')
            ->get();

        $idLabList = $labs
            ->pluck('id_lab')
            ->map(fn ($idLab) => (int) $idLab)
            ->values();

        $anggotaRows = DB::table('users as u')
            ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
            ->join('laboratorium_riset as lr', 'u.id_lab', '=', 'lr.id_lab')
            ->whereIn('u.role', ['Anggota', 'anggota'])
            ->where('lr.id_kk', $idKk)
            ->select(
                'u.id_user',
                'u.username',
                'u.id_lab',
                'd.nama_dosen',
                'd.nidn',
                'd.jad',
                'lr.nama_lab'
            )
            ->orderBy('lr.nama_lab')
            ->orderBy('d.nama_dosen')
            ->get();

        $jumlahLab = $labs->count();
        $jumlahAnggotaKk = $anggotaRows->count();

        /*
        | Variabel lama dipertahankan agar tidak merusak view lain yang
        | sebelumnya memakai nama $jumlahAnggota.
        */
        $jumlahAnggota = $jumlahAnggotaKk;

        /*
        |--------------------------------------------------------------------------
        | Target KM utama Ketua KK
        |--------------------------------------------------------------------------
        */
        $targetKmRows = DB::table('target_km')
            ->join(
                'kontrak_manajemen',
                'target_km.id_km',
                '=',
                'kontrak_manajemen.id_km'
            )
            ->select(
                'target_km.id_target',
                'target_km.kategori_km',
                'target_km.indikator',
                'target_km.target'
            )
            ->where('kontrak_manajemen.id_dosen', $userLogin->id_dosen)
            ->where('kontrak_manajemen.tahun_km', $tahun)
            ->orderBy('target_km.kategori_km')
            ->orderBy('target_km.indikator')
            ->get();

        $totalTargetKm = (int) $targetKmRows->sum('target');

        /*
        |--------------------------------------------------------------------------
        | Jumlah KM yang sudah diturunkan Ketua KK ke Lab Riset
        |--------------------------------------------------------------------------
        */
        $diturunkanPerKategori = DB::table('km_lab as kl')
            ->join('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
            ->join(
                'kontrak_manajemen as km',
                'tk.id_km',
                '=',
                'km.id_km'
            )
            ->join(
                'laboratorium_riset as lr',
                'kl.id_lab',
                '=',
                'lr.id_lab'
            )
            ->where('lr.id_kk', $idKk)
            ->where('km.id_dosen', $userLogin->id_dosen)
            ->where('km.tahun_km', $tahun)
            ->where('kl.status_km', 'Aktif')
            ->select(
                'tk.kategori_km',
                DB::raw('COALESCE(SUM(kl.jumlah_km), 0) as total_diturunkan')
            )
            ->groupBy('tk.kategori_km')
            ->pluck('total_diturunkan', 'kategori_km');

        /*
        |--------------------------------------------------------------------------
        | Realisasi KM Accepted
        |--------------------------------------------------------------------------
        */
        $realisasiRows = DB::table('aktivitas_km')
            ->join(
                'km_anggota',
                'aktivitas_km.id_km_anggota',
                '=',
                'km_anggota.id_km_anggota'
            )
            ->join(
                'km_lab',
                'km_anggota.id_km_lab',
                '=',
                'km_lab.id_km_lab'
            )
            ->join(
                'target_km',
                'km_lab.id_target',
                '=',
                'target_km.id_target'
            )
            ->join(
                'kontrak_manajemen',
                'target_km.id_km',
                '=',
                'kontrak_manajemen.id_km'
            )
            ->join(
                'laboratorium_riset',
                'km_lab.id_lab',
                '=',
                'laboratorium_riset.id_lab'
            )
            ->join(
                'users',
                'aktivitas_km.id_user',
                '=',
                'users.id_user'
            )
            ->select(
                'aktivitas_km.id_aktivitas',
                'aktivitas_km.id_user',
                'km_lab.id_lab',
                'target_km.id_target',
                'target_km.kategori_km',
                'target_km.indikator'
            )
            ->where('laboratorium_riset.id_kk', $idKk)
            ->where('kontrak_manajemen.id_dosen', $userLogin->id_dosen)
            ->where('kontrak_manajemen.tahun_km', $tahun)
            ->where('km_lab.status_km', 'Aktif')
            ->whereIn('users.role', ['Anggota', 'anggota'])
            ->where('aktivitas_km.status_progress', 'Accepted')
            ->whereColumn('aktivitas_km.id_user', 'km_anggota.id_user')
            ->whereColumn('aktivitas_km.id_lab', 'km_lab.id_lab')
            ->get()
            ->unique('id_aktivitas')
            ->values();

        $totalRealisasiKm = $realisasiRows->count();
        $totalSisaKm = max($totalTargetKm - $totalRealisasiKm, 0);

        $persentaseRealisasi = $totalTargetKm > 0
            ? min(round(($totalRealisasiKm / $totalTargetKm) * 100, 1), 100)
            : 0;

        $chartKkLabel = ['Target KM', 'Realisasi KM', 'Sisa KM'];
        $chartKkData = [$totalTargetKm, $totalRealisasiKm, $totalSisaKm];

        /*
        |--------------------------------------------------------------------------
        | Target dan realisasi per Lab Riset
        |--------------------------------------------------------------------------
        */
        $targetLabById = DB::table('km_lab as kl')
            ->join('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
            ->join(
                'kontrak_manajemen as km',
                'tk.id_km',
                '=',
                'km.id_km'
            )
            ->join(
                'laboratorium_riset as lr',
                'kl.id_lab',
                '=',
                'lr.id_lab'
            )
            ->select(
                'kl.id_lab',
                DB::raw('COALESCE(SUM(kl.jumlah_km), 0) as total_target')
            )
            ->where('lr.id_kk', $idKk)
            ->where('km.id_dosen', $userLogin->id_dosen)
            ->where('km.tahun_km', $tahun)
            ->where('kl.status_km', 'Aktif')
            ->groupBy('kl.id_lab')
            ->get()
            ->mapWithKeys(fn ($row) => [
                (int) $row->id_lab => (int) $row->total_target,
            ]);

        $realisasiLabById = $realisasiRows
            ->groupBy('id_lab')
            ->map(fn ($rows) => $rows->count());

        $labChartLabels = [];
        $labShortLabels = [];
        $labTargets = [];
        $labRealisasi = [];
        $labAchievementPercentages = [];
        $rekapLab = [];
        $jumlahLabSelesai = 0;

        foreach ($labs as $lab) {
            $targetLab = (int) ($targetLabById[$lab->id_lab] ?? 0);
            $realisasiLab = (int) ($realisasiLabById[$lab->id_lab] ?? 0);
            $sisaLab = max($targetLab - $realisasiLab, 0);

            $persentaseLab = $targetLab > 0
                ? min(round(($realisasiLab / $targetLab) * 100, 1), 100)
                : 0;

            if ($targetLab > 0 && $realisasiLab >= $targetLab) {
                $jumlahLabSelesai++;
            }

            $namaSingkat = $lab->nama_lab;

            if (str_contains($lab->nama_lab, ' - ')) {
                $namaSingkat = trim(explode(' - ', $lab->nama_lab)[0]);
            }

            $labChartLabels[] = $lab->nama_lab;
            $labShortLabels[] = $namaSingkat;
            $labTargets[] = $targetLab;
            $labRealisasi[] = $realisasiLab;
            $labAchievementPercentages[] = $persentaseLab;

            $rekapLab[] = [
                'id_lab' => (int) $lab->id_lab,
                'nama_lab' => $lab->nama_lab,
                'nama_singkat' => $namaSingkat,
                'target' => $targetLab,
                'realisasi' => $realisasiLab,
                'sisa' => $sisaLab,
                'persentase' => $persentaseLab,
            ];
        }

        $hasLabTarget = array_sum($labTargets) > 0;

        /*
        |--------------------------------------------------------------------------
        | Ringkasan anggota KK untuk tabel dashboard
        |--------------------------------------------------------------------------
        */
        $targetAnggotaById = collect();

        if ($anggotaRows->isNotEmpty()) {
            $targetAnggotaById = DB::table('km_anggota as ka')
                ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                ->join('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                ->join(
                    'kontrak_manajemen as km',
                    'tk.id_km',
                    '=',
                    'km.id_km'
                )
                ->whereIn('ka.id_user', $anggotaRows->pluck('id_user'))
                ->where('km.id_dosen', $userLogin->id_dosen)
                ->where('km.tahun_km', $tahun)
                ->where('kl.status_km', 'Aktif')
                ->select(
                    'ka.id_user',
                    DB::raw('COALESCE(SUM(ka.jumlah_km), 0) as total_target')
                )
                ->groupBy('ka.id_user')
                ->get()
                ->mapWithKeys(fn ($row) => [
                    (int) $row->id_user => (int) $row->total_target,
                ]);
        }

        $realisasiAnggotaById = $realisasiRows
            ->groupBy('id_user')
            ->map(fn ($rows) => $rows->count());

        $jumlahAnggotaSelesai = 0;
        $semuaMonitoringAnggota = [];

        foreach ($anggotaRows as $anggota) {
            $targetAnggota = (int) ($targetAnggotaById[$anggota->id_user] ?? 0);
            $realisasiAnggota = (int) ($realisasiAnggotaById[$anggota->id_user] ?? 0);
            $sisaAnggota = max($targetAnggota - $realisasiAnggota, 0);

            $progressAnggota = $targetAnggota > 0
                ? min(round(($realisasiAnggota / $targetAnggota) * 100, 1), 100)
                : 0;

            if ($targetAnggota <= 0) {
                $statusAnggota = 'Belum Ada KM';
                $statusClass = 'secondary';
            } elseif ($realisasiAnggota <= 0) {
                $statusAnggota = 'Belum Mulai';
                $statusClass = 'danger';
            } elseif ($realisasiAnggota >= $targetAnggota) {
                $statusAnggota = 'Selesai';
                $statusClass = 'success';
                $jumlahAnggotaSelesai++;
            } else {
                $statusAnggota = 'Sedang Progress';
                $statusClass = 'warning';
            }

            $semuaMonitoringAnggota[] = [
                'id_user' => (int) $anggota->id_user,
                'nama_dosen' => $anggota->nama_dosen ?? $anggota->username,
                'nama_lab' => $anggota->nama_lab ?? '-',
                'nidn' => $anggota->nidn ?? '-',
                'jad' => $anggota->jad ?? '-',
                'target' => $targetAnggota,
                'realisasi' => $realisasiAnggota,
                'sisa' => $sisaAnggota,
                'progress' => $progressAnggota,
                'status' => $statusAnggota,
                'status_class' => $statusClass,
            ];
        }

        $monitoringAnggotaRows = collect($semuaMonitoringAnggota)
            ->take(10)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Ringkasan kategori untuk card dashboard dan diagram
        |--------------------------------------------------------------------------
        */
        $realisasiPerTarget = $realisasiRows
            ->groupBy('id_target')
            ->map(fn ($rows) => $rows->count());

        $kategoriLabels = [];
        $kategoriTargets = [];
        $kategoriRealisasi = [];
        $kategoriCards = [];
        $kategoriDetailCharts = [];

        foreach ($kategoriDefault as $kategori) {
            $targetDalamKategori = $targetKmRows
                ->where('kategori_km', $kategori)
                ->values();

            $targetKategori = (int) $targetDalamKategori->sum('target');

            $realisasiKategori = (int) $realisasiRows
                ->where('kategori_km', $kategori)
                ->count();

            $diturunkanKategori = (int) ($diturunkanPerKategori[$kategori] ?? 0);
            $belumTurunKategori = max($targetKategori - $diturunkanKategori, 0);
            $sisaKategori = max($targetKategori - $realisasiKategori, 0);

            $persentaseKategori = $targetKategori > 0
                ? min(round(($realisasiKategori / $targetKategori) * 100, 1), 100)
                : 0;

            $kategoriLabels[] = $kategori;
            $kategoriTargets[] = $targetKategori;
            $kategoriRealisasi[] = $realisasiKategori;

            $kategoriCards[] = [
                'kategori' => $kategori,
                'target' => $targetKategori,
                'realisasi' => $realisasiKategori,
                'diturunkan' => $diturunkanKategori,
                'belum_turun' => $belumTurunKategori,
                'sisa' => $sisaKategori,
                'persentase' => $persentaseKategori,
            ];

            /*
            | Grafik per sub kategori hanya dibuat jika kategori tersebut
            | memang memiliki target KM.
            */
            if ($targetDalamKategori->isEmpty()) {
                continue;
            }

            $targetPerIndikator = $targetDalamKategori
                ->groupBy('indikator')
                ->map(fn ($rows) => (int) $rows->sum('target'));

            $realisasiPerIndikator = [];

            foreach ($targetDalamKategori as $target) {
                $indikator = $target->indikator;

                $realisasiPerIndikator[$indikator] =
                    ($realisasiPerIndikator[$indikator] ?? 0) +
                    (int) ($realisasiPerTarget[$target->id_target] ?? 0);
            }

            $labels = $targetPerIndikator->keys()->values();

            $kategoriDetailCharts[] = [
                'kategori' => $kategori,
                'labels' => $labels->toArray(),
                'targets' => $labels
                    ->map(fn ($label) => (int) ($targetPerIndikator[$label] ?? 0))
                    ->toArray(),
                'realisasi' => $labels
                    ->map(fn ($label) => (int) ($realisasiPerIndikator[$label] ?? 0))
                    ->toArray(),
            ];
        }

        return view('ketuakk.dashboard', compact(
            'tahun',
            'jumlahAnggota',
            'jumlahAnggotaKk',
            'jumlahLab',
            'jumlahLabSelesai',
            'jumlahAnggotaSelesai',
            'totalTargetKm',
            'totalRealisasiKm',
            'totalSisaKm',
            'persentaseRealisasi',
            'chartKkLabel',
            'chartKkData',
            'labChartLabels',
            'labShortLabels',
            'labTargets',
            'labRealisasi',
            'rekapLab',
            'hasLabTarget',
            'kategoriLabels',
            'kategoriTargets',
            'kategoriRealisasi',
            'kategoriCards',
            'labAchievementPercentages',
            'kategoriDetailCharts',
            'monitoringAnggotaRows'
        ));
    }
}
