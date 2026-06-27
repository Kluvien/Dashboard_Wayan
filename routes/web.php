<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TargetKmController;
use App\Http\Controllers\KetuaLabController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\KetuaKkController;

Route::get('/', function () {
    return view('welcome'); // Halaman awal bawaan Laravel
});

// Route untuk Login
Route::get('/login', [AuthController::class, 'index'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::middleware(['auth'])->group(function () {

    // Halaman Utama Pembagi
    Route::get('/', function () {
        $role = auth()->user()->role;

        if ($role === 'Ketua KK') {
            return redirect('/ketuakk/dashboard');
        }

        if ($role === 'Ketua Lab') {
            return redirect('/ketualab/dashboard');
        }

        if ($role === 'Anggota') {
            return redirect('/anggota/dashboard');
        }

        return redirect('/login');
    });

    // RUANG KHUSUS KETUA KK
    Route::middleware(['auth', 'role:Ketua KK'])->group(function () {
        Route::get('/ketuakk/profil', function () {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $dosen = null;
            $lab = null;

            if (!empty($user->id_dosen)) {
                $dosen = \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_dosen', $user->id_dosen)
                    ->first();
            }

            if (!empty($user->id_lab)) {
                $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                    ->where('id_lab', $user->id_lab)
                    ->first();
            }

            return view('ketuakk.profil', compact('user', 'dosen', 'lab'));
        });
        Route::get('/ketuakk/dashboard', function () {
            $tahun = now()->year;

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            /** @var \App\Models\User $userLogin */
            $userLogin = auth()->user();

            $ketuaKkData = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_dosen', $userLogin->id_dosen)
                ->first();

            $idKk = $ketuaKkData->id_kk ?? 1;

            /*
    |--------------------------------------------------------------------------
    | Kartu Ringkasan
    |--------------------------------------------------------------------------
    */
            $jumlahAnggota = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_kk', $idKk)
                ->count();

            $jumlahLab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_kk', $idKk)
                ->count();

            $totalTargetKm = \Illuminate\Support\Facades\DB::table('km_lab')
                ->join('laboratorium_riset', 'km_lab.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('laboratorium_riset.id_kk', $idKk)
                ->where('km_lab.tahun_km', $tahun)
                ->where('km_lab.status_km', 'Aktif')
                ->sum('km_lab.jumlah_km');

            $totalRealisasiKm = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->join('laboratorium_riset', 'aktivitas_km.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('laboratorium_riset.id_kk', $idKk)
                ->whereYear('aktivitas_km.tanggal_mulai', $tahun)
                ->count();

            $totalSisaKm = max($totalTargetKm - $totalRealisasiKm, 0);

            $persentaseRealisasi = $totalTargetKm > 0
                ? min(round(($totalRealisasiKm / $totalTargetKm) * 100, 1), 100)
                : 0;

            /*
    |--------------------------------------------------------------------------
    | Grafik Ringkasan KK
    |--------------------------------------------------------------------------
    */
            $chartKkLabel = ['Target KM', 'Realisasi KM', 'Sisa KM'];
            $chartKkData = [$totalTargetKm, $totalRealisasiKm, $totalSisaKm];

            /*
    |--------------------------------------------------------------------------
    | Grafik dan Rekap per Lab Riset
    |--------------------------------------------------------------------------
    */
            $labs = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_kk', $idKk)
                ->orderBy('id_lab')
                ->get();

            $labChartLabels = [];
            $labShortLabels = [];
            $labTargets = [];
            $labRealisasi = [];
            $labAchievementPercentages = [];
            $rekapLab = [];

            foreach ($labs as $lab) {
                $target = \Illuminate\Support\Facades\DB::table('km_lab')
                    ->where('id_lab', $lab->id_lab)
                    ->where('tahun_km', $tahun)
                    ->where('status_km', 'Aktif')
                    ->sum('jumlah_km');

                $realisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                    ->where('id_lab', $lab->id_lab)
                    ->whereYear('tanggal_mulai', $tahun)
                    ->count();

                $sisa = max($target - $realisasi, 0);

                $persentase = $target > 0
                    ? min(round(($realisasi / $target) * 100, 1), 100)
                    : 0;

                $shortName = $lab->nama_lab;

                if (str_contains($lab->nama_lab, ' - ')) {
                    $shortName = trim(explode(' - ', $lab->nama_lab)[0]);
                }

                $labChartLabels[] = $lab->nama_lab;
                $labShortLabels[] = $shortName;
                $labTargets[] = $target;
                $labRealisasi[] = $realisasi;
                $labAchievementPercentages[] = $persentase;

                $rekapLab[] = [
                    'nama_lab' => $lab->nama_lab,
                    'nama_singkat' => $shortName,
                    'target' => $target,
                    'realisasi' => $realisasi,
                    'sisa' => $sisa,
                    'persentase' => $persentase,
                ];
            }

            /*
    |--------------------------------------------------------------------------
    | Grafik per Kategori KM
    |--------------------------------------------------------------------------
    */
            $kategoriLabels = [];
            $kategoriTargets = [];
            $kategoriRealisasi = [];
            $kategoriCards = [];

            foreach ($kategoriDefault as $kategori) {
                $target = \Illuminate\Support\Facades\DB::table('km_lab')
                    ->join('laboratorium_riset', 'km_lab.id_lab', '=', 'laboratorium_riset.id_lab')
                    ->where('laboratorium_riset.id_kk', $idKk)
                    ->where('km_lab.kategori_km', $kategori)
                    ->where('km_lab.tahun_km', $tahun)
                    ->where('km_lab.status_km', 'Aktif')
                    ->sum('km_lab.jumlah_km');

                $realisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                    ->join('laboratorium_riset', 'aktivitas_km.id_lab', '=', 'laboratorium_riset.id_lab')
                    ->where('laboratorium_riset.id_kk', $idKk)
                    ->where('aktivitas_km.kategori_km', $kategori)
                    ->whereYear('aktivitas_km.tanggal_mulai', $tahun)
                    ->count();

                $sisa = max($target - $realisasi, 0);

                $persentase = $target > 0
                    ? min(round(($realisasi / $target) * 100), 100)
                    : 0;

                $kategoriLabels[] = $kategori;
                $kategoriTargets[] = $target;
                $kategoriRealisasi[] = $realisasi;

                $kategoriCards[] = [
                    'kategori' => $kategori,
                    'target' => $target,
                    'realisasi' => $realisasi,
                    'sisa' => $sisa,
                    'persentase' => $persentase,
                ];
            }

            $hasStatusProgress = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'status_progress');

            $targetSubKategoriRows = \Illuminate\Support\Facades\DB::table('km_lab')
                ->join('laboratorium_riset', 'km_lab.id_lab', '=', 'laboratorium_riset.id_lab')
                ->select(
                    'km_lab.kategori_km',
                    'km_lab.sub_kategori_km',
                    \Illuminate\Support\Facades\DB::raw('SUM(km_lab.jumlah_km) as total_target')
                )
                ->where('laboratorium_riset.id_kk', $idKk)
                ->where('km_lab.tahun_km', $tahun)
                ->where('km_lab.status_km', 'Aktif')
                ->groupBy('km_lab.kategori_km', 'km_lab.sub_kategori_km')
                ->get();

            $queryRealisasiSubKategori = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->join('laboratorium_riset', 'aktivitas_km.id_lab', '=', 'laboratorium_riset.id_lab')
                ->select(
                    'aktivitas_km.kategori_km',
                    'aktivitas_km.sub_kategori_km',
                    \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_realisasi')
                )
                ->where('laboratorium_riset.id_kk', $idKk)
                ->whereYear('aktivitas_km.tanggal_mulai', $tahun);

            if ($hasStatusProgress) {
                $queryRealisasiSubKategori->where('aktivitas_km.status_progress', 'Accepted');
            }

            $realisasiSubKategoriRows = $queryRealisasiSubKategori
                ->groupBy('aktivitas_km.kategori_km', 'aktivitas_km.sub_kategori_km')
                ->get();

            $kategoriDetailCharts = [];

            foreach ($kategoriDefault as $kategori) {
                $targetBySub = $targetSubKategoriRows
                    ->where('kategori_km', $kategori)
                    ->mapWithKeys(function ($row) {
                        return [($row->sub_kategori_km ?: '-') => (int) $row->total_target];
                    });

                $realisasiBySub = $realisasiSubKategoriRows
                    ->where('kategori_km', $kategori)
                    ->mapWithKeys(function ($row) {
                        return [($row->sub_kategori_km ?: '-') => (int) $row->total_realisasi];
                    });

                $labels = $targetBySub
                    ->keys()
                    ->merge($realisasiBySub->keys())
                    ->unique()
                    ->values();

                if ($labels->isEmpty()) {
                    $labels = collect(['Belum ada data']);
                }

                $kategoriDetailCharts[] = [
                    'kategori' => $kategori,
                    'labels' => $labels->toArray(),
                    'targets' => $labels->map(fn($label) => (int) ($targetBySub[$label] ?? 0))->toArray(),
                    'realisasi' => $labels->map(fn($label) => (int) ($realisasiBySub[$label] ?? 0))->toArray(),
                ];
            }

            return view('ketuakk.dashboard', compact(
                'tahun',
                'jumlahAnggota',
                'jumlahLab',
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
                'kategoriLabels',
                'kategoriTargets',
                'kategoriRealisasi',
                'kategoriCards',
                'labAchievementPercentages',
                'kategoriDetailCharts'
            ));
        });

        Route::get('/ketuakk/target-km', [TargetKmController::class, 'index']);
        Route::get('/ketuakk/target-km/create', [TargetKmController::class, 'create']);
        Route::post('/ketuakk/target-km', [TargetKmController::class, 'store']);
        Route::get('/ketuakk/target-km/{id}/edit', [TargetKmController::class, 'edit']);
        Route::put('/ketuakk/target-km/{id}', [TargetKmController::class, 'update']);
        Route::delete('/ketuakk/target-km/{id}', [TargetKmController::class, 'destroy']);
        Route::get('/ketuakk/data-lab-riset', function () {
            $laboratorium = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            $dataLab = [];

            foreach ($laboratorium as $lab) {
                $jumlahDosen = \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_lab', $lab->id_lab)
                    ->count();

                $jumlahAktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                    ->where('id_lab', $lab->id_lab)
                    ->count();

                $dataLab[] = [
                    'id_lab' => $lab->id_lab,
                    'nama_lab' => $lab->nama_lab,
                    'jumlah_dosen' => $jumlahDosen,
                    'jumlah_aktivitas' => $jumlahAktivitas,
                ];
            }

            return view('ketuakk.data-master.lab-riset', compact('dataLab'));
        });

        Route::get('/ketuakk/data-lab-riset/{id}', function ($id) {
            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $id)
                ->first();

            if (!$lab) {
                abort(404);
            }

            $dosen = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_lab', $id)
                ->orderBy('nama_dosen')
                ->get();

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->leftJoin('users', 'aktivitas_km.id_user', '=', 'users.id_user')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->where('aktivitas_km.id_lab', $id)
                ->select(
                    'aktivitas_km.kategori_km',
                    'aktivitas_km.judul_aktivitas',
                    'aktivitas_km.deskripsi_singkat',
                    'aktivitas_km.tanggal_mulai',
                    'aktivitas_km.tanggal_selesai',
                    'users.username',
                    'dosen.nama_dosen',
                    'dosen.nidn'
                )
                ->orderBy('aktivitas_km.tanggal_mulai', 'desc')
                ->get();

            return view('ketuakk.data-master.lab-riset-detail', compact(
                'lab',
                'dosen',
                'aktivitas'
            ));
        });
        Route::get('/ketuakk/data-dosen', function (\Illuminate\Http\Request $request) {
            $q = $request->query('q');

            $allowedPerPage = [50, 100, 1000];
            $perPage = (int) $request->query('per_page', 50);

            if (!in_array($perPage, $allowedPerPage, true)) {
                $perPage = 50;
            }

            $dosens = \Illuminate\Support\Facades\DB::table('dosen')
                ->leftJoin('laboratorium_riset', 'dosen.id_lab', '=', 'laboratorium_riset.id_lab')
                ->select(
                    'dosen.id_dosen',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'dosen.jad',
                    'laboratorium_riset.nama_lab'
                )
                ->when($q, function ($query) use ($q) {
                    $query->where(function ($subQuery) use ($q) {
                        $subQuery->where('dosen.nama_dosen', 'like', '%' . $q . '%')
                            ->orWhere('dosen.nidn', 'like', '%' . $q . '%')
                            ->orWhere('dosen.email', 'like', '%' . $q . '%')
                            ->orWhere('dosen.jad', 'like', '%' . $q . '%')
                            ->orWhere('laboratorium_riset.nama_lab', 'like', '%' . $q . '%');
                    });
                })
                ->orderBy('dosen.id_dosen', 'asc')
                ->paginate($perPage)
                ->withQueryString();

            return view('ketuakk.data-master.dosen', compact('dosens', 'q', 'perPage', 'allowedPerPage'));
        });

        Route::get('/ketuakk/data-dosen/create', function () {
            $labs = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            return view('ketuakk.data-master.dosen-create', compact('labs'));
        });

        Route::post('/ketuakk/data-dosen', function (\Illuminate\Http\Request $request) {
            $request->validate([
                'nama_dosen' => 'required|string|max:255',
                'nidn' => 'required|string|max:50',
                'email' => 'required|email|max:255',
                'id_lab' => 'required|exists:laboratorium_riset,id_lab',
                'jad' => 'required|in:GB,LK,L,AA,NJFA',
            ]);

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $request->id_lab)
                ->first();

            \Illuminate\Support\Facades\DB::table('dosen')->insert([
                'id_kk' => $lab->id_kk ?? 1,
                'id_lab' => $request->id_lab,
                'nama_dosen' => $request->nama_dosen,
                'nidn' => $request->nidn,
                'email' => $request->email,
                'jad' => $request->jad,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect('/ketuakk/data-dosen')->with('success', 'Data dosen berhasil ditambahkan.');
        });

        Route::get('/ketuakk/data-dosen/{id}/edit', function ($id) {
            $dosen = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_dosen', $id)
                ->first();

            if (!$dosen) {
                abort(404);
            }

            $labs = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            return view('ketuakk.data-master.dosen-edit', compact('dosen', 'labs'));
        });

        Route::put('/ketuakk/data-dosen/{id}', function (\Illuminate\Http\Request $request, $id) {
            $request->validate([
                'nama_dosen' => 'required|string|max:255',
                'nidn' => 'required|string|max:50',
                'email' => 'required|email|max:255',
                'id_lab' => 'required|exists:laboratorium_riset,id_lab',
                'jad' => 'required|in:GB,LK,L,AA,NJFA',
            ]);

            $dosen = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_dosen', $id)
                ->first();

            if (!$dosen) {
                abort(404);
            }

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $request->id_lab)
                ->first();

            \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_dosen', $id)
                ->update([
                    'id_kk' => $lab->id_kk ?? 1,
                    'id_lab' => $request->id_lab,
                    'nama_dosen' => $request->nama_dosen,
                    'nidn' => $request->nidn,
                    'email' => $request->email,
                    'jad' => $request->jad,
                    'updated_at' => now(),
                ]);

            return redirect('/ketuakk/data-dosen')->with('success', 'Data dosen berhasil diperbarui.');
        });
        Route::get('/ketuakk/data-kelompok-keahlian', function () {
            $kelompokKeahlian = \Illuminate\Support\Facades\DB::table('kelompok_keahlian')->get();

            return view('ketuakk.data-master.kelompok-keahlian', compact('kelompokKeahlian'));
        });
        Route::get('/ketuakk/monitoring-lab-riset', function (\Illuminate\Http\Request $request) {
            $tahun = (int) $request->query('tahun', now()->year);
            $periode = $request->query('periode', 'triwulan');

            if (!in_array($periode, ['triwulan', 'semester'])) {
                $periode = 'triwulan';
            }

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $periodeColumns = $periode === 'semester'
                ? [
                    1 => 'Semester 1',
                    2 => 'Semester 2',
                ]
                : [
                    1 => 'TW1',
                    2 => 'TW2',
                    3 => 'TW3',
                    4 => 'TW4',
                ];

            $labelPeriode = $periode === 'semester'
                ? 'Semester Tahun ' . $tahun
                : 'Triwulan Tahun ' . $tahun;

            $tahunOptions = collect(
                \Illuminate\Support\Facades\DB::table('km_lab')
                    ->select('tahun_km')
                    ->distinct()
                    ->orderBy('tahun_km', 'desc')
                    ->pluck('tahun_km')
            )
                ->push(now()->year)
                ->unique()
                ->sortDesc()
                ->values();

            $targetReferensi = \Illuminate\Support\Facades\DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->select(
                    'target_km.kategori_km',
                    'target_km.indikator',
                    'target_km.triwulan_1',
                    'target_km.triwulan_2',
                    'target_km.triwulan_3',
                    'target_km.triwulan_4',
                    'target_km.target'
                )
                ->get()
                ->keyBy(function ($item) {
                    return $item->kategori_km . '|' . $item->indikator;
                });

            $hasStatusProgress = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'status_progress');

            $getRangeTanggal = function ($key) use ($tahun, $periode) {
                if ($periode === 'semester') {
                    $bulanMulai = (int) $key === 1 ? 1 : 7;
                    $bulanSelesai = (int) $key === 1 ? 6 : 12;

                    return [
                        \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth()->toDateString(),
                        \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth()->toDateString(),
                    ];
                }

                $bulanMulai = (((int) $key - 1) * 3) + 1;
                $bulanSelesai = $bulanMulai + 2;

                return [
                    \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth()->toDateString(),
                    \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth()->toDateString(),
                ];
            };

            $ubahTriwulanKePeriode = function ($twValues) use ($periode) {
                if ($periode === 'semester') {
                    return [
                        1 => ($twValues[1] ?? 0) + ($twValues[2] ?? 0),
                        2 => ($twValues[3] ?? 0) + ($twValues[4] ?? 0),
                    ];
                }

                return [
                    1 => $twValues[1] ?? 0,
                    2 => $twValues[2] ?? 0,
                    3 => $twValues[3] ?? 0,
                    4 => $twValues[4] ?? 0,
                ];
            };

            $buatDataKosong = function () use ($kategoriDefault, $periodeColumns) {
                $data = [];

                foreach ($kategoriDefault as $kategori) {
                    $data[$kategori] = [
                        'target' => [],
                        'realisasi' => [],
                    ];

                    foreach ($periodeColumns as $key => $label) {
                        $data[$kategori]['target'][$key] = 0;
                        $data[$kategori]['realisasi'][$key] = 0;
                    }
                }

                return $data;
            };

            $labs = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            $monitoringLabs = [];
            $rekapKategori = [];

            foreach ($kategoriDefault as $kategori) {
                $rekapKategori[$kategori] = [
                    'kategori' => $kategori,
                    'target' => 0,
                    'realisasi' => 0,
                    'progress' => 0,
                ];
            }

            foreach ($labs as $lab) {
                $jumlahAnggota = \Illuminate\Support\Facades\DB::table('users')
                    ->whereIn('role', ['Anggota', 'anggota'])
                    ->where('id_lab', $lab->id_lab)
                    ->count();

                $dataPerKategori = $buatDataKosong();

                $kmLabRows = \Illuminate\Support\Facades\DB::table('km_lab')
                    ->where('id_lab', $lab->id_lab)
                    ->where('tahun_km', $tahun)
                    ->where('status_km', 'Aktif')
                    ->get();

                foreach ($kmLabRows as $km) {
                    $subKategori = $km->sub_kategori_km ?? '-';
                    $targetKey = $km->kategori_km . '|' . $subKategori;
                    $targetData = $targetReferensi->get($targetKey);

                    $twValues = [
                        1 => (int) ($targetData->triwulan_1 ?? 0),
                        2 => (int) ($targetData->triwulan_2 ?? 0),
                        3 => (int) ($targetData->triwulan_3 ?? 0),
                        4 => (int) ($targetData->triwulan_4 ?? 0),
                    ];

                    if (array_sum($twValues) <= 0) {
                        $total = (int) ($km->jumlah_km ?? 0);
                        $base = intdiv($total, 4);
                        $sisa = $total % 4;

                        $twValues = [
                            1 => $base + ($sisa >= 1 ? 1 : 0),
                            2 => $base + ($sisa >= 2 ? 1 : 0),
                            3 => $base + ($sisa >= 3 ? 1 : 0),
                            4 => $base,
                        ];
                    }

                    $periodeValues = $ubahTriwulanKePeriode($twValues);

                    foreach ($periodeColumns as $key => $label) {
                        $dataPerKategori[$km->kategori_km]['target'][$key] =
                            ($dataPerKategori[$km->kategori_km]['target'][$key] ?? 0)
                            + (int) ($periodeValues[$key] ?? 0);
                    }
                }

                foreach ($kategoriDefault as $kategori) {
                    foreach ($periodeColumns as $key => $label) {
                        [$tanggalMulai, $tanggalSelesai] = $getRangeTanggal($key);

                        $queryRealisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                            ->where('id_lab', $lab->id_lab)
                            ->where('kategori_km', $kategori)
                            ->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai]);

                        if ($hasStatusProgress) {
                            $queryRealisasi->where('status_progress', 'Accepted');
                        }

                        $dataPerKategori[$kategori]['realisasi'][$key] = (int) $queryRealisasi->count();
                    }
                }

                $totalTargetLab = 0;
                $totalRealisasiLab = 0;

                foreach ($kategoriDefault as $kategori) {
                    $targetKategoriLab = array_sum($dataPerKategori[$kategori]['target']);
                    $realisasiKategoriLab = array_sum($dataPerKategori[$kategori]['realisasi']);

                    $totalTargetLab += $targetKategoriLab;
                    $totalRealisasiLab += $realisasiKategoriLab;

                    $rekapKategori[$kategori]['target'] += $targetKategoriLab;
                    $rekapKategori[$kategori]['realisasi'] += $realisasiKategoriLab;
                }

                $monitoringLabs[] = [
                    'id_lab' => $lab->id_lab,
                    'nama_lab' => $lab->nama_lab,
                    'jumlah_anggota' => $jumlahAnggota,
                    'data' => $dataPerKategori,
                    'total_target' => $totalTargetLab,
                    'total_realisasi' => $totalRealisasiLab,
                    'progress' => $totalTargetLab > 0
                        ? min(round(($totalRealisasiLab / $totalTargetLab) * 100), 100)
                        : 0,
                ];
            }

            foreach ($rekapKategori as $kategori => $item) {
                $rekapKategori[$kategori]['progress'] = $item['target'] > 0
                    ? min(round(($item['realisasi'] / $item['target']) * 100), 100)
                    : 0;
            }

            $rekapKategori = array_values($rekapKategori);

            return view('ketuakk.monitoring-lab-riset.index', compact(
                'tahun',
                'tahunOptions',
                'periode',
                'periodeColumns',
                'labelPeriode',
                'kategoriDefault',
                'monitoringLabs',
                'rekapKategori'
            ));
        });
        Route::get('/ketuakk/monitoring-lab-riset/{id}', function ($id) {
            $tahun = now()->year;

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $id)
                ->first();

            if (!$lab) {
                abort(404);
            }

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $targetPerKategori = \Illuminate\Support\Facades\DB::table('km_lab')
                ->select(
                    'kategori_km',
                    \Illuminate\Support\Facades\DB::raw('SUM(jumlah_km) as total_target')
                )
                ->where('id_lab', $id)
                ->where('tahun_km', $tahun)
                ->where('status_km', 'Aktif')
                ->groupBy('kategori_km')
                ->pluck('total_target', 'kategori_km');

            $realisasiPerKategori = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->select('kategori_km', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_realisasi'))
                ->where('id_lab', $id)
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

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->leftJoin('users', 'aktivitas_km.id_user', '=', 'users.id_user')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->where('aktivitas_km.id_lab', $id)
                ->select(
                    'aktivitas_km.kategori_km',
                    'aktivitas_km.judul_aktivitas',
                    'aktivitas_km.deskripsi_singkat',
                    'aktivitas_km.tanggal_mulai',
                    'aktivitas_km.tanggal_selesai',
                    'users.username',
                    'dosen.nama_dosen',
                    'dosen.nidn'
                )
                ->orderBy('aktivitas_km.tanggal_mulai', 'desc')
                ->get();

            return view('ketuakk.monitoring-lab-riset.detail', compact(
                'lab',
                'tahun',
                'rekapKategori',
                'aktivitas'
            ));
        });
        Route::get('/ketuakk/monitoring-anggota-kk', function (\Illuminate\Http\Request $request) {
            $tahun = (int) $request->query('tahun', now()->year);
            $periode = $request->query('periode', 'triwulan');

            if (!in_array($periode, ['triwulan', 'semester'])) {
                $periode = 'triwulan';
            }

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $labelPeriode = $periode === 'semester'
                ? 'Semester Tahun ' . $tahun
                : 'Triwulan Tahun ' . $tahun;

            $tahunOptions = collect()
                ->merge(
                    \Illuminate\Support\Facades\DB::table('km_lab')
                        ->select('tahun_km')
                        ->distinct()
                        ->pluck('tahun_km')
                )
                ->merge(
                    \Illuminate\Support\Facades\DB::table('aktivitas_km')
                        ->whereNotNull('tanggal_mulai')
                        ->selectRaw("strftime('%Y', tanggal_mulai) as tahun")
                        ->distinct()
                        ->pluck('tahun')
                )
                ->push(now()->year)
                ->map(fn($item) => (int) $item)
                ->unique()
                ->sortDesc()
                ->values();

            $hasStatusProgress = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'status_progress');

            $anggota = \Illuminate\Support\Facades\DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->leftJoin('laboratorium_riset', 'users.id_lab', '=', 'laboratorium_riset.id_lab')
                ->whereIn('users.role', ['Anggota', 'anggota'])
                ->select(
                    'users.id_user',
                    'users.id_dosen',
                    'users.username',
                    'users.id_lab',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'dosen.jad',
                    'laboratorium_riset.nama_lab'
                )
                ->orderBy('laboratorium_riset.nama_lab')
                ->orderBy('dosen.nama_dosen')
                ->get();

            $rekapKategori = [];

            foreach ($kategoriDefault as $kategori) {
                $rekapKategori[$kategori] = [
                    'kategori' => $kategori,
                    'target' => 0,
                    'realisasi' => 0,
                    'progress' => 0,
                ];
            }

            $dataMonitoring = [];

            foreach ($anggota as $item) {
                $targetPerKategori = \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                    ->select(
                        'km_lab.kategori_km',
                        \Illuminate\Support\Facades\DB::raw('SUM(km_anggota.jumlah_km) as total_target')
                    )
                    ->where('km_anggota.id_user', $item->id_user)
                    ->where('km_lab.tahun_km', $tahun)
                    ->where('km_lab.status_km', 'Aktif')
                    ->groupBy('km_lab.kategori_km')
                    ->pluck('total_target', 'kategori_km');

                $queryRealisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                    ->select(
                        'kategori_km',
                        \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_realisasi')
                    )
                    ->where('id_user', $item->id_user)
                    ->whereYear('tanggal_mulai', $tahun);

                if ($hasStatusProgress) {
                    $queryRealisasi->where('status_progress', 'Accepted');
                }

                $realisasiPerKategori = $queryRealisasi
                    ->groupBy('kategori_km')
                    ->pluck('total_realisasi', 'kategori_km');

                $jumlahKmPerKategori = [];
                $realisasiKmPerKategori = [];

                foreach ($kategoriDefault as $kategori) {
                    $targetKategori = (int) ($targetPerKategori[$kategori] ?? 0);
                    $realisasiKategori = (int) ($realisasiPerKategori[$kategori] ?? 0);

                    $jumlahKmPerKategori[$kategori] = $targetKategori;
                    $realisasiKmPerKategori[$kategori] = $realisasiKategori;

                    $rekapKategori[$kategori]['target'] += $targetKategori;
                    $rekapKategori[$kategori]['realisasi'] += $realisasiKategori;
                }

                $totalTarget = array_sum($jumlahKmPerKategori);
                $totalRealisasi = array_sum($realisasiKmPerKategori);
                $sisa = max($totalTarget - $totalRealisasi, 0);

                $persentase = $totalTarget > 0
                    ? min(round(($totalRealisasi / $totalTarget) * 100), 100)
                    : 0;

                if ($totalTarget <= 0) {
                    $statusProgress = 'Belum Ada KM';
                    $statusClass = 'secondary';
                } elseif ($totalRealisasi <= 0) {
                    $statusProgress = 'Belum Mulai';
                    $statusClass = 'danger';
                } elseif ($totalRealisasi >= $totalTarget) {
                    $statusProgress = 'Selesai';
                    $statusClass = 'success';
                } else {
                    $statusProgress = 'Sedang Progress';
                    $statusClass = 'warning';
                }

                $dataMonitoring[] = [
                    'id_user' => $item->id_user,
                    'username' => $item->username,
                    'nama_dosen' => $item->nama_dosen ?? $item->username,
                    'nidn' => $item->nidn ?? '-',
                    'email' => $item->email ?? '-',
                    'jad' => $item->jad ?? 'AA',
                    'nama_lab' => $item->nama_lab ?? '-',
                    'target_kategori' => $jumlahKmPerKategori,
                    'realisasi_kategori' => $realisasiKmPerKategori,
                    'total_target' => $totalTarget,
                    'total_realisasi' => $totalRealisasi,
                    'sisa' => $sisa,
                    'persentase' => $persentase,
                    'status_progress' => $statusProgress,
                    'status_class' => $statusClass,
                ];
            }

            foreach ($rekapKategori as $kategori => $item) {
                $rekapKategori[$kategori]['progress'] = $item['target'] > 0
                    ? min(round(($item['realisasi'] / $item['target']) * 100), 100)
                    : 0;
            }

            $rekapKategori = array_values($rekapKategori);

            $jumlahAnggota = count($dataMonitoring);
            $jumlahSelesai = collect($dataMonitoring)->where('status_progress', 'Selesai')->count();
            $jumlahProgress = collect($dataMonitoring)->where('status_progress', 'Sedang Progress')->count();
            $jumlahBelumMulai = collect($dataMonitoring)
                ->filter(fn($item) => in_array($item['status_progress'], ['Belum Mulai', 'Belum Ada KM']))
                ->count();

            return view('ketuakk.monitoring-anggota-kk.index', compact(
                'tahun',
                'tahunOptions',
                'periode',
                'labelPeriode',
                'kategoriDefault',
                'rekapKategori',
                'dataMonitoring',
                'jumlahAnggota',
                'jumlahSelesai',
                'jumlahProgress',
                'jumlahBelumMulai'
            ));
        });
        Route::get('/ketuakk/monitoring-anggota-kk/{id}', function (\Illuminate\Http\Request $request, $id) {
            $tahun = (int) $request->query('tahun', now()->year);
            $periode = $request->query('periode', 'tahun');

            $bulan = (int) $request->query('bulan', now()->month);
            $bulan = max(1, min(12, $bulan));

            $triwulan = (int) $request->query('triwulan', ceil(now()->month / 3));
            $triwulan = max(1, min(4, $triwulan));

            $semester = (int) $request->query('semester', now()->month <= 6 ? 1 : 2);
            $semester = max(1, min(2, $semester));

            if ($periode === 'bulan') {
                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulan, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth();
                $jumlahBulan = 1;
                $labelPeriode = 'Bulanan - Bulan ' . $bulan . ' Tahun ' . $tahun;
            } elseif ($periode === 'triwulan') {
                $bulanMulai = (($triwulan - 1) * 3) + 1;
                $bulanSelesai = $bulanMulai + 2;

                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
                $jumlahBulan = 3;
                $labelPeriode = 'Triwulan ' . $triwulan . ' Tahun ' . $tahun;
            } elseif ($periode === 'semester') {
                $bulanMulai = $semester === 1 ? 1 : 7;
                $bulanSelesai = $semester === 1 ? 6 : 12;

                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
                $jumlahBulan = 6;
                $labelPeriode = 'Semester ' . $semester . ' Tahun ' . $tahun;
            } else {
                $periode = 'tahun';
                $tanggalMulai = \Carbon\Carbon::create($tahun, 1, 1)->startOfYear();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, 12, 31)->endOfYear();
                $jumlahBulan = 12;
                $labelPeriode = 'Tahunan ' . $tahun;
            }

            $anggota = \Illuminate\Support\Facades\DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->leftJoin('laboratorium_riset', 'users.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('users.id_user', $id)
                ->where('users.role', 'Anggota')
                ->select(
                    'users.id_user',
                    'users.id_dosen',
                    'users.username',
                    'users.id_lab',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'dosen.jad',
                    'laboratorium_riset.nama_lab'
                )
                ->first();

            if (!$anggota) {
                abort(404);
            }

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $targetAssignPerKategori = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->select(
                    'km_lab.kategori_km',
                    \Illuminate\Support\Facades\DB::raw('SUM(km_anggota.jumlah_km) as total_target')
                )
                ->where('km_anggota.id_user', $anggota->id_user)
                ->where('km_lab.tahun_km', $tahun)
                ->where('km_lab.status_km', 'Aktif')
                ->groupBy('km_lab.kategori_km')
                ->pluck('total_target', 'kategori_km');

            $aktivitasPerKategori = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->select(
                    'kategori_km',
                    \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_realisasi')
                )
                ->where('id_user', $anggota->id_user)
                ->whereBetween('tanggal_mulai', [
                    $tanggalMulai->toDateString(),
                    $tanggalSelesai->toDateString(),
                ])
                ->groupBy('kategori_km')
                ->pluck('total_realisasi', 'kategori_km');

            $rekap = [];

            foreach ($kategoriDefault as $kategori) {
                $targetTahunan = $targetAssignPerKategori[$kategori] ?? 0;

                $targetPeriode = $targetTahunan > 0
                    ? (int) ceil(($targetTahunan * $jumlahBulan) / 12)
                    : 0;

                $realisasi = $aktivitasPerKategori[$kategori] ?? 0;
                $sisa = max($targetPeriode - $realisasi, 0);

                $persentase = $targetPeriode > 0
                    ? round(($realisasi / $targetPeriode) * 100)
                    : 0;

                $rekap[] = [
                    'kategori' => $kategori,
                    'target_tahunan' => $targetTahunan,
                    'target_periode' => $targetPeriode,
                    'realisasi' => $realisasi,
                    'sisa' => $sisa,
                    'persentase' => min($persentase, 100),
                    'status' => $targetPeriode > 0 && $sisa <= 0
                        ? 'Tercapai'
                        : 'Belum Tercapai',
                ];
            }

            $totalTargetTahunan = array_sum(array_column($rekap, 'target_tahunan'));
            $totalTargetPeriode = array_sum(array_column($rekap, 'target_periode'));
            $totalRealisasi = array_sum(array_column($rekap, 'realisasi'));
            $totalSisa = array_sum(array_column($rekap, 'sisa'));

            $persentaseTotal = $totalTargetPeriode > 0
                ? round(($totalRealisasi / $totalTargetPeriode) * 100)
                : 0;

            $riwayatAssign = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->where('km_anggota.id_user', $anggota->id_user)
                ->where('km_lab.tahun_km', $tahun)
                ->select(
                    'km_anggota.jumlah_km',
                    'km_anggota.created_at',
                    'km_lab.kategori_km',
                    'km_lab.tahun_km',
                    'km_lab.status_km'
                )
                ->orderBy('km_anggota.created_at', 'desc')
                ->get();

            $riwayatAssign = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->where('km_anggota.id_user', $anggota->id_user)
                ->where('km_lab.tahun_km', $tahun)
                ->select(
                    'km_anggota.jumlah_km',
                    'km_anggota.created_at',
                    'km_lab.kategori_km',
                    'km_lab.tahun_km',
                    'km_lab.status_km'
                )
                ->orderBy('km_anggota.created_at', 'desc')
                ->get();

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_user', $anggota->id_user)
                ->whereBetween('tanggal_mulai', [
                    $tanggalMulai->toDateString(),
                    $tanggalSelesai->toDateString(),
                ])
                ->orderBy('tanggal_mulai', 'desc')
                ->get();

            return view('ketuakk.monitoring-anggota-kk.detail', compact(
                'anggota',
                'tahun',
                'periode',
                'bulan',
                'triwulan',
                'semester',
                'tanggalMulai',
                'tanggalSelesai',
                'labelPeriode',
                'rekap',
                'riwayatAssign',
                'aktivitas',
                'totalTargetTahunan',
                'totalTargetPeriode',
                'totalRealisasi',
                'totalSisa',
                'persentaseTotal'
            ));
        });
        Route::get('/ketuakk/km-lab-riset/create', function () {
            $labs = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            $targetOptions = \Illuminate\Support\Facades\DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->select(
                    'target_km.id_target',
                    'target_km.kategori_km',
                    'target_km.indikator',
                    'target_km.target',
                    'target_km.triwulan_1',
                    'target_km.triwulan_2',
                    'target_km.triwulan_3',
                    'target_km.triwulan_4',
                    'kontrak_manajemen.tahun_km'
                )
                ->orderBy('kontrak_manajemen.tahun_km', 'desc')
                ->orderBy('target_km.kategori_km')
                ->orderBy('target_km.indikator')
                ->get();

            return view('ketuakk.km-lab-riset.create', compact('labs', 'targetOptions'));
        });

        Route::post('/ketuakk/km-lab-riset', function (\Illuminate\Http\Request $request) {
            $request->validate([
                'id_lab' => 'required|exists:laboratorium_riset,id_lab',
                'id_target' => 'required|integer|exists:target_km,id_target',
                'jumlah_km' => 'required|integer|min:1',
                'status_km' => 'required|string|max:50',
            ]);

            $target = \Illuminate\Support\Facades\DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->select(
                    'target_km.id_target',
                    'target_km.kategori_km',
                    'target_km.indikator',
                    'target_km.target',
                    'kontrak_manajemen.tahun_km'
                )
                ->where('target_km.id_target', $request->id_target)
                ->first();

            if (! $target) {
                return back()
                    ->withErrors(['id_target' => 'Data Target KM tidak ditemukan.'])
                    ->withInput();
            }

            \Illuminate\Support\Facades\DB::table('km_lab')->insert([
                'id_lab' => $request->id_lab,
                'tahun_km' => $target->tahun_km,
                'kategori_km' => $target->kategori_km,
                'sub_kategori_km' => $target->indikator,
                'jumlah_km' => $request->jumlah_km,
                'status_km' => $request->status_km,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect('/ketuakk/km-lab-riset')
                ->with('success', 'KM berhasil diturunkan ke Lab Riset.');
        });
        Route::get('/ketuakk/km-lab-riset', function () {
            $tahun = (int) request('tahun', now()->year);

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $tahunOptions = collect()
                ->merge(
                    \Illuminate\Support\Facades\DB::table('kontrak_manajemen')
                        ->select('tahun_km')
                        ->distinct()
                        ->pluck('tahun_km')
                )
                ->merge(
                    \Illuminate\Support\Facades\DB::table('km_lab')
                        ->select('tahun_km')
                        ->distinct()
                        ->pluck('tahun_km')
                )
                ->push(now()->year)
                ->unique()
                ->sortDesc()
                ->values();

            if (! $tahunOptions->contains($tahun)) {
                $tahunOptions->push($tahun);
                $tahunOptions = $tahunOptions->unique()->sortDesc()->values();
            }

            $totalKmKkPerKategori = \Illuminate\Support\Facades\DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->select(
                    'target_km.kategori_km',
                    \Illuminate\Support\Facades\DB::raw('SUM(target_km.target) as total_target')
                )
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->groupBy('target_km.kategori_km')
                ->pluck('total_target', 'kategori_km');

            $kmTurunPerLabKategori = \Illuminate\Support\Facades\DB::table('km_lab')
                ->select(
                    'id_lab',
                    'kategori_km',
                    \Illuminate\Support\Facades\DB::raw('SUM(jumlah_km) as total_turun')
                )
                ->where('tahun_km', $tahun)
                ->where('status_km', 'Aktif')
                ->groupBy('id_lab', 'kategori_km')
                ->get()
                ->groupBy('id_lab');

            $assignPerLab = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->select(
                    'km_lab.id_lab',
                    \Illuminate\Support\Facades\DB::raw('SUM(km_anggota.jumlah_km) as total_assign')
                )
                ->where('km_lab.tahun_km', $tahun)
                ->where('km_lab.status_km', 'Aktif')
                ->groupBy('km_lab.id_lab')
                ->pluck('total_assign', 'id_lab');

            $labs = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            $dataLab = [];

            foreach ($labs as $lab) {
                $turunGroup = $kmTurunPerLabKategori->get($lab->id_lab, collect());

                $kkPerKategori = [];
                $turunPerKategori = [];

                foreach ($kategoriDefault as $kategori) {
                    $kkPerKategori[$kategori] = (int) ($totalKmKkPerKategori[$kategori] ?? 0);

                    $turunItem = $turunGroup->firstWhere('kategori_km', $kategori);
                    $turunPerKategori[$kategori] = (int) ($turunItem->total_turun ?? 0);
                }

                $totalTurun = array_sum($turunPerKategori);
                $totalAssign = (int) ($assignPerLab[$lab->id_lab] ?? 0);
                $sisaKm = max($totalTurun - $totalAssign, 0);

                $persentase = $totalTurun > 0
                    ? min(round(($totalAssign / $totalTurun) * 100), 100)
                    : 0;

                if ($totalTurun <= 0) {
                    $status = 'Belum Ada KM';
                } elseif ($sisaKm <= 0) {
                    $status = 'Selesai';
                } else {
                    $status = 'Belum Selesai';
                }

                $dataLab[] = [
                    'id_lab' => $lab->id_lab,
                    'nama_lab' => $lab->nama_lab,
                    'kk_per_kategori' => $kkPerKategori,
                    'turun_per_kategori' => $turunPerKategori,
                    'total_turun' => $totalTurun,
                    'total_assign' => $totalAssign,
                    'sisa_km' => $sisaKm,
                    'persentase' => $persentase,
                    'status' => $status,
                ];
            }

            $rekapKategori = [];

            foreach ($kategoriDefault as $kategori) {
                $totalKk = (int) ($totalKmKkPerKategori[$kategori] ?? 0);

                $totalTurun = \Illuminate\Support\Facades\DB::table('km_lab')
                    ->where('tahun_km', $tahun)
                    ->where('status_km', 'Aktif')
                    ->where('kategori_km', $kategori)
                    ->sum('jumlah_km');

                $rekapKategori[] = [
                    'kategori' => $kategori,
                    'total_km_kk' => $totalKk,
                    'total_turun' => (int) $totalTurun,
                    'sisa' => max($totalKk - (int) $totalTurun, 0),
                ];
            }

            return view('ketuakk.km-lab-riset.index', compact(
                'dataLab',
                'tahun',
                'tahunOptions',
                'kategoriDefault',
                'rekapKategori'
            ));
        });
        Route::delete('/ketuakk/km-lab-riset/{id}', function ($id) {
            $kmLab = \Illuminate\Support\Facades\DB::table('km_lab')
                ->where('id_km_lab', $id)
                ->first();

            if (!$kmLab) {
                return redirect('/ketuakk/km-lab-riset')
                    ->with('error', 'Data KM lab tidak ditemukan.');
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
                \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->where('id_km_lab', $id)
                    ->delete();

                \Illuminate\Support\Facades\DB::table('km_lab')
                    ->where('id_km_lab', $id)
                    ->delete();
            });

            return redirect('/ketuakk/km-lab-riset')
                ->with('success', 'KM yang diturunkan ke lab berhasil dihapus.');
        });
        Route::get('/ketuakk/km-lab-riset/{id}', function ($id) {
            $tahun = (int) request('tahun', now()->year);

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $id)
                ->first();

            if (!$lab) {
                abort(404);
            }

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $jenisByKategori = [
                'Pendidikan' => 'Pendidikan/Pengajaran',
                'Penelitian' => 'Penelitian',
                'Publikasi' => 'Jurnal',
                'Pengabdian' => 'Pengabdian',
                'Penunjang' => 'Penunjang',
            ];

            $targetReferensi = \Illuminate\Support\Facades\DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->select(
                    'target_km.kategori_km',
                    'target_km.indikator',
                    'target_km.triwulan_1',
                    'target_km.triwulan_2',
                    'target_km.triwulan_3',
                    'target_km.triwulan_4',
                    'target_km.target'
                )
                ->get()
                ->keyBy(function ($item) {
                    return $item->kategori_km . '|' . $item->indikator;
                });

            $daftarKmTurunRaw = \Illuminate\Support\Facades\DB::table('km_lab')
                ->where('id_lab', $id)
                ->where('tahun_km', $tahun)
                ->where('status_km', 'Aktif')
                ->orderBy('created_at', 'desc')
                ->get();

            $daftarKmTurun = [];

            foreach ($daftarKmTurunRaw as $km) {
                $sudahAssign = \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->where('id_km_lab', $km->id_km_lab)
                    ->sum('jumlah_km');

                $sisaKm = max($km->jumlah_km - $sudahAssign, 0);

                $persentase = $km->jumlah_km > 0
                    ? min(round(($sudahAssign / $km->jumlah_km) * 100), 100)
                    : 0;

                $subKategori = $km->sub_kategori_km ?? '-';
                $targetKey = $km->kategori_km . '|' . $subKategori;
                $targetData = $targetReferensi->get($targetKey);

                $daftarKmTurun[] = [
                    'id_km_lab' => $km->id_km_lab,
                    'kategori_km' => $km->kategori_km,
                    'jenis_km' => $jenisByKategori[$km->kategori_km] ?? '-',
                    'sub_kategori_km' => $subKategori,
                    'triwulan_1' => $targetData->triwulan_1 ?? 0,
                    'triwulan_2' => $targetData->triwulan_2 ?? 0,
                    'triwulan_3' => $targetData->triwulan_3 ?? 0,
                    'triwulan_4' => $targetData->triwulan_4 ?? 0,
                    'jumlah_km' => $km->jumlah_km,
                    'tahun_km' => $km->tahun_km,
                    'status_km' => $km->status_km,
                    'created_at' => $km->created_at,
                    'sudah_assign' => $sudahAssign,
                    'sisa_km' => $sisaKm,
                    'persentase' => $persentase,
                ];
            }

            $rekapKategori = collect($daftarKmTurun)
                ->groupBy('kategori_km')
                ->map(function ($items, $kategori) {
                    return [
                        'kategori' => $kategori,
                        'total_km' => $items->sum('jumlah_km'),
                        'sudah_assign' => $items->sum('sudah_assign'),
                        'sisa_km' => $items->sum('sisa_km'),
                        'persentase' => $items->sum('jumlah_km') > 0
                            ? min(round(($items->sum('sudah_assign') / $items->sum('jumlah_km')) * 100), 100)
                            : 0,
                    ];
                })
                ->values();

            foreach ($kategoriDefault as $kategori) {
                if (!$rekapKategori->firstWhere('kategori', $kategori)) {
                    $rekapKategori->push([
                        'kategori' => $kategori,
                        'total_km' => 0,
                        'sudah_assign' => 0,
                        'sisa_km' => 0,
                        'persentase' => 0,
                    ]);
                }
            }

            $riwayatAssign = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->join('users', 'km_anggota.id_user', '=', 'users.id_user')
                ->leftJoin('dosen', 'km_anggota.id_dosen', '=', 'dosen.id_dosen')
                ->where('km_lab.id_lab', $id)
                ->where('km_lab.tahun_km', $tahun)
                ->select(
                    'km_anggota.id_km_anggota',
                    'km_anggota.jumlah_km',
                    'km_anggota.created_at',
                    'km_lab.kategori_km',
                    'km_lab.sub_kategori_km',
                    'users.username',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'dosen.jad'
                )
                ->orderBy('km_anggota.created_at', 'desc')
                ->get();

            $anggota = \Illuminate\Support\Facades\DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->where('users.id_lab', $id)
                ->whereIn('users.role', ['anggota', 'Anggota'])
                ->select(
                    'users.id_user',
                    'users.username',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'dosen.jad'
                )
                ->orderBy('dosen.nama_dosen')
                ->get();

            $totalKmTurun = collect($daftarKmTurun)->sum('jumlah_km');
            $totalKmAssign = collect($daftarKmTurun)->sum('sudah_assign');
            $totalSisaKm = max($totalKmTurun - $totalKmAssign, 0);
            $persentaseTotal = $totalKmTurun > 0
                ? min(round(($totalKmAssign / $totalKmTurun) * 100), 100)
                : 0;

            return view('ketuakk.km-lab-riset.detail', compact(
                'lab',
                'tahun',
                'daftarKmTurun',
                'rekapKategori',
                'totalKmTurun',
                'totalKmAssign',
                'totalSisaKm',
                'persentaseTotal',
                'riwayatAssign',
                'anggota'
            ));
        });
        Route::get('/ketuakk/km-anggota-kk', function () {
            $tahun = (int) request('tahun', now()->year);

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $tahunOptions = collect(
                \Illuminate\Support\Facades\DB::table('km_lab')
                    ->select('tahun_km')
                    ->distinct()
                    ->orderBy('tahun_km', 'desc')
                    ->pluck('tahun_km')
            )
                ->push(now()->year)
                ->unique()
                ->sortDesc()
                ->values();

            $userLogin = auth()->user();

            $ketuaKkData = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_dosen', $userLogin->id_dosen)
                ->first();

            $anggota = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('dosen.id_kk', $ketuaKkData->id_kk ?? null)
                ->leftJoin('laboratorium_riset', 'dosen.id_lab', '=', 'laboratorium_riset.id_lab')
                ->leftJoin('users', 'dosen.id_dosen', '=', 'users.id_dosen')
                ->select(
                    'users.id_user',
                    'dosen.id_dosen',
                    'users.username',
                    'dosen.id_lab',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'dosen.jad',
                    'laboratorium_riset.nama_lab'
                )
                ->orderBy('laboratorium_riset.nama_lab')
                ->orderBy('dosen.nama_dosen')
                ->get();

            $dataAnggota = [];

            foreach ($anggota as $item) {
                $jumlahKmPerKategori = [];

                foreach ($kategoriDefault as $kategori) {
                    $jumlahKmPerKategori[$kategori] = 0;
                }

                if (!empty($item->id_user)) {
                    $targetPerKategori = \Illuminate\Support\Facades\DB::table('km_anggota')
                        ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                        ->select(
                            'km_lab.kategori_km',
                            \Illuminate\Support\Facades\DB::raw('SUM(km_anggota.jumlah_km) as total_km')
                        )
                        ->where('km_anggota.id_user', $item->id_user)
                        ->where('km_lab.tahun_km', $tahun)
                        ->where('km_lab.status_km', 'Aktif')
                        ->groupBy('km_lab.kategori_km')
                        ->pluck('total_km', 'kategori_km');

                    foreach ($kategoriDefault as $kategori) {
                        $jumlahKmPerKategori[$kategori] = (int) ($targetPerKategori[$kategori] ?? 0);
                    }
                }

                $dataAnggota[] = [
                    'id_user' => $item->id_user,
                    'nama_dosen' => $item->nama_dosen ?? $item->username ?? '-',
                    'nidn' => $item->nidn ?? '-',
                    'jad' => $item->jad ?? '-',
                    'email' => $item->email ?? '-',
                    'nama_lab' => $item->nama_lab ?? '-',
                    'jumlah_km' => $jumlahKmPerKategori,
                    'total_km' => array_sum($jumlahKmPerKategori),
                ];
            }

            return view('ketuakk.km-anggota-kk.index', compact(
                'dataAnggota',
                'tahun',
                'tahunOptions',
                'kategoriDefault'
            ));
        });
        Route::get('/ketuakk/km-anggota-kk/{id}', function ($id) {
            $tahun = (int) request('tahun', now()->year);

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $jenisByKategori = [
                'Pendidikan' => 'Pendidikan/Pengajaran',
                'Penelitian' => 'Penelitian',
                'Publikasi' => 'Jurnal',
                'Pengabdian' => 'Pengabdian',
                'Penunjang' => 'Penunjang',
            ];

            $anggota = \Illuminate\Support\Facades\DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->leftJoin('laboratorium_riset', 'users.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('users.id_user', $id)
                ->select(
                    'users.id_user',
                    'users.id_dosen',
                    'users.username',
                    'users.role',
                    'users.id_lab',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'dosen.jad',
                    'laboratorium_riset.nama_lab'
                )
                ->first();

            if (!$anggota) {
                abort(404);
            }

            $targetReferensi = \Illuminate\Support\Facades\DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->select(
                    'target_km.kategori_km',
                    'target_km.indikator',
                    'target_km.triwulan_1',
                    'target_km.triwulan_2',
                    'target_km.triwulan_3',
                    'target_km.triwulan_4',
                    'target_km.target'
                )
                ->get()
                ->keyBy(function ($item) {
                    return $item->kategori_km . '|' . $item->indikator;
                });

            $kmDiterimaRaw = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->where('km_anggota.id_user', $anggota->id_user)
                ->where('km_lab.tahun_km', $tahun)
                ->where('km_lab.status_km', 'Aktif')
                ->select(
                    'km_anggota.id_km_anggota',
                    'km_anggota.jumlah_km',
                    'km_anggota.created_at',
                    'km_lab.kategori_km',
                    'km_lab.sub_kategori_km'
                )
                ->orderBy('km_anggota.created_at', 'desc')
                ->get();

            $rangeTriwulan = [
                1 => [
                    \Carbon\Carbon::create($tahun, 1, 1)->toDateString(),
                    \Carbon\Carbon::create($tahun, 3, 31)->toDateString(),
                ],
                2 => [
                    \Carbon\Carbon::create($tahun, 4, 1)->toDateString(),
                    \Carbon\Carbon::create($tahun, 6, 30)->toDateString(),
                ],
                3 => [
                    \Carbon\Carbon::create($tahun, 7, 1)->toDateString(),
                    \Carbon\Carbon::create($tahun, 9, 30)->toDateString(),
                ],
                4 => [
                    \Carbon\Carbon::create($tahun, 10, 1)->toDateString(),
                    \Carbon\Carbon::create($tahun, 12, 31)->toDateString(),
                ],
            ];

            $hasIdKmAnggotaAktivitas = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'id_km_anggota');
            $hasSubKategoriAktivitas = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'sub_kategori_km');
            $hasStatusProgress = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'status_progress');

            $kmDiterima = [];

            foreach ($kmDiterimaRaw as $km) {
                $subKategori = $km->sub_kategori_km ?? '-';
                $targetKey = $km->kategori_km . '|' . $subKategori;
                $targetData = $targetReferensi->get($targetKey);

                $targetTw1 = (int) ($targetData->triwulan_1 ?? 0);
                $targetTw2 = (int) ($targetData->triwulan_2 ?? 0);
                $targetTw3 = (int) ($targetData->triwulan_3 ?? 0);
                $targetTw4 = (int) ($targetData->triwulan_4 ?? 0);

                $realisasiTw = [];

                foreach ($rangeTriwulan as $tw => $range) {
                    $queryRealisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                        ->where('id_user', $anggota->id_user)
                        ->where('kategori_km', $km->kategori_km)
                        ->whereBetween('tanggal_mulai', [$range[0], $range[1]]);

                    if ($hasStatusProgress) {
                        $queryRealisasi->where('status_progress', 'Accepted');
                    }

                    if ($hasIdKmAnggotaAktivitas) {
                        $queryRealisasi->where('id_km_anggota', $km->id_km_anggota);
                    } elseif ($hasSubKategoriAktivitas) {
                        $queryRealisasi->where('sub_kategori_km', $subKategori);
                    }

                    $realisasiTw[$tw] = (int) $queryRealisasi->count();
                }

                $totalTarget = (int) $km->jumlah_km;
                $totalRealisasi = array_sum($realisasiTw);

                $progress = $totalTarget > 0
                    ? min(round(($totalRealisasi / $totalTarget) * 100), 100)
                    : 0;

                $kmDiterima[] = [
                    'id_km_anggota' => $km->id_km_anggota,
                    'kategori_km' => $km->kategori_km,
                    'jenis_km' => $jenisByKategori[$km->kategori_km] ?? '-',
                    'sub_kategori_km' => $subKategori,
                    'target_tw1' => $targetTw1,
                    'target_tw2' => $targetTw2,
                    'target_tw3' => $targetTw3,
                    'target_tw4' => $targetTw4,
                    'total_target' => $totalTarget,
                    'realisasi_tw1' => $realisasiTw[1] ?? 0,
                    'realisasi_tw2' => $realisasiTw[2] ?? 0,
                    'realisasi_tw3' => $realisasiTw[3] ?? 0,
                    'realisasi_tw4' => $realisasiTw[4] ?? 0,
                    'total_realisasi' => $totalRealisasi,
                    'progress' => $progress,
                    'status' => $totalTarget > 0 && $totalRealisasi >= $totalTarget
                        ? 'Tercapai'
                        : 'Belum Tercapai',
                ];
            }

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_user', $anggota->id_user)
                ->whereYear('tanggal_mulai', $tahun)
                ->orderBy('tanggal_mulai', 'desc')
                ->get();

            return view('ketuakk.km-anggota-kk.detail', compact(
                'anggota',
                'tahun',
                'kmDiterima',
                'aktivitas'
            ));
        });
        Route::get('/ketuakk/km-kk', function () {
            $tahun = (int) request('tahun', now()->year);

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $jenisByKategori = [
                'Pendidikan' => 'Pendidikan/Pengajaran',
                'Penelitian' => 'Penelitian',
                'Publikasi' => 'Publikasi',
                'Pengabdian' => 'Pengabdian',
                'Penunjang' => 'Penunjang',
            ];

            $tahunOptions = collect()
                ->merge(
                    \Illuminate\Support\Facades\DB::table('kontrak_manajemen')
                        ->select('tahun_km')
                        ->distinct()
                        ->pluck('tahun_km')
                )
                ->merge(
                    \Illuminate\Support\Facades\DB::table('target_km')
                        ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                        ->select('kontrak_manajemen.tahun_km')
                        ->distinct()
                        ->pluck('tahun_km')
                )
                ->push(now()->year)
                ->map(fn($item) => (int) $item)
                ->unique()
                ->sortDesc()
                ->values();

            $targetRows = \Illuminate\Support\Facades\DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->select(
                    'target_km.kategori_km',
                    'target_km.indikator',
                    'target_km.triwulan_1',
                    'target_km.triwulan_2',
                    'target_km.triwulan_3',
                    'target_km.triwulan_4',
                    'target_km.target'
                )
                ->orderByRaw("CASE target_km.kategori_km
            WHEN 'Pendidikan' THEN 1
            WHEN 'Penelitian' THEN 2
            WHEN 'Publikasi' THEN 3
            WHEN 'Pengabdian' THEN 4
            WHEN 'Penunjang' THEN 5
            ELSE 6
        END")
                ->orderBy('target_km.indikator')
                ->get()
                ->map(function ($item) use ($jenisByKategori) {
                    return [
                        'kategori_km' => $item->kategori_km ?? '-',
                        'jenis_km' => $jenisByKategori[$item->kategori_km] ?? ($item->kategori_km ?? '-'),
                        'sub_kategori_km' => $item->indikator ?? '-',
                        'triwulan_1' => (int) ($item->triwulan_1 ?? 0),
                        'triwulan_2' => (int) ($item->triwulan_2 ?? 0),
                        'triwulan_3' => (int) ($item->triwulan_3 ?? 0),
                        'triwulan_4' => (int) ($item->triwulan_4 ?? 0),
                        'total_target' => (int) ($item->target ?? 0),
                    ];
                });

            $rekapLab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get()
                ->map(function ($lab) use ($tahun) {
                    $target = \Illuminate\Support\Facades\DB::table('km_lab')
                        ->where('id_lab', $lab->id_lab)
                        ->where('tahun_km', $tahun)
                        ->where('status_km', 'Aktif')
                        ->sum('jumlah_km');

                    $realisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                        ->where('id_lab', $lab->id_lab)
                        ->whereYear('tanggal_mulai', $tahun)
                        ->count();

                    $persentase = $target > 0
                        ? min(round(($realisasi / $target) * 100), 100)
                        : 0;

                    return [
                        'nama_lab' => $lab->nama_lab,
                        'target' => (int) $target,
                        'realisasi' => (int) $realisasi,
                        'persentase' => $persentase,
                        'status' => $target > 0 && $realisasi >= $target
                            ? 'Tercapai'
                            : 'Belum Tercapai',
                    ];
                });

            return view('ketuakk.km-kk.index', compact(
                'tahun',
                'tahunOptions',
                'targetRows',
                'rekapLab'
            ));
        });
        Route::delete('/ketuakk/data-dosen/{id}', function ($id) {
            try {
                $dosen = \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_dosen', $id)
                    ->first();

                if (!$dosen) {
                    return redirect('/ketuakk/data-dosen')
                        ->with('error', 'Data dosen tidak ditemukan.');
                }

                $dipakaiUser = \Illuminate\Support\Facades\DB::table('users')
                    ->where('id_dosen', $id)
                    ->exists();

                if ($dipakaiUser) {
                    return redirect('/ketuakk/data-dosen')
                        ->with('error', 'Data dosen tidak bisa dihapus karena masih terhubung dengan akun user.');
                }
                \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_dosen', $id)
                    ->delete();

                return redirect('/ketuakk/data-dosen')
                    ->with('success', 'Data dosen berhasil dihapus.');
            } catch (\Exception $e) {
                return redirect('/ketuakk/data-dosen')
                    ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
            }
        });
    });


    // RUANG KHUSUS KETUA LAB
    Route::middleware(['auth', 'role:Ketua Lab'])->group(function () {
        Route::get('/ketualab/dashboard', function () {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $idLab = $user->id_lab;
            $tahun = now()->year;

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $idLab)
                ->first();

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $kategoriLabels = [];
            $kategoriKmTurun = [];
            $kategoriKmAssign = [];
            $kategoriRealisasi = [];

            foreach ($kategoriDefault as $kategori) {
                $kmTurun = \Illuminate\Support\Facades\DB::table('km_lab')
                    ->where('id_lab', $idLab)
                    ->where('kategori_km', $kategori)
                    ->where('tahun_km', $tahun)
                    ->where('status_km', 'Aktif')
                    ->sum('jumlah_km');

                $kmAssign = \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                    ->where('km_lab.id_lab', $idLab)
                    ->where('km_lab.kategori_km', $kategori)
                    ->where('km_lab.tahun_km', $tahun)
                    ->where('km_lab.status_km', 'Aktif')
                    ->sum('km_anggota.jumlah_km');

                $realisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                    ->where('id_lab', $idLab)
                    ->where('kategori_km', $kategori)
                    ->whereYear('tanggal_mulai', $tahun)
                    ->count();

                $kategoriLabels[] = $kategori;
                $kategoriKmTurun[] = $kmTurun;
                $kategoriKmAssign[] = $kmAssign;
                $kategoriRealisasi[] = $realisasi;
            }

            $anggota = \Illuminate\Support\Facades\DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->where('users.role', 'Anggota')
                ->where('users.id_lab', $idLab)
                ->select(
                    'users.id_user',
                    'users.username',
                    'dosen.nama_dosen'
                )
                ->orderBy('dosen.nama_dosen')
                ->get();

            $anggotaLabels = [];
            $anggotaKmAssign = [];
            $anggotaRealisasi = [];

            foreach ($anggota as $item) {
                $totalAssign = \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                    ->where('km_anggota.id_user', $item->id_user)
                    ->where('km_lab.id_lab', $idLab)
                    ->where('km_lab.tahun_km', $tahun)
                    ->where('km_lab.status_km', 'Aktif')
                    ->sum('km_anggota.jumlah_km');

                $totalRealisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                    ->where('id_user', $item->id_user)
                    ->whereYear('tanggal_mulai', $tahun)
                    ->count();

                $anggotaLabels[] = $item->nama_dosen ?? $item->username;
                $anggotaKmAssign[] = $totalAssign;
                $anggotaRealisasi[] = $totalRealisasi;
            }

            $jumlahAnggota = $anggota->count();
            $totalKmTurun = array_sum($kategoriKmTurun);
            $totalKmAssign = array_sum($kategoriKmAssign);
            $totalRealisasi = array_sum($kategoriRealisasi);
            $totalSisaAssign = max($totalKmTurun - $totalKmAssign, 0);

            $persentaseAssign = $totalKmTurun > 0
                ? round(($totalKmAssign / $totalKmTurun) * 100)
                : 0;

            $persentaseRealisasi = $totalKmAssign > 0
                ? round(($totalRealisasi / $totalKmAssign) * 100)
                : 0;

            $statusAssignLabels = ['Sudah Assign', 'Sisa Assign'];
            $statusAssignData = [$totalKmAssign, $totalSisaAssign];

            return view('ketualab.dashboard', compact(
                'tahun',
                'lab',
                'jumlahAnggota',
                'totalKmTurun',
                'totalKmAssign',
                'totalSisaAssign',
                'totalRealisasi',
                'persentaseAssign',
                'persentaseRealisasi',
                'kategoriLabels',
                'kategoriKmTurun',
                'kategoriKmAssign',
                'kategoriRealisasi',
                'anggotaLabels',
                'anggotaKmAssign',
                'anggotaRealisasi',
                'statusAssignLabels',
                'statusAssignData'
            ));
        });
        Route::get('/ketualab/penurunan-km', [KetuaLabController::class, 'pembagianKmAnggota']);
        Route::post('/ketualab/penurunan-km', [KetuaLabController::class, 'simpanPembagianKmAnggota']);
        Route::delete('/ketualab/penurunan-km/assign/{id}', function ($id) {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $assign = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->where('km_anggota.id_km_anggota', $id)
                ->where('km_lab.id_lab', $user->id_lab)
                ->select('km_anggota.id_km_anggota')
                ->first();

            if (!$assign) {
                return redirect('/ketualab/penurunan-km')
                    ->with('error', 'Data assign KM tidak ditemukan atau bukan milik lab Anda.');
            }

            \Illuminate\Support\Facades\DB::table('km_anggota')
                ->where('id_km_anggota', $id)
                ->delete();

            return redirect('/ketualab/penurunan-km')
                ->with('success', 'Assign KM anggota berhasil dihapus.');
        });
        Route::get('/ketualab/penurunan-km/{id}/plot', [KetuaLabController::class, 'createPlot']);
        Route::post('/ketualab/penurunan-km/{id}/plot', [KetuaLabController::class, 'storePlot']);
        Route::get('/ketualab/monitoring-lab', function (\Illuminate\Http\Request $request) {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $idLab = $user->id_lab;
            $tahun = (int) $request->query('tahun', now()->year);
            $periode = $request->query('periode', 'tahun');

            $bulan = (int) $request->query('bulan', now()->month);
            $bulan = max(1, min(12, $bulan));

            $triwulan = (int) $request->query('triwulan', ceil(now()->month / 3));
            $triwulan = max(1, min(4, $triwulan));

            $semester = (int) $request->query('semester', now()->month <= 6 ? 1 : 2);
            $semester = max(1, min(2, $semester));

            if ($periode === 'bulan') {
                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulan, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth();
                $jumlahBulan = 1;
                $labelPeriode = 'Bulanan - Bulan ' . $bulan . ' Tahun ' . $tahun;
            } elseif ($periode === 'triwulan') {
                $bulanMulai = (($triwulan - 1) * 3) + 1;
                $bulanSelesai = $bulanMulai + 2;

                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
                $jumlahBulan = 3;
                $labelPeriode = 'Triwulan ' . $triwulan . ' Tahun ' . $tahun;
            } elseif ($periode === 'semester') {
                $bulanMulai = $semester === 1 ? 1 : 7;
                $bulanSelesai = $semester === 1 ? 6 : 12;

                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
                $jumlahBulan = 6;
                $labelPeriode = 'Semester ' . $semester . ' Tahun ' . $tahun;
            } else {
                $periode = 'tahun';
                $tanggalMulai = \Carbon\Carbon::create($tahun, 1, 1)->startOfYear();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, 12, 31)->endOfYear();
                $jumlahBulan = 12;
                $labelPeriode = 'Tahunan ' . $tahun;
            }

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $idLab)
                ->first();

            $jumlahAnggota = \Illuminate\Support\Facades\DB::table('users')
                ->where('role', 'Anggota')
                ->where('id_lab', $idLab)
                ->count();

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $kmTurunPerKategori = \Illuminate\Support\Facades\DB::table('km_lab')
                ->select(
                    'kategori_km',
                    \Illuminate\Support\Facades\DB::raw('SUM(jumlah_km) as total_km')
                )
                ->where('id_lab', $idLab)
                ->where('tahun_km', $tahun)
                ->where('status_km', 'Aktif')
                ->groupBy('kategori_km')
                ->pluck('total_km', 'kategori_km');

            $kmAssignPerKategori = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->select(
                    'km_lab.kategori_km',
                    \Illuminate\Support\Facades\DB::raw('SUM(km_anggota.jumlah_km) as total_assign')
                )
                ->where('km_lab.id_lab', $idLab)
                ->where('km_lab.tahun_km', $tahun)
                ->where('km_lab.status_km', 'Aktif')
                ->groupBy('km_lab.kategori_km')
                ->pluck('total_assign', 'kategori_km');

            $aktivitasPerKategori = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->select(
                    'kategori_km',
                    \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_realisasi')
                )
                ->where('id_lab', $idLab)
                ->whereBetween('tanggal_mulai', [
                    $tanggalMulai->toDateString(),
                    $tanggalSelesai->toDateString(),
                ])
                ->groupBy('kategori_km')
                ->pluck('total_realisasi', 'kategori_km');

            $rekap = [];

            foreach ($kategoriDefault as $kategori) {
                $kmTurun = $kmTurunPerKategori[$kategori] ?? 0;
                $kmAssign = $kmAssignPerKategori[$kategori] ?? 0;
                $sisaAssign = max($kmTurun - $kmAssign, 0);

                $targetPeriode = $kmAssign > 0
                    ? (int) ceil(($kmAssign * $jumlahBulan) / 12)
                    : 0;

                $realisasi = $aktivitasPerKategori[$kategori] ?? 0;

                $persentase = $targetPeriode > 0
                    ? round(($realisasi / $targetPeriode) * 100)
                    : 0;

                $rekap[] = [
                    'kategori' => $kategori,
                    'km_turun' => $kmTurun,
                    'km_assign' => $kmAssign,
                    'sisa_assign' => $sisaAssign,
                    'target_periode' => $targetPeriode,
                    'realisasi' => $realisasi,
                    'persentase' => min($persentase, 100),
                    'status' => $targetPeriode > 0 && $realisasi >= $targetPeriode
                        ? 'Tercapai'
                        : 'Belum Tercapai',
                ];
            }

            $totalKmTurun = array_sum(array_column($rekap, 'km_turun'));
            $totalKmAssign = array_sum(array_column($rekap, 'km_assign'));
            $totalSisaAssign = array_sum(array_column($rekap, 'sisa_assign'));
            $totalTargetPeriode = array_sum(array_column($rekap, 'target_periode'));
            $totalRealisasiPeriode = array_sum(array_column($rekap, 'realisasi'));

            $persentaseAssign = $totalKmTurun > 0
                ? round(($totalKmAssign / $totalKmTurun) * 100)
                : 0;

            $persentaseTotal = $totalTargetPeriode > 0
                ? round(($totalRealisasiPeriode / $totalTargetPeriode) * 100)
                : 0;

            return view('ketualab.monitoring-lab', compact(
                'lab',
                'jumlahAnggota',
                'tahun',
                'periode',
                'bulan',
                'triwulan',
                'semester',
                'tanggalMulai',
                'tanggalSelesai',
                'labelPeriode',
                'rekap',
                'totalKmTurun',
                'totalKmAssign',
                'totalSisaAssign',
                'totalTargetPeriode',
                'totalRealisasiPeriode',
                'persentaseAssign',
                'persentaseTotal'
            ));
        });
        Route::get('/ketualab/monitoring-anggota', function (\Illuminate\Http\Request $request) {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $idLab = $user->id_lab;
            $tahun = (int) $request->query('tahun', now()->year);
            $periode = $request->query('periode', 'tahun');

            $bulan = (int) $request->query('bulan', now()->month);
            $bulan = max(1, min(12, $bulan));

            $triwulan = (int) $request->query('triwulan', ceil(now()->month / 3));
            $triwulan = max(1, min(4, $triwulan));

            $semester = (int) $request->query('semester', now()->month <= 6 ? 1 : 2);
            $semester = max(1, min(2, $semester));

            if ($periode === 'bulan') {
                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulan, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth();
                $jumlahBulan = 1;
                $labelPeriode = 'Bulanan - Bulan ' . $bulan . ' Tahun ' . $tahun;
            } elseif ($periode === 'triwulan') {
                $bulanMulai = (($triwulan - 1) * 3) + 1;
                $bulanSelesai = $bulanMulai + 2;

                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
                $jumlahBulan = 3;
                $labelPeriode = 'Triwulan ' . $triwulan . ' Tahun ' . $tahun;
            } elseif ($periode === 'semester') {
                $bulanMulai = $semester === 1 ? 1 : 7;
                $bulanSelesai = $semester === 1 ? 6 : 12;

                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
                $jumlahBulan = 6;
                $labelPeriode = 'Semester ' . $semester . ' Tahun ' . $tahun;
            } else {
                $periode = 'tahun';
                $tanggalMulai = \Carbon\Carbon::create($tahun, 1, 1)->startOfYear();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, 12, 31)->endOfYear();
                $jumlahBulan = 12;
                $labelPeriode = 'Tahunan ' . $tahun;
            }

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $idLab)
                ->first();

            $anggota = \Illuminate\Support\Facades\DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->where('users.role', 'Anggota')
                ->where('users.id_lab', $idLab)
                ->select(
                    'users.id_user',
                    'users.id_dosen',
                    'users.username',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'dosen.jad'
                )
                ->orderBy('dosen.nama_dosen')
                ->get();

            $dataMonitoring = [];

            foreach ($anggota as $item) {
                $totalKmAssign = \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                    ->where('km_anggota.id_user', $item->id_user)
                    ->where('km_lab.id_lab', $idLab)
                    ->where('km_lab.tahun_km', $tahun)
                    ->where('km_lab.status_km', 'Aktif')
                    ->sum('km_anggota.jumlah_km');

                $targetPeriode = $totalKmAssign > 0
                    ? (int) ceil(($totalKmAssign * $jumlahBulan) / 12)
                    : 0;

                $totalRealisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                    ->where('id_user', $item->id_user)
                    ->whereBetween('tanggal_mulai', [
                        $tanggalMulai->toDateString(),
                        $tanggalSelesai->toDateString(),
                    ])
                    ->count();

                $sisa = max($targetPeriode - $totalRealisasi, 0);

                $persentase = $targetPeriode > 0
                    ? round(($totalRealisasi / $targetPeriode) * 100)
                    : 0;

                $dataMonitoring[] = [
                    'id_user' => $item->id_user,
                    'username' => $item->username,
                    'nama_dosen' => $item->nama_dosen ?? $item->username,
                    'nidn' => $item->nidn ?? '-',
                    'email' => $item->email ?? '-',
                    'jad' => $item->jad ?? 'AA',
                    'total_km_assign' => $totalKmAssign,
                    'target_periode' => $targetPeriode,
                    'total_realisasi' => $totalRealisasi,
                    'sisa' => $sisa,
                    'persentase' => min($persentase, 100),
                    'status' => $targetPeriode > 0 && $sisa <= 0
                        ? 'Tercapai'
                        : 'Belum Tercapai',
                ];
            }

            $jumlahAnggota = count($dataMonitoring);
            $totalKmAssignLab = array_sum(array_column($dataMonitoring, 'total_km_assign'));
            $totalTargetPeriode = array_sum(array_column($dataMonitoring, 'target_periode'));
            $totalRealisasiPeriode = array_sum(array_column($dataMonitoring, 'total_realisasi'));
            $totalSisa = array_sum(array_column($dataMonitoring, 'sisa'));

            $persentaseTotal = $totalTargetPeriode > 0
                ? round(($totalRealisasiPeriode / $totalTargetPeriode) * 100)
                : 0;

            return view('ketualab.monitoring-anggota', compact(
                'lab',
                'tahun',
                'periode',
                'bulan',
                'triwulan',
                'semester',
                'tanggalMulai',
                'tanggalSelesai',
                'labelPeriode',
                'dataMonitoring',
                'jumlahAnggota',
                'totalKmAssignLab',
                'totalTargetPeriode',
                'totalRealisasiPeriode',
                'totalSisa',
                'persentaseTotal'
            ));
        });
        Route::get('/ketualab/detail-anggota/{id}', function (\Illuminate\Http\Request $request, $id) {
            /** @var \App\Models\User $ketuaLab */
            $ketuaLab = auth()->user();

            $idLab = $ketuaLab->id_lab;
            $tahun = (int) $request->query('tahun', now()->year);
            $periode = $request->query('periode', 'tahun');

            $bulan = (int) $request->query('bulan', now()->month);
            $bulan = max(1, min(12, $bulan));

            $triwulan = (int) $request->query('triwulan', ceil(now()->month / 3));
            $triwulan = max(1, min(4, $triwulan));

            $semester = (int) $request->query('semester', now()->month <= 6 ? 1 : 2);
            $semester = max(1, min(2, $semester));

            if ($periode === 'bulan') {
                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulan, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth();
                $jumlahBulan = 1;
                $labelPeriode = 'Bulanan - Bulan ' . $bulan . ' Tahun ' . $tahun;
            } elseif ($periode === 'triwulan') {
                $bulanMulai = (($triwulan - 1) * 3) + 1;
                $bulanSelesai = $bulanMulai + 2;

                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
                $jumlahBulan = 3;
                $labelPeriode = 'Triwulan ' . $triwulan . ' Tahun ' . $tahun;
            } elseif ($periode === 'semester') {
                $bulanMulai = $semester === 1 ? 1 : 7;
                $bulanSelesai = $semester === 1 ? 6 : 12;

                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
                $jumlahBulan = 6;
                $labelPeriode = 'Semester ' . $semester . ' Tahun ' . $tahun;
            } else {
                $periode = 'tahun';
                $tanggalMulai = \Carbon\Carbon::create($tahun, 1, 1)->startOfYear();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, 12, 31)->endOfYear();
                $jumlahBulan = 12;
                $labelPeriode = 'Tahunan ' . $tahun;
            }

            $anggota = \Illuminate\Support\Facades\DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->leftJoin('laboratorium_riset', 'users.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('users.id_user', $id)
                ->where('users.role', 'Anggota')
                ->where('users.id_lab', $idLab)
                ->select(
                    'users.id_user',
                    'users.id_dosen',
                    'users.username',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'dosen.jad',
                    'laboratorium_riset.nama_lab'
                )
                ->first();

            if (!$anggota) {
                abort(404);
            }

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $targetAssignPerKategori = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->select(
                    'km_lab.kategori_km',
                    \Illuminate\Support\Facades\DB::raw('SUM(km_anggota.jumlah_km) as total_target')
                )
                ->where('km_anggota.id_user', $anggota->id_user)
                ->where('km_lab.tahun_km', $tahun)
                ->where('km_lab.status_km', 'Aktif')
                ->groupBy('km_lab.kategori_km')
                ->pluck('total_target', 'kategori_km');

            $aktivitasPerKategori = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->select(
                    'kategori_km',
                    \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_realisasi')
                )
                ->where('id_user', $anggota->id_user)
                ->whereBetween('tanggal_mulai', [
                    $tanggalMulai->toDateString(),
                    $tanggalSelesai->toDateString(),
                ])
                ->groupBy('kategori_km')
                ->pluck('total_realisasi', 'kategori_km');

            $rekap = [];

            foreach ($kategoriDefault as $kategori) {
                $targetTahunan = $targetAssignPerKategori[$kategori] ?? 0;

                $targetPeriode = $targetTahunan > 0
                    ? (int) ceil(($targetTahunan * $jumlahBulan) / 12)
                    : 0;

                $realisasi = $aktivitasPerKategori[$kategori] ?? 0;
                $sisa = max($targetPeriode - $realisasi, 0);

                $persentase = $targetPeriode > 0
                    ? round(($realisasi / $targetPeriode) * 100)
                    : 0;

                $rekap[] = [
                    'kategori' => $kategori,
                    'target_tahunan' => $targetTahunan,
                    'target_periode' => $targetPeriode,
                    'realisasi' => $realisasi,
                    'sisa' => $sisa,
                    'persentase' => min($persentase, 100),
                    'status' => $targetPeriode > 0 && $sisa <= 0
                        ? 'Tercapai'
                        : 'Belum Tercapai',
                ];
            }

            $totalTargetTahunan = array_sum(array_column($rekap, 'target_tahunan'));
            $totalTargetPeriode = array_sum(array_column($rekap, 'target_periode'));
            $totalRealisasi = array_sum(array_column($rekap, 'realisasi'));
            $totalSisa = array_sum(array_column($rekap, 'sisa'));

            $persentaseTotal = $totalTargetPeriode > 0
                ? round(($totalRealisasi / $totalTargetPeriode) * 100)
                : 0;

            $riwayatAssign = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->where('km_anggota.id_user', $anggota->id_user)
                ->where('km_lab.tahun_km', $tahun)
                ->select(
                    'km_anggota.jumlah_km',
                    'km_anggota.created_at',
                    'km_lab.kategori_km',
                    'km_lab.tahun_km',
                    'km_lab.status_km'
                )
                ->orderBy('km_anggota.created_at', 'desc')
                ->get();

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_user', $anggota->id_user)
                ->whereBetween('tanggal_mulai', [
                    $tanggalMulai->toDateString(),
                    $tanggalSelesai->toDateString(),
                ])
                ->orderBy('tanggal_mulai', 'desc')
                ->get();

            return view('ketualab.detail-anggota', compact(
                'anggota',
                'tahun',
                'periode',
                'bulan',
                'triwulan',
                'semester',
                'tanggalMulai',
                'tanggalSelesai',
                'labelPeriode',
                'rekap',
                'riwayatAssign',
                'aktivitas',
                'totalTargetTahunan',
                'totalTargetPeriode',
                'totalRealisasi',
                'totalSisa',
                'persentaseTotal'
            ));
        });
        Route::get('/ketualab/laporan', function (\Illuminate\Http\Request $request) {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $idLab = $user->id_lab;
            $tahun = (int) $request->query('tahun', now()->year);
            $periode = $request->query('periode', 'tahun');

            $bulan = (int) $request->query('bulan', now()->month);
            $bulan = max(1, min(12, $bulan));

            $triwulan = (int) $request->query('triwulan', ceil(now()->month / 3));
            $triwulan = max(1, min(4, $triwulan));

            $semester = (int) $request->query('semester', now()->month <= 6 ? 1 : 2);
            $semester = max(1, min(2, $semester));

            if ($periode === 'bulan') {
                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulan, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth();
                $jumlahBulan = 1;
                $labelPeriode = 'Bulanan - Bulan ' . $bulan . ' Tahun ' . $tahun;
            } elseif ($periode === 'triwulan') {
                $bulanMulai = (($triwulan - 1) * 3) + 1;
                $bulanSelesai = $bulanMulai + 2;

                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
                $jumlahBulan = 3;
                $labelPeriode = 'Triwulan ' . $triwulan . ' Tahun ' . $tahun;
            } elseif ($periode === 'semester') {
                $bulanMulai = $semester === 1 ? 1 : 7;
                $bulanSelesai = $semester === 1 ? 6 : 12;

                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
                $jumlahBulan = 6;
                $labelPeriode = 'Semester ' . $semester . ' Tahun ' . $tahun;
            } else {
                $periode = 'tahun';
                $tanggalMulai = \Carbon\Carbon::create($tahun, 1, 1)->startOfYear();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, 12, 31)->endOfYear();
                $jumlahBulan = 12;
                $labelPeriode = 'Tahunan ' . $tahun;
            }

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $idLab)
                ->first();

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $rekapKategori = [];

            foreach ($kategoriDefault as $kategori) {
                $kmTurun = \Illuminate\Support\Facades\DB::table('km_lab')
                    ->where('id_lab', $idLab)
                    ->where('kategori_km', $kategori)
                    ->where('tahun_km', $tahun)
                    ->where('status_km', 'Aktif')
                    ->sum('jumlah_km');

                $kmAssign = \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                    ->where('km_lab.id_lab', $idLab)
                    ->where('km_lab.kategori_km', $kategori)
                    ->where('km_lab.tahun_km', $tahun)
                    ->where('km_lab.status_km', 'Aktif')
                    ->sum('km_anggota.jumlah_km');

                $targetPeriode = $kmAssign > 0
                    ? (int) ceil(($kmAssign * $jumlahBulan) / 12)
                    : 0;

                $realisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                    ->where('id_lab', $idLab)
                    ->where('kategori_km', $kategori)
                    ->whereBetween('tanggal_mulai', [
                        $tanggalMulai->toDateString(),
                        $tanggalSelesai->toDateString(),
                    ])
                    ->count();

                $sisaAssign = max($kmTurun - $kmAssign, 0);
                $sisaRealisasi = max($targetPeriode - $realisasi, 0);

                $persentase = $targetPeriode > 0
                    ? round(($realisasi / $targetPeriode) * 100)
                    : 0;

                $rekapKategori[] = [
                    'kategori' => $kategori,
                    'km_turun' => $kmTurun,
                    'km_assign' => $kmAssign,
                    'sisa_assign' => $sisaAssign,
                    'target_periode' => $targetPeriode,
                    'realisasi' => $realisasi,
                    'sisa_realisasi' => $sisaRealisasi,
                    'persentase' => min($persentase, 100),
                    'status' => $targetPeriode > 0 && $sisaRealisasi <= 0
                        ? 'Tercapai'
                        : 'Belum Tercapai',
                ];
            }

            $anggota = \Illuminate\Support\Facades\DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->where('users.role', 'Anggota')
                ->where('users.id_lab', $idLab)
                ->select(
                    'users.id_user',
                    'users.username',
                    'users.id_dosen',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'dosen.jad'
                )
                ->orderBy('dosen.nama_dosen')
                ->get();

            $rekapAnggota = [];

            foreach ($anggota as $item) {
                $kmAssign = \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                    ->where('km_anggota.id_user', $item->id_user)
                    ->where('km_lab.id_lab', $idLab)
                    ->where('km_lab.tahun_km', $tahun)
                    ->where('km_lab.status_km', 'Aktif')
                    ->sum('km_anggota.jumlah_km');

                $targetPeriode = $kmAssign > 0
                    ? (int) ceil(($kmAssign * $jumlahBulan) / 12)
                    : 0;

                $realisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                    ->where('id_user', $item->id_user)
                    ->whereBetween('tanggal_mulai', [
                        $tanggalMulai->toDateString(),
                        $tanggalSelesai->toDateString(),
                    ])
                    ->count();

                $sisa = max($targetPeriode - $realisasi, 0);

                $persentase = $targetPeriode > 0
                    ? round(($realisasi / $targetPeriode) * 100)
                    : 0;

                $rekapAnggota[] = [
                    'nama_dosen' => $item->nama_dosen ?? $item->username,
                    'nidn' => $item->nidn ?? '-',
                    'jad' => $item->jad ?? 'AA',
                    'km_assign' => $kmAssign,
                    'target_periode' => $targetPeriode,
                    'realisasi' => $realisasi,
                    'sisa' => $sisa,
                    'persentase' => min($persentase, 100),
                    'status' => $targetPeriode > 0 && $sisa <= 0
                        ? 'Tercapai'
                        : 'Belum Tercapai',
                ];
            }

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->leftJoin('users', 'aktivitas_km.id_user', '=', 'users.id_user')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->where('aktivitas_km.id_lab', $idLab)
                ->whereBetween('aktivitas_km.tanggal_mulai', [
                    $tanggalMulai->toDateString(),
                    $tanggalSelesai->toDateString(),
                ])
                ->select(
                    'aktivitas_km.kategori_km',
                    'aktivitas_km.judul_aktivitas',
                    'aktivitas_km.deskripsi_singkat',
                    'aktivitas_km.tanggal_mulai',
                    'aktivitas_km.tanggal_selesai',
                    'users.username',
                    'dosen.nama_dosen',
                    'dosen.nidn'
                )
                ->orderBy('aktivitas_km.tanggal_mulai', 'desc')
                ->get();

            $jumlahAnggota = count($rekapAnggota);
            $totalKmTurun = array_sum(array_column($rekapKategori, 'km_turun'));
            $totalKmAssign = array_sum(array_column($rekapKategori, 'km_assign'));
            $totalSisaAssign = array_sum(array_column($rekapKategori, 'sisa_assign'));
            $totalTargetPeriode = array_sum(array_column($rekapKategori, 'target_periode'));
            $totalRealisasi = array_sum(array_column($rekapKategori, 'realisasi'));
            $totalSisaRealisasi = array_sum(array_column($rekapKategori, 'sisa_realisasi'));

            $persentaseRealisasi = $totalTargetPeriode > 0
                ? round(($totalRealisasi / $totalTargetPeriode) * 100)
                : 0;

            return view('ketualab.laporan', compact(
                'lab',
                'tahun',
                'periode',
                'bulan',
                'triwulan',
                'semester',
                'tanggalMulai',
                'tanggalSelesai',
                'labelPeriode',
                'rekapKategori',
                'rekapAnggota',
                'aktivitas',
                'jumlahAnggota',
                'totalKmTurun',
                'totalKmAssign',
                'totalSisaAssign',
                'totalTargetPeriode',
                'totalRealisasi',
                'totalSisaRealisasi',
                'persentaseRealisasi'
            ));
        });
        Route::get('/ketualab/profil', function () {
            $user = auth()->user();

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $user->id_lab)
                ->first();

            return view('ketualab.profil', compact('user', 'lab'));
        });
    });


    // RUANG KHUSUS ANGGOTA
    Route::middleware(['auth', 'role:Anggota'])->group(function () {
        Route::get('/anggota/dashboard', function () {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $idUser = $user->id_user;
            $tahun = now()->year;

            $anggota = \Illuminate\Support\Facades\DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->leftJoin('laboratorium_riset', 'users.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('users.id_user', $idUser)
                ->select(
                    'users.id_user',
                    'users.username',
                    'users.id_dosen',
                    'users.id_lab',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'dosen.jad',
                    'laboratorium_riset.nama_lab'
                )
                ->first();

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $kategoriLabels = [];
            $kategoriTarget = [];
            $kategoriRealisasi = [];
            $kategoriSisa = [];

            foreach ($kategoriDefault as $kategori) {
                $target = \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                    ->where('km_anggota.id_user', $idUser)
                    ->where('km_lab.kategori_km', $kategori)
                    ->where('km_lab.tahun_km', $tahun)
                    ->where('km_lab.status_km', 'Aktif')
                    ->sum('km_anggota.jumlah_km');

                $realisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                    ->where('id_user', $idUser)
                    ->where('kategori_km', $kategori)
                    ->whereYear('tanggal_mulai', $tahun)
                    ->count();

                $sisa = max($target - $realisasi, 0);

                $kategoriLabels[] = $kategori;
                $kategoriTarget[] = $target;
                $kategoriRealisasi[] = $realisasi;
                $kategoriSisa[] = $sisa;
            }

            $bulanLabels = [
                'Jan',
                'Feb',
                'Mar',
                'Apr',
                'Mei',
                'Jun',
                'Jul',
                'Agu',
                'Sep',
                'Okt',
                'Nov',
                'Des'
            ];

            $aktivitasBulanan = [];

            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $jumlahAktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                    ->where('id_user', $idUser)
                    ->whereYear('tanggal_mulai', $tahun)
                    ->whereMonth('tanggal_mulai', $bulan)
                    ->count();

                $aktivitasBulanan[] = $jumlahAktivitas;
            }

            $totalTarget = array_sum($kategoriTarget);
            $totalRealisasi = array_sum($kategoriRealisasi);
            $totalSisa = max($totalTarget - $totalRealisasi, 0);

            $persentaseTotal = $totalTarget > 0
                ? round(($totalRealisasi / $totalTarget) * 100)
                : 0;

            $statusProgressLabels = ['Realisasi', 'Sisa'];
            $statusProgressData = [
                min($totalRealisasi, $totalTarget),
                $totalSisa
            ];

            $riwayatAssign = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->where('km_anggota.id_user', $idUser)
                ->where('km_lab.tahun_km', $tahun)
                ->select(
                    'km_anggota.jumlah_km',
                    'km_anggota.created_at',
                    'km_lab.kategori_km',
                    'km_lab.tahun_km',
                    'km_lab.status_km'
                )
                ->orderBy('km_anggota.created_at', 'desc')
                ->limit(5)
                ->get();

            $aktivitasTerbaru = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_user', $idUser)
                ->whereYear('tanggal_mulai', $tahun)
                ->orderBy('tanggal_mulai', 'desc')
                ->limit(5)
                ->get();

            return view('anggota.dashboard', compact(
                'tahun',
                'anggota',
                'totalTarget',
                'totalRealisasi',
                'totalSisa',
                'persentaseTotal',
                'kategoriLabels',
                'kategoriTarget',
                'kategoriRealisasi',
                'kategoriSisa',
                'bulanLabels',
                'aktivitasBulanan',
                'statusProgressLabels',
                'statusProgressData',
                'riwayatAssign',
                'aktivitasTerbaru'
            ));
        });
        Route::get('/anggota/realisasi-km', [AnggotaController::class, 'indexRealisasi']);
        Route::get('/anggota/realisasi-km/{id}/edit', [AnggotaController::class, 'editRealisasi']);
        Route::put('/anggota/realisasi-km/{id}', [AnggotaController::class, 'updateRealisasi']);
        Route::get('/anggota/aktivitas-km', function () {
            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_user', auth()->user()->id_user)
                ->orderBy('tanggal_mulai', 'desc')
                ->get();

            return view('anggota.aktivitas-km.index', compact('aktivitas'));
        });
        Route::get('/anggota/riwayat-realisasi', function () {
            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_user', auth()->user()->id_user)
                ->orderBy('tanggal_mulai', 'desc')
                ->get();

            return view('anggota.riwayat-realisasi', compact('aktivitas'));
        });
        Route::get('/anggota/profil', function () {
            $user = auth()->user();

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $user->id_lab)
                ->first();

            $dosen = null;

            if ($user->id_dosen) {
                $dosen = \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_dosen', $user->id_dosen)
                    ->first();
            }

            return view('anggota.profil', compact('user', 'lab', 'dosen'));
        });
        Route::get('/anggota/aktivitas-km/create', function () {
            $user = auth()->user();

            $kmOptions = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->leftJoin('laboratorium_riset', 'km_lab.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('km_anggota.id_user', $user->id_user)
                ->select(
                    'km_anggota.id_km_anggota',
                    'km_anggota.jumlah_km as jumlah_km_anggota',
                    'km_lab.id_lab',
                    'km_lab.tahun_km',
                    'km_lab.kategori_km',
                    'km_lab.sub_kategori_km',
                    'laboratorium_riset.nama_lab'
                )
                ->orderByDesc('km_lab.tahun_km')
                ->orderBy('km_lab.kategori_km')
                ->get();

            return view('anggota.aktivitas-km.create', compact('kmOptions'));
        });

        Route::post('/anggota/aktivitas-km', function (\Illuminate\Http\Request $request) {
            $request->validate([
                'id_km_anggota' => 'required|integer|exists:km_anggota,id_km_anggota',
                'judul_aktivitas' => 'required|string|max:255',
                'deskripsi_singkat' => 'nullable|string',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'bukti_link' => 'nullable|url|max:255',
                'status_progress' => 'required|in:On Progress,Submitted,Accepted,Rejected',
            ]);

            $user = auth()->user();

            $kmAnggota = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->where('km_anggota.id_km_anggota', $request->id_km_anggota)
                ->where('km_anggota.id_user', $user->id_user)
                ->select(
                    'km_anggota.id_km_anggota',
                    'km_lab.id_lab',
                    'km_lab.kategori_km',
                    'km_lab.sub_kategori_km'
                )
                ->first();

            if (! $kmAnggota) {
                return back()
                    ->withErrors(['id_km_anggota' => 'KM yang dipilih tidak ditemukan atau bukan milik Anda.'])
                    ->withInput();
            }

            \Illuminate\Support\Facades\DB::table('aktivitas_km')->insert([
                'id_user' => $user->id_user,
                'id_lab' => $kmAnggota->id_lab,
                'id_km_anggota' => $kmAnggota->id_km_anggota,
                'kategori_km' => $kmAnggota->kategori_km,
                'sub_kategori_km' => $kmAnggota->sub_kategori_km,
                'judul_aktivitas' => $request->judul_aktivitas,
                'deskripsi_singkat' => $request->deskripsi_singkat,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'bukti_link' => $request->bukti_link,
                'status_progress' => $request->status_progress,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect('/anggota/aktivitas-km')->with('success', 'Aktivitas KM berhasil ditambahkan.');
        });
        Route::get('/anggota/aktivitas-km/{id}/edit', function ($id) {
            $user = auth()->user();

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_aktivitas', $id)
                ->where('id_user', $user->id_user)
                ->first();

            if (! $aktivitas) {
                abort(404);
            }

            $kmOptions = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->leftJoin('laboratorium_riset', 'km_lab.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('km_anggota.id_user', $user->id_user)
                ->select(
                    'km_anggota.id_km_anggota',
                    'km_anggota.jumlah_km as jumlah_km_anggota',
                    'km_lab.id_lab',
                    'km_lab.tahun_km',
                    'km_lab.kategori_km',
                    'km_lab.sub_kategori_km',
                    'laboratorium_riset.nama_lab'
                )
                ->orderByDesc('km_lab.tahun_km')
                ->orderBy('km_lab.kategori_km')
                ->get();

            return view('anggota.aktivitas-km.edit', compact('aktivitas', 'kmOptions'));
        });

        Route::put('/anggota/aktivitas-km/{id}', function (\Illuminate\Http\Request $request, $id) {
            $request->validate([
                'id_km_anggota' => 'required|integer|exists:km_anggota,id_km_anggota',
                'judul_aktivitas' => 'required|string|max:255',
                'deskripsi_singkat' => 'nullable|string',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'bukti_link' => 'nullable|url|max:255',
                'status_progress' => 'required|in:On Progress,Submitted,Accepted,Rejected',
            ]);

            $user = auth()->user();

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_aktivitas', $id)
                ->where('id_user', $user->id_user)
                ->first();

            if (! $aktivitas) {
                abort(404);
            }

            $kmAnggota = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->where('km_anggota.id_km_anggota', $request->id_km_anggota)
                ->where('km_anggota.id_user', $user->id_user)
                ->select(
                    'km_anggota.id_km_anggota',
                    'km_lab.id_lab',
                    'km_lab.kategori_km',
                    'km_lab.sub_kategori_km'
                )
                ->first();

            if (! $kmAnggota) {
                return back()
                    ->withErrors(['id_km_anggota' => 'KM yang dipilih tidak ditemukan atau bukan milik Anda.'])
                    ->withInput();
            }

            \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_aktivitas', $id)
                ->where('id_user', $user->id_user)
                ->update([
                    'id_lab' => $kmAnggota->id_lab,
                    'id_km_anggota' => $kmAnggota->id_km_anggota,
                    'kategori_km' => $kmAnggota->kategori_km,
                    'sub_kategori_km' => $kmAnggota->sub_kategori_km,
                    'judul_aktivitas' => $request->judul_aktivitas,
                    'deskripsi_singkat' => $request->deskripsi_singkat,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                    'bukti_link' => $request->bukti_link,
                    'status_progress' => $request->status_progress,
                    'updated_at' => now(),
                ]);

            return redirect('/anggota/aktivitas-km')->with('success', 'Aktivitas KM berhasil diperbarui.');
        });
        Route::get('/anggota/progress-km', function () {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $idUser = $user->id_user;
            $tahun = (int) request('tahun', now()->year);

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $tahunOptions = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->where('km_anggota.id_user', $idUser)
                ->select('km_lab.tahun_km')
                ->distinct()
                ->orderBy('km_lab.tahun_km', 'desc')
                ->pluck('tahun_km');

            if ($tahunOptions->isEmpty()) {
                $tahunOptions = collect([now()->year]);
            }

            if (! $tahunOptions->contains($tahun)) {
                $tahunOptions->push($tahun);
                $tahunOptions = $tahunOptions->unique()->sortDesc()->values();
            }

            $daftarKm = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->leftJoin('laboratorium_riset', 'km_lab.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('km_anggota.id_user', $idUser)
                ->where('km_lab.tahun_km', $tahun)
                ->select(
                    'km_anggota.id_km_anggota',
                    'km_anggota.jumlah_km',
                    'km_lab.tahun_km',
                    'km_lab.kategori_km',
                    'km_lab.sub_kategori_km',
                    'km_lab.status_km',
                    'laboratorium_riset.nama_lab'
                )
                ->orderBy('km_lab.kategori_km')
                ->orderBy('km_lab.sub_kategori_km')
                ->get();

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_user', $idUser)
                ->whereNotNull('id_km_anggota')
                ->get()
                ->groupBy('id_km_anggota');

            $progressKategori = [];

            foreach ($kategoriDefault as $kategori) {
                $progressKategori[$kategori] = [
                    'kategori' => $kategori,
                    'target' => 0,
                    'realisasi' => 0,
                    'persentase' => 0,
                ];
            }

            $daftarProgressKm = [];

            foreach ($daftarKm as $km) {
                $aktivitasKm = $aktivitas->get($km->id_km_anggota, collect());

                $totalAktivitas = $aktivitasKm->count();

                $totalAccepted = $aktivitasKm
                    ->where('status_progress', 'Accepted')
                    ->count();

                $aktivitasTerakhir = $aktivitasKm
                    ->sortByDesc('updated_at')
                    ->first();

                $statusTerakhir = $aktivitasTerakhir->status_progress ?? 'Belum Mulai';

                $target = (int) $km->jumlah_km;
                $realisasi = (int) $totalAccepted;
                $sisa = max($target - $realisasi, 0);

                $persentase = $target > 0
                    ? min(round(($realisasi / $target) * 100), 100)
                    : 0;

                if ($target > 0 && $realisasi >= $target) {
                    $statusCapaian = 'Tercapai';
                } elseif ($totalAktivitas > 0) {
                    $statusCapaian = $statusTerakhir;
                } else {
                    $statusCapaian = 'Belum Mulai';
                }

                $daftarProgressKm[] = [
                    'id_km_anggota' => $km->id_km_anggota,
                    'tahun' => $km->tahun_km,
                    'lab' => $km->nama_lab ?? '-',
                    'kategori' => $km->kategori_km,
                    'sub_kategori' => $km->sub_kategori_km ?? '-',
                    'target' => $target,
                    'realisasi' => $realisasi,
                    'sisa' => $sisa,
                    'total_aktivitas' => $totalAktivitas,
                    'status_terakhir' => $statusTerakhir,
                    'status_capaian' => $statusCapaian,
                    'persentase' => $persentase,
                    'judul_terakhir' => $aktivitasTerakhir->judul_aktivitas ?? '-',
                    'bukti_link' => $aktivitasTerakhir->bukti_link ?? null,
                ];

                if (isset($progressKategori[$km->kategori_km])) {
                    $progressKategori[$km->kategori_km]['target'] += $target;
                    $progressKategori[$km->kategori_km]['realisasi'] += $realisasi;
                }
            }

            foreach ($progressKategori as $kategori => $item) {
                $target = $item['target'];
                $realisasi = $item['realisasi'];

                $progressKategori[$kategori]['persentase'] = $target > 0
                    ? min(round(($realisasi / $target) * 100), 100)
                    : 0;
            }

            $progressKategori = array_values($progressKategori);

            $totalTarget = array_sum(array_column($daftarProgressKm, 'target'));
            $totalRealisasi = array_sum(array_column($daftarProgressKm, 'realisasi'));
            $totalSisa = array_sum(array_column($daftarProgressKm, 'sisa'));

            $persentaseTotal = $totalTarget > 0
                ? min(round(($totalRealisasi / $totalTarget) * 100), 100)
                : 0;

            return view('anggota.progress-km', compact(
                'tahun',
                'tahunOptions',
                'progressKategori',
                'daftarProgressKm',
                'totalTarget',
                'totalRealisasi',
                'totalSisa',
                'persentaseTotal'
            ));
        });
    });
});
