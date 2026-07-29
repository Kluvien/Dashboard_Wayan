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
        width: 100%;
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
    .ketuakk-targets__records { border-top: 1px solid #D5DCE5; }
    .ketuakk-targets__record + .ketuakk-targets__record { border-top: 1px solid #E5EAF0; }
    .ketuakk-targets__record-header { display: grid; grid-template-columns: minmax(0, 1fr) auto auto; gap: 20px; align-items: center; padding: 16px 22px; background: #F3F6F9; }
    .ketuakk-targets__record-identity { display: flex; gap: 12px; align-items: flex-start; min-width: 0; }
    .ketuakk-targets__record-index { display: inline-grid; place-items: center; min-width: 30px; height: 30px; border: 1px solid #D5DCE5; border-radius: 8px; color: #5B6472; font-size: 13px; font-weight: 700; }
    .ketuakk-targets__record-title { margin: 0; color: #1F2937; font-size: 17px; font-weight: 700; line-height: 1.4; }
    .ketuakk-targets__record-subtitle { margin: 3px 0 0; color: #5B6472; font-size: 14px; line-height: 1.5; }
    .ketuakk-targets__record-metrics { display: flex; gap: 20px; margin: 0; }
    .ketuakk-targets__record-metrics div { min-width: 84px; }
    .ketuakk-targets__record-metrics dt { color: #5B6472; font-size: 13px; font-weight: 600; }
    .ketuakk-targets__record-metrics dd { margin: 2px 0 0; color: #1F2937; font-size: 14px; font-weight: 700; font-variant-numeric: tabular-nums; }
    .ketuakk-targets__record-body { display: grid; grid-template-columns: minmax(180px, .7fr) minmax(480px, 1.5fr); gap: 22px; padding: 16px 22px 20px; }
    .ketuakk-targets__record-note span { color: #5B6472; font-size: 13px; font-weight: 700; }
    .ketuakk-targets__record-note p { margin: 5px 0 0; color: #374151; font-size: 14px; line-height: 1.55; overflow-wrap: anywhere; }
    .ketuakk-targets__period-table { width: 100%; border-collapse: collapse; color: #374151; font-size: 14px; }
    .ketuakk-targets__period-table th,
    .ketuakk-targets__period-table td { padding: 10px 12px; border-bottom: 1px solid #E5EAF0; vertical-align: middle; }
    .ketuakk-targets__period-table thead th { background: #EEF2F6; color: #374151; font-size: 13px; font-weight: 700; text-align: left; }
    .ketuakk-targets__period-table tbody th { color: #1F2937; font-weight: 600; }
    .ketuakk-targets__period-table td:nth-child(2) { text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; }
    .ketuakk-targets__period-table td:nth-child(n+3) { font-variant-numeric: tabular-nums; white-space: nowrap; }
    @media (max-width: 900px) {
        .ketuakk-targets__record-header { grid-template-columns: 1fr; }
        .ketuakk-targets__record-body { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .ketuakk-targets__record-header,
        .ketuakk-targets__record-body { padding-left: 16px; padding-right: 16px; }
        .ketuakk-targets__record-metrics { flex-wrap: wrap; }
        .ketuakk-targets__period-table th,
        .ketuakk-targets__period-table td { padding: 9px 7px; }
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

    <div class="ketuakk-targets__records">
        @forelse($targets as $index => $target)
            <article class="ketuakk-targets__record">
                <header class="ketuakk-targets__record-header">
                    <div class="ketuakk-targets__record-identity">
                        <span class="ketuakk-targets__record-index">{{ $index + 1 }}</span>
                        <div>
                            <h2 class="ketuakk-targets__record-title">{{ $target->kategori_km }}</h2>
                            <p class="ketuakk-targets__record-subtitle">{{ $target->indikator }}</p>
                        </div>
                    </div>
                    <dl class="ketuakk-targets__record-metrics">
                        <div><dt>Tahun</dt><dd>{{ $target->tahun_km }}</dd></div>
                        <div><dt>Total target</dt><dd>{{ $target->target }}</dd></div>
                    </dl>
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
                </header>
                <div class="ketuakk-targets__record-body">
                    <div class="ketuakk-targets__record-note">
                        <span>Keterangan</span>
                        <p>{{ $target->keterangan ?? '-' }}</p>
                    </div>
                    <table class="ketuakk-targets__period-table">
                        <thead>
                            <tr>
                                <th scope="col">Triwulan</th>
                                <th scope="col">Target</th>
                                <th scope="col">Tanggal Mulai</th>
                                <th scope="col">Tanggal Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach([1, 2, 3, 4] as $tw)
                                @php
                                    $tanggalMulai = $target->{'tanggal_mulai_tw' . $tw} ?? null;
                                    $tanggalSelesai = $target->{'tanggal_selesai_tw' . $tw} ?? null;
                                @endphp
                                <tr>
                                    <th scope="row">TW {{ $tw }}</th>
                                    <td>{{ $target->{'triwulan_' . $tw} ?? 0 }}</td>
                                    <td>{{ !empty($tanggalMulai) ? $formatDueDate($tanggalMulai) : '-' }}</td>
                                    <td>{{ !empty($tanggalSelesai) ? $formatDueDate($tanggalSelesai) : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        @empty
            <div class="ketuakk-targets__empty">Belum ada data target KM untuk tahun {{ $tahun }}.</div>
        @endforelse
    </div>
</section>
@endsection
