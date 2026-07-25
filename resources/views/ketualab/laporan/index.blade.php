@extends('layouts.app')

@section('title', 'Laporan Ketua Lab')

@section('content')
@php
    $filters = $filters ?? [];
    $summary = $summary ?? [];
    $rekapKategori = collect($rekapKategori ?? []);
    $detailTargetKategori = collect($detailTargetKategori ?? []);
    $laporanRows = collect($laporanRows ?? []);
    $pdfAktivitasRows = collect($pdfAktivitasRows ?? []);
    $anggotaOptions = collect($anggotaOptions ?? []);
    $labNama = $summary['nama_lab'] ?? ($lab->nama_lab ?? '-');
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

    .report-card-value.lab-name {
        font-size: 18px;
        line-height: 1.35;
    }

    .report-table th,
    .report-table td {
        white-space: nowrap;
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

    .status-danger {
        color: #B91C1C;
        background: #FEE2E2;
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

    .info-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #EEF4FF;
        border: 1px solid #BFDBFE;
        color: #1D4ED8;
        font-size: 12px;
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
</style>

<div class="page-heading">
    Laporan <span class="muted">Ketua Lab</span>
</div>

<div class="card report-filter-card mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Pusat Laporan Kontrak Manajemen</h4>
            <p class="text-muted mb-0">
                Periode aktif: <strong>{{ $filters['label_periode'] ?? '-' }}</strong>
            </p>
        </div>

        <a href="/ketualab/dashboard" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger rounded-4 mb-4">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="reportFilterForm" method="GET" action="/ketualab/laporan">
        <div class="report-filter-grid">
            <div class="filter-col-6">
                <div class="report-filter-section h-100">
                    <div class="report-filter-section-title">Ruang Lingkup Laporan PDF</div>

                    <label class="form-label fw-bold">Jenis Laporan</label>
                    <select name="jenis_laporan" id="jenis_laporan" class="form-select">
                        <option value="lab" {{ ($filters['jenis_laporan'] ?? 'lab') === 'lab' ? 'selected' : '' }}>
                            Keseluruhan Lab Riset
                        </option>
                        <option value="anggota_semua" {{ ($filters['jenis_laporan'] ?? '') === 'anggota_semua' ? 'selected' : '' }}>
                            Seluruh Anggota Lab
                        </option>
                        <option value="anggota_satu" {{ ($filters['jenis_laporan'] ?? '') === 'anggota_satu' ? 'selected' : '' }}>
                            Per Anggota Lab
                        </option>
                    </select>

                    <div class="report-filter-help">
                        Pilihan ini menentukan fokus laporan PDF. Excel dan CSV ZIP selalu memuat seluruh data Lab dalam beberapa sheet/file.
                    </div>

                    <div class="mt-3">
                        <div class="info-chip">
                            <i class="bi bi-building"></i>
                            Lab Riset: {{ $labNama }}
                        </div>
                    </div>

                    <div id="anggotaSelectorWrap" class="mt-3" style="display: none;">
                        <label class="form-label fw-bold">Cari dan Pilih Anggota</label>
                        <input
                            type="search"
                            id="searchAnggota"
                            class="form-control mb-2"
                            placeholder="Cari nama, NIDN, atau JAD...">

                        <select name="id_user" id="id_user" class="form-select" size="6">
                            <option value="">-- Pilih Anggota --</option>
                            @foreach($anggotaOptions as $anggota)
                                @php
                                    $searchText = strtolower(
                                        ($anggota->nama_dosen ?? $anggota->username) . ' ' .
                                        ($anggota->nidn ?? '') . ' ' .
                                        ($anggota->jad ?? '')
                                    );
                                @endphp
                                <option
                                    value="{{ $anggota->id_user }}"
                                    data-search="{{ $searchText }}"
                                    {{ (string) ($filters['id_user'] ?? '') === (string) $anggota->id_user ? 'selected' : '' }}>
                                    {{ $anggota->nama_dosen ?? $anggota->username }}
                                    — NIDN: {{ $anggota->nidn ?? '-' }}
                                    — JAD: {{ $anggota->jad ?? '-' }}
                                </option>
                            @endforeach
                        </select>

                        <div class="report-filter-help">
                            Ketik kata kunci untuk menyaring daftar anggota Lab.
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-col-6">
                <div class="report-filter-section h-100">
                    <div class="report-filter-section-title">Periode Laporan</div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tahun</label>
                            <select name="tahun" class="form-select">
                                @foreach($tahunOptions ?? [now()->year] as $tahunOption)
                                    <option
                                        value="{{ $tahunOption }}"
                                        {{ (int) ($filters['tahun'] ?? now()->year) === (int) $tahunOption ? 'selected' : '' }}>
                                        {{ $tahunOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Format Waktu</label>
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

                        <div class="col-md-12" id="periodeSelectorWrap" style="display: none;">
                            <label class="form-label fw-bold" id="periodeLabel">Pilih Periode</label>

                            <select name="periode_nilai" id="periode_nilai" class="form-select">
                                <option value="1">Periode 1</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-eye me-1"></i>
                        Tampilkan Laporan
                    </button>

                    <div class="download-panel flex-grow-1">
                        <div class="download-panel-title">Unduh Laporan</div>
                        <div class="download-panel-text">
                            PDF mengikuti ruang lingkup yang dipilih. Excel berisi beberapa sheet, sedangkan CSV diunduh sebagai ZIP berisi beberapa file CSV.
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button
                                type="submit"
                                formaction="/ketualab/laporan/download"
                                formmethod="GET"
                                name="format"
                                value="pdf"
                                class="btn btn-danger">
                                <i class="bi bi-file-earmark-pdf me-1"></i>
                                Download PDF
                            </button>

                            <button
                                type="submit"
                                formaction="/ketualab/laporan/download"
                                formmethod="GET"
                                name="format"
                                value="xlsx"
                                class="btn btn-success">
                                <i class="bi bi-file-earmark-excel me-1"></i>
                                Download Excel
                            </button>

                            <button
                                type="submit"
                                formaction="/ketualab/laporan/download"
                                formmethod="GET"
                                name="format"
                                value="csv"
                                class="btn btn-secondary">
                                <i class="bi bi-filetype-csv me-1"></i>
                                Download CSV ZIP
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Lab Riset</p>
            <p class="report-card-value lab-name">{{ $labNama }}</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Jumlah Anggota Lab</p>
            <p class="report-card-value">{{ $summary['jumlah_anggota'] ?? 0 }}</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Target Periode</p>
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
    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">KM Turun dari KK</p>
            <p class="report-card-value text-primary">{{ $summary['total_km_turun'] ?? 0 }}</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">KM Sudah Dibagi</p>
            <p class="report-card-value text-success">{{ $summary['total_km_assign'] ?? 0 }}</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Sisa Belum Dibagi</p>
            <p class="report-card-value text-warning">{{ $summary['total_belum_assign'] ?? 0 }}</p>
        </div>
    </div>

    <div class="col-md-3">
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
                    <th>KM Turun</th>
                    <th>Sudah Dibagi</th>
                    <th>Belum Dibagi</th>
                    <th>Target Periode</th>
                    <th>Realisasi</th>
                    <th>Sisa Realisasi</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rekapKategori as $index => $item)
                    @php
                        $status = $item['status'] ?? 'Belum Tercapai';
                        $statusClass = $status === 'Tercapai'
                            ? 'status-success'
                            : (($item['realisasi'] ?? 0) > 0 ? 'status-warning' : 'status-danger');
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $item['nama'] ?? $item['kategori'] ?? '-' }}</td>
                        <td>{{ $item['km_turun'] ?? 0 }}</td>
                        <td class="text-success fw-bold">{{ $item['km_assign'] ?? 0 }}</td>
                        <td class="text-warning fw-bold">{{ $item['sisa_assign'] ?? 0 }}</td>
                        <td>{{ $item['target'] ?? 0 }}</td>
                        <td class="text-success fw-bold">{{ $item['realisasi'] ?? 0 }}</td>
                        <td class="text-warning fw-bold">{{ $item['sisa'] ?? 0 }}</td>
                        <td style="min-width: 180px;">
                            <div class="progress-soft mb-1">
                                <div class="progress-soft-fill" style="width: {{ $item['persentase'] ?? 0 }}%;"></div>
                            </div>
                            <div class="small text-muted">{{ $item['persentase'] ?? 0 }}%</div>
                        </td>
                        <td>
                            <span class="status-pill {{ $statusClass }}">{{ $status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
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
            <h4 class="fw-bold mb-1">Rekap Detail KM Lab per Kategori</h4>
            <p class="text-muted mb-0">
                Menampilkan seluruh sub kategori/jenis KM yang diturunkan ke Lab, keterangan, pembagian ke anggota, target dan realisasi per triwulan, serta tenggat penyelesaian.
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
                            <th colspan="2" class="group-heading">Pembagian ke Anggota</th>
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
                            <th>Sudah Dibagi</th>
                            <th>Belum Dibagi</th>
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
                                $status = $row['status'] ?? '-';
                                $statusClass = $status === 'Tercapai'
                                    ? 'status-success'
                                    : (($status === 'On Progress') ? 'status-warning' : 'status-danger');
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

                                <td class="fw-bold text-success">{{ $row['sudah_assign'] ?? 0 }}</td>
                                <td class="fw-bold text-warning">{{ $row['sisa_assign'] ?? 0 }}</td>

                                <td>{{ $row['realisasi_tw1'] ?? 0 }}</td>
                                <td>{{ $row['realisasi_tw2'] ?? 0 }}</td>
                                <td>{{ $row['realisasi_tw3'] ?? 0 }}</td>
                                <td>{{ $row['realisasi_tw4'] ?? 0 }}</td>
                                <td class="fw-bold text-success">{{ $row['realisasi_total_tahunan'] ?? 0 }}</td>
                                <td class="fw-bold text-success">{{ $row['realisasi_periode'] ?? 0 }}</td>

                                <td class="fw-bold text-warning">{{ $row['sisa_realisasi'] ?? 0 }}</td>
                                <td>{{ $row['persentase'] ?? 0 }}%</td>
                                <td><span class="status-pill {{ $statusClass }}">{{ $status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="20" class="text-center text-muted py-4">
                                    Belum ada detail KM dalam kategori ini.
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
                            @forelse($kategori['rows'] ?? [] as $row)
                                <tr>
                                    <td>{{ $row['no'] ?? '-' }}</td>
                                    <td class="fw-bold">{{ $row['sub_kategori'] ?? '-' }}</td>
                                    <td><span class="period-chip">{{ $row['tanggal_mulai_tw1'] ?? '-' }} → {{ $row['tanggal_selesai_tw1'] ?? '-' }}</span></td>
                                    <td><span class="period-chip">{{ $row['tanggal_mulai_tw2'] ?? '-' }} → {{ $row['tanggal_selesai_tw2'] ?? '-' }}</span></td>
                                    <td><span class="period-chip">{{ $row['tanggal_mulai_tw3'] ?? '-' }} → {{ $row['tanggal_selesai_tw3'] ?? '-' }}</span></td>
                                    <td><span class="period-chip">{{ $row['tanggal_mulai_tw4'] ?? '-' }} → {{ $row['tanggal_selesai_tw4'] ?? '-' }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">Belum ada tenggat KM pada kategori ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-4">
            Belum ada target KM Lab pada periode ini.
        </div>
    @endforelse
</div>

@if(in_array(($filters['jenis_laporan'] ?? 'lab'), ['anggota_semua', 'anggota_satu'], true))
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
                        <th>Nama Anggota</th>
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
                        @php
                            $rowStatus = $item['status'] ?? 'Belum Mulai';
                            $rowClass = $rowStatus === 'Tercapai'
                                ? 'status-success'
                                : (($rowStatus === 'On Progress') ? 'status-warning' : 'status-danger');
                        @endphp
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
                            <td><span class="status-pill {{ $rowClass }}">{{ $rowStatus }}</span></td>
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
    <div class="card mb-4">
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
                                @if(!empty($aktivitas['bukti_url']))
                                    <a href="{{ $aktivitas['bukti_url'] }}" target="_blank" class="btn btn-primary btn-sm">Lihat</a>
                                @elseif(!empty($aktivitas['bukti_link']) && $aktivitas['bukti_link'] !== '-')
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
        const anggotaSelectorWrap = document.getElementById('anggotaSelectorWrap');
        const idUser = document.getElementById('id_user');

        const modePeriode = document.getElementById('mode_periode');
        const periodeSelectorWrap = document.getElementById('periodeSelectorWrap');
        const periodeLabel = document.getElementById('periodeLabel');
        const periodeNilai = document.getElementById('periode_nilai');

        const searchAnggota = document.getElementById('searchAnggota');
        const selectedPeriod = @json((string) ($filters['periode_nilai'] ?? '1'));

        function updateScopeFilter() {
            const isAnggotaSatu = jenisLaporan.value === 'anggota_satu';
            anggotaSelectorWrap.style.display = isAnggotaSatu ? 'block' : 'none';
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

            const isSemester = mode === 'semester';
            periodeLabel.textContent = isSemester ? 'Pilih Semester' : 'Pilih Triwulan';

            const max = isSemester ? 2 : 4;
            const label = isSemester ? 'Semester ' : 'Triwulan ';

            for (let i = 1; i <= max; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = label + i;
                periodeNilai.appendChild(option);
            }

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

                option.hidden = keyword.length > 0 && !(option.dataset.search || '').includes(keyword);
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
