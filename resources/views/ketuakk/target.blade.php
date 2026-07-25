@extends('layouts.app')

@section('title', 'Kelola Target KM')

@section('content')
@php
    $formatDueDate = function ($tanggal) {
        if (empty($tanggal)) {
            return '-';
        }

        return \Carbon\Carbon::parse($tanggal)->format('d/m/Y');
    };
@endphp

<style>
    .target-table th,
    .target-table td {
        vertical-align: middle;
        font-size: 13px;
        white-space: nowrap;
    }

    .target-table th {
        font-weight: 800;
        text-transform: uppercase;
    }

    .target-table th,
    .target-table td {
        border-bottom: 1px solid #E5E7EB !important;
    }

    .target-group-header,
    .due-group-header {
        background: #F3F6FB !important;
        text-align: center;
        font-weight: 800 !important;
    }

    .target-group-header {
        border-left: 2px solid #CBD5E1 !important;
        border-right: 2px solid #CBD5E1 !important;
    }

    .due-group-header {
        border-left: 2px solid #CBD5E1 !important;
        border-right: 2px solid #CBD5E1 !important;
    }

    .target-start {
        border-left: 2px solid #CBD5E1 !important;
    }

    .target-end {
        border-right: 2px solid #CBD5E1 !important;
    }

    .due-start {
        border-left: 2px solid #CBD5E1 !important;
    }

    .due-end {
        border-right: 2px solid #CBD5E1 !important;
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
    }

    .due-date-value i {
        color: #477EF7;
    }

    .due-date-empty {
        color: #94A3B8;
        font-weight: 700;
    }
</style>

<div class="page-heading">
    Kelola <span class="muted">Target KM</span>
</div>

<div class="card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Daftar Target Kontrak Manajemen</h4>
            <p class="text-muted mb-0">
                Halaman ini digunakan untuk mengelola target tahunan berdasarkan kategori, jenis KM, triwulan, keterangan, dan due date.
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <form method="GET" action="/ketuakk/target-km" class="d-flex gap-2">
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

            <a href="/ketuakk/km-kk?tahun={{ $tahun }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Kembali
            </a>

            <a href="/ketuakk/target-km/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Tambah Target
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 target-table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Tahun</th>
                    <th rowspan="2">Kategori KM</th>
                    <th rowspan="2">Jenis KM / Sub Kategori</th>
                    <th rowspan="2">Keterangan</th>

                    <th colspan="4" class="target-group-header">
                        Target KM per Triwulan
                    </th>

                    <th colspan="4" class="due-group-header">
                        Tenggat per Triwulan
                    </th>

                    <th rowspan="2">Total</th>
                    <th rowspan="2">Aksi</th>
                </tr>

                <tr>
                    <th class="text-center target-start">TW 1</th>
                    <th class="text-center">TW 2</th>
                    <th class="text-center">TW 3</th>
                    <th class="text-center target-end">TW 4</th>

                    <th class="text-center due-start">TW 1</th>
                    <th class="text-center">TW 2</th>
                    <th class="text-center">TW 3</th>
                    <th class="text-center due-end">TW 4</th>
                </tr>
            </thead>

            <tbody>
                @forelse($targets as $index => $target)
                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td>{{ $target->tahun_km }}</td>

                        <td class="fw-bold">
                            {{ $target->kategori_km }}
                        </td>

                        <td>{{ $target->indikator }}</td>

                        <td style="white-space: normal; min-width: 170px;">
                            {{ $target->keterangan ?? '-' }}
                        </td>

                        <td class="text-center target-start">
                            {{ $target->triwulan_1 }}
                        </td>

                        <td class="text-center">
                            {{ $target->triwulan_2 }}
                        </td>

                        <td class="text-center">
                            {{ $target->triwulan_3 }}
                        </td>

                        <td class="text-center target-end">
                            {{ $target->triwulan_4 }}
                        </td>

                        @foreach([1, 2, 3, 4] as $tw)
                            @php
                                $tanggalSelesai = $target->{'tanggal_selesai_tw' . $tw} ?? null;
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
                            {{ $target->target }}
                        </td>

                        <td>
                            <div class="d-flex gap-2">
                                <a
                                    href="/ketuakk/target-km/{{ $target->id_target }}/edit"
                                    class="btn btn-primary btn-sm">
                                    Edit
                                </a>

                                <form
                                    action="/ketuakk/target-km/{{ $target->id_target }}"
                                    method="POST"
                                    class="js-delete-form"
                                    data-message="Apakah Anda yakin ingin menghapus target KM ini?">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-delete btn-sm">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" class="text-center text-muted py-4">
                            Belum ada data target KM untuk tahun {{ $tahun }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection