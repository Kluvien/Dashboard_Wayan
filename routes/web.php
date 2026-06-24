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

                $kategoriLabels[] = $kategori;
                $kategoriTargets[] = $target;
                $kategoriRealisasi[] = $realisasi;
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
                'kategoriRealisasi'
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

            $labs = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            $monitoringLabs = [];

            foreach ($labs as $lab) {
                $jumlahDosen = \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_lab', $lab->id_lab)
                    ->count();

                $jumlahAnggota = \Illuminate\Support\Facades\DB::table('users')
                    ->where('role', 'Anggota')
                    ->where('id_lab', $lab->id_lab)
                    ->count();

                $totalKmTurun = \Illuminate\Support\Facades\DB::table('km_lab')
                    ->where('id_lab', $lab->id_lab)
                    ->where('tahun_km', $tahun)
                    ->where('status_km', 'Aktif')
                    ->sum('jumlah_km');

                $totalKmAssign = \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                    ->where('km_lab.id_lab', $lab->id_lab)
                    ->where('km_lab.tahun_km', $tahun)
                    ->where('km_lab.status_km', 'Aktif')
                    ->sum('km_anggota.jumlah_km');

                $sisaAssign = max($totalKmTurun - $totalKmAssign, 0);

                $targetPeriode = $totalKmAssign > 0
                    ? (int) ceil(($totalKmAssign * $jumlahBulan) / 12)
                    : 0;

                $totalRealisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                    ->where('id_lab', $lab->id_lab)
                    ->whereBetween('tanggal_mulai', [
                        $tanggalMulai->toDateString(),
                        $tanggalSelesai->toDateString(),
                    ])
                    ->count();

                $sisaRealisasi = max($targetPeriode - $totalRealisasi, 0);

                $persentaseAssign = $totalKmTurun > 0
                    ? round(($totalKmAssign / $totalKmTurun) * 100)
                    : 0;

                $persentaseRealisasi = $targetPeriode > 0
                    ? round(($totalRealisasi / $targetPeriode) * 100)
                    : 0;

                $monitoringLabs[] = [
                    'id_lab' => $lab->id_lab,
                    'nama_lab' => $lab->nama_lab,
                    'jumlah_dosen' => $jumlahDosen,
                    'jumlah_anggota' => $jumlahAnggota,
                    'total_km_turun' => $totalKmTurun,
                    'total_km_assign' => $totalKmAssign,
                    'sisa_assign' => $sisaAssign,
                    'target_periode' => $targetPeriode,
                    'total_realisasi' => $totalRealisasi,
                    'sisa_realisasi' => $sisaRealisasi,
                    'persentase_assign' => min($persentaseAssign, 100),
                    'persentase_realisasi' => min($persentaseRealisasi, 100),
                    'status' => $targetPeriode > 0 && $sisaRealisasi <= 0
                        ? 'Tercapai'
                        : 'Belum Tercapai',
                ];
            }

            $totalLab = count($monitoringLabs);
            $totalKmTurunAll = array_sum(array_column($monitoringLabs, 'total_km_turun'));
            $totalKmAssignAll = array_sum(array_column($monitoringLabs, 'total_km_assign'));
            $totalSisaAssignAll = array_sum(array_column($monitoringLabs, 'sisa_assign'));
            $totalTargetPeriode = array_sum(array_column($monitoringLabs, 'target_periode'));
            $totalRealisasiPeriode = array_sum(array_column($monitoringLabs, 'total_realisasi'));

            $persentaseAssignAll = $totalKmTurunAll > 0
                ? round(($totalKmAssignAll / $totalKmTurunAll) * 100)
                : 0;

            $persentaseRealisasiAll = $totalTargetPeriode > 0
                ? round(($totalRealisasiPeriode / $totalTargetPeriode) * 100)
                : 0;

            return view('ketuakk.monitoring-lab-riset.index', compact(
                'tahun',
                'periode',
                'bulan',
                'triwulan',
                'semester',
                'tanggalMulai',
                'tanggalSelesai',
                'labelPeriode',
                'monitoringLabs',
                'totalLab',
                'totalKmTurunAll',
                'totalKmAssignAll',
                'totalSisaAssignAll',
                'totalTargetPeriode',
                'totalRealisasiPeriode',
                'persentaseAssignAll',
                'persentaseRealisasiAll'
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
                ->orderBy('laboratorium_riset.nama_lab')
                ->orderBy('dosen.nama_dosen')
                ->get();

            $dataMonitoring = [];

            foreach ($anggota as $item) {
                $totalKmAssign = \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                    ->where('km_anggota.id_user', $item->id_user)
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
                    'nama_lab' => $item->nama_lab ?? '-',
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
            $totalKmAssignAll = array_sum(array_column($dataMonitoring, 'total_km_assign'));
            $totalTargetPeriode = array_sum(array_column($dataMonitoring, 'target_periode'));
            $totalRealisasiPeriode = array_sum(array_column($dataMonitoring, 'total_realisasi'));
            $totalSisa = array_sum(array_column($dataMonitoring, 'sisa'));

            $persentaseTotal = $totalTargetPeriode > 0
                ? round(($totalRealisasiPeriode / $totalTargetPeriode) * 100)
                : 0;

            return view('ketuakk.monitoring-anggota-kk.index', compact(
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
                'totalKmAssignAll',
                'totalTargetPeriode',
                'totalRealisasiPeriode',
                'totalSisa',
                'persentaseTotal'
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

            return view('ketuakk.km-lab-riset.create', compact('labs'));
        });

        Route::post('/ketuakk/km-lab-riset', function (\Illuminate\Http\Request $request) {
            $request->validate([
                'id_lab' => 'required|exists:laboratorium_riset,id_lab',
                'tahun_km' => 'required|integer|min:2000',
                'kategori_km' => 'required|string|max:100',
                'jumlah_km' => 'required|integer|min:1',
                'status_km' => 'required|string|max:50',
            ]);

            \Illuminate\Support\Facades\DB::table('km_lab')->insert([
                'id_lab' => $request->id_lab,
                'tahun_km' => $request->tahun_km,
                'kategori_km' => $request->kategori_km,
                'jumlah_km' => $request->jumlah_km,
                'status_km' => $request->status_km,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect('/ketuakk/km-lab-riset')
                ->with('success', 'KM berhasil diturunkan ke Lab Riset.');
        });
        Route::get('/ketuakk/km-lab-riset', function () {
            $tahun = now()->year;

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            // Query untuk Total KM dari target_km
            $totalKmPerKategori = \Illuminate\Support\Facades\DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->select(
                    'target_km.kategori_km',
                    \Illuminate\Support\Facades\DB::raw('SUM(target_km.target) as total_target')
                )
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->groupBy('target_km.kategori_km')
                ->pluck('total_target', 'kategori_km');

            // Query untuk Sudah Diturunkan dari km_lab
            $sudahTurunPerKategori = \Illuminate\Support\Facades\DB::table('km_lab')
                ->select(
                    'kategori_km',
                    \Illuminate\Support\Facades\DB::raw('SUM(jumlah_km) as total_turun')
                )
                ->where('tahun_km', $tahun)
                ->where('status_km', 'Aktif')
                ->groupBy('kategori_km')
                ->pluck('total_turun', 'kategori_km');

            // Buat recap kategori
            $rekapKategori = [];
            foreach ($kategoriDefault as $kategori) {
                $totalKm = $totalKmPerKategori[$kategori] ?? 0;
                $sudahTurun = $sudahTurunPerKategori[$kategori] ?? 0;
                $belumTurun = $totalKm - $sudahTurun;

                $rekapKategori[] = [
                    'kategori' => $kategori,
                    'total_km' => $totalKm,
                    'sudah_turun' => $sudahTurun,
                    'belum_turun' => $belumTurun,
                ];
            }

            $labs = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            $dataLab = [];

            foreach ($labs as $lab) {
                $jumlahDosen = \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_lab', $lab->id_lab)
                    ->count();

                $totalKmTurun = \Illuminate\Support\Facades\DB::table('km_lab')
                    ->where('id_lab', $lab->id_lab)
                    ->where('tahun_km', $tahun)
                    ->where('status_km', 'Aktif')
                    ->sum('jumlah_km');

                $totalKmAssign = \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                    ->where('km_lab.id_lab', $lab->id_lab)
                    ->where('km_lab.tahun_km', $tahun)
                    ->where('km_lab.status_km', 'Aktif')
                    ->sum('km_anggota.jumlah_km');

                $sisaKm = $totalKmTurun - $totalKmAssign;

                $persentase = $totalKmTurun > 0
                    ? round(($totalKmAssign / $totalKmTurun) * 100)
                    : 0;

                $dataLab[] = [
                    'id_lab' => $lab->id_lab,
                    'nama_lab' => $lab->nama_lab,
                    'jumlah_dosen' => $jumlahDosen,
                    'total_target' => $totalKmTurun,
                    'total_realisasi' => $totalKmAssign,
                    'sisa_km' => $sisaKm,
                    'persentase' => min($persentase, 100),
                    'status' => $totalKmTurun > 0 && $sisaKm <= 0
                        ? 'Sudah Dibagi'
                        : 'Belum Selesai',
                ];
            }

            return view('ketuakk.km-lab-riset.index', compact('dataLab', 'tahun', 'rekapKategori'));
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
                    ? round(($sudahAssign / $km->jumlah_km) * 100)
                    : 0;

                $daftarKmTurun[] = [
                    'id_km_lab' => $km->id_km_lab,
                    'kategori_km' => $km->kategori_km,
                    'jumlah_km' => $km->jumlah_km,
                    'tahun_km' => $km->tahun_km,
                    'status_km' => $km->status_km,
                    'created_at' => $km->created_at,
                    'sudah_assign' => $sudahAssign,
                    'sisa_km' => $sisaKm,
                    'persentase' => min($persentase, 100),
                ];
            }

            $targetPerKategori = \Illuminate\Support\Facades\DB::table('km_lab')
                ->select(
                    'kategori_km',
                    \Illuminate\Support\Facades\DB::raw('SUM(jumlah_km) as total_km')
                )
                ->where('id_lab', $id)
                ->where('tahun_km', $tahun)
                ->where('status_km', 'Aktif')
                ->groupBy('kategori_km')
                ->pluck('total_km', 'kategori_km');

            $assignPerKategori = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->select(
                    'km_lab.kategori_km',
                    \Illuminate\Support\Facades\DB::raw('SUM(km_anggota.jumlah_km) as total_assign')
                )
                ->where('km_lab.id_lab', $id)
                ->where('km_lab.tahun_km', $tahun)
                ->where('km_lab.status_km', 'Aktif')
                ->groupBy('km_lab.kategori_km')
                ->pluck('total_assign', 'kategori_km');

            $rekapKategori = [];

            foreach ($kategoriDefault as $kategori) {
                $totalKm = $targetPerKategori[$kategori] ?? 0;
                $sudahAssign = $assignPerKategori[$kategori] ?? 0;
                $sisaKm = max($totalKm - $sudahAssign, 0);

                $persentase = $totalKm > 0
                    ? round(($sudahAssign / $totalKm) * 100)
                    : 0;

                $rekapKategori[] = [
                    'kategori' => $kategori,
                    'total_km' => $totalKm,
                    'sudah_assign' => $sudahAssign,
                    'sisa_km' => $sisaKm,
                    'persentase' => min($persentase, 100),
                    'status' => $totalKm > 0 && $sisaKm <= 0
                        ? 'Sudah Dibagi'
                        : 'Belum Selesai',
                ];
            }

            $totalKmTurun = array_sum(array_column($rekapKategori, 'total_km'));
            $totalKmAssign = array_sum(array_column($rekapKategori, 'sudah_assign'));
            $totalSisaKm = array_sum(array_column($rekapKategori, 'sisa_km'));

            $persentaseTotal = $totalKmTurun > 0
                ? round(($totalKmAssign / $totalKmTurun) * 100)
                : 0;

            $riwayatAssign = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->leftJoin('users', 'km_anggota.id_user', '=', 'users.id_user')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->where('km_lab.id_lab', $id)
                ->where('km_lab.tahun_km', $tahun)
                ->select(
                    'km_anggota.id_km_anggota',
                    'km_anggota.jumlah_km',
                    'km_anggota.created_at',
                    'km_lab.kategori_km',
                    'km_lab.tahun_km',
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
                ->where('users.role', 'Anggota')
                ->where('users.id_lab', $id)
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
            $tahun = now()->year;

            $tanggalMulai = \Carbon\Carbon::create($tahun, 1, 1)->startOfYear()->toDateString();
            $tanggalSelesai = \Carbon\Carbon::create($tahun, 12, 31)->endOfYear()->toDateString();

            // Ambil id_kk dari user yang login (Ketua KK)
            $userLogin = auth()->user();
            $ketuaKkData = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_dosen', $userLogin->id_dosen)
                ->first();

            // Ambil SEMUA anggota KK dari tabel dosen (data master)
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
                    'laboratorium_riset.nama_lab'
                )
                ->orderBy('laboratorium_riset.nama_lab')
                ->orderBy('dosen.nama_dosen')
                ->get();

            $dataAnggota = [];

            foreach ($anggota as $item) {
                $totalTarget = \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                    ->where('km_anggota.id_user', $item->id_user)
                    ->where('km_lab.tahun_km', $tahun)
                    ->where('km_lab.status_km', 'Aktif')
                    ->sum('km_anggota.jumlah_km');

                $totalRealisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                    ->where('id_user', $item->id_user)
                    ->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai])
                    ->count();

                $persentase = $totalTarget > 0
                    ? round(($totalRealisasi / $totalTarget) * 100)
                    : 0;

                $dataAnggota[] = [
                    'id_user' => $item->id_user,
                    'nama_dosen' => $item->nama_dosen ?? $item->username,
                    'nidn' => $item->nidn ?? '-',
                    'email' => $item->email ?? '-',
                    'nama_lab' => $item->nama_lab ?? '-',
                    'total_target' => $totalTarget,
                    'total_realisasi' => $totalRealisasi,
                    'persentase' => min($persentase, 100),
                    'status' => $totalTarget > 0 && $totalRealisasi >= $totalTarget
                        ? 'Tercapai'
                        : 'Belum Tercapai',
                ];
            }

            return view('ketuakk.km-anggota-kk.index', compact('dataAnggota', 'tahun'));
        });
        Route::get('/ketuakk/km-anggota-kk/{id}', function ($id) {
            $tahun = now()->year;

            $tanggalMulai = \Carbon\Carbon::create($tahun, 1, 1)->startOfYear()->toDateString();
            $tanggalSelesai = \Carbon\Carbon::create($tahun, 12, 31)->endOfYear()->toDateString();

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

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
                    'laboratorium_riset.nama_lab'
                )
                ->first();

            if (!$anggota) {
                abort(404);
            }

            $targets = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->select(
                    'km_lab.kategori_km',
                    \Illuminate\Support\Facades\DB::raw('SUM(km_anggota.jumlah_km) as total_target')
                )
                ->where('km_anggota.id_user', $anggota->id_user)
                ->where('km_lab.tahun_km', $tahun)
                ->where('km_lab.status_km', 'Aktif')
                ->groupBy('km_lab.kategori_km')
                ->pluck('total_target', 'kategori_km')
                ->toArray();

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_user', $anggota->id_user)
                ->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai])
                ->orderBy('tanggal_mulai', 'desc')
                ->get();

            $rekap = [];

            foreach ($kategoriDefault as $kategori) {
                $target = $targets[$kategori] ?? 0;
                $realisasi = $aktivitas->where('kategori_km', $kategori)->count();

                $persentase = $target > 0
                    ? round(($realisasi / $target) * 100)
                    : 0;

                $rekap[] = [
                    'kategori' => $kategori,
                    'target' => $target,
                    'realisasi' => $realisasi,
                    'persentase' => min($persentase, 100),
                    'status' => $target > 0 && $realisasi >= $target
                        ? 'Tercapai'
                        : 'Belum Tercapai',
                ];
            }

            return view('ketuakk.km-anggota-kk.detail', compact(
                'anggota',
                'tahun',
                'rekap',
                'aktivitas'
            ));
        });
        Route::get('/ketuakk/km-kk', function () {
            $tahun = now()->year;

            $tanggalMulai = \Carbon\Carbon::create($tahun, 1, 1)->startOfYear()->toDateString();
            $tanggalSelesai = \Carbon\Carbon::create($tahun, 12, 31)->endOfYear()->toDateString();

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $totalTargetKm = \Illuminate\Support\Facades\DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->sum('target_km.target');

            $totalRealisasiKm = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai])
                ->count();

            $persentaseTotal = $totalTargetKm > 0
                ? round(($totalRealisasiKm / $totalTargetKm) * 100)
                : 0;

            $targetPerKategori = \Illuminate\Support\Facades\DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->select(
                    'target_km.kategori_km',
                    \Illuminate\Support\Facades\DB::raw('SUM(target_km.target) as total_target')
                )
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->groupBy('target_km.kategori_km')
                ->pluck('total_target', 'kategori_km');

            $realisasiPerKategori = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->select(
                    'kategori_km',
                    \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_realisasi')
                )
                ->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai])
                ->groupBy('kategori_km')
                ->pluck('total_realisasi', 'kategori_km');

            $rekapKategori = [];

            foreach ($kategoriDefault as $kategori) {
                $target = $targetPerKategori[$kategori] ?? 0;
                $realisasi = $realisasiPerKategori[$kategori] ?? 0;

                $persentase = $target > 0
                    ? round(($realisasi / $target) * 100)
                    : 0;

                $rekapKategori[] = [
                    'kategori' => $kategori,
                    'target' => $target,
                    'realisasi' => $realisasi,
                    'persentase' => min($persentase, 100),
                    'status' => $target > 0 && $realisasi >= $target
                        ? 'Tercapai'
                        : 'Belum Tercapai',
                ];
            }

            $rekapLab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get()
                ->map(function ($lab) use ($tahun, $tanggalMulai, $tanggalSelesai) {
                    $target = \Illuminate\Support\Facades\DB::table('km_lab')
                        ->where('id_lab', $lab->id_lab)
                        ->where('tahun_km', $tahun)
                        ->where('status_km', 'Aktif')
                        ->sum('jumlah_km');

                    $realisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                        ->where('id_lab', $lab->id_lab)
                        ->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai])
                        ->count();

                    $persentase = $target > 0
                        ? round(($realisasi / $target) * 100)
                        : 0;

                    return [
                        'nama_lab' => $lab->nama_lab,
                        'target' => $target,
                        'realisasi' => $realisasi,
                        'persentase' => min($persentase, 100),
                        'status' => $target > 0 && $realisasi >= $target
                            ? 'Tercapai'
                            : 'Belum Tercapai',
                    ];
                });

            return view('ketuakk.km-kk.index', compact(
                'tahun',
                'totalTargetKm',
                'totalRealisasiKm',
                'persentaseTotal',
                'rekapKategori',
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
            return view('anggota.aktivitas-km.create');
        });
        Route::post('/anggota/aktivitas-km', function (\Illuminate\Http\Request $request) {
            $request->validate([
                'kategori_km' => 'required',
                'judul_aktivitas' => 'required',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            ]);
            \Illuminate\Support\Facades\DB::table('aktivitas_km')->insert([
                'id_user' => auth()->user()->id_user,
                'id_lab' => auth()->user()->id_lab ?? null,
                'kategori_km' => $request->kategori_km,
                'judul_aktivitas' => $request->judul_aktivitas,
                'deskripsi_singkat' => $request->deskripsi_singkat,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return redirect('/anggota/aktivitas-km')->with('success', 'Aktivitas KM berhasil ditambahkan.');
        });
        Route::get('/anggota/aktivitas-km/{id}/edit', function ($id) {
            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_aktivitas', $id)
                ->where('id_user', auth()->user()->id_user)
                ->first();

            if (!$aktivitas) {
                abort(404);
            }

            return view('anggota.aktivitas-km.edit', compact('aktivitas'));
        });
        Route::put('/anggota/aktivitas-km/{id}', function (\Illuminate\Http\Request $request, $id) {
            $request->validate([
                'kategori_km' => 'required',
                'judul_aktivitas' => 'required',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            ]);

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_aktivitas', $id)
                ->where('id_user', auth()->user()->id_user)
                ->first();

            if (!$aktivitas) {
                abort(404);
            }

            \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_aktivitas', $id)
                ->where('id_user', auth()->user()->id_user)
                ->update([
                    'kategori_km' => $request->kategori_km,
                    'judul_aktivitas' => $request->judul_aktivitas,
                    'deskripsi_singkat' => $request->deskripsi_singkat,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                    'updated_at' => now(),
                ]);

            return redirect('/anggota/aktivitas-km')->with('success', 'Aktivitas KM berhasil diperbarui.');
        });
        Route::delete('/anggota/aktivitas-km/{id}', function ($id) {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_aktivitas', $id)
                ->where('id_user', $user->id_user)
                ->first();

            if (!$aktivitas) {
                return redirect('/anggota/aktivitas-km')
                    ->with('error', 'Aktivitas KM tidak ditemukan atau bukan milik Anda.');
            }

            \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_aktivitas', $id)
                ->where('id_user', $user->id_user)
                ->delete();

            return redirect('/anggota/aktivitas-km')
                ->with('success', 'Aktivitas KM berhasil dihapus.');
        });
        Route::get('/anggota/progress-km', function () {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $idUser = $user->id_user;
            $tahun = now()->year;

            $tanggalMulai = \Carbon\Carbon::create($tahun, 1, 1)->startOfYear();
            $tanggalSelesai = \Carbon\Carbon::create($tahun, 12, 31)->endOfYear();

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $targetPerKategori = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->select(
                    'km_lab.kategori_km',
                    \Illuminate\Support\Facades\DB::raw('SUM(km_anggota.jumlah_km) as total_target')
                )
                ->where('km_anggota.id_user', $idUser)
                ->where('km_lab.tahun_km', $tahun)
                ->where('km_lab.status_km', 'Aktif')
                ->groupBy('km_lab.kategori_km')
                ->pluck('total_target', 'kategori_km');

            $aktivitasPerKategori = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->select(
                    'kategori_km',
                    \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_realisasi')
                )
                ->where('id_user', $idUser)
                ->whereBetween('tanggal_mulai', [
                    $tanggalMulai->toDateString(),
                    $tanggalSelesai->toDateString(),
                ])
                ->groupBy('kategori_km')
                ->pluck('total_realisasi', 'kategori_km');

            $progress = [];

            foreach ($kategoriDefault as $kategori) {
                $target = $targetPerKategori[$kategori] ?? 0;
                $realisasi = $aktivitasPerKategori[$kategori] ?? 0;
                $sisa = max($target - $realisasi, 0);

                $persentase = $target > 0
                    ? round(($realisasi / $target) * 100)
                    : 0;

                $progress[] = [
                    'kategori' => $kategori,
                    'target' => $target,
                    'realisasi' => $realisasi,
                    'sisa' => $sisa,
                    'persentase' => min($persentase, 100),
                    'status' => $target > 0 && $sisa <= 0
                        ? 'Tercapai'
                        : 'Belum Tercapai',
                ];
            }

            $totalTarget = array_sum(array_column($progress, 'target'));
            $totalRealisasi = array_sum(array_column($progress, 'realisasi'));
            $totalSisa = array_sum(array_column($progress, 'sisa'));

            $persentaseTotal = $totalTarget > 0
                ? round(($totalRealisasi / $totalTarget) * 100)
                : 0;

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
                ->get();

            return view('anggota.progress-km', compact(
                'progress',
                'totalTarget',
                'totalRealisasi',
                'totalSisa',
                'persentaseTotal',
                'riwayatAssign',
                'tahun'
            ));
        });
    });
});
