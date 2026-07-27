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
        white-space: nowrap;
        vertical-align: middle;
        font-size: 13px;
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
        white-space: nowrap;
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
    .lab-name {
        min-width: 220px;
        font-weight: 800;
        line-height: 1.35;
    }

    .progress-soft {
        width: 100%;
        min-width: 100px;
        height: 9px;
        overflow: hidden;
        border-radius: 999px;
        background: #E8EDF5;
    }

    .progress-soft-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #477EF7, #76A3FF);
    }

    .status-badge {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 6px 11px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-done {
        color: #15803D;
        background: #DCFCE7;
    }

    .status-progress {
        color: #B45309;
        background: #FEF3C7;
    }

    .status-empty {
        color: #64748B;
        background: #E2E8F0;
    }

    /*
    |--------------------------------------------------------------------------
    | Riwayat Penurunan KM
    |--------------------------------------------------------------------------
    */
    .history-card {
        margin-top: 20px;
    }

    .history-timestamp {
        display: inline-flex;
        flex-direction: column;
        gap: 2px;
        min-width: 130px;
        padding: 8px 10px;
        border: 1px solid #DBEAFE;
        border-radius: 10px;
        background: #EFF6FF;
    }

    .history-timestamp-date {
        color: #1D4ED8;
        font-size: 12px;
        font-weight: 800;
    }

    .history-timestamp-time {
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
    }

    .history-lab-name {
        min-width: 190px;
        color: #0F172A;
        font-weight: 800;
        line-height: 1.35;
    }

    .history-kategori {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #EEF4FF;
        color: #2563EB;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .history-subkategori {
        min-width: 150px;
        color: #334155;
        font-weight: 700;
    }

    .history-keterangan {
        min-width: 180px;
        max-width: 250px;
        white-space: normal;
        color: #64748B;
        line-height: 1.45;
    }

    .history-tw {
        text-align: center;
        color: #2563EB;
        font-size: 15px;
        font-weight: 800;
    }

    .history-total {
        color: #0F172A;
        font-size: 16px;
        font-weight: 900;
        text-align: center;
    }

    .status-active {
        color: #15803D;
        background: #DCFCE7;
    }

    .status-inactive {
        color: #64748B;
        background: #E2E8F0;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 7px;
        padding: 30px 15px;
        text-align: center;
        color: #64748B;
    }

    .empty-state i {
        color: #94A3B8;
        font-size: 36px;
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
                Menampilkan seluruh Lab Riset beserta jumlah KM yang telah diturunkan dari Ketua KK.
            </p>
        </div>

        <div class="ketuakk-lab-km__actions">
            <a href="/ketuakk/dashboard" class="btn btn-outline-secondary ketuakk-lab-km__action">
                <i class="bi bi-arrow-left me-1"></i>
                Kembali
            </a>

            <a href="/ketuakk/km-lab-riset/create" class="btn btn-primary ketuakk-lab-km__action">
                <i class="bi bi-plus-lg me-1"></i>
                Turunkan KM ke Lab
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
                Perbandingan target Ketua KK dan KM yang sudah diturunkan ke Lab Riset.
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
                        Progress Penurunan KM
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
                        aria-label="Progress penurunan KM kategori {{ $kategori }}: {{ $persentaseSebenarnya }} persen">
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
                            <div class="ketuakk-lab-km__metric-label">Diturunkan</div>
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
                            Seluruh target sudah diturunkan
                        </div>
                    @else
                        <div class="ketuakk-lab-km__status ketuakk-lab-km__status--progress">
                            <i class="bi bi-clock" aria-hidden="true"></i>
                            {{ number_format($sisa, 0, ',', '.') }} KM belum diturunkan
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>

<div class="card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Daftar Lab Riset</h4>
            <p class="text-muted mb-0">
                Ketua KK dapat melihat penurunan KM ke setiap Lab Riset dan membuka detail pembagian KM anggota.
            </p>
        </div>

        <div class="small text-muted">
            Total Lab: <strong>{{ $dataLab->count() }}</strong>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Nama Lab Riset</th>
                    <th colspan="5" class="group-header">KM DITURUNKAN KE LAB</th>
                    <th rowspan="2">Total Turun</th>
                    <th rowspan="2">Sudah Dibagi ke Anggota</th>
                    <th rowspan="2">Sisa KM</th>
                    <th rowspan="2">Progress</th>
                    <th rowspan="2">Status</th>
                    <th rowspan="2">Aksi</th>
                </tr>

                <tr>
                    @foreach($kategoriDefault as $kategori)
                        <th>{{ $kategori }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @forelse($dataLab as $index => $lab)
                    @php
                        $totalTurun = (int) data_get($lab, 'total_turun', 0);
                        $totalAssign = (int) data_get($lab, 'total_assign', 0);
                        $sisaKm = (int) data_get($lab, 'sisa_km', 0);
                        $persentase = (int) data_get($lab, 'persentase', 0);
                        $status = data_get($lab, 'status', 'Belum Ada KM');

                        $statusClass = match ($status) {
                            'Selesai' => 'status-done',
                            'Belum Selesai' => 'status-progress',
                            default => 'status-empty',
                        };
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td>
                            <div class="lab-name">
                                {{ data_get($lab, 'nama_lab', '-') }}
                            </div>
                        </td>

                        @foreach($kategoriDefault as $kategori)
                            <td class="fw-bold text-center">
                                {{ data_get($lab, 'turun_per_kategori.' . $kategori, 0) }}
                            </td>
                        @endforeach

                        <td class="fw-bold text-center">
                            {{ $totalTurun }}
                        </td>

                        <td class="text-center">
                            {{ $totalAssign }}
                        </td>

                        <td class="text-center">
                            {{ $sisaKm }}
                        </td>

                        <td style="min-width: 145px;">
                            <div class="progress-soft mb-1">
                                <div
                                    class="progress-soft-fill"
                                    style="width: {{ min($persentase, 100) }}%;">
                                </div>
                            </div>

                            <div class="small text-muted text-center">
                                {{ min($persentase, 100) }}%
                            </div>
                        </td>

                        <td>
                            <span class="status-badge {{ $statusClass }}">
                                {{ $status }}
                            </span>
                        </td>

                        <td>
                            <a
                                href="/ketuakk/km-lab-riset/{{ data_get($lab, 'id_lab') }}?tahun={{ $tahun }}"
                                class="btn btn-primary btn-sm">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13">
                            <div class="empty-state">
                                <i class="bi bi-building"></i>
                                <strong>Belum ada Lab Riset.</strong>
                                <span>Tambahkan data Lab Riset terlebih dahulu pada menu Data Master.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card history-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Riwayat Penurunan KM ke Lab Riset</h4>
            <p class="text-muted mb-0">
                Riwayat target KM yang diturunkan Ketua KK kepada Lab Riset pada tahun {{ $tahun }}.
            </p>
        </div>

        <div class="small text-muted">
            Total Riwayat: <strong>{{ $riwayatPenurunanKm->count() }}</strong>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 history-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Waktu Penurunan</th>
                    <th>Lab Riset</th>
                    <th>Kategori KM</th>
                    <th>Sub Kategori / Jenis KM</th>
                    <th>Keterangan</th>
                    <th class="text-center">TW 1</th>
                    <th class="text-center">TW 2</th>
                    <th class="text-center">TW 3</th>
                    <th class="text-center">TW 4</th>
                    <th class="text-center">Total Turun</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($riwayatPenurunanKm as $index => $riwayat)
                    @php
                        $waktuPenurunan = !empty($riwayat->waktu_penurunan)
                            ? \Carbon\Carbon::parse($riwayat->waktu_penurunan)
                            : null;

                        $statusKm = $riwayat->status_km ?? 'Aktif';

                        $statusKmClass = $statusKm === 'Aktif'
                            ? 'status-active'
                            : 'status-inactive';
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td>
                            @if($waktuPenurunan)
                                <div class="history-timestamp">
                                    <span class="history-timestamp-date">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        {{ $waktuPenurunan->format('d/m/Y') }}
                                    </span>

                                    <span class="history-timestamp-time">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $waktuPenurunan->format('H:i') }}
                                    </span>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            <div class="history-lab-name">
                                {{ $riwayat->nama_lab ?? '-' }}
                            </div>
                        </td>

                        <td>
                            <span class="history-kategori">
                                <i class="bi bi-folder2-open"></i>
                                {{ $riwayat->kategori_km ?? '-' }}
                            </span>
                        </td>

                        <td>
                            <div class="history-subkategori">
                                {{ $riwayat->sub_kategori_km ?? '-' }}
                            </div>
                        </td>

                        <td>
                            <div class="history-keterangan">
                                {{ $riwayat->keterangan ?? '-' }}
                            </div>
                        </td>

                        <td class="history-tw">
                            {{ (int) ($riwayat->triwulan_1 ?? 0) }}
                        </td>

                        <td class="history-tw">
                            {{ (int) ($riwayat->triwulan_2 ?? 0) }}
                        </td>

                        <td class="history-tw">
                            {{ (int) ($riwayat->triwulan_3 ?? 0) }}
                        </td>

                        <td class="history-tw">
                            {{ (int) ($riwayat->triwulan_4 ?? 0) }}
                        </td>

                        <td>
                            <div class="history-total">
                                {{ (int) ($riwayat->jumlah_km ?? 0) }}
                            </div>
                        </td>

                        <td>
                            <span class="status-badge {{ $statusKmClass }}">
                                {{ $statusKm }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12">
                            <div class="empty-state">
                                <i class="bi bi-clock-history"></i>
                                <strong>Belum ada riwayat penurunan KM.</strong>
                                <span>
                                    Riwayat akan muncul setelah Ketua KK menurunkan KM kepada Lab Riset.
                                </span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
