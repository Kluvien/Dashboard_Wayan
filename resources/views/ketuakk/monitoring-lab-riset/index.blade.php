@extends('layouts.app')

@section('title', 'Monitoring Lab Riset')

@section('content')
@php
$monitoringLabs = collect($monitoringLabs ?? []);
$rekapKategori = collect($rekapKategori ?? []);
$kategoriDefault = $kategoriDefault ?? ['Pendidikan', 'Penelitian', 'Publikasi', 'Pengabdian', 'Penunjang'];
$periodeColumns = $periodeColumns ?? [1 => 'TW1', 2 => 'TW2', 3 => 'TW3', 4 => 'TW4'];
@endphp

<style>
    .monitoring-table-card {
        position: relative;
    }

    .km-table {
        min-width: 1700px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .km-table th {
        white-space: nowrap;
        vertical-align: middle;
        font-size: 13px;
        background: #fff;
        border-bottom: 1px solid #E5E7EB;
    }

    .km-table td {
        vertical-align: middle;
        font-size: 13px;
        background: #fff;
        border-bottom: 1px solid #E5E7EB;
    }

    .km-table thead {
        position: sticky;
        top: 0;
        z-index: 80;
        background: #fff;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }

    .km-table thead tr:first-child th {
        position: static;
        height: 64px;
        background: #fff;
        box-shadow: inset 0 -1px 0 #E5E7EB;
    }

    .km-table thead tr:nth-child(2) th {
        position: static;
        height: 44px;
        background: #fff;
        box-shadow: inset 0 -1px 0 #E5E7EB;
    }

    .km-table thead th[rowspan="2"] {
        vertical-align: middle;
        background: #fff !important;
    }

    .group-header {
        background: #F3F6FB !important;
        text-align: center;
        font-weight: 800;
        border-left: 2px solid #CBD5E1 !important;
        border-right: 2px solid #CBD5E1 !important;
        padding-top: 20px !important;
        padding-bottom: 20px !important;
    }

    .category-start {
        border-left: 2px solid #CBD5E1 !important;
    }

    .category-end {
        border-right: 2px solid #CBD5E1 !important;
    }

    .period-cell {
        text-align: center;
        font-weight: 700;
        min-width: 70px;
    }

    .data-label-cell {
        min-width: 110px;
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

    .table-scroll-container {
        overflow-x: auto;
        overflow-y: visible;
        scrollbar-width: none;
        -ms-overflow-style: none;
        max-height: none;
    }

    .table-scroll-container::-webkit-scrollbar {
        display: none;
    }

    .sticky-col-no {
        position: sticky;
        left: 0;
        z-index: 12;
        min-width: 60px;
        background: #fff !important;
    }

    .sticky-col-lab {
        position: sticky;
        left: 60px;
        z-index: 12;
        min-width: 170px;
        background: #fff !important;
    }

    .sticky-col-anggota {
        position: sticky;
        left: 230px;
        z-index: 12;
        min-width: 90px;
        background: #fff !important;
    }

    .sticky-col-data {
        position: sticky;
        left: 320px;
        z-index: 12;
        min-width: 110px;
        background: #fff !important;
        border-right: 2px solid #CBD5E1 !important;
    }

    thead .sticky-col-no,
    thead .sticky-col-lab,
    thead .sticky-col-anggota,
    thead .sticky-col-data {
        z-index: 50;
        background: #fff !important;
    }

    tbody tr:nth-child(4n+1) td,
    tbody tr:nth-child(4n+2) td {
        background: #FFFFFF;
    }

    tbody tr:nth-child(4n+3) td,
    tbody tr:nth-child(4n+4) td {
        background: #FAFBFC;
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

    @media (max-width: 992px) {
        .floating-table-scroll {
            left: 16px;
            right: 16px;
        }
    }
</style>

<div class="page-heading">
    Monitoring <span class="muted">Lab Riset</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Filter Monitoring Lab Riset</h4>
            <p class="text-muted mb-0">
                Monitoring saat ini:
                <strong>{{ $labelPeriode ?? 'Triwulan Tahun ' . ($tahun ?? now()->year) }}</strong>
            </p>
        </div>

        <a href="/ketuakk/dashboard" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <form action="/ketuakk/monitoring-lab-riset" method="GET">
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

<div class="card monitoring-table-card">
    <h4 class="fw-bold mb-3">Monitoring Lab Riset</h4>

    <div class="table-scroll-sync">
        <div class="table-scroll-container">
            <table class="table align-middle mb-0 km-table">
                <thead>
                    <tr>
                        <th rowspan="2" class="sticky-col-no">No</th>
                        <th rowspan="2" class="sticky-col-lab">Lab Riset</th>
                        <th rowspan="2" class="sticky-col-anggota">Anggota</th>
                        <th rowspan="2" class="sticky-col-data">Data</th>

                        @foreach($kategoriDefault as $kategori)
                        <th colspan="{{ count($periodeColumns) }}" class="group-header">
                            {{ $kategori }}
                        </th>
                        @endforeach

                        <th rowspan="2">Total</th>
                    </tr>

                    <tr>
                        @foreach($kategoriDefault as $kategori)
                        @foreach($periodeColumns as $key => $label)
                        <th class="text-center {{ $loop->first ? 'category-start' : '' }} {{ $loop->last ? 'category-end' : '' }}">
                            {{ $label }}
                        </th>
                        @endforeach
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @forelse($monitoringLabs as $index => $lab)
                    <tr>
                        <td rowspan="2" class="sticky-col-no">{{ $index + 1 }}</td>

                        <td rowspan="2" class="fw-bold sticky-col-lab">
                            {{ $lab['nama_lab'] ?? '-' }}
                        </td>

                        <td rowspan="2" class="sticky-col-anggota">
                            {{ $lab['jumlah_anggota'] ?? 0 }}
                        </td>

                        <td class="sticky-col-data data-label-cell">
                            <span class="badge bg-primary">Target</span>
                        </td>

                        @foreach($kategoriDefault as $kategori)
                        @foreach($periodeColumns as $key => $label)
                        <td class="period-cell {{ $loop->first ? 'category-start' : '' }} {{ $loop->last ? 'category-end' : '' }}">
                            {{ $lab['data'][$kategori]['target'][$key] ?? 0 }}
                        </td>
                        @endforeach
                        @endforeach

                        <td class="fw-bold text-center">
                            {{ $lab['total_target'] ?? 0 }}
                        </td>
                    </tr>

                    <tr>
                        <td class="sticky-col-data data-label-cell">
                            <span class="badge bg-success">Realisasi</span>
                        </td>

                        @foreach($kategoriDefault as $kategori)
                        @foreach($periodeColumns as $key => $label)
                        <td class="period-cell {{ $loop->first ? 'category-start' : '' }} {{ $loop->last ? 'category-end' : '' }}">
                            {{ $lab['data'][$kategori]['realisasi'][$key] ?? 0 }}
                        </td>
                        @endforeach
                        @endforeach

                        <td class="fw-bold text-center">
                            {{ $lab['total_realisasi'] ?? 0 }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 5 + (count($kategoriDefault) * count($periodeColumns)) }}" class="text-center text-muted py-4">
                            Belum ada data monitoring lab riset.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="floating-table-scroll" id="floatingMonitoringLabScroll">
            <div class="floating-table-scroll-inner"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const wrapper = document.querySelector('.table-scroll-sync');
        const tableScroll = document.querySelector('.table-scroll-container');
        const floatingScroll = document.getElementById('floatingMonitoringLabScroll');
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