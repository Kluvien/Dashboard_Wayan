@extends('layouts.app')

@section('title', 'Monitoring Anggota Lab')

@section('content')
<div class="page-heading">
    Monitoring <span class="muted">Anggota Lab</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Filter Periode Monitoring Anggota</h4>
            <p class="text-muted mb-0">
                Monitoring saat ini:
                <strong>{{ $labelPeriode ?? 'Tahunan' }}</strong>

                @if(isset($tanggalMulai) && isset($tanggalSelesai))
                | {{ $tanggalMulai->format('d/m/Y') }} - {{ $tanggalSelesai->format('d/m/Y') }}
                @endif
            </p>
        </div>

        <a href="/ketualab/dashboard" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <form action="/ketualab/monitoring-anggota" method="GET">
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
            <p class="text-muted mb-1">Lab Riset</p>
            <h5 class="fw-bold mb-0">{{ $lab->nama_lab ?? '-' }}</h5>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Jumlah Anggota</p>
            <h3 class="fw-bold mb-0">{{ $jumlahAnggota ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Total KM Assign</p>
            <h3 class="fw-bold mb-0">{{ $totalKmAssignLab ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Target Periode</p>
            <h3 class="fw-bold mb-0">{{ $totalTargetPeriode ?? 0 }}</h3>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <p class="text-muted mb-1">Realisasi Periode</p>
            <h3 class="fw-bold mb-0">{{ $totalRealisasiPeriode ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <p class="text-muted mb-1">Sisa Target Periode</p>
            <h3 class="fw-bold mb-0">{{ $totalSisa ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <p class="text-muted mb-1">Progress Total</p>
            <h3 class="fw-bold mb-0">{{ min($persentaseTotal ?? 0, 100) }}%</h3>
        </div>
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Monitoring Progress Anggota</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 22%;">Nama Anggota</th>
                    <th style="width: 12%;">NIDN</th>
                    <th style="width: 8%;">JAD</th>
                    <th style="width: 12%;">KM Assign</th>
                    <th style="width: 12%;">Target Periode</th>
                    <th style="width: 10%;">Realisasi</th>
                    <th style="width: 10%;">Progress</th>
                    <th style="width: 9%;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($dataMonitoring as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $item['nama_dosen'] }}
                    </td>

                    <td>
                        {{ $item['nidn'] }}
                    </td>

                    <td>
                        <span class="badge bg-primary">{{ $item['jad'] }}</span>
                    </td>

                    <td>
                        {{ $item['total_km_assign'] }}
                    </td>

                    <td>
                        {{ $item['target_periode'] }}
                    </td>

                    <td>
                        {{ $item['total_realisasi'] }}
                    </td>

                    <td>
                        <div class="progress mb-1" style="height: 8px;">
                            <div
                                class="progress-bar"
                                role="progressbar"
                                style="width: {{ min($item['persentase'], 100) }}%;"></div>
                        </div>

                        <div class="small text-muted">
                            {{ $item['persentase'] }}%
                        </div>
                    </td>

                    <td>
                        <a href="/ketualab/detail-anggota/{{ $item['id_user'] }}" class="btn btn-primary btn-sm">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        Belum ada data anggota pada lab ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection