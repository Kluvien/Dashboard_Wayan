@extends('layouts.app')

@section('title', 'KM Kelompok Keahlian')

@section('content')
@php
    $targetRows = collect($targetRows ?? []);
    $rekapLab = collect($rekapLab ?? []);

    $kategoriDefault = $kategoriDefault ?? [
        'Penelitian',
        'Publikasi',
        'Pengabdian',
        'Penunjang',
    ];

    $formatDueDate = function ($tanggal) {
        if (empty($tanggal)) {
            return '-';
        }

        return \Carbon\Carbon::parse($tanggal)->format('d/m/Y');
    };
@endphp

<style>
    .km-table th {
        white-space: nowrap;
        vertical-align: middle;
        font-size: 13px;
    }

    .km-table td {
        vertical-align: middle;
        font-size: 13px;
    }

    .km-table th,
    .km-table td {
        border-bottom: 1px solid #E5E7EB !important;
    }

    .tw-header,
    .group-header,
    .due-header {
        text-align: center;
        background: #F3F6FB !important;
        font-weight: 800;
    }

    .tw-header {
        border-left: 2px solid #CBD5E1 !important;
        border-right: 2px solid #CBD5E1 !important;
    }

    .due-header {
        border-left: 2px solid #CBD5E1 !important;
        border-right: 2px solid #CBD5E1 !important;
    }

    .tw-start {
        border-left: 2px solid #CBD5E1 !important;
    }

    .tw-end {
        border-right: 2px solid #CBD5E1 !important;
    }

    .due-start {
        border-left: 2px solid #CBD5E1 !important;
    }

    .due-end {
        border-right: 2px solid #CBD5E1 !important;
    }

    .period-cell {
        text-align: center;
        font-weight: 700;
    }

    .due-date-cell {
        min-width: 105px;
        text-align: center;
    }

    .due-date-value {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 6px 8px;
        border-radius: 8px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .due-date-value i {
        color: #477EF7;
    }

    .due-date-empty {
        color: #94A3B8;
        font-weight: 700;
    }

    .progress-thin {
        height: 10px;
        background: #E9EEF5;
        border-radius: 999px;
        overflow: hidden;
    }

    .progress-thin .progress-bar {
        background: #4F7DF3;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        white-space: nowrap;
    }

    .status-warning {
        background: #FDE68A;
        color: #92400E;
    }

    .status-success {
        background: #D1FAE5;
        color: #065F46;
    }

    .status-secondary {
        background: #E5E7EB;
        color: #475569;
    }
</style>

<div class="page-heading">
    Kontrak Manajemen <span class="muted">Kelompok Keahlian</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Kontrak Manajemen Kelompok Keahlian</h4>
            <p class="text-muted mb-0">
                Rekap target Kontrak Manajemen Kelompok Keahlian tahun {{ $tahun }}.
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <form method="GET" action="/ketuakk/km-kk" class="d-flex gap-2">
                <select name="tahun" class="form-select" style="min-width: 120px;">
                    @foreach($tahunOptions ?? [$tahun] as $itemTahun)
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

            <a href="/ketuakk/target-km?tahun={{ $tahun }}" class="btn btn-secondary">
                <i class="bi bi-pencil-square me-1"></i>
                Kelola Target KM
            </a>

            <a href="/ketuakk/target-km/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Tambah Target
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Kategori KM</th>
                    <th rowspan="2">Jenis KM</th>
                    <th rowspan="2">Sub Kategori</th>
                    <th rowspan="2">Keterangan</th>

                    <th colspan="4" class="tw-header">
                        Target KM per Triwulan
                    </th>

                    <th colspan="4" class="due-header">
                        Tenggat per Triwulan
                    </th>

                    <th rowspan="2">Total Target</th>
                    <th rowspan="2">Sudah Turun</th>
                    <th rowspan="2">Sisa Belum Turun</th>
                    <th rowspan="2">Aksi</th>
                </tr>

                <tr>
                    <th class="period-cell tw-start">TW 1</th>
                    <th class="period-cell">TW 2</th>
                    <th class="period-cell">TW 3</th>
                    <th class="period-cell tw-end">TW 4</th>

                    <th class="period-cell due-start">TW 1</th>
                    <th class="period-cell">TW 2</th>
                    <th class="period-cell">TW 3</th>
                    <th class="period-cell due-end">TW 4</th>
                </tr>
            </thead>

            <tbody>
                @forelse($targetRows as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td class="fw-bold">
                            {{ $item['kategori_km'] ?? '-' }}
                        </td>

                        <td>{{ $item['jenis_km'] ?? '-' }}</td>

                        <td>{{ $item['sub_kategori_km'] ?? '-' }}</td>

                        <td style="min-width: 180px;">
                            {{ $item['keterangan'] ?? '-' }}
                        </td>

                        <td class="period-cell tw-start">
                            {{ $item['triwulan_1'] ?? 0 }}
                        </td>

                        <td class="period-cell">
                            {{ $item['triwulan_2'] ?? 0 }}
                        </td>

                        <td class="period-cell">
                            {{ $item['triwulan_3'] ?? 0 }}
                        </td>

                        <td class="period-cell tw-end">
                            {{ $item['triwulan_4'] ?? 0 }}
                        </td>

                        @foreach([1, 2, 3, 4] as $tw)
                            @php
                                $tanggalSelesai = $item['tanggal_selesai_tw' . $tw] ?? null;
                            @endphp

                            <td class="due-date-cell {{ $tw === 1 ? 'due-start' : '' }} {{ $tw === 4 ? 'due-end' : '' }}">
                                @if(!empty($tanggalSelesai))
                                    <span class="due-date-value">
                                        <i class="bi bi-calendar-event"></i>
                                        {{ $formatDueDate($tanggalSelesai) }}
                                    </span>
                                @else
                                    <span class="due-date-empty">-</span>
                                @endif
                            </td>
                        @endforeach

                        <td class="fw-bold text-center">
                            {{ $item['total_target'] ?? 0 }}
                        </td>

                        <td class="fw-bold text-center text-primary">
                            {{ $item['sudah_turun'] ?? 0 }}
                        </td>

                        <td class="fw-bold text-center text-warning">
                            {{ $item['sisa_belum_turun'] ?? 0 }}
                        </td>

                        <td class="text-center">
                            @if(($item['sisa_belum_turun'] ?? 0) > 0)
                                <a
                                    href="/ketuakk/km-lab-riset/create?id_target={{ $item['id_target'] }}"
                                    class="btn btn-primary btn-sm">
                                    <i class="bi bi-arrow-down-circle me-1"></i>
                                    Turunkan KM
                                </a>
                            @else
                                <button class="btn btn-secondary btn-sm" disabled>
                                    Sudah Habis
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="17" class="text-center text-muted py-4">
                            Belum ada data target KM Kelompok Keahlian pada tahun ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Daftar Lab Riset</h4>
            <p class="text-muted mb-0">
                Ketua KK dapat melihat penurunan KM ke setiap Lab Riset dan membuka detail pembagian KM anggota.
            </p>
        </div>

        <div class="small text-muted">
            Total Lab: {{ $rekapLab->count() }}
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Nama Lab Riset</th>

                    <th colspan="5" class="group-header">
                        KM Diturunkan ke Lab
                    </th>

                    <th rowspan="2">Total Turun</th>
                    <th rowspan="2">Sudah Dibagi ke Anggota</th>
                    <th rowspan="2">Sisa KM</th>
                    <th rowspan="2">Progress</th>
                    <th rowspan="2">Status</th>
                    <th rowspan="2">Aksi</th>
                </tr>

                <tr>
                    @foreach($kategoriDefault as $kategori)
                        <th class="text-center">
                            {{ strtoupper($kategori) }}
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @forelse($rekapLab as $index => $lab)
                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td class="fw-bold">
                            {{ $lab['nama_lab'] ?? '-' }}
                        </td>

                        @foreach($kategoriDefault as $kategori)
                            <td class="text-center fw-bold">
                                {{ $lab['jumlah_per_kategori'][$kategori] ?? 0 }}
                            </td>
                        @endforeach

                        <td class="text-center fw-bold">
                            {{ $lab['total_turun'] ?? 0 }}
                        </td>

                        <td class="text-center fw-bold">
                            {{ $lab['sudah_dibagi_ke_anggota'] ?? 0 }}
                        </td>

                        <td class="text-center fw-bold">
                            {{ $lab['sisa_km'] ?? 0 }}
                        </td>

                        <td style="min-width: 145px;">
                            <div class="progress-thin">
                                <div
                                    class="progress-bar"
                                    role="progressbar"
                                    style="width: {{ $lab['progress'] ?? 0 }}%;">
                                </div>
                            </div>

                            <div class="small mt-1 text-center">
                                {{ $lab['progress'] ?? 0 }}%
                            </div>
                        </td>

                        <td>
                            @if(($lab['status'] ?? '') === 'Selesai')
                                <span class="status-pill status-success">
                                    Selesai
                                </span>
                            @elseif(($lab['status'] ?? '') === 'Belum Selesai')
                                <span class="status-pill status-warning">
                                    Belum Selesai
                                </span>
                            @else
                                <span class="status-pill status-secondary">
                                    Belum Ada KM
                                </span>
                            @endif
                        </td>

                        <td>
                            <a
                                href="/ketuakk/km-lab-riset/{{ $lab['id_lab'] }}?tahun={{ $tahun }}"
                                class="btn btn-primary btn-sm">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="text-center text-muted py-4">
                            Belum ada data Lab Riset.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection