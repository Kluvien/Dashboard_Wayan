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
                Menampilkan target, capaian, dan data penurunan Kontrak Manajemen berdasarkan masing-masing lab riset.
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
        <table class="table align-middle mb-0" style="table-layout: fixed; width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 6%;">No</th>
                    <th style="width: 22%;">Lab Riset</th>
                    <th>Total KM Turun</th>
                    <th>Sudah Assign</th>
                    <th>Sisa KM</th>
                    <th style="width: 16%;">Progress</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 8%;">Aksi</th>
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
                        {{ $lab['jumlah_dosen'] }}
                    </td>

                    <td>
                        {{ $lab['total_target'] }}
                    </td>

                    <td>
                        {{ $lab['total_realisasi'] }}
                    </td>
                    <td>
                        {{ $lab['sisa_km'] }}
                    </td>

                    <td>
                        <div class="progress mb-1" style="height: 10px;">
                            <div
                                class="progress-bar"
                                role="progressbar"
                                style="width: {{ min($lab['persentase'], 100) }}%;"
                                aria-valuenow="{{ min($lab['persentase'], 100) }}"
                                aria-valuemin="0"
                                aria-valuemax="100"></div>
                        </div>

                        <div class="small text-muted">
                            {{ $lab['persentase'] }}%
                        </div>
                    </td>

                    <td>
                        @if($lab['status'] === 'Tercapai')
                        <span class="badge bg-success">Tercapai</span>
                        @else
                        <span class="badge bg-warning text-dark">Belum</span>
                        @endif
                    </td>

                    <td>
                        <a href="/ketuakk/km-lab-riset/{{ $lab['id_lab'] }}" class="btn btn-primary btn-sm">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        Belum ada data lab riset.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection