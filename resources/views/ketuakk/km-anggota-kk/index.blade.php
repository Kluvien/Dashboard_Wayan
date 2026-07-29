@extends('layouts.app')

@section('title', 'KM Anggota KK')

@section('content')
@php
    $dataAnggota = collect($dataAnggota ?? []);
    $kategoriDefault = $kategoriDefault ?? ['Penelitian', 'Publikasi', 'Pengabdian', 'Penunjang'];
    $jumlahKolomAnggota = count($kategoriDefault) + 8;
@endphp

<style>
    .ketuakk-member-km {
        overflow: hidden;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        background: #FFFFFF;
    }

    .ketuakk-member-km__header {
        padding: 20px 22px 18px;
        border-bottom: 1px solid #EEF2F7;
    }

    .ketuakk-member-km__eyebrow {
        margin-bottom: 5px;
        color: #2563EB;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .ketuakk-member-km__title {
        margin: 0;
        color: #0F172A;
        font-size: 23px;
        font-weight: 700;
        letter-spacing: -.02em;
        line-height: 1.25;
        text-wrap: balance;
    }

    .ketuakk-member-km__description {
        max-width: 65ch;
        margin: 7px 0 0;
        color: #64748B;
        font-size: 13px;
        line-height: 1.55;
    }

    .ketuakk-member-km__toolbar {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        padding: 14px 22px;
        border-bottom: 1px solid #E2E8F0;
        background: #F8FAFC;
    }

    .ketuakk-member-km__filter {
        display: flex;
        align-items: flex-end;
        gap: 8px;
    }

    .ketuakk-member-km__filter-field {
        min-width: 124px;
    }

    .ketuakk-member-km__filter-label {
        display: block;
        margin-bottom: 5px;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
    }

    .ketuakk-member-km__filter-select {
        min-width: 124px;
    }

    .ketuakk-member-km__back {
        white-space: nowrap;
    }

    .km-table {
        min-width: 1250px;
    }

    table.ketuakk-member-km__table {
        width: 100%;
        margin: 0;
        border: 0 !important;
        border-radius: 0 !important;
        color: #334155;
    }

    .ketuakk-member-km__table > thead > tr > th {
        padding: 11px 16px !important;
        border: 0 !important;
        border-bottom: 1px solid #CBD5E1 !important;
        background: #F8FAFC !important;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .035em;
        line-height: 1.35;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    .ketuakk-member-km__table > tbody > tr {
        min-height: 56px;
    }

    .ketuakk-member-km__table > tbody > tr > td {
        height: 56px;
        padding: 11px 16px !important;
        border: 0 !important;
        border-bottom: 1px solid #EEF2F7 !important;
        background: #FFFFFF;
        color: #334155;
        font-size: 13px;
        font-weight: 500;
        vertical-align: middle;
    }

    .ketuakk-member-km__table > tbody > tr:last-child > td {
        border-bottom: 0 !important;
    }

    .ketuakk-member-km__table > tbody > tr:hover > td {
        background: #F8FAFC;
    }

    .ketuakk-member-km__group-header {
        border-right: 1px solid #CBD5E1 !important;
        border-left: 1px solid #CBD5E1 !important;
        text-align: center;
    }

    .ketuakk-member-km__cell--index {
        text-align: center;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .ketuakk-member-km__cell--identity {
        min-width: 180px;
        max-width: 260px;
        text-align: left;
    }

    .ketuakk-member-km__identity-primary {
        color: #0F172A;
        font-weight: 700;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .ketuakk-member-km__cell--nidn {
        text-align: left;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .ketuakk-member-km__cell--jad {
        min-width: 110px;
        text-align: left;
    }

    .ketuakk-member-km__jad {
        display: inline-block;
        padding: 3px 7px;
        border: 1px solid #E2E8F0;
        border-radius: 4px;
        background: #F8FAFC;
        color: #475569;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.35;
    }

    .ketuakk-member-km__cell--email {
        min-width: 190px;
        max-width: 260px;
        color: #64748B !important;
        white-space: nowrap;
    }

    .ketuakk-member-km__cell--lab {
        min-width: 170px;
        max-width: 250px;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .ketuakk-member-km__cell--number {
        text-align: right;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .ketuakk-member-km__cell--action {
        text-align: center;
        white-space: nowrap;
    }

    .ketuakk-member-km__detail {
        padding: 5px 10px;
        border-color: #CBD5E1;
        color: #1D4ED8;
        font-size: 12px;
        font-weight: 600;
    }

    .ketuakk-member-km__detail:hover {
        border-color: #2563EB;
        background: #EFF6FF;
        color: #1D4ED8;
    }

    .ketuakk-member-km__detail:focus-visible {
        outline: 3px solid rgba(37, 99, 235, .22);
        outline-offset: 2px;
        box-shadow: none;
    }

    .ketuakk-member-km__detail:disabled {
        border-color: #E2E8F0;
        background: #F8FAFC;
        color: #94A3B8;
        opacity: 1;
    }

    .ketuakk-member-km__empty {
        padding: 24px 16px !important;
        color: #64748B !important;
        font-size: 13px;
        font-weight: 500 !important;
        line-height: 1.5;
        text-align: center;
    }

    .table-scroll-container {
        overflow-x: auto;
        overflow-y: hidden;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .table-scroll-container::-webkit-scrollbar {
        display: none;
    }

    .sticky-col {
        position: sticky;
        left: 0;
        z-index: 2;
    }

    .sticky-col-2 {
        position: sticky;
        left: 60px;
        z-index: 2;
    }

    .ketuakk-member-km__table thead .sticky-col,
    .ketuakk-member-km__table thead .sticky-col-2 {
        z-index: 4;
        background: #F8FAFC !important;
    }

    .ketuakk-member-km__table tbody .sticky-col,
    .ketuakk-member-km__table tbody .sticky-col-2 {
        background: #FFFFFF;
    }

    .ketuakk-member-km__table tbody tr:hover .sticky-col,
    .ketuakk-member-km__table tbody tr:hover .sticky-col-2 {
        background: #F8FAFC;
    }

    .floating-table-scroll {
        position: fixed;
        left: 320px;
        right: 32px;
        bottom: 16px;
        height: 18px;
        overflow-x: auto;
        overflow-y: hidden;
        background: #ffffff;
        border: 1px solid #E5E7EB;
        border-radius: 999px;
        z-index: 999;
        display: none;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
    }

    .floating-table-scroll-inner {
        height: 1px;
    }

    @media (max-width: 992px) {
        .floating-table-scroll {
            left: 16px;
            right: 16px;
        }
    }

    @media (max-width: 575.98px) {
        .ketuakk-member-km__header,
        .ketuakk-member-km__toolbar {
            padding-right: 16px;
            padding-left: 16px;
        }
    }
    .ketuakk-member-km__table { min-width: 0; table-layout: fixed; }
    .ketuakk-member-km__table > thead > tr > th { padding: 11px 14px; background: #EEF2F6; color: #374151; font-size: 13px; }
    .ketuakk-member-km__table > tbody > tr > td { padding: 11px 14px; color: #374151; font-size: 14px; line-height: 1.5; overflow-wrap: anywhere; }
    .ketuakk-member-km__identity-meta { margin-top: 3px; color: #5B6472; font-size: 13px; }
    .ketuakk-member-km__category-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 5px 14px; }
    .ketuakk-member-km__category-list > div { display: flex; justify-content: space-between; gap: 8px; }
    .ketuakk-member-km__category-list strong { font-variant-numeric: tabular-nums; color: #1F2937; }
    @media (max-width: 767.98px) {
        .ketuakk-member-km__category-list { grid-template-columns: 1fr; }
    }
</style>

<section class="ketuakk-member-km" aria-labelledby="ketuakk-member-km-title">
    <header class="ketuakk-member-km__header">
        <div class="ketuakk-member-km__eyebrow">Kontrak Manajemen</div>
        <h1 id="ketuakk-member-km-title" class="ketuakk-member-km__title">
            KM Anggota KK Tahun {{ $tahun }}
        </h1>
        <p class="ketuakk-member-km__description">
            Menampilkan jumlah Kontrak Manajemen anggota KK berdasarkan kategori KM.
        </p>
    </header>

    <div class="ketuakk-member-km__toolbar">
        <form method="GET" action="/ketuakk/km-anggota-kk" class="ketuakk-member-km__filter">
            <div class="ketuakk-member-km__filter-field">
                <label for="ketuakk-member-km-tahun" class="ketuakk-member-km__filter-label">
                    Tahun KM
                </label>

                <select
                    id="ketuakk-member-km-tahun"
                    name="tahun"
                    class="form-select form-select-sm ketuakk-member-km__filter-select">
                    @foreach($tahunOptions ?? [$tahun] as $itemTahun)
                        <option value="{{ $itemTahun }}" {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                            {{ $itemTahun }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-sm btn-outline-primary">
                Filter
            </button>
        </form>

        <a href="/ketuakk/dashboard" class="btn btn-outline-secondary ketuakk-member-km__back">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
            Kembali
        </a>
    </div>

    <div class="table-responsive ketuakk-member-km__table-scroll">
            <table class="table align-middle km-table ketuakk-member-km__table">
                <thead>
                    <tr>
                        <th scope="col" class="ketuakk-member-km__cell--index">No</th>
                        <th scope="col" class="ketuakk-member-km__cell--identity">Identitas</th>
                        <th scope="col" class="ketuakk-member-km__cell--lab">Unit</th>
                        <th scope="col">KM per Kategori</th>
                        <th scope="col" class="ketuakk-member-km__cell--number">Total</th>
                        <th scope="col" class="ketuakk-member-km__cell--action">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($dataAnggota as $index => $item)
                    <tr>
                        <td class="ketuakk-member-km__cell--index">{{ $index + 1 }}</td>

                        <td class="ketuakk-member-km__cell--identity">
                            <div class="ketuakk-member-km__identity-primary">
                                {{ $item['nama_dosen'] }}
                            </div>
                            <div class="ketuakk-member-km__identity-meta">NIDN: {{ $item['nidn'] }}</div>
                            <div class="ketuakk-member-km__identity-meta">{{ $item['email'] }}</div>
                        </td>

                        <td class="ketuakk-member-km__cell--lab">
                            <div>{{ $item['nama_lab'] }}</div>
                            <span class="ketuakk-member-km__jad">
                                {{ $item['jad'] }}
                            </span>
                        </td>

                        <td>
                            <div class="ketuakk-member-km__category-list">
                                @foreach($kategoriDefault as $kategori)
                                    <div><span>{{ $kategori }}</span><strong>{{ $item['jumlah_km'][$kategori] ?? 0 }}</strong></div>
                                @endforeach
                            </div>
                        </td>

                        <td class="ketuakk-member-km__cell--number">
                            {{ $item['total_km'] ?? 0 }}
                        </td>

                        <td class="ketuakk-member-km__cell--action">
                            @if(!empty($item['id_user']))
                                <a
                                    href="/ketuakk/km-anggota-kk/{{ $item['id_user'] }}?tahun={{ $tahun }}&periode=triwulan"
                                    class="btn btn-sm btn-outline-primary ketuakk-member-km__detail">
                                    Detail
                                </a>
                            @else
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary ketuakk-member-km__detail"
                                    disabled>
                                    Detail
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="ketuakk-member-km__empty">
                            Belum ada data anggota KK.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
    </div>
</section>
@endsection
