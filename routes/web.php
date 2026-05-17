<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TargetKmController;
use App\Http\Controllers\KetuaLabController;

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
    Route::middleware(['role:Ketua KK'])->group(function () {
        Route::get('/ketuakk/dashboard', function () {
            return view('ketuakk.dashboard');
        });

        Route::get('/ketuakk/target-km', [TargetKmController::class, 'index']);
        Route::get('/ketuakk/target-km/create', [TargetKmController::class, 'create']);
        Route::post('/ketuakk/target-km', [TargetKmController::class, 'store']);
        Route::get('/ketuakk/target-km/{id}/edit', [TargetKmController::class, 'edit']);
        Route::put('/ketuakk/target-km/{id}', [TargetKmController::class, 'update']);
        Route::delete('/ketuakk/target-km/{id}', [TargetKmController::class, 'destroy']);
    });


    // RUANG KHUSUS KETUA LAB

    Route::middleware(['role:Ketua Lab'])->group(function () {
        Route::get('/ketualab/dashboard', function () {
            return view('ketualab.dashboard');
        });

        Route::get('/ketualab/penurunan-km', [KetuaLabController::class, 'penurunanKm']);
    });

    // RUANG KHUSUS ANGGOTA
    Route::middleware(['role:Anggota'])->group(function () {
        Route::get('/anggota/dashboard', function () {
            return view('anggota.dashboard');
        });
    });

});