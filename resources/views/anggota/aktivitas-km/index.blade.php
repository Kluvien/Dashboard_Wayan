@extends('layouts.app')

@section('title', 'Aktivitas KM')

@section('content')
@php
    $targetKmSaya = collect($targetKmSaya ?? []);
    $aktivitas = collect($aktivitas ?? []);
    $riwayatRealisasi = collect($riwayatRealisasi ?? $aktivitas);
    $kategoriCards = collect($kategoriCards ?? []);

    $namaAnggota = $namaAnggota ?? auth()->user()->username ?? 'Anggota';
    $tahun = (int) ($tahun ?? now()->year);
    $periode = $periode ?? 'tahun';
    $triwulan = (int) ($triwulan ?? 1);
    $semester = (int) ($semester ?? 1);

    $tahunOptions = collect($tahunOptions ?? [$tahun])
        ->map(fn ($item) => (int) $item)
        ->unique()
        ->sortDesc()
        ->values();

    $labelPeriode = $labelPeriode ?? ('Tahunan ' . $tahun);
    $keteranganPeriode = $keteranganPeriode ?? 'Data target dan realisasi ditampilkan untuk satu tahun penuh.';

    $labelStatusAktivitas = function ($status) {
        return match($status) {
            'Accepted' => 'Disetujui',
            'Submitted' => 'Diajukan',
            'On Progress' => 'Sedang Berjalan',
            'Rejected' => 'Ditolak',
            'Pending' => 'Menunggu Verifikasi',
            'Belum Mulai' => 'Belum Mulai',
            default => $status ?: '-',
        };
    };

    $formatTriwulanDitambahkan = function ($tanggal) {
        if (empty($tanggal)) {
            return '-';
        }

        try {
            $bulan = \Carbon\Carbon::parse($tanggal)->month;
            return 'Triwulan ' . (int) ceil($bulan / 3);
        } catch (\Throwable $e) {
            return '-';
        }
    };
@endphp

