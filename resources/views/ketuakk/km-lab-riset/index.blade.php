@extends('layouts.app')

@section('title', 'KM Lab Riset')

@section('content')
<div class="page-heading">
    Kontrak Manajemen <span class="muted">Lab Riset</span>
</div>

<!-- Rekap Kategori KM -->
<div class="row g-3 mb-4">
    @foreach($rekapKategori as $kategori_data)
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title fw-bold text-primary mb-3">{{ $kategori_data['kategori'] }}</h5>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Total KM</span>
                        <span class="fw-bold">{{ $kategori_data['total_km'] }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Sudah Diturunkan</span>
                        <span class="fw-bold text-success">{{ $kategori_data['sudah_turun'] }}</span>
                    </div>
                </div>

                <div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Belum Diturunkan</span>
                        <span class="fw-bold text-warning">{{ $kategori_data['belum_turun'] }}</span>
                    </div>
                </div>

                @if($kategori_data['total_km'] > 0)
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar bg-success" 
                        style="width: {{ ($kategori_data['sudah_turun'] / $kategori_data['total_km']) * 100 }}%;">
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    {{ round(($kategori_data['sudah_turun'] / $kategori_data['total_km']) * 100) }}% selesai
                </div>
                @else
                <div class="small text-muted mt-3">Belum ada target KM</div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">KM Lab Riset Tahun {{ $tahun }}</h4>
            <p class="text-muted mb-0">
                Menampilkan data penurunan Kontrak Manajemen dari Ketua KK ke masing-masing lab riset.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="/ketuakk/km-lab-riset/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Turunkan KM ke Lab
            </a>

            <a href="/ketuakk/dashboard" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 27%;">Lab Riset</th>
                    <th style="width: 13%;">Total KM Turun</th>
                    <th style="width: 13%;">Sudah Assign</th>
                    <th style="width: 10%;">Sisa KM</th>
                    <th style="width: 14%;">Progress</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 8%; text-align: center;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($dataLab as $index => $lab)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $lab['nama_lab'] }}
                    </td>

                    <td>
                        {{ $lab['total_target'] ?? 0 }}
                    </td>

                    <td>
                        {{ $lab['total_realisasi'] ?? 0 }}
                    </td>

                    <td>
                        {{ $lab['sisa_km'] ?? 0 }}
                    </td>

                    <td>
                        <div class="progress mb-1" style="height: 10px;">
                            <div
                                class="progress-bar"
                                role="progressbar"
                                style="width: {{ min($lab['persentase'] ?? 0, 100) }}%;"></div>
                        </div>

                        <div class="small text-muted">
                            {{ $lab['persentase'] ?? 0 }}%
                        </div>
                    </td>

                    <td>
                        @if(($lab['status'] ?? '') === 'Sudah Dibagi')
                        <span class="badge bg-success">Selesai</span>
                        @else
                        <span class="badge bg-warning text-dark">Belum</span>
                        @endif
                    </td>

                    <td class="text-center">
                        <a href="/ketuakk/km-lab-riset/{{ $lab['id_lab'] }}" class="btn btn-primary btn-sm px-3">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        Belum ada data KM Lab Riset.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection