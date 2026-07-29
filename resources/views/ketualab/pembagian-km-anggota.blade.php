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
    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 11px;
        border-radius: 6px;
        font-size: 13px;
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
    .ketualab-assignment__section-title { margin: 0; color: #1F2937; font-size: 18px; font-weight: 700; }
    .ketualab-assignment__section-description { margin: 5px 0 0; color: #64748B; font-size: 13px; }
    .ketualab-assignment__records { min-width: 0; }
    .ketualab-assignment__record { padding: 18px 22px; border-bottom: 1px solid #E5EAF0; }
    .ketualab-assignment__record:last-child { border-bottom: 0; }
    .ketualab-assignment__record-header { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 18px; align-items: start; margin-bottom: 14px; }
    .ketualab-assignment__record-title { margin: 0; color: #1F2937; font-size: 18px; font-weight: 700; line-height: 1.4; overflow-wrap: anywhere; }
    .ketualab-assignment__record-subtitle { margin: 4px 0 0; color: #5B6472; font-size: 14px; line-height: 1.5; overflow-wrap: anywhere; }
    .ketualab-assignment__record-description { margin: 10px 0 0; color: #374151; font-size: 14px; line-height: 1.5; overflow-wrap: anywhere; }
    .ketualab-assignment__record-summary { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px 18px; margin: 0; }
    .ketualab-assignment__record-summary div { min-width: 82px; }
    .ketualab-assignment__record-summary dt { color: #5B6472; font-size: 13px; font-weight: 600; }
    .ketualab-assignment__record-summary dd { margin: 2px 0 0; color: #1F2937; font-size: 16px; font-weight: 700; font-variant-numeric: tabular-nums; }
    .ketualab-assignment__record-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 12px; }
    .ketualab-assignment__period-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .ketualab-assignment__period-table th,
    .ketualab-assignment__period-table td { padding: 10px 12px; border-bottom: 1px solid #E5EAF0; color: #374151; font-size: 14px; line-height: 1.5; overflow-wrap: anywhere; }
    .ketualab-assignment__period-table thead th { background: #EEF2F6; color: #374151; font-size: 13px; font-weight: 700; text-align: left; }
    .ketualab-assignment__period-table tbody th { color: #1F2937; font-weight: 700; }
    .ketualab-assignment__period-table td:nth-child(2),
    .ketualab-assignment__period-table td:nth-child(3),
    .ketualab-assignment__period-table td:nth-child(4) { text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; }
    .ketualab-assignment__history-table,
    .ketualab-assignment__member-table { width: 100%; table-layout: fixed; }
    .ketualab-assignment__history-table th,
    .ketualab-assignment__history-table td,
    .ketualab-assignment__member-table th,
    .ketualab-assignment__member-table td { padding: 10px 12px; color: #374151; font-size: 14px; line-height: 1.5; overflow-wrap: anywhere; vertical-align: top; }
    .ketualab-assignment__history-table th,
    .ketualab-assignment__member-table th { background: #EEF2F6; color: #374151; font-size: 13px; font-weight: 700; }
    .ketualab-assignment__identity { color: #1F2937; font-weight: 700; }
    .ketualab-assignment__meta { margin-top: 3px; color: #5B6472; font-size: 13px; }
    .ketualab-assignment__values { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 3px 12px; margin: 0; }
    .ketualab-assignment__values dt { color: #5B6472; font-size: 13px; font-weight: 500; }
    .ketualab-assignment__values dd { margin: 0; color: #1F2937; font-size: 14px; font-weight: 700; text-align: right; font-variant-numeric: tabular-nums; }
    .ketualab-assignment__table { width: 100%; margin: 0; }
    .ketualab-assignment__table > thead > tr > th { padding: 11px 16px; background: #EEF2F6; border: 0; border-bottom: 1px solid #D5DCE5; color: #374151; font-size: 13px; font-weight: 700; vertical-align: middle; }
    .ketualab-assignment__table > tbody > tr > td { height: auto; padding: 10px 14px; border: 0; border-bottom: 1px solid #E5EAF0; color: #374151; font-size: 14px; vertical-align: middle; }
    .ketualab-assignment__number { text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; }
    .ketualab-assignment__action { min-height: 40px; padding: 7px 11px; border-radius: 7px; font-size: 14px; font-weight: 700; }
    .ketualab-assignment__filter { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; min-width: 0; }
    .ketualab-assignment__filter .form-select { width: auto; min-width: 0; min-height: 44px; font-size: 14px; }
    .ketualab-assignment__filter .btn,
    .ketualab-assignment__record-actions .btn { min-height: 40px; font-size: 14px; }
    .ketualab-assignment__modal .form-select,
    .ketualab-assignment__modal .form-control { width: 100%; min-width: 0; min-height: 44px; font-size: 14px; }
    .ketualab-assignment__modal .form-label { font-size: 14px; }
    .ketualab-assignment__modal .modal-body { overflow-y: auto; overflow-x: visible; }

    .modal-km-info-item.full {
        grid-column: span 2;
    }

    .modal-km-info-label {
        font-size: 13px;
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
        font-size: 13px;
        font-weight: 800;
    }

    .modal-tw-tenggat {
        margin-bottom: 10px;
        color: #64748B;
        font-size: 13px;
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
        .ketualab-assignment__record { padding: 16px; }
        .ketualab-assignment__record-header { grid-template-columns: minmax(0, 1fr); }
        .ketualab-assignment__record-summary { justify-content: flex-start; }
        .ketualab-assignment__period-table { table-layout: auto; }
        .ketualab-assignment__period-table th,
        .ketualab-assignment__period-table td { padding: 9px 7px; }
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
            <form method="GET" action="/ketualab/penurunan-km" class="ketualab-assignment__filter">
                <select name="tahun" class="form-select">
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

    <div class="ketualab-assignment__records">
        @forelse($dataKmLab as $index => $km)
                    @php
                        $statusClass = match($km->status ?? 'Belum Ada KM') {
                            'Selesai' => 'status-success',
                            'Belum Selesai' => 'status-warning',
                            default => 'status-secondary',
                        };
                    @endphp
                    <article class="ketualab-assignment__record">
                        <header class="ketualab-assignment__record-header">
                            <div>
                                <h3 class="ketualab-assignment__record-title">
                                    {{ $index + 1 }}. {{ $km->kategori_km ?? '-' }}
                                </h3>
                                <p class="ketualab-assignment__record-subtitle">{{ $km->sub_kategori_display ?? '-' }}</p>
                                <p class="ketualab-assignment__record-description">{{ $km->keterangan ?? '-' }}</p>
                            </div>
                            <dl class="ketualab-assignment__record-summary">
                                <div><dt>Total KM</dt><dd>{{ $km->jumlah_km ?? 0 }}</dd></div>
                                <div><dt>Sudah Dibagi</dt><dd>{{ $km->sudah_assign ?? 0 }}</dd></div>
                                <div><dt>Total Sisa</dt><dd>{{ $km->sisa_km ?? 0 }}</dd></div>
                            </dl>
                        </header>
                        <table class="ketualab-assignment__period-table">
                            <thead><tr><th scope="col">Periode</th><th scope="col">KM Diterima</th><th scope="col">Sudah Dibagi</th><th scope="col">Sisa</th><th scope="col">Tenggat</th></tr></thead>
                            <tbody>
                                @foreach([1, 2, 3, 4] as $tw)
                                    @php
                                        $jumlahTw = (int) ($km->{'triwulan_' . $tw} ?? 0);
                                        $sudahDibagiTw = (int) ($km->{'sudah_assign_tw' . $tw} ?? 0);
                                        $sisaTw = (int) ($km->{'sisa_tw' . $tw} ?? 0);
                                        $tenggat = $km->{'tanggal_selesai_tw' . $tw} ?? null;
                                    @endphp
                                    <tr>
                                        <th scope="row">TW {{ $tw }}</th>
                                        <td>{{ $jumlahTw }}</td>
                                        <td>{{ $sudahDibagiTw }}</td>
                                        <td>{{ $sisaTw }}</td>
                                        <td>{{ $jumlahTw > 0 && !empty($tenggat) ? $formatTanggal($tenggat) : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="ketualab-assignment__record-actions">
                            <span class="status-pill {{ $statusClass }}">
                                {{ $km->status ?? 'Belum Ada KM' }}
                            </span>
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
                        </div>
                    </article>
        @empty
            <div class="empty-state">Belum ada KM yang diberikan oleh Ketua KK ke Lab ini.</div>
        @endforelse
    </div>
</section>

<section class="ketualab-assignment__section">
    <header class="ketualab-assignment__section-header">
        <div>
        <h2 class="ketualab-assignment__section-title">Riwayat Pembagian KM ke Anggota</h2>
        <p class="text-muted mb-0">
            Riwayat pembagian KM dari Ketua Lab kepada anggota Lab.
        </p>
        </div>
    </header>

    <div>
        <table class="table align-middle mb-0 ketualab-assignment__history-table">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Anggota</th>
                    <th scope="col">Detail KM</th>
                    <th scope="col">Pembagian</th>
                    <th scope="col">Waktu Pembagian</th>
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
                        <td>
                            <div class="ketualab-assignment__identity">{{ $assign->nama_dosen ?? $assign->username ?? '-' }}</div>
                            <div class="ketualab-assignment__meta">NIDN: {{ $assign->nidn ?? '-' }}</div>
                            <div class="ketualab-assignment__meta">JAD: {{ $assign->jad ?? 'AA' }}</div>
                        </td>
                        <td>
                            <div class="ketualab-assignment__identity">{{ $assign->kategori_km ?? '-' }}</div>
                            <div class="ketualab-assignment__meta">{{ $subKategori }}</div>
                            <div class="ketualab-assignment__meta">{{ $assign->keterangan ?? '-' }}</div>
                        </td>
                        <td>
                            <dl class="ketualab-assignment__values">
                                @foreach([1, 2, 3, 4] as $tw)
                                    @php
                                        $jumlahTw = (int) ($assign->{'triwulan_' . $tw} ?? 0);
                                    @endphp
                                    <dt>TW{{ $tw }}</dt><dd>{{ $jumlahTw }}</dd>
                                @endforeach
                                <dt>Total</dt><dd>{{ $assign->jumlah_km ?? 0 }}</dd>
                            </dl>
                        </td>
                        <td>
                            <div>{{ $formatTanggal($assign->created_at ?? null) }}</div>
                            <div class="ketualab-assignment__meta">{{ !empty($assign->created_at) ? \Carbon\Carbon::parse($assign->created_at)->format('H:i') : '-' }}</div>
                            <form
                                action="/ketualab/penurunan-km/assign/{{ $assign->id_km_anggota }}"
                                method="POST"
                                class="js-delete-form"
                                data-message="Apakah Anda yakin ingin menghapus pembagian KM anggota ini?">

                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="tahun" value="{{ $tahun }}">

                                <button type="submit" class="btn btn-delete btn-sm mt-2">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                Belum ada riwayat pembagian KM kepada anggota.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="ketualab-assignment__section">
    <header class="ketualab-assignment__section-header">
        <div>
        <h2 class="ketualab-assignment__section-title">Daftar Anggota Lab</h2>
        <p class="text-muted mb-0">
            Data anggota yang dapat menerima pembagian KM dari Ketua Lab.
        </p>
        </div>
    </header>

    <div>
        <table class="table align-middle mb-0 ketualab-assignment__member-table">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Identitas</th>
                    <th scope="col">Posisi</th>
                    <th scope="col">Bobot Saran</th>
                </tr>
            </thead>

            <tbody>
                @forelse($anggota as $index => $item)
                    @php
                        $jad = $item->jad ?? 'AA';
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td>
                            <div class="ketualab-assignment__identity">{{ $item->nama_dosen ?? $item->username }}</div>
                            <div class="ketualab-assignment__meta">NIDN: {{ $item->nidn ?? '-' }}</div>
                            <div class="ketualab-assignment__meta">{{ $item->email ?? '-' }}</div>
                        </td>
                        <td><div class="ketualab-assignment__identity">{{ $jad }}</div><div class="ketualab-assignment__meta">{{ $jadLabel[$jad] ?? 'Non-Jabatan Fungsional Akademik' }}</div></td>
                        <td class="ketualab-assignment__number">{{ $bobotJad[$jad] ?? 0.6 }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                Belum ada anggota pada Lab ini.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<div class="modal fade ketualab-assignment__modal" id="assignKmModal" tabindex="-1" aria-hidden="true">
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
