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
        Route::get('/ketuakk/dashboard', [KetuaKkController::class, 'dashboard']);

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
                ->get();

            return view('ketuakk.data-master.dosen', compact('dosens', 'q'));
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
                'jad' => 'required|in:GB,LK,L,AA',
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
                'jad' => 'required|in:GB,LK,L,AA',
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
        Route::get('/ketuakk/monitoring-lab-riset', function () {
            $tahun = now()->year;

            $labs = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            $monitoringLabs = [];

            foreach ($labs as $lab) {
                $dosenIds = \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_lab', $lab->id_lab)
                    ->pluck('id_dosen');

                $totalTarget = 0;

                if ($dosenIds->count() > 0) {
                    $totalTarget = \Illuminate\Support\Facades\DB::table('target_km')
                        ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                        ->whereIn('kontrak_manajemen.id_dosen', $dosenIds)
                        ->where('kontrak_manajemen.tahun_km', $tahun)
                        ->where('kontrak_manajemen.status_km', 'Aktif')
                        ->sum('target');
                }

                $totalRealisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
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

            $dosenIds = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_lab', $id)
                ->pluck('id_dosen');

            $targetPerKategori = collect();

            if ($dosenIds->count() > 0) {
                $targetPerKategori = \Illuminate\Support\Facades\DB::table('target_km')
                    ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                    ->select('target_km.indikator', \Illuminate\Support\Facades\DB::raw('SUM(target_km.target) as total_target'))
                    ->whereIn('kontrak_manajemen.id_dosen', $dosenIds)
                    ->where('kontrak_manajemen.tahun_km', $tahun)
                    ->where('kontrak_manajemen.status_km', 'Aktif')
                    ->groupBy('target_km.indikator')
                    ->pluck('total_target', 'indikator');
            }

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
        Route::get('/ketuakk/monitoring-anggota-kk', function () {
            $tahun = now()->year;

            $anggota = \Illuminate\Support\Facades\DB::table('users')
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
                $targets = \Illuminate\Support\Facades\DB::table('target_km')
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

                $totalRealisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
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

            $anggota = \Illuminate\Support\Facades\DB::table('users')
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

            if (!$anggota) {
                abort(404);
            }

            $targets = \Illuminate\Support\Facades\DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->where('kontrak_manajemen.id_dosen', $anggota->id_dosen)
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->where('kontrak_manajemen.status_km', 'Aktif')
                ->pluck('target', 'indikator')
                ->toArray();

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
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

            return view('ketuakk.km-lab-riset.index', compact('dataLab', 'tahun'));
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
                $sisaKm = $totalKm - $sudahAssign;

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
                ->leftJoin('dosen', 'km_anggota.id_dosen', '=', 'dosen.id_dosen')
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

            $anggota = \Illuminate\Support\Facades\DB::table('users')
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
                $targets = \Illuminate\Support\Facades\DB::table('target_km')
                    ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                    ->where('kontrak_manajemen.id_dosen', $item->id_dosen)
                    ->where('kontrak_manajemen.tahun_km', $tahun)
                    ->where('kontrak_manajemen.status_km', 'Aktif')
                    ->pluck('target', 'indikator')
                    ->toArray();

                $totalTarget = array_sum($targets);

                $totalRealisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
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

            $anggota = \Illuminate\Support\Facades\DB::table('users')
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

            if (!$anggota) {
                abort(404);
            }

            $targets = \Illuminate\Support\Facades\DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->where('kontrak_manajemen.id_dosen', $anggota->id_dosen)
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->where('kontrak_manajemen.status_km', 'Aktif')
                ->pluck('target', 'indikator')
                ->toArray();

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
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

            $totalTargetKm = \Illuminate\Support\Facades\DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->where('kontrak_manajemen.status_km', 'Aktif')
                ->sum('target');

            $totalRealisasiKm = \Illuminate\Support\Facades\DB::table('aktivitas_km')->count();

            $persentaseTotal = $totalTargetKm > 0
                ? round(($totalRealisasiKm / $totalTargetKm) * 100)
                : 0;

            $targetPerKategori = \Illuminate\Support\Facades\DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->select(
                    'target_km.indikator',
                    \Illuminate\Support\Facades\DB::raw('SUM(target_km.target) as total_target')
                )
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->where('kontrak_manajemen.status_km', 'Aktif')
                ->groupBy('target_km.indikator')
                ->pluck('total_target', 'indikator');

            $realisasiPerKategori = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->select(
                    'kategori_km',
                    \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_realisasi')
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

            $rekapLab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get()
                ->map(function ($lab) use ($tahun) {
                    $dosenIds = \Illuminate\Support\Facades\DB::table('dosen')
                        ->where('id_lab', $lab->id_lab)
                        ->pluck('id_dosen');

                    $target = 0;

                    if ($dosenIds->count() > 0) {
                        $target = \Illuminate\Support\Facades\DB::table('target_km')
                            ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                            ->whereIn('kontrak_manajemen.id_dosen', $dosenIds)
                            ->where('kontrak_manajemen.tahun_km', $tahun)
                            ->where('kontrak_manajemen.status_km', 'Aktif')
                            ->sum('target');
                    }

                    $realisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
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

                \Illuminate\Support\Facades\DB::table('target_km')
                    ->whereIn('id_km', function ($query) use ($id) {
                        $query->select('id_km')
                            ->from('kontrak_manajemen')
                            ->where('id_dosen', $id);
                    })
                    ->delete();

                \Illuminate\Support\Facades\DB::table('kontrak_manajemen')
                    ->where('id_dosen', $id)
                    ->delete();

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
        Route::get('/ketualab/dashboard', [KetuaLabController::class, 'dashboard']);
        Route::get('/ketualab/penurunan-km', [KetuaLabController::class, 'pembagianKmAnggota']);
        Route::post('/ketualab/penurunan-km', [KetuaLabController::class, 'simpanPembagianKmAnggota']);
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
        Route::get('/ketualab/laporan', function () {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $idLab = $user->id_lab;
            $tahun = now()->year;

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $idLab)
                ->first();

            $anggota = \Illuminate\Support\Facades\DB::table('users')
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
                $targets = \Illuminate\Support\Facades\DB::table('target_km')
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

            $aktivitasPerKategori = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->select('kategori_km', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
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

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
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

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
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
