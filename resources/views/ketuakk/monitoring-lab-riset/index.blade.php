@extends('layouts.app')

@section('title', 'Monitoring Lab Riset')

@section('content')
<div class="page-heading">
    Monitoring <span class="muted">Lab Riset</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Filter Periode Monitoring Lab Riset</h4>
            <p class="text-muted mb-0">
                Monitoring saat ini:
                <strong>{{ $labelPeriode ?? 'Tahunan' }}</strong>

                @if(isset($tanggalMulai) && isset($tanggalSelesai))
                | {{ $tanggalMulai->format('d/m/Y') }} - {{ $tanggalSelesai->format('d/m/Y') }}
                @endif
            </p>
        </div>

        <a href="/ketuakk/dashboard" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <form action="/ketuakk/monitoring-lab-riset" method="GET">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">Jenis Periode</label>
                <select name="periode" class="form-select">
                    <option value="tahun" {{ ($periode ?? 'tahun') == 'tahun' ? 'selected' : '' }}>Tahunan</option>
                    <option value="bulan" {{ ($periode ?? '') == 'bulan' ? 'selected' : '' }}>Bulanan</option>
                    <option value="triwulan" {{ ($periode ?? '') == 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                    <option value="semester" {{ ($periode ?? '') == 'semester' ? 'selected' : '' }}>Semester</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">Tahun</label>
                <input type="number" name="tahun" class="form-control" value="{{ $tahun ?? now()->year }}">
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">Bulan</label>
                <select name="bulan" class="form-select">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ ($bulan ?? now()->month) == $i ? 'selected' : '' }}>
                        {{ $i }}
                        </option>
                        @endfor
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">Triwulan</label>
                <select name="triwulan" class="form-select">
                    <option value="1" {{ ($triwulan ?? 1) == 1 ? 'selected' : '' }}>Triwulan 1</option>
                    <option value="2" {{ ($triwulan ?? 1) == 2 ? 'selected' : '' }}>Triwulan 2</option>
                    <option value="3" {{ ($triwulan ?? 1) == 3 ? 'selected' : '' }}>Triwulan 3</option>
                    <option value="4" {{ ($triwulan ?? 1) == 4 ? 'selected' : '' }}>Triwulan 4</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">Semester</label>
                <select name="semester" class="form-select">
                    <option value="1" {{ ($semester ?? 1) == 1 ? 'selected' : '' }}>Semester 1</option>
                    <option value="2" {{ ($semester ?? 1) == 2 ? 'selected' : '' }}>Semester 2</option>
                </select>
            </div>

            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    Filter
                </button>
            </div>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Total Lab Riset</p>
            <h3 class="fw-bold mb-0">{{ $totalLab ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Total KM Turun</p>
            <h3 class="fw-bold mb-0">{{ $totalKmTurunAll ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Sudah Assign</p>
            <h3 class="fw-bold mb-0">{{ $totalKmAssignAll ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Sisa Assign</p>
            <h3 class="fw-bold mb-0">{{ $totalSisaAssignAll ?? 0 }}</h3>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Target Periode</p>
            <h3 class="fw-bold mb-0">{{ $totalTargetPeriode ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Realisasi Periode</p>
            <h3 class="fw-bold mb-0">{{ $totalRealisasiPeriode ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Progress Assign</p>
            <h3 class="fw-bold mb-0">{{ min($persentaseAssignAll ?? 0, 100) }}%</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Progress Realisasi</p>
            <h3 class="fw-bold mb-0">{{ min($persentaseRealisasiAll ?? 0, 100) }}%</h3>
        </div>
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Monitoring Lab Riset</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 22%;">Lab Riset</th>
                    <th style="width: 10%;">Anggota</th>
                    <th style="width: 10%;">KM Turun</th>
                    <th style="width: 10%;">Assign</th>
                    <th style="width: 10%;">Sisa Assign</th>
                    <th style="width: 11%;">Target Periode</th>
                    <th style="width: 10%;">Realisasi</th>
                    <th style="width: 12%;">Progress</th>
                </tr>
            </thead>

            <tbody>
                @forelse($monitoringLabs as $index => $lab)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $lab['nama_lab'] }}
                    </td>

                    <td>
                        {{ $lab['jumlah_anggota'] }}
                    </td>

                    <td>
                        {{ $lab['total_km_turun'] }}
                    </td>

                    <td>
                        {{ $lab['total_km_assign'] }}
                    </td>

                    <td>
                        {{ $lab['sisa_assign'] }}
                    </td>

                    <td>
                        {{ $lab['target_periode'] }}
                    </td>

                    <td>
                        {{ $lab['total_realisasi'] }}
                    </td>

                    <td>
                        <div class="progress mb-1" style="height: 8px;">
                            <div
                                class="progress-bar"
                                role="progressbar"
                                style="width: {{ min($lab['persentase_realisasi'], 100) }}%;"></div>
                        </div>

                        <div class="small text-muted">
                            {{ $lab['persentase_realisasi'] }}%
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        Belum ada data monitoring lab riset.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection