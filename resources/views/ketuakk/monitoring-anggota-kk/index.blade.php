@extends('layouts.app')

@section('title', 'Monitoring Anggota KK')

@section('content')
@php
$dataMonitoring = collect($dataMonitoring ?? []);
$rekapKategori = collect($rekapKategori ?? []);
$kategoriDefault = $kategoriDefault ?? ['Pendidikan', 'Penelitian', 'Publikasi', 'Pengabdian', 'Penunjang'];
@endphp

<style>
    .km-table {
        min-width: 1350px;
    }

    .km-table th {
        white-space: nowrap;
        vertical-align: middle;
        font-size: 13px;
        background: #fff;
    }

    .km-table td {
        vertical-align: middle;
        font-size: 13px;
        background: #fff;
    }

    .km-table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .km-table th,
    .km-table td {
        border-bottom: 1px solid #E5E7EB !important;
    }

    .km-table th:not(:last-child),
    .km-table td:not(:last-child) {
        border-right: 1px solid #F1F5F9;
    }

    .progress-start {
        border-left: 2px solid #CBD5E1 !important;
    }

    .progress-end {
        border-right: 2px solid #CBD5E1 !important;
    }

    .progress-group-cell {
        background: #F8FAFC !important;
    }

    .sticky-col-name {
        border-right: 2px solid #CBD5E1 !important;
    }

    .group-header {
        background: #F3F6FB !important;
        text-align: center;
        font-weight: 800;
        border-left: 2px solid #CBD5E1 !important;
        border-right: 2px solid #CBD5E1 !important;
    }

    .category-card-title {
        font-size: 13px;
        color: #6B7280;
        margin-bottom: 4px;
    }

    .category-card-value {
        font-size: 22px;
        font-weight: 800;
        margin-bottom: 0;
    }

    .progress-soft {
        height: 10px;
        border-radius: 999px;
        background: #E5E7EB;
        overflow: hidden;
    }

    .progress-soft-fill {
        height: 100%;
        border-radius: 999px;
        background: #477EF7;
    }

    .summary-value {
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 0;
    }

    .table-scroll-container {
        overflow-x: auto;
        overflow-y: hidden;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .table-scroll-container::-webkit-scrollbar {
        display: none;
    }

    .sticky-col-no {
        position: sticky;
        left: 0;
        min-width: 60px;
        background: #fff !important;
        z-index: 10;
    }

    .sticky-col-name {
        position: sticky;
        left: 60px;
        min-width: 220px;
        background: #fff !important;
        z-index: 10;
        border-right: 2px solid #CBD5E1 !important;
    }

    thead .sticky-col-no,
    thead .sticky-col-name {
        z-index: 20;
    }

    .floating-table-scroll {
        position: fixed;
        left: 320px;
        right: 32px;
        bottom: 16px;
        height: 18px;
        overflow-x: auto;
        overflow-y: hidden;
        background: #ffffff;
        border: 1px solid #E5E7EB;
        border-radius: 999px;
        z-index: 999;
        display: none;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
    }

    .floating-table-scroll-inner {
        height: 1px;
    }

    .jad-col {
        border-left: 2px solid #CBD5E1 !important;
        border-right: 2px solid #CBD5E1 !important;
    }

    .status-col {
        border-left: 2px solid #CBD5E1 !important;
        border-right: 2px solid #CBD5E1 !important;
    }

    .aksi-col {
        border-left: 2px solid #CBD5E1 !important;
    }

    @media (max-width: 992px) {
        .floating-table-scroll {
            left: 16px;
            right: 16px;
        }
    }
</style>

<div class="page-heading">
    Monitoring <span class="muted">Anggota KK</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Filter Monitoring Anggota KK</h4>
            <p class="text-muted mb-0">
                Monitoring saat ini:
                <strong>{{ $labelPeriode ?? 'Triwulan Tahun ' . ($tahun ?? now()->year) }}</strong>
            </p>
        </div>

        <a href="/ketuakk/dashboard" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <form action="/ketuakk/monitoring-anggota-kk" method="GET">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">Tahun</label>
                <select name="tahun" class="form-select">
                    @foreach($tahunOptions ?? [$tahun] as $itemTahun)
                    <option value="{{ $itemTahun }}" {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                        {{ $itemTahun }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Jenis Periode</label>
                <select name="periode" class="form-select">
                    <option value="triwulan" {{ ($periode ?? 'triwulan') === 'triwulan' ? 'selected' : '' }}>
                        Triwulan
                    </option>
                    <option value="semester" {{ ($periode ?? '') === 'semester' ? 'selected' : '' }}>
                        Semester
                    </option>
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    Filter
                </button>
            </div>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    @foreach($rekapKategori as $item)
    <div class="col-md-6 col-lg">
        <div class="card h-100">
            <p class="category-card-title">{{ $item['kategori'] ?? '-' }}</p>

            <p class="category-card-value">
                {{ $item['progress'] ?? 0 }}%
            </p>

            <div class="progress-soft my-2">
                <div class="progress-soft-fill" style="width: {{ $item['progress'] ?? 0 }}%;"></div>
            </div>

            <div class="d-flex justify-content-between small text-muted">
                <span>Target: {{ $item['target'] ?? 0 }}</span>
                <span>Realisasi: {{ $item['realisasi'] ?? 0 }}</span>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Jumlah Anggota</p>
            <p class="summary-value">{{ $jumlahAnggota ?? 0 }}</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Sudah Selesai</p>
            <p class="summary-value text-success">{{ $jumlahSelesai ?? 0 }}</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Sedang Progress</p>
            <p class="summary-value text-warning">{{ $jumlahProgress ?? 0 }}</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Belum Mulai</p>
            <p class="summary-value text-danger">{{ $jumlahBelumMulai ?? 0 }}</p>
        </div>
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Monitoring Progress Anggota KK</h4>

    <div class="table-scroll-sync">
        <div class="table-scroll-container">
            <table class="table align-middle mb-0 km-table">
                <thead>
                    <tr>
                        <th rowspan="2" class="sticky-col-no">No</th>
                        <th rowspan="2" class="sticky-col-name">Nama Anggota</th>
                        <th rowspan="2">Lab Riset</th>
                        <th rowspan="2" class="jad-col">JAD</th>
                        <th colspan="3" class="group-header">Progress KM</th>
                        <th rowspan="2" class="status-col">Status</th>
                        <th rowspan="2" class="aksi-col">Aksi</th>
                    </tr>
                    <tr>
                        <th class="progress-start progress-group-cell">Total Target</th>
                        <th class="progress-group-cell">Total Realisasi</th>
                        <th class="progress-end progress-group-cell">Progress</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($dataMonitoring as $index => $item)
                    <tr>
                        <td class="sticky-col-no">{{ $index + 1 }}</td>

                        <td class="fw-bold sticky-col-name">
                            {{ $item['nama_dosen'] }}
                            <div class="small text-muted">
                                {{ $item['nidn'] }}
                            </div>
                        </td>

                        <td>
                            {{ $item['nama_lab'] }}
                        </td>

                        <td class="jad-col">
                            <span class="badge bg-primary">
                                {{ $item['jad'] }}
                            </span>
                        </td>

                        <td class="fw-bold text-center progress-start">
                            {{ $item['total_target'] }}
                        </td>

                        <td class="fw-bold text-center">
                            {{ $item['total_realisasi'] }}
                        </td>

                        <td class="progress-end" style="min-width: 180px;">
                            <div class="progress-soft mb-1">
                                <div class="progress-soft-fill" style="width: {{ $item['persentase'] }}%;"></div>
                            </div>
                            <div class="small text-muted">
                                {{ $item['persentase'] }}%
                            </div>
                        </td>

                        <td class="status-col">
                            @if($item['status_class'] === 'success')
                            <span class="badge bg-success">Selesai</span>
                            @elseif($item['status_class'] === 'warning')
                            <span class="badge bg-warning text-dark">Sedang Progress</span>
                            @elseif($item['status_class'] === 'danger')
                            <span class="badge bg-danger">Belum Mulai</span>
                            @else
                            <span class="badge bg-secondary">{{ $item['status_progress'] }}</span>
                            @endif
                        </td>

                        <td class="aksi-col">
                            <a href="/ketuakk/monitoring-anggota-kk/{{ $item['id_user'] }}?tahun={{ $tahun }}&periode={{ $periode }}" class="btn btn-primary btn-sm">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            Belum ada data anggota KK.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="floating-table-scroll" id="floatingMonitoringAnggotaScroll">
            <div class="floating-table-scroll-inner"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const wrapper = document.querySelector('.table-scroll-sync');
        const tableScroll = document.querySelector('.table-scroll-container');
        const floatingScroll = document.getElementById('floatingMonitoringAnggotaScroll');
        const floatingInner = floatingScroll ? floatingScroll.querySelector('.floating-table-scroll-inner') : null;

        if (!wrapper || !tableScroll || !floatingScroll || !floatingInner) {
            return;
        }

        let isSyncing = false;

        function updateWidth() {
            floatingInner.style.width = tableScroll.scrollWidth + 'px';
        }

        function syncScroll(source, target) {
            if (isSyncing) return;

            isSyncing = true;
            target.scrollLeft = source.scrollLeft;
            isSyncing = false;
        }

        function toggleFloatingScroll() {
            const rect = wrapper.getBoundingClientRect();
            const isTableVisible = rect.top < window.innerHeight && rect.bottom > 120;
            const needHorizontalScroll = tableScroll.scrollWidth > tableScroll.clientWidth;

            if (isTableVisible && needHorizontalScroll) {
                floatingScroll.style.display = 'block';
            } else {
                floatingScroll.style.display = 'none';
            }
        }

        updateWidth();
        toggleFloatingScroll();

        tableScroll.addEventListener('scroll', function() {
            syncScroll(tableScroll, floatingScroll);
        });

        floatingScroll.addEventListener('scroll', function() {
            syncScroll(floatingScroll, tableScroll);
        });

        window.addEventListener('resize', function() {
            updateWidth();
            toggleFloatingScroll();
        });

        window.addEventListener('scroll', toggleFloatingScroll);
    });
</script>
@endsection