<style>
    .aktivitas-km-table th,
    .aktivitas-km-table td,
    .target-km-table th,
    .target-km-table td,
    .history-activity-table th,
    .history-activity-table td {
        vertical-align: middle;
        font-size: 13px;
        white-space: nowrap;
    }

    .target-km-table {
        width: 100%;
    }

    .aktivitas-km-table,
    .history-activity-table {
        min-width: 0;
        table-layout: fixed;
    }

    .target-km-table td.detail-target,
    .aktivitas-km-table td.activity-title,
    .history-activity-table td.history-title {
        min-width: 240px;
        white-space: normal;
    }

    .page-filter-form {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
        margin: 0;
    }

    .page-filter-control {
        height: 38px;
        min-width: 122px;
        padding: 0 11px;
        border: 1px solid #CBD5E1;
        border-radius: 10px;
        background: #FFFFFF;
        color: #1E293B;
        font-size: 13px;
        font-weight: 700;
    }

    .page-filter-control.year {
        min-width: 102px;
    }

    .page-filter-form .btn {
        min-height: 38px;
        padding: 0 15px;
        font-size: 13px;
        font-weight: 800;
    }

    .period-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
        padding: 5px 9px;
        border: 1px solid #DBEAFE;
        border-radius: 999px;
        background: #EFF6FF;
        color: #2563EB;
        font-size: 12px;
        font-weight: 700;
    }

    .summary-total {
        font-size: 13px;
        white-space: nowrap;
    }

    .km-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .km-summary-card {
        min-height: 258px;
        overflow: hidden;
        padding: 15px;
        border: 1px solid #E2E8F0;
        border-top: 4px solid #5D8EF8;
        border-radius: 16px;
        background: linear-gradient(180deg, #FFFFFF 0%, #F9FBFF 100%);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .045);
    }

    .km-summary-title-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .km-summary-title {
        color: #334155;
        font-size: 14px;
        font-weight: 800;
    }

    .km-summary-subtitle {
        margin-top: 2px;
        color: #94A3B8;
        font-size: 10px;
        font-weight: 700;
    }

    .km-summary-percent {
        flex: 0 0 auto;
        min-width: 58px;
        padding: 7px 9px;
        border: 1px solid #D7E6FF;
        border-radius: 12px;
        background: #EDF4FF;
        color: #2563EB;
        font-size: 20px;
        font-weight: 900;
        line-height: 1;
        text-align: center;
    }

    .km-summary-progress {
        height: 8px;
        overflow: hidden;
        margin: 12px 0;
        border-radius: 999px;
        background: #E8EDF5;
    }

    .km-summary-progress-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #477EF7, #77A2FF);
    }

    .km-summary-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .km-summary-info {
        min-height: 56px;
        padding: 9px 10px;
        border: 1px solid #E2E8F0;
        border-radius: 11px;
        background: #F8FAFC;
    }

    .km-summary-info.target {
        border-color: #BFDBFE;
        background: #EFF6FF;
    }

    .km-summary-info.realisasi {
        border-color: #BBF7D0;
        background: #ECFDF5;
    }

    .km-summary-info.sisa-alert {
        border-color: #FECACA;
        background: #FEF2F2;
    }

    .km-summary-info.sisa-safe {
        border-color: #BBF7D0;
        background: #ECFDF5;
    }

    .km-summary-info.subkategori {
        border-color: #DDD6FE;
        background: #F5F3FF;
    }

    .km-summary-label {
        color: #64748B;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .km-summary-value {
        margin-top: 3px;
        color: #0F172A;
        font-size: 19px;
        font-weight: 900;
        line-height: 1;
    }

    .km-summary-info.target .km-summary-value { color: #2563EB; }
    .km-summary-info.realisasi .km-summary-value { color: #059669; }
    .km-summary-info.sisa-alert .km-summary-value { color: #DC2626; }
    .km-summary-info.sisa-safe .km-summary-value { color: #15803D; }
    .km-summary-info.subkategori .km-summary-value { color: #7C3AED; }

    .km-summary-note {
        margin: 11px 0 12px;
        min-height: 26px;
        font-size: 10px;
        font-weight: 800;
        line-height: 1.35;
    }

    .km-summary-note.success { color: #15803D; }
    .km-summary-note.danger { color: #DC2626; }
    .km-summary-note.secondary { color: #64748B; }

    .km-summary-card .btn {
        width: 100%;
        padding: 7px 10px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 800;
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
        margin-bottom: 7px;
        color: #0F172A;
        font-size: 14px;
        font-weight: 800;
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

    .target-deadline-list {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 7px;
    }

    .target-deadline {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 6px;
        border: 1px solid #DBEAFE;
        border-radius: 7px;
        background: #F8FAFC;
        color: #334155;
        font-size: 10px;
        font-weight: 700;
    }

    .target-period-number {
        color: #1D4ED8;
        font-size: 15px;
        font-weight: 800;
        text-align: center;
    }

    .target-realisasi-number {
        color: #059669;
        font-size: 15px;
        font-weight: 800;
        text-align: center;
    }

    .target-period-empty {
        color: #94A3B8;
        font-weight: 700;
        text-align: center;
    }

    .target-summary-number {
        font-size: 16px;
        font-weight: 800;
        text-align: center;
    }

    .target-summary-primary { color: #1D4ED8; }
    .target-summary-success { color: #15803D; }
    .target-summary-warning { color: #D97706; }

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

    .target-status-success { background: #DCFCE7; color: #15803D; }
    .target-status-warning { background: #FEF3C7; color: #B45309; }
    .target-status-danger { background: #FEE2E2; color: #B91C1C; }
    .target-status-secondary { background: #E2E8F0; color: #64748B; }

    .empty-state {
        padding: 28px;
        color: #64748B;
        text-align: center;
    }

    @media (max-width: 1200px) {
        .km-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 576px) {
        .km-summary-grid {
            grid-template-columns: 1fr;
        }

        .page-filter-form {
            justify-content: flex-start;
            width: 100%;
        }

        .page-filter-control,
        .page-filter-form .btn {
            flex: 1 1 100%;
            width: 100%;
        }
    }
    .anggota-activities__header { display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; padding: 20px 22px; margin-bottom: 16px; background: #FFF; border: 1px solid #E2E8F0; border-radius: 14px; }
    .anggota-activities__eyebrow { margin: 0 0 5px; color: #2563EB; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .anggota-activities__title { margin: 0; color: #0F172A; font-size: 24px; font-weight: 700; }
    .anggota-activities__description { margin: 7px 0 0; color: #64748B; font-size: 13px; }

    .anggota-activity-index__section { margin-bottom: 16px; overflow: hidden; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; }
    .anggota-activity-index__section-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; padding: 18px 22px; border-bottom: 1px solid #EEF2F7; }
    .anggota-activity-index__section-title { margin: 0; color: #0F172A; font-size: 17px; font-weight: 700; }
    .anggota-activity-index__section-description { max-width: 780px; margin: 5px 0 0; color: #5B6472; font-size: 14px; line-height: 1.55; }
    .anggota-activity-index__period { display: flex; align-items: flex-start; gap: 8px; margin-top: 10px; color: #64748B; font-size: 12px; }
    .anggota-activity-index__current-period { display: flex; align-items: center; gap: 10px; padding: 11px 22px; border-bottom: 1px solid #EEF2F7; background: #F8FAFC; color: #334155; font-size: 12px; }
    .anggota-activity-index__current-period strong { color: #0F172A; font-weight: 700; }
    .anggota-activity-index__filter { display: flex; align-items: flex-end; justify-content: flex-end; gap: 8px; flex-wrap: wrap; }
    .anggota-activity-index__filter-field { display: grid; gap: 5px; }
    .anggota-activity-index__filter-label { color: #64748B; font-size: 11px; font-weight: 700; }
    .anggota-activity-index__filter-button { width: auto; min-width: 110px; min-height: 38px; padding: 7px 14px; border-radius: 8px; font-size: 13px; font-weight: 700; }
    .anggota-activity-index__metrics { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); border-bottom: 1px solid #EEF2F7; }
    .anggota-activity-index__metric { min-height: 92px; padding: 16px 22px; border-right: 1px solid #EEF2F7; }
    .anggota-activity-index__metric:last-child { border-right: 0; }
    .anggota-activity-index__metric-label { color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .anggota-activity-index__metric-value { display: block; margin-top: 5px; color: #0F172A; font-size: 24px; font-weight: 700; font-variant-numeric: tabular-nums; }
    .anggota-activity-index__progress { height: 6px; margin-top: 9px; overflow: hidden; border-radius: 999px; background: #E2E8F0; }
    .anggota-activity-index__progress-fill { height: 100%; background: #2563EB; }
    .anggota-activity-index__comparison-head, .anggota-activity-index__comparison-row { display: grid; grid-template-columns: minmax(180px, 1.5fr) repeat(4, minmax(84px, .55fr)) minmax(180px, 1fr); align-items: center; column-gap: 14px; }
    .anggota-activity-index__comparison-head { padding: 9px 22px; background: #F8FAFC; border-bottom: 1px solid #CBD5E1; color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .anggota-activity-index__comparison-row { min-height: 72px; padding: 11px 22px; border-bottom: 1px solid #E5EAF0; color: #374151; font-size: 14px; }
    .anggota-activity-index__comparison-row:last-child { border-bottom: 0; }
    .anggota-activity-index__comparison-row:hover { background: #F8FAFC; }
    .anggota-activity-index__category-name { color: #0F172A; font-weight: 700; }
    .anggota-activity-index__category-note { display: block; margin-top: 3px; color: #64748B; font-size: 12px; }
    .anggota-activity-index__category-note.is-success { color: #15803D; }
    .anggota-activity-index__category-note.is-warning { color: #B45309; }
    .anggota-activity-index__number { text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; }
    .anggota-activity-index__comparison-progress { display: grid; grid-template-columns: minmax(90px, 1fr) auto; align-items: center; gap: 9px; }
    .anggota-activity-index__detail-link { display: inline-flex; margin-top: 7px; color: #2563EB; font-size: 12px; font-weight: 700; text-decoration: none; }
    .anggota-activity-index__scroll { overflow-x: auto; }
    .anggota-activity-index__table { width: 100%; margin: 0; }
    .anggota-activity-index__table > thead > tr > th { padding: 11px 14px; background: #EEF2F6; border: 0; border-bottom: 1px solid #D5DCE5; color: #374151; font-size: 13px; font-weight: 700; letter-spacing: .01em; vertical-align: middle; white-space: normal; }
    .anggota-activity-index__table > tbody > tr > td { padding: 11px 14px; border: 0; border-bottom: 1px solid #E5EAF0; color: #374151; font-size: 14px; line-height: 1.5; font-weight: 500; vertical-align: middle; overflow-wrap: anywhere; }
    .anggota-activity-index__table > tbody > tr:last-child > td { border-bottom: 0; }
    .anggota-activity-index__table > tbody > tr:hover > td { background: #F8FAFC; }
    .anggota-activity-index__table.target-km-table > tbody > tr { height: auto; min-height: 0; }
    .anggota-activity-index__table.target-km-table > tbody > tr > td { height: auto; min-height: 0; padding: 10px 14px; vertical-align: middle; }
    .anggota-activity-index__table.target-km-table > tbody > tr > td.detail-target { width: 310px; min-width: 270px; vertical-align: top; white-space: normal; }
    .anggota-activity-index__target-detail { display: grid; gap: 3px; min-height: 0; }
    .anggota-activity-index__target-detail .anggota-activity-index__metadata { margin-top: 0; line-height: 1.35; }
    .anggota-activity-index__deadlines { display: grid; grid-template-columns: repeat(2, minmax(0, max-content)); gap: 2px 14px; margin-top: 3px; }
    .anggota-activity-index__table .anggota-activity-index__cell--number { text-align: center; font-variant-numeric: tabular-nums; }
    .anggota-activity-index__table .anggota-activity-index__cell--numeric { text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .anggota-activity-index__table .anggota-activity-index__cell--date { text-align: center; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .anggota-activity-index__identity { color: #0F172A; font-weight: 600; }
    .anggota-activity-index__metadata { margin-top: 3px; color: #64748B; font-size: 12px; font-weight: 500; white-space: normal; }
    .anggota-activity-index__deadline { display: block; margin: 0; color: #64748B; font-size: 11px; line-height: 1.35; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .anggota-activity-index__status { display: inline-flex; align-items: center; min-height: 26px; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .anggota-activity-index__status--success { color: #15803D; background: #F0FDF4; }
    .anggota-activity-index__status--warning { color: #B45309; background: #FFFBEB; }
    .anggota-activity-index__status--danger { color: #B91C1C; background: #FEF2F2; }
    .anggota-activity-index__status--neutral { color: #64748B; background: #F8FAFC; }
    .anggota-activity-index__actions { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; min-width: 150px; }
    .anggota-activity-index__action { min-height: 32px; padding: 5px 9px; border-radius: 7px; font-size: 12px; font-weight: 700; }
    .anggota-activity-index__lock { color: #64748B; font-size: 11px; font-weight: 600; white-space: normal; }
    .anggota-activity-index__empty { padding: 24px 16px !important; color: #64748B !important; font-size: 13px !important; text-align: center; }
    @media (max-width: 991.98px) {
        .anggota-activity-index__metrics { grid-template-columns: repeat(2, 1fr); }
        .anggota-activity-index__comparison-head { display: none; }
        .anggota-activity-index__comparison-row { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px 18px; }
        .anggota-activity-index__comparison-row > :first-child, .anggota-activity-index__comparison-progress { grid-column: 1 / -1; }
    }
    @media (max-width: 575.98px) {
        .anggota-activities__header, .anggota-activity-index__section-header { flex-direction: column; }
        .anggota-activity-index__filter, .anggota-activity-index__filter-field, .anggota-activity-index__filter .page-filter-control, .anggota-activity-index__filter-button { width: 100%; }
        .anggota-activity-index__metrics, .anggota-activity-index__comparison-row { grid-template-columns: 1fr; }
        .anggota-activity-index__metric { border-right: 0; border-bottom: 1px solid #EEF2F7; }
        .anggota-activity-index__comparison-row > * { grid-column: 1; }
        .anggota-activity-index__deadlines { grid-template-columns: 1fr; }
    }
    .anggota-activity-index__target-records{border-top:1px solid #D5DCE5}.anggota-activity-index__target-record{padding:14px 20px 18px;border-bottom:1px solid #E5EAF0}.anggota-activity-index__target-record header{display:flex;justify-content:space-between;gap:16px}.anggota-activity-index__target-record h3{margin:2px 0;color:#1F2937;font-size:16px}.anggota-activity-index__target-record header p{margin:0;color:#5B6472;font-size:14px;line-height:1.5}.anggota-activity-index__target-record dl{display:flex;gap:24px;margin:12px 0}.anggota-activity-index__target-record dt{color:#5B6472;font-size:13px}.anggota-activity-index__target-record dd{margin:2px 0 0;color:#1F2937;font-size:14px;font-weight:700;font-variant-numeric:tabular-nums}.anggota-activity-index__period-table{width:100%;border-collapse:collapse;color:#374151;font-size:14px}.anggota-activity-index__period-table th,.anggota-activity-index__period-table td{padding:9px 12px;border-bottom:1px solid #E5EAF0}.anggota-activity-index__period-table thead th{background:#EEF2F6;font-size:13px;text-align:left}.anggota-activity-index__period-table td:nth-child(2),.anggota-activity-index__period-table td:nth-child(3){text-align:right;font-weight:700;font-variant-numeric:tabular-nums}@media(max-width:640px){.anggota-activity-index__target-record{padding:14px 16px}.anggota-activity-index__target-record header{flex-direction:column}.anggota-activity-index__target-record dl{flex-wrap:wrap}}
</style>

<header class="anggota-activities__header">
    <div>
        <p class="anggota-activities__eyebrow">Realisasi Kontrak Manajemen</p>
        <h1 class="anggota-activities__title">Aktivitas KM</h1>
        <p class="anggota-activities__description">Kelola aktivitas, bukti, dan riwayat realisasi KM Anda.</p>
    </div>
    <a href="/anggota/aktivitas-km/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah Aktivitas</a>
</header>

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

{{-- ========================================================= --}}
{{-- RINGKASAN KM NAMA ANGGOTA --}}
{{-- ========================================================= --}}
<section id="ringkasan-km" class="anggota-activity-index__section" aria-labelledby="activity-summary-title">
    <header class="anggota-activity-index__section-header">
        <div>
            <h2 id="activity-summary-title" class="anggota-activity-index__section-title">Ringkasan KM {{ $namaAnggota }}</h2>
            <p class="anggota-activity-index__section-description">
                Ringkasan target, realisasi disetujui, sisa target, dan capaian KM pada {{ $labelPeriode }}.
            </p>
            <span class="anggota-activity-index__period">
                <i class="bi bi-calendar3"></i>
                {{ $keteranganPeriode }}
            </span>
        </div>
            <form method="GET" action="{{ route('anggota.aktivitas-km.index') }}" class="anggota-activity-index__filter">
                <div class="anggota-activity-index__filter-field">
                    <label for="periodeRingkasan" class="anggota-activity-index__filter-label">Periode</label>
                <select name="periode" id="periodeRingkasan" class="page-filter-control">
                    <option value="tahun" {{ $periode === 'tahun' ? 'selected' : '' }}>Tahunan</option>
                    <option value="triwulan" {{ $periode === 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                    <option value="semester" {{ $periode === 'semester' ? 'selected' : '' }}>Semester</option>
                </select>
                </div>
                <div class="anggota-activity-index__filter-field">
                    <label for="tahunRingkasan" class="anggota-activity-index__filter-label">Tahun</label>
                <select name="tahun" id="tahunRingkasan" class="page-filter-control year">
                    @foreach($tahunOptions as $itemTahun)
                        <option value="{{ $itemTahun }}" {{ $tahun === (int) $itemTahun ? 'selected' : '' }}>
                            {{ $itemTahun }}
                        </option>
                    @endforeach
                </select>
                </div>
                <select
                    name="triwulan"
                    id="triwulanRingkasan"
                    class="page-filter-control year {{ $periode === 'triwulan' ? '' : 'd-none' }}">
                    @for($tw = 1; $tw <= 4; $tw++)
                        <option value="{{ $tw }}" {{ $triwulan === $tw ? 'selected' : '' }}>
                            Triwulan {{ $tw }}
                        </option>
                    @endfor
                </select>

                <select
                    name="semester"
                    id="semesterRingkasan"
                    class="page-filter-control year {{ $periode === 'semester' ? '' : 'd-none' }}">
                    <option value="1" {{ $semester === 1 ? 'selected' : '' }}>Semester 1</option>
                    <option value="2" {{ $semester === 2 ? 'selected' : '' }}>Semester 2</option>
                </select>

                <button type="submit" class="btn btn-primary anggota-activity-index__filter-button">
                    <i class="bi bi-funnel-fill me-1"></i> Terapkan
                </button>
            </form>
    </header>

    <div class="anggota-activity-index__metrics">
        <div class="anggota-activity-index__metric"><span class="anggota-activity-index__metric-label">Total Target</span><strong class="anggota-activity-index__metric-value">{{ $totalTarget ?? 0 }}</strong></div>
        <div class="anggota-activity-index__metric"><span class="anggota-activity-index__metric-label">Total Realisasi</span><strong class="anggota-activity-index__metric-value">{{ $totalRealisasi ?? 0 }}</strong></div>
        <div class="anggota-activity-index__metric"><span class="anggota-activity-index__metric-label">Sisa</span><strong class="anggota-activity-index__metric-value">{{ $totalSisa ?? 0 }}</strong></div>
        <div class="anggota-activity-index__metric">
            <span class="anggota-activity-index__metric-label">Persentase Capaian</span>
            <strong class="anggota-activity-index__metric-value">{{ $persentaseTotal ?? 0 }}%</strong>
            <div class="anggota-activity-index__progress" role="progressbar" aria-label="Persentase capaian KM anggota" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $persentaseTotal ?? 0 }}">
                <div class="anggota-activity-index__progress-fill" style="width: {{ min((int) ($persentaseTotal ?? 0), 100) }}%;"></div>
            </div>
        </div>
    </div>

    <div class="anggota-activity-index__current-period" role="status" aria-label="Periode kalender saat ini">
        <i class="bi bi-calendar-range text-primary"></i>
        <span>Periode saat ini:</span>
        <strong>Triwulan {{ (int) ceil(now()->month / 3) }}</strong>
        <span aria-hidden="true">·</span>
        <strong>Semester {{ now()->month <= 6 ? 1 : 2 }}</strong>
        <span aria-hidden="true">·</span>
        <strong>Tahun {{ now()->year }}</strong>
    </div>

    <div class="anggota-activity-index__comparison-head" aria-hidden="true">
        <span>Kategori</span><span class="text-end">Target</span><span class="text-end">Realisasi</span><span class="text-end">Sisa</span><span class="text-end">Subkategori</span><span>Progress</span>
    </div>
    <div>
        @forelse($kategoriCards as $card)
            <div class="anggota-activity-index__comparison-row">
                <div>
                    <span class="anggota-activity-index__category-name">{{ $card['kategori'] ?? '-' }}</span>
                    <span class="anggota-activity-index__category-note {{ ($card['catatan_class'] ?? '') === 'success' ? 'is-success' : (((int) ($card['sisa'] ?? 0) > 0) ? 'is-warning' : '') }}">{{ $card['catatan'] ?? '-' }}</span>
                    <a href="#daftar-target-km" class="anggota-activity-index__detail-link">Lihat detail</a>
                </div>
                <span class="anggota-activity-index__number">{{ $card['target'] ?? 0 }}</span>
                <span class="anggota-activity-index__number">{{ $card['realisasi'] ?? 0 }}</span>
                <span class="anggota-activity-index__number">{{ $card['sisa'] ?? 0 }}</span>
                <span class="anggota-activity-index__number">{{ $card['jumlah_subkategori'] ?? 0 }}</span>
                <div class="anggota-activity-index__comparison-progress">
                    <div class="anggota-activity-index__progress" role="progressbar" aria-label="Capaian {{ $card['kategori'] ?? 'kategori KM' }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $card['persentase'] ?? 0 }}"><div class="anggota-activity-index__progress-fill" style="width: {{ min((int) ($card['persentase'] ?? 0), 100) }}%;"></div></div>
                    <strong class="anggota-activity-index__number">{{ $card['persentase'] ?? 0 }}%</strong>
                </div>
            </div>
        @empty
            <div class="anggota-activity-index__empty">Belum ada ringkasan KM pada {{ $labelPeriode }}.</div>
        @endforelse
    </div>
</section>

{{-- ========================================================= --}}
{{-- DAFTAR TARGET KM NAMA ANGGOTA --}}
{{-- ========================================================= --}}
<section id="daftar-target-km" class="anggota-activity-index__section" aria-labelledby="activity-target-title">
    <header class="anggota-activity-index__section-header">
        <div>
            <h2 id="activity-target-title" class="anggota-activity-index__section-title">Daftar Target KM {{ $namaAnggota }}</h2>
            <p class="anggota-activity-index__section-description">
                Menampilkan target tahunan yang telah dibagikan oleh Ketua Lab, beserta target dan realisasi per triwulan.
            </p>
            <span class="anggota-activity-index__period">
                <i class="bi bi-calendar3"></i>
                Menampilkan data tahun {{ $tahun }}
            </span>
        </div>

        <form method="GET" action="{{ route('anggota.aktivitas-km.index') }}" class="anggota-activity-index__filter">
            <input type="hidden" name="periode" value="{{ $periode }}">
            <input type="hidden" name="triwulan" value="{{ $triwulan }}">
            <input type="hidden" name="semester" value="{{ $semester }}">

            <select name="tahun" class="page-filter-control year" aria-label="Pilih tahun target">
                @foreach($tahunOptions as $itemTahun)
                    <option value="{{ $itemTahun }}" {{ $tahun === (int) $itemTahun ? 'selected' : '' }}>
                        {{ $itemTahun }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-outline-primary anggota-activity-index__filter-button">
                <i class="bi bi-funnel-fill me-1"></i> Filter
            </button>
        </form>
    </header>

    <div class="anggota-activity-index__target-records">
                @forelse($targetKmSaya as $index => $target)
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

                    @endphp

                    <article class="anggota-activity-index__target-record">
                        <header><div><span>{{ $index + 1 }} · {{ $target->tahun_km ?? '-' }}</span><h3>{{ $target->kategori_km ?? '-' }}</h3><p>{{ $target->sub_kategori_km ?? '-' }} · {{ $target->keterangan ?? '-' }}</p></div><span class="anggota-activity-index__status {{ ($target->status_class ?? '') === 'success' ? 'anggota-activity-index__status--success' : (($target->status_class ?? '') === 'warning' ? 'anggota-activity-index__status--warning' : 'anggota-activity-index__status--neutral') }}">{{ $target->status_target ?? 'Belum Ada Target' }}</span></header>
                        <dl><div><dt>Total target</dt><dd>{{ $target->jumlah_km ?? 0 }}</dd></div><div><dt>Total realisasi</dt><dd>{{ $target->total_realisasi ?? 0 }}</dd></div><div><dt>Sisa</dt><dd>{{ $target->sisa_km ?? 0 }}</dd></div></dl>
                        <table class="anggota-activity-index__period-table"><thead><tr><th scope="col">Periode</th><th scope="col">Target</th><th scope="col">Realisasi</th><th scope="col">Tenggat</th></tr></thead><tbody>
                            @for($tw = 1; $tw <= 4; $tw++)
                                <tr><th scope="row">TW {{ $tw }}</th><td>{{ $targetTriwulan[$tw] ?? 0 }}</td><td>{{ $realisasiTriwulan[$tw] ?? 0 }}</td><td>{{ !empty($tenggat[$tw]) ? \Carbon\Carbon::parse($tenggat[$tw])->format('d/m/Y') : '-' }}</td></tr>
                            @endfor
                        </tbody></table>
                    </article>
                @empty
                    <div class="anggota-activity-index__empty">Belum ada target KM yang dibagikan kepada {{ $namaAnggota }} pada tahun {{ $tahun }}.</div>
                @endforelse
    </div>
</section>

{{-- ========================================================= --}}
{{-- DAFTAR AKTIVITAS KM NAMA ANGGOTA --}}
{{-- ========================================================= --}}
<section class="anggota-activity-index__section" aria-labelledby="activity-list-title">
    <header class="anggota-activity-index__section-header">
        <div>
            <h2 id="activity-list-title" class="anggota-activity-index__section-title">Daftar Aktivitas KM {{ $namaAnggota }}</h2>
            <p class="anggota-activity-index__section-description">
                Menampilkan aktivitas KM pada tahun {{ $tahun }}. Upload bukti aktivitas berupa PDF, PNG, JPG, atau JPEG.
                Jika berupa link, ubah terlebih dahulu menjadi format PDF.
            </p>
        </div>
    </header>
    <div class="table-responsive anggota-activity-index__scroll">
        <table class="table aktivitas-km-table anggota-activity-index__table">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Tahun</th>
                    <th scope="col">Kategori</th>
                    <th scope="col">Sub Kategori</th>
                    <th scope="col">Judul Aktivitas</th>
                    <th scope="col">Status</th>
                    <th scope="col">Tanggal Mulai</th>
                    <th scope="col">Tanggal Selesai</th>
                    <th scope="col">Ditambahkan di Triwulan</th>
                    <th scope="col">Bukti</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($aktivitas as $index => $item)
                    @php
                        $status = $item->status_progress ?? 'On Progress';
                        $statusVisualClass = match($status) {
                            'Accepted' => 'anggota-activity-index__status--success',
                            'Rejected' => 'anggota-activity-index__status--danger',
                            'Submitted', 'On Progress', 'Pending' => 'anggota-activity-index__status--warning',
                            default => 'anggota-activity-index__status--neutral',
                        };
                    @endphp
                    <tr>
                        <td class="anggota-activity-index__cell--number">{{ $index + 1 }}</td>
                        <td class="anggota-activity-index__cell--number">{{ !empty($item->tanggal_mulai) ? \Carbon\Carbon::parse($item->tanggal_mulai)->format('Y') : '-' }}</td>
                        <td class="anggota-activity-index__identity">{{ $item->kategori_km ?? '-' }}</td>
                        <td>{{ $item->sub_kategori_km ?? '-' }}</td>

                        <td class="activity-title">
                            <strong class="anggota-activity-index__identity">{{ $item->judul_aktivitas ?? '-' }}</strong>
                            @if(!empty($item->deskripsi_singkat))
                                <div class="anggota-activity-index__metadata">
                                    {{ \Illuminate\Support\Str::limit($item->deskripsi_singkat, 80) }}
                                </div>
                            @endif
                        </td>

                        <td>
                            <span class="anggota-activity-index__status {{ $statusVisualClass }}">
                                {{ $labelStatusAktivitas($status) }}
                            </span>

                            @if($status === 'Rejected' && !empty($item->catatan_verifikasi))
                                <div class="anggota-activity-index__metadata text-danger">
                                    {{ \Illuminate\Support\Str::limit($item->catatan_verifikasi, 90) }}
                                </div>
                            @endif
                        </td>

                        <td class="anggota-activity-index__cell--date">{{ !empty($item->tanggal_mulai) ? \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') : '-' }}</td>
                        <td class="anggota-activity-index__cell--date">{{ !empty($item->tanggal_selesai) ? \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') : '-' }}</td>
                        <td class="anggota-activity-index__cell--date">{{ $formatTriwulanDitambahkan($item->created_at ?? $item->tanggal_mulai ?? null) }}</td>

                        <td>
                            @if(!empty($item->bukti_pdf_path) || !empty($item->bukti_file_path))
                                <a href="/bukti-km/{{ $item->id_aktivitas }}/download" class="btn btn-outline-primary anggota-activity-index__action">
                                    <i class="bi bi-download me-1"></i> Download
                                </a>
                            @elseif(!empty($item->bukti_link))
                                <a href="{{ $item->bukti_link }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary anggota-activity-index__action">Link</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            <div class="anggota-activity-index__actions">
                                @if(in_array($status, ['Accepted', 'Rejected'], true))
                                    <a href="/anggota/aktivitas-km/{{ $item->id_aktivitas }}/detail" class="btn btn-outline-secondary anggota-activity-index__action">
                                        Detail
                                    </a>
                                @endif

                                @if($status === 'Submitted')
                                    <span class="anggota-activity-index__lock">
                                        <i class="bi bi-hourglass-split me-1"></i> Menunggu verifikasi Ketua Lab
                                    </span>
                                @elseif($status === 'Accepted')
                                    <span class="anggota-activity-index__lock">
                                        <i class="bi bi-lock-fill me-1"></i> Terkunci setelah disetujui
                                    </span>
                                @else
                                    <a href="/anggota/aktivitas-km/{{ $item->id_aktivitas }}/edit" class="btn btn-outline-primary anggota-activity-index__action">Edit</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="anggota-activity-index__empty">
                            Belum ada aktivitas KM pada tahun {{ $tahun }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

{{-- ========================================================= --}}
{{-- RIWAYAT REALISASI KM NAMA ANGGOTA --}}
{{-- ========================================================= --}}
<section id="riwayat-realisasi-km" class="anggota-activity-index__section" aria-labelledby="activity-history-title">
    <header class="anggota-activity-index__section-header">
        <div>
            <h2 id="activity-history-title" class="anggota-activity-index__section-title">Riwayat Realisasi KM {{ $namaAnggota }}</h2>
            <p class="anggota-activity-index__section-description">
                Riwayat aktivitas KM yang telah diinput pada tahun {{ $tahun }}, termasuk aktivitas yang masih diproses.
            </p>
        </div>

        <div class="anggota-activity-index__period">
            Total aktivitas: <strong>{{ $riwayatRealisasi->count() }}</strong>
        </div>
    </header>

    <div class="table-responsive anggota-activity-index__scroll">
        <table class="table history-activity-table anggota-activity-index__table">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Kategori</th>
                    <th scope="col">Sub Kategori</th>
                    <th scope="col">Judul Aktivitas</th>
                    <th scope="col">Status</th>
                    <th scope="col">Tanggal Mulai</th>
                    <th scope="col">Tanggal Selesai</th>
                    <th scope="col">Ditambahkan di Triwulan</th>
                    <th scope="col">Update Terakhir</th>
                </tr>
            </thead>

            <tbody>
                @forelse($riwayatRealisasi as $index => $item)
                    @php
                        $status = $item->status_progress ?? 'On Progress';
                        $statusVisualClass = match($status) {
                            'Accepted' => 'anggota-activity-index__status--success',
                            'Rejected' => 'anggota-activity-index__status--danger',
                            'Submitted', 'On Progress', 'Pending' => 'anggota-activity-index__status--warning',
                            default => 'anggota-activity-index__status--neutral',
                        };
                    @endphp
                    <tr>
                        <td class="anggota-activity-index__cell--number">{{ $index + 1 }}</td>
                        <td class="anggota-activity-index__identity">{{ $item->kategori_km ?? '-' }}</td>
                        <td>{{ $item->sub_kategori_km ?? '-' }}</td>

                        <td class="history-title">
                            <strong class="anggota-activity-index__identity">{{ $item->judul_aktivitas ?? '-' }}</strong>
                            @if(!empty($item->deskripsi_singkat))
                                <div class="anggota-activity-index__metadata">
                                    {{ \Illuminate\Support\Str::limit($item->deskripsi_singkat, 100) }}
                                </div>
                            @endif
                        </td>

                        <td>
                            <span class="anggota-activity-index__status {{ $statusVisualClass }}">
                                {{ $labelStatusAktivitas($status) }}
                            </span>
                        </td>

                        <td class="anggota-activity-index__cell--date">{{ !empty($item->tanggal_mulai) ? \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') : '-' }}</td>
                        <td class="anggota-activity-index__cell--date">{{ !empty($item->tanggal_selesai) ? \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') : '-' }}</td>
                        <td class="anggota-activity-index__cell--date">{{ $formatTriwulanDitambahkan($item->created_at ?? $item->tanggal_mulai ?? null) }}</td>
                        <td class="anggota-activity-index__cell--date">{{ !empty($item->updated_at) ? \Carbon\Carbon::parse($item->updated_at)->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="anggota-activity-index__empty">
                            Belum ada riwayat realisasi KM pada tahun {{ $tahun }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const periode = document.getElementById('periodeRingkasan');
        const triwulan = document.getElementById('triwulanRingkasan');
        const semester = document.getElementById('semesterRingkasan');

        if (!periode || !triwulan || !semester) {
            return;
        }

        function sinkronkanFilterPeriode() {
            triwulan.classList.toggle('d-none', periode.value !== 'triwulan');
            semester.classList.toggle('d-none', periode.value !== 'semester');
        }

        periode.addEventListener('change', sinkronkanFilterPeriode);
        sinkronkanFilterPeriode();
    });
</script>
@endsection
