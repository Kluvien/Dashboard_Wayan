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
    .ketuakk-km-overview__panel { overflow:hidden; border:1px solid #E2E8F0; border-radius:14px; background:#FFF; margin-bottom:16px; }
    .ketuakk-km-overview__header { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; padding:20px 22px; border-bottom:1px solid #E2E8F0; flex-wrap:wrap; }
    .ketuakk-km-overview__title { margin:0; color:#0F172A; font-size:22px; font-weight:700; }
    .ketuakk-km-overview__description { margin:5px 0 0; color:#64748B; font-size:13px; }
    .ketuakk-km-overview__table { border:0!important; border-radius:0; }
    .ketuakk-km-overview__table th,.ketuakk-km-overview__table td { padding:11px 16px!important; border-bottom:1px solid #EEF2F7!important; }
    .km-table th {
        vertical-align: middle;
        font-size: 14px;
    }

    .km-table td {
        vertical-align: middle;
        font-size: 14px;
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
    .ketuakk-km-overview__records{border-top:1px solid #D5DCE5}.ketuakk-km-overview__record{border-bottom:1px solid #E5EAF0}.ketuakk-km-overview__record-header{display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:20px;align-items:center;padding:16px 20px;background:#F3F6F9}.ketuakk-km-overview__record-header h2{margin:2px 0;color:#1F2937;font-size:17px}.ketuakk-km-overview__record-header p{margin:0;color:#5B6472;font-size:14px}.ketuakk-km-overview__record-header dl{display:flex;gap:18px;margin:0}.ketuakk-km-overview__record-header dt,.ketuakk-km-overview__category-list dt,.ketuakk-km-overview__summary-list dt{color:#5B6472;font-size:13px}.ketuakk-km-overview__record-header dd,.ketuakk-km-overview__category-list dd,.ketuakk-km-overview__summary-list dd{margin:2px 0 0;color:#1F2937;font-size:14px;font-weight:700;font-variant-numeric:tabular-nums}.ketuakk-km-overview__record-body{padding:14px 20px 18px}.ketuakk-km-overview__record-body p{color:#374151;font-size:14px;line-height:1.5}.ketuakk-km-overview__period-table{width:100%;border-collapse:collapse;color:#374151;font-size:14px}.ketuakk-km-overview__period-table th,.ketuakk-km-overview__period-table td{padding:9px 12px;border-bottom:1px solid #E5EAF0}.ketuakk-km-overview__period-table thead th{background:#EEF2F6;font-size:13px;text-align:left}.ketuakk-km-overview__period-table td:nth-child(2){text-align:right;font-weight:700;font-variant-numeric:tabular-nums}.ketuakk-km-overview__category-list,.ketuakk-km-overview__summary-list{display:grid;gap:5px;margin:0}.ketuakk-km-overview__category-list div,.ketuakk-km-overview__summary-list div{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px}@media(max-width:850px){.ketuakk-km-overview__record-header{grid-template-columns:1fr}.ketuakk-km-overview__record-header dl{flex-wrap:wrap}}
</style>

<section class="ketuakk-km-overview__panel" aria-labelledby="km-overview-title">
    <header class="ketuakk-km-overview__header">
        <div>
            <h1 id="km-overview-title" class="ketuakk-km-overview__title">Kontrak Manajemen Kelompok Keahlian</h1>
            <p class="ketuakk-km-overview__description">
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
    </header>

    <div class="ketuakk-km-overview__records">
                @forelse($targetRows as $index => $item)
                    <article class="ketuakk-km-overview__record">
                        <header class="ketuakk-km-overview__record-header">
                            <div><span>{{ $index + 1 }} · Tahun {{ $tahun }}</span><h2>{{ $item['kategori_km'] ?? '-' }}</h2><p>{{ $item['jenis_km'] ?? '-' }} · {{ $item['sub_kategori_km'] ?? '-' }}</p></div>
                            <dl><div><dt>Total target</dt><dd>{{ $item['total_target'] ?? 0 }}</dd></div><div><dt>Sudah didistribusikan</dt><dd>{{ $item['sudah_turun'] ?? 0 }}</dd></div><div><dt>Sisa</dt><dd>{{ $item['sisa_belum_turun'] ?? 0 }}</dd></div></dl>
                            <div class="ketuakk-km-overview__record-action">
                            @if(($item['sisa_belum_turun'] ?? 0) > 0)
                                <a
                                    href="/ketuakk/km-lab-riset/create?id_target={{ $item['id_target'] }}"
                                    class="btn btn-primary btn-sm">
                                    <i class="bi bi-arrow-down-circle me-1"></i>
                                    Distribusikan KM
                                </a>
                            @else
                                <button class="btn btn-secondary btn-sm" disabled>
                                    Sudah Habis
                                </button>
                            @endif
                            </div>
                        </header>
                        <div class="ketuakk-km-overview__record-body">
                            <p><strong>Keterangan:</strong> {{ $item['keterangan'] ?? '-' }}</p>
                            <table class="ketuakk-km-overview__period-table"><thead><tr><th scope="col">Periode</th><th scope="col">Target</th><th scope="col">Tanggal Mulai</th><th scope="col">Tanggal Selesai</th></tr></thead><tbody>
                                @foreach([1,2,3,4] as $tw)
                                    <tr><th scope="row">TW {{ $tw }}</th><td>{{ $item['triwulan_'.$tw] ?? 0 }}</td><td>{{ !empty($item['tanggal_mulai_tw'.$tw]) ? $formatDueDate($item['tanggal_mulai_tw'.$tw]) : '-' }}</td><td>{{ !empty($item['tanggal_selesai_tw'.$tw]) ? $formatDueDate($item['tanggal_selesai_tw'.$tw]) : '-' }}</td></tr>
                                @endforeach
                            </tbody></table>
                        </div>
                    </article>
                @empty
                    <div class="text-center text-muted py-4">Belum ada data target KM Kelompok Keahlian pada tahun ini.</div>
                @endforelse
    </div>
</section>

<div class="card">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Daftar Lab Riset</h4>
            <p class="text-muted mb-0">
                Ketua KK dapat melihat distribusi KM ke setiap Lab Riset dan membuka detail pembagian KM anggota.
            </p>
        </div>

        <div class="small text-muted">
            Total Lab: {{ $rekapLab->count() }}
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table">
            <thead>
                <tr><th>No</th><th>Lab Riset</th><th>Distribusi per Kategori</th><th>Ringkasan</th><th>Aksi</th></tr>
            </thead>

            <tbody>
                @forelse($rekapLab as $index => $lab)
                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td class="fw-bold">
                            {{ $lab['nama_lab'] ?? '-' }}
                        </td>

                        <td><dl class="ketuakk-km-overview__category-list">@foreach($kategoriDefault as $kategori)<div><dt>{{ $kategori }}</dt><dd>{{ $lab['jumlah_per_kategori'][$kategori] ?? 0 }}</dd></div>@endforeach</dl></td>
                        <td><dl class="ketuakk-km-overview__summary-list"><div><dt>Total didistribusikan</dt><dd>{{ $lab['total_turun'] ?? 0 }}</dd></div><div><dt>Sudah dibagi</dt><dd>{{ $lab['sudah_dibagi_ke_anggota'] ?? 0 }}</dd></div><div><dt>Sisa KM</dt><dd>{{ $lab['sisa_km'] ?? 0 }}</dd></div></dl>
                            <div class="progress-thin">
                                <div
                                    class="progress-bar"
                                    role="progressbar"
                                    aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ min(max((int)($lab['progress'] ?? 0),0),100) }}"
                                    aria-label="Progress {{ $lab['nama_lab'] ?? 'lab' }} {{ $lab['progress'] ?? 0 }} persen"
                                    style="width: {{ min(max((int)($lab['progress'] ?? 0),0),100) }}%;">
                                </div>
                            </div>
                            <div class="small mt-1">{{ $lab['progress'] ?? 0 }}%</div>
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
                        <td colspan="5" class="text-center text-muted py-4">
                            Belum ada data Lab Riset.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
