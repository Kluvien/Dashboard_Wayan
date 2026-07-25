@extends('layouts.app')

@section('title', 'Monitoring Lab Riset')

@section('content')
@php
    $monitoringLabs = collect($monitoringLabs ?? []);
    $rekapKategori = collect($rekapKategori ?? []);
    $kategoriDefault = $kategoriDefault ?? ['Penelitian', 'Publikasi', 'Pengabdian', 'Penunjang'];
    $periodeColumns = $periodeColumns ?? [1 => 'TW1', 2 => 'TW2', 3 => 'TW3', 4 => 'TW4'];

    $jumlahLab = $monitoringLabs->count();
    $jumlahAnggota = $monitoringLabs->sum(fn ($item) => (int) ($item['jumlah_anggota'] ?? 0));
    $totalTarget = $monitoringLabs->sum(fn ($item) => (int) ($item['total_target'] ?? 0));
    $totalRealisasi = $monitoringLabs->sum(fn ($item) => (int) ($item['total_realisasi'] ?? 0));
    $sisaTarget = max($totalTarget - $totalRealisasi, 0);
    $progressTotal = $totalTarget > 0 ? min(round(($totalRealisasi / $totalTarget) * 100), 100) : 0;
    $labelMode = ($periode ?? 'triwulan') === 'semester'
        ? 'Data monitoring ditampilkan dalam pembagian semester.'
        : 'Data monitoring ditampilkan dalam pembagian triwulan.';
@endphp

