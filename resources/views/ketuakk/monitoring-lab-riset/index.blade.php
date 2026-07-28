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

    .ketuakk-lab-monitoring {
        margin-bottom: 20px;
        overflow: hidden;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        background: #FFFFFF;
    }

    .ketuakk-lab-monitoring__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        flex-wrap: wrap;
        padding: 20px 22px 18px;
        border-bottom: 1px solid #EEF2F7;
    }

    .ketuakk-lab-monitoring__heading {
        min-width: 0;
        max-width: 720px;
    }

    .ketuakk-lab-monitoring__eyebrow {
        margin-bottom: 5px;
        color: #2563EB;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .ketuakk-lab-monitoring__title {
        margin: 0;
        color: #0F172A;
        font-size: 23px;
        font-weight: 700;
        letter-spacing: -.02em;
        line-height: 1.25;
        text-wrap: balance;
    }

    .ketuakk-lab-monitoring__description {
        max-width: 65ch;
        margin: 7px 0 0;
        color: #64748B;
        font-size: 13px;
        line-height: 1.55;
    }

    .ketuakk-lab-monitoring__period {
        margin-top: 8px;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
    }

    .ketuakk-lab-monitoring__period span {
        color: #64748B;
        font-weight: 500;
    }

    .ketuakk-lab-monitoring__back {
        white-space: nowrap;
    }

    .ketuakk-lab-monitoring__toolbar {
        padding: 14px 22px;
        border-bottom: 1px solid #EEF2F7;
        background: #F8FAFC;
    }

    .ketuakk-lab-monitoring__filter {
        display: grid;
        grid-template-columns: minmax(124px, 160px) minmax(170px, 210px) auto;
        align-items: end;
        gap: 10px;
    }

    .ketuakk-lab-monitoring__filter-label {
        display: block;
        margin-bottom: 5px;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
    }

    .ketuakk-lab-monitoring__filter-select {
        min-width: 0;
    }

    .ketuakk-lab-monitoring__apply {
        width: auto;
        min-width: 128px;
        justify-self: start;
        white-space: nowrap;
    }

    .ketuakk-lab-monitoring__summary {
        padding: 18px 22px;
        border-bottom: 1px solid #EEF2F7;
    }

    .ketuakk-lab-monitoring__section-heading {
        margin-bottom: 12px;
    }

    .ketuakk-lab-monitoring__section-title {
        margin: 0;
        color: #0F172A;
        font-size: 14px;
        font-weight: 700;
    }

    .ketuakk-lab-monitoring__section-description {
        margin: 3px 0 0;
        color: #64748B;
        font-size: 12px;
        line-height: 1.45;
    }

    .ketuakk-lab-monitoring__metrics {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 1px;
        overflow: hidden;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        background: #EEF2F7;
    }

    .ketuakk-lab-monitoring__metric {
        min-width: 0;
        padding: 13px 14px;
        background: #FFFFFF;
    }

    .ketuakk-lab-monitoring__metric-label {
        margin-bottom: 5px;
        color: #64748B;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .035em;
        line-height: 1.35;
        text-transform: uppercase;
    }

    .ketuakk-lab-monitoring__metric-value {
        color: #0F172A;
        font-size: 20px;
        font-weight: 700;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-lab-monitoring__summary-progress {
        margin-top: 13px;
    }

    .ketuakk-lab-monitoring__progress-meta {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 7px;
        color: #475569;
        font-size: 11px;
        font-weight: 600;
    }

    .ketuakk-lab-monitoring__progress-value {
        color: #0F172A;
        font-size: 13px;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-lab-monitoring__progress {
        width: 100%;
        height: 6px;
        overflow: hidden;
        border-radius: 999px;
        background: #E2E8F0;
    }

    .ketuakk-lab-monitoring__progress-fill {
        height: 100%;
        border-radius: inherit;
        background: #2563EB;
    }

    .ketuakk-lab-monitoring__categories {
        padding: 18px 22px 20px;
    }

    .ketuakk-lab-monitoring__category-list {
        overflow: hidden;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        background: #FFFFFF;
    }

    .ketuakk-lab-monitoring__category-header,
    .ketuakk-lab-monitoring__category-row {
        display: grid;
        grid-template-columns: minmax(170px, 1.5fr) repeat(3, minmax(76px, .65fr)) minmax(180px, 1.3fr);
        align-items: center;
        gap: 14px;
        padding: 11px 16px;
    }

    .ketuakk-lab-monitoring__category-header {
        border-bottom: 1px solid #CBD5E1;
        background: #F8FAFC;
        color: #64748B;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    .ketuakk-lab-monitoring__category-row {
        min-height: 58px;
        border-bottom: 1px solid #EEF2F7;
    }

    .ketuakk-lab-monitoring__category-row:last-child {
        border-bottom: 0;
    }

    .ketuakk-lab-monitoring__category-name {
        min-width: 0;
        color: #0F172A;
        font-size: 13px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .ketuakk-lab-monitoring__category-number {
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .ketuakk-lab-monitoring__category-progress {
        min-width: 0;
    }

    .kk-btn-soft {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 12px;
        border: 1px solid #BFDBFE;
        border-radius: 10px;
        background: #EFF6FF;
        color: #2563EB;
        text-decoration: none;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .kk-btn-soft:hover {
        color: #1D4ED8;
        background: #DBEAFE;
    }

    .kk-table-card {
        padding: 17px;
        position: relative;
        border: 1px solid #E2E8F0;
        border-radius: 18px;
        background: #FFFFFF;
        box-shadow: 0 6px 16px rgba(15, 23, 42, .04);
    }

    .kk-section-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
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

    @media (max-width: 1199.98px) {
        .ketuakk-lab-monitoring__metrics {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .ketuakk-lab-monitoring__filter {
            grid-template-columns: 1fr;
        }

        .ketuakk-lab-monitoring__apply {
            width: 100%;
            justify-self: stretch;
        }

        .ketuakk-lab-monitoring__category-list {
            overflow-x: auto;
        }

        .ketuakk-lab-monitoring__category-header,
        .ketuakk-lab-monitoring__category-row {
            min-width: 720px;
        }
    }

    @media (max-width: 720px) {
        .ketuakk-lab-monitoring__header,
        .ketuakk-lab-monitoring__toolbar,
        .ketuakk-lab-monitoring__summary,
        .ketuakk-lab-monitoring__categories {
            padding-right: 16px;
            padding-left: 16px;
        }

        .ketuakk-lab-monitoring__metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .floating-table-scroll {
            left: 16px;
            right: 16px;
        }
    }
</style>

<div class="kk-monitoring-page">
    <section class="ketuakk-lab-monitoring" aria-labelledby="ketuakk-lab-monitoring-title">
        <header class="ketuakk-lab-monitoring__header">
            <div class="ketuakk-lab-monitoring__heading">
                <div class="ketuakk-lab-monitoring__eyebrow">Monitoring</div>
                <h1 id="ketuakk-lab-monitoring-title" class="ketuakk-lab-monitoring__title">
                    Monitoring Lab Riset
                </h1>
                <p class="ketuakk-lab-monitoring__description">
                    Pantau target dan realisasi Lab Riset berdasarkan kategori KM serta periode yang dipilih.
                </p>
                <div class="ketuakk-lab-monitoring__period">
                    {{ $labelPeriode ?? 'Triwulan Tahun ' . ($tahun ?? now()->year) }}
                    <span>- {{ $labelMode }}</span>
                </div>
            </div>

            <a href="/ketuakk/dashboard" class="btn btn-outline-secondary ketuakk-lab-monitoring__back">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
                Kembali
            </a>
        </header>

        <div class="ketuakk-lab-monitoring__toolbar">
            <form
                action="/ketuakk/monitoring-lab-riset"
                method="GET"
                class="ketuakk-lab-monitoring__filter">
                <div>
                    <label for="tahun" class="ketuakk-lab-monitoring__filter-label">Tahun</label>
                    <select
                        name="tahun"
                        id="tahun"
                        class="form-select form-select-sm ketuakk-lab-monitoring__filter-select">
                        @foreach($tahunOptions ?? [$tahun] as $itemTahun)
                            <option value="{{ $itemTahun }}" {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                                {{ $itemTahun }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="periode" class="ketuakk-lab-monitoring__filter-label">Jenis Periode</label>
                    <select
                        name="periode"
                        id="periode"
                        class="form-select form-select-sm ketuakk-lab-monitoring__filter-select">
                        <option value="triwulan" {{ ($periode ?? 'triwulan') === 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                        <option value="semester" {{ ($periode ?? '') === 'semester' ? 'selected' : '' }}>Semester</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-sm btn-outline-primary ketuakk-lab-monitoring__apply">
                    Terapkan
                </button>
            </form>
        </div>

        <section class="ketuakk-lab-monitoring__summary" aria-labelledby="ketuakk-lab-monitoring-summary-title">
            <div class="ketuakk-lab-monitoring__section-heading">
                <h2 id="ketuakk-lab-monitoring-summary-title" class="ketuakk-lab-monitoring__section-title">
                    Ringkasan keseluruhan
                </h2>
                <p class="ketuakk-lab-monitoring__section-description">
                    Rekap seluruh Lab Riset pada {{ $labelPeriode ?? 'periode aktif' }}.
                </p>
            </div>

            <div class="ketuakk-lab-monitoring__metrics">
                <div class="ketuakk-lab-monitoring__metric">
                    <div class="ketuakk-lab-monitoring__metric-label">Jumlah Lab Riset</div>
                    <div class="ketuakk-lab-monitoring__metric-value">{{ $jumlahLab }}</div>
                </div>
                <div class="ketuakk-lab-monitoring__metric">
                    <div class="ketuakk-lab-monitoring__metric-label">Jumlah Anggota</div>
                    <div class="ketuakk-lab-monitoring__metric-value">{{ $jumlahAnggota }}</div>
                </div>
                <div class="ketuakk-lab-monitoring__metric">
                    <div class="ketuakk-lab-monitoring__metric-label">Target Periode</div>
                    <div class="ketuakk-lab-monitoring__metric-value">{{ $totalTarget }}</div>
                </div>
                <div class="ketuakk-lab-monitoring__metric">
                    <div class="ketuakk-lab-monitoring__metric-label">Realisasi Periode</div>
                    <div class="ketuakk-lab-monitoring__metric-value">{{ $totalRealisasi }}</div>
                </div>
                <div class="ketuakk-lab-monitoring__metric">
                    <div class="ketuakk-lab-monitoring__metric-label">Sisa</div>
                    <div class="ketuakk-lab-monitoring__metric-value">{{ $sisaTarget }}</div>
                </div>
                <div class="ketuakk-lab-monitoring__metric">
                    <div class="ketuakk-lab-monitoring__metric-label">Progress Total</div>
                    <div class="ketuakk-lab-monitoring__metric-value">{{ $progressTotal }}%</div>
                </div>
            </div>

            <div class="ketuakk-lab-monitoring__summary-progress">
                <div class="ketuakk-lab-monitoring__progress-meta">
                    <span>Progress total Lab Riset</span>
                    <span class="ketuakk-lab-monitoring__progress-value">{{ $progressTotal }}%</span>
                </div>
                <div
                    class="ketuakk-lab-monitoring__progress"
                    role="progressbar"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="{{ $progressTotal }}"
                    aria-label="Progress total Lab Riset: {{ $progressTotal }} persen">
                    <div
                        class="ketuakk-lab-monitoring__progress-fill"
                        style="width: {{ min($progressTotal, 100) }}%;">
                    </div>
                </div>
            </div>
        </section>

        <section class="ketuakk-lab-monitoring__categories" aria-labelledby="ketuakk-lab-monitoring-categories-title">
            <div class="ketuakk-lab-monitoring__section-heading">
                <h2 id="ketuakk-lab-monitoring-categories-title" class="ketuakk-lab-monitoring__section-title">
                    Ringkasan per kategori
                </h2>
                <p class="ketuakk-lab-monitoring__section-description">
                    Progress realisasi kategori KM - {{ $labelPeriode ?? 'periode aktif' }}
                </p>
            </div>

            <div class="ketuakk-lab-monitoring__category-list">
                <div class="ketuakk-lab-monitoring__category-header" aria-hidden="true">
                    <span>Kategori</span>
                    <span class="text-end">Target</span>
                    <span class="text-end">Realisasi</span>
                    <span class="text-end">Sisa</span>
                    <span>Progress</span>
                </div>

                @foreach($rekapKategori as $item)
                    @php
                        $targetKategori = (int) ($item['target'] ?? 0);
                        $realisasiKategori = (int) ($item['realisasi'] ?? 0);
                        $sisaKategori = max($targetKategori - $realisasiKategori, 0);
                        $progressKategori = min((int) ($item['progress'] ?? 0), 100);
                    @endphp

                    <div class="ketuakk-lab-monitoring__category-row">
                        <div class="ketuakk-lab-monitoring__category-name">
                            {{ $item['kategori'] ?? '-' }}
                        </div>
                        <div class="ketuakk-lab-monitoring__category-number">{{ $targetKategori }}</div>
                        <div class="ketuakk-lab-monitoring__category-number">{{ $realisasiKategori }}</div>
                        <div class="ketuakk-lab-monitoring__category-number">{{ $sisaKategori }}</div>
                        <div class="ketuakk-lab-monitoring__category-progress">
                            <div class="ketuakk-lab-monitoring__progress-meta">
                                <span>Progress</span>
                                <span class="ketuakk-lab-monitoring__progress-value">{{ $progressKategori }}%</span>
                            </div>
                            <div
                                class="ketuakk-lab-monitoring__progress"
                                role="progressbar"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                aria-valuenow="{{ $progressKategori }}"
                                aria-label="Progress kategori {{ $item['kategori'] ?? '-' }}: {{ $progressKategori }} persen">
                                <div
                                    class="ketuakk-lab-monitoring__progress-fill"
                                    style="width: {{ min($progressKategori, 100) }}%;">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </section>

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
