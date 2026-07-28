@extends('layouts.app')

@section('title', 'Pembagian KM Anggota')

@section('content')
@php
    $dataKmLab = collect($dataKmLab ?? []);
    $anggota = collect($anggota ?? []);
    $riwayatAssign = collect($riwayatAssign ?? []);
    $tahunOptions = collect($tahunOptions ?? [$tahun ?? now()->year]);

    $formatTanggal = function ($tanggal) {
        if (empty($tanggal)) {
            return '-';
        }

        return \Carbon\Carbon::parse($tanggal)->format('d/m/Y');
    };

    $jadLabel = [
        'GB' => 'Guru Besar',
        'LK' => 'Lektor Kepala',
        'L' => 'Lektor',
        'AA' => 'Asisten Ahli',
        'NJFA' => 'Non-Jabatan Fungsional Akademik',
    ];

    $bobotJad = [
        'GB' => 1.4,
        'LK' => 1.2,
        'L' => 1.0,
        'AA' => 0.8,
        'NJFA' => 0.6,
    ];
@endphp

<style>
    .km-table th,
    .km-table td {
        vertical-align: middle;
        font-size: 13px;
    }

    .km-table th {
        white-space: nowrap;
        font-weight: 800;
        text-transform: uppercase;
    }

    .km-table th,
    .km-table td {
        border-bottom: 1px solid #E5E7EB !important;
    }

    .group-header {
        background: #F3F6FB !important;
        text-align: center;
        font-weight: 800 !important;
    }

    .tw-start {
        border-left: 2px solid #CBD5E1 !important;
    }

    .tw-end {
        border-right: 2px solid #CBD5E1 !important;
    }

    .km-detail-category {
        font-size: 14px;
        font-weight: 800;
        color: #0F172A;
        margin-bottom: 7px;
    }

    .km-detail-meta {
        display: flex;
        gap: 7px;
        margin-top: 4px;
        color: #64748B;
        font-size: 12px;
        white-space: normal;
    }

    .km-detail-meta strong {
        min-width: 88px;
        color: #475569;
    }

    .period-cell {
        text-align: center;
        font-weight: 800;
    }

    .period-subtext {
        display: block;
        margin-top: 4px;
        color: #64748B;
        font-size: 11px;
        font-weight: 600;
    }

    .deadline-date {
        display: inline-block;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .deadline-date i {
        color: #477EF7;
    }

    .deadline-empty {
        color: #94A3B8;
        font-weight: 700;
    }

    .remaining-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        padding: 5px 9px;
        border-radius: 6px;
        background: #F8FAFC;
        color: #334155;
        font-size: 12px;
        font-weight: 800;
    }

    .remaining-badge.empty {
        background: #F1F5F9;
        color: #64748B;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 11px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-success {
        background: #DCFCE7;
        color: #15803D;
    }

    .status-warning {
        background: #FEF3C7;
        color: #B45309;
    }

    .status-secondary {
        background: #E2E8F0;
        color: #64748B;
    }

    .modal-km-info {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .modal-km-info-item {
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        background: #F8FAFC;
        padding: 10px 12px;
    }

    .ketualab-assignment__section { margin-bottom: 16px; overflow: hidden; background: #FFF; border: 1px solid #E2E8F0; border-radius: 14px; }
    .ketualab-assignment__section-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; padding: 18px 22px; border-bottom: 1px solid #EEF2F7; }
    .ketualab-assignment__section-title { margin: 0; color: #0F172A; font-size: 17px; font-weight: 700; }
    .ketualab-assignment__section-description { margin: 5px 0 0; color: #64748B; font-size: 13px; }
    .ketualab-assignment__scroll { overflow-x: auto; }
    .ketualab-assignment__table { width: 100%; margin: 0; }
    .ketualab-assignment__table > thead > tr > th { padding: 11px 16px; background: #F8FAFC; border: 0; border-bottom: 1px solid #CBD5E1; color: #334155; font-size: 11px; font-weight: 700; text-transform: uppercase; vertical-align: middle; }
    .ketualab-assignment__table > tbody > tr > td { height: auto; padding: 10px 14px; border: 0; border-bottom: 1px solid #EEF2F7; color: #334155; font-size: 13px; vertical-align: middle; }
    .ketualab-assignment__number { text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; }
    .ketualab-assignment__action { min-height: 32px; padding: 5px 9px; border-radius: 7px; font-size: 12px; font-weight: 700; }

    .modal-km-info-item.full {
        grid-column: span 2;
    }

    .modal-km-info-label {
        font-size: 11px;
        font-weight: 800;
        color: #64748B;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .modal-km-info-value {
        font-size: 14px;
        font-weight: 800;
        color: #0F172A;
        word-break: break-word;
    }

    .modal-tw-card {
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 12px;
        background: #FFFFFF;
        height: 100%;
    }

    .modal-tw-card.disabled {
        opacity: 0.6;
        background: #F8FAFC;
    }

    .modal-tw-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .modal-tw-title strong {
        font-size: 13px;
    }

    .modal-tw-sisa {
        color: #15803D;
        font-size: 12px;
        font-weight: 800;
    }

    .modal-tw-tenggat {
        margin-bottom: 10px;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
    }

    .modal-total-box {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid #BFDBFE;
        border-radius: 12px;
        background: #EFF6FF;
        padding: 11px 13px;
        color: #1D4ED8;
        font-weight: 800;
    }

    .empty-state {
        padding: 30px 15px;
        color: #64748B;
        text-align: center;
    }

    @media (max-width: 768px) {
        .modal-km-info {
            grid-template-columns: 1fr;
        }

        .modal-km-info-item.full {
            grid-column: span 1;
        }
    }
</style>

@if(session('success'))
    <div class="alert alert-success rounded-4">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger rounded-4">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger rounded-4">
        <div class="fw-bold mb-1">Terjadi kesalahan:</div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="card mb-4" aria-labelledby="ketualab-assignment-title">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <p class="text-primary fw-bold mb-1">Pembagian KM Anggota</p>
            <h1 id="ketualab-assignment-title" class="fw-bold fs-4 mb-1">KM yang Diberikan ke Lab</h1>
            <p class="text-muted mb-0">
                Lab: {{ $lab->nama_lab ?? '-' }} | Tahun: {{ $tahun }}
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form method="GET" action="/ketualab/penurunan-km" class="d-flex align-items-center gap-2">
                <select name="tahun" class="form-select" style="min-width: 120px;">
                    @foreach($tahunOptions as $itemTahun)
                        <option
                            value="{{ $itemTahun }}"
                            {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                            {{ $itemTahun }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-primary">
                    Filter
                </button>
            </form>

            <a href="/ketualab/dashboard" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Kembali
            </a>
        </div>
    </div>
</section>

<section class="ketualab-assignment__section" aria-labelledby="assignment-source-title">
    <header class="ketualab-assignment__section-header">
        <div>
            <h2 id="assignment-source-title" class="ketualab-assignment__section-title">Daftar KM dari Ketua KK</h2>
            <p class="ketualab-assignment__section-description">
                Rincian target KM yang diterima Lab beserta pembagian per Triwulan dan tenggat penyelesaian.
            </p>
        </div>
    </header>

    <div class="table-responsive ketualab-assignment__scroll">
        <table class="table km-table ketualab-assignment__table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Detail KM</th>

                    <th colspan="4" class="group-header">
                        KM Lab per Triwulan
                    </th>

                    <th colspan="4" class="group-header">
                        Tenggat per Triwulan
                    </th>

                    <th rowspan="2">Total KM</th>
                    <th rowspan="2">Sudah Dibagi</th>
                    <th rowspan="2">Sisa KM</th>
                    <th rowspan="2">Status</th>
                    <th rowspan="2">Aksi</th>
                </tr>

                <tr>
                    <th class="text-center tw-start">TW 1</th>
                    <th class="text-center">TW 2</th>
                    <th class="text-center">TW 3</th>
                    <th class="text-center tw-end">TW 4</th>

                    <th class="text-center tw-start">TW 1</th>
                    <th class="text-center">TW 2</th>
                    <th class="text-center">TW 3</th>
                    <th class="text-center tw-end">TW 4</th>
                </tr>
            </thead>

            <tbody>
                @forelse($dataKmLab as $index => $km)
                    @php
                        $statusClass = match($km->status ?? 'Belum Ada KM') {
                            'Selesai' => 'status-success',
                            'Belum Selesai' => 'status-warning',
                            default => 'status-secondary',
                        };
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td style="min-width: 300px;">
                            <div class="km-detail-category">
                                {{ $km->kategori_km ?? '-' }}
                            </div>

                            <div class="km-detail-meta">
                                <strong>Sub Kategori:</strong>
                                <span>{{ $km->sub_kategori_display ?? '-' }}</span>
                            </div>

                            <div class="km-detail-meta">
                                <strong>Keterangan:</strong>
                                <span>{{ $km->keterangan ?? '-' }}</span>
                            </div>
                        </td>

                        @foreach([1, 2, 3, 4] as $tw)
                            @php
                                $jumlahTw = (int) ($km->{'triwulan_' . $tw} ?? 0);
                                $sisaTw = (int) ($km->{'sisa_tw' . $tw} ?? 0);
                            @endphp

                            <td class="period-cell {{ $tw === 1 ? 'tw-start' : '' }} {{ $tw === 4 ? 'tw-end' : '' }}">
                                {{ $jumlahTw }}

                                <span class="period-subtext">
                                    Sisa: {{ $sisaTw }}
                                </span>
                            </td>
                        @endforeach

                        @foreach([1, 2, 3, 4] as $tw)
                            @php
                                $jumlahTw = (int) ($km->{'triwulan_' . $tw} ?? 0);
                                $tenggat = $km->{'tanggal_selesai_tw' . $tw} ?? null;
                            @endphp

                            <td class="text-center {{ $tw === 1 ? 'tw-start' : '' }} {{ $tw === 4 ? 'tw-end' : '' }}">
                                @if($jumlahTw > 0 && !empty($tenggat))
                                    <span class="deadline-date">{{ $formatTanggal($tenggat) }}</span>
                                @else
                                    <span class="deadline-empty">-</span>
                                @endif
                            </td>
                        @endforeach

                        <td class="ketualab-assignment__number">
                            {{ $km->jumlah_km ?? 0 }}
                        </td>

                        <td class="ketualab-assignment__number">
                            {{ $km->sudah_assign ?? 0 }}
                        </td>

                        <td class="text-center">
                            <span class="remaining-badge {{ ($km->sisa_km ?? 0) <= 0 ? 'empty' : '' }}">
                                {{ $km->sisa_km ?? 0 }}
                            </span>
                        </td>

                        <td>
                            <span class="status-pill {{ $statusClass }}">
                                {{ $km->status ?? 'Belum Ada KM' }}
                            </span>
                        </td>

                        <td>
                            @if(($km->sisa_km ?? 0) > 0)
                                <button
                                    type="button"
                                    class="btn btn-primary js-open-assign-modal ketualab-assignment__action"
                                    data-bs-toggle="modal"
                                    data-bs-target="#assignKmModal"

                                    data-id-km-lab="{{ $km->id_km_lab }}"
                                    data-kategori="{{ $km->kategori_km }}"
                                    data-sub-kategori="{{ $km->sub_kategori_display }}"
                                    data-keterangan="{{ $km->keterangan ?? '-' }}"

                                    data-sisa-total="{{ $km->sisa_km }}"

                                    data-sisa-tw1="{{ $km->sisa_tw1 }}"
                                    data-sisa-tw2="{{ $km->sisa_tw2 }}"
                                    data-sisa-tw3="{{ $km->sisa_tw3 }}"
                                    data-sisa-tw4="{{ $km->sisa_tw4 }}"

                                    data-tenggat-tw1="{{ $formatTanggal($km->tanggal_selesai_tw1 ?? null) }}"
                                    data-tenggat-tw2="{{ $formatTanggal($km->tanggal_selesai_tw2 ?? null) }}"
                                    data-tenggat-tw3="{{ $formatTanggal($km->tanggal_selesai_tw3 ?? null) }}"
                                    data-tenggat-tw4="{{ $formatTanggal($km->tanggal_selesai_tw4 ?? null) }}">
                                    <i class="bi bi-diagram-3 me-1"></i>
                                    Bagi
                                </button>
                            @else
                                <button type="button" class="btn btn-outline-secondary ketualab-assignment__action" disabled>
                                    Selesai
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15">
                            <div class="empty-state">
                                Belum ada KM yang diberikan oleh Ketua KK ke Lab ini.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<div class="card mb-4">
    <div class="mb-3">
        <h4 class="fw-bold mb-1">Riwayat Assign KM ke Anggota</h4>
        <p class="text-muted mb-0">
            Riwayat pembagian KM dari Ketua Lab kepada anggota Lab.
        </p>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Detail KM</th>
                    <th>Nama Anggota</th>
                    <th>NIDN</th>
                    <th>JAD</th>
                    <th>Pembagian per Triwulan</th>
                    <th>Total KM</th>
                    <th>Tanggal Assign</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($riwayatAssign as $index => $assign)
                    @php
                        $subKategori =
                            $assign->sub_kategori_km
                            ?? $assign->indikator_target
                            ?? '-';
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td style="min-width: 240px;">
                            <div class="fw-bold">
                                {{ $assign->kategori_km ?? '-' }}
                            </div>

                            <div class="small text-muted mt-1">
                                {{ $subKategori }}
                            </div>

                            <div class="small text-muted mt-1">
                                {{ $assign->keterangan ?? '-' }}
                            </div>
                        </td>

                        <td class="fw-bold">
                            {{ $assign->nama_dosen ?? $assign->username ?? '-' }}
                        </td>

                        <td>{{ $assign->nidn ?? '-' }}</td>

                        <td>
                            <span class="badge bg-primary">
                                {{ $assign->jad ?? 'AA' }}
                            </span>
                        </td>

                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach([1, 2, 3, 4] as $tw)
                                    @php
                                        $jumlahTw = (int) ($assign->{'triwulan_' . $tw} ?? 0);
                                    @endphp

                                    @if($jumlahTw > 0)
                                        <span class="badge bg-light text-dark border">
                                            TW{{ $tw }}: {{ $jumlahTw }}
                                        </span>
                                    @endif
                                @endforeach

                                @if(
                                    (int) ($assign->triwulan_1 ?? 0) <= 0 &&
                                    (int) ($assign->triwulan_2 ?? 0) <= 0 &&
                                    (int) ($assign->triwulan_3 ?? 0) <= 0 &&
                                    (int) ($assign->triwulan_4 ?? 0) <= 0
                                )
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </td>

                        <td class="fw-bold">
                            {{ $assign->jumlah_km ?? 0 }}
                        </td>

                        <td>
                            {{ $formatTanggal($assign->created_at ?? null) }}
                        </td>

                        <td>
                            <form
                                action="/ketualab/penurunan-km/assign/{{ $assign->id_km_anggota }}"
                                method="POST"
                                class="js-delete-form"
                                data-message="Apakah Anda yakin ingin menghapus assign KM anggota ini?">

                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="tahun" value="{{ $tahun }}">

                                <button type="submit" class="btn btn-delete btn-sm">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                Belum ada riwayat pembagian KM kepada anggota.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="mb-3">
        <h4 class="fw-bold mb-1">Daftar Anggota Lab</h4>
        <p class="text-muted mb-0">
            Data anggota yang dapat menerima pembagian KM dari Ketua Lab.
        </p>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Anggota</th>
                    <th>NIDN</th>
                    <th>Email</th>
                    <th>JAD</th>
                    <th>Bobot Saran</th>
                </tr>
            </thead>

            <tbody>
                @forelse($anggota as $index => $item)
                    @php
                        $jad = $item->jad ?? 'AA';
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td class="fw-bold">
                            {{ $item->nama_dosen ?? $item->username }}
                        </td>

                        <td>{{ $item->nidn ?? '-' }}</td>

                        <td>{{ $item->email ?? '-' }}</td>

                        <td>
                            <span class="badge bg-primary">{{ $jad }}</span>

                            <div class="small text-muted mt-1">
                                {{ $jadLabel[$jad] ?? 'Non-Jabatan Fungsional Akademik' }}
                            </div>
                        </td>

                        <td>{{ $bobotJad[$jad] ?? 0.6 }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                Belum ada anggota pada Lab ini.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="assignKmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0" style="border-radius: 18px;">
            <form action="/ketualab/penurunan-km" method="POST" id="assignKmForm">
                @csrf

                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <input type="hidden" name="id_km_lab" id="modalIdKmLab">

                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold">Bagi KM ke Anggota</h5>

                        <p class="text-muted mb-0 small">
                            Pembagian dilakukan per Triwulan sesuai sisa KM Lab.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>
                </div>

                <div class="modal-body pt-3">
                    <div class="modal-km-info">
                        <div class="modal-km-info-item">
                            <div class="modal-km-info-label">Kategori KM</div>
                            <div class="modal-km-info-value" id="modalKategoriKm">-</div>
                        </div>

                        <div class="modal-km-info-item">
                            <div class="modal-km-info-label">Sub Kategori KM</div>
                            <div class="modal-km-info-value" id="modalSubKategoriKm">-</div>
                        </div>

                        <div class="modal-km-info-item full">
                            <div class="modal-km-info-label">Keterangan</div>
                            <div class="modal-km-info-value" id="modalKeteranganKm">-</div>
                        </div>

                        <div class="modal-km-info-item full">
                            <div class="modal-km-info-label">Sisa KM Total yang Bisa Dibagikan</div>
                            <div class="modal-km-info-value text-success">
                                <span id="modalSisaTotal">0</span> KM
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilih Anggota</label>

                        <select name="id_user" class="form-select" required>
                            <option value="">-- Pilih Anggota --</option>

                            @foreach($anggota as $item)
                                <option value="{{ $item->id_user }}">
                                    {{ $item->nama_dosen ?? $item->username }}
                                    ({{ $item->username }}) - {{ $item->jad ?? 'AA' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold">Pembagian KM per Triwulan</label>
                        <div class="small text-muted">
                            Hanya isi Triwulan yang memiliki sisa KM. Tenggat ditampilkan sebagai informasi penyelesaian KM.
                        </div>
                    </div>

                    <div class="row g-3">
                        @foreach([1, 2, 3, 4] as $tw)
                            <div class="col-md-6">
                                <div class="modal-tw-card" id="modalTwCard{{ $tw }}">
                                    <div class="modal-tw-title">
                                        <strong>Triwulan {{ $tw }}</strong>
                                        <span class="modal-tw-sisa">
                                            Sisa: <span id="modalSisaTw{{ $tw }}">0</span>
                                        </span>
                                    </div>

                                    <div class="modal-tw-tenggat">
                                        Tenggat:
                                        <span id="modalTenggatTw{{ $tw }}">-</span>
                                    </div>

                                    <input
                                        type="number"
                                        name="triwulan_{{ $tw }}"
                                        id="modalTw{{ $tw }}"
                                        class="form-control js-input-tw"
                                        min="0"
                                        value="0"
                                        placeholder="Jumlah KM TW {{ $tw }}">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="modal-total-box mt-4">
                        <span>Total KM yang Akan Dibagikan</span>
                        <span><span id="modalTotalPembagian">0</span> KM</span>
                    </div>

                    <div
                        id="modalPembagianError"
                        class="alert alert-danger mt-3 mb-0 d-none">
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Simpan Pembagian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.js-open-assign-modal');

        const modalIdKmLab = document.getElementById('modalIdKmLab');
        const modalKategoriKm = document.getElementById('modalKategoriKm');
        const modalSubKategoriKm = document.getElementById('modalSubKategoriKm');
        const modalKeteranganKm = document.getElementById('modalKeteranganKm');
        const modalSisaTotal = document.getElementById('modalSisaTotal');
        const modalTotalPembagian = document.getElementById('modalTotalPembagian');
        const modalError = document.getElementById('modalPembagianError');
        const form = document.getElementById('assignKmForm');

        const inputTriwulan = {
            1: document.getElementById('modalTw1'),
            2: document.getElementById('modalTw2'),
            3: document.getElementById('modalTw3'),
            4: document.getElementById('modalTw4')
        };

        function updateTotalPembagian() {
            let total = 0;

            for (let tw = 1; tw <= 4; tw++) {
                total += parseInt(inputTriwulan[tw].value || 0);
            }

            modalTotalPembagian.textContent = total;

            return total;
        }

        function resetError() {
            modalError.classList.add('d-none');
            modalError.textContent = '';
        }

        function setTriwulanInput(tw, sisa, tenggat) {
            const input = inputTriwulan[tw];
            const card = document.getElementById('modalTwCard' + tw);
            const sisaLabel = document.getElementById('modalSisaTw' + tw);
            const tenggatLabel = document.getElementById('modalTenggatTw' + tw);

            sisaLabel.textContent = sisa;
            tenggatLabel.textContent = tenggat || '-';

            input.value = 0;
            input.max = sisa;
            input.disabled = sisa <= 0;

            card.classList.toggle('disabled', sisa <= 0);
        }

        buttons.forEach(function(button) {
            button.addEventListener('click', function() {
                resetError();

                modalIdKmLab.value = button.getAttribute('data-id-km-lab');
                modalKategoriKm.textContent = button.getAttribute('data-kategori') || '-';
                modalSubKategoriKm.textContent = button.getAttribute('data-sub-kategori') || '-';
                modalKeteranganKm.textContent = button.getAttribute('data-keterangan') || '-';
                modalSisaTotal.textContent = button.getAttribute('data-sisa-total') || '0';

                for (let tw = 1; tw <= 4; tw++) {
                    const sisa = parseInt(
                        button.getAttribute('data-sisa-tw' + tw) || 0
                    );

                    const tenggat = button.getAttribute(
                        'data-tenggat-tw' + tw
                    ) || '-';

                    setTriwulanInput(tw, sisa, tenggat);
                }

                updateTotalPembagian();
            });
        });

        Object.values(inputTriwulan).forEach(function(input) {
            input.addEventListener('input', function() {
                const max = parseInt(input.max || 0);
                const value = parseInt(input.value || 0);

                if (value > max) {
                    input.value = max;
                }

                if (value < 0) {
                    input.value = 0;
                }

                updateTotalPembagian();
                resetError();
            });
        });

        form.addEventListener('submit', function(event) {
            const total = updateTotalPembagian();

            if (total <= 0) {
                event.preventDefault();

                modalError.textContent =
                    'Masukkan minimal satu jumlah KM pada salah satu Triwulan.';

                modalError.classList.remove('d-none');
            }
        });
    });
</script>
@endsection
