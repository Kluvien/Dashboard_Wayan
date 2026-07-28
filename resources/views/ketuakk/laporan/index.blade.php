@extends('layouts.app')

@section('title', 'Laporan Ketua KK')

@section('content')
@php
    $filters = $filters ?? [];
    $summary = $summary ?? [];
    $rekapKategori = collect($rekapKategori ?? []);
    $detailTargetKategori = collect($detailTargetKategori ?? []);
    $laporanRows = collect($laporanRows ?? []);
    $pdfAktivitasRows = collect($pdfAktivitasRows ?? []);
@endphp

<style>
    .report-filter-card {
        border: 1px solid #DDE5F0;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }

    .report-filter-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 16px;
    }

    .filter-col-3 { grid-column: span 3; }
    .filter-col-4 { grid-column: span 4; }
    .filter-col-6 { grid-column: span 6; }
    .filter-col-12 { grid-column: span 12; }

    .report-filter-section {
        background: #F8FAFD;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 16px;
    }

    .report-filter-section-title {
        font-size: 13px;
        font-weight: 800;
        color: #1E3A8A;
        text-transform: uppercase;
        letter-spacing: .3px;
        margin-bottom: 12px;
    }

    .report-filter-help {
        font-size: 12px;
        color: #64748B;
        margin-top: 6px;
    }

    .download-panel {
        background: linear-gradient(135deg, #EFF6FF 0%, #F8FAFC 100%);
        border: 1px solid #BFDBFE;
        border-radius: 14px;
        padding: 16px;
    }

    .download-panel-title {
        font-size: 15px;
        font-weight: 800;
        color: #1E3A8A;
        margin-bottom: 4px;
    }

    .download-panel-text {
        font-size: 12px;
        color: #475569;
        margin-bottom: 12px;
    }

    .report-table th {
        white-space: nowrap;
        vertical-align: middle;
        font-size: 13px;
    }

    .report-table td {
        vertical-align: middle;
        font-size: 13px;
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

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-success {
        color: #166534;
        background: #DCFCE7;
    }

    .status-warning {
        color: #92400E;
        background: #FEF3C7;
    }

    .detail-category-card {
        border: 1px solid #DDE5F0;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 16px;
        background: #FFFFFF;
    }

    .detail-category-header {
        padding: 14px 16px;
        background: linear-gradient(135deg, #EFF6FF 0%, #F8FAFC 100%);
        border-bottom: 1px solid #DDE5F0;
    }

    .detail-category-title {
        font-size: 16px;
        font-weight: 800;
        color: #1E3A8A;
        margin: 0;
    }

    .detail-category-meta {
        margin-top: 4px;
        color: #64748B;
        font-size: 12px;
    }

    .detail-target-table th,
    .detail-target-table td {
        font-size: 12px;
        white-space: nowrap;
        vertical-align: middle;
    }

    .detail-target-table td.detail-description {
        white-space: normal;
        min-width: 210px;
    }

    .detail-target-table .group-heading {
        background: #EFF6FF;
        color: #1E3A8A;
        text-align: center;
        font-weight: 800;
        border-bottom: 1px solid #BFDBFE;
    }

    .period-table th,
    .period-table td {
        font-size: 12px;
        vertical-align: top;
    }

    .period-chip {
        display: inline-block;
        padding: 5px 8px;
        border-radius: 8px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        color: #334155;
        white-space: nowrap;
        font-size: 11px;
        font-weight: 700;
    }

    @media (max-width: 992px) {
        .filter-col-3,
        .filter-col-4,
        .filter-col-6,
        .filter-col-12 {
            grid-column: span 12;
        }
    }

    .ketuakk-report {
        margin-bottom: 24px;
        overflow: hidden;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        background: #FFFFFF;
    }

    .ketuakk-report__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        padding: 20px 22px;
        border-bottom: 1px solid #E2E8F0;
    }

    .ketuakk-report__eyebrow {
        margin-bottom: 5px;
        color: #2563EB;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .ketuakk-report__title {
        margin: 0;
        color: #0F172A;
        font-size: 22px;
        font-weight: 700;
        line-height: 1.25;
        letter-spacing: -.02em;
    }

    .ketuakk-report__description {
        max-width: 720px;
        margin: 6px 0 0;
        color: #64748B;
        font-size: 13px;
        line-height: 1.6;
    }

    .ketuakk-report__period {
        margin-top: 8px;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
    }

    .ketuakk-report__button {
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 1px solid transparent;
        border-radius: 8px;
        padding: 0 13px;
        background: #FFFFFF;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        text-decoration: none;
        white-space: nowrap;
    }

    .ketuakk-report__button--back {
        border-color: #CBD5E1;
        color: #334155;
    }

    .ketuakk-report__button--back:hover {
        border-color: #94A3B8;
        background: #F8FAFC;
        color: #0F172A;
    }

    .ketuakk-report__button--primary {
        min-width: 142px;
        border-color: #2563EB;
        background: #2563EB;
        color: #FFFFFF;
    }

    .ketuakk-report__button--primary:hover {
        border-color: #1D4ED8;
        background: #1D4ED8;
        color: #FFFFFF;
    }

    .ketuakk-report__button--export {
        border-color: #CBD5E1;
        color: #334155;
    }

    .ketuakk-report__button--export:hover {
        border-color: #93C5FD;
        background: #EFF6FF;
        color: #1D4ED8;
    }

    .ketuakk-report__button:focus-visible {
        outline: 3px solid rgba(37, 99, 235, .22);
        outline-offset: 2px;
    }

    .ketuakk-report__validation {
        margin: 16px 22px 0;
        padding: 12px 14px;
        border: 1px solid #FECACA;
        border-radius: 8px;
        color: #991B1B;
        background: #FEF2F2;
        font-size: 13px;
    }

    .ketuakk-report__form {
        padding: 0 22px 20px;
    }

    .ketuakk-report__filter-group {
        display: grid;
        grid-template-columns: minmax(190px, .9fr) minmax(0, 2.1fr);
        gap: 20px;
        padding: 18px 0;
        border-bottom: 1px solid #EEF2F7;
    }

    .ketuakk-report__filter-heading {
        color: #0F172A;
        font-size: 13px;
        font-weight: 700;
    }

    .ketuakk-report__filter-intro {
        margin: 4px 0 0;
        color: #64748B;
        font-size: 12px;
        line-height: 1.5;
    }

    .ketuakk-report__fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        align-items: start;
    }

    .ketuakk-report__field--full {
        grid-column: 1 / -1;
    }

    .ketuakk-report__field label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
    }

    .ketuakk-report__field .form-control,
    .ketuakk-report__field .form-select {
        min-height: 40px;
        border-color: #CBD5E1;
        border-radius: 8px;
        color: #334155;
        background-color: #FFFFFF;
        font-size: 13px;
        font-weight: 500;
    }

    .ketuakk-report__field .form-control:focus,
    .ketuakk-report__field .form-select:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .14);
    }

    .ketuakk-report__help {
        margin: 6px 0 0;
        color: #64748B;
        font-size: 12px;
        line-height: 1.5;
    }

    .ketuakk-report__conditional {
        display: none;
    }

    .ketuakk-report__member-select {
        min-height: 142px;
    }

    .ketuakk-report__actions {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        padding-top: 18px;
    }

    .ketuakk-report__export {
        min-width: 0;
        flex: 1;
        padding-left: 18px;
        border-left: 1px solid #E2E8F0;
    }

    .ketuakk-report__export-title {
        color: #0F172A;
        font-size: 13px;
        font-weight: 700;
    }

    .ketuakk-report__export-description {
        max-width: 760px;
        margin: 3px 0 10px;
        color: #64748B;
        font-size: 12px;
        line-height: 1.5;
    }

    .ketuakk-report__export-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .ketuakk-report__summary {
        margin-bottom: 24px;
        overflow: hidden;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        background: #FFFFFF;
    }

    .ketuakk-report__summary-header {
        padding: 16px 20px;
        border-bottom: 1px solid #E2E8F0;
        background: #F8FAFC;
    }

    .ketuakk-report__summary-title {
        margin: 0;
        color: #0F172A;
        font-size: 14px;
        font-weight: 700;
    }

    .ketuakk-report__summary-description {
        margin: 3px 0 0;
        color: #64748B;
        font-size: 12px;
        line-height: 1.5;
    }

    .ketuakk-report__metric-group + .ketuakk-report__metric-group {
        border-top: 1px solid #E2E8F0;
    }

    .ketuakk-report__metric-group-title {
        padding: 11px 18px 0;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .ketuakk-report__metrics {
        display: grid;
    }

    .ketuakk-report__metrics--scope {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .ketuakk-report__metrics--achievement {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .ketuakk-report__metric {
        min-width: 0;
        padding: 14px 18px 16px;
        border-right: 1px solid #EEF2F7;
    }

    .ketuakk-report__metric:last-child {
        border-right: 0;
    }

    .ketuakk-report__metric-label {
        display: block;
        color: #64748B;
        font-size: 11px;
        font-weight: 600;
    }

    .ketuakk-report__metric-value {
        display: block;
        margin-top: 5px;
        color: #0F172A;
        font-size: 22px;
        font-weight: 700;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-report__summary-progress {
        padding: 0 18px 16px;
    }

    .ketuakk-report__summary-progress-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 7px;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
    }

    .ketuakk-report__progress-track {
        width: 100%;
        height: 6px;
        overflow: hidden;
        border-radius: 999px;
        background: #E2E8F0;
    }

    .ketuakk-report__progress-fill {
        height: 100%;
        border-radius: inherit;
        background: #2563EB;
    }

    .ketuakk-report__section {
        margin-bottom: 24px;
        overflow: hidden;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        background: #FFFFFF;
    }

    .ketuakk-report__section-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 16px 20px;
        border-bottom: 1px solid #E2E8F0;
        background: #F8FAFC;
    }

    .ketuakk-report__section-title {
        margin: 0;
        color: #0F172A;
        font-size: 15px;
        font-weight: 700;
    }

    .ketuakk-report__section-description,
    .ketuakk-report__section-meta {
        margin: 3px 0 0;
        color: #64748B;
        font-size: 12px;
        line-height: 1.5;
    }

    .ketuakk-report__period-label {
        color: #475569;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .ketuakk-report__scroll {
        overflow-x: auto;
    }

    .ketuakk-report__table {
        width: 100%;
        margin: 0;
        border: 0 !important;
        border-radius: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .ketuakk-report__table th,
    .ketuakk-report__table td {
        min-height: 56px;
        padding: 11px 16px !important;
        border: 0 !important;
        border-bottom: 1px solid #EEF2F7 !important;
        color: #334155;
        background: #FFFFFF;
        font-size: 13px;
        font-weight: 500;
        vertical-align: middle;
    }

    .ketuakk-report__table thead th {
        color: #475569;
        background: #F8FAFC;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .ketuakk-report__table tbody tr:hover > td,
    .ketuakk-report__table tbody tr:hover > th {
        background: #F8FAFC;
    }

    .ketuakk-report__table tbody tr:last-child > td,
    .ketuakk-report__table tbody tr:last-child > th {
        border-bottom: 0 !important;
    }

    .ketuakk-report__cell--index {
        text-align: center;
        white-space: nowrap;
    }

    .ketuakk-report__cell--identity {
        min-width: 180px;
        color: #0F172A !important;
        font-weight: 700 !important;
        text-align: left;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .ketuakk-report__cell--description {
        min-width: 220px;
        text-align: left;
        white-space: normal !important;
        overflow-wrap: anywhere;
    }

    .ketuakk-report__cell--number {
        text-align: right;
        white-space: nowrap;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-report__cell--date {
        text-align: center;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-report__cell--status,
    .ketuakk-report__cell--action {
        white-space: nowrap;
    }

    .ketuakk-report__group-heading {
        border-right: 1px solid #CBD5E1 !important;
        border-left: 1px solid #CBD5E1 !important;
        text-align: center;
    }

    .ketuakk-report__group-start { border-left: 1px solid #CBD5E1 !important; }
    .ketuakk-report__group-end { border-right: 1px solid #CBD5E1 !important; }

    .ketuakk-report__table-progress {
        min-width: 170px;
    }

    .ketuakk-report__table-progress-value {
        margin-top: 4px;
        color: #64748B;
        font-size: 11px;
        font-weight: 600;
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-report__status {
        display: inline-block;
        font-size: 12px;
        font-weight: 700;
    }

    .ketuakk-report__status--success { color: #15803D; }
    .ketuakk-report__status--warning { color: #B45309; }
    .ketuakk-report__status--neutral { color: #64748B; }

    .ketuakk-report__empty {
        padding: 24px 16px !important;
        color: #64748B !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        text-align: center;
    }

    .ketuakk-report__category + .ketuakk-report__category {
        border-top: 1px solid #CBD5E1;
    }

    .ketuakk-report__category-header {
        padding: 14px 18px;
        border-bottom: 1px solid #E2E8F0;
        background: #F8FAFC;
    }

    .ketuakk-report__category-title {
        margin: 0;
        color: #0F172A;
        font-size: 14px;
        font-weight: 700;
    }

    .ketuakk-report__category-meta {
        margin-top: 3px;
        color: #64748B;
        font-size: 12px;
    }

    .ketuakk-report__deadline {
        border-top: 1px solid #E2E8F0;
    }

    .ketuakk-report__deadline-title {
        padding: 12px 18px;
        color: #334155;
        background: #F8FAFC;
        font-size: 12px;
        font-weight: 700;
    }

    .ketuakk-report__date-range {
        color: #334155;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-report__date-range--empty { color: #64748B; }

    .ketuakk-report__evidence {
        display: inline-flex;
        min-height: 32px;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        border: 1px solid #93C5FD;
        border-radius: 7px;
        color: #1D4ED8;
        background: #FFFFFF;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
    }

    .ketuakk-report__evidence:hover {
        background: #EFF6FF;
        color: #1D4ED8;
    }

    .ketuakk-report__evidence:focus-visible {
        outline: 3px solid rgba(37, 99, 235, .22);
        outline-offset: 2px;
    }

    .ketuakk-report__table--detail td:nth-child(n + 4):nth-child(-n + 17),
    .ketuakk-report__table--scope td:nth-child(n + 4):nth-child(-n + 6) {
        color: #334155 !important;
        text-align: right;
        white-space: nowrap;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-report__table--detail td:first-child,
    .ketuakk-report__table--deadline td:first-child,
    .ketuakk-report__table--scope td:first-child,
    .ketuakk-report__table--activity td:first-child {
        text-align: center;
    }

    .ketuakk-report__table--deadline td:nth-child(n + 3),
    .ketuakk-report__table--activity td:nth-child(5),
    .ketuakk-report__table--activity td:nth-child(6) {
        text-align: center;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-report__table--detail td:nth-child(2),
    .ketuakk-report__table--scope td:nth-child(2),
    .ketuakk-report__table--activity td:nth-child(2),
    .ketuakk-report__table--activity td:nth-child(4) {
        color: #0F172A;
        font-weight: 700;
    }

    .ketuakk-report__table--detail td:nth-child(2),
    .ketuakk-report__table--detail td:nth-child(3),
    .ketuakk-report__table--scope td:nth-child(2),
    .ketuakk-report__table--scope td:nth-child(3),
    .ketuakk-report__table--activity td:nth-child(2),
    .ketuakk-report__table--activity td:nth-child(3),
    .ketuakk-report__table--activity td:nth-child(4) {
        white-space: normal;
        overflow-wrap: anywhere;
    }

    @media (max-width: 900px) {
        .ketuakk-report__filter-group {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .ketuakk-report__actions {
            flex-direction: column;
        }

        .ketuakk-report__export {
            width: 100%;
            padding: 16px 0 0;
            border-top: 1px solid #E2E8F0;
            border-left: 0;
        }

        .ketuakk-report__metrics--achievement {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ketuakk-report__metrics--achievement .ketuakk-report__metric:nth-child(2) {
            border-right: 0;
        }

        .ketuakk-report__metrics--achievement .ketuakk-report__metric:nth-child(-n + 2) {
            border-bottom: 1px solid #EEF2F7;
        }
    }

    @media (max-width: 576px) {
        .ketuakk-report__header {
            padding: 18px 16px;
        }

        .ketuakk-report__form {
            padding: 0 16px 18px;
        }

        .ketuakk-report__validation {
            margin-right: 16px;
            margin-left: 16px;
        }

        .ketuakk-report__fields {
            grid-template-columns: 1fr;
        }

        .ketuakk-report__field--full {
            grid-column: auto;
        }

        .ketuakk-report__button,
        .ketuakk-report__export-buttons {
            width: 100%;
        }

        .ketuakk-report__export-buttons {
            flex-direction: column;
        }

        .ketuakk-report__metrics--scope,
        .ketuakk-report__metrics--achievement {
            grid-template-columns: 1fr;
        }

        .ketuakk-report__metric,
        .ketuakk-report__metrics--achievement .ketuakk-report__metric:nth-child(2) {
            border-right: 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .ketuakk-report__metric:last-child {
            border-bottom: 0;
        }
    }
</style>

<section class="ketuakk-report" aria-labelledby="ketuakk-report-title">
    <header class="ketuakk-report__header">
        <div>
            <div class="ketuakk-report__eyebrow">Laporan Ketua KK</div>
            <h1 id="ketuakk-report-title" class="ketuakk-report__title">Pusat Laporan Kontrak Manajemen</h1>
            <p class="ketuakk-report__description">
                Atur ruang lingkup dan periode untuk menampilkan atau mengunduh laporan kontrak manajemen.
            </p>
            <div class="ketuakk-report__period">
                Periode aktif: {{ $filters['label_periode'] ?? '-' }}
            </div>
        </div>

        <a href="/ketuakk/dashboard" class="ketuakk-report__button ketuakk-report__button--back">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </a>
    </header>

    @if($errors->any())
        <div class="ketuakk-report__validation" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="reportFilterForm" method="GET" action="/ketuakk/laporan" class="ketuakk-report__form">
        <section class="ketuakk-report__filter-group" aria-labelledby="ketuakk-report-scope-title">
            <div>
                <h2 id="ketuakk-report-scope-title" class="ketuakk-report__filter-heading">Ruang lingkup laporan</h2>
                <p class="ketuakk-report__filter-intro">
                    Pilihan ruang lingkup menentukan fokus laporan PDF.
                </p>
            </div>

            <div class="ketuakk-report__fields">
                <div class="ketuakk-report__field ketuakk-report__field--full">
                    <label for="jenis_laporan">Jenis Laporan</label>
                    <select name="jenis_laporan" id="jenis_laporan" class="form-select">
                        <option value="kk" {{ ($filters['jenis_laporan'] ?? 'kk') === 'kk' ? 'selected' : '' }}>
                            Seluruh KK
                        </option>
                        <option value="lab_semua" {{ ($filters['jenis_laporan'] ?? '') === 'lab_semua' ? 'selected' : '' }}>
                            Seluruh Lab Riset
                        </option>
                        <option value="lab_satu" {{ ($filters['jenis_laporan'] ?? '') === 'lab_satu' ? 'selected' : '' }}>
                            Per Lab Riset
                        </option>
                        <option value="anggota_semua" {{ ($filters['jenis_laporan'] ?? '') === 'anggota_semua' ? 'selected' : '' }}>
                            Seluruh Anggota KK
                        </option>
                        <option value="anggota_satu" {{ ($filters['jenis_laporan'] ?? '') === 'anggota_satu' ? 'selected' : '' }}>
                            Per Anggota
                        </option>
                    </select>
                </div>

                <div id="labSelectorWrap" class="ketuakk-report__field ketuakk-report__field--full ketuakk-report__conditional">
                    <label for="id_lab">Pilih Lab Riset</label>
                    <select name="id_lab" id="id_lab" class="form-select">
                        <option value="">-- Pilih Lab Riset --</option>
                        @foreach($labOptions ?? [] as $lab)
                            <option
                                value="{{ $lab->id_lab }}"
                                {{ (string) ($filters['id_lab'] ?? '') === (string) $lab->id_lab ? 'selected' : '' }}>
                                {{ $lab->nama_lab }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="anggotaSelectorWrap" class="ketuakk-report__field ketuakk-report__field--full ketuakk-report__conditional">
                    <label for="searchAnggota">Cari Anggota</label>
                    <input
                        type="search"
                        id="searchAnggota"
                        class="form-control mb-2"
                        placeholder="Cari nama, NIDN, atau lab riset...">

                    <label for="id_user">Pilih Anggota</label>
                    <select name="id_user" id="id_user" class="form-select ketuakk-report__member-select" size="6">
                        <option value="">-- Pilih Anggota --</option>
                        @foreach($anggotaOptions ?? [] as $anggota)
                            @php
                                $searchText = strtolower(
                                    ($anggota->nama_dosen ?? $anggota->username) . ' ' .
                                    ($anggota->nidn ?? '') . ' ' .
                                    ($anggota->nama_lab ?? '')
                                );
                            @endphp
                            <option
                                value="{{ $anggota->id_user }}"
                                data-search="{{ $searchText }}"
                                {{ (string) ($filters['id_user'] ?? '') === (string) $anggota->id_user ? 'selected' : '' }}>
                                {{ $anggota->nama_dosen ?? $anggota->username }} — {{ $anggota->nama_lab ?? '-' }}
                            </option>
                        @endforeach
                    </select>

                    <p class="ketuakk-report__help">Ketik kata kunci untuk menyaring daftar anggota.</p>
                </div>
            </div>
        </section>

        <section class="ketuakk-report__filter-group" aria-labelledby="ketuakk-report-period-title">
            <div>
                <h2 id="ketuakk-report-period-title" class="ketuakk-report__filter-heading">Periode laporan</h2>
                <p class="ketuakk-report__filter-intro">
                    Pilih tahun dan pembagian waktu yang digunakan dalam laporan.
                </p>
            </div>

            <div class="ketuakk-report__fields">
                <div class="ketuakk-report__field">
                    <label for="report_tahun">Tahun</label>
                    <select name="tahun" id="report_tahun" class="form-select">
                        @foreach($tahunOptions ?? [now()->year] as $tahunOption)
                            <option
                                value="{{ $tahunOption }}"
                                {{ (int) ($filters['tahun'] ?? now()->year) === (int) $tahunOption ? 'selected' : '' }}>
                                {{ $tahunOption }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ketuakk-report__field">
                    <label for="mode_periode">Format Waktu</label>
                    <select name="mode_periode" id="mode_periode" class="form-select">
                        <option value="tahun" {{ ($filters['mode_periode'] ?? 'tahun') === 'tahun' ? 'selected' : '' }}>
                            Dalam 1 Tahun
                        </option>
                        <option value="semester" {{ ($filters['mode_periode'] ?? '') === 'semester' ? 'selected' : '' }}>
                            Per Semester
                        </option>
                        <option value="triwulan" {{ ($filters['mode_periode'] ?? '') === 'triwulan' ? 'selected' : '' }}>
                            Per Triwulan
                        </option>
                    </select>
                </div>

                <div id="periodeSelectorWrap" class="ketuakk-report__field ketuakk-report__field--full ketuakk-report__conditional">
                    <label for="periode_nilai" id="periodeLabel">Pilih Periode</label>
                    <select name="periode_nilai" id="periode_nilai" class="form-select">
                        <option value="1" {{ (int) ($filters['periode_nilai'] ?? 1) === 1 ? 'selected' : '' }}>
                            Periode 1
                        </option>
                        <option value="2" {{ (int) ($filters['periode_nilai'] ?? 1) === 2 ? 'selected' : '' }}>
                            Periode 2
                        </option>
                        <option value="3" {{ (int) ($filters['periode_nilai'] ?? 1) === 3 ? 'selected' : '' }}>
                            Periode 3
                        </option>
                        <option value="4" {{ (int) ($filters['periode_nilai'] ?? 1) === 4 ? 'selected' : '' }}>
                            Periode 4
                        </option>
                    </select>
                </div>
            </div>
        </section>

        <div class="ketuakk-report__actions">
            <button type="submit" class="ketuakk-report__button ketuakk-report__button--primary">
                <i class="bi bi-eye"></i>
                Tampilkan Laporan
            </button>

            <div class="ketuakk-report__export">
                <div class="ketuakk-report__export-title">Unduh laporan</div>
                <p class="ketuakk-report__export-description">
                    PDF mengikuti ruang lingkup yang dipilih. Excel selalu memuat seluruh data dalam beberapa sheet, sedangkan CSV diunduh sebagai ZIP berisi beberapa file CSV.
                </p>

                <div class="ketuakk-report__export-buttons">
                    <button
                        type="submit"
                        formaction="/ketuakk/laporan/download"
                        formmethod="GET"
                        name="format"
                        value="pdf"
                        class="ketuakk-report__button ketuakk-report__button--export">
                        <i class="bi bi-file-earmark-pdf"></i>
                        Download PDF
                    </button>

                    <button
                        type="submit"
                        formaction="/ketuakk/laporan/download"
                        formmethod="GET"
                        name="format"
                        value="xlsx"
                        class="ketuakk-report__button ketuakk-report__button--export">
                        <i class="bi bi-file-earmark-excel"></i>
                        Download Excel
                    </button>

                    <button
                        type="submit"
                        formaction="/ketuakk/laporan/download"
                        formmethod="GET"
                        name="format"
                        value="csv"
                        class="ketuakk-report__button ketuakk-report__button--export">
                        <i class="bi bi-filetype-csv"></i>
                        Download CSV ZIP
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>

<section class="ketuakk-report__summary" aria-labelledby="ketuakk-report-summary-title">
    <div class="ketuakk-report__summary-header">
        <h2 id="ketuakk-report-summary-title" class="ketuakk-report__summary-title">Ringkasan laporan</h2>
        <p class="ketuakk-report__summary-description">
            Cakupan data dan capaian kontrak manajemen pada periode yang dipilih.
        </p>
    </div>

    <div class="ketuakk-report__metric-group">
        <div class="ketuakk-report__metric-group-title">Cakupan laporan</div>
        <div class="ketuakk-report__metrics ketuakk-report__metrics--scope">
            <div class="ketuakk-report__metric">
                <span class="ketuakk-report__metric-label">Jumlah Lab Riset</span>
                <strong class="ketuakk-report__metric-value">{{ $summary['jumlah_lab'] ?? 0 }}</strong>
            </div>
            <div class="ketuakk-report__metric">
                <span class="ketuakk-report__metric-label">Jumlah Anggota KK</span>
                <strong class="ketuakk-report__metric-value">{{ $summary['jumlah_anggota'] ?? 0 }}</strong>
            </div>
        </div>
    </div>

    <div class="ketuakk-report__metric-group">
        <div class="ketuakk-report__metric-group-title">Capaian KM</div>
        <div class="ketuakk-report__metrics ketuakk-report__metrics--achievement">
            <div class="ketuakk-report__metric">
                <span class="ketuakk-report__metric-label">Total Target KK</span>
                <strong class="ketuakk-report__metric-value">{{ $summary['total_target'] ?? 0 }}</strong>
            </div>
            <div class="ketuakk-report__metric">
                <span class="ketuakk-report__metric-label">Total Realisasi</span>
                <strong class="ketuakk-report__metric-value">{{ $summary['total_realisasi'] ?? 0 }}</strong>
            </div>
            <div class="ketuakk-report__metric">
                <span class="ketuakk-report__metric-label">Sisa Target</span>
                <strong class="ketuakk-report__metric-value">{{ $summary['total_sisa'] ?? 0 }}</strong>
            </div>
            <div class="ketuakk-report__metric">
                <span class="ketuakk-report__metric-label">Persentase Capaian</span>
                <strong class="ketuakk-report__metric-value">{{ $summary['persentase'] ?? 0 }}%</strong>
            </div>
        </div>

        <div class="ketuakk-report__summary-progress">
            <div class="ketuakk-report__summary-progress-heading">
                <span>Progress capaian kontrak manajemen</span>
                <span>{{ $summary['persentase'] ?? 0 }}%</span>
            </div>
            <div
                class="ketuakk-report__progress-track"
                role="progressbar"
                aria-valuemin="0"
                aria-valuemax="100"
                aria-valuenow="{{ $summary['persentase'] ?? 0 }}"
                aria-label="Progress capaian kontrak manajemen {{ $summary['persentase'] ?? 0 }} persen">
                <div
                    class="ketuakk-report__progress-fill"
                    style="width: {{ min((int) ($summary['persentase'] ?? 0), 100) }}%;">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="ketuakk-report__section" aria-labelledby="report-category-title">
    <div class="ketuakk-report__section-header">
        <div>
            <h2 id="report-category-title" class="ketuakk-report__section-title">Rekap Kategori KM</h2>
            <p class="ketuakk-report__section-description">Perbandingan target dan realisasi untuk setiap kategori kontrak manajemen.</p>
        </div>
    </div>

    <div class="table-responsive ketuakk-report__scroll">
        <table class="table align-middle mb-0 report-table ketuakk-report__table">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Kategori KM</th>
                    <th scope="col" class="ketuakk-report__cell--number">Target</th>
                    <th scope="col" class="ketuakk-report__cell--number">Realisasi</th>
                    <th scope="col" class="ketuakk-report__cell--number">Sisa</th>
                    <th scope="col">Progress</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rekapKategori as $index => $item)
                    <tr>
                        <td class="ketuakk-report__cell--index">{{ $index + 1 }}</td>
                        <td class="ketuakk-report__cell--identity">{{ $item['nama'] ?? '-' }}</td>
                        <td class="ketuakk-report__cell--number">{{ $item['target'] ?? 0 }}</td>
                        <td class="ketuakk-report__cell--number">{{ $item['realisasi'] ?? 0 }}</td>
                        <td class="ketuakk-report__cell--number">{{ $item['sisa'] ?? 0 }}</td>
                        <td class="ketuakk-report__table-progress">
                            <div class="ketuakk-report__progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $item['persentase'] ?? 0 }}" aria-label="Progress {{ $item['nama'] ?? 'kategori' }} {{ $item['persentase'] ?? 0 }} persen">
                                <div class="ketuakk-report__progress-fill" style="width: {{ min((int) ($item['persentase'] ?? 0), 100) }}%;"></div>
                            </div>
                            <div class="ketuakk-report__table-progress-value">{{ $item['persentase'] ?? 0 }}%</div>
                        </td>
                        <td class="ketuakk-report__cell--status">
                            @if(($item['status'] ?? '') === 'Tercapai')
                                <span class="ketuakk-report__status ketuakk-report__status--success">Tercapai</span>
                            @else
                                <span class="ketuakk-report__status ketuakk-report__status--warning">Belum Tercapai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="ketuakk-report__empty">
                            Belum ada data rekap kategori.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="ketuakk-report__section" aria-labelledby="report-target-detail-title">
    <div class="ketuakk-report__section-header">
        <div>
            <h2 id="report-target-detail-title" class="ketuakk-report__section-title">Rekap Detail Target KM per Kategori</h2>
            <p class="ketuakk-report__section-description">
                Menampilkan seluruh sub kategori/jenis KM yang dibuat Ketua KK, beserta keterangan, target, realisasi, pembagian triwulan, dan tenggat penyelesaian.
            </p>
        </div>

        <span class="ketuakk-report__period-label">{{ $filters['label_periode'] ?? '-' }}</span>
    </div>

    @forelse($detailTargetKategori as $kategori)
        <section class="ketuakk-report__category">
            <div class="ketuakk-report__category-header">
                <div>
                    <h3 class="ketuakk-report__category-title">{{ $kategori['kategori'] ?? '-' }}</h3>
                    <div class="ketuakk-report__category-meta">
                        {{ $kategori['jumlah_sub_kategori'] ?? 0 }} sub kategori/jenis KM
                        · Target periode: {{ $kategori['target_periode'] ?? 0 }}
                        · Realisasi periode: {{ $kategori['realisasi_periode'] ?? 0 }}
                    </div>
                </div>
            </div>

            <div class="table-responsive ketuakk-report__scroll">
                <table class="table align-middle mb-0 detail-target-table ketuakk-report__table ketuakk-report__table--detail">
                    <thead>
                        <tr>
                            <th rowspan="2">No</th>
                            <th rowspan="2">Sub Kategori / Jenis KM</th>
                            <th rowspan="2">Keterangan</th>
                            <th scope="colgroup" colspan="6" class="ketuakk-report__group-heading">Target KM</th>
                            <th scope="colgroup" colspan="6" class="ketuakk-report__group-heading">Realisasi KM</th>
                            <th rowspan="2">Sisa</th>
                            <th rowspan="2">Progress</th>
                            <th rowspan="2">Status</th>
                        </tr>
                        <tr>
                            <th>TW 1</th>
                            <th>TW 2</th>
                            <th>TW 3</th>
                            <th>TW 4</th>
                            <th>Total Tahun</th>
                            <th>Periode</th>
                            <th>TW 1</th>
                            <th>TW 2</th>
                            <th>TW 3</th>
                            <th>TW 4</th>
                            <th>Total Tahun</th>
                            <th>Periode</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kategori['rows'] ?? [] as $row)
                            @php
                                $statusClass = match($row['status'] ?? '') {
                                    'Tercapai' => 'status-success',
                                    'On Progress' => 'status-warning',
                                    'Belum Mulai' => 'status-warning',
                                    default => 'status-warning',
                                };
                            @endphp
                            <tr>
                                <td>{{ $row['no'] ?? '-' }}</td>
                                <td class="fw-bold">{{ $row['sub_kategori'] ?? '-' }}</td>
                                <td class="detail-description">{{ $row['keterangan'] ?? '-' }}</td>

                                <td>{{ $row['target_tw1'] ?? 0 }}</td>
                                <td>{{ $row['target_tw2'] ?? 0 }}</td>
                                <td>{{ $row['target_tw3'] ?? 0 }}</td>
                                <td>{{ $row['target_tw4'] ?? 0 }}</td>
                                <td class="fw-bold">{{ $row['target_total_tahunan'] ?? 0 }}</td>
                                <td class="fw-bold text-primary">{{ $row['target_periode'] ?? 0 }}</td>

                                <td>{{ $row['realisasi_tw1'] ?? 0 }}</td>
                                <td>{{ $row['realisasi_tw2'] ?? 0 }}</td>
                                <td>{{ $row['realisasi_tw3'] ?? 0 }}</td>
                                <td>{{ $row['realisasi_tw4'] ?? 0 }}</td>
                                <td class="fw-bold text-success">{{ $row['realisasi_total_tahunan'] ?? 0 }}</td>
                                <td class="fw-bold text-success">{{ $row['realisasi_periode'] ?? 0 }}</td>

                                <td class="fw-bold text-warning">{{ $row['sisa_periode'] ?? 0 }}</td>
                                <td>{{ $row['persentase'] ?? 0 }}%</td>
                                <td>
                                    <span class="ketuakk-report__status {{ ($row['status'] ?? '') === 'Tercapai' ? 'ketuakk-report__status--success' : 'ketuakk-report__status--warning' }}">
                                        {{ $row['status'] ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="18" class="ketuakk-report__empty">
                                    Belum ada detail target KM dalam kategori ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="ketuakk-report__deadline">
                <div class="ketuakk-report__deadline-title">Tenggat Penyelesaian per Triwulan</div>

                <div class="table-responsive ketuakk-report__scroll">
                    <table class="table table-sm align-middle mb-0 period-table ketuakk-report__table ketuakk-report__table--deadline">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Sub Kategori / Jenis KM</th>
                                <th>TW 1</th>
                                <th>TW 2</th>
                                <th>TW 3</th>
                                <th>TW 4</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kategori['rows'] ?? [] as $row)
                                <tr>
                                    <td>{{ $row['no'] ?? '-' }}</td>
                                    <td class="fw-bold">{{ $row['sub_kategori'] ?? '-' }}</td>
                                    <td>
                                        <span class="ketuakk-report__date-range">
                                            {{ $row['tanggal_mulai_tw1'] ?? '-' }} → {{ $row['tanggal_selesai_tw1'] ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="ketuakk-report__date-range">
                                            {{ $row['tanggal_mulai_tw2'] ?? '-' }} → {{ $row['tanggal_selesai_tw2'] ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="ketuakk-report__date-range">
                                            {{ $row['tanggal_mulai_tw3'] ?? '-' }} → {{ $row['tanggal_selesai_tw3'] ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="ketuakk-report__date-range">
                                            {{ $row['tanggal_mulai_tw4'] ?? '-' }} → {{ $row['tanggal_selesai_tw4'] ?? '-' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="ketuakk-report__empty">Belum ada tenggat penyelesaian pada kategori ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @empty
        <div class="ketuakk-report__empty">
            Belum ada target KM yang dibuat pada periode ini.
        </div>
    @endforelse
</section>

@if(($filters['jenis_laporan'] ?? 'kk') !== 'kk')
    <section class="ketuakk-report__section" aria-labelledby="report-scope-detail-title">
        <div class="ketuakk-report__section-header">
            <h2 id="report-scope-detail-title" class="ketuakk-report__section-title">Detail Laporan: {{ $scopeTitle ?? '-' }}</h2>
            <span class="ketuakk-report__period-label">{{ $filters['label_periode'] ?? '-' }}</span>
        </div>

        <div class="table-responsive ketuakk-report__scroll">
            <table class="table align-middle mb-0 report-table ketuakk-report__table ketuakk-report__table--scope">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Keterangan</th>
                        <th>Target</th>
                        <th>Realisasi</th>
                        <th>Sisa</th>
                        <th>Progress</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($laporanRows as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-bold">{{ $item['nama'] ?? '-' }}</td>
                            <td>{{ $item['keterangan'] ?? '-' }}</td>
                            <td>{{ $item['target'] ?? 0 }}</td>
                            <td>{{ $item['realisasi'] ?? 0 }}</td>
                            <td>{{ $item['sisa'] ?? 0 }}</td>
                            <td class="ketuakk-report__table-progress">
                                <div class="ketuakk-report__progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $item['persentase'] ?? 0 }}" aria-label="Progress {{ $item['nama'] ?? 'laporan' }} {{ $item['persentase'] ?? 0 }} persen">
                                    <div class="ketuakk-report__progress-fill" style="width: {{ min((int) ($item['persentase'] ?? 0), 100) }}%;"></div>
                                </div>
                                <div class="ketuakk-report__table-progress-value">{{ $item['persentase'] ?? 0 }}%</div>
                            </td>
                            <td>
                                @if(($item['status'] ?? '') === 'Tercapai')
                                    <span class="ketuakk-report__status ketuakk-report__status--success">Tercapai</span>
                                @else
                                    <span class="ketuakk-report__status ketuakk-report__status--warning">Belum Tercapai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="ketuakk-report__empty">
                                Tidak ada data pada ruang lingkup dan periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endif

@if(($filters['jenis_laporan'] ?? '') === 'anggota_satu')
    <section class="ketuakk-report__section" aria-labelledby="report-activity-title">
        <div class="ketuakk-report__section-header">
            <h2 id="report-activity-title" class="ketuakk-report__section-title">Riwayat Aktivitas Anggota</h2>
        </div>

        <div class="table-responsive ketuakk-report__scroll">
            <table class="table align-middle mb-0 report-table ketuakk-report__table ketuakk-report__table--activity">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kategori</th>
                        <th>Sub Kategori</th>
                        <th>Judul Aktivitas</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Status</th>
                        <th>Bukti</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($pdfAktivitasRows as $index => $aktivitas)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-bold">{{ $aktivitas['kategori_km'] ?? '-' }}</td>
                            <td>{{ $aktivitas['sub_kategori_km'] ?? '-' }}</td>
                            <td>{{ $aktivitas['judul_aktivitas'] ?? '-' }}</td>
                            <td>{{ $aktivitas['tanggal_mulai'] ?? '-' }}</td>
                            <td>{{ $aktivitas['tanggal_selesai'] ?? '-' }}</td>
                            <td><span class="ketuakk-report__status ketuakk-report__status--success">{{ $aktivitas['status_progress'] ?? 'Accepted' }}</span></td>
                            <td>
                                @if(!empty($aktivitas['bukti_link']) && $aktivitas['bukti_link'] !== '-')
                                    <a href="{{ $aktivitas['bukti_link'] }}" target="_blank" rel="noopener noreferrer" class="ketuakk-report__evidence" aria-label="Lihat bukti aktivitas di tab baru">Lihat</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="ketuakk-report__empty">
                                Belum ada aktivitas yang diterima pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const jenisLaporan = document.getElementById('jenis_laporan');
        const labSelectorWrap = document.getElementById('labSelectorWrap');
        const anggotaSelectorWrap = document.getElementById('anggotaSelectorWrap');
        const idLab = document.getElementById('id_lab');
        const idUser = document.getElementById('id_user');

        const modePeriode = document.getElementById('mode_periode');
        const periodeSelectorWrap = document.getElementById('periodeSelectorWrap');
        const periodeLabel = document.getElementById('periodeLabel');
        const periodeNilai = document.getElementById('periode_nilai');

        const searchAnggota = document.getElementById('searchAnggota');

        function updateScopeFilter() {
            const jenis = jenisLaporan.value;
            const isLabSatu = jenis === 'lab_satu';
            const isAnggotaSatu = jenis === 'anggota_satu';

            labSelectorWrap.style.display = isLabSatu ? 'block' : 'none';
            anggotaSelectorWrap.style.display = isAnggotaSatu ? 'block' : 'none';

            idLab.required = isLabSatu;
            idUser.required = isAnggotaSatu;
        }

        function updatePeriodFilter() {
            const mode = modePeriode.value;

            if (mode === 'tahun') {
                periodeSelectorWrap.style.display = 'none';
                periodeNilai.required = false;
                return;
            }

            periodeSelectorWrap.style.display = 'block';
            periodeNilai.required = true;
            periodeNilai.innerHTML = '';

            if (mode === 'semester') {
                periodeLabel.textContent = 'Pilih Semester';

                [1, 2].forEach(function (semester) {
                    const option = document.createElement('option');
                    option.value = semester;
                    option.textContent = 'Semester ' + semester;
                    periodeNilai.appendChild(option);
                });
            } else {
                periodeLabel.textContent = 'Pilih Triwulan';

                [1, 2, 3, 4].forEach(function (triwulan) {
                    const option = document.createElement('option');
                    option.value = triwulan;
                    option.textContent = 'Triwulan ' + triwulan;
                    periodeNilai.appendChild(option);
                });
            }

            const selectedPeriod = @json((string) ($filters['periode_nilai'] ?? '1'));
            const exists = Array.from(periodeNilai.options).some(function (option) {
                return option.value === selectedPeriod;
            });

            periodeNilai.value = exists ? selectedPeriod : '1';
        }

        function filterAnggota() {
            const keyword = (searchAnggota.value || '').toLowerCase().trim();

            Array.from(idUser.options).forEach(function (option, index) {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const searchText = option.dataset.search || '';
                option.hidden = keyword !== '' && !searchText.includes(keyword);
            });
        }

        jenisLaporan.addEventListener('change', updateScopeFilter);
        modePeriode.addEventListener('change', updatePeriodFilter);
        searchAnggota.addEventListener('input', filterAnggota);

        updateScopeFilter();
        updatePeriodFilter();
        filterAnggota();
    });
</script>
@endsection
