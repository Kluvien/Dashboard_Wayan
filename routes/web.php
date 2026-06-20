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
        Route::view('/ketualab/profil', 'ketualab.profil');
    });

    // RUANG KHUSUS ANGGOTA
    Route::middleware(['auth', 'role:Anggota'])->group(function () {
        Route::get('/anggota/dashboard', [AnggotaController::class, 'dashboard']);
        Route::get('/anggota/realisasi-km', [AnggotaController::class, 'indexRealisasi']);
        Route::get('/anggota/realisasi-km/{id}/edit', [AnggotaController::class, 'editRealisasi']);
        Route::put('/anggota/realisasi-km/{id}', [AnggotaController::class, 'updateRealisasi']);
    });

});