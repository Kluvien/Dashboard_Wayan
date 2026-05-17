@extends('layouts.app')

@section('title', 'Dashboard Anggota KK')

@section('content')
<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card border-primary shadow-sm border-0 border-start border-5">
            <div class="card-body">
                <h5 class="card-title text-primary fw-bold">Status Realisasi KM Anda</h5>
                
                <div class="progress mb-2" style="height: 25px;">
                    <div class="progress-bar bg-success" role="progressbar" 
                        style="width: {{ $averagePercentage }}%;" 
                        aria-valuenow="{{ $averagePercentage }}" 
                        aria-valuemin="0" aria-valuemax="100">
                        {{ $averagePercentage }}% Selesai
                    </div>
                </div>

                <p class="text-muted small m-0">
                    Anda memiliki <strong>{{ $totalTasks }}</strong> target tugas. Batas akhir pengisian: Desember 2026
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-white fw-bold">
        Petunjuk Pengisian
    </div>
    <div class="card-body text-secondary">
        <p>Silakan klik menu <b>Input Realisasi</b> di sebelah kiri untuk mulai memperbarui progres capaian dari target Kontrak Manajemen yang telah dibebankan kepada Anda.</p>
    </div>
</div>
@endsection