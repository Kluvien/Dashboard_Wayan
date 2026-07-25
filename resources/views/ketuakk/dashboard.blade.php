@extends('layouts.app')

@section('title', 'Dashboard Ketua KK')

@section('content')
@php
    $monitoringAnggotaRows = collect($monitoringAnggotaRows ?? []);
    $tahunOptions = collect($tahunOptions ?? [$tahun ?? now()->year]);
    $mode = $mode ?? 'tahunan';
    $triwulan = (int) ($triwulan ?? 1);
    $semester = (int) ($semester ?? 1);
    $periodeLabel = $periodeLabel ?? ('Tahun ' . ($tahun ?? now()->year));
    $periodeKeterangan = $periodeKeterangan ?? 'Data target, penurunan, dan realisasi ditampilkan untuk satu tahun penuh.';
    $filterQuery = $filterQuery ?? http_build_query([
        'tahun' => $tahun ?? now()->year,
        'mode' => $mode,
    ]);

    $filterDashboardAktif = request()->hasAny(['mode', 'tahun', 'triwulan', 'semester']);
    $filterDashboardLabel = match ($mode) {
        'triwulan' => 'Triwulan ' . $triwulan . ' Tahun ' . ($tahun ?? now()->year),
        'semester' => 'Semester ' . $semester . ' Tahun ' . ($tahun ?? now()->year),
        default => 'Tahunan ' . ($tahun ?? now()->year),
    };
@endphp

