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
        box-shadow: none;
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
        font-size: 13px;
        color: #64748B;
        margin-top: 6px;
    }

    .download-panel {
        background: #F3F6F9;
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
        font-size: 13px;
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
        font-size: 13px;
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
        background: #F3F6F9;
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
        font-size: 13px;
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
        font-size: 13px;
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
    .ketualab-laporan__rekap-table { width: 100%; table-layout: fixed; }
    .ketualab-laporan__rekap-table th,
    .ketualab-laporan__rekap-table td { padding: 10px 12px; border-bottom: 1px solid #E5EAF0; color: #374151; font-size: 14px; line-height: 1.5; overflow-wrap: anywhere; vertical-align: top; }
    .ketualab-laporan__rekap-table th { background: #EEF2F6; font-size: 13px; font-weight: 700; }
    .ketualab-laporan__metric-list { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 3px 12px; margin: 0; }
    .ketualab-laporan__metric-list dt { color: #5B6472; font-size: 13px; font-weight: 500; }
    .ketualab-laporan__metric-list dd { margin: 0; color: #1F2937; font-size: 14px; font-weight: 700; text-align: right; font-variant-numeric: tabular-nums; }
    .ketualab-laporan__filter .form-control,
    .ketualab-laporan__filter .form-select { width: 100%; min-width: 0; min-height: 44px; font-size: 14px; }
    .ketualab-laporan__filter .form-label { font-size: 14px; color: #374151; }
    .ketualab-laporan__filter-actions { display: flex; flex-wrap: wrap; gap: 10px; }
    .ketualab-laporan__filter-actions .btn { min-height: 40px; }
    .ketualab-laporan__target-records{border-top:1px solid #D5DCE5}.ketualab-laporan__target-record{padding:14px 16px 18px;border-bottom:1px solid #E5EAF0}.ketualab-laporan__target-record header{display:flex;justify-content:space-between;gap:16px}.ketualab-laporan__target-record h6{margin:0;color:#1F2937;font-size:16px}.ketualab-laporan__target-record header p{margin:3px 0 0;color:#5B6472;font-size:14px}.ketualab-laporan__target-record dl{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:12px 0}.ketualab-laporan__target-record dt{color:#5B6472;font-size:13px}.ketualab-laporan__target-record dd{margin:2px 0 0;color:#1F2937;font-size:14px;font-weight:700;font-variant-numeric:tabular-nums}.ketualab-laporan__progress{height:6px;overflow:hidden;border-radius:999px;background:#E5EAF0;margin-bottom:12px}.ketualab-laporan__progress span{display:block;height:100%;background:#2457A6}.ketualab-laporan__period-table{width:100%;border-collapse:collapse;table-layout:fixed;color:#374151;font-size:14px}.ketualab-laporan__period-table th,.ketualab-laporan__period-table td{padding:9px 12px;border-bottom:1px solid #E5EAF0;line-height:1.5;overflow-wrap:anywhere}.ketualab-laporan__period-table thead th{background:#EEF2F6;font-size:13px;text-align:left}.ketualab-laporan__period-table td:nth-child(2),.ketualab-laporan__period-table td:nth-child(3),.ketualab-laporan__period-table td:nth-child(4){text-align:right;font-weight:700;font-variant-numeric:tabular-nums}@media(max-width:700px){.ketualab-laporan__target-record dl{grid-template-columns:repeat(2,minmax(0,1fr))}.ketualab-laporan__target-record header{flex-direction:column}.ketualab-laporan__period-table th,.ketualab-laporan__period-table td{padding:8px 6px}}
</style>

<section class="card report-filter-card mb-4" aria-labelledby="ketualab-report-title">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <p class="mb-1 text-primary fw-bold">Laporan Ketua Lab</p>
            <h1 id="ketualab-report-title" class="fw-bold mb-1 fs-4">Pusat Laporan Kontrak Manajemen</h1>
            <p class="text-muted mb-0">
                Periode aktif: <strong>{{ $filters['label_periode'] ?? '-' }}</strong>
            </p>
        </div>

        <a href="/ketualab/dashboard" class="btn btn-outline-secondary">
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

    <form id="reportFilterForm" class="ketualab-laporan__filter" method="GET" action="/ketualab/laporan">
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
                <div class="ketualab-laporan__filter-actions justify-content-between align-items-center">
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
</section>

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
            <p class="text-muted mb-1">KM Diterima dari Ketua KK</p>
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
        <table class="table align-middle mb-0 ketualab-laporan__rekap-table">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Kategori KM</th>
                    <th scope="col">Distribusi dan Pembagian</th>
                    <th scope="col">Capaian</th>
                    <th scope="col">Progress</th>
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
                        <td>
                            <dl class="ketualab-laporan__metric-list">
                                <dt>KM Diterima</dt><dd>{{ $item['km_turun'] ?? 0 }}</dd>
                                <dt>Sudah Dibagi</dt><dd>{{ $item['km_assign'] ?? 0 }}</dd>
                                <dt>Belum Dibagi</dt><dd>{{ $item['sisa_assign'] ?? 0 }}</dd>
                            </dl>
                        </td>
                        <td>
                            <dl class="ketualab-laporan__metric-list">
                                <dt>Target Periode</dt><dd>{{ $item['target'] ?? 0 }}</dd>
                                <dt>Realisasi</dt><dd>{{ $item['realisasi'] ?? 0 }}</dd>
                                <dt>Sisa Realisasi</dt><dd>{{ $item['sisa'] ?? 0 }}</dd>
                            </dl>
                        </td>
                        <td>
                            @php $rekapProgress = min(max((float) ($item['persentase'] ?? 0), 0), 100); @endphp
                            <div class="progress-soft mb-1" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $rekapProgress }}" aria-label="Progress {{ $item['nama'] ?? $item['kategori'] ?? 'kategori' }} {{ $item['persentase'] ?? 0 }} persen">
                                <div class="progress-soft-fill" style="width: {{ $rekapProgress }}%;"></div>
                            </div>
                            <div class="small text-muted">{{ $item['persentase'] ?? 0 }}%</div>
                            <span class="status-pill {{ $statusClass }}">{{ $status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
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
                Menampilkan seluruh sub kategori/jenis KM yang diberikan ke Lab, keterangan, pembagian ke anggota, target dan realisasi per triwulan, serta tenggat penyelesaian.
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
                        {{ $kategori['jumlah_sub_kategori'] ?? 0 }} subkategori/jenis KM
                        · Target periode: {{ $kategori['target_periode'] ?? 0 }}
                        · Realisasi periode: {{ $kategori['realisasi_periode'] ?? 0 }}
                    </div>
                </div>
            </div>

            <div class="ketualab-laporan__target-records">
                        @forelse($kategori['rows'] ?? [] as $row)
                            @php
                                $status = $row['status'] ?? '-';
                                $statusClass = $status === 'Tercapai'
                                    ? 'status-success'
                                    : (($status === 'On Progress') ? 'status-warning' : 'status-danger');
                            @endphp
                            @php $rowProgress = min(max((float) ($row['persentase'] ?? 0), 0), 100); @endphp
                            <article class="ketualab-laporan__target-record">
                                <header><div><span>{{ $row['no'] ?? '-' }}</span><h6>{{ $row['sub_kategori'] ?? '-' }}</h6><p>{{ $row['keterangan'] ?? '-' }}</p></div><span class="status-pill {{ $statusClass }}">{{ $status }}</span></header>
                                <dl><div><dt>Total KM diterima</dt><dd>{{ $row['target_total_tahunan'] ?? 0 }}</dd></div><div><dt>Target periode</dt><dd>{{ $row['target_periode'] ?? 0 }}</dd></div><div><dt>Total realisasi</dt><dd>{{ $row['realisasi_total_tahunan'] ?? 0 }}</dd></div><div><dt>Realisasi periode</dt><dd>{{ $row['realisasi_periode'] ?? 0 }}</dd></div><div><dt>Total dibagi</dt><dd>{{ $row['sudah_assign'] ?? 0 }}</dd></div><div><dt>Belum dibagi</dt><dd>{{ $row['sisa_assign'] ?? 0 }}</dd></div><div><dt>Sisa realisasi</dt><dd>{{ $row['sisa_realisasi'] ?? 0 }}</dd></div><div><dt>Progress</dt><dd>{{ $row['persentase'] ?? 0 }}%</dd></div></dl>
                                <div class="ketualab-laporan__progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $rowProgress }}" aria-label="Progress {{ $row['sub_kategori'] ?? 'subkategori' }} {{ $row['persentase'] ?? 0 }} persen"><span style="width:{{ $rowProgress }}%"></span></div>
                                <table class="ketualab-laporan__period-table"><thead><tr><th scope="col">Periode</th><th scope="col">KM Diterima</th><th scope="col">Dibagi</th><th scope="col">Realisasi</th><th scope="col">Tenggat</th></tr></thead><tbody>
                                    @for($tw = 1; $tw <= 4; $tw++)
                                        <tr><th scope="row">TW {{ $tw }}</th><td>{{ $row['target_tw' . $tw] ?? 0 }}</td><td aria-label="Data pembagian per triwulan tidak tersedia">-</td><td>{{ $row['realisasi_tw' . $tw] ?? 0 }}</td><td>{{ $row['tanggal_mulai_tw' . $tw] ?? '-' }} → {{ $row['tanggal_selesai_tw' . $tw] ?? '-' }}</td></tr>
                                    @endfor
                                </tbody></table>
                            </article>
                        @empty
                            <div class="text-center text-muted py-4">Belum ada detail KM dalam kategori ini.</div>
                        @endforelse
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
            <table class="table align-middle mb-0 ketualab-laporan__rekap-table">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Anggota</th>
                        <th scope="col">Capaian</th>
                        <th scope="col">Progress</th>
                        <th scope="col">Status</th>
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
                            <td><div class="fw-bold">{{ $item['nama'] ?? '-' }}</div><div class="text-muted mt-1">{{ $item['keterangan'] ?? '-' }}</div></td>
                            <td><dl class="ketualab-laporan__metric-list"><dt>Target</dt><dd>{{ $item['target'] ?? 0 }}</dd><dt>Realisasi</dt><dd>{{ $item['realisasi'] ?? 0 }}</dd><dt>Sisa</dt><dd>{{ $item['sisa'] ?? 0 }}</dd></dl></td>
                            <td>
                                @php $memberProgress = min(max((float) ($item['persentase'] ?? 0), 0), 100); @endphp
                                <div class="progress-soft mb-1" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $memberProgress }}" aria-label="Progress {{ $item['nama'] ?? 'anggota' }} {{ $item['persentase'] ?? 0 }} persen">
                                    <div class="progress-soft-fill" style="width: {{ $memberProgress }}%;"></div>
                                </div>
                                <div class="small text-muted">{{ $item['persentase'] ?? 0 }}%</div>
                            </td>
                            <td><span class="status-pill {{ $rowClass }}">{{ $rowStatus }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
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
            <table class="table align-middle mb-0 ketualab-laporan__rekap-table">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Aktivitas</th>
                        <th scope="col">Periode</th>
                        <th scope="col">Status</th>
                        <th scope="col">Bukti</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($pdfAktivitasRows as $index => $aktivitas)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><div class="fw-bold">{{ $aktivitas['judul_aktivitas'] ?? '-' }}</div><div class="text-muted mt-1">{{ $aktivitas['kategori_km'] ?? '-' }} · {{ $aktivitas['sub_kategori_km'] ?? '-' }}</div></td>
                            <td><div>{{ $aktivitas['tanggal_mulai'] ?? '-' }}</div><div class="text-muted mt-1">s.d. {{ $aktivitas['tanggal_selesai'] ?? '-' }}</div></td>
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
                            <td colspan="5" class="text-center text-muted py-4">
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
