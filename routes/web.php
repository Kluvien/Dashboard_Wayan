<?php

use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KetuaKkController;
use App\Http\Controllers\KetuaLabController;
use App\Http\Controllers\TargetKmController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

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
        Route::get('/ketuakk/dashboard', [KetuaKkController::class, 'dashboard']);

        Route::get('/ketuakk/target-km', [TargetKmController::class, 'index']);
        Route::get('/ketuakk/target-km/create', [TargetKmController::class, 'create']);
        Route::post('/ketuakk/target-km', [TargetKmController::class, 'store']);
        Route::get('/ketuakk/target-km/{id}/edit', [TargetKmController::class, 'edit']);
        Route::put('/ketuakk/target-km/{id}', [TargetKmController::class, 'update']);
        Route::delete('/ketuakk/target-km/{id}', [TargetKmController::class, 'destroy']);
        Route::get('/ketuakk/data-lab-riset', function () {
            $laboratorium = DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            $dataLab = [];

            foreach ($laboratorium as $lab) {
                $jumlahDosen = DB::table('dosen')
                    ->where('id_lab', $lab->id_lab)
                    ->count();

                $jumlahAktivitas = DB::table('aktivitas_km')
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
            $lab = DB::table('laboratorium_riset')
                ->where('id_lab', $id)
                ->first();

            if (! $lab) {
                abort(404);
            }

            $dosen = DB::table('dosen')
                ->where('id_lab', $id)
                ->orderBy('nama_dosen')
                ->get();

            $aktivitas = DB::table('aktivitas_km')
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
        Route::get('/ketuakk/data-dosen', function (Request $request) {
            $q = $request->query('q');

            $dosens = DB::table('dosen')
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
                        $subQuery->where('dosen.nama_dosen', 'like', '%'.$q.'%')
                            ->orWhere('dosen.nidn', 'like', '%'.$q.'%')
                            ->orWhere('dosen.email', 'like', '%'.$q.'%')
                            ->orWhere('dosen.jad', 'like', '%'.$q.'%')
                            ->orWhere('laboratorium_riset.nama_lab', 'like', '%'.$q.'%');
                    });
                })
                ->orderBy('dosen.id_dosen', 'asc')
                ->get();

            return view('ketuakk.data-master.dosen', compact('dosens', 'q'));
        });

        Route::get('/ketuakk/data-dosen/create', function () {
            $labs = DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            return view('ketuakk.data-master.dosen-create', compact('labs'));
        });

        Route::post('/ketuakk/data-dosen', function (Request $request) {
            $request->validate([
                'nama_dosen' => 'required|string|max:255',
                'nidn' => 'required|string|max:50',
                'email' => 'required|email|max:255',
                'id_lab' => 'required|exists:laboratorium_riset,id_lab',
                'jad' => 'required|in:GB,LK,L,AA',
            ]);

            $lab = DB::table('laboratorium_riset')
                ->where('id_lab', $request->id_lab)
                ->first();

            DB::table('dosen')->insert([
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
            $dosen = DB::table('dosen')
                ->where('id_dosen', $id)
                ->first();

            if (! $dosen) {
                abort(404);
            }

            $labs = DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            return view('ketuakk.data-master.dosen-edit', compact('dosen', 'labs'));
        });

        Route::put('/ketuakk/data-dosen/{id}', function (Request $request, $id) {
            $request->validate([
                'nama_dosen' => 'required|string|max:255',
                'nidn' => 'required|string|max:50',
                'email' => 'required|email|max:255',
                'id_lab' => 'required|exists:laboratorium_riset,id_lab',
                'jad' => 'required|in:GB,LK,L,AA',
            ]);

            $dosen = DB::table('dosen')
                ->where('id_dosen', $id)
                ->first();

            if (! $dosen) {
                abort(404);
            }

            $lab = DB::table('laboratorium_riset')
                ->where('id_lab', $request->id_lab)
                ->first();

            DB::table('dosen')
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
            $kelompokKeahlian = DB::table('kelompok_keahlian')->get();

            return view('ketuakk.data-master.kelompok-keahlian', compact('kelompokKeahlian'));
        });
        Route::get('/ketuakk/monitoring-lab-riset', function () {
            $tahun = now()->year;

            $labs = DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            $monitoringLabs = [];

            foreach ($labs as $lab) {
                $dosenIds = DB::table('dosen')
                    ->where('id_lab', $lab->id_lab)
                    ->pluck('id_dosen');

                $totalTarget = 0;

                if ($dosenIds->count() > 0) {
                    $totalTarget = DB::table('target_km')
                        ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                        ->whereIn('kontrak_manajemen.id_dosen', $dosenIds)
                        ->where('kontrak_manajemen.tahun_km', $tahun)
                        ->where('kontrak_manajemen.status_km', 'Aktif')
                        ->sum('target');
                }

                $totalRealisasi = DB::table('aktivitas_km')
                    ->where('id_lab', $lab->id_lab)
                    ->count();

                $persentase = $totalTarget > 0
                    ? round(($totalRealisasi / $totalTarget) * 100)
                    : 0;

                $monitoringLabs[] = [
                    'id_lab' => $lab->id_lab,
                    'nama_lab' => $lab->nama_lab,
                    'jumlah_dosen' => $dosenIds->count(),
                    'total_target' => $totalTarget,
                    'total_realisasi' => $totalRealisasi,
                    'persentase' => min($persentase, 100),
                    'status' => $totalTarget > 0 && $totalRealisasi >= $totalTarget
                        ? 'Tercapai'
                        : 'Belum Tercapai',
                ];
            }

            return view('ketuakk.monitoring-lab-riset.index', compact('monitoringLabs', 'tahun'));
        });
        Route::get('/ketuakk/monitoring-lab-riset/{id}', function ($id) {
            $tahun = now()->year;

            $lab = DB::table('laboratorium_riset')
                ->where('id_lab', $id)
                ->first();

            if (! $lab) {
                abort(404);
            }

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $dosenIds = DB::table('dosen')
                ->where('id_lab', $id)
                ->pluck('id_dosen');

            $targetPerKategori = collect();

            if ($dosenIds->count() > 0) {
                $targetPerKategori = DB::table('target_km')
                    ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                    ->select('target_km.indikator', DB::raw('SUM(target_km.target) as total_target'))
                    ->whereIn('kontrak_manajemen.id_dosen', $dosenIds)
                    ->where('kontrak_manajemen.tahun_km', $tahun)
                    ->where('kontrak_manajemen.status_km', 'Aktif')
                    ->groupBy('target_km.indikator')
                    ->pluck('total_target', 'indikator');
            }

            $realisasiPerKategori = DB::table('aktivitas_km')
                ->select('kategori_km', DB::raw('COUNT(*) as total_realisasi'))
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

            $aktivitas = DB::table('aktivitas_km')
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
        Route::get('/ketuakk/monitoring-anggota-kk', function () {
            $tahun = now()->year;

            $anggota = DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->leftJoin('laboratorium_riset', 'users.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('users.role', 'Anggota')
                ->select(
                    'users.id_user',
                    'users.id_dosen',
                    'users.username',
                    'users.role',
                    'users.id_lab',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'laboratorium_riset.nama_lab'
                )
                ->orderBy('laboratorium_riset.nama_lab')
                ->orderBy('dosen.nama_dosen')
                ->get();

            $dataMonitoring = [];

            foreach ($anggota as $item) {
                $targets = DB::table('target_km')
                    ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                    ->where('kontrak_manajemen.id_dosen', $item->id_dosen)
                    ->where('kontrak_manajemen.tahun_km', $tahun)
                    ->where('kontrak_manajemen.status_km', 'Aktif')
                    ->pluck('target', 'indikator')
                    ->toArray();

                if (empty($targets)) {
                    $targets = [
                        'Pendidikan' => 0,
                        'Penelitian' => 0,
                        'Publikasi' => 0,
                        'Pengabdian' => 0,
                        'Penunjang' => 0,
                    ];
                }

                $totalTarget = array_sum($targets);

                $totalRealisasi = DB::table('aktivitas_km')
                    ->where('id_user', $item->id_user)
                    ->count();

                $persentase = $totalTarget > 0
                    ? round(($totalRealisasi / $totalTarget) * 100)
                    : 0;

                $dataMonitoring[] = [
                    'id_user' => $item->id_user,
                    'username' => $item->username,
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

            return view('ketuakk.monitoring-anggota-kk.index', compact('dataMonitoring', 'tahun'));
        });
        Route::get('/ketuakk/monitoring-anggota-kk/{id}', function ($id) {
            $tahun = now()->year;

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $anggota = DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->leftJoin('laboratorium_riset', 'users.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('users.id_user', $id)
                ->where('users.role', 'Anggota')
                ->select(
                    'users.id_user',
                    'users.id_dosen',
                    'users.username',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'laboratorium_riset.nama_lab'
                )
                ->first();

            if (! $anggota) {
                abort(404);
            }

            $targets = DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->where('kontrak_manajemen.id_dosen', $anggota->id_dosen)
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->where('kontrak_manajemen.status_km', 'Aktif')
                ->pluck('target', 'indikator')
                ->toArray();

            $aktivitas = DB::table('aktivitas_km')
                ->where('id_user', $anggota->id_user)
                ->orderBy('tanggal_mulai', 'desc')
                ->get();

            $rekap = [];

            foreach ($kategoriDefault as $kategori) {
                $target = $targets[$kategori] ?? 0;
                $realisasi = $aktivitas->where('kategori_km', $kategori)->count();
                $persentase = $target > 0 ? round(($realisasi / $target) * 100) : 0;

                $rekap[] = [
                    'kategori' => $kategori,
                    'target' => $target,
                    'realisasi' => $realisasi,
                    'persentase' => min($persentase, 100),
                    'status' => $target > 0 && $realisasi >= $target ? 'Tercapai' : 'Belum Tercapai',
                ];
            }

            return view('ketuakk.monitoring-anggota-kk.detail', compact(
                'anggota',
                'tahun',
                'aktivitas',
                'rekap'
            ));
        });
        Route::get('/ketuakk/km-lab-riset', function () {
            $tahun = now()->year;

            $labs = DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            $dataLab = [];

            foreach ($labs as $lab) {
                $dosenIds = DB::table('dosen')
                    ->where('id_lab', $lab->id_lab)
                    ->pluck('id_dosen');

                $totalTarget = 0;

                if ($dosenIds->count() > 0) {
                    $totalTarget = DB::table('target_km')
                        ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                        ->whereIn('kontrak_manajemen.id_dosen', $dosenIds)
                        ->where('kontrak_manajemen.tahun_km', $tahun)
                        ->where('kontrak_manajemen.status_km', 'Aktif')
                        ->sum('target');
                }

                $totalRealisasi = DB::table('aktivitas_km')
                    ->where('id_lab', $lab->id_lab)
                    ->count();

                $persentase = $totalTarget > 0
                    ? round(($totalRealisasi / $totalTarget) * 100)
                    : 0;

                $dataLab[] = [
                    'id_lab' => $lab->id_lab,
                    'nama_lab' => $lab->nama_lab,
                    'jumlah_dosen' => $dosenIds->count(),
                    'total_target' => $totalTarget,
                    'total_realisasi' => $totalRealisasi,
                    'persentase' => min($persentase, 100),
                    'status' => $totalTarget > 0 && $totalRealisasi >= $totalTarget
                        ? 'Tercapai'
                        : 'Belum Tercapai',
                ];
            }

            return view('ketuakk.km-lab-riset.index', compact('dataLab', 'tahun'));
        });
        Route::get('/ketuakk/km-lab-riset/{id}', function ($id) {
            $tahun = now()->year;

            $lab = DB::table('laboratorium_riset')
                ->where('id_lab', $id)
                ->first();

            if (! $lab) {
                abort(404);
            }

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $dosenIds = DB::table('dosen')
                ->where('id_lab', $id)
                ->pluck('id_dosen');

            $targetPerKategori = collect();

            if ($dosenIds->count() > 0) {
                $targetPerKategori = DB::table('target_km')
                    ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                    ->select(
                        'target_km.indikator',
                        DB::raw('SUM(target_km.target) as total_target')
                    )
                    ->whereIn('kontrak_manajemen.id_dosen', $dosenIds)
                    ->where('kontrak_manajemen.tahun_km', $tahun)
                    ->where('kontrak_manajemen.status_km', 'Aktif')
                    ->groupBy('target_km.indikator')
                    ->pluck('total_target', 'indikator');
            }

            $realisasiPerKategori = DB::table('aktivitas_km')
                ->select(
                    'kategori_km',
                    DB::raw('COUNT(*) as total_realisasi')
                )
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
                    'status' => $target > 0 && $realisasi >= $target
                        ? 'Tercapai'
                        : 'Belum Tercapai',
                ];
            }

            $anggota = DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->where('users.role', 'Anggota')
                ->where('users.id_lab', $id)
                ->select(
                    'users.id_user',
                    'users.username',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email'
                )
                ->get();

            return view('ketuakk.km-lab-riset.detail', compact(
                'lab',
                'tahun',
                'rekapKategori',
                'anggota'
            ));
        });
        Route::get('/ketuakk/km-anggota-kk', function () {
            $tahun = now()->year;

            $anggota = DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->leftJoin('laboratorium_riset', 'users.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('users.role', 'Anggota')
                ->select(
                    'users.id_user',
                    'users.id_dosen',
                    'users.username',
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
                $targets = DB::table('target_km')
                    ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                    ->where('kontrak_manajemen.id_dosen', $item->id_dosen)
                    ->where('kontrak_manajemen.tahun_km', $tahun)
                    ->where('kontrak_manajemen.status_km', 'Aktif')
                    ->pluck('target', 'indikator')
                    ->toArray();

                $totalTarget = array_sum($targets);

                $totalRealisasi = DB::table('aktivitas_km')
                    ->where('id_user', $item->id_user)
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

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $anggota = DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->leftJoin('laboratorium_riset', 'users.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('users.id_user', $id)
                ->where('users.role', 'Anggota')
                ->select(
                    'users.id_user',
                    'users.id_dosen',
                    'users.username',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'laboratorium_riset.nama_lab'
                )
                ->first();

            if (! $anggota) {
                abort(404);
            }

            $targets = DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->where('kontrak_manajemen.id_dosen', $anggota->id_dosen)
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->where('kontrak_manajemen.status_km', 'Aktif')
                ->pluck('target', 'indikator')
                ->toArray();

            $aktivitas = DB::table('aktivitas_km')
                ->where('id_user', $anggota->id_user)
                ->orderBy('tanggal_mulai', 'desc')
                ->get();

            $rekap = [];

            foreach ($kategoriDefault as $kategori) {
                $target = $targets[$kategori] ?? 0;
                $realisasi = $aktivitas->where('kategori_km', $kategori)->count();
                $persentase = $target > 0 ? round(($realisasi / $target) * 100) : 0;

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

            $kategoriDefault = [
                'Pendidikan',
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $totalTargetKm = DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->where('kontrak_manajemen.status_km', 'Aktif')
                ->sum('target');

            $totalRealisasiKm = DB::table('aktivitas_km')->count();

            $persentaseTotal = $totalTargetKm > 0
                ? round(($totalRealisasiKm / $totalTargetKm) * 100)
                : 0;

            $targetPerKategori = DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->select(
                    'target_km.indikator',
                    DB::raw('SUM(target_km.target) as total_target')
                )
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->where('kontrak_manajemen.status_km', 'Aktif')
                ->groupBy('target_km.indikator')
                ->pluck('total_target', 'indikator');

            $realisasiPerKategori = DB::table('aktivitas_km')
                ->select(
                    'kategori_km',
                    DB::raw('COUNT(*) as total_realisasi')
                )
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
                    'status' => $target > 0 && $realisasi >= $target
                        ? 'Tercapai'
                        : 'Belum Tercapai',
                ];
            }

            $rekapLab = DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get()
                ->map(function ($lab) use ($tahun) {
                    $dosenIds = DB::table('dosen')
                        ->where('id_lab', $lab->id_lab)
                        ->pluck('id_dosen');

                    $target = 0;

                    if ($dosenIds->count() > 0) {
                        $target = DB::table('target_km')
                            ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                            ->whereIn('kontrak_manajemen.id_dosen', $dosenIds)
                            ->where('kontrak_manajemen.tahun_km', $tahun)
                            ->where('kontrak_manajemen.status_km', 'Aktif')
                            ->sum('target');
                    }

                    $realisasi = DB::table('aktivitas_km')
                        ->where('id_lab', $lab->id_lab)
                        ->count();

                    $persentase = $target > 0 ? round(($realisasi / $target) * 100) : 0;

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
                $dosen = DB::table('dosen')
                    ->where('id_dosen', $id)
                    ->first();

                if (! $dosen) {
                    return redirect('/ketuakk/data-dosen')
                        ->with('error', 'Data dosen tidak ditemukan.');
                }

                $dipakaiUser = DB::table('users')
                    ->where('id_dosen', $id)
                    ->exists();

                if ($dipakaiUser) {
                    return redirect('/ketuakk/data-dosen')
                        ->with('error', 'Data dosen tidak bisa dihapus karena masih terhubung dengan akun user.');
                }

                DB::table('target_km')
                    ->whereIn('id_km', function ($query) use ($id) {
                        $query->select('id_km')
                            ->from('kontrak_manajemen')
                            ->where('id_dosen', $id);
                    })
                    ->delete();

                DB::table('kontrak_manajemen')
                    ->where('id_dosen', $id)
                    ->delete();

                DB::table('dosen')
                    ->where('id_dosen', $id)
                    ->delete();

                return redirect('/ketuakk/data-dosen')
                    ->with('success', 'Data dosen berhasil dihapus.');
            } catch (Exception $e) {
                return redirect('/ketuakk/data-dosen')
                    ->with('error', 'Terjadi kesalahan: '.$e->getMessage());
            }
        });
    });

    // RUANG KHUSUS KETUA LAB
    Route::middleware(['auth', 'role:Ketua Lab'])->group(function () {
        Route::get('/ketualab/dashboard', [KetuaLabController::class, 'dashboard']);
        Route::get('/ketualab/penurunan-km', [KetuaLabController::class, 'pembagianKmAnggota']);
        Route::post('/ketualab/penurunan-km', [KetuaLabController::class, 'simpanPembagianKmAnggota']);
        Route::get('/ketualab/penurunan-km/{id}/plot', [KetuaLabController::class, 'createPlot']);
        Route::post('/ketualab/penurunan-km/{id}/plot', [KetuaLabController::class, 'storePlot']);
        Route::get('/ketualab/monitoring-lab', function () {
            /** @var User $user */
            $user = auth()->user();

            $idLab = $user->id_lab;
            $tahun = now()->year;

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
                    if (! isset($targetLabPerKategori[$kategori])) {
                        $targetLabPerKategori[$kategori] = 0;
                    }

                    $targetLabPerKategori[$kategori] += $target;
                }
            }

            $aktivitas = DB::table('aktivitas_km')
                ->select('kategori_km', DB::raw('COUNT(*) as total'))
                ->where('id_lab', $idLab)
                ->groupBy('kategori_km')
                ->pluck('total', 'kategori_km');

            $rekap = [];

            foreach ($targetLabPerKategori as $kategori => $targetLab) {
                $realisasi = $aktivitas[$kategori] ?? 0;
                $persentase = $targetLab > 0 ? round(($realisasi / $targetLab) * 100) : 0;

                $rekap[] = [
                    'kategori' => $kategori,
                    'target' => $targetLab,
                    'realisasi' => $realisasi,
                    'persentase' => min($persentase, 100),
                    'status' => $targetLab > 0 && $realisasi >= $targetLab ? 'Tercapai' : 'Belum Tercapai',
                ];
            }

            $totalTargetLab = array_sum($targetLabPerKategori);
            $totalRealisasiLab = array_sum($aktivitas->toArray());

            $persentaseTotal = $totalTargetLab > 0
                ? round(($totalRealisasiLab / $totalTargetLab) * 100)
                : 0;

            return view('ketualab.monitoring-lab', compact(
                'lab',
                'jumlahAnggota',
                'totalTargetLab',
                'totalRealisasiLab',
                'persentaseTotal',
                'rekap'
            ));
        });
        Route::get('/ketualab/monitoring-anggota', function () {
            /** @var User $user */
            $user = auth()->user();

            $idLab = $user->id_lab;
            $tahun = now()->year;

            $anggota = DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->leftJoin('laboratorium_riset', 'users.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('users.role', 'Anggota')
                ->where('users.id_lab', $idLab)
                ->select(
                    'users.id_user',
                    'users.id_dosen',
                    'users.username',
                    'users.role',
                    'users.id_lab',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'laboratorium_riset.nama_lab'
                )
                ->get();

            $dataMonitoring = [];

            foreach ($anggota as $item) {
                $targets = DB::table('target_km')
                    ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                    ->where('kontrak_manajemen.id_dosen', $item->id_dosen)
                    ->where('kontrak_manajemen.tahun_km', $tahun)
                    ->where('kontrak_manajemen.status_km', 'Aktif')
                    ->pluck('target', 'indikator')
                    ->toArray();

                if (empty($targets)) {
                    $targets = [
                        'Pendidikan' => 0,
                        'Penelitian' => 0,
                        'Publikasi' => 0,
                        'Pengabdian' => 0,
                        'Penunjang' => 0,
                    ];
                }

                $totalTarget = array_sum($targets);

                $totalRealisasi = DB::table('aktivitas_km')
                    ->where('id_user', $item->id_user)
                    ->count();

                $persentase = $totalTarget > 0
                    ? round(($totalRealisasi / $totalTarget) * 100)
                    : 0;

                $dataMonitoring[] = [
                    'id_user' => $item->id_user,
                    'username' => $item->username,
                    'nama_dosen' => $item->nama_dosen ?? '-',
                    'nidn' => $item->nidn ?? '-',
                    'email' => $item->email ?? '-',
                    'nama_lab' => $item->nama_lab ?? '-',
                    'total_target' => $totalTarget,
                    'total_realisasi' => $totalRealisasi,
                    'persentase' => min($persentase, 100),
                    'status' => $totalTarget > 0 && $totalRealisasi >= $totalTarget ? 'Tercapai' : 'Belum Tercapai',
                ];
            }

            return view('ketualab.monitoring-anggota', compact('dataMonitoring'));
        });
        Route::get('/ketualab/detail-anggota/{id}', function ($id) {
            /** @var User $ketuaLab */
            $ketuaLab = auth()->user();

            $tahun = now()->year;

            $anggota = DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->leftJoin('laboratorium_riset', 'users.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('users.id_user', $id)
                ->where('users.role', 'Anggota')
                ->where('users.id_lab', $ketuaLab->id_lab)
                ->select(
                    'users.id_user',
                    'users.id_dosen',
                    'users.username',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'laboratorium_riset.nama_lab'
                )
                ->first();

            if (! $anggota) {
                abort(404);
            }

            $aktivitas = DB::table('aktivitas_km')
                ->where('id_user', $anggota->id_user)
                ->orderBy('tanggal_mulai', 'desc')
                ->get();

            $targets = DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->where('kontrak_manajemen.id_dosen', $anggota->id_dosen)
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->where('kontrak_manajemen.status_km', 'Aktif')
                ->pluck('target', 'indikator')
                ->toArray();

            if (empty($targets)) {
                $targets = [
                    'Pendidikan' => 0,
                    'Penelitian' => 0,
                    'Publikasi' => 0,
                    'Pengabdian' => 0,
                    'Penunjang' => 0,
                ];
            }

            $rekap = [];

            foreach ($targets as $kategori => $target) {
                $realisasi = $aktivitas->where('kategori_km', $kategori)->count();
                $persentase = $target > 0 ? round(($realisasi / $target) * 100) : 0;

                $rekap[] = [
                    'kategori' => $kategori,
                    'target' => $target,
                    'realisasi' => $realisasi,
                    'persentase' => min($persentase, 100),
                    'status' => $target > 0 && $realisasi >= $target ? 'Tercapai' : 'Belum Tercapai',
                ];
            }

            return view('ketualab.detail-anggota', compact('anggota', 'aktivitas', 'rekap'));
        });
        Route::get('/ketualab/laporan', function () {
            /** @var User $user */
            $user = auth()->user();

            $idLab = $user->id_lab;
            $tahun = now()->year;

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
                    if (! isset($targetLabPerKategori[$kategori])) {
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

            foreach ($targetLabPerKategori as $kategori => $targetLab) {
                $realisasi = $aktivitasPerKategori[$kategori] ?? 0;
                $persentase = $targetLab > 0 ? round(($realisasi / $targetLab) * 100) : 0;

                $rekapKategori[] = [
                    'kategori' => $kategori,
                    'target' => $targetLab,
                    'realisasi' => $realisasi,
                    'persentase' => min($persentase, 100),
                    'status' => $targetLab > 0 && $realisasi >= $targetLab ? 'Tercapai' : 'Belum Tercapai',
                ];
            }

            $totalTargetLab = array_sum($targetLabPerKategori);
            $totalRealisasiLab = array_sum($aktivitasPerKategori->toArray());

            $persentaseTotal = $totalTargetLab > 0
                ? round(($totalRealisasiLab / $totalTargetLab) * 100)
                : 0;

            $aktivitas = DB::table('aktivitas_km')
                ->leftJoin('users', 'aktivitas_km.id_user', '=', 'users.id_user')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->where('aktivitas_km.id_lab', $idLab)
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

            return view('ketualab.laporan', compact(
                'lab',
                'jumlahAnggota',
                'totalTargetLab',
                'totalRealisasiLab',
                'persentaseTotal',
                'rekapKategori',
                'aktivitas'
            ));
        });
        Route::get('/ketualab/profil', function () {
            $user = auth()->user();

            $lab = DB::table('laboratorium_riset')
                ->where('id_lab', $user->id_lab)
                ->first();

            return view('ketualab.profil', compact('user', 'lab'));
        });
    });

    // RUANG KHUSUS ANGGOTA
    Route::middleware(['auth', 'role:Anggota'])->group(function () {
        Route::get('/anggota/dashboard', [AnggotaController::class, 'dashboard']);
        Route::get('/anggota/realisasi-km', [AnggotaController::class, 'indexRealisasi']);
        Route::get('/anggota/realisasi-km/{id}/edit', [AnggotaController::class, 'editRealisasi']);
        Route::put('/anggota/realisasi-km/{id}', [AnggotaController::class, 'updateRealisasi']);
        Route::get('/anggota/aktivitas-km', function () {
            $aktivitas = DB::table('aktivitas_km')
                ->where('id_user', auth()->user()->id_user)
                ->orderBy('tanggal_mulai', 'desc')
                ->get();

            return view('anggota.aktivitas-km.index', compact('aktivitas'));
        });
        Route::get('/anggota/riwayat-realisasi', function () {
            $aktivitas = DB::table('aktivitas_km')
                ->where('id_user', auth()->user()->id_user)
                ->orderBy('tanggal_mulai', 'desc')
                ->get();

            return view('anggota.riwayat-realisasi', compact('aktivitas'));
        });
        Route::get('/anggota/profil', function () {
            $user = auth()->user();

            $lab = DB::table('laboratorium_riset')
                ->where('id_lab', $user->id_lab)
                ->first();

            $dosen = null;

            if ($user->id_dosen) {
                $dosen = DB::table('dosen')
                    ->where('id_dosen', $user->id_dosen)
                    ->first();
            }

            return view('anggota.profil', compact('user', 'lab', 'dosen'));
        });
        Route::get('/anggota/aktivitas-km/create', function () {
            return view('anggota.aktivitas-km.create');
        });
        Route::post('/anggota/aktivitas-km', function (Request $request) {
            $request->validate([
                'kategori_km' => 'required',
                'judul_aktivitas' => 'required',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'jad' => 'required|in:GB,LK,L,AA',
            ]);
            DB::table('aktivitas_km')->insert([
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
            $aktivitas = DB::table('aktivitas_km')
                ->where('id_aktivitas', $id)
                ->where('id_user', auth()->user()->id_user)
                ->first();

            if (! $aktivitas) {
                abort(404);
            }

            return view('anggota.aktivitas-km.edit', compact('aktivitas'));
        });
        Route::put('/anggota/aktivitas-km/{id}', function (Request $request, $id) {
            $request->validate([
                'kategori_km' => 'required',
                'judul_aktivitas' => 'required',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            ]);

            $aktivitas = DB::table('aktivitas_km')
                ->where('id_aktivitas', $id)
                ->where('id_user', auth()->user()->id_user)
                ->first();

            if (! $aktivitas) {
                abort(404);
            }

            DB::table('aktivitas_km')
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
        Route::get('/anggota/progress-km', function () {
            /** @var User $user */
            $user = auth()->user();

            $idUser = $user->id_user;
            $idDosen = $user->id_dosen;
            $tahun = now()->year;

            $targets = DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->where('kontrak_manajemen.id_dosen', $idDosen)
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->where('kontrak_manajemen.status_km', 'Aktif')
                ->pluck('target', 'indikator')
                ->toArray();

            if (empty($targets)) {
                $targets = [
                    'Pendidikan' => 0,
                    'Penelitian' => 0,
                    'Publikasi' => 0,
                    'Pengabdian' => 0,
                    'Penunjang' => 0,
                ];
            }

            $aktivitas = DB::table('aktivitas_km')
                ->select('kategori_km', DB::raw('COUNT(*) as total'))
                ->where('id_user', $idUser)
                ->groupBy('kategori_km')
                ->pluck('total', 'kategori_km');

            $progress = [];

            foreach ($targets as $kategori => $target) {
                $realisasi = $aktivitas[$kategori] ?? 0;
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
            $totalRealisasi = array_sum($aktivitas->toArray());
            $persentaseTotal = $totalTarget > 0 ? round(($totalRealisasi / $totalTarget) * 100) : 0;

            return view('anggota.progress-km', compact(
                'progress',
                'totalTarget',
                'totalRealisasi',
                'persentaseTotal'
            ));
        });
    });
});
