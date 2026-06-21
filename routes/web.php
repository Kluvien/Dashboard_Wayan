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
        return view('welcome');
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
            $laboratorium = \Illuminate\Support\Facades\DB::table('laboratorium_riset')->get();

            return view('ketuakk.data-master.lab-riset', compact('laboratorium'));
        });
        Route::get('/ketuakk/data-dosen', function () {
            $dosens = \Illuminate\Support\Facades\DB::table('dosen')
                ->leftJoin('laboratorium_riset', 'dosen.id_lab', '=', 'laboratorium_riset.id_lab')
                ->select(
                    'dosen.id_dosen',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'laboratorium_riset.nama_lab'
                )
                ->get();

            return view('ketuakk.data-master.dosen', compact('dosens'));
        });
        Route::get('/ketuakk/data-kelompok-keahlian', function () {
            $kelompokKeahlian = \Illuminate\Support\Facades\DB::table('kelompok_keahlian')->get();

            return view('ketuakk.data-master.kelompok-keahlian', compact('kelompokKeahlian'));
        });
    });


    // RUANG KHUSUS KETUA LAB
    Route::middleware(['auth', 'role:Ketua Lab'])->group(function () {
        Route::get('/ketualab/dashboard', [KetuaLabController::class, 'dashboard']);
        Route::get('/ketualab/penurunan-km', [KetuaLabController::class, 'penurunanKm']);
        Route::get('/ketualab/penurunan-km/{id}/plot', [KetuaLabController::class, 'createPlot']);
        Route::post('/ketualab/penurunan-km/{id}/plot', [KetuaLabController::class, 'storePlot']);
        Route::view('/ketualab/monitoring-lab', 'ketualab.monitoring-lab');
        Route::view('/ketualab/monitoring-anggota', 'ketualab.monitoring-anggota');
        Route::view('/ketualab/detail-anggota', 'ketualab.detail-anggota');
        Route::view('/ketualab/laporan', 'ketualab.laporan');
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
                ->delete();

            return redirect('/anggota/aktivitas-km')->with('success', 'Aktivitas KM berhasil dihapus.');
        });
    });

});