<?php

namespace App\Http\Controllers;

use App\Models\RealisasiKm;
use App\Models\TargetKm;
use App\Models\User;
use App\Services\KmNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KetuaLabController extends Controller
{
    private const KATEGORI_DEFAULT = [
        'Penelitian',
        'Publikasi',
        'Pengabdian',
        'Penunjang',
    ];

    public function dashboard(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Filter periode dashboard
        |--------------------------------------------------------------------------
        | tahunan  : seluruh Triwulan 1-4 pada tahun terpilih
        | triwulan : hanya triwulan yang dipilih
        | semester : Semester 1 = TW1 + TW2, Semester 2 = TW3 + TW4
        */
        $tahun = (int) $request->query('tahun', now()->year);
        $mode = strtolower(trim((string) $request->query('mode', 'tahunan')));

        if (! in_array($mode, ['tahunan', 'triwulan', 'semester'], true)) {
            $mode = 'tahunan';
        }

        $triwulan = (int) $request->query('triwulan', 1);
        $semester = (int) $request->query('semester', 1);

        if (! in_array($triwulan, [1, 2, 3, 4], true)) {
            $triwulan = 1;
        }

        if (! in_array($semester, [1, 2], true)) {
            $semester = 1;
        }

        $triwulanTerpilih = match ($mode) {
            'triwulan' => [$triwulan],
            'semester' => $semester === 1 ? [1, 2] : [3, 4],
            default => [1, 2, 3, 4],
        };

        $periodeLabel = match ($mode) {
            'triwulan' => 'Triwulan ' . $triwulan . ' Tahun ' . $tahun,
            'semester' => 'Semester ' . $semester . ' Tahun ' . $tahun,
            default => 'Tahun ' . $tahun,
        };

        $periodeKeterangan = match ($mode) {
            'triwulan' => 'Data target, pembagian, dan realisasi ditampilkan khusus untuk Triwulan ' . $triwulan . '.',
            'semester' => 'Data target, pembagian, dan realisasi ditampilkan khusus untuk Semester ' . $semester . '.',
            default => 'Data target, pembagian, dan realisasi ditampilkan untuk satu tahun penuh.',
        };

        $bulanMulai = match ($mode) {
            'triwulan' => (($triwulan - 1) * 3) + 1,
            'semester' => $semester === 1 ? 1 : 7,
            default => 1,
        };

        $bulanSelesai = match ($mode) {
            'triwulan' => $bulanMulai + 2,
            'semester' => $semester === 1 ? 6 : 12,
            default => 12,
        };

        $tanggalMulaiPeriode = \Carbon\Carbon::create($tahun, $bulanMulai, 1)
            ->startOfMonth()
            ->toDateString();

        $tanggalSelesaiPeriode = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)
            ->endOfMonth()
            ->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Identitas Lab Ketua Lab
        |--------------------------------------------------------------------------
        */
        $dosenLogin = null;

        if (! empty($user->id_dosen)) {
            $dosenLogin = DB::table('dosen')
                ->where('id_dosen', $user->id_dosen)
                ->first();
        }

        $idLab = $user->id_lab ?? ($dosenLogin->id_lab ?? null);

        abort_unless($idLab, 403, 'Ketua Lab belum terhubung dengan Lab Riset.');

        $lab = DB::table('laboratorium_riset')
            ->where('id_lab', $idLab)
            ->first();

        abort_unless($lab, 403, 'Data Lab Riset tidak ditemukan.');

        $tahunOptions = collect()
            ->merge(
                DB::table('km_lab')
                    ->where('id_lab', $idLab)
                    ->select('tahun_km')
                    ->distinct()
                    ->pluck('tahun_km')
            )
            ->merge(
                DB::table('aktivitas_km')
                    ->where('id_lab', $idLab)
                    ->selectRaw("strftime('%Y', tanggal_mulai) as tahun")
                    ->pluck('tahun')
            )
            ->push(now()->year)
            ->push($tahun)
            ->map(fn ($item) => (int) $item)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        $hasStatusProgress = Schema::hasColumn('aktivitas_km', 'status_progress');
        $hasAktivitasIdKmAnggota = Schema::hasColumn('aktivitas_km', 'id_km_anggota');
        $hasKmLabSubKategori = Schema::hasColumn('km_lab', 'sub_kategori_km');
        $hasKmAnggotaTriwulan =
            Schema::hasColumn('km_anggota', 'triwulan_1') &&
            Schema::hasColumn('km_anggota', 'triwulan_2') &&
            Schema::hasColumn('km_anggota', 'triwulan_3') &&
            Schema::hasColumn('km_anggota', 'triwulan_4');

        /*
        |--------------------------------------------------------------------------
        | Ekspresi SQL sesuai periode aktif
        |--------------------------------------------------------------------------
        */
        $buatEkspresiKmLab = function (string $alias) use ($mode, $triwulanTerpilih): string {
            if ($mode === 'tahunan') {
                return "COALESCE({$alias}.jumlah_km, 0)";
            }

            return implode(' + ', array_map(
                fn ($tw) => "COALESCE({$alias}.triwulan_{$tw}, 0)",
                $triwulanTerpilih
            ));
        };

        $buatEkspresiKmAnggota = function (string $alias, string $kmLabAlias) use ($mode, $triwulanTerpilih, $hasKmAnggotaTriwulan): string {
            if ($mode === 'tahunan') {
                return "COALESCE({$alias}.jumlah_km, 0)";
            }

            if ($hasKmAnggotaTriwulan) {
                return implode(' + ', array_map(
                    fn ($tw) => "COALESCE({$alias}.triwulan_{$tw}, 0)",
                    $triwulanTerpilih
                ));
            }

            // Fallback untuk database lama yang belum menyimpan pembagian per triwulan.
            $periodeLab = implode(' + ', array_map(
                fn ($tw) => "COALESCE({$kmLabAlias}.triwulan_{$tw}, 0)",
                $triwulanTerpilih
            ));

            return "CASE WHEN COALESCE({$kmLabAlias}.jumlah_km, 0) > 0
                THEN COALESCE({$alias}.jumlah_km, 0) * (({$periodeLab}) * 1.0 / {$kmLabAlias}.jumlah_km)
                ELSE 0 END";
        };

        $kmLabPeriodeKl = $buatEkspresiKmLab('kl');
        $kmLabPeriodeKlp = $buatEkspresiKmLab('klp');
        $kmAnggotaPeriodeKa = $buatEkspresiKmAnggota('ka', 'kl');

        /*
        |--------------------------------------------------------------------------
        | Target Lab dari KK sesuai periode aktif
        |--------------------------------------------------------------------------
        */
        $kmLabRows = DB::table('km_lab as kl')
            ->where('kl.id_lab', $idLab)
            ->where('kl.tahun_km', $tahun)
            ->where('kl.status_km', 'Aktif')
            ->select(
                'kl.id_km_lab',
                'kl.kategori_km',
                $hasKmLabSubKategori
                    ? 'kl.sub_kategori_km'
                    : DB::raw("'-' as sub_kategori_km"),
                'kl.jumlah_km',
                'kl.triwulan_1',
                'kl.triwulan_2',
                'kl.triwulan_3',
                'kl.triwulan_4',
                DB::raw("({$kmLabPeriodeKl}) as jumlah_periode")
            )
            ->whereRaw("({$kmLabPeriodeKl}) > 0")
            ->orderBy('kl.kategori_km')
            ->orderBy('kl.sub_kategori_km')
            ->get();

        $targetByKategori = $kmLabRows
            ->groupBy('kategori_km')
            ->map(fn ($rows) => (int) $rows->sum('jumlah_periode'));

        /*
        |--------------------------------------------------------------------------
        | KM yang telah dibagi ke anggota sesuai periode aktif
        |--------------------------------------------------------------------------
        */
        $dibagiByKategori = DB::table('km_anggota as ka')
            ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
            ->where('kl.id_lab', $idLab)
            ->where('kl.tahun_km', $tahun)
            ->where('kl.status_km', 'Aktif')
            ->whereRaw("({$kmLabPeriodeKl}) > 0")
            ->select(
                'kl.kategori_km',
                DB::raw("COALESCE(SUM({$kmAnggotaPeriodeKa}), 0) as total_dibagi")
            )
            ->groupBy('kl.kategori_km')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->kategori_km => (int) round($row->total_dibagi),
            ]);

        /*
        |--------------------------------------------------------------------------
        | Query realisasi Accepted sesuai periode aktif
        |--------------------------------------------------------------------------
        */
        $buildRealisasiQuery = function () use (
            $hasAktivitasIdKmAnggota,
            $hasStatusProgress,
            $idLab,
            $tahun,
            $tanggalMulaiPeriode,
            $tanggalSelesaiPeriode,
            $kmLabPeriodeKl
        ) {
            $query = DB::table('aktivitas_km as ak')
                ->whereBetween('ak.tanggal_mulai', [
                    $tanggalMulaiPeriode,
                    $tanggalSelesaiPeriode,
                ]);

            if ($hasAktivitasIdKmAnggota) {
                $query
                    ->join('km_anggota as ka', 'ak.id_km_anggota', '=', 'ka.id_km_anggota')
                    ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                    ->where('kl.id_lab', $idLab)
                    ->where('kl.tahun_km', $tahun)
                    ->where('kl.status_km', 'Aktif')
                    ->whereRaw("({$kmLabPeriodeKl}) > 0");
            } else {
                $query->where('ak.id_lab', $idLab);
            }

            if ($hasStatusProgress) {
                $query->where('ak.status_progress', 'Accepted');
            }

            return $query;
        };

        $realisasiByKategori = collect();

        if ($hasAktivitasIdKmAnggota) {
            $realisasiByKategori = $buildRealisasiQuery()
                ->select(
                    'kl.kategori_km',
                    DB::raw('COUNT(DISTINCT ak.id_aktivitas) as total_realisasi')
                )
                ->groupBy('kl.kategori_km')
                ->get()
                ->mapWithKeys(fn ($row) => [
                    $row->kategori_km => (int) $row->total_realisasi,
                ]);
        } else {
            $realisasiByKategori = $buildRealisasiQuery()
                ->select(
                    'ak.kategori_km',
                    DB::raw('COUNT(ak.id_aktivitas) as total_realisasi')
                )
                ->groupBy('ak.kategori_km')
                ->get()
                ->mapWithKeys(fn ($row) => [
                    $row->kategori_km => (int) $row->total_realisasi,
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Card kategori dashboard
        |--------------------------------------------------------------------------
        */
        $kategoriCards = [];

        foreach (self::KATEGORI_DEFAULT as $kategori) {
            $target = (int) ($targetByKategori[$kategori] ?? 0);
            $realisasi = (int) ($realisasiByKategori[$kategori] ?? 0);
            $dibagi = (int) ($dibagiByKategori[$kategori] ?? 0);
            $belumDibagi = max($target - $dibagi, 0);
            $sisa = max($target - $realisasi, 0);
            $persentase = $target > 0
                ? min((int) round(($realisasi / $target) * 100), 100)
                : 0;

            $kategoriCards[] = [
                'kategori' => $kategori,
                'target' => $target,
                'realisasi' => $realisasi,
                'dibagi' => $dibagi,
                'belum_dibagi' => $belumDibagi,
                'sisa' => $sisa,
                'persentase' => $persentase,
                'detail_url' => '/ketualab/monitoring-lab?' . http_build_query([
                    'tahun' => $tahun,
                    'periode' => $mode === 'tahunan' ? 'tahun' : $mode,
                    'triwulan' => $mode === 'triwulan' ? $triwulan : null,
                    'semester' => $mode === 'semester' ? $semester : null,
                    'kategori' => $kategori,
                ]),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Anggota Lab: mendukung relasi users.id_lab maupun dosen.id_lab
        |--------------------------------------------------------------------------
        */
        $anggota = DB::table('users')
            ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
            ->where('users.role', 'Anggota')
            ->where(function ($query) use ($idLab) {
                $query
                    ->where('users.id_lab', $idLab)
                    ->orWhere('dosen.id_lab', $idLab);
            })
            ->select(
                'users.id_user',
                'users.id_dosen',
                'users.username',
                'dosen.nama_dosen',
                'dosen.nidn',
                'dosen.email',
                'dosen.jad'
            )
            ->distinct()
            ->orderBy('dosen.nama_dosen')
            ->get();

        $idUserList = $anggota->pluck('id_user')->filter()->values()->all();

        $targetAnggotaByUser = collect();
        $realisasiAnggotaByUser = collect();

        if (! empty($idUserList)) {
            $targetAnggotaByUser = DB::table('km_anggota as ka')
                ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                ->whereIn('ka.id_user', $idUserList)
                ->where('kl.id_lab', $idLab)
                ->where('kl.tahun_km', $tahun)
                ->where('kl.status_km', 'Aktif')
                ->whereRaw("({$kmLabPeriodeKl}) > 0")
                ->select(
                    'ka.id_user',
                    DB::raw("COALESCE(SUM({$kmAnggotaPeriodeKa}), 0) as total_target")
                )
                ->groupBy('ka.id_user')
                ->get()
                ->mapWithKeys(fn ($row) => [
                    $row->id_user => (int) round($row->total_target),
                ]);

            $realisasiAnggotaByUser = $buildRealisasiQuery()
                ->whereIn('ak.id_user', $idUserList)
                ->select(
                    'ak.id_user',
                    DB::raw('COUNT(DISTINCT ak.id_aktivitas) as total_realisasi')
                )
                ->groupBy('ak.id_user')
                ->get()
                ->mapWithKeys(fn ($row) => [
                    $row->id_user => (int) $row->total_realisasi,
                ]);
        }

        $monitoringAnggotaAll = [];
        $anggotaChartLabels = [];
        $anggotaAchievementPercentages = [];

        foreach ($anggota as $item) {
            $target = (int) ($targetAnggotaByUser[$item->id_user] ?? 0);
            $realisasi = (int) ($realisasiAnggotaByUser[$item->id_user] ?? 0);
            $sisa = max($target - $realisasi, 0);
            $progress = $target > 0
                ? min((int) round(($realisasi / $target) * 100), 100)
                : 0;

            if ($target <= 0) {
                $status = 'Belum Ada KM';
                $statusClass = 'secondary';
            } elseif ($realisasi <= 0) {
                $status = 'Belum Mulai';
                $statusClass = 'danger';
            } elseif ($realisasi >= $target) {
                $status = 'Tercapai';
                $statusClass = 'success';
            } else {
                $status = 'Sedang Berjalan';
                $statusClass = 'warning';
            }

            $namaAnggota = $item->nama_dosen ?? $item->username ?? '-';
            $namaPotong = trim($namaAnggota);
            $namaBagian = preg_split('/\s+/', $namaPotong);
            $namaDiagram = count($namaBagian) > 2
                ? $namaBagian[0] . ' ' . end($namaBagian)
                : $namaPotong;

            $row = [
                'id_user' => $item->id_user,
                'nama_dosen' => $namaAnggota,
                'username' => $item->username ?? '-',
                'nidn' => $item->nidn ?? '-',
                'email' => $item->email ?? '-',
                'jad' => $item->jad ?? 'AA',
                'target' => $target,
                'realisasi' => $realisasi,
                'sisa' => $sisa,
                'progress' => $progress,
                'status' => $status,
                'status_class' => $statusClass,
            ];

            $monitoringAnggotaAll[] = $row;
            $anggotaChartLabels[] = $namaDiagram;
            $anggotaAchievementPercentages[] = $progress;
        }

        $monitoringAnggotaRows = collect($monitoringAnggotaAll)
            ->take(10)
            ->values();

        $jumlahAnggota = count($monitoringAnggotaAll);
        $jumlahAnggotaSelesai = collect($monitoringAnggotaAll)
            ->where('status', 'Tercapai')
            ->count();
        $jumlahAnggotaBerjalan = collect($monitoringAnggotaAll)
            ->where('status', 'Sedang Berjalan')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Ringkasan Lab sesuai periode aktif
        |--------------------------------------------------------------------------
        */
        $totalTargetLab = (int) collect($kategoriCards)->sum('target');
        $totalRealisasiLab = (int) collect($kategoriCards)->sum('realisasi');
        $totalSisaLab = max($totalTargetLab - $totalRealisasiLab, 0);
        $persentaseRealisasi = $totalTargetLab > 0
            ? min(round(($totalRealisasiLab / $totalTargetLab) * 100, 1), 100)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Grafik kategori berdasarkan sub kategori sesuai periode aktif
        |--------------------------------------------------------------------------
        */
        $targetDetailMap = [];

        foreach ($kmLabRows as $row) {
            $kategori = trim((string) ($row->kategori_km ?? ''));
            $subKategori = trim((string) ($row->sub_kategori_km ?? 'Tanpa Sub Kategori'));
            $key = $kategori . '|' . $subKategori;

            $targetDetailMap[$key] = ($targetDetailMap[$key] ?? 0) + (int) $row->jumlah_periode;
        }

        $realisasiDetailMap = [];

        if ($hasAktivitasIdKmAnggota) {
            $realisasiDetailRows = $buildRealisasiQuery()
                ->select(
                    'kl.kategori_km',
                    $hasKmLabSubKategori
                        ? 'kl.sub_kategori_km'
                        : DB::raw("'-' as sub_kategori_km"),
                    DB::raw('COUNT(DISTINCT ak.id_aktivitas) as total_realisasi')
                )
                ->groupBy('kl.kategori_km', 'kl.sub_kategori_km')
                ->get();

            foreach ($realisasiDetailRows as $row) {
                $kategori = trim((string) ($row->kategori_km ?? ''));
                $subKategori = trim((string) ($row->sub_kategori_km ?? 'Tanpa Sub Kategori'));
                $realisasiDetailMap[$kategori . '|' . $subKategori] = (int) $row->total_realisasi;
            }
        }

        $kategoriDetailCharts = [];

        foreach (self::KATEGORI_DEFAULT as $kategori) {
            $labels = collect($targetDetailMap)
                ->keys()
                ->filter(fn ($key) => str_starts_with($key, $kategori . '|'))
                ->map(fn ($key) => substr($key, strlen($kategori) + 1))
                ->merge(
                    collect($realisasiDetailMap)
                        ->keys()
                        ->filter(fn ($key) => str_starts_with($key, $kategori . '|'))
                        ->map(fn ($key) => substr($key, strlen($kategori) + 1))
                )
                ->unique()
                ->values();

            if ($labels->isEmpty()) {
                $labels = collect(['Belum ada data']);
            }

            $kategoriDetailCharts[] = [
                'kategori' => $kategori,
                'labels' => $labels->all(),
                'targets' => $labels
                    ->map(fn ($sub) => (int) ($targetDetailMap[$kategori . '|' . $sub] ?? 0))
                    ->values()
                    ->all(),
                'realisasi' => $labels
                    ->map(fn ($sub) => (int) ($realisasiDetailMap[$kategori . '|' . $sub] ?? 0))
                    ->values()
                    ->all(),
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Pengajuan aktivitas yang menunggu verifikasi Ketua Lab
        |--------------------------------------------------------------------------
        | Data ini sengaja tidak dibatasi oleh filter periode dashboard agar
        | pengajuan baru dari anggota tidak terlewat saat Ketua Lab sedang
        | melihat tahun atau periode lain.
        */
        $pengajuanMenungguVerifikasi = collect();
        $jumlahMenungguVerifikasi = 0;

        if ($hasStatusProgress) {
            $queryPengajuan = DB::table('aktivitas_km as ak')
                ->join('users as u', 'ak.id_user', '=', 'u.id_user')
                ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
                ->where('ak.id_lab', $idLab)
                ->where('ak.status_progress', 'Submitted')
                ->where(function ($query) {
                    $query->where('ak.kategori_km', '!=', 'Pendidikan')
                        ->orWhereNull('ak.kategori_km');
                })
                ->select(
                    'ak.id_aktivitas',
                    'ak.id_user',
                    'ak.kategori_km',
                    'ak.sub_kategori_km',
                    'ak.judul_aktivitas',
                    'ak.diajukan_pada',
                    'ak.created_at',
                    'ak.updated_at',
                    DB::raw("COALESCE(NULLIF(d.nama_dosen, ''), u.username) as nama_anggota"),
                    DB::raw("COALESCE(NULLIF(d.nidn, ''), '-') as nidn")
                );

            $jumlahMenungguVerifikasi = (clone $queryPengajuan)->count();

            $pengajuanMenungguVerifikasi = $queryPengajuan
                ->orderByDesc('ak.diajukan_pada')
                ->orderByDesc('ak.updated_at')
                ->limit(6)
                ->get()
                ->map(function ($item) use ($tahun, $mode, $triwulan, $semester) {
                    $item->detail_url = route('ketualab.aktivitas-km.detail', [
                        'id' => $item->id_aktivitas,
                        'from' => 'dashboard',
                        'tahun' => $tahun,
                        'mode' => $mode,
                        'triwulan' => $triwulan,
                        'semester' => $semester,
                    ]);

                    return $item;
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Riwayat aksi verifikasi terbaru
        |--------------------------------------------------------------------------
        | Riwayat memakai tabel audit khusus agar keputusan sebelumnya tetap
        | tersimpan meskipun anggota memperbaiki lalu mengajukan ulang aktivitas.
        */
        $riwayatVerifikasi = collect();

        if (Schema::hasTable('riwayat_verifikasi_aktivitas_km')) {
            $riwayatVerifikasi = DB::table('riwayat_verifikasi_aktivitas_km as rv')
                ->join('aktivitas_km as ak', 'rv.id_aktivitas', '=', 'ak.id_aktivitas')
                ->join('users as ua', 'rv.id_user_anggota', '=', 'ua.id_user')
                ->leftJoin('dosen as da', 'ua.id_dosen', '=', 'da.id_dosen')
                ->leftJoin('users as uv', 'rv.id_verifikator', '=', 'uv.id_user')
                ->leftJoin('dosen as dv', 'uv.id_dosen', '=', 'dv.id_dosen')
                ->where('rv.id_lab', $idLab)
                ->where(function ($query) {
                    $query->where('ak.kategori_km', '!=', 'Pendidikan')
                        ->orWhereNull('ak.kategori_km');
                })
                ->select(
                    'rv.id_riwayat',
                    'rv.id_aktivitas',
                    'rv.keputusan',
                    'rv.catatan_verifikasi',
                    'rv.created_at as waktu_aksi',
                    'ak.judul_aktivitas',
                    'ak.kategori_km',
                    'ak.sub_kategori_km',
                    DB::raw("COALESCE(NULLIF(da.nama_dosen, ''), ua.username) as nama_anggota"),
                    DB::raw("COALESCE(NULLIF(dv.nama_dosen, ''), uv.username, '-') as nama_verifikator")
                )
                ->orderByDesc('rv.created_at')
                ->limit(8)
                ->get()
                ->map(function ($item) use ($tahun, $mode, $triwulan, $semester) {
                    $item->detail_url = route('ketualab.aktivitas-km.detail', [
                        'id' => $item->id_aktivitas,
                        'from' => 'dashboard',
                        'tahun' => $tahun,
                        'mode' => $mode,
                        'triwulan' => $triwulan,
                        'semester' => $semester,
                    ]);

                    return $item;
                });
        }

        $filterQuery = http_build_query(array_filter([
            'tahun' => $tahun,
            'mode' => $mode,
            'triwulan' => $mode === 'triwulan' ? $triwulan : null,
            'semester' => $mode === 'semester' ? $semester : null,
        ], fn ($value) => $value !== null));

        return view('ketualab.dashboard', compact(
            'tahun',
            'tahunOptions',
            'mode',
            'triwulan',
            'semester',
            'triwulanTerpilih',
            'periodeLabel',
            'periodeKeterangan',
            'tanggalMulaiPeriode',
            'tanggalSelesaiPeriode',
            'filterQuery',
            'lab',
            'kategoriCards',
            'jumlahAnggota',
            'jumlahAnggotaSelesai',
            'jumlahAnggotaBerjalan',
            'totalTargetLab',
            'totalRealisasiLab',
            'totalSisaLab',
            'persentaseRealisasi',
            'anggotaChartLabels',
            'anggotaAchievementPercentages',
            'kategoriDetailCharts',
            'monitoringAnggotaRows',
            'jumlahMenungguVerifikasi',
            'pengajuanMenungguVerifikasi',
            'riwayatVerifikasi'
        ));
    }

    public function penurunanKm()
    {
        $targets = TargetKm::all();

        return view('ketualab.penurunan', compact('targets'));
    }

    public function createPlot($id)
    {
        $target = TargetKm::findOrFail($id);

        $anggotas = User::where('role', 'Anggota')->get();

        return view('ketualab.plot_create', compact('target', 'anggotas'));
    }

    public function storePlot(Request $request, $id)
    {
        $request->validate([
            'id_dosen' => 'required',
        ]);

        RealisasiKm::create([
            'id_target' => $id,
            'id_dosen' => $request->id_dosen,
            'realisasi' => 0,
            'status_realisasi' => 'Belum Tercapai',
        ]);

        return redirect('/ketualab/penurunan-km')
            ->with('success', 'Target KM berhasil didistribusikan ke Anggota.');
    }

    public function pembagianKmAnggota(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $dosenLogin = ! empty($user->id_dosen)
            ? DB::table('dosen')->where('id_dosen', $user->id_dosen)->first()
            : null;

        $idLab = $user->id_lab ?? ($dosenLogin->id_lab ?? null);

        abort_unless($idLab, 403, 'Lab Riset Ketua Lab tidak ditemukan.');

        $tahun = (int) $request->query('tahun', now()->year);

        $tahunOptions = DB::table('km_lab')
            ->where('id_lab', $idLab)
            ->select('tahun_km')
            ->distinct()
            ->pluck('tahun_km')
            ->push(now()->year)
            ->push($tahun)
            ->map(fn ($item) => (int) $item)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        $lab = DB::table('laboratorium_riset')
            ->where('id_lab', $idLab)
            ->first();

        abort_unless($lab, 404, 'Data Lab Riset tidak ditemukan.');

        $kmLabRows = DB::table('km_lab as kl')
            ->leftJoin('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
            ->where('kl.id_lab', $idLab)
            ->where('kl.tahun_km', $tahun)
            ->where('kl.status_km', 'Aktif')
            ->select(
                'kl.id_km_lab',
                'kl.id_target',
                'kl.kategori_km',
                'kl.sub_kategori_km',
                'kl.jumlah_km',
                'kl.triwulan_1',
                'kl.triwulan_2',
                'kl.triwulan_3',
                'kl.triwulan_4',
                'kl.created_at as tanggal_turun',
                'tk.indikator as indikator_target',
                'tk.keterangan',
                'tk.tanggal_selesai_tw1',
                'tk.tanggal_selesai_tw2',
                'tk.tanggal_selesai_tw3',
                'tk.tanggal_selesai_tw4'
            )
            ->orderBy('kl.kategori_km')
            ->orderBy('kl.sub_kategori_km')
            ->get();

        $idKmLabList = $kmLabRows
            ->pluck('id_km_lab')
            ->filter()
            ->values();

        $assignTotals = collect();

        if ($idKmLabList->isNotEmpty()) {
            $assignTotals = DB::table('km_anggota')
                ->whereIn('id_km_lab', $idKmLabList)
                ->select(
                    'id_km_lab',
                    DB::raw('COALESCE(SUM(jumlah_km), 0) as total_assign'),
                    DB::raw('COALESCE(SUM(triwulan_1), 0) as total_tw1'),
                    DB::raw('COALESCE(SUM(triwulan_2), 0) as total_tw2'),
                    DB::raw('COALESCE(SUM(triwulan_3), 0) as total_tw3'),
                    DB::raw('COALESCE(SUM(triwulan_4), 0) as total_tw4')
                )
                ->groupBy('id_km_lab')
                ->get()
                ->keyBy('id_km_lab');
        }

        $dataKmLab = $kmLabRows->map(function ($km) use ($assignTotals) {
            $assign = $assignTotals->get($km->id_km_lab);

            $km->sub_kategori_display =
                $km->sub_kategori_km
                ?? $km->indikator_target
                ?? '-';

            $km->sudah_assign = (int) ($assign->total_assign ?? 0);

            for ($triwulan = 1; $triwulan <= 4; $triwulan++) {
                $jumlahKmTw = (int) ($km->{'triwulan_' . $triwulan} ?? 0);
                $sudahAssignTw = (int) ($assign->{'total_tw' . $triwulan} ?? 0);

                $km->{'sudah_assign_tw' . $triwulan} = $sudahAssignTw;
                $km->{'sisa_tw' . $triwulan} = max($jumlahKmTw - $sudahAssignTw, 0);
            }

            $km->sisa_km = max(
                (int) $km->jumlah_km - (int) $km->sudah_assign,
                0
            );

            if ((int) $km->jumlah_km <= 0) {
                $km->status = 'Belum Ada KM';
            } elseif ((int) $km->sisa_km <= 0) {
                $km->status = 'Selesai';
            } else {
                $km->status = 'Belum Selesai';
            }

            return $km;
        });

        $anggota = DB::table('users as u')
            ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
            ->where('u.role', 'Anggota')
            ->where(function ($query) use ($idLab) {
                $query->where('u.id_lab', $idLab)
                    ->orWhere('d.id_lab', $idLab);
            })
            ->select(
                'u.id_user',
                'u.id_dosen',
                'u.username',
                'd.nama_dosen',
                'd.nidn',
                'd.email',
                'd.jad'
            )
            ->distinct()
            ->orderBy('d.nama_dosen')
            ->get();

        $riwayatAssign = DB::table('km_anggota as ka')
            ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
            ->leftJoin('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
            ->leftJoin('users as u', 'ka.id_user', '=', 'u.id_user')
            ->leftJoin('dosen as d', 'ka.id_dosen', '=', 'd.id_dosen')
            ->where('kl.id_lab', $idLab)
            ->where('kl.tahun_km', $tahun)
            ->select(
                'ka.id_km_anggota',
                'ka.jumlah_km',
                'ka.triwulan_1',
                'ka.triwulan_2',
                'ka.triwulan_3',
                'ka.triwulan_4',
                'ka.created_at',
                'kl.kategori_km',
                'kl.sub_kategori_km',
                'tk.indikator as indikator_target',
                'tk.keterangan',
                'u.username',
                'd.nama_dosen',
                'd.nidn',
                'd.email',
                'd.jad'
            )
            ->orderByDesc('ka.created_at')
            ->get();

        return view('ketualab.pembagian-km-anggota', compact(
            'lab',
            'tahun',
            'tahunOptions',
            'dataKmLab',
            'anggota',
            'riwayatAssign'
        ));
    }

    public function simpanPembagianKmAnggota(Request $request)
    {
        $validated = $request->validate([
            'id_km_lab' => 'required|exists:km_lab,id_km_lab',
            'id_user' => 'required|exists:users,id_user',
            'triwulan_1' => 'nullable|integer|min:0',
            'triwulan_2' => 'nullable|integer|min:0',
            'triwulan_3' => 'nullable|integer|min:0',
            'triwulan_4' => 'nullable|integer|min:0',
        ]);

        $pembagianTriwulan = [
            1 => (int) ($validated['triwulan_1'] ?? 0),
            2 => (int) ($validated['triwulan_2'] ?? 0),
            3 => (int) ($validated['triwulan_3'] ?? 0),
            4 => (int) ($validated['triwulan_4'] ?? 0),
        ];

        $jumlahKmBaru = array_sum($pembagianTriwulan);

        if ($jumlahKmBaru <= 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'triwulan_1' => 'Masukkan minimal satu jumlah KM pada salah satu Triwulan.',
                ]);
        }

        /** @var User $user */
        $user = auth()->user();

        $dosenLogin = ! empty($user->id_dosen)
            ? DB::table('dosen')->where('id_dosen', $user->id_dosen)->first()
            : null;

        $idLab = $user->id_lab ?? ($dosenLogin->id_lab ?? null);

        abort_unless($idLab, 403, 'Lab Riset Ketua Lab tidak ditemukan.');

        $kmLab = DB::table('km_lab')
            ->where('id_km_lab', $validated['id_km_lab'])
            ->where('id_lab', $idLab)
            ->where('status_km', 'Aktif')
            ->first();

        if (! $kmLab) {
            return redirect('/ketualab/penurunan-km')
                ->with('error', 'KM Lab tidak ditemukan atau bukan milik Lab Anda.');
        }

        $redirectUrl = '/ketualab/penurunan-km?tahun=' . (int) $kmLab->tahun_km;

        $anggota = DB::table('users as u')
            ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
            ->where('u.id_user', $validated['id_user'])
            ->where('u.role', 'Anggota')
            ->where(function ($query) use ($idLab) {
                $query->where('u.id_lab', $idLab)
                    ->orWhere('d.id_lab', $idLab);
            })
            ->select('u.id_user', 'u.id_dosen')
            ->first();

        if (! $anggota) {
            return redirect($redirectUrl)
                ->with('error', 'Anggota tidak ditemukan atau bukan anggota Lab Anda.');
        }

        $sudahAssign = DB::table('km_anggota')
            ->where('id_km_lab', $kmLab->id_km_lab)
            ->select(
                DB::raw('COALESCE(SUM(jumlah_km), 0) as total_assign'),
                DB::raw('COALESCE(SUM(triwulan_1), 0) as total_tw1'),
                DB::raw('COALESCE(SUM(triwulan_2), 0) as total_tw2'),
                DB::raw('COALESCE(SUM(triwulan_3), 0) as total_tw3'),
                DB::raw('COALESCE(SUM(triwulan_4), 0) as total_tw4')
            )
            ->first();

        $sisaTotal = max(
            (int) $kmLab->jumlah_km - (int) ($sudahAssign->total_assign ?? 0),
            0
        );

        if ($jumlahKmBaru > $sisaTotal) {
            return back()
                ->withInput()
                ->with('error', 'Jumlah KM yang dibagikan melebihi sisa KM yang tersedia.');
        }

        for ($triwulan = 1; $triwulan <= 4; $triwulan++) {
            $jumlahLabTw = (int) ($kmLab->{'triwulan_' . $triwulan} ?? 0);
            $sudahAssignTw = (int) ($sudahAssign->{'total_tw' . $triwulan} ?? 0);
            $sisaTw = max($jumlahLabTw - $sudahAssignTw, 0);

            if ($pembagianTriwulan[$triwulan] > $sisaTw) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Jumlah KM Triwulan ' . $triwulan
                        . ' melebihi sisa kuota yang tersedia. '
                        . 'Sisa Triwulan ' . $triwulan . ': ' . $sisaTw . ' KM.'
                    );
            }
        }

        $assignLama = DB::table('km_anggota')
            ->where('id_km_lab', $kmLab->id_km_lab)
            ->where('id_user', $anggota->id_user)
            ->first();

        if ($assignLama) {
            $idKmAnggota = $assignLama->id_km_anggota;

            DB::table('km_anggota')
                ->where('id_km_anggota', $idKmAnggota)
                ->update([
                    'jumlah_km' => (int) $assignLama->jumlah_km + $jumlahKmBaru,
                    'triwulan_1' => (int) $assignLama->triwulan_1 + $pembagianTriwulan[1],
                    'triwulan_2' => (int) $assignLama->triwulan_2 + $pembagianTriwulan[2],
                    'triwulan_3' => (int) $assignLama->triwulan_3 + $pembagianTriwulan[3],
                    'triwulan_4' => (int) $assignLama->triwulan_4 + $pembagianTriwulan[4],
                    'updated_at' => now(),
                ]);
        } else {
            $idKmAnggota = DB::table('km_anggota')->insertGetId([
                'id_km_lab' => $kmLab->id_km_lab,
                'id_user' => $anggota->id_user,
                'id_dosen' => $anggota->id_dosen,
                'jumlah_km' => $jumlahKmBaru,
                'triwulan_1' => $pembagianTriwulan[1],
                'triwulan_2' => $pembagianTriwulan[2],
                'triwulan_3' => $pembagianTriwulan[3],
                'triwulan_4' => $pembagianTriwulan[4],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        KmNotificationService::notifyAnggotaKmBaru(
            (int) $idKmAnggota,
            $pembagianTriwulan,
            now()->format('YmdHis') . '-' . uniqid()
        );

        return redirect($redirectUrl)
            ->with('success', 'KM berhasil dibagikan ke anggota berdasarkan Triwulan.');
    }
    private function buildTargetKey(string $kategori, string $subKategori): string
    {
        return mb_strtolower(trim($kategori)) . '|' . mb_strtolower(trim($subKategori));
    }

    private function shortPersonName(string $nama): string
    {
        $nama = trim($nama);

        if ($nama === '') {
            return '-';
        }

        $parts = preg_split('/\s+/', $nama);

        if (count($parts) <= 2) {
            return $nama;
        }

        return $parts[0] . ' ' . end($parts);
    }
}