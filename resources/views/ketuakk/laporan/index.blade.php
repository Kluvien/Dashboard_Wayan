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

    .report-card-value {
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 0;
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

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Jumlah Lab Riset</p>
            <p class="report-card-value">{{ $summary['jumlah_lab'] ?? 0 }}</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Jumlah Anggota KK</p>
            <p class="report-card-value">{{ $summary['jumlah_anggota'] ?? 0 }}</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Total Target KK</p>
            <p class="report-card-value">{{ $summary['total_target'] ?? 0 }}</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Total Realisasi</p>
            <p class="report-card-value text-success">{{ $summary['total_realisasi'] ?? 0 }}</p>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <p class="text-muted mb-1">Sisa Target</p>
            <p class="report-card-value text-warning">{{ $summary['total_sisa'] ?? 0 }}</p>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <p class="text-muted mb-1">Persentase Capaian</p>
            <p class="report-card-value">{{ $summary['persentase'] ?? 0 }}%</p>
        </div>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Rekap Kategori KM</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0 report-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori KM</th>
                    <th>Target</th>
                    <th>Realisasi</th>
                    <th>Sisa</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rekapKategori as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $item['nama'] ?? '-' }}</td>
                        <td>{{ $item['target'] ?? 0 }}</td>
                        <td>{{ $item['realisasi'] ?? 0 }}</td>
                        <td>{{ $item['sisa'] ?? 0 }}</td>
                        <td style="min-width: 180px;">
                            <div class="progress-soft mb-1">
                                <div class="progress-soft-fill" style="width: {{ $item['persentase'] ?? 0 }}%;"></div>
                            </div>
                            <div class="small text-muted">{{ $item['persentase'] ?? 0 }}%</div>
                        </td>
                        <td>
                            @if(($item['status'] ?? '') === 'Tercapai')
                                <span class="status-pill status-success">Tercapai</span>
                            @else
                                <span class="status-pill status-warning">Belum Tercapai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Belum ada data rekap kategori.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Rekap Detail Target KM per Kategori</h4>
            <p class="text-muted mb-0">
                Menampilkan seluruh sub kategori/jenis KM yang dibuat Ketua KK, beserta keterangan, target, realisasi, pembagian triwulan, dan tenggat penyelesaian.
            </p>
        </div>

        <span class="badge bg-primary">{{ $filters['label_periode'] ?? '-' }}</span>
    </div>

    @forelse($detailTargetKategori as $kategori)
        <div class="detail-category-card">
            <div class="detail-category-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h5 class="detail-category-title">{{ $kategori['kategori'] ?? '-' }}</h5>
                    <div class="detail-category-meta">
                        {{ $kategori['jumlah_sub_kategori'] ?? 0 }} sub kategori/jenis KM
                        · Target periode: {{ $kategori['target_periode'] ?? 0 }}
                        · Realisasi periode: {{ $kategori['realisasi_periode'] ?? 0 }}
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0 detail-target-table">
                    <thead>
                        <tr>
                            <th rowspan="2">No</th>
                            <th rowspan="2">Sub Kategori / Jenis KM</th>
                            <th rowspan="2">Keterangan</th>
                            <th colspan="6" class="group-heading">Target KM</th>
                            <th colspan="6" class="group-heading">Realisasi KM</th>
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
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ $row['status'] ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="18" class="text-center text-muted py-4">
                                    Belum ada detail target KM dalam kategori ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                <div class="fw-bold mb-2">Tenggat Penyelesaian per Triwulan</div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0 period-table">
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
                            @foreach($kategori['rows'] ?? [] as $row)
                                <tr>
                                    <td>{{ $row['no'] ?? '-' }}</td>
                                    <td class="fw-bold">{{ $row['sub_kategori'] ?? '-' }}</td>
                                    <td>
                                        <span class="period-chip">
                                            {{ $row['tanggal_mulai_tw1'] ?? '-' }} → {{ $row['tanggal_selesai_tw1'] ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="period-chip">
                                            {{ $row['tanggal_mulai_tw2'] ?? '-' }} → {{ $row['tanggal_selesai_tw2'] ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="period-chip">
                                            {{ $row['tanggal_mulai_tw3'] ?? '-' }} → {{ $row['tanggal_selesai_tw3'] ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="period-chip">
                                            {{ $row['tanggal_mulai_tw4'] ?? '-' }} → {{ $row['tanggal_selesai_tw4'] ?? '-' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-4">
            Belum ada target KM yang dibuat pada periode ini.
        </div>
    @endforelse
</div>

@if(($filters['jenis_laporan'] ?? 'kk') !== 'kk')
    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h4 class="fw-bold mb-0">Detail Laporan: {{ $scopeTitle ?? '-' }}</h4>
            <span class="badge bg-primary">{{ $filters['label_periode'] ?? '-' }}</span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0 report-table">
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
                            <td style="min-width: 180px;">
                                <div class="progress-soft mb-1">
                                    <div class="progress-soft-fill" style="width: {{ $item['persentase'] ?? 0 }}%;"></div>
                                </div>
                                <div class="small text-muted">{{ $item['persentase'] ?? 0 }}%</div>
                            </td>
                            <td>
                                @if(($item['status'] ?? '') === 'Tercapai')
                                    <span class="status-pill status-success">Tercapai</span>
                                @else
                                    <span class="status-pill status-warning">Belum Tercapai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Tidak ada data pada ruang lingkup dan periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@if(($filters['jenis_laporan'] ?? '') === 'anggota_satu')
    <div class="card">
        <h4 class="fw-bold mb-3">Riwayat Aktivitas Anggota</h4>

        <div class="table-responsive">
            <table class="table align-middle mb-0 report-table">
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
                            <td><span class="badge bg-success">{{ $aktivitas['status_progress'] ?? 'Accepted' }}</span></td>
                            <td>
                                @if(!empty($aktivitas['bukti_link']) && $aktivitas['bukti_link'] !== '-')
                                    <a href="{{ $aktivitas['bukti_link'] }}" target="_blank" class="btn btn-primary btn-sm">Lihat</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Belum ada aktivitas yang diterima pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
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