<style>
    .dashboard-header {
        padding: 18px 22px;
    }

    /*
    |--------------------------------------------------------------------------
    | Ringkasan kategori KM
    |--------------------------------------------------------------------------
    */
    .dashboard-stat-grid {
        display: grid;
        /* Empat kategori aktif akan memenuhi satu baris tanpa menyisakan kolom kosong. */
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
        margin-bottom: 18px;
    }

    .dashboard-stat-card {
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 270px;
        padding: 17px;
        border: 1px solid #E2E8F0;
        border-radius: 18px;
        background: linear-gradient(180deg, #FFFFFF 0%, #FAFCFF 100%);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .dashboard-stat-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, #477EF7 0%, #7AA4FF 100%);
    }

    .dashboard-stat-card:hover {
        transform: translateY(-3px);
        border-color: #C7D7FF;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.10);
    }

    .dashboard-stat-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 10px;
    }

    .dashboard-stat-label {
        color: #475569;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.35;
    }

    .dashboard-stat-subtitle {
        margin-top: 2px;
        color: #94A3B8;
        font-size: 11px;
        font-weight: 600;
    }

    .dashboard-stat-value {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 64px;
        padding: 8px 10px;
        border: 1px solid #DCE7FF;
        border-radius: 13px;
        background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
        color: #2563EB;
        font-size: 22px;
        font-weight: 800;
        line-height: 1;
    }

    .dashboard-category-progress {
        height: 9px;
        border-radius: 999px;
        background: #E8EDF5;
        overflow: hidden;
        margin: 0 0 13px;
    }

    .dashboard-category-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #477EF7 0%, #6B9CFF 100%);
    }

    .dashboard-km-info {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
        margin-bottom: 12px;
    }

    .dashboard-km-item {
        min-height: 62px;
        padding: 9px 10px;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        background: #F8FAFC;
    }

    .dashboard-km-item-label {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 4px;
        color: #64748B;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.25px;
        text-transform: uppercase;
    }

    .dashboard-km-item-value {
        font-size: 21px;
        font-weight: 800;
        line-height: 1;
    }

    .dashboard-km-item.target-item {
        border-color: #BFDBFE;
        background: #EFF6FF;
    }

    .dashboard-km-item.realisasi-item {
        border-color: #BBF7D0;
        background: #ECFDF5;
    }

    .dashboard-km-item.diturunkan-item {
        border-color: #DDD6FE;
        background: #F5F3FF;
    }

    .dashboard-km-item.belum-turun-alert {
        border-color: #FECACA;
        background: #FEF2F2;
    }

    .dashboard-km-item.belum-turun-done {
        border-color: #BBF7D0;
        background: #ECFDF5;
    }

    .text-target {
        color: #2563EB;
    }

    .text-realisasi {
        color: #059669;
    }

    .text-diturunkan {
        color: #7C3AED;
    }

    .text-belum-turun {
        color: #DC2626;
    }

    .text-belum-turun-selesai {
        color: #15803D;
    }

    .dashboard-status-note {
        min-height: 31px;
        margin: auto 0 11px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.35;
    }

    .dashboard-status-note.alert {
        color: #DC2626;
    }

    .dashboard-status-note.done {
        color: #15803D;
    }

    .dashboard-category-button {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        padding: 9px 10px;
        border-radius: 11px;
        background: linear-gradient(90deg, #477EF7 0%, #5E91FB 100%);
        box-shadow: 0 6px 15px rgba(71, 126, 247, 0.20);
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
    }

    .dashboard-category-button:hover {
        color: #fff;
        opacity: 0.94;
    }

    .dashboard-grid-main {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 18px;
        margin-bottom: 18px;
    }

    .dashboard-panel-title {
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .dashboard-panel-subtitle {
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 14px;
    }

    .dashboard-chart-box {
        position: relative;
        width: 100%;
        height: 280px;
    }

    .dashboard-chart-box-small {
        position: relative;
        width: 100%;
        height: 230px;
    }

    .dashboard-category-chart-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 18px;
    }

    .dashboard-category-chart-grid .card:last-child:nth-child(odd) {
        grid-column: span 2;
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
        background: var(--blue);
    }

    /* Filter periode dashboard */
    .dashboard-filter-form {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .dashboard-filter-control {
        min-width: 132px;
        height: 40px;
        border: 1px solid #CBD5E1;
        border-radius: 10px;
        background: #FFFFFF;
        color: #1E293B;
        font-size: 14px;
        font-weight: 600;
        padding: 0 10px;
    }

    .dashboard-filter-control.small-control {
        min-width: 122px;
    }

    .dashboard-period-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
        padding: 6px 10px;
        border: 1px solid #D7E4FF;
        border-radius: 999px;
        background: #EFF6FF;
        color: #2563EB;
        font-size: 12px;
        font-weight: 800;
    }

    .dashboard-applied-filter-box {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 11px;
        border: 1px solid #BFDBFE;
        border-left: 4px solid #477EF7;
        border-radius: 12px;
        background: linear-gradient(90deg, #EFF6FF 0%, #FFFFFF 100%);
        color: #1D4ED8;
        box-shadow: 0 5px 12px rgba(37, 99, 235, .07);
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .dashboard-applied-filter-box .filter-label {
        color: #64748B;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .dashboard-applied-filter-box .filter-value {
        color: #1D4ED8;
        font-weight: 900;
    }

    @media (max-width: 576px) {
        .dashboard-applied-filter-box {
            width: 100%;
            justify-content: flex-start;
        }
    }

    .monitoring-table th,
    .monitoring-table td {
        white-space: nowrap;
        vertical-align: middle;
        font-size: 13px;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 11px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-success {
        background: #DCFCE7;
        color: #15803D;
    }

    .status-warning {
        background: #FEF3C7;
        color: #B45309;
    }

    .status-danger {
        background: #FEE2E2;
        color: #B91C1C;
    }

    .status-secondary {
        background: #E5E7EB;
        color: #475569;
    }

    @media (max-width: 1200px) {
        .dashboard-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-grid-main {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 992px) {
        .dashboard-category-chart-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-category-chart-grid .card:last-child:nth-child(odd) {
            grid-column: span 1;
        }
    }

    @media (max-width: 768px) {
        .dashboard-stat-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-km-info {
            grid-template-columns: 1fr;
        }
    }

    /* Dashboard Ketua KK: header, filter, action area, and category overview only. */
    .ketuakk-dashboard-overview {
        --kk-primary: #2563EB;
        --kk-primary-dark: #1D4ED8;
        --kk-text: #0F172A;
        --kk-muted: #64748B;
        --kk-border: #E2E8F0;
        margin-bottom: 18px;
    }

    .ketuakk-dashboard-overview__heading {
        margin-bottom: 12px;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .ketuakk-dashboard-overview__header {
        padding: 22px;
        border: 1px solid var(--kk-border);
        border-radius: 16px;
        background: #FFFFFF;
    }

    .ketuakk-dashboard-overview__header-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 22px;
        align-items: start;
    }

    .ketuakk-dashboard-overview__intro {
        max-width: 650px;
    }

    .ketuakk-dashboard-overview__title {
        margin: 0;
        color: var(--kk-text);
        font-size: clamp(24px, 2.2vw, 32px);
        font-weight: 800;
        letter-spacing: -.035em;
        line-height: 1.15;
        text-wrap: balance;
    }

    .ketuakk-dashboard-overview__description {
        max-width: 62ch;
        margin: 9px 0 0;
        color: var(--kk-muted);
        font-size: 14px;
        line-height: 1.6;
    }

    .ketuakk-dashboard-overview__period {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-top: 14px;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
    }

    .ketuakk-dashboard-overview__period i {
        color: var(--kk-primary);
    }

    .ketuakk-dashboard-overview__controls {
        display: grid;
        justify-items: end;
        gap: 12px;
    }

    .ketuakk-dashboard-overview__filter,
    .ketuakk-dashboard-overview__actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .ketuakk-dashboard-overview__filter-control {
        min-width: 132px;
        height: 40px;
        padding: 0 10px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        background: #FFFFFF;
        color: #1E293B;
        font-size: 13px;
        font-weight: 600;
    }

    .ketuakk-dashboard-overview__filter-control--small {
        min-width: 122px;
    }

    .ketuakk-dashboard-overview__filter-control:focus-visible,
    .ketuakk-dashboard-overview__header .btn:focus-visible,
    .ketuakk-dashboard-overview__detail:focus-visible {
        outline: 3px solid rgba(37, 99, 235, .22);
        outline-offset: 2px;
    }

    .ketuakk-dashboard-overview__stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-top: 14px;
    }

    .ketuakk-dashboard-overview__stat {
        display: flex;
        flex-direction: column;
        min-width: 0;
        min-height: 238px;
        padding: 17px;
        border: 1px solid var(--kk-border);
        border-radius: 14px;
        background: #FFFFFF;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .ketuakk-dashboard-overview__stat:hover {
        border-color: #CBD5E1;
        box-shadow: 0 6px 18px rgba(30, 64, 175, .06);
    }

    .ketuakk-dashboard-overview__stat-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 13px;
    }

    .ketuakk-dashboard-overview__stat-label {
        color: var(--kk-text);
        font-size: 15px;
        font-weight: 700;
        line-height: 1.35;
    }

    .ketuakk-dashboard-overview__stat-subtitle {
        margin-top: 3px;
        color: var(--kk-muted);
        font-size: 11px;
        font-weight: 500;
    }

    .ketuakk-dashboard-overview__stat-value {
        color: var(--kk-primary-dark);
        font-size: 21px;
        font-weight: 700;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-dashboard-overview__progress {
        height: 6px;
        margin-bottom: 16px;
        overflow: hidden;
        border-radius: 999px;
        background: #E2E8F0;
    }

    .ketuakk-dashboard-overview__progress-fill {
        height: 100%;
        border-radius: 999px;
        background: var(--kk-primary);
    }

    .ketuakk-dashboard-overview__metrics {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0 14px;
        margin-bottom: 13px;
    }

    .ketuakk-dashboard-overview__metric {
        min-width: 0;
        padding: 9px 0;
        border-top: 1px solid #EEF2F7;
    }

    .ketuakk-dashboard-overview__metric-label {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 4px;
        color: var(--kk-muted);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .ketuakk-dashboard-overview__metric-value {
        color: #334155;
        font-size: 19px;
        font-weight: 700;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-dashboard-overview__metric--attention .ketuakk-dashboard-overview__metric-value,
    .ketuakk-dashboard-overview__note--attention {
        color: #B91C1C;
    }

    .ketuakk-dashboard-overview__metric--complete .ketuakk-dashboard-overview__metric-value,
    .ketuakk-dashboard-overview__note--complete {
        color: #15803D;
    }

    .ketuakk-dashboard-overview__note {
        min-height: 30px;
        margin: auto 0 10px;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.4;
    }

    .ketuakk-dashboard-overview__detail {
        display: inline-flex;
        align-items: center;
        align-self: flex-start;
        gap: 5px;
        color: var(--kk-primary);
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
    }

    .ketuakk-dashboard-overview__detail:hover {
        color: var(--kk-primary-dark);
        text-decoration: underline;
    }

    @media (max-width: 1200px) {
        .ketuakk-dashboard-overview__header-layout {
            grid-template-columns: 1fr;
        }

        .ketuakk-dashboard-overview__controls {
            justify-items: start;
        }

        .ketuakk-dashboard-overview__filter,
        .ketuakk-dashboard-overview__actions {
            justify-content: flex-start;
        }

        .ketuakk-dashboard-overview__stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .ketuakk-dashboard-overview__header {
            padding: 18px;
        }

        .ketuakk-dashboard-overview__stats {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="ketuakk-dashboard-overview" aria-labelledby="ketuakkDashboardOverviewTitle">
<div class="ketuakk-dashboard-overview__heading">
    Dashboard Ketua KK
</div>

<div class="ketuakk-dashboard-overview__header">
    <div class="ketuakk-dashboard-overview__header-layout">
        <div class="ketuakk-dashboard-overview__intro">
            <h1 class="ketuakk-dashboard-overview__title" id="ketuakkDashboardOverviewTitle">
                Ringkasan Kontrak Manajemen {{ $periodeLabel }}
            </h1>
            <p class="ketuakk-dashboard-overview__description">
                Monitoring target KM, penurunan KM, realisasi, dan capaian setiap Lab Riset dalam Kelompok Keahlian.
            </p>
            <span class="ketuakk-dashboard-overview__period">
                <i class="bi bi-calendar3"></i>
                {{ $periodeKeterangan }}
            </span>
        </div>

        <div class="ketuakk-dashboard-overview__controls">
            <form method="GET" action="{{ url('/ketuakk/dashboard') }}" class="ketuakk-dashboard-overview__filter" aria-label="Filter periode dashboard">
                <select name="mode" id="dashboardPeriodMode" class="ketuakk-dashboard-overview__filter-control" aria-label="Mode periode">
                    <option value="tahunan" {{ $mode === 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                    <option value="triwulan" {{ $mode === 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                    <option value="semester" {{ $mode === 'semester' ? 'selected' : '' }}>Semester</option>
                </select>

                <select name="tahun" class="ketuakk-dashboard-overview__filter-control ketuakk-dashboard-overview__filter-control--small" aria-label="Tahun">
                    @foreach($tahunOptions as $itemTahun)
                        <option value="{{ $itemTahun }}" {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                            {{ $itemTahun }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="triwulan"
                    id="dashboardTriwulanGroup"
                    class="ketuakk-dashboard-overview__filter-control ketuakk-dashboard-overview__filter-control--small {{ $mode === 'triwulan' ? '' : 'd-none' }}"
                    aria-label="Triwulan">
                    @for($tw = 1; $tw <= 4; $tw++)
                        <option value="{{ $tw }}" {{ $triwulan === $tw ? 'selected' : '' }}>
                            Triwulan {{ $tw }}
                        </option>
                    @endfor
                </select>

                <select
                    name="semester"
                    id="dashboardSemesterGroup"
                    class="ketuakk-dashboard-overview__filter-control ketuakk-dashboard-overview__filter-control--small {{ $mode === 'semester' ? '' : 'd-none' }}"
                    aria-label="Semester">
                    <option value="1" {{ $semester === 1 ? 'selected' : '' }}>Semester 1</option>
                    <option value="2" {{ $semester === 2 ? 'selected' : '' }}>Semester 2</option>
                </select>

                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-funnel me-1"></i>
                    Terapkan
                </button>
            </form>

            <div class="ketuakk-dashboard-overview__actions" aria-label="Aksi kontrak manajemen">
                <a href="/ketuakk/target-km/create" class="btn btn-outline-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Tambah Target
                </a>

                <a href="/ketuakk/km-lab-riset/create" class="btn btn-primary">
                    <i class="bi bi-arrow-down-circle me-1"></i>
                    Turunkan KM ke Lab
                </a>
            </div>
        </div>
    </div>
</div>

@include('partials.periode-saat-ini')

<div class="ketuakk-dashboard-overview__stats">
    @forelse($kategoriCards ?? [] as $item)
        @php
            $targetKategori = (int) ($item['target'] ?? 0);
            $realisasiKategori = (int) ($item['realisasi'] ?? 0);
            $diturunkanKategori = (int) ($item['diturunkan'] ?? 0);
            $belumTurunKategori = (int) ($item['belum_turun'] ?? 0);
            $persentaseKategori = (float) ($item['persentase'] ?? 0);

            $belumTurunClass = $belumTurunKategori > 0
                ? 'belum-turun-alert'
                : 'belum-turun-done';

            $belumTurunTextClass = $belumTurunKategori > 0
                ? 'text-belum-turun'
                : 'text-belum-turun-selesai';
        @endphp

        <article class="ketuakk-dashboard-overview__stat">
            <div class="ketuakk-dashboard-overview__stat-top">
                <div>
                    <div class="ketuakk-dashboard-overview__stat-label">
                        {{ $item['kategori'] ?? '-' }}
                    </div>

                    <div class="ketuakk-dashboard-overview__stat-subtitle">
                        Progress realisasi kategori KM • {{ $periodeLabel }}
                    </div>
                </div>

                <div class="ketuakk-dashboard-overview__stat-value">
                    {{ rtrim(rtrim(number_format($persentaseKategori, 1), '0'), '.') }}%
                </div>
            </div>

            <div class="ketuakk-dashboard-overview__progress">
                <div
                    class="ketuakk-dashboard-overview__progress-fill"
                    style="width: {{ min($persentaseKategori, 100) }}%;">
                </div>
            </div>

            <div class="ketuakk-dashboard-overview__metrics">
                <div class="ketuakk-dashboard-overview__metric">
                    <div class="ketuakk-dashboard-overview__metric-label">
                        <i class="bi bi-bullseye"></i>
                        Target
                    </div>

                    <div class="ketuakk-dashboard-overview__metric-value">
                        {{ number_format($targetKategori, 0, ',', '.') }}
                    </div>
                </div>

                <div class="ketuakk-dashboard-overview__metric">
                    <div class="ketuakk-dashboard-overview__metric-label">
                        <i class="bi bi-check2-circle"></i>
                        Realisasi
                    </div>

                    <div class="ketuakk-dashboard-overview__metric-value">
                        {{ number_format($realisasiKategori, 0, ',', '.') }}
                    </div>
                </div>

                <div class="ketuakk-dashboard-overview__metric">
                    <div class="ketuakk-dashboard-overview__metric-label">
                        <i class="bi bi-arrow-down-circle"></i>
                        Diturunkan
                    </div>

                    <div class="ketuakk-dashboard-overview__metric-value">
                        {{ number_format($diturunkanKategori, 0, ',', '.') }}
                    </div>
                </div>

                <div class="ketuakk-dashboard-overview__metric {{ $belumTurunKategori > 0 ? 'ketuakk-dashboard-overview__metric--attention' : 'ketuakk-dashboard-overview__metric--complete' }}">
                    <div class="ketuakk-dashboard-overview__metric-label">
                        <i class="bi bi-exclamation-circle"></i>
                        Belum Turun
                    </div>

                    <div class="ketuakk-dashboard-overview__metric-value">
                        {{ number_format($belumTurunKategori, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="ketuakk-dashboard-overview__note {{ $belumTurunKategori > 0 ? 'ketuakk-dashboard-overview__note--attention' : 'ketuakk-dashboard-overview__note--complete' }}">
                @if($belumTurunKategori > 0)
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Masih ada {{ number_format($belumTurunKategori, 0, ',', '.') }} KM yang belum diturunkan.
                @else
                    <i class="bi bi-check-circle-fill me-1"></i>
                    Seluruh target kategori sudah diturunkan.
                @endif
            </div>

            <a href="/ketuakk/km-kk?tahun={{ $tahun }}" class="ketuakk-dashboard-overview__detail">
                Lihat detail <span aria-hidden="true">→</span>
            </a>
        </article>
    @empty
        <article class="ketuakk-dashboard-overview__stat">
            <div class="ketuakk-dashboard-overview__stat-label">Data kategori KM</div>
            <div class="ketuakk-dashboard-overview__stat-subtitle">Belum ada target KM pada tahun ini.</div>
        </article>
    @endforelse
</div>
</section>

<div class="dashboard-grid-main">
    <div class="card">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-2">
            <div>
                <div class="dashboard-panel-title">Diagram Pencapaian Lab Riset</div>
                <div class="dashboard-panel-subtitle mb-0">
                    Persentase pencapaian realisasi KM pada masing-masing Lab Riset untuk {{ $periodeLabel }}.
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
                @if($filterDashboardAktif)
                    <div class="dashboard-applied-filter-box">
                        <i class="bi bi-funnel-fill"></i>
                        <span class="filter-label">Filter</span>
                        <span class="filter-value">{{ $filterDashboardLabel }}</span>
                    </div>
                @endif

                <a
                    href="/ketuakk/monitoring-lab-riset?tahun={{ $tahun }}&periode=triwulan"
                    class="btn btn-primary btn-sm">
                    Lihat Selengkapnya
                </a>
            </div>
        </div>

        <div class="dashboard-chart-box">
            <canvas id="chartLabAchievement"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-3">
            <div>
                <h4 class="fw-bold mb-1">Ringkasan</h4>
                <p class="text-muted mb-0">
                    Rekap jumlah lab, anggota KK, dan progres total KM Kelompok Keahlian.
                </p>
            </div>

            @if($filterDashboardAktif)
                <div class="dashboard-applied-filter-box">
                    <i class="bi bi-funnel-fill"></i>
                    <span class="filter-label">Filter</span>
                    <span class="filter-value">{{ $filterDashboardLabel }}</span>
                </div>
            @endif
        </div>

        <div class="row g-3">
            <div class="col-6">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small">Jumlah Lab</div>
                    <div class="fs-3 fw-bold">{{ $jumlahLab ?? 0 }}</div>
                </div>
            </div>

            <div class="col-6">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small">Jumlah Anggota KK</div>
                    <div class="fs-3 fw-bold">{{ $jumlahAnggotaKk ?? 0 }}</div>
                </div>
            </div>

            <div class="col-6">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small">Lab Menyelesaikan Target KM</div>

                    <div class="fs-3 fw-bold text-success">
                        {{ $jumlahLabSelesai ?? 0 }}
                        <span class="fs-5 text-muted">/ {{ $jumlahLab ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small">Anggota Menyelesaikan Target KM</div>

                    <div class="fs-3 fw-bold text-warning">
                        {{ $jumlahAnggotaSelesai ?? 0 }}
                        <span class="fs-5 text-muted">/ {{ $jumlahAnggotaKk ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <div class="d-flex justify-content-between mb-1">
                <span class="fw-bold">Progress Total KM KK ({{ $periodeLabel }})</span>
                <span class="fw-bold">{{ $persentaseRealisasi ?? 0 }}%</span>
            </div>

            <div class="progress-soft">
                <div
                    class="progress-soft-fill"
                    style="width: {{ $persentaseRealisasi ?? 0 }}%;">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-category-chart-grid">
    @foreach($kategoriDetailCharts ?? [] as $index => $chart)
        <div class="card">
            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-2">
                <div>
                    <div class="dashboard-panel-title">{{ $chart['kategori'] }}</div>
                    <div class="dashboard-panel-subtitle mb-0">
                        Target dan realisasi berdasarkan sub kategori KM.
                    </div>
                </div>

                @if($filterDashboardAktif)
                    <div class="dashboard-applied-filter-box">
                        <i class="bi bi-funnel-fill"></i>
                        <span class="filter-label">Filter</span>
                        <span class="filter-value">{{ $filterDashboardLabel }}</span>
                    </div>
                @endif
            </div>

            <div class="dashboard-chart-box-small">
                <canvas id="chartKategoriDetail{{ $index }}"></canvas>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Monitoring Anggota KK</h4>
            <p class="text-muted mb-0">
                Menampilkan 10 anggota pertama beserta target, realisasi, dan status capaian KM pada {{ $periodeLabel }}.
            </p>
        </div>

        <a
            href="/ketuakk/monitoring-anggota-kk?tahun={{ $tahun }}&periode=triwulan"
            class="btn btn-primary">
            Lihat Selengkapnya
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 monitoring-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Anggota</th>
                    <th>Lab Riset</th>
                    <th>NIDN</th>
                    <th>JAD</th>
                    <th>Target</th>
                    <th>Realisasi</th>
                    <th>Sisa</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($monitoringAnggotaRows as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td class="fw-bold">
                            {{ $item['nama_dosen'] }}
                        </td>

                        <td>
                            {{ $item['nama_lab'] }}
                        </td>

                        <td>
                            {{ $item['nidn'] }}
                        </td>

                        <td>
                            <span class="badge bg-primary">
                                {{ $item['jad'] }}
                            </span>
                        </td>

                        <td>
                            {{ $item['target'] }}
                        </td>

                        <td>
                            {{ $item['realisasi'] }}
                        </td>

                        <td>
                            {{ $item['sisa'] }}
                        </td>

                        <td style="min-width: 160px;">
                            <div class="progress-soft mb-1">
                                <div
                                    class="progress-soft-fill"
                                    style="width: {{ $item['progress'] }}%;">
                                </div>
                            </div>

                            <div class="small text-muted">
                                {{ $item['progress'] }}%
                            </div>
                        </td>

                        <td>
                            @if($item['status_class'] === 'success')
                                <span class="status-pill status-success">
                                    {{ $item['status'] }}
                                </span>
                            @elseif($item['status_class'] === 'warning')
                                <span class="status-pill status-warning">
                                    {{ $item['status'] }}
                                </span>
                            @elseif($item['status_class'] === 'danger')
                                <span class="status-pill status-danger">
                                    {{ $item['status'] }}
                                </span>
                            @else
                                <span class="status-pill status-secondary">
                                    {{ $item['status'] }}
                                </span>
                            @endif
                        </td>

                        <td>
                            <a
                                href="/ketuakk/monitoring-anggota-kk/{{ $item['id_user'] }}?tahun={{ $tahun }}"
                                class="btn btn-primary btn-sm">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            Belum ada data anggota KK.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modeSelect = document.getElementById('dashboardPeriodMode');
        const triwulanSelect = document.getElementById('dashboardTriwulanGroup');
        const semesterSelect = document.getElementById('dashboardSemesterGroup');

        const toggleDashboardPeriodFields = function() {
            if (!modeSelect || !triwulanSelect || !semesterSelect) {
                return;
            }

            triwulanSelect.classList.toggle('d-none', modeSelect.value !== 'triwulan');
            semesterSelect.classList.toggle('d-none', modeSelect.value !== 'semester');
        };

        if (modeSelect) {
            modeSelect.addEventListener('change', toggleDashboardPeriodFields);
            toggleDashboardPeriodFields();
        }

        const labLabels = @json($labShortLabels ?? $labChartLabels ?? []);
        const labAchievementPercentages = @json($labAchievementPercentages ?? []);
        const kategoriDetailCharts = @json($kategoriDetailCharts ?? []);

        const blue = '#477EF7';
        const green = '#22C55E';

        const defaultOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        usePointStyle: true
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        };

        const chartLabAchievementElement = document.getElementById('chartLabAchievement');

        if (chartLabAchievementElement) {
            new Chart(chartLabAchievementElement, {
                type: 'bar',
                data: {
                    labels: labLabels,
                    datasets: [{
                        label: 'Pencapaian (%)',
                        data: labAchievementPercentages,
                        backgroundColor: blue,
                        borderRadius: 8
                    }]
                },
                options: {
                    ...defaultOptions,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        kategoriDetailCharts.forEach(function(chart, index) {
            const chartElement = document.getElementById(
                'chartKategoriDetail' + index
            );

            if (!chartElement) {
                return;
            }

            new Chart(chartElement, {
                type: 'bar',
                data: {
                    labels: chart.labels,
                    datasets: [{
                            label: 'Target',
                            data: chart.targets,
                            backgroundColor: blue,
                            borderRadius: 8
                        },
                        {
                            label: 'Realisasi',
                            data: chart.realisasi,
                            backgroundColor: green,
                            borderRadius: 8
                        }
                    ]
                },
                options: defaultOptions
            });
        });
    });
</script>
@endsection
