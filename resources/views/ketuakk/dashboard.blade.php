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

    @media (max-width: 1200px) {
        .dashboard-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-grid-main {
            grid-template-columns: 1fr;
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

    .ketuakk-dashboard-overview__lab-section {
        display: grid;
        grid-template-columns: minmax(0, 1.75fr) minmax(280px, .75fr);
        gap: 14px;
        align-items: stretch;
        margin-bottom: 18px;
    }

    .ketuakk-dashboard-overview__lab-chart,
    .ketuakk-dashboard-overview__summary {
        min-width: 0;
        border: 1px solid var(--kk-border);
        background: #FFFFFF;
    }

    .ketuakk-dashboard-overview__lab-chart {
        padding: 20px 22px 18px;
        border-radius: 16px;
    }

    .ketuakk-dashboard-overview__lab-chart-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 12px;
    }

    .ketuakk-dashboard-overview__section-copy {
        min-width: 0;
        max-width: 62ch;
    }

    .ketuakk-dashboard-overview__section-title {
        margin: 0;
        color: var(--kk-text);
        font-size: 18px;
        font-weight: 750;
        letter-spacing: -.02em;
        line-height: 1.3;
    }

    .ketuakk-dashboard-overview__section-description {
        margin: 5px 0 0;
        color: var(--kk-muted);
        font-size: 12px;
        line-height: 1.55;
    }

    .ketuakk-dashboard-overview__chart-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
        flex: 0 0 auto;
    }

    .ketuakk-dashboard-overview__active-filter {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #475569;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .ketuakk-dashboard-overview__active-filter i {
        color: var(--kk-primary);
    }

    .ketuakk-dashboard-overview__chart-link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 7px 10px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: border-color .2s ease, color .2s ease, background-color .2s ease;
    }

    .ketuakk-dashboard-overview__chart-link:hover {
        border-color: #93C5FD;
        background: #F8FAFC;
        color: var(--kk-primary-dark);
    }

    .ketuakk-dashboard-overview__chart-link:focus-visible {
        outline: 3px solid rgba(37, 99, 235, .22);
        outline-offset: 2px;
    }

    .ketuakk-dashboard-overview__lab-chart-box {
        position: relative;
        width: 100%;
        height: 300px;
    }

    .ketuakk-dashboard-overview__summary {
        display: flex;
        flex-direction: column;
        padding: 20px 18px 18px;
        border-radius: 12px;
    }

    .ketuakk-dashboard-overview__summary-header {
        padding-bottom: 14px;
        border-bottom: 1px solid #EEF2F7;
    }

    .ketuakk-dashboard-overview__summary-filter {
        margin-top: 9px;
    }

    .ketuakk-dashboard-overview__summary-list {
        margin: 0;
    }

    .ketuakk-dashboard-overview__summary-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 14px;
        align-items: center;
        padding: 13px 0;
        border-bottom: 1px solid #EEF2F7;
    }

    .ketuakk-dashboard-overview__summary-item dt,
    .ketuakk-dashboard-overview__summary-item dd {
        margin: 0;
    }

    .ketuakk-dashboard-overview__summary-item dt {
        color: #475569;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
    }

    .ketuakk-dashboard-overview__summary-value {
        color: var(--kk-text);
        font-size: 20px;
        font-weight: 750;
        line-height: 1;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .ketuakk-dashboard-overview__summary-value--complete {
        color: #15803D;
    }

    .ketuakk-dashboard-overview__summary-total {
        margin-top: auto;
        padding-top: 16px;
    }

    .ketuakk-dashboard-overview__summary-progress-label {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 7px;
        color: #334155;
        font-size: 12px;
        font-weight: 650;
        line-height: 1.4;
    }

    .ketuakk-dashboard-overview__summary-progress-value {
        color: var(--kk-primary-dark);
        font-weight: 750;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .ketuakk-dashboard-overview__summary-progress {
        height: 7px;
        overflow: hidden;
        border-radius: 999px;
        background: #E2E8F0;
    }

    .ketuakk-dashboard-overview__summary-progress-fill {
        height: 100%;
        border-radius: inherit;
        background: var(--kk-primary);
    }

    .ketuakk-dashboard-overview__comparison {
        margin-bottom: 18px;
        padding: 20px 22px;
        border: 1px solid var(--kk-border);
        border-radius: 14px;
        background: #FFFFFF;
    }

    .ketuakk-dashboard-overview__comparison-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        padding-bottom: 16px;
        border-bottom: 1px solid #E2E8F0;
    }

    .ketuakk-dashboard-overview__comparison-groups {
        display: grid;
    }

    .ketuakk-dashboard-overview__comparison-group {
        padding: 17px 0 5px;
    }

    .ketuakk-dashboard-overview__comparison-group + .ketuakk-dashboard-overview__comparison-group {
        border-top: 1px solid #CBD5E1;
    }

    .ketuakk-dashboard-overview__comparison-category {
        margin: 0 0 10px;
        color: var(--kk-text);
        font-size: 14px;
        font-weight: 700;
        line-height: 1.4;
    }

    .ketuakk-dashboard-overview__comparison-columns,
    .ketuakk-dashboard-overview__comparison-row {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) repeat(3, minmax(72px, 96px)) minmax(210px, .8fr);
        column-gap: 16px;
        align-items: center;
    }

    .ketuakk-dashboard-overview__comparison-columns {
        padding: 0 16px 8px;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .ketuakk-dashboard-overview__comparison-columns span:not(:first-child) {
        text-align: right;
    }

    .ketuakk-dashboard-overview__comparison-columns span:last-child {
        text-align: left;
    }

    .ketuakk-dashboard-overview__comparison-row {
        min-height: 56px;
        padding: 11px 16px;
        border-top: 1px solid #EEF2F7;
    }

    .ketuakk-dashboard-overview__comparison-row:hover {
        background: #F8FAFC;
    }

    .ketuakk-dashboard-overview__comparison-name {
        min-width: 0;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .ketuakk-dashboard-overview__comparison-number {
        color: var(--kk-text);
        font-size: 13px;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        text-align: right;
        white-space: nowrap;
    }

    .ketuakk-dashboard-overview__comparison-progress {
        min-width: 0;
    }

    .ketuakk-dashboard-overview__comparison-progress-meta {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 6px;
        color: #475569;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.35;
    }

    .ketuakk-dashboard-overview__comparison-progress-meta span:last-child {
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .ketuakk-dashboard-overview__comparison-track {
        height: 6px;
        overflow: hidden;
        border-radius: 999px;
        background: #E2E8F0;
    }

    .ketuakk-dashboard-overview__comparison-fill {
        height: 100%;
        border-radius: inherit;
        background: var(--kk-primary);
    }

    .ketuakk-dashboard-overview__comparison-progress--complete .ketuakk-dashboard-overview__comparison-progress-meta {
        color: #15803D;
    }

    .ketuakk-dashboard-overview__comparison-progress--complete .ketuakk-dashboard-overview__comparison-fill {
        background: #16A34A;
    }

    .ketuakk-dashboard-overview__comparison-progress--no-target .ketuakk-dashboard-overview__comparison-progress-meta {
        color: #64748B;
    }

    .ketuakk-dashboard-overview__comparison-progress--not-started .ketuakk-dashboard-overview__comparison-progress-meta {
        color: #64748B;
    }

    .ketuakk-dashboard-overview__comparison-progress--no-target .ketuakk-dashboard-overview__comparison-fill,
    .ketuakk-dashboard-overview__comparison-progress--not-started .ketuakk-dashboard-overview__comparison-fill {
        background: #94A3B8;
    }

    .ketuakk-dashboard-overview__comparison-empty {
        padding: 24px 16px;
        border-top: 1px solid #EEF2F7;
        color: #64748B;
        font-size: 13px;
        text-align: center;
    }

    .ketuakk-dashboard-overview__monitoring {
        overflow: hidden;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        background: #FFFFFF;
    }

    .ketuakk-dashboard-overview__monitoring-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        flex-wrap: wrap;
        padding: 20px 22px;
        border-bottom: 1px solid #E2E8F0;
    }

    .ketuakk-dashboard-overview__monitoring-scroll {
        overflow-x: auto;
    }

    table.ketuakk-dashboard-overview__monitoring-table {
        width: 100%;
        min-width: 1080px;
        margin: 0;
        border: 0 !important;
        border-radius: 0;
        background: #FFFFFF;
    }

    .ketuakk-dashboard-overview__monitoring-table > thead > tr > th {
        height: 44px;
        padding: 10px 16px !important;
        border: 0 !important;
        border-bottom: 1px solid #E2E8F0 !important;
        background: #F8FAFC;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .05em;
        line-height: 1.3;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .ketuakk-dashboard-overview__monitoring-table > tbody > tr > td {
        height: 56px;
        padding: 11px 16px !important;
        border: 0 !important;
        border-bottom: 1px solid #EEF2F7 !important;
        background: #FFFFFF;
        color: #334155;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.4;
        vertical-align: middle;
    }

    .ketuakk-dashboard-overview__monitoring-table > tbody > tr:last-child > td {
        border-bottom: 0 !important;
    }

    .ketuakk-dashboard-overview__monitoring-table > tbody > tr:hover > td {
        background: #F8FAFC;
    }

    .ketuakk-dashboard-overview__monitoring-table th:first-child,
    .ketuakk-dashboard-overview__monitoring-table td:first-child {
        text-align: center;
        white-space: nowrap;
    }

    .ketuakk-dashboard-overview__monitoring-cell--identity {
        min-width: 150px;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .ketuakk-dashboard-overview__monitoring-identity-primary {
        color: #0F172A;
        font-weight: 700;
    }

    .ketuakk-dashboard-overview__monitoring-cell--number {
        color: #0F172A !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
        text-align: right;
        white-space: nowrap;
    }

    .ketuakk-dashboard-overview__monitoring-table th.ketuakk-dashboard-overview__monitoring-cell--number {
        color: #64748B !important;
    }

    .ketuakk-dashboard-overview__monitoring-nidn,
    .ketuakk-dashboard-overview__monitoring-cell--status,
    .ketuakk-dashboard-overview__monitoring-cell--action {
        white-space: nowrap;
    }

    .ketuakk-dashboard-overview__monitoring-cell--status,
    .ketuakk-dashboard-overview__monitoring-cell--action {
        text-align: center;
    }

    .ketuakk-dashboard-overview__monitoring-jad {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        padding: 3px 7px;
        border: 1px solid #E2E8F0;
        border-radius: 6px;
        background: #F8FAFC;
        color: #475569;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.2;
        white-space: nowrap;
    }

    .ketuakk-dashboard-overview__monitoring-progress {
        min-width: 150px;
    }

    .ketuakk-dashboard-overview__monitoring-progress-meta {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 5px;
        color: #475569;
        font-size: 11px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .ketuakk-dashboard-overview__monitoring-progress-track {
        height: 6px;
        overflow: hidden;
        border-radius: 999px;
        background: #E2E8F0;
    }

    .ketuakk-dashboard-overview__monitoring-progress-fill {
        height: 100%;
        border-radius: inherit;
        background: #2563EB;
    }

    .ketuakk-dashboard-overview__monitoring-progress--complete .ketuakk-dashboard-overview__monitoring-progress-meta {
        color: #15803D;
    }

    .ketuakk-dashboard-overview__monitoring-progress--complete .ketuakk-dashboard-overview__monitoring-progress-fill {
        background: #16A34A;
    }

    .ketuakk-dashboard-overview__monitoring-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
    }

    .ketuakk-dashboard-overview__monitoring-status::before {
        content: "";
        width: 6px;
        height: 6px;
        flex: 0 0 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .ketuakk-dashboard-overview__monitoring-status--complete {
        background: #F0FDF4;
        color: #15803D;
    }

    .ketuakk-dashboard-overview__monitoring-status--pending {
        background: #FFFBEB;
        color: #A16207;
    }

    .ketuakk-dashboard-overview__monitoring-status--neutral {
        background: #F1F5F9;
        color: #475569;
    }

    .ketuakk-dashboard-overview__monitoring-status--error {
        background: #FEF2F2;
        color: #B91C1C;
    }

    .ketuakk-dashboard-overview__monitoring-link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 8px;
        border: 1px solid #CBD5E1;
        border-radius: 7px;
        background: #FFFFFF;
        color: #1D4ED8;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.2;
        text-decoration: none;
    }

    .ketuakk-dashboard-overview__monitoring-link:hover {
        border-color: #93C5FD;
        background: #F8FAFC;
        color: #1D4ED8;
    }

    .ketuakk-dashboard-overview__monitoring-link:focus-visible {
        outline: 3px solid rgba(37, 99, 235, .22);
        outline-offset: 2px;
    }

    .ketuakk-dashboard-overview__monitoring-empty {
        padding: 24px 16px !important;
        color: #64748B !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        text-align: center;
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

        .ketuakk-dashboard-overview__lab-section {
            grid-template-columns: minmax(0, 1.45fr) minmax(270px, .75fr);
        }

        .ketuakk-dashboard-overview__comparison-columns,
        .ketuakk-dashboard-overview__comparison-row {
            grid-template-columns: minmax(180px, 1fr) repeat(3, minmax(64px, 82px)) minmax(190px, .75fr);
            column-gap: 12px;
        }
    }

    @media (max-width: 992px) {
        .ketuakk-dashboard-overview__lab-section {
            grid-template-columns: 1fr;
        }

        .ketuakk-dashboard-overview__summary-total {
            margin-top: 0;
        }
    }

    @media (max-width: 768px) {
        .ketuakk-dashboard-overview__header {
            padding: 18px;
        }

        .ketuakk-dashboard-overview__stats {
            grid-template-columns: 1fr;
        }

        .ketuakk-dashboard-overview__lab-chart-header {
            display: grid;
        }

        .ketuakk-dashboard-overview__chart-actions {
            justify-content: flex-start;
        }

        .ketuakk-dashboard-overview__lab-chart-box {
            height: 270px;
        }

        .ketuakk-dashboard-overview__comparison {
            padding-right: 18px;
            padding-left: 18px;
        }

        .ketuakk-dashboard-overview__comparison-columns {
            display: none;
        }

        .ketuakk-dashboard-overview__comparison-row {
            grid-template-columns: minmax(0, 1fr) repeat(3, minmax(58px, 72px));
            row-gap: 10px;
        }

        .ketuakk-dashboard-overview__comparison-progress {
            grid-column: 1 / -1;
        }

        .ketuakk-dashboard-overview__comparison-number::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 3px;
            color: #64748B;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
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
                        Didistribusikan
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
                    Masih ada {{ number_format($belumTurunKategori, 0, ',', '.') }} KM yang belum didistribusikan.
                @else
                    <i class="bi bi-check-circle-fill me-1"></i>
                    Seluruh target kategori sudah didistribusikan.
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

<section class="ketuakk-dashboard-overview ketuakk-dashboard-overview__lab-section" aria-labelledby="labAchievementTitle">
    <article class="ketuakk-dashboard-overview__lab-chart">
        <header class="ketuakk-dashboard-overview__lab-chart-header">
            <div class="ketuakk-dashboard-overview__section-copy">
                <h2 class="ketuakk-dashboard-overview__section-title" id="labAchievementTitle">
                    Diagram Pencapaian Lab Riset
                </h2>
                <p class="ketuakk-dashboard-overview__section-description">
                    Persentase pencapaian realisasi KM pada masing-masing Lab Riset untuk {{ $periodeLabel }}.
                </p>
            </div>

            <div class="ketuakk-dashboard-overview__chart-actions">
                @if($filterDashboardAktif)
                    <div class="ketuakk-dashboard-overview__active-filter">
                        <i class="bi bi-funnel"></i>
                        <span>{{ $filterDashboardLabel }}</span>
                    </div>
                @endif

                <a
                    href="/ketuakk/monitoring-lab-riset?tahun={{ $tahun }}&periode=triwulan"
                    class="ketuakk-dashboard-overview__chart-link">
                    Lihat Selengkapnya <span aria-hidden="true">→</span>
                </a>
            </div>
        </header>

        <div class="ketuakk-dashboard-overview__lab-chart-box">
            <canvas id="chartLabAchievement"></canvas>
        </div>
    </article>

    <aside class="ketuakk-dashboard-overview__summary" aria-labelledby="dashboardSummaryTitle">
        <header class="ketuakk-dashboard-overview__summary-header">
            <h2 class="ketuakk-dashboard-overview__section-title" id="dashboardSummaryTitle">
                Ringkasan
            </h2>
            <p class="ketuakk-dashboard-overview__section-description">
                    Rekap jumlah lab, anggota KK, dan progres total KM Kelompok Keahlian.
            </p>

            @if($filterDashboardAktif)
                <div class="ketuakk-dashboard-overview__active-filter ketuakk-dashboard-overview__summary-filter">
                    <i class="bi bi-funnel"></i>
                    <span>{{ $filterDashboardLabel }}</span>
                </div>
            @endif
        </header>

        <dl class="ketuakk-dashboard-overview__summary-list">
            <div class="ketuakk-dashboard-overview__summary-item">
                <dt>Jumlah Lab</dt>
                <dd class="ketuakk-dashboard-overview__summary-value">{{ $jumlahLab ?? 0 }}</dd>
            </div>

            <div class="ketuakk-dashboard-overview__summary-item">
                <dt>Jumlah Anggota KK</dt>
                <dd class="ketuakk-dashboard-overview__summary-value">{{ $jumlahAnggotaKk ?? 0 }}</dd>
            </div>

            <div class="ketuakk-dashboard-overview__summary-item">
                <dt>Lab menyelesaikan target KM</dt>
                <dd class="ketuakk-dashboard-overview__summary-value {{ (int) ($jumlahLab ?? 0) > 0 && (int) ($jumlahLabSelesai ?? 0) === (int) ($jumlahLab ?? 0) ? 'ketuakk-dashboard-overview__summary-value--complete' : '' }}">
                    {{ $jumlahLabSelesai ?? 0 }} / {{ $jumlahLab ?? 0 }}
                </dd>
            </div>

            <div class="ketuakk-dashboard-overview__summary-item">
                <dt>Anggota menyelesaikan target KM</dt>
                <dd class="ketuakk-dashboard-overview__summary-value {{ (int) ($jumlahAnggotaKk ?? 0) > 0 && (int) ($jumlahAnggotaSelesai ?? 0) === (int) ($jumlahAnggotaKk ?? 0) ? 'ketuakk-dashboard-overview__summary-value--complete' : '' }}">
                    {{ $jumlahAnggotaSelesai ?? 0 }} / {{ $jumlahAnggotaKk ?? 0 }}
                </dd>
            </div>
        </dl>

        <div class="ketuakk-dashboard-overview__summary-total">
            <div class="ketuakk-dashboard-overview__summary-progress-label">
                <span>Progress Total KM KK ({{ $periodeLabel }})</span>
                <span class="ketuakk-dashboard-overview__summary-progress-value">
                    {{ $persentaseRealisasi ?? 0 }}%
                </span>
            </div>

            <div class="ketuakk-dashboard-overview__summary-progress">
                <div
                    class="ketuakk-dashboard-overview__summary-progress-fill"
                    style="width: {{ $persentaseRealisasi ?? 0 }}%;">
                </div>
            </div>
        </div>
    </aside>
</section>

<section class="ketuakk-dashboard-overview ketuakk-dashboard-overview__comparison" aria-labelledby="subcategoryComparisonTitle">
    <header class="ketuakk-dashboard-overview__comparison-header">
        <div class="ketuakk-dashboard-overview__section-copy">
            <h2 class="ketuakk-dashboard-overview__section-title" id="subcategoryComparisonTitle">
                Target dan Realisasi per Subkategori
            </h2>
            <p class="ketuakk-dashboard-overview__section-description">
                Perbandingan target, realisasi, dan sisa capaian setiap subkategori KM.
            </p>
        </div>

        @if($filterDashboardAktif)
            <div class="ketuakk-dashboard-overview__active-filter">
                <i class="bi bi-funnel"></i>
                <span>{{ $filterDashboardLabel }}</span>
            </div>
        @endif
    </header>

    <div class="ketuakk-dashboard-overview__comparison-groups">
        <div class="ketuakk-dashboard-overview__comparison-columns" aria-hidden="true">
            <span>Subkategori</span>
            <span>Target</span>
            <span>Realisasi</span>
            <span>Selisih</span>
            <span>Capaian</span>
        </div>

        @forelse($kategoriDetailCharts ?? [] as $chart)
            @php
                $namaKategori = trim((string) ($chart['kategori'] ?? ''));
                $subkategoriLabels = collect($chart['labels'] ?? []);
            @endphp

            <section class="ketuakk-dashboard-overview__comparison-group">
                <h3 class="ketuakk-dashboard-overview__comparison-category">
                    {{ $namaKategori !== '' ? $namaKategori : 'Kategori tanpa nama' }}
                </h3>

                @forelse($subkategoriLabels as $subkategoriIndex => $subkategoriLabel)
                    @php
                        $namaSubkategori = trim((string) $subkategoriLabel);
                        $targetSubkategori = (float) data_get($chart['targets'] ?? [], $subkategoriIndex, 0);
                        $realisasiSubkategori = (float) data_get($chart['realisasi'] ?? [], $subkategoriIndex, 0);
                        $selisihSubkategori = max($targetSubkategori - $realisasiSubkategori, 0);
                        $targetTersedia = $targetSubkategori > 0;
                        $persentaseSebenarnya = $targetTersedia
                            ? ($realisasiSubkategori / $targetSubkategori) * 100
                            : null;
                        $lebarProgress = $targetTersedia
                            ? min(max($persentaseSebenarnya, 0), 100)
                            : 0;
                        $belumMulai = $targetTersedia && $realisasiSubkategori === 0.0;
                        $targetTercapai = $targetTersedia && $realisasiSubkategori >= $targetSubkategori;
                        $progressStateClass = !$targetTersedia
                            ? 'ketuakk-dashboard-overview__comparison-progress--no-target'
                            : ($belumMulai
                                ? 'ketuakk-dashboard-overview__comparison-progress--not-started'
                                : ($targetTercapai ? 'ketuakk-dashboard-overview__comparison-progress--complete' : ''));
                        $targetDisplay = rtrim(rtrim(number_format($targetSubkategori, 2, ',', '.'), '0'), ',');
                        $realisasiDisplay = rtrim(rtrim(number_format($realisasiSubkategori, 2, ',', '.'), '0'), ',');
                        $selisihDisplay = rtrim(rtrim(number_format($selisihSubkategori, 2, ',', '.'), '0'), ',');
                        $persentaseDisplay = $targetTersedia
                            ? rtrim(rtrim(number_format($persentaseSebenarnya, 1, ',', '.'), '0'), ',') . '%'
                            : null;
                    @endphp

                    <div class="ketuakk-dashboard-overview__comparison-row">
                        <div class="ketuakk-dashboard-overview__comparison-name">
                            {{ $namaSubkategori !== '' ? $namaSubkategori : 'Subkategori tanpa nama' }}
                        </div>

                        <div class="ketuakk-dashboard-overview__comparison-number" data-label="Target">
                            {{ $targetDisplay }}
                        </div>

                        <div class="ketuakk-dashboard-overview__comparison-number" data-label="Realisasi">
                            {{ $realisasiDisplay }}
                        </div>

                        <div class="ketuakk-dashboard-overview__comparison-number" data-label="Selisih">
                            {{ $selisihDisplay }}
                        </div>

                        <div class="ketuakk-dashboard-overview__comparison-progress {{ $progressStateClass }}">
                            <div class="ketuakk-dashboard-overview__comparison-progress-meta">
                                @if(!$targetTersedia)
                                    <span>Belum ada target</span>
                                    <span>Tidak tersedia</span>
                                @elseif($belumMulai)
                                    <span>Belum mulai</span>
                                    <span>{{ $persentaseDisplay }}</span>
                                @elseif($targetTercapai)
                                    <span>Target tercapai</span>
                                    <span>{{ $persentaseDisplay }}</span>
                                @else
                                    <span>Dalam proses</span>
                                    <span>{{ $persentaseDisplay }}</span>
                                @endif
                            </div>

                            <div
                                class="ketuakk-dashboard-overview__comparison-track"
                                role="progressbar"
                                aria-label="Capaian {{ $namaSubkategori !== '' ? $namaSubkategori : 'subkategori tanpa nama' }}"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                aria-valuenow="{{ round($lebarProgress, 1) }}"
                                aria-valuetext="{{ $targetTersedia ? $persentaseDisplay . ' dari target' : 'Belum ada target' }}">
                                <div
                                    class="ketuakk-dashboard-overview__comparison-fill"
                                    style="width: {{ $lebarProgress }}%;">
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="ketuakk-dashboard-overview__comparison-empty">
                        Belum ada data subkategori.
                    </div>
                @endforelse
            </section>
        @empty
            <div class="ketuakk-dashboard-overview__comparison-empty">
                Belum ada data target dan realisasi per subkategori.
            </div>
        @endforelse
    </div>
</section>

<section class="ketuakk-dashboard-overview ketuakk-dashboard-overview__monitoring" aria-labelledby="monitoringAnggotaTitle">
    <header class="ketuakk-dashboard-overview__monitoring-header">
        <div class="ketuakk-dashboard-overview__section-copy">
            <h2 class="ketuakk-dashboard-overview__section-title" id="monitoringAnggotaTitle">
                Monitoring Anggota KK
            </h2>
            <p class="ketuakk-dashboard-overview__section-description">
                Menampilkan 10 anggota pertama beserta target, realisasi, dan status capaian KM pada {{ $periodeLabel }}.
            </p>
        </div>

        <a
            href="/ketuakk/monitoring-anggota-kk?tahun={{ $tahun }}&periode=triwulan"
            class="ketuakk-dashboard-overview__monitoring-link">
            Lihat Selengkapnya
        </a>
    </header>

    <div class="table-responsive ketuakk-dashboard-overview__monitoring-scroll">
        <table class="table align-middle mb-0 ketuakk-dashboard-overview__monitoring-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Anggota</th>
                    <th>Lab Riset</th>
                    <th>NIDN</th>
                    <th>JAD</th>
                    <th class="ketuakk-dashboard-overview__monitoring-cell--number">Target</th>
                    <th class="ketuakk-dashboard-overview__monitoring-cell--number">Realisasi</th>
                    <th class="ketuakk-dashboard-overview__monitoring-cell--number">Sisa</th>
                    <th>Progress</th>
                    <th class="ketuakk-dashboard-overview__monitoring-cell--status">Status</th>
                    <th class="ketuakk-dashboard-overview__monitoring-cell--action">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($monitoringAnggotaRows as $index => $item)
                    @php
                        $progressAnggota = is_numeric($item['progress'] ?? null)
                            ? (float) $item['progress']
                            : 0;
                        $progressVisualAnggota = min(max($progressAnggota, 0), 100);
                        $progressDisplayAnggota = rtrim(
                            rtrim(number_format($progressAnggota, 1, ',', '.'), '0'),
                            ','
                        );
                        $statusAnggotaText = (string) ($item['status'] ?? '');
                        $statusAnggotaAdalahError = \Illuminate\Support\Str::contains(
                            \Illuminate\Support\Str::lower($statusAnggotaText),
                            ['error', 'gagal', 'ditolak']
                        );
                        $statusAnggotaClass = match ($item['status_class'] ?? null) {
                            'success' => 'ketuakk-dashboard-overview__monitoring-status--complete',
                            'warning' => 'ketuakk-dashboard-overview__monitoring-status--pending',
                            'danger' => $statusAnggotaAdalahError
                                ? 'ketuakk-dashboard-overview__monitoring-status--error'
                                : 'ketuakk-dashboard-overview__monitoring-status--pending',
                            default => 'ketuakk-dashboard-overview__monitoring-status--neutral',
                        };
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td class="ketuakk-dashboard-overview__monitoring-cell--identity">
                            <span class="ketuakk-dashboard-overview__monitoring-identity-primary">
                                {{ $item['nama_dosen'] }}
                            </span>
                        </td>

                        <td class="ketuakk-dashboard-overview__monitoring-cell--identity">
                            {{ $item['nama_lab'] }}
                        </td>

                        <td class="ketuakk-dashboard-overview__monitoring-nidn">
                            {{ $item['nidn'] }}
                        </td>

                        <td>
                            <span class="ketuakk-dashboard-overview__monitoring-jad">
                                {{ $item['jad'] }}
                            </span>
                        </td>

                        <td class="ketuakk-dashboard-overview__monitoring-cell--number">
                            {{ $item['target'] }}
                        </td>

                        <td class="ketuakk-dashboard-overview__monitoring-cell--number">
                            {{ $item['realisasi'] }}
                        </td>

                        <td class="ketuakk-dashboard-overview__monitoring-cell--number">
                            {{ $item['sisa'] }}
                        </td>

                        <td>
                            <div class="ketuakk-dashboard-overview__monitoring-progress {{ $progressAnggota >= 100 ? 'ketuakk-dashboard-overview__monitoring-progress--complete' : '' }}">
                                <div class="ketuakk-dashboard-overview__monitoring-progress-meta">
                                    {{ $progressDisplayAnggota }}%
                                </div>

                                <div
                                    class="ketuakk-dashboard-overview__monitoring-progress-track"
                                    role="progressbar"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                    aria-valuenow="{{ round($progressVisualAnggota, 1) }}"
                                    aria-valuetext="{{ $progressDisplayAnggota }}% capaian"
                                    aria-label="Progress {{ $item['nama_dosen'] }}: {{ $progressDisplayAnggota }}%">
                                    <div
                                        class="ketuakk-dashboard-overview__monitoring-progress-fill"
                                        style="width: {{ $progressVisualAnggota }}%;">
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="ketuakk-dashboard-overview__monitoring-cell--status">
                            <span class="ketuakk-dashboard-overview__monitoring-status {{ $statusAnggotaClass }}">
                                {{ $item['status'] }}
                            </span>
                        </td>

                        <td class="ketuakk-dashboard-overview__monitoring-cell--action">
                            <a
                                href="/ketuakk/monitoring-anggota-kk/{{ $item['id_user'] }}?tahun={{ $tahun }}"
                                class="ketuakk-dashboard-overview__monitoring-link">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="ketuakk-dashboard-overview__monitoring-empty">
                            Belum ada data anggota KK.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

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
        const blue = '#477EF7';

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

    });
</script>
@endsection
