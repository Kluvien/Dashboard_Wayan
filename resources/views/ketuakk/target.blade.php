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
    .ketuakk-targets {
        overflow: hidden;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        background: #FFFFFF;
    }

    .ketuakk-targets__header {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 22px;
        align-items: start;
        padding: 20px 22px;
        border-bottom: 1px solid #E2E8F0;
    }

    .ketuakk-targets__heading {
        max-width: 680px;
    }

    .ketuakk-targets__eyebrow {
        margin-bottom: 6px;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
    }

    .ketuakk-targets__title {
        margin: 0;
        color: #0F172A;
        font-size: 22px;
        font-weight: 700;
        letter-spacing: -.025em;
        line-height: 1.25;
    }

    .ketuakk-targets__description {
        max-width: 65ch;
        margin: 6px 0 0;
        color: #64748B;
        font-size: 13px;
        line-height: 1.55;
    }

    .ketuakk-targets__toolbar {
        display: grid;
        gap: 10px;
        justify-items: end;
    }

    .ketuakk-targets__filter,
    .ketuakk-targets__actions {
        display: flex;
        align-items: flex-end;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .ketuakk-targets__filter-field {
        display: grid;
        gap: 5px;
    }

    .ketuakk-targets__filter-label {
        color: #475569;
        font-size: 11px;
        font-weight: 700;
    }

    .ketuakk-targets__filter-control {
        min-width: 120px;
        height: 38px;
        padding: 0 10px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        background: #FFFFFF;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
    }

    .ketuakk-targets__filter-control:focus-visible,
    .ketuakk-targets__header .btn:focus-visible {
        outline: 3px solid rgba(37, 99, 235, .22);
        outline-offset: 2px;
    }

    .ketuakk-targets__filter-submit {
        min-height: 38px;
        padding: 7px 11px;
        border-radius: 8px;
        font-size: 12px;
    }

    .ketuakk-targets__scroll {
        overflow-x: auto;
    }

    table.ketuakk-targets__table {
        width: 100%;
        min-width: 1480px;
        margin: 0;
        border: 0 !important;
        border-radius: 0;
        background: #FFFFFF;
    }

    .ketuakk-targets__table > thead > tr > th {
        height: 44px;
        padding: 10px 16px !important;
        border: 0 !important;
        border-bottom: 1px solid #E2E8F0 !important;
        background: #F8FAFC !important;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .045em;
        line-height: 1.35;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .ketuakk-targets__table > tbody > tr > td {
        height: 56px;
        padding: 11px 16px !important;
        border: 0 !important;
        border-bottom: 1px solid #EEF2F7 !important;
        background: #FFFFFF;
        color: #334155;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.45;
        vertical-align: middle;
    }

    .ketuakk-targets__table > tbody > tr:last-child > td {
        border-bottom: 0 !important;
    }

    .ketuakk-targets__table > tbody > tr:hover > td {
        background: #F8FAFC;
    }

    .ketuakk-targets__group-header {
        border-left: 1px solid #CBD5E1 !important;
        text-align: center;
    }

    .ketuakk-targets__group-start {
        border-left: 1px solid #CBD5E1 !important;
    }

    .ketuakk-targets__table > thead > tr > th.ketuakk-targets__group-header,
    .ketuakk-targets__table > thead > tr > th.ketuakk-targets__group-start,
    .ketuakk-targets__table > tbody > tr > td.ketuakk-targets__group-start {
        border-left: 1px solid #CBD5E1 !important;
    }

    .ketuakk-targets__cell--index,
    .ketuakk-targets__cell--year,
    .ketuakk-targets__cell--date,
    .ketuakk-targets__cell--action {
        text-align: center;
        white-space: nowrap;
    }

    .ketuakk-targets__table > thead > tr > th.ketuakk-targets__cell--action {
        position: sticky;
        right: 0;
        z-index: 3;
        min-width: 126px;
        border-left: 1px solid #CBD5E1 !important;
        background: #F8FAFC !important;
    }

    .ketuakk-targets__table > tbody > tr > td.ketuakk-targets__cell--action {
        position: sticky;
        right: 0;
        z-index: 2;
        min-width: 126px;
        border-left: 1px solid #CBD5E1 !important;
        background: #FFFFFF;
    }

    .ketuakk-targets__table > tbody > tr:hover > td.ketuakk-targets__cell--action {
        background: #F8FAFC;
    }

    .ketuakk-targets__cell--category {
        min-width: 130px;
        color: #0F172A !important;
        font-weight: 700 !important;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .ketuakk-targets__cell--subcategory {
        min-width: 190px;
        font-weight: 600 !important;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .ketuakk-targets__cell--description {
        min-width: 190px;
        color: #64748B !important;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .ketuakk-targets__cell--number {
        color: #0F172A !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
        text-align: right;
        white-space: nowrap;
    }

    .ketuakk-targets__table th.ketuakk-targets__cell--number {
        color: #64748B !important;
    }

    .ketuakk-targets__empty {
        padding: 24px 16px !important;
        color: #64748B !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        text-align: center;
    }

    .target-group-header,
    .due-group-header {
        text-align: center;
    }

    .target-group-header,
    .target-start,
    .due-group-header,
    .due-start {
        border-left: 1px solid #CBD5E1 !important;
    }

    .ketuakk-targets__date {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        line-height: 1.3;
        white-space: nowrap;
    }

    .ketuakk-targets__date i {
        color: #64748B;
        font-size: 11px;
    }

    .ketuakk-targets__date--empty {
        color: #94A3B8;
        font-size: 12px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .ketuakk-targets__row-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .ketuakk-targets__action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 48px;
        min-height: 30px;
        padding: 5px 8px;
        border: 1px solid;
        border-radius: 7px;
        background: #FFFFFF;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.2;
        text-decoration: none;
    }

    .ketuakk-targets__action--edit {
        border-color: #BFDBFE;
        color: #1D4ED8;
    }

    .ketuakk-targets__action--edit:hover {
        border-color: #93C5FD;
        background: #EFF6FF;
        color: #1D4ED8;
    }

    .ketuakk-targets__action--delete {
        border-color: #FECACA;
        color: #B91C1C;
    }

    .ketuakk-targets__action--delete:hover {
        border-color: #FCA5A5;
        background: #FEF2F2;
        color: #991B1B;
    }

    .ketuakk-targets__action:focus-visible {
        outline: 3px solid rgba(37, 99, 235, .22);
        outline-offset: 2px;
    }

    @media (max-width: 1200px) {
        .ketuakk-targets__header {
            grid-template-columns: 1fr;
        }

        .ketuakk-targets__toolbar {
            justify-items: start;
        }

        .ketuakk-targets__filter,
        .ketuakk-targets__actions {
            justify-content: flex-start;
        }
    }
</style>

<section class="ketuakk-targets" aria-labelledby="targetKmTitle">
    <header class="ketuakk-targets__header">
        <div class="ketuakk-targets__heading">
            <div class="ketuakk-targets__eyebrow">Kelola Target KM</div>
            <h1 class="ketuakk-targets__title" id="targetKmTitle">
                Daftar Target Kontrak Manajemen
            </h1>
            <p class="ketuakk-targets__description">
                Halaman ini digunakan untuk mengelola target tahunan berdasarkan kategori, jenis KM, triwulan, keterangan, dan due date.
            </p>
        </div>

        <div class="ketuakk-targets__toolbar">
            <form method="GET" action="/ketuakk/target-km" class="ketuakk-targets__filter">
                <label class="ketuakk-targets__filter-field">
                    <span class="ketuakk-targets__filter-label">Tahun</span>
                    <select name="tahun" class="ketuakk-targets__filter-control">
                        @foreach($tahunOptions as $itemTahun)
                            <option
                                value="{{ $itemTahun }}"
                                {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                                {{ $itemTahun }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <button type="submit" class="btn btn-outline-primary ketuakk-targets__filter-submit">
                    Filter
                </button>
            </form>

            <div class="ketuakk-targets__actions">
                <a href="/ketuakk/km-kk?tahun={{ $tahun }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Kembali
                </a>

                <a href="/ketuakk/target-km/create" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>
                    Tambah Target
                </a>
            </div>
        </div>
    </header>

    <div class="table-responsive ketuakk-targets__scroll">
        <table class="table align-middle mb-0 ketuakk-targets__table">
            <thead>
                <tr>
                    <th rowspan="2" class="ketuakk-targets__cell--index">No</th>
                    <th rowspan="2" class="ketuakk-targets__cell--year">Tahun</th>
                    <th rowspan="2">Kategori KM</th>
                    <th rowspan="2">Jenis KM / Sub Kategori</th>
                    <th rowspan="2">Keterangan</th>

                    <th colspan="4" class="target-group-header ketuakk-targets__group-header">
                        Target KM per Triwulan
                    </th>

                    <th colspan="4" class="due-group-header ketuakk-targets__group-header">
                        Tenggat per Triwulan
                    </th>

                    <th rowspan="2" class="ketuakk-targets__cell--number">Total</th>
                    <th rowspan="2" class="ketuakk-targets__cell--action">Aksi</th>
                </tr>

                <tr>
                    <th class="target-start ketuakk-targets__group-start ketuakk-targets__cell--number">TW 1</th>
                    <th class="ketuakk-targets__cell--number">TW 2</th>
                    <th class="ketuakk-targets__cell--number">TW 3</th>
                    <th class="ketuakk-targets__cell--number">TW 4</th>

                    <th class="text-center due-start ketuakk-targets__group-start">TW 1</th>
                    <th class="text-center">TW 2</th>
                    <th class="text-center">TW 3</th>
                    <th class="text-center">TW 4</th>
                </tr>
            </thead>

            <tbody>
                @forelse($targets as $index => $target)
                    <tr>
                        <td class="ketuakk-targets__cell--index">{{ $index + 1 }}</td>

                        <td class="ketuakk-targets__cell--year">{{ $target->tahun_km }}</td>

                        <td class="ketuakk-targets__cell--category">
                            {{ $target->kategori_km }}
                        </td>

                        <td class="ketuakk-targets__cell--subcategory">{{ $target->indikator }}</td>

                        <td class="ketuakk-targets__cell--description">
                            {{ $target->keterangan ?? '-' }}
                        </td>

                        <td class="target-start ketuakk-targets__group-start ketuakk-targets__cell--number">
                            {{ $target->triwulan_1 }}
                        </td>

                        <td class="ketuakk-targets__cell--number">
                            {{ $target->triwulan_2 }}
                        </td>

                        <td class="ketuakk-targets__cell--number">
                            {{ $target->triwulan_3 }}
                        </td>

                        <td class="ketuakk-targets__cell--number">
                            {{ $target->triwulan_4 }}
                        </td>

                        @foreach([1, 2, 3, 4] as $tw)
                            @php
                                $tanggalSelesai = $target->{'tanggal_selesai_tw' . $tw} ?? null;
                            @endphp

                            <td class="ketuakk-targets__cell--date {{ $tw === 1 ? 'due-start ketuakk-targets__group-start' : '' }}">
                                @if(!empty($tanggalSelesai))
                                    <span class="ketuakk-targets__date">
                                        <i class="bi bi-calendar-event"></i>
                                        {{ $formatDueDate($tanggalSelesai) }}
                                    </span>
                                @else
                                    <span class="ketuakk-targets__date--empty">-</span>
                                @endif
                            </td>
                        @endforeach

                        <td class="ketuakk-targets__cell--number">
                            {{ $target->target }}
                        </td>

                        <td class="ketuakk-targets__cell--action">
                            <div class="ketuakk-targets__row-actions">
                                <a
                                    href="/ketuakk/target-km/{{ $target->id_target }}/edit"
                                    class="ketuakk-targets__action ketuakk-targets__action--edit">
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
                                        class="ketuakk-targets__action ketuakk-targets__action--delete">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" class="ketuakk-targets__empty">
                            Belum ada data target KM untuk tahun {{ $tahun }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
