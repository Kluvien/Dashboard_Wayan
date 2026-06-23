@extends('layouts.app')

@section('title', 'Data Lab Riset')

@section('content')
<div class="page-heading">
    Data <span class="muted">Lab Riset</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Daftar Laboratorium Riset</h4>
            <p class="text-muted mb-0">
                Halaman ini menampilkan daftar lab riset beserta jumlah dosen dan aktivitas KM yang tercatat.
            </p>
        </div>

        <a href="/ketuakk/dashboard" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-4">
    @forelse($dataLab as $lab)
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-2">{{ $lab['nama_lab'] }}</h5>
                        <p class="text-muted mb-0">
                            Laboratorium riset dalam Kelompok Keahlian Sistem Informasi.
                        </p>
                    </div>

                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 44px; height: 44px; background:#EAF1FF; color:#477EF7;">
                        <i class="bi bi-building fs-5"></i>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:#F6F7FB;">
                            <div class="text-muted small fw-bold">Jumlah Dosen</div>
                            <div class="fs-4 fw-bold">{{ $lab['jumlah_dosen'] }}</div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="p-3 rounded" style="background:#F6F7FB;">
                            <div class="text-muted small fw-bold">Aktivitas KM</div>
                            <div class="fs-4 fw-bold">{{ $lab['jumlah_aktivitas'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <a href="/ketuakk/data-lab-riset/{{ $lab['id_lab'] }}" class="btn btn-primary">
                        Lihat Detail
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card text-center text-muted py-4">
                Belum ada data laboratorium riset.
            </div>
        </div>
    @endforelse
</div>
@endsection