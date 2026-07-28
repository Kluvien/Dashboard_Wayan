@extends('layouts.app')

@section('title', 'Detail Monitoring Lab Riset')

@section('content')
@php
    $kategoriDefault = $kategoriDefault ?? ['Penelitian', 'Publikasi', 'Pengabdian', 'Penunjang'];
    $periodeColumns = $periodeColumns ?? [1 => 'TW1', 2 => 'TW2', 3 => 'TW3', 4 => 'TW4'];
    $detailPerKategori = collect($detailPerKategori ?? []);
    $anggotaLab = collect($anggotaLab ?? []);
@endphp

<style>
    .ketuakk-lab-monitor-detail__overview,.ketuakk-lab-monitor-detail__section { overflow:hidden; border:1px solid #E2E8F0; border-radius:14px; background:#FFF; margin-bottom:16px; }
    .ketuakk-lab-monitor-detail__header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; padding:20px 22px; border-bottom:1px solid #E2E8F0; flex-wrap:wrap; }
    .ketuakk-lab-monitor-detail__title { margin:0; color:#0F172A; font-size:22px; font-weight:700; }
    .ketuakk-lab-monitor-detail__description { margin:5px 0 0; color:#64748B; font-size:13px; }
    .ketuakk-lab-monitor-detail__table { border:0!important; border-radius:0; }
    .ketuakk-lab-monitor-detail__table th,.ketuakk-lab-monitor-detail__table td { padding:11px 16px!important; border-bottom:1px solid #EEF2F7!important; }
    .lab-detail-filter {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .lab-detail-filter .form-select {
        height: 40px;
        min-height: 40px;
        width: 170px;
        border-color: #CBD5E1;
        font-size: 14px;
    }

    .lab-detail-filter .year-select {
        width: 120px;
    }

    .lab-detail-table {
        min-width: 1580px;
    }

    .lab-detail-table th,
    .lab-detail-table td {
        vertical-align: middle;
        font-size: 13px;
        white-space: nowrap;
    }

    .lab-detail-table th {
        background: #F3F6FB;
        font-weight: 800;
        text-align: center;
    }

    .lab-detail-table .text-wrap-cell {
        white-space: normal;
        min-width: 190px;
    }

    .lab-detail-progress {
        width: 128px;
        min-width: 128px;
    }

    .lab-detail-progress-track {
        height: 8px;
        border-radius: 999px;
        background: #E5E7EB;
        overflow: hidden;
    }

    .lab-detail-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: #477EF7;
    }

    .deadline-list {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 155px;
    }

    .deadline-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        width: max-content;
        max-width: 100%;
        padding: 3px 7px;
        border-radius: 7px;
        border: 1px solid #D7E4FF;
        background: #F7FAFF;
        color: #2458C4;
        font-size: 11px;
        font-weight: 700;
    }

    .summary-number {
        font-size: 27px;
        line-height: 1;
        font-weight: 800;
    }

    .summary-caption {
        color: #64748B;
        font-size: 13px;
        margin-bottom: 7px;
    }

    .category-detail-card {
        border: 1px solid #DCE7FA;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }

    .category-detail-card + .category-detail-card {
        margin-top: 18px;
    }

    .category-detail-card-header {
        padding: 14px 16px;
        background: #F3F7FF;
        border-bottom: 1px solid #DCE7FA;
    }

    .category-detail-card-title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
    }

    .category-detail-card-subtitle {
        margin: 4px 0 0;
        font-size: 13px;
        color: #64748B;
    }

    @media (max-width: 576px) {
        .lab-detail-filter,
        .lab-detail-filter .form-select,
        .lab-detail-filter .btn {
            width: 100%;
        }
    }
</style>

<section class="ketuakk-lab-monitor-detail__overview" aria-labelledby="lab-monitor-detail-title">
    <header class="ketuakk-lab-monitor-detail__header">
        <div>
            <h1 id="lab-monitor-detail-title" class="ketuakk-lab-monitor-detail__title">{{ $lab->nama_lab ?? '-' }}</h1>
            <p class="ketuakk-lab-monitor-detail__description">
                Tahun {{ $tahun }} · Tampilan {{ $labelPeriode ?? 'Triwulan' }} ·
                {{ $jumlahAnggota ?? 0 }} anggota Lab
            </p>
        </div>

        <a href="/ketuakk/monitoring-lab-riset?tahun={{ $tahun }}&periode={{ $periode }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </header>
</section>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Filter Tampilan Detail Lab</h4>
            <p class="text-muted mb-0">
                Data KM ditampilkan berdasarkan tahun dan format periode yang dipilih.
            </p>
        </div>
    </div>

    <form action="/ketuakk/monitoring-lab-riset/{{ $lab->id_lab }}" method="GET" class="lab-detail-filter">
        <select name="tahun" class="form-select year-select" aria-label="Pilih tahun">
            @foreach($tahunOptions ?? [$tahun] as $itemTahun)
                <option value="{{ $itemTahun }}" {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                    {{ $itemTahun }}
                </option>
            @endforeach
        </select>

        <select name="periode" class="form-select" aria-label="Pilih format periode">
            <option value="triwulan" {{ ($periode ?? 'triwulan') === 'triwulan' ? 'selected' : '' }}>
                Triwulan
            </option>
            <option value="semester" {{ ($periode ?? '') === 'semester' ? 'selected' : '' }}>
                Semester
            </option>
        </select>

        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-funnel-fill me-1"></i> Terapkan
        </button>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg">
        <div class="card h-100">
            <div class="summary-caption">Jumlah Anggota</div>
            <div class="summary-number">{{ $jumlahAnggota ?? 0 }}</div>
        </div>
    </div>

    <div class="col-md-6 col-lg">
        <div class="card h-100">
            <div class="summary-caption">KM Turun dari KK</div>
            <div class="summary-number text-primary">{{ $totalKmTurun ?? 0 }}</div>
        </div>
    </div>

    <div class="col-md-6 col-lg">
        <div class="card h-100">
            <div class="summary-caption">Sudah Dibagi ke Anggota</div>
            <div class="summary-number text-success">{{ $totalKmDibagi ?? 0 }}</div>
        </div>
    </div>

    <div class="col-md-6 col-lg">
        <div class="card h-100">
            <div class="summary-caption">Belum Dibagi</div>
            <div class="summary-number {{ ($totalBelumDibagi ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                {{ $totalBelumDibagi ?? 0 }}
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg">
        <div class="card h-100">
            <div class="summary-caption">Realisasi Accepted</div>
            <div class="summary-number text-success">{{ $totalRealisasi ?? 0 }}</div>
        </div>
    </div>

    <div class="col-md-6 col-lg">
        <div class="card h-100">
            <div class="summary-caption">Progress Lab</div>
            <div class="summary-number">{{ min($persentaseLab ?? 0, 100) }}%</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-1">Rekap KM Lab per Kategori</h4>
    <p class="text-muted mb-4">
        Rincian KM yang telah diturunkan ke Lab, pembagian kepada anggota, realisasi Accepted, dan tenggat per periode.
    </p>

    @foreach($kategoriDefault as $kategori)
        @php
            $kategoriData = collect($detailPerKategori->get($kategori, []));
            $targetKategori = $kategoriData->sum('total_target');
            $realisasiKategori = $kategoriData->sum('total_realisasi');
            $dibagiKategori = $kategoriData->sum('sudah_dibagi');
            $belumDibagiKategori = $kategoriData->sum('belum_dibagi');
            $progressKategori = $targetKategori > 0 ? min(round(($realisasiKategori / $targetKategori) * 100), 100) : 0;
        @endphp

        <div class="category-detail-card">
            <div class="category-detail-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="category-detail-card-title">{{ $kategori }}</h5>
                    <p class="category-detail-card-subtitle">
                        Target: {{ $targetKategori }} ·
                        Realisasi: {{ $realisasiKategori }} ·
                        Sudah dibagi: {{ $dibagiKategori }} ·
                        Belum dibagi: {{ $belumDibagiKategori }}
                    </p>
                </div>

                <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                    Progress {{ $progressKategori }}%
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 lab-detail-table ketuakk-lab-monitor-detail__table">
                    <thead>
                        <tr>
                            <th rowspan="2">No</th>
                            <th rowspan="2">Sub Kategori / Jenis KM</th>
                            <th rowspan="2">Keterangan</th>
                            <th colspan="{{ count($periodeColumns) }}">Target per Periode</th>
                            <th colspan="{{ count($periodeColumns) }}">Realisasi Accepted</th>
                            <th rowspan="2">Total Target</th>
                            <th rowspan="2">Realisasi</th>
                            <th rowspan="2">Sudah Dibagi</th>
                            <th rowspan="2">Belum Dibagi</th>
                            <th rowspan="2">Tenggat</th>
                            <th rowspan="2">Status</th>
                            <th rowspan="2">Progress</th>
                        </tr>
                        <tr>
                            @foreach($periodeColumns as $label)
                                <th>{{ $label }}</th>
                            @endforeach
                            @foreach($periodeColumns as $label)
                                <th>{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kategoriData as $index => $item)
                            @php
                                $progress = min((int) ($item['persentase'] ?? 0), 100);
                                $status = $item['status'] ?? 'Belum Mulai';
                            @endphp
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-wrap-cell fw-bold">{{ $item['sub_kategori'] ?? '-' }}</td>
                                <td class="text-wrap-cell">{{ $item['keterangan'] ?? '-' }}</td>

                                @foreach($periodeColumns as $key => $label)
                                    <td class="text-center">{{ $item['target_periode'][$key] ?? 0 }}</td>
                                @endforeach

                                @foreach($periodeColumns as $key => $label)
                                    <td class="text-center text-success fw-bold">{{ $item['realisasi_periode'][$key] ?? 0 }}</td>
                                @endforeach

                                <td class="text-center fw-bold">{{ $item['total_target'] ?? 0 }}</td>
                                <td class="text-center text-success fw-bold">{{ $item['total_realisasi'] ?? 0 }}</td>
                                <td class="text-center text-primary fw-bold">{{ $item['sudah_dibagi'] ?? 0 }}</td>
                                <td class="text-center {{ ($item['belum_dibagi'] ?? 0) > 0 ? 'text-danger' : 'text-success' }} fw-bold">
                                    {{ $item['belum_dibagi'] ?? 0 }}
                                </td>

                                <td>
                                    <div class="deadline-list">
                                        @forelse($item['deadline_periode'] ?? [] as $key => $deadline)
                                            <span class="deadline-chip">
                                                {{ $periodeColumns[$key] ?? ('P' . $key) }}:
                                                {{ $deadline ?: '-' }}
                                            </span>
                                        @empty
                                            <span class="text-muted">-</span>
                                        @endforelse
                                    </div>
                                </td>

                                <td class="text-center">
                                    @if($status === 'Tercapai')
                                        <span class="badge bg-success">Tercapai</span>
                                    @elseif($status === 'Lewat Tenggat')
                                        <span class="badge bg-danger">Lewat Tenggat</span>
                                    @elseif($status === 'Belum Dibagi')
                                        <span class="badge bg-danger">Belum Dibagi</span>
                                    @elseif($status === 'On Progress')
                                        <span class="badge bg-warning text-dark">On Progress</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $status }}</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="lab-detail-progress">
                                        <div class="lab-detail-progress-track">
                                            <div class="lab-detail-progress-fill" style="width: {{ $progress }}%;"></div>
                                        </div>
                                        <div class="small text-muted mt-1">{{ $progress }}%</div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 8 + (count($periodeColumns) * 2) }}" class="text-center text-muted py-4">
                                    Belum ada KM kategori {{ $kategori }} pada tahun {{ $tahun }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Monitoring Anggota Lab</h4>
            <p class="text-muted mb-0">
                Ringkasan pembagian KM dan realisasi Accepted setiap anggota pada tahun {{ $tahun }}.
            </p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Anggota</th>
                    <th>NIDN</th>
                    <th>JAD</th>
                    <th>KM Assign</th>
                    <th>Realisasi</th>
                    <th>Sisa</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($anggotaLab as $index => $item)
                    @php
                        $progress = min((int) ($item['persentase'] ?? 0), 100);
                        $status = $item['status'] ?? 'Belum Mulai';
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $item['nama'] ?? '-' }}</td>
                        <td>{{ $item['nidn'] ?? '-' }}</td>
                        <td>
                            <span class="badge bg-primary">{{ $item['jad'] ?? 'AA' }}</span>
                        </td>
                        <td>{{ $item['target'] ?? 0 }}</td>
                        <td class="text-success fw-bold">{{ $item['realisasi'] ?? 0 }}</td>
                        <td class="{{ ($item['sisa'] ?? 0) > 0 ? 'text-warning' : 'text-success' }} fw-bold">
                            {{ $item['sisa'] ?? 0 }}
                        </td>
                        <td style="min-width: 150px;">
                            <div class="lab-detail-progress-track">
                                <div class="lab-detail-progress-fill" style="width: {{ $progress }}%;"></div>
                            </div>
                            <div class="small text-muted mt-1">{{ $progress }}%</div>
                        </td>
                        <td>
                            @if($status === 'Tercapai')
                                <span class="badge bg-success">Tercapai</span>
                            @elseif($status === 'On Progress')
                                <span class="badge bg-warning text-dark">On Progress</span>
                            @else
                                <span class="badge bg-secondary">Belum Mulai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            Belum ada anggota pada Lab ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