<style>
    .kk-monitoring-page {
        padding-bottom: 28px;
    }

    .kk-monitoring-hero,
    .kk-monitoring-card,
    .kk-category-card,
    .kk-table-card {
        border: 1px solid #E2E8F0;
        border-radius: 18px;
        background: #FFFFFF;
        box-shadow: 0 6px 16px rgba(15, 23, 42, .04);
    }

    .kk-monitoring-hero {
        padding: 18px;
        margin-bottom: 16px;
    }

    .kk-hero-row,
    .kk-section-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .kk-card-title {
        color: #111827;
        font-size: 19px;
        font-weight: 900;
        margin-bottom: 4px;
    }

    .kk-card-subtitle {
        color: #64748B;
        font-size: 13px;
        margin: 0;
    }

    .kk-period-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-top: 10px;
        padding: 7px 11px;
        border: 1px solid #BFDBFE;
        border-radius: 999px;
        background: #EFF6FF;
        color: #2563EB;
        font-size: 12px;
        font-weight: 900;
    }

    .kk-filter-grid {
        display: grid;
        grid-template-columns: minmax(140px, .9fr) minmax(185px, 1.2fr) auto;
        gap: 10px;
        align-items: end;
        margin-top: 16px;
    }

    .kk-filter-group label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 12px;
        font-weight: 900;
    }

    .kk-filter-group .form-select {
        height: 41px;
        border-color: #CBD5E1;
        border-radius: 10px;
        color: #172033;
        font-size: 14px;
        font-weight: 700;
    }

    .kk-btn-primary,
    .kk-btn-secondary,
    .kk-btn-soft {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 0;
        border-radius: 10px;
        padding: 0 15px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 900;
        white-space: nowrap;
    }

    .kk-btn-primary {
        background: linear-gradient(135deg, #4F7DF3, #6A98FF);
        color: #FFFFFF;
        box-shadow: 0 7px 14px rgba(79, 125, 243, .18);
    }

    .kk-btn-primary:hover {
        color: #FFFFFF;
        opacity: .94;
    }

    .kk-btn-secondary {
        background: #6B7280;
        color: #FFFFFF;
    }

    .kk-btn-secondary:hover {
        color: #FFFFFF;
        background: #4B5563;
    }

    .kk-btn-soft {
        min-height: 34px;
        padding: 0 12px;
        border: 1px solid #BFDBFE;
        background: #EFF6FF;
        color: #2563EB;
        font-size: 12px;
    }

    .kk-btn-soft:hover {
        color: #1D4ED8;
        background: #DBEAFE;
    }

    .kk-category-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }

    .kk-category-card {
        min-height: 230px;
        padding: 14px;
        border-top: 4px solid #5A88FF;
        background: linear-gradient(180deg, #FFFFFF 0%, #FBFDFF 100%);
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .kk-category-top {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: flex-start;
    }

    .kk-category-name {
        color: #334155;
        font-size: 15px;
        font-weight: 900;
        line-height: 1.25;
    }

    .kk-category-caption {
        display: block;
        margin-top: 3px;
        color: #94A3B8;
        font-size: 10px;
        font-weight: 800;
    }

    .kk-category-percent {
        padding: 6px 10px;
        border-radius: 12px;
        background: #EAF1FF;
        color: #2563EB;
        font-size: 16px;
        font-weight: 900;
        white-space: nowrap;
    }

    .kk-progress-track {
        width: 100%;
        height: 9px;
        overflow: hidden;
        border-radius: 999px;
        background: #E7EDF7;
    }

    .kk-progress-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #4F7DF3, #79A0FF);
    }

    .kk-metric-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .kk-metric {
        min-height: 58px;
        padding: 8px 10px;
        border: 1px solid;
        border-radius: 11px;
    }

    .kk-metric-label {
        color: inherit;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .15px;
        text-transform: uppercase;
    }

    .kk-metric-value {
        margin-top: 4px;
        color: inherit;
        font-size: 19px;
        font-weight: 900;
        line-height: 1;
    }

    .metric-target { color: #2563EB; background: #EFF6FF; border-color: #BFDBFE; }
    .metric-realisasi { color: #059669; background: #ECFDF5; border-color: #BBF7D0; }
    .metric-sisa { color: #D97706; background: #FFF7ED; border-color: #FED7AA; }
    .metric-progress { color: #7C3AED; background: #F5F3FF; border-color: #DDD6FE; }

    .kk-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }

    .kk-monitoring-card {
        padding: 15px;
    }

    .kk-summary-label {
        color: #64748B;
        font-size: 12px;
        font-weight: 900;
        margin-bottom: 6px;
    }

    .kk-summary-value {
        color: #0F172A;
        font-size: 24px;
        font-weight: 900;
        line-height: 1;
    }

    .kk-summary-value.primary { color: #2563EB; }
    .kk-summary-value.success { color: #059669; }
    .kk-summary-value.warning { color: #D97706; }

    .kk-summary-progress-card {
        grid-column: span 4;
    }

    .kk-progress-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
        color: #1F2937;
        font-size: 13px;
        font-weight: 900;
    }

    .kk-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .kk-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border: 1px solid;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
    }

    .chip-primary { color: #2563EB; background: #EFF6FF; border-color: #BFDBFE; }
    .chip-success { color: #15803D; background: #ECFDF5; border-color: #BBF7D0; }
    .chip-warning { color: #B45309; background: #FFF7ED; border-color: #FED7AA; }

    .kk-table-card {
        padding: 17px;
        position: relative;
    }

    .kk-section-title {
        color: #111827;
        font-size: 18px;
        font-weight: 900;
        margin-bottom: 3px;
    }

    .kk-section-desc {
        color: #64748B;
        font-size: 13px;
        margin: 0;
    }

    .table-scroll-container {
        overflow-x: auto;
        overflow-y: visible;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .table-scroll-container::-webkit-scrollbar {
        display: none;
    }

    .km-table {
        min-width: 1780px;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    .km-table th,
    .km-table td {
        vertical-align: middle;
        font-size: 12px;
        border-bottom: 1px solid #EEF2F7;
        background: #FFFFFF;
        white-space: nowrap;
    }

    .km-table thead th {
        padding: 11px 10px;
        color: #334155;
        background: #F8FAFC;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .km-table tbody td {
        padding: 11px 10px;
        color: #1F2937;
    }

    .km-table thead {
        position: sticky;
        top: 0;
        z-index: 80;
        background: #FFFFFF;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .08);
    }

    .group-header {
        text-align: center;
        border-left: 1px solid #CBD5E1 !important;
        border-right: 1px solid #CBD5E1 !important;
    }

    .category-start { border-left: 1px solid #CBD5E1 !important; }
    .category-end { border-right: 1px solid #CBD5E1 !important; }
    .period-cell { min-width: 70px; text-align: center; font-weight: 800; }
    .data-label-cell { min-width: 110px; }

    .sticky-col-no,
    .sticky-col-lab,
    .sticky-col-anggota,
    .sticky-col-data {
        position: sticky;
        z-index: 12;
        background: #FFFFFF !important;
    }

    .sticky-col-no { left: 0; min-width: 58px; }
    .sticky-col-lab { left: 58px; min-width: 190px; }
    .sticky-col-anggota { left: 248px; min-width: 92px; }
    .sticky-col-data {
        left: 340px;
        min-width: 110px;
        border-right: 1px solid #CBD5E1 !important;
    }

    thead .sticky-col-no,
    thead .sticky-col-lab,
    thead .sticky-col-anggota,
    thead .sticky-col-data {
        z-index: 50;
        background: #F8FAFC !important;
    }

    tbody tr:nth-child(4n + 3) td,
    tbody tr:nth-child(4n + 4) td {
        background: #FAFBFC;
    }

    .data-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 74px;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
    }

    .data-badge.target { color: #2563EB; background: #DBEAFE; }
    .data-badge.realisasi { color: #059669; background: #D1FAE5; }

    .floating-table-scroll {
        position: fixed;
        left: 320px;
        right: 32px;
        bottom: 16px;
        height: 18px;
        overflow-x: auto;
        overflow-y: hidden;
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 999px;
        z-index: 999;
        display: none;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .12);
    }

    .floating-table-scroll-inner { height: 1px; }

    @media (max-width: 1240px) {
        .kk-category-grid,
        .kk-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .kk-summary-progress-card {
            grid-column: span 2;
        }
    }

    @media (max-width: 720px) {
        .kk-filter-grid,
        .kk-category-grid,
        .kk-summary-grid {
            grid-template-columns: 1fr;
        }

        .kk-summary-progress-card {
            grid-column: span 1;
        }

        .floating-table-scroll {
            left: 16px;
            right: 16px;
        }
    }
</style>

<div class="kk-monitoring-page">
    <div class="page-heading">
        Monitoring <span class="muted">Lab Riset</span>
    </div>

    <div class="kk-monitoring-hero">
        <div class="kk-hero-row">
            <div>
                <div class="kk-card-title">Filter Periode Monitoring Lab Riset</div>
                <p class="kk-card-subtitle">
                    Monitoring saat ini:
                    <strong>{{ $labelPeriode ?? 'Triwulan Tahun ' . ($tahun ?? now()->year) }}</strong>
                </p>
                <span class="kk-period-pill">
                    <i class="bi bi-calendar-range"></i>
                    {{ $labelMode }}
                </span>
            </div>

            <a href="/ketuakk/dashboard" class="kk-btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>
        </div>

        <form action="/ketuakk/monitoring-lab-riset" method="GET">
            <div class="kk-filter-grid">
                <div class="kk-filter-group">
                    <label for="tahun">Tahun</label>
                    <select name="tahun" id="tahun" class="form-select">
                        @foreach($tahunOptions ?? [$tahun] as $itemTahun)
                            <option value="{{ $itemTahun }}" {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                                {{ $itemTahun }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="kk-filter-group">
                    <label for="periode">Jenis Periode</label>
                    <select name="periode" id="periode" class="form-select">
                        <option value="triwulan" {{ ($periode ?? 'triwulan') === 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                        <option value="semester" {{ ($periode ?? '') === 'semester' ? 'selected' : '' }}>Semester</option>
                    </select>
                </div>

                <div class="kk-filter-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="kk-btn-primary w-100">
                        <i class="bi bi-funnel-fill"></i>
                        Terapkan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="category-card-grid kk-category-grid">
        @foreach($rekapKategori as $item)
            @php
                $targetKategori = (int) ($item['target'] ?? 0);
                $realisasiKategori = (int) ($item['realisasi'] ?? 0);
                $sisaKategori = max($targetKategori - $realisasiKategori, 0);
                $progressKategori = min((int) ($item['progress'] ?? 0), 100);
            @endphp
            <div class="kk-category-card">
                <div class="kk-category-top">
                    <div class="kk-category-name">
                        {{ $item['kategori'] ?? '-' }}
                        <span class="kk-category-caption">Progress realisasi kategori KM · {{ $labelPeriode ?? 'periode aktif' }}</span>
                    </div>
                    <div class="kk-category-percent">{{ $progressKategori }}%</div>
                </div>

                <div class="kk-progress-track">
                    <div class="kk-progress-fill" style="width: {{ $progressKategori }}%;"></div>
                </div>

                <div class="kk-metric-grid">
                    <div class="kk-metric metric-target">
                        <div class="kk-metric-label"><i class="bi bi-bullseye"></i> Target</div>
                        <div class="kk-metric-value">{{ $targetKategori }}</div>
                    </div>
                    <div class="kk-metric metric-realisasi">
                        <div class="kk-metric-label"><i class="bi bi-check2-circle"></i> Realisasi</div>
                        <div class="kk-metric-value">{{ $realisasiKategori }}</div>
                    </div>
                    <div class="kk-metric metric-sisa">
                        <div class="kk-metric-label"><i class="bi bi-hourglass-split"></i> Sisa</div>
                        <div class="kk-metric-value">{{ $sisaKategori }}</div>
                    </div>
                    <div class="kk-metric metric-progress">
                        <div class="kk-metric-label"><i class="bi bi-graph-up-arrow"></i> Progress</div>
                        <div class="kk-metric-value">{{ $progressKategori }}%</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="kk-summary-grid">
        <div class="kk-monitoring-card">
            <div class="kk-summary-label">Jumlah Lab Riset</div>
            <div class="kk-summary-value primary">{{ $jumlahLab }}</div>
        </div>
        <div class="kk-monitoring-card">
            <div class="kk-summary-label">Jumlah Anggota</div>
            <div class="kk-summary-value primary">{{ $jumlahAnggota }}</div>
        </div>
        <div class="kk-monitoring-card">
            <div class="kk-summary-label">Target Periode</div>
            <div class="kk-summary-value primary">{{ $totalTarget }}</div>
        </div>
        <div class="kk-monitoring-card">
            <div class="kk-summary-label">Realisasi Periode</div>
            <div class="kk-summary-value success">{{ $totalRealisasi }}</div>
        </div>

        <div class="kk-monitoring-card kk-summary-progress-card">
            <div class="kk-progress-head">
                <span>Progress Total Lab Riset</span>
                <span>{{ $progressTotal }}%</span>
            </div>
            <div class="kk-progress-track">
                <div class="kk-progress-fill" style="width: {{ $progressTotal }}%;"></div>
            </div>
            <div class="kk-chip-row">
                <span class="kk-chip chip-primary"><i class="bi bi-layers-fill"></i> Target: {{ $totalTarget }}</span>
                <span class="kk-chip chip-success"><i class="bi bi-check-circle-fill"></i> Realisasi: {{ $totalRealisasi }}</span>
                <span class="kk-chip chip-warning"><i class="bi bi-hourglass-split"></i> Sisa: {{ $sisaTarget }}</span>
            </div>
        </div>
    </div>

    <div class="kk-table-card monitoring-table-card">
        <div class="kk-section-head mb-3">
            <div>
                <div class="kk-section-title">Monitoring Progress Lab Riset</div>
                <p class="kk-section-desc">
                    Target dan realisasi ditampilkan berdasarkan kategori KM serta periode yang dipilih.
                </p>
            </div>
        </div>

        <div class="table-scroll-sync">
            <div class="table-scroll-container">
                <table class="km-table">
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
                            <th rowspan="2" class="text-center">Aksi</th>
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
                                    <div class="small text-muted mt-1">Progress {{ $lab['progress'] ?? 0 }}%</div>
                                </td>

                                <td rowspan="2" class="sticky-col-anggota text-center fw-bold">
                                    {{ $lab['jumlah_anggota'] ?? 0 }}
                                </td>

                                <td class="sticky-col-data data-label-cell">
                                    <span class="data-badge target">Target</span>
                                </td>

                                @foreach($kategoriDefault as $kategori)
                                    @foreach($periodeColumns as $key => $label)
                                        <td class="period-cell {{ $loop->first ? 'category-start' : '' }} {{ $loop->last ? 'category-end' : '' }}">
                                            {{ $lab['data'][$kategori]['target'][$key] ?? 0 }}
                                        </td>
                                    @endforeach
                                @endforeach

                                <td class="fw-bold text-center">{{ $lab['total_target'] ?? 0 }}</td>

                                <td rowspan="2" class="text-center">
                                    <a
                                        href="/ketuakk/monitoring-lab-riset/{{ $lab['id_lab'] }}?tahun={{ $tahun }}&periode={{ $periode }}"
                                        class="kk-btn-soft">
                                        Detail
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td class="sticky-col-data data-label-cell">
                                    <span class="data-badge realisasi">Realisasi</span>
                                </td>

                                @foreach($kategoriDefault as $kategori)
                                    @foreach($periodeColumns as $key => $label)
                                        <td class="period-cell {{ $loop->first ? 'category-start' : '' }} {{ $loop->last ? 'category-end' : '' }}">
                                            {{ $lab['data'][$kategori]['realisasi'][$key] ?? 0 }}
                                        </td>
                                    @endforeach
                                @endforeach

                                <td class="fw-bold text-center">{{ $lab['total_realisasi'] ?? 0 }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 6 + (count($kategoriDefault) * count($periodeColumns)) }}">
                                    <div class="text-center text-muted py-4 fw-semibold">
                                        Belum ada data monitoring lab riset.
                                    </div>
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

            floatingScroll.style.display = isTableVisible && needHorizontalScroll ? 'block' : 'none';
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
