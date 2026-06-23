@extends('layouts.app')

@section('title', 'Monitoring KM Lab')

@section('content')
<div class="page-heading">
    Monitoring <span class="muted">KM Lab</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Filter Periode Monitoring</h4>
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

    <form action="/ketualab/monitoring-lab" method="GET">
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
            <p class="text-muted mb-1">KM Turun dari Ketua KK</p>
            <h3 class="fw-bold mb-0">{{ $totalKmTurun ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Sudah Assign ke Anggota</p>
            <h3 class="fw-bold mb-0">{{ $totalKmAssign ?? 0 }}</h3>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Sisa Belum Assign</p>
            <h3 class="fw-bold mb-0">{{ $totalSisaAssign ?? 0 }}</h3>
        </div>
    </div>

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
            <p class="text-muted mb-1">Progress Realisasi</p>
            <h3 class="fw-bold mb-0">{{ min($persentaseTotal ?? 0, 100) }}%</h3>
        </div>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Progress Pembagian KM ke Anggota</h4>

    <div class="progress" style="height: 14px;">
        <div
            class="progress-bar"
            role="progressbar"
            style="width: {{ min($persentaseAssign ?? 0, 100) }}%;"></div>
    </div>

    <div class="small text-muted mt-2">
        {{ $persentaseAssign ?? 0 }}% KM sudah dibagikan ke anggota.
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Rekap Monitoring KM Lab per Kategori</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori KM</th>
                    <th>KM Turun</th>
                    <th>Sudah Assign</th>
                    <th>Sisa Assign</th>
                    <th>Target Periode</th>
                    <th>Realisasi</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rekap as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $item['kategori'] }}
                    </td>

                    <td>{{ $item['km_turun'] }}</td>

                    <td>{{ $item['km_assign'] }}</td>

                    <td>{{ $item['sisa_assign'] }}</td>

                    <td>{{ $item['target_periode'] }}</td>

                    <td>{{ $item['realisasi'] }}</td>

                    <td style="min-width: 130px;">
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
                        @if($item['status'] === 'Tercapai')
                        <span class="badge bg-success">Tercapai</span>
                        @else
                        <span class="badge bg-warning text-dark">Belum</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        Belum ada data monitoring.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection