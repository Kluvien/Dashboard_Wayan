@extends('layouts.app')

@section('title', 'KM Lab Riset')

@section('content')
<div class="page-heading">
    Kontrak Manajemen <span class="muted">Lab Riset</span>
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