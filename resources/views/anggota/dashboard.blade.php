@extends('layouts.app')

@section('title', 'Dashboard Anggota')

@section('content')
@php
    $kategoriCards = collect($kategoriCards ?? []);

    $chartKategori = $chartKategori ?? [
        'labels' => [],
        'target' => [],
        'realisasi' => [],
        'sisa' => [],
    ];

    $targetKmSaya = collect($targetKmSaya ?? []);
    $targetKmTahunan = collect($targetKmTahunan ?? $targetKmSaya ?? []);
    $historyAktivitas = collect($historyAktivitas ?? []);
    $detailKategoriCharts = collect($detailKategoriCharts ?? []);
    $statusPengajuanKm = collect($statusPengajuanKm ?? []);

    $periode = $periode ?? 'tahun';
    $tahun = (int) ($tahun ?? now()->year);
    $triwulan = (int) ($triwulan ?? 1);
    $semester = (int) ($semester ?? 1);
    $tahunOptions = collect($tahunOptions ?? [$tahun]);
    $labelPeriode = $labelPeriode ?? ('Tahunan ' . $tahun);
    $keteranganPeriode = $keteranganPeriode ?? 'Data target dan realisasi ditampilkan untuk satu tahun penuh.';
    $filterQuery = http_build_query([
        'periode' => $periode,
        'tahun' => $tahun,
        'triwulan' => $triwulan,
        'semester' => $semester,
    ]);
@endphp

<style>
    .dashboard-header {
        padding: 18px 22px;
    }

    /*
    |--------------------------------------------------------------------------
    | Filter periode — disamakan dengan Dashboard Ketua Lab
    |--------------------------------------------------------------------------
    */
    .dashboard-filter-form {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .dashboard-filter-control {
        min-width: 126px;
        height: 38px;
        padding: 0 11px;
        border: 1px solid #CBD5E1;
        border-radius: 10px;
        background: #FFFFFF;
        color: #1E293B;
        font-size: 14px;
        font-weight: 700;
        outline: none;
    }

    .dashboard-filter-control.small-control {
        min-width: 104px;
    }

    .dashboard-filter-control:focus {
        border-color: #477EF7;
        box-shadow: 0 0 0 3px rgba(71, 126, 247, 0.14);
    }

    .dashboard-period-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 9px;
        padding: 5px 9px;
        border: 1px solid #DBEAFE;
        border-radius: 999px;
        background: #EFF6FF;
        color: #2563EB;
        font-size: 11px;
        font-weight: 800;
    }

    .dashboard-mini-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .dashboard-mini-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 18px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }

    .dashboard-mini-label {
        font-size: 14px;
        color: var(--text-muted);
        margin-bottom: 6px;
    }

    .dashboard-mini-value {
        font-size: 34px;
        font-weight: 800;
        line-height: 1;
        color: #0F172A;
    }

    .dashboard-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .dashboard-stat-card {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 14px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .dashboard-stat-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 4px;
        width: 100%;
        background: linear-gradient(90deg, #4F7DF3 0%, #6B9CFF 100%);
    }

    .dashboard-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.07);
    }

    .dashboard-stat-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 8px;
    }

    .dashboard-stat-title {
        font-size: 20px;
        font-weight: 800;
        line-height: 1.2;
        color: #1E293B;
        margin-bottom: 2px;
    }

    .dashboard-stat-subtitle {
        font-size: 11px;
        font-weight: 600;
        color: #94A3B8;
        line-height: 1.35;
    }

    .dashboard-stat-percent {
        min-width: 58px;
        text-align: center;
        padding: 6px 10px;
        background: #EAF1FF;
        border-radius: 12px;
        color: #2563EB;
        font-size: 16px;
        font-weight: 800;
        line-height: 1;
    }

    .dashboard-category-progress {
        height: 8px;
        border-radius: 999px;
        background: #EEF1F6;
        overflow: hidden;
        margin: 10px 0 12px;
    }

    .dashboard-category-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #4F7DF3 0%, #6B9CFF 100%);
    }

    .dashboard-km-info {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
        margin-bottom: 12px;
    }

    .dashboard-km-item {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 11px;
        padding: 9px 10px;
    }

    .dashboard-km-item-label {
        font-size: 11px;
        font-weight: 800;
        color: #64748B;
        margin-bottom: 3px;
        text-transform: uppercase;
        letter-spacing: 0.2px;
    }

    .dashboard-km-item-value {
        font-size: 16px;
        font-weight: 800;
        line-height: 1.15;
    }

    .text-target {
        color: #1D4ED8;
    }

    .text-realisasi {
        color: #059669;
    }

    .text-sisa {
        color: #D97706;
    }

    .text-progress {
        color: #7C3AED;
    }

    .dashboard-category-note {
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 10px;
        min-height: 18px;
    }

    .dashboard-category-note.success {
        color: #15803D;
    }

    .dashboard-category-note.danger {
        color: #DC2626;
    }

    .dashboard-category-note.secondary {
        color: #64748B;
    }

    .dashboard-category-button {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        padding: 9px 12px;
        border-radius: 10px;
        background: var(--blue);
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
    }

    .dashboard-category-button:hover {
        color: #fff;
        opacity: 0.92;
    }

    .dashboard-grid-main {
        display: grid;
        grid-template-columns: 1.55fr 1fr;
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

    .dashboard-category-chart-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 18px;
    }

    .dashboard-category-chart-grid .card:last-child:nth-child(odd) {
        grid-column: span 2;
    }

    .dashboard-chart-box-medium {
        position: relative;
        width: 100%;
        height: 260px;
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

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }

    .summary-item {
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 12px 14px;
        background: #fff;
    }

    .summary-item-label {
        font-size: 13px;
        color: #64748B;
        margin-bottom: 5px;
    }

    .summary-item-value {
        font-size: 18px;
        font-weight: 800;
        color: #0F172A;
    }

    .target-table th,
    .target-table td,
    .history-table th,
    .history-table td {
        white-space: nowrap;
        vertical-align: middle;
        font-size: 13px;
    }

    .target-table {
        width: 100%;
    }

    .history-table td.keterangan-km {
        min-width: 220px;
        white-space: normal;
    }

    .target-deadline-list {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 8px;
    }

    .target-realisasi-number {
        text-align: center;
        font-size: 15px;
        font-weight: 800;
        color: #059669;
    }

    .target-table td.detail-target {
        white-space: normal;
        min-width: 250px;
    }

    .target-group-header {
        background: #F3F6FB !important;
        text-align: center;
        font-weight: 800 !important;
    }

    .target-tw-start {
        border-left: 2px solid #CBD5E1 !important;
    }

    .target-tw-end {
        border-right: 2px solid #CBD5E1 !important;
    }

    .target-detail-category {
        font-size: 14px;
        font-weight: 800;
        color: #0F172A;
        margin-bottom: 7px;
    }

    .target-detail-row {
        display: flex;
        gap: 7px;
        margin-top: 4px;
        color: #64748B;
        font-size: 12px;
        line-height: 1.4;
    }

    .target-detail-row strong {
        min-width: 88px;
        color: #475569;
    }

    .target-period-number {
        text-align: center;
        font-size: 15px;
        font-weight: 800;
        color: #1D4ED8;
    }

    .target-period-empty {
        text-align: center;
        color: #94A3B8;
        font-weight: 700;
    }

    .target-deadline {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 6px 8px;
        border-radius: 8px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
    }

    .target-deadline i {
        color: #477EF7;
    }

    .target-summary-number {
        font-size: 16px;
        font-weight: 800;
        text-align: center;
    }

    .target-summary-primary {
        color: #1D4ED8;
    }

    .target-summary-success {
        color: #15803D;
    }

    .target-summary-warning {
        color: #D97706;
    }

    .target-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 11px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .target-status-success {
        background: #DCFCE7;
        color: #15803D;
    }

    .target-status-warning {
        background: #FEF3C7;
        color: #B45309;
    }

    .target-status-danger {
        background: #FEE2E2;
        color: #B91C1C;
    }

    .target-status-secondary {
        background: #E2E8F0;
        color: #64748B;
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

    .status-primary {
        background: #DBEAFE;
        color: #1D4ED8;
    }

    .status-secondary {
        background: #E5E7EB;
        color: #475569;
    }

    .empty-state {
        padding: 28px;
        text-align: center;
        color: #64748B;
    }


    /*
    |--------------------------------------------------------------------------
    | Status hasil verifikasi KM untuk anggota
    |--------------------------------------------------------------------------
    */
    .member-verification-alert {
        position: relative;
        margin-bottom: 18px;
        padding: 17px;
        border: 1px solid #BFDBFE;
        border-left: 5px solid #4F7DF3;
        border-radius: 16px;
        background: linear-gradient(135deg, #EFF6FF 0%, #FFFFFF 72%);
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.08);
    }

    .member-verification-title {
        color: #1D4ED8;
        font-size: 18px;
        font-weight: 900;
    }

    .member-verification-subtitle {
        margin: 4px 0 0;
        padding-right: 42px;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.45;
    }

    .member-verification-count {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        background: #DBEAFE;
        color: #1D4ED8;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .member-verification-close {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #BFDBFE;
        border-radius: 9px;
        background: #FFFFFF;
        color: #2563EB;
        font-size: 16px;
        cursor: pointer;
    }

    .member-verification-close:hover {
        background: #DBEAFE;
    }

    .member-verification-list {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 15px;
    }

    .member-verification-item {
        display: flex;
        flex-direction: column;
        min-height: 168px;
        padding: 13px;
        border: 1px solid #DBEAFE;
        border-radius: 13px;
        background: rgba(255, 255, 255, 0.94);
    }

    .member-verification-item.rejected {
        border-color: #FECACA;
        background: #FFFDFD;
    }

    .member-verification-item-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
    }

    .member-verification-category {
        color: #64748B;
        font-size: 11px;
        font-weight: 800;
    }

    .member-verification-activity {
        margin-top: 8px;
        color: #0F172A;
        font-size: 13px;
        font-weight: 900;
        line-height: 1.35;
    }

    .member-verification-note {
        margin-top: 7px;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.4;
    }

    .member-verification-time {
        margin-top: auto;
        padding-top: 10px;
        color: #64748B;
        font-size: 10px;
        font-weight: 800;
    }

    .member-verification-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-top: 9px;
    }

    .member-verification-detail-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 7px 9px;
        border-radius: 9px;
        background: #4F7DF3;
        color: #FFFFFF !important;
        font-size: 11px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }

    .member-verification-detail-btn:hover {
        background: #3E6DE8;
        color: #FFFFFF !important;
    }

    .member-decision-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .member-decision-pill.accepted { color: #15803D; background: #DCFCE7; }
    .member-decision-pill.rejected { color: #B91C1C; background: #FEE2E2; }

    @media (max-width: 1200px) {
        .dashboard-header-content {
            grid-template-columns: 1fr;
        }

        .dashboard-header-actions {
            justify-content: flex-start;
        }

        .dashboard-mini-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-grid-main {
            grid-template-columns: 1fr;
        }

        .member-verification-list {
            grid-template-columns: repeat(2, minmax(0, 1fr));
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
        .dashboard-header-actions,
        .dashboard-filter-form {
            flex-wrap: wrap;
        }

        .dashboard-filter-form .filter-periode,
        .dashboard-filter-form .filter-tahun,
        .dashboard-filter-form .filter-detail {
            width: 100%;
        }

        .dashboard-header-actions > .btn {
            width: 100%;
        }

        .dashboard-mini-summary,
        .dashboard-stat-grid,
        .summary-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-km-info {
            grid-template-columns: 1fr;
        }

        .member-verification-list {
            grid-template-columns: 1fr;
        }
    }
    .anggota-dashboard__overview { padding: 20px 22px; background: #FFF; border: 1px solid #E2E8F0; border-radius: 14px; }
    .anggota-dashboard__eyebrow { margin: 0 0 5px; color: #2563EB; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .anggota-dashboard__title { margin: 0 0 7px; color: #0F172A; font-size: 24px; font-weight: 700; }
    .anggota-dashboard__toolbar { padding-top: 16px; margin-top: 16px; border-top: 1px solid #EEF2F7; }
    .anggota-dashboard__target-records{border-top:1px solid #D5DCE5}.anggota-dashboard__target-record{padding:14px 20px 18px;border-bottom:1px solid #E5EAF0}.anggota-dashboard__target-record header{display:flex;justify-content:space-between;gap:16px}.anggota-dashboard__target-record h3{margin:2px 0;color:#1F2937;font-size:16px}.anggota-dashboard__target-record header p{margin:0;color:#5B6472;font-size:14px}.anggota-dashboard__target-record dl{display:flex;gap:24px;margin:12px 0}.anggota-dashboard__target-record dt{color:#5B6472;font-size:13px}.anggota-dashboard__target-record dd{margin:2px 0 0;color:#1F2937;font-size:14px;font-weight:700;font-variant-numeric:tabular-nums}.anggota-dashboard__period-table{width:100%;border-collapse:collapse;color:#374151;font-size:14px}.anggota-dashboard__period-table th,.anggota-dashboard__period-table td{padding:9px 12px;border-bottom:1px solid #E5EAF0}.anggota-dashboard__period-table thead th{background:#EEF2F6;font-size:13px;text-align:left}.anggota-dashboard__period-table td:nth-child(2),.anggota-dashboard__period-table td:nth-child(3){text-align:right;font-weight:700;font-variant-numeric:tabular-nums}@media(max-width:640px){.anggota-dashboard__target-record header{flex-direction:column}.anggota-dashboard__target-record dl{flex-wrap:wrap}}
</style>

<section class="dashboard-header anggota-dashboard__overview mb-3" aria-labelledby="anggota-dashboard-title">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <p class="anggota-dashboard__eyebrow">Dashboard Anggota</p>
            <h1 id="anggota-dashboard-title" class="anggota-dashboard__title">{{ $namaAnggota ?? 'Anggota' }}</h1>
            <div class="dashboard-panel-subtitle mb-0">
                Lab: {{ $namaLab ?? '-' }}
                |
                NIDN: {{ $nidnAnggota ?? '-' }}
                |
                JAD: {{ $jadAnggota ?? '-' }}
            </div>

            <span class="dashboard-period-badge">
                <i class="bi bi-calendar3"></i>
                {{ $keteranganPeriode }}
            </span>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap anggota-dashboard__toolbar">
            <form method="GET" action="{{ route('anggota.dashboard') }}" class="dashboard-filter-form">
                <select name="periode" id="anggotaPeriodeFilter" class="dashboard-filter-control">
                    <option value="tahun" {{ $periode === 'tahun' ? 'selected' : '' }}>Tahunan</option>
                    <option value="triwulan" {{ $periode === 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                    <option value="semester" {{ $periode === 'semester' ? 'selected' : '' }}>Semester</option>
                </select>

                <select name="tahun" class="dashboard-filter-control small-control">
                    @foreach($tahunOptions as $itemTahun)
                        <option value="{{ $itemTahun }}" {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                            {{ $itemTahun }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="triwulan"
                    id="anggotaTriwulanSelect"
                    class="dashboard-filter-control small-control {{ $periode === 'triwulan' ? '' : 'd-none' }}">
                    @for($tw = 1; $tw <= 4; $tw++)
                        <option value="{{ $tw }}" {{ $triwulan === $tw ? 'selected' : '' }}>
                            Triwulan {{ $tw }}
                        </option>
                    @endfor
                </select>

                <select
                    name="semester"
                    id="anggotaSemesterSelect"
                    class="dashboard-filter-control small-control {{ $periode === 'semester' ? '' : 'd-none' }}">
                    <option value="1" {{ $semester === 1 ? 'selected' : '' }}>Semester 1</option>
                    <option value="2" {{ $semester === 2 ? 'selected' : '' }}>Semester 2</option>
                </select>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel-fill me-1"></i>
                    Terapkan
                </button>
            </form>

            <a href="/anggota/aktivitas-km" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Input Aktivitas KM
            </a>
        </div>
    </div>
</section>

@if($statusPengajuanKm->isNotEmpty())
    <div id="statusPengajuanKmPanel" class="member-verification-alert">
        <button
            type="button"
            class="member-verification-close"
            aria-label="Tutup status pengajuan KM"
            title="Tutup"
            data-close-member-verification>
            <i class="bi bi-x-lg"></i>
        </button>

        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="member-verification-title">
                    <i class="bi bi-shield-check me-1"></i>
                    Status Pengajuan KM
                </div>
                <p class="member-verification-subtitle">
                    Berikut hasil verifikasi terbaru dari Ketua Lab. Panel ini hanya muncul satu kali setelah hasil verifikasi diterima; riwayatnya tetap dapat dilihat melalui notifikasi dan detail aktivitas.
                </p>
            </div>

            <span class="member-verification-count">
                <i class="bi bi-bell-fill"></i>
                {{ $statusPengajuanKm->count() }} pembaruan
            </span>
        </div>

        <div class="member-verification-list">
            @foreach($statusPengajuanKm as $pengajuan)
                @php
                    $disetujui = ($pengajuan->status_progress ?? '') === 'Accepted';
                    $waktuVerifikasi = $pengajuan->diverifikasi_pada ?? null;
                @endphp

                <div class="member-verification-item {{ $disetujui ? 'accepted' : 'rejected' }}">
                    <div class="member-verification-item-top">
                        <div class="member-verification-category">
                            {{ $pengajuan->kategori_km ?? '-' }}{{ !empty($pengajuan->sub_kategori_km) ? ' · ' . $pengajuan->sub_kategori_km : '' }}
                        </div>

                        <span class="member-decision-pill {{ $disetujui ? 'accepted' : 'rejected' }}">
                            <i class="bi {{ $disetujui ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                            {{ $disetujui ? 'Disetujui' : 'Ditolak' }}
                        </span>
                    </div>

                    <div class="member-verification-activity">
                        {{ \Illuminate\Support\Str::limit($pengajuan->judul_aktivitas ?? '-', 82) }}
                    </div>

                    <div class="member-verification-note">
                        @if($disetujui)
                            Aktivitas ini telah dihitung sebagai realisasi KM.
                        @else
                            <strong>Catatan Ketua Lab:</strong>
                            {{ \Illuminate\Support\Str::limit($pengajuan->catatan_verifikasi ?: 'Silakan buka detail untuk melihat arahan perbaikan.', 110) }}
                        @endif
                    </div>

                    <div class="member-verification-footer">
                        <span class="member-verification-time">
                            <i class="bi bi-clock-history me-1"></i>
                            {{ !empty($waktuVerifikasi) ? \Carbon\Carbon::parse($waktuVerifikasi)->format('d/m/Y H:i') : 'Baru diperbarui' }}
                        </span>

                        <a href="{{ $pengajuan->detail_url }}" class="member-verification-detail-btn">
                            Detail
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- Ringkasan utama anggota --}}
<div class="dashboard-mini-summary">
    <div class="dashboard-mini-card">
        <div class="dashboard-mini-label">Target Periode</div>
        <div class="dashboard-mini-value">{{ $totalTarget ?? 0 }}</div>
    </div>

    <div class="dashboard-mini-card">
        <div class="dashboard-mini-label">Realisasi</div>
        <div class="dashboard-mini-value" style="color:#059669;">{{ $totalRealisasi ?? 0 }}</div>
    </div>

    <div class="dashboard-mini-card">
        <div class="dashboard-mini-label">Sisa KM</div>
        <div class="dashboard-mini-value" style="color:#D97706;">{{ $totalSisa ?? 0 }}</div>
    </div>

    <div class="dashboard-mini-card">
        <div class="dashboard-mini-label">Progress Total</div>
        <div class="dashboard-mini-value" style="color:#2563EB;">{{ $persentaseTotal ?? 0 }}%</div>
    </div>
</div>

{{-- Card kategori seperti dashboard Ketua KK --}}
@include('partials.periode-saat-ini')
@include('partials.filter-diterapkan')

<div class="dashboard-stat-grid">
    @foreach($kategoriCards as $item)
        <div class="dashboard-stat-card">
            <div class="dashboard-stat-top">
                <div>
                    <div class="dashboard-stat-title">
                        {{ $item['kategori'] }}
                    </div>
                    <div class="dashboard-stat-subtitle">
                        Progress realisasi kategori KM • {{ $labelPeriode }}
                    </div>
                </div>

                <div class="dashboard-stat-percent">
                    {{ $item['persentase'] }}%
                </div>
            </div>

            <div class="dashboard-category-progress">
                <div
                    class="dashboard-category-progress-fill"
                    style="width: {{ $item['persentase'] }}%;"
                ></div>
            </div>

            <div class="dashboard-km-info">
                <div class="dashboard-km-item">
                    <div class="dashboard-km-item-label">Target</div>
                    <div class="dashboard-km-item-value text-target">
                        {{ $item['target'] }}
                    </div>
                </div>

                <div class="dashboard-km-item">
                    <div class="dashboard-km-item-label">Realisasi</div>
                    <div class="dashboard-km-item-value text-realisasi">
                        {{ $item['realisasi'] }}
                    </div>
                </div>

                <div class="dashboard-km-item">
                    <div class="dashboard-km-item-label">Sisa</div>
                    <div class="dashboard-km-item-value text-sisa">
                        {{ $item['sisa'] }}
                    </div>
                </div>

                <div class="dashboard-km-item">
                    <div class="dashboard-km-item-label">Sub Kategori</div>
                    <div class="dashboard-km-item-value text-progress">
                        {{ $item['jumlah_subkategori'] }}
                    </div>
                </div>
            </div>

            <div class="dashboard-category-note {{ $item['catatan_class'] }}">
                @if($item['catatan_class'] === 'success')
                    <i class="bi bi-check-circle-fill me-1"></i>
                @elseif($item['catatan_class'] === 'danger')
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                @else
                    <i class="bi bi-info-circle-fill me-1"></i>
                @endif
                {{ $item['catatan'] }}
            </div>

            <a href="{{ $item['detail_url'] }}" class="dashboard-category-button">
                Lihat Detail
            </a>
        </div>
    @endforeach
</div>

{{-- Diagram utama + ringkasan --}}
<div class="dashboard-grid-main">
    <div class="card">
        @include('partials.filter-diterapkan')
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
            <div>
                <div class="dashboard-panel-title">Diagram Pencapaian KM Saya</div>
                <div class="dashboard-panel-subtitle">
                    Perbandingan target, realisasi, dan sisa KM per kategori untuk {{ $labelPeriode }}.
                </div>
            </div>

            <a href="/anggota/progress-km?{{ $filterQuery }}" class="btn btn-primary btn-sm">
                Lihat Selengkapnya
            </a>
        </div>

        <div class="dashboard-chart-box">
            <canvas id="anggotaKategoriChart"></canvas>
        </div>
    </div>

    <div class="card">
        @include('partials.filter-diterapkan')
        <div class="dashboard-panel-title">Ringkasan</div>
        <div class="dashboard-panel-subtitle">
            Rekap target, realisasi, status proses, dan progres KM Anda untuk {{ $labelPeriode }}.
        </div>

        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-item-label">Jumlah Target Aktif</div>
                <div class="summary-item-value">{{ $jumlahTargetAktif ?? 0 }}</div>
            </div>

            <div class="summary-item">
                <div class="summary-item-label">Jumlah Kategori Aktif</div>
                <div class="summary-item-value">{{ $jumlahKategoriAktif ?? 0 }}</div>
            </div>

            <div class="summary-item">
                <div class="summary-item-label">Diajukan</div>
                <div class="summary-item-value" style="color:#2563EB;">
                    {{ $jumlahSubmitted ?? 0 }}
                </div>
            </div>

            <div class="summary-item">
                <div class="summary-item-label">Sedang Berjalan</div>
                <div class="summary-item-value" style="color:#D97706;">
                    {{ $jumlahOnProgress ?? 0 }}
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2 mt-2">
            <strong>Progress Total KM</strong>
            <strong>{{ $persentaseTotal ?? 0 }}%</strong>
        </div>

        <div class="progress-soft">
            <div
                class="progress-soft-fill"
                style="width: {{ $persentaseTotal ?? 0 }}%;"
            ></div>
        </div>
    </div>
</div>

{{-- Grafik detail per kategori -> sub kategori --}}
<div class="dashboard-category-chart-grid">
    @foreach($detailKategoriCharts as $index => $chart)
        <div class="card">
            @include('partials.filter-diterapkan')
            <div class="dashboard-panel-title">{{ $chart['kategori'] }}</div>
            <div class="dashboard-panel-subtitle">
                Target dan realisasi berdasarkan sub kategori KM untuk {{ $labelPeriode }}.
            </div>

            <div class="dashboard-chart-box-medium">
                <canvas id="detailChart{{ $index }}"></canvas>
            </div>
        </div>
    @endforeach
</div>

{{-- Daftar Target KM anggota --}}
<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
            <div class="dashboard-panel-title">Daftar Target KM {{ $namaAnggota ?? 'Anggota' }}</div>
            <div class="dashboard-panel-subtitle mb-0">
                Menampilkan target tahunan yang telah dibagikan oleh Ketua Lab, beserta target dan realisasi per triwulan.
            </div>

            <span class="dashboard-period-badge">
                <i class="bi bi-calendar3"></i>
                Menampilkan data tahun {{ $tahun }}
            </span>
        </div>

        <form method="GET" action="{{ route('anggota.dashboard') }}" class="dashboard-filter-form">
            <input type="hidden" name="periode" value="{{ $periode }}">
            <input type="hidden" name="triwulan" value="{{ $triwulan }}">
            <input type="hidden" name="semester" value="{{ $semester }}">

            <select name="tahun" class="dashboard-filter-control small-control" aria-label="Pilih tahun target">
                @foreach($tahunOptions as $itemTahun)
                    <option value="{{ $itemTahun }}" {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                        {{ $itemTahun }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-funnel-fill me-1"></i>
                Filter
            </button>
        </form>
    </div>

    <div class="anggota-dashboard__target-records">
                @forelse($targetKmTahunan as $index => $target)
                    @php
                        $targetTriwulan = [
                            1 => (int) ($target->target_tw_1 ?? 0),
                            2 => (int) ($target->target_tw_2 ?? 0),
                            3 => (int) ($target->target_tw_3 ?? 0),
                            4 => (int) ($target->target_tw_4 ?? 0),
                        ];

                        $realisasiTriwulan = [
                            1 => (int) ($target->realisasi_tw_1 ?? 0),
                            2 => (int) ($target->realisasi_tw_2 ?? 0),
                            3 => (int) ($target->realisasi_tw_3 ?? 0),
                            4 => (int) ($target->realisasi_tw_4 ?? 0),
                        ];

                        $tenggat = [
                            1 => $target->tanggal_selesai_tw1 ?? null,
                            2 => $target->tanggal_selesai_tw2 ?? null,
                            3 => $target->tanggal_selesai_tw3 ?? null,
                            4 => $target->tanggal_selesai_tw4 ?? null,
                        ];

                        $statusClass = match($target->status_tahunan_class ?? 'secondary') {
                            'success' => 'target-status-success',
                            'warning' => 'target-status-warning',
                            'danger' => 'target-status-danger',
                            default => 'target-status-secondary',
                        };
                    @endphp

                    <article class="anggota-dashboard__target-record"><header><div><span>{{ $index+1 }} · {{ $target->tahun_km??'-' }}</span><h3>{{ $target->kategori_km??'-' }}</h3><p>{{ $target->sub_kategori_km??'-' }} · {{ $target->keterangan??'-' }}</p></div><span class="target-status {{ $statusClass }}">{{ $target->status_tahunan??'Belum Ada Target' }}</span></header><dl><div><dt>Total target</dt><dd>{{ $target->jumlah_km_tahunan??$target->jumlah_km??0 }}</dd></div><div><dt>Total realisasi</dt><dd>{{ $target->total_realisasi_tahunan??0 }}</dd></div><div><dt>Sisa</dt><dd>{{ $target->sisa_km_tahunan??0 }}</dd></div></dl><table class="anggota-dashboard__period-table"><thead><tr><th scope="col">Periode</th><th scope="col">Target</th><th scope="col">Realisasi</th><th scope="col">Tenggat</th></tr></thead><tbody>@for($tw=1;$tw<=4;$tw++)<tr><th scope="row">TW {{ $tw }}</th><td>{{ $targetTriwulan[$tw]??0 }}</td><td>{{ $realisasiTriwulan[$tw]??0 }}</td><td>{{ !empty($tenggat[$tw])?\Carbon\Carbon::parse($tenggat[$tw])->format('d/m/Y'):'-' }}</td></tr>@endfor</tbody></table></article>
                @empty
                    <div class="empty-state">Belum ada target KM yang dibagikan kepada {{ $namaAnggota ?? 'anggota' }} pada tahun {{ $tahun }}.</div>
                @endforelse
    </div>
</div>

{{-- Riwayat penyelesaian KM --}}
<div class="card">
    <div class="dashboard-panel-title">Riwayat Penyelesaian KM</div>
    <div class="dashboard-panel-subtitle">
        Riwayat aktivitas KM yang diinput, disubmit, maupun diselesaikan pada {{ $labelPeriode }}.
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 history-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori</th>
                    <th>Sub Kategori</th>
                    <th>Keterangan KM</th>
                    <th>Judul Aktivitas</th>
                    <th>Status</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Selesai</th>
                    <th>Update Terakhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historyAktivitas as $index => $item)
                    @php
                        $status = $item->status_progress ?? '-';

                        $statusClass = match($status) {
                            'Accepted' => 'status-success',
                            'Submitted' => 'status-primary',
                            'Rejected' => 'status-danger',
                            'On Progress' => 'status-warning',
                            default => 'status-secondary',
                        };

                        $statusLabel = match($status) {
                            'Accepted' => 'Disetujui',
                            'Submitted' => 'Diajukan',
                            'Rejected' => 'Ditolak',
                            'On Progress' => 'Sedang Berjalan',
                            'Pending' => 'Menunggu Verifikasi',
                            default => $status,
                        };
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->kategori_km ?? '-' }}</td>
                        <td>{{ $item->sub_kategori_km ?? '-' }}</td>
                        <td class="keterangan-km">{{ $item->keterangan_km ?? '-' }}</td>
                        <td>{{ $item->judul_aktivitas ?? '-' }}</td>
                        <td>
                            <span class="status-pill {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td>
                            {{ !empty($item->tanggal_mulai) ? \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') : '-' }}
                        </td>
                        <td>
                            {{ !empty($item->tanggal_selesai) ? \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') : '-' }}
                        </td>
                        <td>
                            {{ !empty($item->updated_at) ? \Carbon\Carbon::parse($item->updated_at)->format('d/m/Y H:i') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                Belum ada riwayat penyelesaian KM.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const anggotaPeriodeFilter = document.getElementById('anggotaPeriodeFilter');
    const anggotaTriwulanSelect = document.getElementById('anggotaTriwulanSelect');
    const anggotaSemesterSelect = document.getElementById('anggotaSemesterSelect');

    function sinkronkanFilterAnggota() {
        if (!anggotaPeriodeFilter || !anggotaTriwulanSelect || !anggotaSemesterSelect) return;

        anggotaTriwulanSelect.classList.toggle(
            'd-none',
            anggotaPeriodeFilter.value !== 'triwulan'
        );

        anggotaSemesterSelect.classList.toggle(
            'd-none',
            anggotaPeriodeFilter.value !== 'semester'
        );
    }

    if (anggotaPeriodeFilter) {
        anggotaPeriodeFilter.addEventListener('change', sinkronkanFilterAnggota);
        sinkronkanFilterAnggota();
    }


    const kategoriChartData = @json($chartKategori);

    const detailKategoriCharts = @json($detailKategoriCharts);

    const kategoriCtx = document.getElementById('anggotaKategoriChart');
    if (kategoriCtx) {
        new Chart(kategoriCtx, {
            type: 'bar',
            data: {
                labels: kategoriChartData.labels,
                datasets: [
                    {
                        label: 'Target',
                        data: kategoriChartData.target,
                        backgroundColor: '#4F7DF3',
                        borderRadius: 8
                    },
                    {
                        label: 'Realisasi',
                        data: kategoriChartData.realisasi,
                        backgroundColor: '#22C55E',
                        borderRadius: 8
                    },
                    {
                        label: 'Sisa',
                        data: kategoriChartData.sisa,
                        backgroundColor: '#EF4444',
                        borderRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    detailKategoriCharts.forEach((item, index) => {
        const canvas = document.getElementById('detailChart' + index);
        if (!canvas) return;

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: item.labels.length ? item.labels : ['Belum ada data'],
                datasets: [
                    {
                        label: 'Target',
                        data: item.target.length ? item.target : [0],
                        backgroundColor: '#4F7DF3',
                        borderRadius: 8
                    },
                    {
                        label: 'Realisasi',
                        data: item.realisasi.length ? item.realisasi : [0],
                        backgroundColor: '#22C55E',
                        borderRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    });


        document.querySelectorAll('[data-close-member-verification]').forEach(function (button) {
            button.addEventListener('click', function () {
                const panel = document.getElementById('statusPengajuanKmPanel');
                if (panel) {
                    panel.remove();
                }
            });
        });

</script>
@endsection
