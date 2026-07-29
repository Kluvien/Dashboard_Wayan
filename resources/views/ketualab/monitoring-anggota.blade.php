@extends('layouts.app')

@section('title', 'Monitoring Anggota Lab')

@section('content')
@php
    $kategoriCards = collect($kategoriCards ?? []);
    $dataMonitoring = collect($dataMonitoring ?? []);
    $rincianPerKategori = collect($rincianPerKategori ?? []);
    $kategoriDetail = $kategoriDetail ?? null;
    $kategoriDitampilkan = $kategoriDitampilkan ?? null;

    $kategoriDefault = [
        'Penelitian',
        'Publikasi',
        'Pengabdian',
        'Penunjang',
    ];

    $kategoriDitampilkan = $kategoriDitampilkan ?: $kategoriDefault;

    $jumlahMenungguVerifikasi = (int) ($jumlahMenungguVerifikasi ?? 0);
    $pengajuanMenungguVerifikasi = collect($pengajuanMenungguVerifikasi ?? []);
    $riwayatVerifikasi = collect($riwayatVerifikasi ?? []);
@endphp

<style>
    .monitoring-anggota-page {
        padding-bottom: 26px;
    }

    .monitoring-filter-card,
    .monitoring-summary-card,
    .monitoring-table-card,
    .monitoring-category-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }

    .monitoring-filter-card {
        padding: 18px;
        margin-bottom: 16px;
    }

    .monitoring-header-line {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .monitoring-card-title {
        font-size: 19px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 4px;
    }

    .monitoring-card-subtitle {
        color: #64748b;
        font-size: 13px;
        margin: 0;
    }

    .monitoring-period-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        border: 1px solid #bfdbfe;
        border-radius: 999px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 12px;
        font-weight: 800;
        margin-top: 10px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: minmax(160px, 1.1fr) minmax(120px, .8fr) minmax(150px, 1fr) minmax(210px, 1.55fr) auto;
        gap: 10px;
        align-items: end;
    }

    .filter-group label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 12px;
        font-weight: 800;
    }

    .filter-group .form-select,
    .filter-group .form-control {
        height: 41px;
        border-radius: 10px;
        border-color: #cbd5e1;
        font-size: 14px;
    }

    .btn-monitoring-primary,
    .btn-monitoring-secondary {
        height: 41px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 10px;
        border: 0;
        padding: 0 15px;
        text-decoration: none;
        font-weight: 800;
        font-size: 14px;
        white-space: nowrap;
    }

    .btn-monitoring-primary {
        background: #4f7df3;
        color: #fff;
    }

    .btn-monitoring-primary:hover {
        color: #fff;
        background: #3f6ee8;
    }

    .btn-monitoring-secondary {
        background: #6b7280;
        color: #fff;
    }

    .btn-monitoring-secondary:hover {
        color: #fff;
        background: #4b5563;
    }

    /*
    |--------------------------------------------------------------------------
    | 4 kategori aktif: Penelitian, Publikasi, Pengabdian, dan Penunjang.
    | Empat kolom membuat card kategori memenuhi satu baris penuh dan tidak
    | meninggalkan ruang kosong di sisi kanan.
    |--------------------------------------------------------------------------
    */
    .category-card-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }

    .category-stat-card {
        min-height: 258px;
        padding: 14px;
        border: 1px solid #dbe5f7;
        border-top: 4px solid #5a88ff;
        border-radius: 17px;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        box-shadow: 0 5px 14px rgba(59, 111, 230, .08);
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

    .category-stat-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
    }

    .category-name {
        color: #334155;
        font-size: 15px;
        font-weight: 900;
        line-height: 1.2;
    }

    .category-caption {
        display: block;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 700;
        margin-top: 3px;
    }

    .category-percent {
        padding: 5px 9px;
        border-radius: 12px;
        color: #2563eb;
        background: #eaf1ff;
        font-size: 15px;
        font-weight: 900;
        white-space: nowrap;
    }

    .category-progress {
        width: 100%;
        height: 8px;
        overflow: hidden;
        border-radius: 999px;
        background: #e7edf7;
    }

    .category-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #4f7df3, #79a0ff);
    }

    .category-metric-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .category-metric {
        min-height: 58px;
        padding: 8px 10px;
        border: 1px solid;
        border-radius: 11px;
    }

    .metric-label {
        color: inherit;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .18px;
        text-transform: uppercase;
    }

    .metric-value {
        margin-top: 4px;
        color: inherit;
        font-size: 19px;
        font-weight: 900;
        line-height: 1;
    }

    .metric-target {
        color: #2563eb;
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .metric-realisasi {
        color: #059669;
        background: #ecfdf5;
        border-color: #bbf7d0;
    }

    .metric-dibagi {
        color: #7c3aed;
        background: #f5f3ff;
        border-color: #ddd6fe;
    }

    .metric-belum {
        color: #dc2626;
        background: #fff1f2;
        border-color: #fecdd3;
    }

    .metric-belum-done {
        color: #15803d;
        background: #ecfdf5;
        border-color: #bbf7d0;
    }

    .category-note {
        min-height: 30px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 800;
        line-height: 1.3;
    }

    .note-success { color: #15803d; }
    .note-warning { color: #b45309; }
    .note-danger { color: #dc2626; }

    .summary-grid {
        display: grid;
        grid-template-columns: 1.4fr repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }

    .monitoring-summary-card {
        padding: 15px;
    }

    .summary-label {
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .summary-value {
        color: #0f172a;
        font-size: 24px;
        font-weight: 900;
        line-height: 1.1;
    }

    .summary-value.success { color: #059669; }
    .summary-value.warning { color: #d97706; }
    .summary-value.primary { color: #2563eb; }

    .summary-progress-box {
        grid-column: span 4;
    }

    .summary-progress-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        color: #1f2937;
        font-size: 13px;
        font-weight: 900;
    }

    .summary-progress-bar {
        width: 100%;
        height: 10px;
        overflow: hidden;
        border-radius: 999px;
        background: #e5e7eb;
    }

    .summary-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #4f7df3, #79a0ff);
    }

    .summary-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .summary-chip {
        padding: 6px 10px;
        border: 1px solid;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
    }

    .chip-success {
        color: #15803d;
        background: #ecfdf5;
        border-color: #bbf7d0;
    }

    .chip-warning {
        color: #b45309;
        background: #fff7ed;
        border-color: #fed7aa;
    }

    .chip-danger {
        color: #dc2626;
        background: #fff1f2;
        border-color: #fecdd3;
    }

    .monitoring-table-card,
    .monitoring-category-card {
        padding: 17px;
        margin-bottom: 16px;
    }

    .section-heading {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: flex-start;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .section-title {
        color: #111827;
        font-size: 18px;
        font-weight: 900;
        margin-bottom: 3px;
    }

    .section-desc {
        color: #64748b;
        font-size: 13px;
        margin: 0;
    }

    .table-wrap {
        overflow-x: auto;
    }

    .monitoring-table {
        width: 100%;
        width: 100%;
        border-collapse: collapse;
    }

    .monitoring-table thead th {
        padding: 11px 10px;
        border-bottom: 1px solid #e2e8f0;
        color: #334155;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .monitoring-table tbody td {
        padding: 12px 10px;
        border-bottom: 1px solid #f1f5f9;
        color: #1f2937;
        font-size: 13px;
        vertical-align: middle;
    }

    .member-name {
        color: #0f172a;
        font-weight: 900;
    }

    .member-meta {
        display: block;
        margin-top: 2px;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
    }

    .soft-progress {
        width: 125px;
        height: 8px;
        overflow: hidden;
        border-radius: 999px;
        background: #e5e7eb;
        margin-bottom: 4px;
    }

    .soft-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #4f7df3, #79a0ff);
    }

    .status-pill,
    .deadline-pill,
    .jad-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 72px;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .status-success {
        color: #15803d;
        background: #dcfce7;
    }

    .status-warning {
        color: #b45309;
        background: #fef3c7;
    }

    .status-danger {
        color: #dc2626;
        background: #fee2e2;
    }

    .status-secondary {
        color: #64748b;
        background: #e2e8f0;
    }

    .jad-pill {
        min-width: 34px;
        color: #ffffff;
        background: #2563eb;
    }

    /*
    |--------------------------------------------------------------------------
    | Rekap target anggota per kategori
    |--------------------------------------------------------------------------
    | Setiap kategori dibuat full width agar tabel tidak lagi terpotong menjadi
    | card kiri-kanan. Tabel tetap bisa digeser horizontal pada layar sempit.
    */
    .category-detail-grid {
        display: block;
    }

    .category-detail-grid .monitoring-category-card {
        width: 100%;
        margin-bottom: 16px;
    }

    .category-detail-grid .monitoring-category-card:last-child {
        margin-bottom: 0;
    }

    .category-card-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .category-card-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
    }

    .category-count-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 10px;
        border: 1px solid #dbeafe;
        border-radius: 999px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 11px;
        font-weight: 900;
    }

    .btn-category-detail {
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0 12px;
        border-radius: 10px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #2563eb;
        text-decoration: none;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .btn-category-detail:hover {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .category-pagination-wrap {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 15px;
        padding-top: 14px;
        border-top: 1px solid #edf2f7;
    }

    .category-pagination-info {
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
    }

    .category-pagination {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }

    .category-page-link,
    .category-page-ellipsis {
        min-width: 33px;
        height: 33px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 9px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 900;
    }

    .category-page-link {
        border: 1px solid #dbe5f7;
        background: #ffffff;
        color: #475569;
        text-decoration: none;
    }

    .category-page-link:hover {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #2563eb;
    }

    .category-page-link.active {
        background: #4f7df3;
        border-color: #4f7df3;
        color: #ffffff;
        cursor: default;
    }

    .category-page-link.disabled {
        pointer-events: none;
        opacity: .45;
    }

    .category-page-ellipsis {
        color: #94a3b8;
    }

    .category-block-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 3px;
        color: #111827;
        font-size: 18px;
        font-weight: 900;
    }

    .category-block-heading::before {
        content: "";
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #4f7df3;
        box-shadow: 0 0 0 4px #eaf1ff;
    }

    .category-detail-table {
        width: 100%;
        width: 100%;
        border-collapse: collapse;
    }

    .category-detail-table thead th {
        padding: 10px 8px;
        color: #475569;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .category-detail-table tbody td {
        padding: 10px 8px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 12px;
        vertical-align: top;
    }

    .keterangan-cell {
        min-width: 165px;
        max-width: 220px;
        color: #64748b !important;
        line-height: 1.35;
    }

    .tenggat-list {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        min-width: 130px;
    }

    .tenggat-item {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 6px;
        border: 1px solid #dbeafe;
        border-radius: 7px;
        color: #2563eb;
        background: #eff6ff;
        font-size: 10px;
        font-weight: 800;
    }

    .empty-state {
        padding: 24px 12px;
        color: #94a3b8;
        font-size: 13px;
        font-weight: 700;
        text-align: center;
    }


    /*
    |--------------------------------------------------------------------------
    | Antrean dan riwayat verifikasi aktivitas KM
    |--------------------------------------------------------------------------
    */
    .approval-queue-card {
        margin-bottom: 16px;
        padding: 17px;
        border: 1px solid #FDE68A;
        border-left: 5px solid #F59E0B;
        border-radius: 18px;
        background: linear-gradient(135deg, #FFFBEB 0%, #FFFFFF 72%);
        box-shadow: 0 5px 14px rgba(180, 83, 9, 0.08);
    }

    .approval-queue-title {
        color: #92400E;
        font-size: 18px;
        font-weight: 900;
        margin-bottom: 3px;
    }

    .approval-queue-subtitle {
        color: #A16207;
        font-size: 13px;
        margin: 0;
    }

    .approval-queue-count {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 999px;
        background: #FEF3C7;
        color: #92400E;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .approval-queue-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 14px;
    }

    .approval-queue-item {
        display: flex;
        flex-direction: column;
        min-height: 146px;
        padding: 13px;
        border: 1px solid #FDE68A;
        border-radius: 13px;
        background: rgba(255, 255, 255, .94);
    }

    .approval-member {
        color: #0F172A;
        font-size: 13px;
        font-weight: 900;
    }

    .approval-meta {
        margin-top: 3px;
        color: #78716C;
        font-size: 11px;
        font-weight: 700;
    }

    .approval-title {
        margin-top: 10px;
        color: #334155;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.35;
    }

    .approval-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-top: auto;
        padding-top: 12px;
    }

    .approval-time {
        color: #A16207;
        font-size: 10px;
        font-weight: 800;
    }

    .btn-approval-detail {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 7px 9px;
        border: 0;
        border-radius: 9px;
        background: #F59E0B;
        color: #FFFFFF;
        text-decoration: none;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .btn-approval-detail:hover {
        background: #D97706;
        color: #FFFFFF;
    }

    .verification-history-card {
        margin-bottom: 16px;
    }

    .verification-history-table {
        width: 100%;
        width: 100%;
        border-collapse: collapse;
    }

    .verification-history-table thead th {
        padding: 10px 8px;
        color: #475569;
        border-bottom: 1px solid #E2E8F0;
        background: #F8FAFC;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .verification-history-table tbody td {
        padding: 10px 8px;
        color: #334155;
        border-bottom: 1px solid #F1F5F9;
        font-size: 12px;
        vertical-align: middle;
    }

    .verification-history-note {
        min-width: 180px;
        max-width: 280px;
        color: #64748B !important;
        line-height: 1.4;
    }

    .verification-decision {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .verification-decision.accepted { color: #15803D; background: #DCFCE7; }
    .verification-decision.rejected { color: #B91C1C; background: #FEE2E2; }

    @media (max-width: 1240px) {
        .category-card-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .approval-queue-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 1050px) {
        .filter-grid,
        .summary-grid,
        .category-detail-grid {
            grid-template-columns: 1fr 1fr;
        }

        .summary-progress-box {
            grid-column: span 2;
        }
    }

    @media (max-width: 720px) {
        .category-card-grid,
        .filter-grid,
        .summary-grid,
        .category-detail-grid,
        .approval-queue-grid {
            grid-template-columns: 1fr;
        }

        .summary-progress-box,
        .category-detail-grid .monitoring-category-card:last-child:nth-child(odd) {
            grid-column: span 1;
        }
    }
    .ketualab-monitoring-anggota__records{border-top:1px solid #D5DCE5}
    .ketualab-monitoring-anggota__record{display:grid;grid-template-columns:minmax(240px,1.2fr) minmax(420px,2fr);gap:14px 24px;padding:16px 20px;border-bottom:1px solid #E5EAF0;background:#fff}
    .ketualab-monitoring-anggota__identity{display:flex;gap:12px;align-items:flex-start}
    .ketualab-monitoring-anggota__identity strong{display:block;color:#1F2937;font-size:15px}
    .ketualab-monitoring-anggota__identity p{margin:3px 0 0;color:#5B6472;font-size:13px;line-height:1.5}
    .ketualab-monitoring-anggota__record dl{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin:0}
    .ketualab-monitoring-anggota__record dt{color:#5B6472;font-size:13px}.ketualab-monitoring-anggota__record dd{margin:2px 0 0;color:#1F2937;font-size:14px;font-weight:700;font-variant-numeric:tabular-nums}
    .ketualab-monitoring-anggota__progress{grid-column:1/2;height:6px;overflow:hidden;border-radius:999px;background:#E5EAF0}.ketualab-monitoring-anggota__progress span{display:block;height:100%;background:#2457A6}
    .ketualab-monitoring-anggota__actions{display:flex;justify-content:flex-end;align-items:center;gap:12px}.ketualab-monitoring-anggota__actions .btn-monitoring-primary{min-height:40px;font-size:14px}
    @media(max-width:900px){.ketualab-monitoring-anggota__record{grid-template-columns:1fr}.ketualab-monitoring-anggota__progress{grid-column:auto}.ketualab-monitoring-anggota__actions{justify-content:flex-start}}
    @media(max-width:640px){.ketualab-monitoring-anggota__record dl{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>

<div class="monitoring-anggota-page">
    <section class="monitoring-filter-card" aria-labelledby="ketualab-monitoring-member-title">
        <div class="monitoring-header-line">
            <div>
                <h1 id="ketualab-monitoring-member-title" class="monitoring-card-title">Monitoring Anggota Lab</h1>
                <p class="monitoring-card-subtitle">
                    Monitoring saat ini:
                    <strong>{{ $labelPeriode ?? 'Tahunan' }}</strong>
                    @if(isset($tanggalMulai, $tanggalSelesai))
                        | {{ $tanggalMulai->format('d/m/Y') }} - {{ $tanggalSelesai->format('d/m/Y') }}
                    @endif
                </p>
                <span class="monitoring-period-pill">
                    <i class="bi bi-calendar-range"></i>
                    {{ $labelMode ?? 'Data monitoring ditampilkan sesuai periode terpilih.' }}
                </span>
            </div>

            <a href="/ketualab/dashboard" class="btn-monitoring-secondary">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>
        </div>

        <form action="/ketualab/monitoring-anggota" method="GET" id="monitoringAnggotaFilterForm">
            <div class="filter-grid">
                <div class="filter-group">
                    <label for="periode">Jenis Periode</label>
                    <select name="periode" id="periode" class="form-select">
                        <option value="tahun" {{ ($periode ?? 'tahun') === 'tahun' ? 'selected' : '' }}>Tahunan</option>
                        <option value="triwulan" {{ ($periode ?? '') === 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                        <option value="semester" {{ ($periode ?? '') === 'semester' ? 'selected' : '' }}>Semester</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="tahun">Tahun</label>
                    <select name="tahun" id="tahun" class="form-select">
                        @foreach(($tahunOptions ?? [now()->year]) as $itemTahun)
                            <option value="{{ $itemTahun }}" {{ (int)($tahun ?? now()->year) === (int)$itemTahun ? 'selected' : '' }}>
                                {{ $itemTahun }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group" id="triwulanGroup">
                    <label for="triwulan">Triwulan</label>
                    <select name="triwulan" id="triwulan" class="form-select">
                        @for($tw = 1; $tw <= 4; $tw++)
                            <option value="{{ $tw }}" {{ (int)($triwulan ?? 1) === $tw ? 'selected' : '' }}>
                                Triwulan {{ $tw }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="filter-group" id="semesterGroup">
                    <label for="semester">Semester</label>
                    <select name="semester" id="semester" class="form-select">
                        <option value="1" {{ (int)($semester ?? 1) === 1 ? 'selected' : '' }}>Semester 1</option>
                        <option value="2" {{ (int)($semester ?? 1) === 2 ? 'selected' : '' }}>Semester 2</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn-monitoring-primary w-100">
                        <i class="bi bi-funnel-fill"></i>
                        Terapkan
                    </button>
                </div>

                <div class="filter-group" style="grid-column: 1 / span 4;">
                    <label for="search">Cari Anggota</label>
                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search ?? '' }}"
                        class="form-control"
                        placeholder="Cari nama dosen/anggota, NIDN, atau JAD..."
                        autocomplete="off">
                </div>
            </div>
        </form>
    </section>

    @if($jumlahMenungguVerifikasi > 0)
        <div class="approval-queue-card">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <div class="approval-queue-title">
                        <i class="bi bi-shield-exclamation me-1"></i>
                        Pengajuan KM Menunggu Verifikasi
                    </div>
                    <p class="approval-queue-subtitle">
                        Buka rincian aktivitas terlebih dahulu untuk memeriksa target, deskripsi, dan bukti sebelum menyetujui atau menolak.
                    </p>
                </div>
                <span class="approval-queue-count">
                    <i class="bi bi-hourglass-split"></i>
                    {{ $jumlahMenungguVerifikasi }} menunggu aksi
                </span>
            </div>

            <div class="approval-queue-grid">
                @foreach($pengajuanMenungguVerifikasi as $pengajuan)
                    @php
                        $waktuAjukan = $pengajuan->diajukan_pada ?? $pengajuan->updated_at ?? $pengajuan->created_at ?? null;
                        $detailUrl = route('ketualab.aktivitas-km.detail', [
                            'id' => $pengajuan->id_aktivitas,
                            'from' => 'monitoring',
                            'tahun' => $tahun,
                            'periode' => $periode,
                            'triwulan' => $triwulan,
                            'semester' => $semester,
                        ]);
                    @endphp
                    <div class="approval-queue-item">
                        <div class="approval-member">{{ $pengajuan->nama_anggota ?? '-' }}</div>
                        <div class="approval-meta">
                            {{ $pengajuan->nidn ?? '-' }} · {{ $pengajuan->kategori_km ?? '-' }}{{ !empty($pengajuan->sub_kategori_km) ? ' · ' . $pengajuan->sub_kategori_km : '' }}
                        </div>
                        <div class="approval-title">{{ \Illuminate\Support\Str::limit($pengajuan->judul_aktivitas ?? '-', 72) }}</div>
                        <div class="approval-footer">
                            <span class="approval-time">
                                <i class="bi bi-clock-history me-1"></i>
                                {{ $waktuAjukan ? \Carbon\Carbon::parse($waktuAjukan)->format('d/m/Y H:i') : 'Baru diajukan' }}
                            </span>
                            <a href="{{ $detailUrl }}" class="btn-approval-detail">
                                Detail
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($jumlahMenungguVerifikasi > $pengajuanMenungguVerifikasi->count())
                <div class="small text-muted mt-3 fw-semibold">
                    Menampilkan {{ $pengajuanMenungguVerifikasi->count() }} dari {{ $jumlahMenungguVerifikasi }} pengajuan yang menunggu verifikasi.
                </div>
            @endif
        </div>
    @endif

    @include('partials.periode-saat-ini')
@include('partials.filter-diterapkan')

<div class="category-card-grid">
        @foreach($kategoriDefault as $kategori)
            @php
                $item = $kategoriCards->firstWhere('kategori', $kategori) ?? [
                    'kategori' => $kategori,
                    'target' => 0,
                    'realisasi' => 0,
                    'sudah_dibagi' => 0,
                    'belum_dibagi' => 0,
                    'persentase' => 0,
                    'note' => 'Belum ada target pada kategori ini.',
                    'note_class' => 'success',
                ];

                $belumDibagiClass = (int) ($item['belum_dibagi'] ?? 0) > 0
                    ? 'metric-belum'
                    : 'metric-belum-done';

                $noteClass = match($item['note_class'] ?? 'success') {
                    'danger' => 'note-danger',
                    'warning' => 'note-warning',
                    default => 'note-success',
                };
            @endphp

            <div class="category-stat-card">
                <div class="category-stat-top">
                    <div class="category-name">
                        {{ $item['kategori'] }}
                        <span class="category-caption">Progress realisasi kategori KM · {{ $labelPeriode ?? 'Tahunan' }}</span>
                    </div>
                    <div class="category-percent">{{ min((int)($item['persentase'] ?? 0), 100) }}%</div>
                </div>

                <div class="category-progress">
                    <div class="category-progress-fill" style="width: {{ min((int)($item['persentase'] ?? 0), 100) }}%;"></div>
                </div>

                <div class="category-metric-grid">
                    <div class="category-metric metric-target">
                        <div class="metric-label">◉ Target</div>
                        <div class="metric-value">{{ (int)($item['target'] ?? 0) }}</div>
                    </div>

                    <div class="category-metric metric-realisasi">
                        <div class="metric-label">✓ Realisasi</div>
                        <div class="metric-value">{{ (int)($item['realisasi'] ?? 0) }}</div>
                    </div>

                    <div class="category-metric metric-dibagi">
                        <div class="metric-label">⇢ Sudah Dibagi</div>
                        <div class="metric-value">{{ (int)($item['sudah_dibagi'] ?? 0) }}</div>
                    </div>

                    <div class="category-metric {{ $belumDibagiClass }}">
                        <div class="metric-label">⌛ Belum Dibagi</div>
                        <div class="metric-value">{{ (int)($item['belum_dibagi'] ?? 0) }}</div>
                    </div>
                </div>

                <div class="category-note {{ $noteClass }}">
                    @if(($item['note_class'] ?? '') === 'danger')
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    @elseif(($item['note_class'] ?? '') === 'warning')
                        <i class="bi bi-hourglass-split"></i>
                    @else
                        <i class="bi bi-check-circle-fill"></i>
                    @endif
                    {{ $item['note'] ?? '-' }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="summary-grid">
        <div class="monitoring-summary-card">
            <div class="summary-label">Lab Riset</div>
            <div class="summary-value" style="font-size:18px; line-height:1.35;">
                {{ $lab->nama_lab ?? '-' }}
            </div>
        </div>

        <div class="monitoring-summary-card">
            <div class="summary-label">Jumlah Anggota</div>
            <div class="summary-value primary">{{ $jumlahAnggota ?? 0 }}</div>
        </div>

        <div class="monitoring-summary-card">
            <div class="summary-label">Target Periode</div>
            <div class="summary-value primary">{{ $totalTargetPeriode ?? 0 }}</div>
        </div>

        <div class="monitoring-summary-card">
            <div class="summary-label">Realisasi Periode</div>
            <div class="summary-value success">{{ $totalRealisasiPeriode ?? 0 }}</div>
        </div>

        <div class="monitoring-summary-card summary-progress-box">
            <div class="summary-progress-head">
                <span>Progress Total Anggota Lab</span>
                <span>{{ min((int)($persentaseTotal ?? 0), 100) }}%</span>
            </div>

            <div class="summary-progress-bar">
                <div class="summary-progress-fill" style="width: {{ min((int)($persentaseTotal ?? 0), 100) }}%;"></div>
            </div>

            <div class="summary-chip-row">
                <span class="summary-chip chip-success">
                    <i class="bi bi-check-circle-fill"></i>
                    Anggota Tercapai: {{ $anggotaTercapai ?? 0 }}
                </span>

                <span class="summary-chip chip-warning">
                    <i class="bi bi-hourglass-split"></i>
                    On Progress: {{ $anggotaOnProgress ?? 0 }}
                </span>

                <span class="summary-chip chip-danger">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    Belum Mulai: {{ $anggotaBelumMulai ?? 0 }}
                </span>

                <span class="summary-chip chip-warning">
                    Sisa Target: {{ $totalSisa ?? 0 }}
                </span>
            </div>
        </div>
    </div>

    <div class="monitoring-table-card">
        <div class="section-heading">
            <div>
                <div class="section-title">Monitoring Progress Anggota</div>
                <p class="section-desc">
                    Menampilkan anggota berdasarkan filter periode serta pencarian yang diterapkan.
                </p>
            </div>

            @if(($search ?? '') !== '')
                <span class="monitoring-period-pill" style="margin-top:0;">
                    <i class="bi bi-search"></i>
                    Hasil pencarian: “{{ $search }}”
                </span>
            @endif
        </div>

        <div class="ketualab-monitoring-anggota__records">
            @forelse($dataMonitoring as $index => $item)
                        @php
                            $statusClass = match($item['status_class'] ?? 'secondary') {
                                'success' => 'status-success',
                                'warning' => 'status-warning',
                                'danger' => 'status-danger',
                                default => 'status-secondary',
                            };
                        @endphp
                <article class="ketualab-monitoring-anggota__record">
                    <div class="ketualab-monitoring-anggota__identity"><span>{{ $index + 1 }}</span><div><strong>{{ $item['nama_dosen'] }}</strong><p>{{ $item['username'] }} · NIDN {{ $item['nidn'] }} · {{ $item['jad'] }}</p></div></div>
                    <dl>
                        <div><dt>KM diberikan</dt><dd>{{ $item['total_km_assign'] }}</dd></div>
                        <div><dt>Target periode</dt><dd>{{ $item['target_periode'] }}</dd></div>
                        <div><dt>Realisasi</dt><dd>{{ $item['total_realisasi'] }}</dd></div>
                        <div><dt>Sisa</dt><dd>{{ $item['sisa'] }}</dd></div>
                        <div><dt>Progress</dt><dd>{{ $item['persentase'] }}%</dd></div>
                    </dl>
                    <div class="ketualab-monitoring-anggota__progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ min((int)$item['persentase'], 100) }}" aria-label="Progress {{ $item['nama_dosen'] }} {{ $item['persentase'] }} persen"><span style="width:{{ min((int)$item['persentase'], 100) }}%"></span></div>
                    <div class="ketualab-monitoring-anggota__actions"><span class="status-pill {{ $statusClass }}">{{ $item['status'] }}</span><a
                                    href="/ketualab/detail-anggota/{{ $item['id_user'] }}?tahun={{ $tahun }}&periode={{ $periode }}&triwulan={{ $triwulan }}&semester={{ $semester }}"
                                    class="btn-monitoring-primary">
                                    Detail
                                </a></div>
                </article>
                    @empty
                        <div class="empty-state">Belum ada anggota yang sesuai dengan filter atau pencarian.</div>
                    @endforelse
        </div>
    </div>

    <div class="monitoring-table-card verification-history-card">
        <div class="section-heading">
            <div>
                <div class="section-title">Riwayat Aksi Verifikasi</div>
                <p class="section-desc">Riwayat persetujuan atau penolakan aktivitas KM oleh Ketua Lab.</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="verification-history-table">
                <thead>
                    <tr>
                        <th>Waktu Aksi</th>
                        <th>Anggota</th>
                        <th>Aktivitas KM</th>
                        <th>Keputusan</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayatVerifikasi as $riwayat)
                        @php
                            $diterima = ($riwayat->keputusan ?? '') === 'Accepted';
                            $detailUrl = route('ketualab.aktivitas-km.detail', [
                                'id' => $riwayat->id_aktivitas,
                                'from' => 'monitoring',
                                'tahun' => $tahun,
                                'periode' => $periode,
                                'triwulan' => $triwulan,
                                'semester' => $semester,
                            ]);
                        @endphp
                        <tr>
                            <td>{{ !empty($riwayat->waktu_aksi) ? \Carbon\Carbon::parse($riwayat->waktu_aksi)->format('d/m/Y H:i') : '-' }}</td>
                            <td class="member-name">{{ $riwayat->nama_anggota ?? '-' }}</td>
                            <td>
                                <div class="member-name">{{ $riwayat->judul_aktivitas ?? '-' }}</div>
                                <span class="member-meta">{{ $riwayat->kategori_km ?? '-' }}{{ !empty($riwayat->sub_kategori_km) ? ' · ' . $riwayat->sub_kategori_km : '' }}</span>
                            </td>
                            <td>
                                <span class="verification-decision {{ $diterima ? 'accepted' : 'rejected' }}">
                                    <i class="bi {{ $diterima ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                                    {{ $diterima ? 'Disetujui' : 'Ditolak' }}
                                </span>
                            </td>
                            <td class="verification-history-note">{{ $riwayat->catatan_verifikasi ?: '-' }}</td>
                            <td><a href="{{ $detailUrl }}" class="btn-category-detail">Detail</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"><div class="empty-state">Belum ada aksi verifikasi yang tercatat.</div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-heading" id="rekap-kategori" style="margin: 20px 0 12px;">
        <div>
            <div class="section-title">
                {{ $kategoriDetail ? 'Detail Target Anggota Kategori ' . $kategoriDetail : 'Rekap Target Anggota per Kategori' }}
            </div>
            <p class="section-desc">
                Menampilkan maksimal 10 rincian target anggota pada setiap halaman. Gunakan pagination untuk melihat data berikutnya.
            </p>
        </div>

        @if($kategoriDetail)
            @php
                $allKategoriQuery = [
                    'tahun' => $tahun,
                    'periode' => $periode,
                    'triwulan' => $triwulan,
                    'semester' => $semester,
                ];

                if (!empty($search)) {
                    $allKategoriQuery['search'] = $search;
                }

                $allKategoriUrl = url('/ketualab/monitoring-anggota') . '?' . http_build_query($allKategoriQuery);
            @endphp

            <a href="{{ $allKategoriUrl }}#rekap-kategori" class="btn-category-detail">
                <i class="bi bi-grid-3x3-gap"></i>
                Tampilkan Semua Kategori
            </a>
        @endif
    </div>

    <div class="category-detail-grid">
        @foreach(($kategoriDitampilkan ?? $kategoriDefault) as $kategori)
            @php
                $detail = $rincianPerKategori->get($kategori, []);

                $rows = collect(data_get($detail, 'rows', []));
                $totalRows = (int) data_get($detail, 'total', 0);
                $from = (int) data_get($detail, 'from', 0);
                $to = (int) data_get($detail, 'to', 0);
                $currentPage = (int) data_get($detail, 'current_page', 1);
                $lastPage = (int) data_get($detail, 'last_page', 1);
                $pageKey = data_get(
                    $detail,
                    'page_key',
                    'page_' . \Illuminate\Support\Str::slug($kategori, '_')
                );

                $baseQuery = [
                    'tahun' => $tahun,
                    'periode' => $periode,
                    'triwulan' => $triwulan,
                    'semester' => $semester,
                ];

                if (!empty($search)) {
                    $baseQuery['search'] = $search;
                }

                if ($kategoriDetail) {
                    $baseQuery['kategori_detail'] = $kategoriDetail;
                }

                $focusQuery = $baseQuery;
                $focusQuery['kategori_detail'] = $kategori;
                $focusQuery[$pageKey] = 1;

                $focusUrl = url('/ketualab/monitoring-anggota')
                    . '?' . http_build_query($focusQuery)
                    . '#rekap-kategori';

                $visiblePages = collect([
                    1,
                    2,
                    $currentPage - 1,
                    $currentPage,
                    $currentPage + 1,
                    $lastPage - 1,
                    $lastPage,
                ])
                    ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                    ->unique()
                    ->sort()
                    ->values();
            @endphp

            <div class="monitoring-category-card" id="kategori-{{ \Illuminate\Support\Str::slug($kategori) }}">
                <div class="category-card-toolbar">
                    <div>
                        <div class="category-block-heading">{{ $kategori }}</div>
                        <p class="section-desc">
                            Rincian target KM kategori {{ $kategori }} untuk {{ $labelPeriode ?? 'periode aktif' }}.
                        </p>

                        <div class="category-card-meta">
                            <span class="category-count-pill">
                                <i class="bi bi-people-fill"></i>
                                {{ $totalRows }} rincian target anggota
                            </span>

                            @if($totalRows > 0)
                                <span>
                                    Menampilkan {{ $from }}–{{ $to }} dari {{ $totalRows }} data.
                                </span>
                            @endif
                        </div>
                    </div>

                    @if(!$kategoriDetail)
                        <a href="{{ $focusUrl }}" class="btn-category-detail">
                            <i class="bi bi-arrows-fullscreen"></i>
                            Lihat Selengkapnya
                        </a>
                    @endif
                </div>

                <div class="table-wrap">
                    <table class="category-detail-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Anggota</th>
                                <th>Sub Kategori</th>
                                <th>Keterangan</th>
                                <th>Target</th>
                                <th>Realisasi</th>
                                <th>Sisa</th>
                                <th>Progress</th>
                                <th>Tenggat Aktif</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($rows as $index => $row)
                                @php
                                    $statusClass = match($row->status_class ?? 'secondary') {
                                        'success' => 'status-success',
                                        'warning' => 'status-warning',
                                        'danger' => 'status-danger',
                                        default => 'status-secondary',
                                    };

                                    $deadlineClass = match($row->tenggat_class ?? 'secondary') {
                                        'success' => 'status-success',
                                        'warning' => 'status-warning',
                                        'danger' => 'status-danger',
                                        default => 'status-secondary',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $from + $index }}</td>
                                    <td>
                                        <span class="member-name">{{ $row->nama_anggota }}</span>
                                        <span class="member-meta">{{ $row->nidn }} · {{ $row->jad }}</span>
                                    </td>
                                    <td>{{ $row->sub_kategori_km ?: '-' }}</td>
                                    <td class="keterangan-cell">{{ $row->keterangan ?: '-' }}</td>
                                    <td>{{ $row->target_periode }}</td>
                                    <td style="color:#059669; font-weight:900;">{{ $row->realisasi_periode }}</td>
                                    <td style="color:#d97706; font-weight:900;">{{ $row->sisa_periode }}</td>
                                    <td>{{ $row->persentase }}%</td>
                                    <td>
                                        @if(collect($row->tenggat_aktif ?? [])->isNotEmpty())
                                            <div class="tenggat-list">
                                                @foreach($row->tenggat_aktif as $tenggat)
                                                    <span class="tenggat-item">
                                                        {{ $tenggat['triwulan'] }}
                                                        {{ $tenggat['tanggal']->format('d/m/Y') }}
                                                    </span>
                                                @endforeach
                                            </div>
                                            <div style="margin-top:5px;">
                                                <span class="deadline-pill {{ $deadlineClass }}">{{ $row->status_tenggat }}</span>
                                            </div>
                                        @else
                                            <span class="member-meta">Belum diatur</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-pill {{ $statusClass }}">{{ $row->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10">
                                        <div class="empty-state">
                                            Belum ada target KM anggota pada kategori ini.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($lastPage > 1)
                    <div class="category-pagination-wrap">
                        <div class="category-pagination-info">
                            Halaman {{ $currentPage }} dari {{ $lastPage }}
                        </div>

                        <div class="category-pagination">
                            @php
                                $previousQuery = $baseQuery;
                                $previousQuery[$pageKey] = max(1, $currentPage - 1);

                                $nextQuery = $baseQuery;
                                $nextQuery[$pageKey] = min($lastPage, $currentPage + 1);
                            @endphp

                            <a
                                class="category-page-link {{ $currentPage <= 1 ? 'disabled' : '' }}"
                                href="{{ url('/ketualab/monitoring-anggota') . '?' . http_build_query($previousQuery) }}#kategori-{{ \Illuminate\Support\Str::slug($kategori) }}"
                                aria-label="Halaman sebelumnya">
                                <i class="bi bi-chevron-left"></i>
                            </a>

                            @php $lastRenderedPage = 0; @endphp

                            @foreach($visiblePages as $page)
                                @if($page > ($lastRenderedPage + 1))
                                    <span class="category-page-ellipsis">…</span>
                                @endif

                                @php
                                    $pageQuery = $baseQuery;
                                    $pageQuery[$pageKey] = $page;
                                @endphp

                                <a
                                    class="category-page-link {{ $page === $currentPage ? 'active' : '' }}"
                                    href="{{ url('/ketualab/monitoring-anggota') . '?' . http_build_query($pageQuery) }}#kategori-{{ \Illuminate\Support\Str::slug($kategori) }}">
                                    {{ $page }}
                                </a>

                                @php $lastRenderedPage = $page; @endphp
                            @endforeach

                            <a
                                class="category-page-link {{ $currentPage >= $lastPage ? 'disabled' : '' }}"
                                href="{{ url('/ketualab/monitoring-anggota') . '?' . http_build_query($nextQuery) }}#kategori-{{ \Illuminate\Support\Str::slug($kategori) }}"
                                aria-label="Halaman berikutnya">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<script>
    (function () {
        const periodeSelect = document.getElementById('periode');
        const triwulanGroup = document.getElementById('triwulanGroup');
        const semesterGroup = document.getElementById('semesterGroup');

        function syncPeriodeFields() {
            const mode = periodeSelect.value;

            triwulanGroup.style.display = mode === 'triwulan' ? 'block' : 'none';
            semesterGroup.style.display = mode === 'semester' ? 'block' : 'none';
        }

        if (periodeSelect) {
            periodeSelect.addEventListener('change', syncPeriodeFields);
            syncPeriodeFields();
        }
    })();
</script>
@endsection
