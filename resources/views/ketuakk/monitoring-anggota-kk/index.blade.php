@extends('layouts.app')

@section('title', 'Monitoring Anggota KK')

@section('content')
@php
    $dataMonitoring = collect($dataMonitoring ?? []);
    $rekapKategori = collect($rekapKategori ?? []);
    $kategoriDefault = $kategoriDefault ?? ['Penelitian', 'Publikasi', 'Pengabdian', 'Penunjang'];
    $periodeColumns = $periodeColumns ?? [1 => 'TW1', 2 => 'TW2', 3 => 'TW3', 4 => 'TW4'];

    $totalTarget = $dataMonitoring->sum(fn ($item) => (int) ($item['total_target'] ?? 0));
    $totalRealisasi = $dataMonitoring->sum(fn ($item) => (int) ($item['total_realisasi'] ?? 0));
    $totalSisa = max($totalTarget - $totalRealisasi, 0);
    $progressTotal = $totalTarget > 0 ? min(round(($totalRealisasi / $totalTarget) * 100), 100) : 0;
    $labelMode = ($periode ?? 'triwulan') === 'semester'
        ? 'Data monitoring anggota ditampilkan dalam pembagian semester.'
        : 'Data monitoring anggota ditampilkan dalam pembagian triwulan.';
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
    .kk-summary-value.danger { color: #DC2626; }

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
    .chip-danger { color: #DC2626; background: #FFF1F2; border-color: #FECDD3; }

    .ketuakk-member-monitoring {
        padding-bottom: 28px;
    }

    .ketuakk-member-monitoring__overview,
    .ketuakk-member-monitoring__summary,
    .ketuakk-member-monitoring__categories {
        margin-bottom: 16px;
        overflow: hidden;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        background: #FFFFFF;
    }

    .ketuakk-member-monitoring__overview {
        padding: 20px 22px;
    }

    .ketuakk-member-monitoring__heading-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .ketuakk-member-monitoring__eyebrow {
        margin-bottom: 5px;
        color: #2563EB;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .ketuakk-member-monitoring__title {
        margin: 0;
        color: #0F172A;
        font-size: 22px;
        font-weight: 700;
        line-height: 1.25;
        letter-spacing: -.02em;
    }

    .ketuakk-member-monitoring__description {
        max-width: 720px;
        margin: 6px 0 0;
        color: #64748B;
        font-size: 13px;
        line-height: 1.6;
    }

    .ketuakk-member-monitoring__period {
        margin-top: 8px;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
    }

    .ketuakk-member-monitoring__toolbar {
        padding-top: 16px;
        margin-top: 16px;
        border-top: 1px solid #EEF2F7;
    }

    .ketuakk-member-monitoring__filter {
        display: grid;
        grid-template-columns: minmax(150px, 190px) minmax(190px, 240px) auto;
        gap: 10px;
        align-items: end;
    }

    .ketuakk-member-monitoring__field label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
    }

    .ketuakk-member-monitoring__field .form-select {
        min-height: 40px;
        border-color: #CBD5E1;
        border-radius: 8px;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
    }

    .ketuakk-member-monitoring__button {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 1px solid transparent;
        border-radius: 8px;
        padding: 0 15px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
    }

    .ketuakk-member-monitoring__button--apply {
        width: auto;
        min-width: 128px;
        border-color: #2563EB;
        background: #2563EB;
        color: #FFFFFF;
    }

    .ketuakk-member-monitoring__button--apply:hover {
        background: #1D4ED8;
        color: #FFFFFF;
    }

    .ketuakk-member-monitoring__button--back {
        border-color: #CBD5E1;
        background: #FFFFFF;
        color: #334155;
    }

    .ketuakk-member-monitoring__button--back:hover {
        border-color: #94A3B8;
        background: #F8FAFC;
        color: #0F172A;
    }

    .ketuakk-member-monitoring__button:focus-visible {
        outline: 3px solid rgba(37, 99, 235, .22);
        outline-offset: 2px;
    }

    .ketuakk-member-monitoring__section-heading {
        padding: 16px 20px;
        border-bottom: 1px solid #E2E8F0;
        background: #F8FAFC;
    }

    .ketuakk-member-monitoring__section-title {
        margin: 0;
        color: #0F172A;
        font-size: 14px;
        font-weight: 700;
    }

    .ketuakk-member-monitoring__section-description {
        margin: 3px 0 0;
        color: #64748B;
        font-size: 12px;
    }

    .ketuakk-member-monitoring__status-metrics,
    .ketuakk-member-monitoring__km-metrics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .ketuakk-member-monitoring__metric {
        min-width: 0;
        padding: 15px 18px;
        border-right: 1px solid #EEF2F7;
    }

    .ketuakk-member-monitoring__metric:last-child {
        border-right: 0;
    }

    .ketuakk-member-monitoring__metric-label {
        display: block;
        color: #64748B;
        font-size: 11px;
        font-weight: 600;
    }

    .ketuakk-member-monitoring__metric-value {
        display: block;
        margin-top: 5px;
        color: #0F172A;
        font-size: 22px;
        font-weight: 700;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-member-monitoring__metric-value--success { color: #15803D; }
    .ketuakk-member-monitoring__metric-value--warning { color: #B45309; }
    .ketuakk-member-monitoring__metric-value--neutral { color: #475569; }

    .ketuakk-member-monitoring__km-group {
        border-top: 1px solid #E2E8F0;
    }

    .ketuakk-member-monitoring__group-label {
        padding: 11px 18px 0;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .ketuakk-member-monitoring__progress-block {
        padding: 0 18px 16px;
    }

    .ketuakk-member-monitoring__progress-heading,
    .ketuakk-member-monitoring__category-progress-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 7px;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
    }

    .ketuakk-member-monitoring__progress-track {
        width: 100%;
        height: 6px;
        overflow: hidden;
        border-radius: 999px;
        background: #E2E8F0;
    }

    .ketuakk-member-monitoring__progress-fill {
        height: 100%;
        border-radius: inherit;
        background: #2563EB;
    }

    .ketuakk-member-monitoring__category-header,
    .ketuakk-member-monitoring__category-row {
        display: grid;
        grid-template-columns: minmax(180px, 1.6fr) repeat(3, minmax(90px, .65fr)) minmax(210px, 1.2fr);
        align-items: center;
        column-gap: 16px;
    }

    .ketuakk-member-monitoring__category-header {
        padding: 10px 18px;
        border-bottom: 1px solid #CBD5E1;
        color: #64748B;
        background: #F8FAFC;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .ketuakk-member-monitoring__category-row {
        min-height: 58px;
        padding: 11px 18px;
        border-bottom: 1px solid #EEF2F7;
    }

    .ketuakk-member-monitoring__category-row:last-child {
        border-bottom: 0;
    }

    .ketuakk-member-monitoring__category-name {
        color: #0F172A;
        font-size: 13px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .ketuakk-member-monitoring__category-caption {
        display: block;
        margin-top: 2px;
        color: #64748B;
        font-size: 11px;
        font-weight: 500;
    }

    .ketuakk-member-monitoring__category-number {
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

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
        min-width: 2450px;
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
    .period-cell { min-width: 74px; text-align: center; font-weight: 800; }
    .data-label-cell { min-width: 110px; }

    .sticky-col-no,
    .sticky-col-name,
    .sticky-col-lab,
    .sticky-col-jad,
    .sticky-col-data {
        position: sticky;
        z-index: 12;
        background: #FFFFFF !important;
    }

    .sticky-col-no { left: 0; min-width: 58px; }
    .sticky-col-name { left: 58px; min-width: 240px; }
    .sticky-col-lab { left: 298px; min-width: 270px; }
    .sticky-col-jad {
        left: 568px;
        min-width: 82px;
        border-left: 1px solid #CBD5E1 !important;
    }
    .sticky-col-data {
        left: 650px;
        min-width: 110px;
        border-right: 1px solid #CBD5E1 !important;
    }

    thead .sticky-col-no,
    thead .sticky-col-name,
    thead .sticky-col-lab,
    thead .sticky-col-jad,
    thead .sticky-col-data {
        z-index: 50;
        background: #F8FAFC !important;
    }

    tbody tr:nth-child(4n + 3) td,
    tbody tr:nth-child(4n + 4) td {
        background: #FAFBFC;
    }

    .data-badge,
    .status-pill,
    .jad-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .data-badge.target { color: #2563EB; background: #DBEAFE; }
    .data-badge.realisasi { color: #059669; background: #D1FAE5; }
    .jad-pill { min-width: 34px; color: #FFFFFF; background: #2563EB; }
    .status-success { color: #15803D; background: #DCFCE7; }
    .status-warning { color: #B45309; background: #FEF3C7; }
    .status-danger { color: #DC2626; background: #FEE2E2; }
    .status-secondary { color: #64748B; background: #E2E8F0; }

    .member-name {
        color: #0F172A;
        font-weight: 900;
    }

    .member-meta {
        display: block;
        margin-top: 2px;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
    }

    .ketuakk-member-monitoring__table-section {
        position: relative;
        overflow: hidden;
        padding: 0;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        background: #FFFFFF;
        box-shadow: none;
    }

    .ketuakk-member-monitoring__table-header {
        padding: 18px 20px;
        border-bottom: 1px solid #E2E8F0;
        background: #FFFFFF;
    }

    .ketuakk-member-monitoring__table-title {
        margin: 0;
        color: #0F172A;
        font-size: 16px;
        font-weight: 700;
    }

    .ketuakk-member-monitoring__table-description {
        margin: 4px 0 0;
        color: #64748B;
        font-size: 13px;
        line-height: 1.5;
    }

    .ketuakk-member-monitoring__table-scroll {
        overflow-x: auto;
        overflow-y: visible;
    }

    .ketuakk-member-monitoring__table {
        width: 100%;
        min-width: 2450px;
        margin: 0;
        border: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .ketuakk-member-monitoring__table th,
    .ketuakk-member-monitoring__table td {
        padding: 11px 16px !important;
        border: 0;
        border-bottom: 1px solid #EEF2F7;
        color: #334155;
        background: #FFFFFF;
        font-size: 13px;
        font-weight: 500;
        vertical-align: middle;
    }

    .ketuakk-member-monitoring__table thead {
        position: static;
        background: #F8FAFC;
        box-shadow: none;
    }

    .ketuakk-member-monitoring__table thead th {
        color: #475569;
        background: #F8FAFC;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .ketuakk-member-monitoring__table tbody tr:hover > td,
    .ketuakk-member-monitoring__table tbody tr:hover > th {
        background: #F8FAFC !important;
    }

    .ketuakk-member-monitoring__table tbody tr > td,
    .ketuakk-member-monitoring__table tbody tr > th {
        background: #FFFFFF;
    }

    .ketuakk-member-monitoring__group-header {
        border-left: 1px solid #CBD5E1 !important;
        border-right: 1px solid #CBD5E1 !important;
        text-align: center;
    }

    .ketuakk-member-monitoring__group-start {
        border-left: 1px solid #CBD5E1 !important;
    }

    .ketuakk-member-monitoring__group-end {
        border-right: 1px solid #CBD5E1 !important;
    }

    .ketuakk-member-monitoring__cell--index {
        width: 58px;
        min-width: 58px;
        max-width: 58px;
        text-align: center;
        white-space: nowrap;
    }

    .ketuakk-member-monitoring__cell--identity {
        width: 240px;
        min-width: 240px;
        max-width: 240px;
        text-align: left;
        white-space: normal !important;
    }

    .ketuakk-member-monitoring__member-name {
        display: block;
        color: #0F172A;
        font-weight: 700;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .ketuakk-member-monitoring__member-meta {
        display: block;
        margin-top: 3px;
        color: #64748B;
        font-size: 11px;
        font-weight: 500;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-member-monitoring__cell--lab {
        width: 270px;
        min-width: 270px;
        max-width: 270px;
        color: #334155;
        line-height: 1.45;
        text-align: left;
        white-space: normal !important;
        overflow-wrap: anywhere;
    }

    .ketuakk-member-monitoring__cell--jad {
        width: 82px;
        min-width: 82px;
        max-width: 82px;
        text-align: center;
        white-space: nowrap;
    }

    .ketuakk-member-monitoring__jad {
        display: inline-block;
        min-width: 34px;
        padding: 3px 7px;
        border: 1px solid #E2E8F0;
        border-radius: 5px;
        color: #475569;
        background: #F8FAFC;
        font-size: 11px;
        font-weight: 600;
    }

    .ketuakk-member-monitoring__cell--data-type {
        width: 110px;
        min-width: 110px;
        max-width: 110px;
        color: #334155;
        font-size: 12px;
        font-weight: 700 !important;
        text-align: left;
        white-space: nowrap;
    }

    .ketuakk-member-monitoring__cell--data-type-realisasi {
        color: #2563EB !important;
    }

    .ketuakk-member-monitoring__cell--number {
        min-width: 78px;
        color: #334155;
        font-size: 13px;
        font-weight: 700 !important;
        text-align: right;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-member-monitoring__cell--total {
        min-width: 90px;
    }

    .ketuakk-member-monitoring__cell--status {
        min-width: 150px;
        text-align: left;
        white-space: nowrap;
    }

    .ketuakk-member-monitoring__status {
        display: block;
        font-size: 12px;
        font-weight: 700;
    }

    .ketuakk-member-monitoring__status--success { color: #15803D; }
    .ketuakk-member-monitoring__status--warning { color: #B45309; }
    .ketuakk-member-monitoring__status--neutral { color: #475569; }
    .ketuakk-member-monitoring__status--secondary { color: #64748B; }

    .ketuakk-member-monitoring__status-progress {
        display: block;
        margin-top: 3px;
        color: #64748B;
        font-size: 11px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    .ketuakk-member-monitoring__cell--action {
        min-width: 90px;
        text-align: center;
        white-space: nowrap;
    }

    .ketuakk-member-monitoring__detail {
        display: inline-flex;
        min-height: 32px;
        align-items: center;
        justify-content: center;
        padding: 0 11px;
        border: 1px solid #93C5FD;
        border-radius: 7px;
        color: #1D4ED8;
        background: #FFFFFF;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
    }

    .ketuakk-member-monitoring__detail:hover {
        border-color: #60A5FA;
        color: #1D4ED8;
        background: #EFF6FF;
    }

    .ketuakk-member-monitoring__detail:focus-visible {
        outline: 3px solid rgba(37, 99, 235, .22);
        outline-offset: 2px;
    }

    .ketuakk-member-monitoring__empty {
        padding: 24px 16px !important;
        color: #64748B !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        text-align: center;
    }

    .ketuakk-member-monitoring__table .sticky-col-no,
    .ketuakk-member-monitoring__table .sticky-col-name,
    .ketuakk-member-monitoring__table .sticky-col-lab,
    .ketuakk-member-monitoring__table .sticky-col-jad,
    .ketuakk-member-monitoring__table .sticky-col-data {
        background: #FFFFFF !important;
    }

    .ketuakk-member-monitoring__table thead .sticky-col-no,
    .ketuakk-member-monitoring__table thead .sticky-col-name,
    .ketuakk-member-monitoring__table thead .sticky-col-lab,
    .ketuakk-member-monitoring__table thead .sticky-col-jad,
    .ketuakk-member-monitoring__table thead .sticky-col-data {
        background: #F8FAFC !important;
    }

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

        .ketuakk-member-monitoring__status-metrics,
        .ketuakk-member-monitoring__km-metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ketuakk-member-monitoring__metric:nth-child(2) {
            border-right: 0;
        }

        .ketuakk-member-monitoring__metric:nth-child(-n + 2) {
            border-bottom: 1px solid #EEF2F7;
        }

        .ketuakk-member-monitoring__category-header,
        .ketuakk-member-monitoring__category-row {
            grid-template-columns: minmax(170px, 1.4fr) repeat(3, minmax(72px, .55fr)) minmax(180px, 1fr);
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

        .ketuakk-member-monitoring__overview {
            padding: 18px 16px;
        }

        .ketuakk-member-monitoring__filter,
        .ketuakk-member-monitoring__status-metrics,
        .ketuakk-member-monitoring__km-metrics {
            grid-template-columns: 1fr;
        }

        .ketuakk-member-monitoring__button--apply {
            width: 100%;
        }

        .ketuakk-member-monitoring__metric {
            border-right: 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .ketuakk-member-monitoring__metric:last-child {
            border-bottom: 0;
        }

        .ketuakk-member-monitoring__category-header {
            display: none;
        }

        .ketuakk-member-monitoring__category-row {
            grid-template-columns: repeat(3, 1fr);
            row-gap: 10px;
        }

        .ketuakk-member-monitoring__category-name,
        .ketuakk-member-monitoring__category-progress {
            grid-column: 1 / -1;
        }

        .ketuakk-member-monitoring__category-number {
            text-align: left;
        }

        .ketuakk-member-monitoring__category-number::before {
            display: block;
            margin-bottom: 2px;
            color: #64748B;
            content: attr(data-label);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .floating-table-scroll {
            left: 16px;
            right: 16px;
        }
    }
</style>

<div class="ketuakk-member-monitoring">
    <section class="ketuakk-member-monitoring__overview" aria-labelledby="monitoring-anggota-title">
        <div class="ketuakk-member-monitoring__heading-row">
            <div>
                <div class="ketuakk-member-monitoring__eyebrow">Monitoring Ketua KK</div>
                <h1 id="monitoring-anggota-title" class="ketuakk-member-monitoring__title">Monitoring Anggota KK</h1>
                <p class="ketuakk-member-monitoring__description">
                    Pantau target dan realisasi kontrak manajemen anggota berdasarkan periode yang dipilih.
                </p>
                <div class="ketuakk-member-monitoring__period">
                    {{ $labelPeriode ?? 'Triwulan Tahun ' . ($tahun ?? now()->year) }} · {{ $labelMode }}
                </div>
            </div>

            <a href="/ketuakk/dashboard" class="ketuakk-member-monitoring__button ketuakk-member-monitoring__button--back">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>
        </div>

        <div class="ketuakk-member-monitoring__toolbar">
            <form action="/ketuakk/monitoring-anggota-kk" method="GET">
                <div class="ketuakk-member-monitoring__filter">
                    <div class="ketuakk-member-monitoring__field">
                        <label for="tahun">Tahun</label>
                        <select name="tahun" id="tahun" class="form-select">
                            @foreach($tahunOptions ?? [$tahun] as $itemTahun)
                                <option value="{{ $itemTahun }}" {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                                    {{ $itemTahun }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ketuakk-member-monitoring__field">
                        <label for="periode">Jenis Periode</label>
                        <select name="periode" id="periode" class="form-select">
                            <option value="triwulan" {{ ($periode ?? 'triwulan') === 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                            <option value="semester" {{ ($periode ?? '') === 'semester' ? 'selected' : '' }}>Semester</option>
                        </select>
                    </div>

                    <div class="ketuakk-member-monitoring__field">
                        <button type="submit" class="ketuakk-member-monitoring__button ketuakk-member-monitoring__button--apply">
                            <i class="bi bi-funnel-fill"></i>
                            Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="ketuakk-member-monitoring__summary" aria-labelledby="ringkasan-anggota-title">
        <div class="ketuakk-member-monitoring__section-heading">
            <h2 id="ringkasan-anggota-title" class="ketuakk-member-monitoring__section-title">Ringkasan keseluruhan</h2>
            <p class="ketuakk-member-monitoring__section-description">Jumlah anggota berdasarkan status dan akumulasi capaian kontrak manajemen.</p>
        </div>
        <div class="ketuakk-member-monitoring__status-metrics">
            <div class="ketuakk-member-monitoring__metric">
                <span class="ketuakk-member-monitoring__metric-label">Jumlah Anggota</span>
                <strong class="ketuakk-member-monitoring__metric-value">{{ $jumlahAnggota ?? 0 }}</strong>
            </div>
            <div class="ketuakk-member-monitoring__metric">
                <span class="ketuakk-member-monitoring__metric-label">Sudah Selesai</span>
                <strong class="ketuakk-member-monitoring__metric-value ketuakk-member-monitoring__metric-value--success">{{ $jumlahSelesai ?? 0 }}</strong>
            </div>
            <div class="ketuakk-member-monitoring__metric">
                <span class="ketuakk-member-monitoring__metric-label">Sedang Progress</span>
                <strong class="ketuakk-member-monitoring__metric-value ketuakk-member-monitoring__metric-value--warning">{{ $jumlahProgress ?? 0 }}</strong>
            </div>
            <div class="ketuakk-member-monitoring__metric">
                <span class="ketuakk-member-monitoring__metric-label">Belum Mulai</span>
                <strong class="ketuakk-member-monitoring__metric-value ketuakk-member-monitoring__metric-value--neutral">{{ $jumlahBelumMulai ?? 0 }}</strong>
            </div>
        </div>
        <div class="ketuakk-member-monitoring__km-group">
            <div class="ketuakk-member-monitoring__group-label">Metrik KM</div>
            <div class="ketuakk-member-monitoring__km-metrics">
                <div class="ketuakk-member-monitoring__metric">
                    <span class="ketuakk-member-monitoring__metric-label">Target</span>
                    <strong class="ketuakk-member-monitoring__metric-value">{{ $totalTarget }}</strong>
                </div>
                <div class="ketuakk-member-monitoring__metric">
                    <span class="ketuakk-member-monitoring__metric-label">Realisasi</span>
                    <strong class="ketuakk-member-monitoring__metric-value">{{ $totalRealisasi }}</strong>
                </div>
                <div class="ketuakk-member-monitoring__metric">
                    <span class="ketuakk-member-monitoring__metric-label">Sisa</span>
                    <strong class="ketuakk-member-monitoring__metric-value">{{ $totalSisa }}</strong>
                </div>
                <div class="ketuakk-member-monitoring__metric">
                    <span class="ketuakk-member-monitoring__metric-label">Progress Total</span>
                    <strong class="ketuakk-member-monitoring__metric-value">{{ $progressTotal }}%</strong>
                </div>
            </div>
            <div class="ketuakk-member-monitoring__progress-block">
                <div class="ketuakk-member-monitoring__progress-heading">
                    <span>Progress realisasi seluruh anggota</span>
                    <span>{{ $progressTotal }}%</span>
                </div>
                <div class="ketuakk-member-monitoring__progress-track"
                    role="progressbar"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="{{ $progressTotal }}"
                    aria-label="Progress realisasi seluruh anggota {{ $progressTotal }} persen">
                    <div class="ketuakk-member-monitoring__progress-fill" style="width: {{ min($progressTotal, 100) }}%;"></div>
                </div>
            </div>
        </div>
    </section>

    <section class="ketuakk-member-monitoring__categories" aria-labelledby="ringkasan-kategori-title">
        <div class="ketuakk-member-monitoring__section-heading">
            <h2 id="ringkasan-kategori-title" class="ketuakk-member-monitoring__section-title">Ringkasan per kategori</h2>
            <p class="ketuakk-member-monitoring__section-description">Perbandingan target dan realisasi anggota untuk {{ $labelPeriode ?? 'periode aktif' }}.</p>
        </div>
        <div class="ketuakk-member-monitoring__category-header" aria-hidden="true">
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
            <div class="ketuakk-member-monitoring__category-row">
                <div class="ketuakk-member-monitoring__category-name">
                    {{ $item['kategori'] ?? '-' }}
                    <span class="ketuakk-member-monitoring__category-caption">Progress realisasi anggota · {{ $labelPeriode ?? 'periode aktif' }}</span>
                </div>
                <div class="ketuakk-member-monitoring__category-number" data-label="Target">{{ $targetKategori }}</div>
                <div class="ketuakk-member-monitoring__category-number" data-label="Realisasi">{{ $realisasiKategori }}</div>
                <div class="ketuakk-member-monitoring__category-number" data-label="Sisa">{{ $sisaKategori }}</div>
                <div class="ketuakk-member-monitoring__category-progress">
                    <div class="ketuakk-member-monitoring__category-progress-heading">
                        <span>Capaian</span>
                        <span>{{ $progressKategori }}%</span>
                    </div>
                    <div class="ketuakk-member-monitoring__progress-track"
                        role="progressbar"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-valuenow="{{ $progressKategori }}"
                        aria-label="Progress {{ $item['kategori'] ?? 'kategori' }} {{ $progressKategori }} persen">
                        <div class="ketuakk-member-monitoring__progress-fill" style="width: {{ min($progressKategori, 100) }}%;"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </section>

    <section
        class="kk-table-card monitoring-table-card ketuakk-member-monitoring__table-section"
        aria-labelledby="monitoring-progress-anggota-title">
        <div class="ketuakk-member-monitoring__table-header">
            <h2 id="monitoring-progress-anggota-title" class="ketuakk-member-monitoring__table-title">
                Monitoring Progress Anggota KK
            </h2>
            <p class="ketuakk-member-monitoring__table-description">
                Target dan realisasi setiap anggota tetap ditampilkan lengkap per kategori KM dan periode.
            </p>
        </div>

        <div class="table-scroll-sync">
            <div class="table-scroll-container ketuakk-member-monitoring__table-scroll">
                <table class="km-table ketuakk-member-monitoring__table">
                    <thead>
                        <tr>
                            <th scope="col" rowspan="2" class="sticky-col-no ketuakk-member-monitoring__cell--index">No</th>
                            <th scope="col" rowspan="2" class="sticky-col-name ketuakk-member-monitoring__cell--identity">Nama Anggota</th>
                            <th scope="col" rowspan="2" class="sticky-col-lab ketuakk-member-monitoring__cell--lab">Lab Riset</th>
                            <th scope="col" rowspan="2" class="sticky-col-jad ketuakk-member-monitoring__cell--jad">JAD</th>
                            <th scope="col" rowspan="2" class="sticky-col-data ketuakk-member-monitoring__cell--data-type">Data</th>

                            @foreach($kategoriDefault as $kategori)
                                <th
                                    scope="colgroup"
                                    colspan="{{ count($periodeColumns) }}"
                                    class="group-header ketuakk-member-monitoring__group-header">
                                    {{ strtoupper($kategori) }}
                                </th>
                            @endforeach

                            <th scope="col" rowspan="2" class="ketuakk-member-monitoring__cell--number ketuakk-member-monitoring__cell--total">Total</th>
                            <th scope="col" rowspan="2" class="ketuakk-member-monitoring__cell--status">Status</th>
                            <th scope="col" rowspan="2" class="ketuakk-member-monitoring__cell--action">Aksi</th>
                        </tr>

                        <tr>
                            @foreach($kategoriDefault as $kategori)
                                @foreach($periodeColumns as $key => $label)
                                    <th
                                        scope="col"
                                        class="ketuakk-member-monitoring__cell--number {{ $loop->first ? 'category-start ketuakk-member-monitoring__group-start' : '' }} {{ $loop->last ? 'category-end ketuakk-member-monitoring__group-end' : '' }}">
                                        {{ $label }}
                                    </th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($dataMonitoring as $index => $item)
                            @php
                                $statusClass = match($item['status_class'] ?? 'secondary') {
                                    'success' => 'ketuakk-member-monitoring__status--success',
                                    'warning' => 'ketuakk-member-monitoring__status--warning',
                                    'danger' => 'ketuakk-member-monitoring__status--neutral',
                                    default => 'ketuakk-member-monitoring__status--secondary',
                                };
                            @endphp
                            <tr>
                                <td rowspan="2" class="sticky-col-no ketuakk-member-monitoring__cell--index">{{ $index + 1 }}</td>

                                <td rowspan="2" class="sticky-col-name ketuakk-member-monitoring__cell--identity">
                                    <span class="ketuakk-member-monitoring__member-name">{{ $item['nama_dosen'] }}</span>
                                    <span class="ketuakk-member-monitoring__member-meta">{{ $item['nidn'] }}</span>
                                </td>

                                <td rowspan="2" class="sticky-col-lab ketuakk-member-monitoring__cell--lab">
                                    {{ $item['nama_lab'] }}
                                </td>

                                <td rowspan="2" class="sticky-col-jad ketuakk-member-monitoring__cell--jad">
                                    <span class="ketuakk-member-monitoring__jad">{{ $item['jad'] }}</span>
                                </td>

                                <th scope="row" class="sticky-col-data data-label-cell ketuakk-member-monitoring__cell--data-type">Target</th>

                                @foreach($kategoriDefault as $kategori)
                                    @foreach($periodeColumns as $key => $label)
                                        <td class="period-cell ketuakk-member-monitoring__cell--number {{ $loop->first ? 'category-start ketuakk-member-monitoring__group-start' : '' }} {{ $loop->last ? 'category-end ketuakk-member-monitoring__group-end' : '' }}">
                                            {{ $item['data'][$kategori]['target'][$key] ?? 0 }}
                                        </td>
                                    @endforeach
                                @endforeach

                                <td class="ketuakk-member-monitoring__cell--number ketuakk-member-monitoring__cell--total">{{ $item['total_target'] ?? 0 }}</td>

                                <td rowspan="2" class="ketuakk-member-monitoring__cell--status">
                                    <span class="ketuakk-member-monitoring__status {{ $statusClass }}">
                                        {{ $item['status_progress'] ?? 'Belum Ada KM' }}
                                    </span>
                                    <span class="ketuakk-member-monitoring__status-progress">{{ $item['persentase'] ?? 0 }}%</span>
                                </td>

                                <td rowspan="2" class="ketuakk-member-monitoring__cell--action">
                                    <a
                                        href="/ketuakk/monitoring-anggota-kk/{{ $item['id_user'] }}?tahun={{ $tahun }}&periode={{ $periode }}"
                                        class="ketuakk-member-monitoring__detail">
                                        Detail
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <th
                                    scope="row"
                                    class="sticky-col-data data-label-cell ketuakk-member-monitoring__cell--data-type ketuakk-member-monitoring__cell--data-type-realisasi">
                                    Realisasi
                                </th>

                                @foreach($kategoriDefault as $kategori)
                                    @foreach($periodeColumns as $key => $label)
                                        <td class="period-cell ketuakk-member-monitoring__cell--number {{ $loop->first ? 'category-start ketuakk-member-monitoring__group-start' : '' }} {{ $loop->last ? 'category-end ketuakk-member-monitoring__group-end' : '' }}">
                                            {{ $item['data'][$kategori]['realisasi'][$key] ?? 0 }}
                                        </td>
                                    @endforeach
                                @endforeach

                                <td class="ketuakk-member-monitoring__cell--number ketuakk-member-monitoring__cell--total">{{ $item['total_realisasi'] ?? 0 }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="{{ 8 + (count($kategoriDefault) * count($periodeColumns)) }}"
                                    class="ketuakk-member-monitoring__empty">
                                    Belum ada data anggota KK.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="floating-table-scroll" id="floatingMonitoringAnggotaScroll">
                <div class="floating-table-scroll-inner"></div>
            </div>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const wrapper = document.querySelector('.table-scroll-sync');
        const tableScroll = document.querySelector('.table-scroll-container');
        const floatingScroll = document.getElementById('floatingMonitoringAnggotaScroll');
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
