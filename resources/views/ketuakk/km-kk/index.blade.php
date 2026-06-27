@extends('layouts.app')

@section('title', 'KM Kelompok Keahlian')

@section('content')
@php
$targetRows = collect($targetRows ?? []);
$rekapLab = collect($rekapLab ?? []);
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

    .tw-header {
        text-align: center;
        background: #F3F6FB !important;
        font-weight: 800;
        border-left: 2px solid #CBD5E1 !important;
        border-right: 2px solid #CBD5E1 !important;
    }

    .tw-start {
        border-left: 2px solid #CBD5E1 !important;
    }

    .tw-end {
        border-right: 2px solid #CBD5E1 !important;
    }

    .period-cell {
        text-align: center;
        font-weight: 700;
    }
</style>

<div class="page-heading">
    Kontrak Manajemen <span class="muted">Kelompok Keahlian</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Pencapaian KM Kelompok Keahlian</h4>
            <p class="text-muted mb-0">
                Rekap target Kontrak Manajemen Kelompok Keahlian tahun {{ $tahun }}.
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <form method="GET" action="/ketuakk/km-kk" class="d-flex gap-2">
                <select name="tahun" class="form-select" style="min-width: 120px;">
                    @foreach($tahunOptions ?? [$tahun] as $itemTahun)
                    <option value="{{ $itemTahun }}" {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                        {{ $itemTahun }}
                    </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-primary">
                    Filter
                </button>
            </form>

            <a href="/ketuakk/target-km" class="btn btn-primary">
                Kelola Target KM
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table" style="width: 100%;">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 5%;">No</th>
                    <th rowspan="2" style="width: 14%;">Kategori KM</th>
                    <th rowspan="2" style="width: 18%;">Jenis KM</th>
                    <th rowspan="2" style="width: 23%;">Sub Kategori</th>
                    <th colspan="4" class="tw-header">Target KM per Triwulan</th>
                    <th rowspan="2" style="width: 10%;">Total Target</th>
                </tr>
                <tr>
                    <th class="period-cell tw-start">Triwulan 1</th>
                    <th class="period-cell">Triwulan 2</th>
                    <th class="period-cell">Triwulan 3</th>
                    <th class="period-cell tw-end">Triwulan 4</th>
                </tr>
            </thead>

            <tbody>
                @forelse($targetRows as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $item['kategori_km'] ?? '-' }}
                    </td>

                    <td>
                        {{ $item['jenis_km'] ?? '-' }}
                    </td>

                    <td>
                        {{ $item['sub_kategori_km'] ?? '-' }}
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

                    <td class="fw-bold text-center">
                        {{ $item['total_target'] ?? 0 }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        Belum ada data target KM Kelompok Keahlian pada tahun ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Rangkuman Pencapaian Lab Riset</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Lab Riset</th>
                    <th>Target</th>
                    <th>Realisasi</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rekapLab as $index => $lab)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $lab['nama_lab'] }}
                    </td>

                    <td>
                        {{ $lab['target'] }}
                    </td>

                    <td>
                        {{ $lab['realisasi'] }}
                    </td>

                    <td style="min-width: 180px;">
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" style="width: {{ $lab['persentase'] }}%;"></div>
                        </div>
                        <div class="small mt-1">{{ $lab['persentase'] }}%</div>
                    </td>

                    <td>
                        @if($lab['status'] === 'Tercapai')
                        <span class="badge bg-success">Tercapai</span>
                        @else
                        <span class="badge bg-warning text-dark">Belum Tercapai</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Belum ada data lab riset.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection