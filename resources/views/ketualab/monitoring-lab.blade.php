@extends('layouts.app')

@section('title', 'Monitoring KM Lab')

@section('content')
@php
    $kategoriCards = collect($kategoriCards ?? []);
    $detailKategori = collect($detailKategori ?? []);
    $tahunOptions = collect($tahunOptions ?? [$tahun ?? now()->year]);

    $periode = $periode ?? 'tahun';
    $tahun = $tahun ?? now()->year;
    $triwulan = $triwulan ?? 1;
    $semester = $semester ?? 1;
    $periodeSingkat = $periodeSingkat ?? ('Tahun ' . $tahun);
@endphp

<style>
    .monitoring-lab-page {
        padding-bottom: 24px;
    }

    .monitoring-title {
        margin-bottom: 18px;
        color: #14213d;
        font-size: 30px;
        font-weight: 800;
        letter-spacing: -0.3px;
    }

    .monitoring-title .muted {
        color: #99a1ad;
        font-weight: 800;
    }

    .monitoring-hero,
    .monitoring-section {
        border: 1px solid #e3eaf5;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 7px 18px rgba(15, 23, 42, 0.035);
    }

    .monitoring-hero {
        margin-bottom: 16px;
        padding: 18px 20px;
    }

    .monitoring-hero-inner {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        flex-wrap: wrap;
    }

    .monitoring-hero-title {
        margin: 0;
        color: #14213d;
        font-size: 22px;
        font-weight: 800;
    }

    .monitoring-hero-desc {
        margin: 6px 0 0;
        color: #5d6b82;
        font-size: 14px;
    }

    .monitoring-period-note {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-top: 10px;
        padding: 6px 10px;
        border: 1px solid #cadbfd;
        border-radius: 999px;
        background: #edf4ff;
        color: #2765dc;
        font-size: 12px;
        font-weight: 700;
    }

    .monitoring-filter-form {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .monitoring-filter-form select {
        height: 40px;
        min-width: 118px;
        border: 1px solid #cdd9ec;
        border-radius: 10px;
        background: #ffffff;
        color: #17223a;
        font-weight: 700;
        font-size: 14px;
        padding: 0 32px 0 12px;
        outline: none;
    }

    .monitoring-filter-form .select-period {
        min-width: 132px;
    }

    .btn-filter-apply,
    .btn-primary-soft,
    .btn-view-recap {
        height: 40px;
        border: none;
        border-radius: 10px;
        color: #ffffff;
        background: linear-gradient(135deg, #3f78ef, #568afa);
        box-shadow: 0 6px 12px rgba(62, 117, 236, 0.18);
        font-size: 14px;
        font-weight: 800;
        padding: 0 15px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        transition: .18s ease;
    }

    .btn-filter-apply:hover,
    .btn-primary-soft:hover,
    .btn-view-recap:hover {
        color: #ffffff;
        opacity: .94;
        transform: translateY(-1px);
        text-decoration: none;
    }

    .btn-secondary-monitor {
        height: 40px;
        border: none;
        border-radius: 10px;
        background: #6d7885;
        color: #ffffff;
        font-size: 14px;
        font-weight: 800;
        padding: 0 15px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .btn-secondary-monitor:hover {
        color: #ffffff;
        opacity: .92;
        text-decoration: none;
    }

    .period-select-wrap {
        display: none;
    }

    .period-select-wrap.is-visible {
        display: block;
    }

    /*
    |--------------------------------------------------------------------------
    | 4 kategori aktif: Penelitian, Publikasi, Pengabdian, dan Penunjang.
    | Empat kolom membuat setiap card mengisi satu baris penuh tanpa menyisakan
    | ruang kosong di sisi kanan seperti saat grid masih memakai 5 kolom.
    |--------------------------------------------------------------------------
    */
    .category-card-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .category-card {
        min-height: 300px;
        padding: 14px;
        border: 1px solid #dfE8f6;
        border-top: 4px solid #6092ff;
        border-radius: 17px;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        box-shadow: 0 7px 16px rgba(40, 74, 123, 0.06);
        display: flex;
        flex-direction: column;
    }

    .category-card-head {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        align-items: flex-start;
    }

    .category-name {
        color: #334155;
        font-size: 15px;
        font-weight: 800;
        line-height: 1.2;
    }

    .category-caption {
        margin-top: 2px;
        color: #8da0bf;
        font-size: 10px;
        font-weight: 800;
        line-height: 1.3;
    }

    .category-percentage {
        flex: 0 0 auto;
        padding: 6px 10px;
        border: 1px solid #d5e4ff;
        border-radius: 12px;
        background: #eaf2ff;
        color: #2864e0;
        font-size: 17px;
        font-weight: 900;
    }

    .progress-track {
        height: 9px;
        overflow: hidden;
        border-radius: 99px;
        background: #e8eef7;
        margin: 12px 0 12px;
    }

    .progress-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #427cf0, #70a0ff);
    }

    .category-mini-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .category-metric {
        min-height: 63px;
        padding: 9px 10px;
        border: 1px solid;
        border-radius: 11px;
    }

    .category-metric-label {
        display: flex;
        align-items: center;
        gap: 4px;
        color: #7083a2;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .2px;
        text-transform: uppercase;
    }

    .category-metric-value {
        margin-top: 5px;
        font-size: 21px;
        font-weight: 900;
        line-height: 1;
    }

    .metric-target {
        border-color: #bcd5ff;
        background: #edf5ff;
    }

    .metric-target .category-metric-value {
        color: #2362e4;
    }

    .metric-realisasi {
        border-color: #b8edcd;
        background: #ecfdf4;
    }

    .metric-realisasi .category-metric-value {
        color: #0a9665;
    }

    .metric-dibagi {
        border-color: #d7cdfd;
        background: #f4f1ff;
    }

    .metric-dibagi .category-metric-value {
        color: #7539e8;
    }

    .metric-belum {
        border-color: #ffc9c9;
        background: #fff3f3;
    }

    .metric-belum .category-metric-value {
        color: #e33b3b;
    }

    .metric-belum.is-zero {
        border-color: #b8edcd;
        background: #ecfdf4;
    }

    .metric-belum.is-zero .category-metric-value {
        color: #0a9665;
    }

    .category-note {
        min-height: 36px;
        margin: 11px 0 10px;
        font-size: 11px;
        font-weight: 800;
        line-height: 1.45;
    }

    .note-danger { color: #e33434; }
    .note-warning { color: #c67b11; }
    .note-success { color: #168344; }
    .note-muted { color: #718096; }

    .btn-view-recap {
        width: 100%;
        margin-top: auto;
        height: 38px;
        font-size: 13px;
    }

    .monitoring-section {
        margin-bottom: 18px;
        padding: 18px;
    }

    .monitoring-section-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 15px;
    }

    .monitoring-section-title {
        margin: 0;
        color: #16233e;
        font-size: 20px;
        font-weight: 800;
    }

    .monitoring-section-desc {
        margin: 4px 0 0;
        color: #6b778c;
        font-size: 13px;
    }

    .overall-summary {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        margin-top: 3px;
    }

    .overall-summary-item {
        padding: 11px 12px;
        border: 1px solid #e3ebf7;
        border-radius: 12px;
        background: #fbfdff;
    }

    .overall-summary-label {
        color: #78889f;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .overall-summary-value {
        margin-top: 5px;
        color: #1e2c48;
        font-size: 22px;
        font-weight: 900;
        line-height: 1;
    }

    .overall-summary-value.blue { color: #2864e0; }
    .overall-summary-value.green { color: #0a9665; }
    .overall-summary-value.orange { color: #dc8511; }
    .overall-summary-value.red { color: #e33b3b; }

    .allocation-progress {
        margin-top: 14px;
        padding: 13px 14px;
        border: 1px solid #e1ebfa;
        border-radius: 13px;
        background: #f9fbff;
    }

    .allocation-progress-label {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        color: #34445f;
        font-size: 13px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .allocation-progress .progress-track {
        margin: 0;
    }

    .category-recap-card {
        margin-bottom: 16px;
        border: 1px solid #e1e9f6;
        border-radius: 15px;
        background: #ffffff;
        overflow: hidden;
    }

    .category-recap-card:last-child {
        margin-bottom: 0;
    }

    .recap-card-head {
        padding: 13px 15px;
        border-bottom: 1px solid #d9e6fa;
        background: linear-gradient(135deg, #eff5ff, #fbfdff);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .recap-category-title {
        color: #244270;
        font-size: 17px;
        font-weight: 900;
    }

    .recap-category-meta {
        margin-top: 3px;
        color: #687891;
        font-size: 12px;
        font-weight: 700;
    }

    .recap-category-progress {
        padding: 7px 11px;
        border: 1px solid #cde0ff;
        border-radius: 999px;
        background: #ffffff;
        color: #2d67da;
        font-size: 12px;
        font-weight: 900;
    }

    .recap-table-wrap {
        overflow-x: auto;
    }

    .recap-table {
        width: 100%;
        min-width: 1570px;
        border-collapse: collapse;
    }

    .recap-table th,
    .recap-table td {
        padding: 10px 9px;
        border-bottom: 1px solid #edf1f7;
        vertical-align: middle;
        text-align: center;
        font-size: 12px;
    }

    .recap-table th {
        color: #33445f;
        font-weight: 900;
        white-space: nowrap;
        background: #fbfdff;
    }

    .recap-table thead .group-header th {
        color: #275bc5;
        background: #eff5ff;
        border-bottom: 1px solid #d7e5fb;
        font-size: 11px;
        text-transform: uppercase;
    }

    .recap-table .text-start {
        text-align: left;
    }

    .recap-subcategory {
        min-width: 165px;
        color: #223550;
        font-weight: 800;
        line-height: 1.35;
    }

    .recap-description {
        min-width: 200px;
        max-width: 280px;
        color: #687891;
        line-height: 1.45;
        text-align: left !important;
    }

    .tw-selected {
        background: #f0f7ff !important;
        color: #165fcb;
    }

    .total-period-cell {
        color: #275fd5;
        font-size: 14px !important;
        font-weight: 900;
    }

    .realisasi-cell {
        color: #099768;
        font-size: 14px !important;
        font-weight: 900;
    }

    .distribution-cell {
        color: #7440e7;
        font-weight: 900;
    }

    .distribution-pending {
        color: #e33b3b;
        font-weight: 900;
    }

    .badge-monitor {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 900;
        white-space: nowrap;
    }

    .badge-success { background: #dff8e9; color: #147a3d; }
    .badge-warning { background: #fff3d9; color: #b46e0a; }
    .badge-danger { background: #ffe6e6; color: #cf3030; }
    .badge-info { background: #e6f1ff; color: #2862c9; }
    .badge-secondary { background: #e9eef5; color: #64748b; }

    .deadline-text {
        min-width: 210px;
        color: #596b85;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.45;
        text-align: left;
    }

    .empty-category {
        padding: 30px 15px;
        text-align: center;
        color: #7a879a;
        font-size: 13px;
        font-weight: 700;
    }

    @media (max-width: 1240px) {
        .category-card-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 1024px) {
        .overall-summary {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .monitoring-hero {
            padding: 16px;
        }

        .monitoring-filter-form {
            width: 100%;
            justify-content: stretch;
        }

        .monitoring-filter-form select,
        .monitoring-filter-form button,
        .monitoring-filter-form a {
            flex: 1 1 100%;
            width: 100%;
        }

        .category-card-grid,
        .overall-summary {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="monitoring-lab-page">
    {{-- HEADER + FILTER: mengikuti pola Dashboard Ketua KK --}}
    <section class="monitoring-hero" aria-labelledby="ketualab-monitoring-lab-title">
        <div class="monitoring-hero-inner">
            <div>
                <h1 id="ketualab-monitoring-lab-title" class="monitoring-hero-title">
                    Ringkasan Monitoring KM Lab {{ $labelPeriode ?? '' }}
                </h1>
                <p class="monitoring-hero-desc">
                    Lab: <strong>{{ $lab->nama_lab ?? '-' }}</strong> · Monitoring target, pembagian KM, realisasi, dan tenggat penyelesaian.
                </p>
                <div class="monitoring-period-note">
                    <i class="bi bi-calendar3"></i>
                    {{ $periodeInfo ?? 'Data monitoring ditampilkan sesuai periode yang dipilih.' }}
                </div>
            </div>

            <form action="{{ url('/ketualab/monitoring-lab') }}" method="GET" class="monitoring-filter-form" id="monitoringFilterForm">
                <select name="periode" class="select-period" id="periodeSelect">
                    <option value="tahun" {{ $periode === 'tahun' ? 'selected' : '' }}>Tahunan</option>
                    <option value="triwulan" {{ $periode === 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                    <option value="semester" {{ $periode === 'semester' ? 'selected' : '' }}>Semester</option>
                </select>

                <select name="tahun">
                    @foreach($tahunOptions as $itemTahun)
                        <option value="{{ $itemTahun }}" {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                            {{ $itemTahun }}
                        </option>
                    @endforeach
                </select>

                <div class="period-select-wrap {{ $periode === 'triwulan' ? 'is-visible' : '' }}" id="triwulanWrap">
                    <select name="triwulan">
                        @for($tw = 1; $tw <= 4; $tw++)
                            <option value="{{ $tw }}" {{ (int) $triwulan === $tw ? 'selected' : '' }}>
                                Triwulan {{ $tw }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="period-select-wrap {{ $periode === 'semester' ? 'is-visible' : '' }}" id="semesterWrap">
                    <select name="semester">
                        <option value="1" {{ (int) $semester === 1 ? 'selected' : '' }}>Semester 1</option>
                        <option value="2" {{ (int) $semester === 2 ? 'selected' : '' }}>Semester 2</option>
                    </select>
                </div>

                <button type="submit" class="btn-filter-apply">
                    <i class="bi bi-funnel-fill"></i>
                    Terapkan
                </button>

                <a href="{{ url('/ketualab/dashboard') }}" class="btn-secondary-monitor">
                    <i class="bi bi-arrow-left"></i>
                    Kembali
                </a>
            </form>
        </div>
    </section>

    {{-- CARD KATEGORI: mengikuti tone dan struktur dashboard Ketua KK --}}
    @include('partials.periode-saat-ini')
@include('partials.filter-diterapkan')

<div class="category-card-grid">
        @foreach($kategoriCards as $card)
            @php
                $persentase = min((int) ($card['persentase'] ?? 0), 100);
                $belumDibagi = (int) ($card['belum_dibagi'] ?? 0);
                $catatanClass = $card['catatan_class'] ?? 'muted';
            @endphp

            <div class="category-card">
                <div class="category-card-head">
                    <div>
                        <div class="category-name">{{ $card['kategori'] }}</div>
                        <div class="category-caption">
                            Progress realisasi KM · {{ $periodeSingkat }}
                        </div>
                    </div>
                    <div class="category-percentage">{{ $persentase }}%</div>
                </div>

                <div class="progress-track">
                    <div class="progress-fill" style="width: {{ $persentase }}%"></div>
                </div>

                <div class="category-mini-grid">
                    <div class="category-metric metric-target">
                        <div class="category-metric-label">
                            <i class="bi bi-bullseye"></i>
                            Target
                        </div>
                        <div class="category-metric-value">{{ $card['target'] ?? 0 }}</div>
                    </div>

                    <div class="category-metric metric-realisasi">
                        <div class="category-metric-label">
                            <i class="bi bi-check2-circle"></i>
                            Realisasi
                        </div>
                        <div class="category-metric-value">{{ $card['realisasi'] ?? 0 }}</div>
                    </div>

                    <div class="category-metric metric-dibagi">
                        <div class="category-metric-label">
                            <i class="bi bi-person-check"></i>
                            Sudah Dibagi
                        </div>
                        <div class="category-metric-value">{{ $card['sudah_dibagi'] ?? 0 }}</div>
                    </div>

                    <div class="category-metric metric-belum {{ $belumDibagi <= 0 ? 'is-zero' : '' }}">
                        <div class="category-metric-label">
                            <i class="bi bi-hourglass-split"></i>
                            Belum Dibagi
                        </div>
                        <div class="category-metric-value">{{ $belumDibagi }}</div>
                    </div>
                </div>

                <div class="category-note note-{{ $catatanClass }}">
                    @if($catatanClass === 'danger')
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    @elseif($catatanClass === 'success')
                        <i class="bi bi-check-circle-fill me-1"></i>
                    @elseif($catatanClass === 'warning')
                        <i class="bi bi-info-circle-fill me-1"></i>
                    @else
                        <i class="bi bi-info-circle me-1"></i>
                    @endif
                    {{ $card['catatan'] ?? '-' }}
                </div>

                <a href="#{{ $card['anchor'] }}" class="btn-view-recap">
                    Lihat Rekap
                </a>
            </div>
        @endforeach
    </div>

    {{-- RINGKASAN UMUM --}}
    <div class="monitoring-section">
        <div class="monitoring-section-head">
            <div>
                <h4 class="monitoring-section-title">Ringkasan Periode {{ $periodeSingkat }}</h4>
                <p class="monitoring-section-desc">
                    Rekap umum pembagian dan capaian KM Lab Riset sesuai filter waktu yang dipilih.
                </p>
            </div>
        </div>

        <div class="overall-summary">
            <div class="overall-summary-item">
                <div class="overall-summary-label">Jumlah Anggota</div>
                <div class="overall-summary-value">{{ $jumlahAnggota ?? 0 }}</div>
            </div>

            <div class="overall-summary-item">
                <div class="overall-summary-label">KM Turun Tahun {{ $tahun }}</div>
                <div class="overall-summary-value blue">{{ $totalKmTurun ?? 0 }}</div>
            </div>

            <div class="overall-summary-item">
                <div class="overall-summary-label">Sudah Dibagi</div>
                <div class="overall-summary-value" style="color:#7440e7">{{ $totalKmAssign ?? 0 }}</div>
            </div>

            <div class="overall-summary-item">
                <div class="overall-summary-label">Sisa Belum Dibagi</div>
                <div class="overall-summary-value {{ ($totalSisaAssign ?? 0) > 0 ? 'red' : 'green' }}">
                    {{ $totalSisaAssign ?? 0 }}
                </div>
            </div>

            <div class="overall-summary-item">
                <div class="overall-summary-label">Realisasi {{ $periodeSingkat }}</div>
                <div class="overall-summary-value green">{{ $totalRealisasiPeriode ?? 0 }}</div>
            </div>
        </div>

        <div class="allocation-progress">
            <div class="allocation-progress-label">
                <span>Progress Pembagian KM ke Anggota</span>
                <span>{{ min((int) ($persentaseAssign ?? 0), 100) }}%</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill" style="width: {{ min((int) ($persentaseAssign ?? 0), 100) }}%"></div>
            </div>
            <div class="small text-muted mt-2">
                Target periode: <strong>{{ $totalTargetPeriode ?? 0 }}</strong>
                · Realisasi periode: <strong>{{ $totalRealisasiPeriode ?? 0 }}</strong>
                · Progress realisasi: <strong>{{ min((int) ($persentaseTotal ?? 0), 100) }}%</strong>
            </div>
        </div>
    </div>

    {{-- REKAP RINCI PER KATEGORI --}}
    <div class="monitoring-section">
        <div class="monitoring-section-head">
            <div>
                <h4 class="monitoring-section-title">Rekap KM Lab per Kategori</h4>
                <p class="monitoring-section-desc">
                    Menampilkan sub kategori, keterangan, target dan realisasi tiap triwulan, distribusi ke anggota, tenggat, serta status capaian.
                </p>
            </div>
        </div>

        @foreach($detailKategori as $group)
            @php
                $summary = $group['summary'] ?? [];
                $progressGroup = min((int) ($summary['persentase'] ?? 0), 100);
            @endphp

            <div class="category-recap-card" id="{{ $group['anchor'] }}">
                <div class="recap-card-head">
                    <div>
                        <div class="recap-category-title">{{ $group['kategori'] }}</div>
                        <div class="recap-category-meta">
                            Target {{ $periodeSingkat }}: {{ $summary['target_periode'] ?? 0 }}
                            · Realisasi: {{ $summary['realisasi_periode'] ?? 0 }}
                            · Sudah Dibagi: {{ $summary['sudah_dibagi'] ?? 0 }}
                            · Belum Dibagi: {{ $summary['belum_dibagi'] ?? 0 }}
                        </div>
                    </div>

                    <div class="recap-category-progress">
                        Progress {{ $progressGroup }}%
                    </div>
                </div>

                <div class="recap-table-wrap">
                    <table class="recap-table">
                        <thead>
                            <tr class="group-header">
                                <th rowspan="2">No</th>
                                <th rowspan="2" class="text-start">Sub Kategori / Jenis KM</th>
                                <th rowspan="2" class="text-start">Keterangan</th>
                                <th colspan="4">Target KM per Triwulan</th>
                                <th rowspan="2">Target Periode</th>
                                <th colspan="4">Realisasi Accepted per Triwulan</th>
                                <th rowspan="2">Realisasi Periode</th>
                                <th rowspan="2">Sudah Dibagi</th>
                                <th rowspan="2">Belum Dibagi</th>
                                <th rowspan="2">Tenggat Periode</th>
                                <th rowspan="2">Status Tenggat</th>
                                <th rowspan="2">Progress</th>
                                <th rowspan="2">Status</th>
                            </tr>
                            <tr>
                                @for($tw = 1; $tw <= 4; $tw++)
                                    <th class="{{ in_array($tw, $triwulanAktif ?? []) ? 'tw-selected' : '' }}">TW{{ $tw }}</th>
                                @endfor
                                @for($tw = 1; $tw <= 4; $tw++)
                                    <th class="{{ in_array($tw, $triwulanAktif ?? []) ? 'tw-selected' : '' }}">TW{{ $tw }}</th>
                                @endfor
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($group['items'] as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="text-start">
                                        <div class="recap-subcategory">
                                            {{ $item['sub_kategori_km'] }}
                                        </div>
                                    </td>
                                    <td class="recap-description">
                                        {{ $item['keterangan'] }}
                                    </td>

                                    @for($tw = 1; $tw <= 4; $tw++)
                                        <td class="{{ in_array($tw, $triwulanAktif ?? []) ? 'tw-selected' : '' }}">
                                            {{ data_get($item, 'target_tw.' . $tw, 0) }}
                                        </td>
                                    @endfor

                                    <td class="total-period-cell">{{ $item['target_periode'] }}</td>

                                    @for($tw = 1; $tw <= 4; $tw++)
                                        <td class="{{ in_array($tw, $triwulanAktif ?? []) ? 'tw-selected' : '' }}">
                                            {{ data_get($item, 'realisasi_tw.' . $tw, 0) }}
                                        </td>
                                    @endfor

                                    <td class="realisasi-cell">{{ $item['realisasi_periode'] }}</td>
                                    <td class="distribution-cell">{{ $item['sudah_dibagi'] }}</td>
                                    <td class="distribution-pending">{{ $item['belum_dibagi'] }}</td>
                                    <td class="deadline-text">{{ $item['tenggat_periode'] }}</td>

                                    <td>
                                        <span class="badge-monitor badge-{{ $item['status_tenggat_class'] }}">
                                            {{ $item['status_tenggat'] }}
                                        </span>
                                    </td>

                                    <td>
                                        <div style="min-width:90px;">
                                            <div class="progress-track" style="margin:0 0 4px;">
                                                <div class="progress-fill" style="width: {{ min($item['persentase'], 100) }}%"></div>
                                            </div>
                                            <div class="small text-muted">{{ min($item['persentase'], 100) }}%</div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="badge-monitor badge-{{ $item['status_class'] }}">
                                            {{ $item['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="21">
                                        <div class="empty-category">
                                            Belum ada data KM kategori {{ $group['kategori'] }} pada tahun {{ $tahun }}.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    (function () {
        const periodeSelect = document.getElementById('periodeSelect');
        const triwulanWrap = document.getElementById('triwulanWrap');
        const semesterWrap = document.getElementById('semesterWrap');

        function updatePeriodSelect() {
            const mode = periodeSelect.value;

            triwulanWrap.classList.toggle('is-visible', mode === 'triwulan');
            semesterWrap.classList.toggle('is-visible', mode === 'semester');
        }

        periodeSelect.addEventListener('change', updatePeriodSelect);
        updatePeriodSelect();
    })();
</script>
@endsection
