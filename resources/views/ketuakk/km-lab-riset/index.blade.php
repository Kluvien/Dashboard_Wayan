@extends('layouts.app')

@section('title', 'KM Lab Riset')

@section('content')
@php
    $dataLab = collect($dataLab ?? []);

    $kategoriDefault = $kategoriDefault ?? [
        'Penelitian',
        'Publikasi',
        'Pengabdian',
        'Penunjang',
    ];

    $rekapKategori = collect($rekapKategori ?? []);
    $riwayatPenurunanKm = collect($riwayatPenurunanKm ?? []);
@endphp

<style>
    .km-table th,
    .history-table th {
        vertical-align: middle;
        font-size: 14px;
    }

    .km-table td,
    .history-table td {
        vertical-align: middle;
        font-size: 13px;
    }

    .group-header {
        background: #F3F6FB;
        text-align: center;
        font-weight: 800;
    }

    .ketuakk-lab-km {
        margin-bottom: 20px;
        overflow: hidden;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        background: #FFFFFF;
        color: #334155;
    }

    .ketuakk-lab-km__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        padding: 20px 22px 18px;
        border-bottom: 1px solid #EEF2F7;
    }

    .ketuakk-lab-km__heading {
        min-width: 0;
        max-width: 680px;
    }

    .ketuakk-lab-km__eyebrow {
        margin-bottom: 5px;
        color: #2563EB;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .ketuakk-lab-km__title {
        margin: 0;
        color: #0F172A;
        font-size: 23px;
        font-weight: 700;
        letter-spacing: -.02em;
        line-height: 1.25;
        text-wrap: balance;
    }

    .ketuakk-lab-km__description {
        max-width: 65ch;
        margin: 7px 0 0;
        color: #64748B;
        font-size: 13px;
        line-height: 1.55;
    }

    .ketuakk-lab-km__actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .ketuakk-lab-km__action {
    }

    .ketuakk-lab-km__context {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 18px;
        padding: 14px 22px;
        border-bottom: 1px solid #EEF2F7;
        background: #F8FAFC;
    }

    .ketuakk-lab-km__period .km-current-period-banner {
        margin: 0;
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .ketuakk-lab-km__period .km-current-period-icon {
        width: 34px;
        height: 34px;
        flex-basis: 34px;
        border-radius: 8px;
    }

    .ketuakk-lab-km__filter {
        display: flex;
        align-items: flex-end;
        gap: 8px;
    }

    .ketuakk-lab-km__filter-field {
        min-width: 124px;
    }

    .ketuakk-lab-km__filter-label {
        display: block;
        margin-bottom: 5px;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
    }

    .ketuakk-lab-km__filter-select {
        min-width: 124px;
    }

    .ketuakk-lab-km__summary {
        padding: 18px 22px 20px;
    }

    .ketuakk-lab-km__summary-heading {
        margin-bottom: 13px;
    }

    .ketuakk-lab-km__summary-title {
        margin: 0;
        color: #0F172A;
        font-size: 14px;
        font-weight: 700;
    }

    .ketuakk-lab-km__summary-description {
        margin: 3px 0 0;
        color: #64748B;
        font-size: 12px;
    }

    .ketuakk-lab-km__summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        background: #FFFFFF;
    }

    .ketuakk-lab-km__summary-item {
        min-width: 0;
        padding: 15px 16px 14px;
    }

    .ketuakk-lab-km__summary-item + .ketuakk-lab-km__summary-item {
        border-left: 1px solid #EEF2F7;
    }

    .ketuakk-lab-km__category {
        margin-bottom: 10px;
        color: #0F172A;
        font-size: 13px;
        font-weight: 700;
    }

    .ketuakk-lab-km__progress-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 10px;
        margin-bottom: 7px;
    }

    .ketuakk-lab-km__progress-label {
        color: #64748B;
        font-size: 11px;
        font-weight: 600;
    }

    .ketuakk-lab-km__progress-value {
        color: #0F172A;
        font-size: 19px;
        line-height: 1;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-lab-km__progress {
        height: 6px;
        overflow: hidden;
        border-radius: 999px;
        background: #E2E8F0;
    }

    .ketuakk-lab-km__progress-fill {
        height: 100%;
        border-radius: 999px;
        background: #2563EB;
    }

    .ketuakk-lab-km__progress-fill--complete {
        background: #15803D;
    }

    .ketuakk-lab-km__metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin: 13px 0 11px;
    }

    .ketuakk-lab-km__metric {
        min-width: 0;
    }

    .ketuakk-lab-km__metric-label {
        margin-bottom: 3px;
        color: #64748B;
        font-size: 10px;
        font-weight: 600;
        line-height: 1.3;
    }

    .ketuakk-lab-km__metric-value {
        color: #334155;
        font-size: 15px;
        font-weight: 700;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-lab-km__metric-value--complete {
        color: #15803D;
    }

    .ketuakk-lab-km__status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #475569;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.4;
    }

    .ketuakk-lab-km__status--progress {
        color: #2563EB;
    }

    .ketuakk-lab-km__status--complete {
        color: #15803D;
    }

    /*
    |--------------------------------------------------------------------------
    | Table Lab Riset
    |--------------------------------------------------------------------------
    */
    .ketuakk-lab-km__labs {
        margin-bottom: 20px;
        overflow: hidden;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        background: #FFFFFF;
    }

    .ketuakk-lab-km__labs-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        padding: 20px 22px;
        border-bottom: 1px solid #E2E8F0;
    }

    .ketuakk-lab-km__labs-title {
        margin: 0;
        color: #0F172A;
        font-size: 16px;
        font-weight: 700;
        line-height: 1.35;
    }

    .ketuakk-lab-km__labs-description {
        max-width: 72ch;
        margin: 4px 0 0;
        color: #64748B;
        font-size: 13px;
        line-height: 1.5;
    }

    .ketuakk-lab-km__labs-count {
        flex: 0 0 auto;
        color: #64748B;
        font-size: 12px;
    }

    .ketuakk-lab-km__labs-count strong {
        color: #0F172A;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-lab-km__labs-scroll {
        overflow-x: auto;
    }

    .ketuakk-lab-km__labs-table {
        width: 100%;
        margin: 0;
        border: 0 !important;
        border-radius: 0 !important;
        color: #334155;
    }

    .ketuakk-lab-km__labs-table thead th {
        padding: 11px 16px !important;
        border: 0 !important;
        border-bottom: 1px solid #CBD5E1 !important;
        background: #F8FAFC !important;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .035em;
        line-height: 1.35;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .ketuakk-lab-km__labs-table thead tr:first-child th[rowspan] {
        border-bottom-color: #CBD5E1 !important;
    }

    .ketuakk-lab-km__labs-table tbody tr {
        min-height: 56px;
    }

    .ketuakk-lab-km__labs-table tbody td {
        height: 56px;
        padding: 11px 16px !important;
        border: 0 !important;
        border-bottom: 1px solid #EEF2F7 !important;
        background: #FFFFFF;
        color: #334155;
        font-size: 13px;
        font-weight: 500;
        vertical-align: middle;
    }

    .ketuakk-lab-km__labs-table tbody tr:last-child td {
        border-bottom: 0 !important;
    }

    .ketuakk-lab-km__labs-table tbody tr:hover td {
        background: #F8FAFC;
    }

    .ketuakk-lab-km__labs-group {
        border-right: 1px solid #CBD5E1 !important;
        border-left: 1px solid #CBD5E1 !important;
        text-align: center;
    }

    .ketuakk-lab-km__labs-cell--index {
        min-width: 56px;
        text-align: center;
        white-space: nowrap;
    }

    .ketuakk-lab-km__labs-cell--identity {
        min-width: 190px;
        max-width: 280px;
        text-align: left;
    }

    .ketuakk-lab-km__labs-name {
        color: #0F172A;
        font-weight: 700;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .ketuakk-lab-km__labs-cell--number {
        text-align: right;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .ketuakk-lab-km__labs-cell--progress {
        min-width: 150px;
        text-align: left;
    }

    .ketuakk-lab-km__labs-progress {
        width: 100%;
        min-width: 100px;
        height: 6px;
        overflow: hidden;
        border-radius: 999px;
        background: #E2E8F0;
    }

    .ketuakk-lab-km__labs-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: #2563EB;
    }

    .ketuakk-lab-km__labs-progress-fill--complete {
        background: #15803D;
    }

    .ketuakk-lab-km__labs-progress-value {
        margin-top: 5px;
        color: #64748B;
        font-size: 11px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-lab-km__labs-cell--status,
    .ketuakk-lab-km__labs-cell--action {
        white-space: nowrap;
    }

    .ketuakk-lab-km__labs-status {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 5px;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .ketuakk-lab-km__labs-status--complete {
        color: #15803D;
        background: #DCFCE7;
    }

    .ketuakk-lab-km__labs-status--progress {
        color: #B45309;
        background: #FEF3C7;
    }

    .ketuakk-lab-km__labs-status--empty {
        color: #64748B;
        background: #F1F5F9;
    }

    .ketuakk-lab-km__labs-detail {
        padding: 5px 10px;
        border-color: #CBD5E1;
        color: #1D4ED8;
        font-size: 12px;
        font-weight: 600;
    }

    .ketuakk-lab-km__labs-detail:hover {
        border-color: #2563EB;
        background: #EFF6FF;
        color: #1D4ED8;
    }

    .ketuakk-lab-km__labs-detail:focus-visible {
        outline: 3px solid rgba(37, 99, 235, .22);
        outline-offset: 2px;
        box-shadow: none;
    }

    .ketuakk-lab-km__labs-empty {
        padding: 24px 16px;
        color: #64748B;
        font-size: 13px;
        line-height: 1.5;
        text-align: center;
    }

    /*
    |--------------------------------------------------------------------------
    | Riwayat Distribusi KM
    |--------------------------------------------------------------------------
    */
    .ketuakk-lab-km__history {
        margin-top: 20px;
        overflow: hidden;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        background: #FFFFFF;
    }

    .ketuakk-lab-km__history-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
        padding: 20px 22px;
        border-bottom: 1px solid #E2E8F0;
    }

    .ketuakk-lab-km__history-title {
        margin: 0;
        color: #0F172A;
        font-size: 16px;
        font-weight: 700;
        line-height: 1.35;
    }

    .ketuakk-lab-km__history-description {
        max-width: 72ch;
        margin: 4px 0 0;
        color: #64748B;
        font-size: 13px;
        line-height: 1.5;
    }

    .ketuakk-lab-km__history-count {
        flex: 0 0 auto;
        color: #64748B;
        font-size: 12px;
        white-space: nowrap;
    }

    .ketuakk-lab-km__history-count strong {
        color: #0F172A;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-lab-km__history-scroll {
        overflow-x: auto;
    }

    .ketuakk-lab-km__history-table {
        width: 100%;
        margin: 0;
        border: 0 !important;
        border-radius: 0 !important;
        color: #334155;
    }

    .ketuakk-lab-km__history-table thead th {
        padding: 11px 16px !important;
        border: 0 !important;
        border-bottom: 1px solid #CBD5E1 !important;
        background: #F8FAFC !important;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .035em;
        line-height: 1.35;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .ketuakk-lab-km__history-table tbody tr {
        min-height: 56px;
    }

    .ketuakk-lab-km__history-table tbody td {
        height: 56px;
        padding: 11px 16px !important;
        border: 0 !important;
        border-bottom: 1px solid #EEF2F7 !important;
        background: #FFFFFF;
        color: #334155;
        font-size: 13px;
        font-weight: 500;
        vertical-align: middle;
    }

    .ketuakk-lab-km__history-table tbody tr:last-child td {
        border-bottom: 0 !important;
    }

    .ketuakk-lab-km__history-table tbody tr:hover td {
        background: #F8FAFC;
    }

    .ketuakk-lab-km__history-cell--index {
        min-width: 56px;
        text-align: center;
        white-space: nowrap;
    }

    .ketuakk-lab-km__history-cell--time {
        min-width: 132px;
    }

    .ketuakk-lab-km__history-timestamp {
        display: inline-flex;
        flex-direction: column;
        gap: 2px;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-lab-km__history-date {
        color: #0F172A;
        font-size: 13px;
        font-weight: 600;
    }

    .ketuakk-lab-km__history-time {
        color: #64748B;
        font-size: 11px;
        font-weight: 500;
    }

    .ketuakk-lab-km__history-date i,
    .ketuakk-lab-km__history-time i {
        width: 13px;
        margin-right: 3px;
        color: #94A3B8;
        font-size: 11px;
    }

    .ketuakk-lab-km__history-cell--lab {
        min-width: 190px;
        max-width: 280px;
        text-align: left;
    }

    .ketuakk-lab-km__history-lab-name {
        color: #0F172A;
        font-weight: 700;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .ketuakk-lab-km__history-category {
        display: inline-flex;
        align-items: center;
        padding: 3px 7px;
        border: 1px solid #E2E8F0;
        border-radius: 4px;
        background: #F8FAFC;
        color: #475569;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.35;
    }

    .ketuakk-lab-km__history-subcategory {
        min-width: 170px;
        max-width: 250px;
        color: #334155;
        font-weight: 600;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .ketuakk-lab-km__history-notes {
        min-width: 180px;
        max-width: 280px;
        color: #64748B;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .ketuakk-lab-km__history-cell--number {
        color: #334155 !important;
        font-size: 13px;
        font-weight: 700 !important;
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .ketuakk-lab-km__history-cell--total {
        color: #0F172A;
    }

    .ketuakk-lab-km__history-cell--status {
        white-space: nowrap;
    }

    .ketuakk-lab-km__history-status {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .ketuakk-lab-km__history-status--active {
        color: #15803D;
        background: #DCFCE7;
    }

    .ketuakk-lab-km__history-status--inactive {
        color: #64748B;
        background: #F1F5F9;
    }

    .ketuakk-lab-km__history-empty {
        padding: 24px 16px;
        color: #64748B;
        font-size: 13px;
        line-height: 1.5;
        text-align: center;
    }

    @media (max-width: 1199.98px) {
        .ketuakk-lab-km__summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ketuakk-lab-km__summary-item:nth-child(3) {
            border-left: 0;
        }

        .ketuakk-lab-km__summary-item:nth-child(n + 3) {
            border-top: 1px solid #EEF2F7;
        }
    }

    @media (max-width: 767.98px) {
        .ketuakk-lab-km__header,
        .ketuakk-lab-km__context {
            grid-template-columns: 1fr;
        }

        .ketuakk-lab-km__header {
            display: block;
        }

        .ketuakk-lab-km__actions {
            justify-content: flex-start;
            margin-top: 16px;
        }

        .ketuakk-lab-km__context {
            display: grid;
        }

        .ketuakk-lab-km__filter {
            align-items: flex-end;
        }
    }

    @media (max-width: 575.98px) {
        .ketuakk-lab-km__header,
        .ketuakk-lab-km__context,
        .ketuakk-lab-km__summary {
            padding-right: 16px;
            padding-left: 16px;
        }

        .ketuakk-lab-km__summary-grid {
            grid-template-columns: 1fr;
        }

        .ketuakk-lab-km__summary-item + .ketuakk-lab-km__summary-item {
            border-top: 1px solid #EEF2F7;
            border-left: 0;
        }
    }
    .ketuakk-lab-km__category-list,.ketuakk-lab-km__summary-list,.ketuakk-lab-km__distribution-list{display:grid;gap:5px;margin:0}.ketuakk-lab-km__category-list div,.ketuakk-lab-km__summary-list div,.ketuakk-lab-km__distribution-list div{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px}.ketuakk-lab-km__category-list dt,.ketuakk-lab-km__summary-list dt,.ketuakk-lab-km__distribution-list dt{color:#5B6472;font-size:13px}.ketuakk-lab-km__category-list dd,.ketuakk-lab-km__summary-list dd,.ketuakk-lab-km__distribution-list dd{margin:0;color:#1F2937;font-size:14px;font-weight:700;font-variant-numeric:tabular-nums}.ketuakk-lab-km__labs-table,.ketuakk-lab-km__history-table{width:100%;table-layout:auto}.ketuakk-lab-km__labs-table td,.ketuakk-lab-km__history-table td{font-size:14px;line-height:1.5;overflow-wrap:anywhere}.ketuakk-lab-km__labs-cell--progress .ketuakk-lab-km__labs-status,.ketuakk-lab-km__labs-cell--progress .ketuakk-lab-km__labs-detail{display:inline-flex;margin-top:8px}.ketuakk-lab-km__labs-detail{min-height:40px;align-items:center}
</style>

@if(session('success'))
    <div class="alert alert-success rounded-4 mb-4">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger rounded-4 mb-4">
        {{ session('error') }}
    </div>
@endif

<section class="ketuakk-lab-km" aria-labelledby="ketuakk-lab-km-title">
    <div class="ketuakk-lab-km__header">
        <div class="ketuakk-lab-km__heading">
            <div class="ketuakk-lab-km__eyebrow">Kontrak Manajemen</div>
            <h1 id="ketuakk-lab-km-title" class="ketuakk-lab-km__title">
                KM Lab Riset Tahun {{ $tahun }}
            </h1>
            <p class="ketuakk-lab-km__description">
                Menampilkan seluruh Lab Riset beserta jumlah KM yang telah diberikan oleh Ketua KK.
            </p>
        </div>

        <div class="ketuakk-lab-km__actions">
            <a href="/ketuakk/dashboard" class="btn btn-outline-secondary ketuakk-lab-km__action">
                <i class="bi bi-arrow-left me-1"></i>
                Kembali
            </a>

            <a href="/ketuakk/km-lab-riset/create" class="btn btn-primary ketuakk-lab-km__action">
                <i class="bi bi-plus-lg me-1"></i>
                Distribusikan KM ke Lab
            </a>
        </div>
    </div>

    <div class="ketuakk-lab-km__context">
        <div class="ketuakk-lab-km__period">
            @include('partials.periode-saat-ini')
        </div>

        <form method="GET" action="/ketuakk/km-lab-riset" class="ketuakk-lab-km__filter">
            <div class="ketuakk-lab-km__filter-field">
                <label for="ketuakk-lab-km-tahun" class="ketuakk-lab-km__filter-label">
                    Tahun KM
                </label>
                <select
                    id="ketuakk-lab-km-tahun"
                    name="tahun"
                    class="form-select form-select-sm ketuakk-lab-km__filter-select">
                    @foreach($tahunOptions ?? [$tahun] as $itemTahun)
                        <option
                            value="{{ $itemTahun }}"
                            {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                            {{ $itemTahun }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-sm btn-outline-primary">
                Filter
            </button>
        </form>
    </div>

    <div class="ketuakk-lab-km__summary">
        <div class="ketuakk-lab-km__summary-heading">
            <h2 class="ketuakk-lab-km__summary-title">Ringkasan target per kategori</h2>
            <p class="ketuakk-lab-km__summary-description">
                Perbandingan target Ketua KK dan KM yang sudah didistribusikan ke Lab Riset.
            </p>
        </div>

        <div class="ketuakk-lab-km__summary-grid">
            @foreach($kategoriDefault as $kategori)
                @php
                    $rekap = $rekapKategori->firstWhere('kategori', $kategori);

                    $targetKk = (int) data_get($rekap, 'total_km_kk', 0);
                    $totalTurun = (int) data_get($rekap, 'total_turun', 0);
                    $sisa = (int) data_get($rekap, 'sisa', max($targetKk - $totalTurun, 0));

                    $persentaseSebenarnya = $targetKk > 0
                        ? round(($totalTurun / $targetKk) * 100)
                        : 0;
                    $persentaseTurun = min($persentaseSebenarnya, 100);
                    $targetSelesai = $targetKk > 0 && $totalTurun >= $targetKk;
                @endphp

                <article class="ketuakk-lab-km__summary-item">
                    <h3 class="ketuakk-lab-km__category">{{ $kategori }}</h3>

                    <div class="ketuakk-lab-km__progress-row">
                        <span class="ketuakk-lab-km__progress-label">
                        Progress Distribusi KM
                        </span>
                        <span class="ketuakk-lab-km__progress-value">
                            {{ $persentaseSebenarnya }}%
                        </span>
                    </div>

                    <div
                        class="ketuakk-lab-km__progress"
                        role="progressbar"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-valuenow="{{ $persentaseTurun }}"
                        aria-label="Progress distribusi KM kategori {{ $kategori }}: {{ $persentaseSebenarnya }} persen">
                        <div
                            class="ketuakk-lab-km__progress-fill {{ $targetSelesai ? 'ketuakk-lab-km__progress-fill--complete' : '' }}"
                            style="width: {{ $persentaseTurun }}%;">
                        </div>
                    </div>

                    <div class="ketuakk-lab-km__metrics">
                        <div class="ketuakk-lab-km__metric">
                            <div class="ketuakk-lab-km__metric-label">Target KK</div>
                            <div class="ketuakk-lab-km__metric-value">
                                {{ number_format($targetKk, 0, ',', '.') }}
                            </div>
                        </div>

                        <div class="ketuakk-lab-km__metric">
                            <div class="ketuakk-lab-km__metric-label">Didistribusikan</div>
                            <div class="ketuakk-lab-km__metric-value">
                                {{ number_format($totalTurun, 0, ',', '.') }}
                            </div>
                        </div>

                        <div class="ketuakk-lab-km__metric">
                            <div class="ketuakk-lab-km__metric-label">Sisa</div>
                            <div class="ketuakk-lab-km__metric-value {{ $targetSelesai ? 'ketuakk-lab-km__metric-value--complete' : '' }}">
                                {{ number_format($sisa, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    @if($targetKk <= 0)
                        <div class="ketuakk-lab-km__status">
                            <i class="bi bi-dash-circle" aria-hidden="true"></i>
                            Belum ada target
                        </div>
                    @elseif($targetSelesai)
                        <div class="ketuakk-lab-km__status ketuakk-lab-km__status--complete">
                            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                            Seluruh target sudah didistribusikan
                        </div>
                    @else
                        <div class="ketuakk-lab-km__status ketuakk-lab-km__status--progress">
                            <i class="bi bi-clock" aria-hidden="true"></i>
                            {{ number_format($sisa, 0, ',', '.') }} KM belum didistribusikan
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>

@php
    $jumlahKolomLab = count($kategoriDefault) + 8;
@endphp

<section class="ketuakk-lab-km__labs" aria-labelledby="ketuakk-lab-km-labs-title">
    <div class="ketuakk-lab-km__labs-header">
        <div>
            <h2 id="ketuakk-lab-km-labs-title" class="ketuakk-lab-km__labs-title">
                Daftar Lab Riset
            </h2>
            <p class="ketuakk-lab-km__labs-description">
                Ketua KK dapat melihat distribusi KM ke setiap Lab Riset dan membuka detail pembagian KM anggota.
            </p>
        </div>

        <div class="ketuakk-lab-km__labs-count">
            Total Lab: <strong>{{ $dataLab->count() }}</strong>
        </div>
    </div>

    <div class="table-responsive ketuakk-lab-km__labs-scroll">
        <table class="table align-middle ketuakk-lab-km__labs-table">
            <thead><tr><th>No</th><th>Lab Riset</th><th>Distribusi per Kategori</th><th>Ringkasan</th><th>Progress/Aksi</th></tr></thead>

            <tbody>
                @forelse($dataLab as $index => $lab)
                    @php
                        $totalTurun = (int) data_get($lab, 'total_turun', 0);
                        $totalAssign = (int) data_get($lab, 'total_assign', 0);
                        $sisaKm = (int) data_get($lab, 'sisa_km', 0);
                        $persentase = (int) data_get($lab, 'persentase', 0);
                        $lebarProgress = max(0, min($persentase, 100));
                        $status = data_get($lab, 'status', 'Belum Ada KM');

                        $statusClass = match ($status) {
                            'Selesai' => 'ketuakk-lab-km__labs-status--complete',
                            'Belum Selesai' => 'ketuakk-lab-km__labs-status--progress',
                            default => 'ketuakk-lab-km__labs-status--empty',
                        };
                    @endphp

                    <tr>
                        <td class="ketuakk-lab-km__labs-cell--index">{{ $index + 1 }}</td>

                        <td class="ketuakk-lab-km__labs-cell--identity">
                            <div class="ketuakk-lab-km__labs-name">
                                {{ data_get($lab, 'nama_lab', '-') }}
                            </div>
                        </td>

                        <td><dl class="ketuakk-lab-km__category-list">@foreach($kategoriDefault as $kategori)<div><dt>{{ $kategori }}</dt><dd>{{ data_get($lab,'turun_per_kategori.'.$kategori,0) }}</dd></div>@endforeach</dl></td>
                        <td><dl class="ketuakk-lab-km__summary-list"><div><dt>Total didistribusikan</dt><dd>{{ $totalTurun }}</dd></div><div><dt>Sudah dibagi</dt><dd>{{ $totalAssign }}</dd></div><div><dt>Sisa KM</dt><dd>{{ $sisaKm }}</dd></div></dl></td>
                        <td class="ketuakk-lab-km__labs-cell--progress">
                            <div
                                class="ketuakk-lab-km__labs-progress"
                                role="progressbar"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                aria-valuenow="{{ $lebarProgress }}"
                                aria-label="Progress pembagian KM {{ data_get($lab, 'nama_lab', '-') }}: {{ $persentase }} persen">
                                <div
                                    class="ketuakk-lab-km__labs-progress-fill {{ $status === 'Selesai' ? 'ketuakk-lab-km__labs-progress-fill--complete' : '' }}"
                                    style="width: {{ $lebarProgress }}%;">
                                </div>
                            </div>

                            <div class="ketuakk-lab-km__labs-progress-value">
                                {{ $persentase }}%
                            </div>
                            <span class="ketuakk-lab-km__labs-status {{ $statusClass }}">
                                {{ $status }}
                            </span>
                            <a
                                href="/ketuakk/km-lab-riset/{{ data_get($lab, 'id_lab') }}?tahun={{ $tahun }}"
                                class="btn btn-sm btn-outline-primary ketuakk-lab-km__labs-detail">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="ketuakk-lab-km__labs-empty">
                                <strong>Belum ada Lab Riset.</strong>
                                Tambahkan data Lab Riset terlebih dahulu pada menu Data Master.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="ketuakk-lab-km__history" aria-labelledby="ketuakk-lab-km-history-title">
    <div class="ketuakk-lab-km__history-header">
        <div>
            <h2 id="ketuakk-lab-km-history-title" class="ketuakk-lab-km__history-title">
                Riwayat Distribusi KM ke Lab Riset
            </h2>
            <p class="ketuakk-lab-km__history-description">
                Riwayat target KM yang didistribusikan Ketua KK kepada Lab Riset pada tahun {{ $tahun }}.
            </p>
        </div>

        <div class="ketuakk-lab-km__history-count">
            Total Riwayat: <strong>{{ $riwayatPenurunanKm->count() }}</strong>
        </div>
    </div>

    <div class="table-responsive ketuakk-lab-km__history-scroll">
        <table class="table align-middle ketuakk-lab-km__history-table">
            <thead>
                <tr><th>No</th><th>Waktu Distribusi</th><th>Lab Riset</th><th>Detail KM</th><th>Jumlah Distribusi</th></tr>
            </thead>

            <tbody>
                @forelse($riwayatPenurunanKm as $index => $riwayat)
                    @php
                        $waktuPenurunan = !empty($riwayat->waktu_penurunan)
                            ? \Carbon\Carbon::parse($riwayat->waktu_penurunan)
                            : null;

                        $statusKm = $riwayat->status_km ?? 'Aktif';

                        $statusKmClass = $statusKm === 'Aktif'
                            ? 'ketuakk-lab-km__history-status--active'
                            : 'ketuakk-lab-km__history-status--inactive';
                    @endphp

                    <tr>
                        <td class="ketuakk-lab-km__history-cell--index">{{ $index + 1 }}</td>

                        <td class="ketuakk-lab-km__history-cell--time">
                            @if($waktuPenurunan)
                                <div class="ketuakk-lab-km__history-timestamp">
                                    <span class="ketuakk-lab-km__history-date">
                                        <i class="bi bi-calendar-event" aria-hidden="true"></i>
                                        {{ $waktuPenurunan->format('d/m/Y') }}
                                    </span>

                                    <span class="ketuakk-lab-km__history-time">
                                        <i class="bi bi-clock" aria-hidden="true"></i>
                                        {{ $waktuPenurunan->format('H:i') }}
                                    </span>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td class="ketuakk-lab-km__history-cell--lab">
                            <div class="ketuakk-lab-km__history-lab-name">
                                {{ $riwayat->nama_lab ?? '-' }}
                            </div>
                        </td>

                        <td><strong>{{ $riwayat->kategori_km ?? '-' }}</strong><div class="ketuakk-lab-km__history-subcategory">{{ $riwayat->sub_kategori_km ?? '-' }}</div><div class="ketuakk-lab-km__history-notes">{{ $riwayat->keterangan ?? '-' }}</div></td>
                        <td><dl class="ketuakk-lab-km__distribution-list">@foreach([1,2,3,4] as $tw)<div><dt>TW{{ $tw }}</dt><dd>{{ (int)($riwayat->{'triwulan_'.$tw} ?? 0) }}</dd></div>@endforeach<div><dt>Total didistribusikan</dt><dd>{{ (int)($riwayat->jumlah_km ?? 0) }}</dd></div></dl>
                            <span class="ketuakk-lab-km__history-status {{ $statusKmClass }}">
                                {{ $statusKm }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="ketuakk-lab-km__history-empty">
                                <strong>Belum ada riwayat distribusi KM.</strong>
                                Riwayat akan muncul setelah Ketua KK mendistribusikan KM kepada Lab Riset.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
