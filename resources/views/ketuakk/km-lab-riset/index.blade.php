@extends('layouts.app')

@section('title', 'KM Lab Riset')

@section('content')
<style>
    .km-table th {
        white-space: nowrap;
        vertical-align: middle;
        text-align: center;
        font-size: 12px;
    }

    .km-table td {
        vertical-align: middle;
        text-align: center;
        font-size: 13px;
    }

    .km-table td.lab-name {
        text-align: left;
        font-weight: 700;
        min-width: 260px;
    }

    .group-header {
        background: #F3F6FB;
        font-weight: 800;
        color: #1F2937;
    }

    .progress-soft {
        height: 10px;
        border-radius: 999px;
        background: #E5E7EB;
        overflow: hidden;
        min-width: 120px;
    }

    .progress-soft-fill {
        height: 100%;
        border-radius: 999px;
        background: #477EF7;
    }

    .summary-card {
        border: 1px solid #E5E7EB;
        border-radius: 16px;
        padding: 16px;
        height: 100%;
        background: #fff;
    }

    .summary-title {
        font-weight: 800;
        color: #477EF7;
        margin-bottom: 10px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: 13px;
        margin-bottom: 6px;
    }

    .summary-row strong {
        color: #111827;
    }
</style>

<div class="page-heading">
    Kontrak Manajemen <span class="muted">Lab Riset</span>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">KM Lab Riset Tahun {{ $tahun }}</h4>
            <p class="text-muted mb-0">
                Menampilkan rekap jumlah KM KK dan jumlah KM yang turun ke masing-masing lab riset berdasarkan kategori.
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap align-items-center">
            <form method="GET" action="/ketuakk/km-lab-riset" class="d-flex gap-2 align-items-center">
                <label class="fw-bold mb-0">Tahun</label>
                <select name="tahun" class="form-select" style="min-width: 130px;" onchange="this.form.submit()">
                    @foreach($tahunOptions as $itemTahun)
                    <option value="{{ $itemTahun }}" {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                        {{ $itemTahun }}
                    </option>
                    @endforeach
                </select>
            </form>

            <a href="/ketuakk/km-lab-riset/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Turunkan KM ke Lab
            </a>

            <a href="/ketuakk/dashboard" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach($rekapKategori as $item)
    <div class="col-md-6 col-lg-4">
        <div class="summary-card">
            <div class="summary-title">{{ $item['kategori'] }}</div>

            <div class="summary-row">
                <span>Jumlah KM KK</span>
                <strong>{{ $item['total_km_kk'] }}</strong>
            </div>

            <div class="summary-row">
                <span>Turun ke Lab Riset</span>
                <strong>{{ $item['total_turun'] }}</strong>
            </div>

            <div class="summary-row">
                <span>Sisa Belum Turun</span>
                <strong>{{ $item['sisa'] }}</strong>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card mb-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table" style="width: 100%;">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 5%;">No</th>
                    <th rowspan="2" style="width: 22%;">Lab Riset</th>

                    <th colspan="5" class="group-header">
                        Jumlah KM KK
                    </th>

                    <th colspan="5" class="group-header">
                        Jumlah KM yang Turun ke Lab Riset
                    </th>

                    <th rowspan="2" style="width: 12%;">Progress Assign</th>
                    <th rowspan="2" style="width: 10%;">Status</th>
                    <th rowspan="2" style="width: 8%;">Aksi</th>
                </tr>

                <tr>
                    @foreach($kategoriDefault as $kategori)
                    <th>{{ $kategori }}</th>
                    @endforeach

                    @foreach($kategoriDefault as $kategori)
                    <th>{{ $kategori }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @forelse($dataLab as $index => $lab)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="lab-name">
                        {{ $lab['nama_lab'] }}
                    </td>

                    @foreach($kategoriDefault as $kategori)
                    <td>{{ $lab['kk_per_kategori'][$kategori] ?? 0 }}</td>
                    @endforeach

                    @foreach($kategoriDefault as $kategori)
                    <td>{{ $lab['turun_per_kategori'][$kategori] ?? 0 }}</td>
                    @endforeach

                    <td>
                        <div class="progress-soft mb-1">
                            <div class="progress-soft-fill" style="width: {{ $lab['persentase'] ?? 0 }}%;"></div>
                        </div>
                        <div class="small fw-bold">
                            {{ $lab['persentase'] ?? 0 }}%
                        </div>
                        <div class="small text-muted">
                            Assign: {{ $lab['total_assign'] ?? 0 }} / {{ $lab['total_turun'] ?? 0 }}
                        </div>
                    </td>

                    <td>
                        @if(($lab['status'] ?? '') === 'Selesai')
                        <span class="badge bg-success">Selesai</span>
                        @elseif(($lab['status'] ?? '') === 'Belum Ada KM')
                        <span class="badge bg-secondary">Belum Ada KM</span>
                        @else
                        <span class="badge bg-warning text-dark">Belum Selesai</span>
                        @endif
                    </td>

                    <td>
                        <a href="/ketuakk/km-lab-riset/{{ $lab['id_lab'] }}?tahun={{ $tahun }}" class="btn btn-primary btn-sm px-3">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="15" class="text-center text-muted py-4">
                        Belum ada data KM Lab Riset.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection