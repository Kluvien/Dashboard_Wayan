@extends('layouts.app')

@section('title', 'Aktivitas KM')

@section('content')
@php
    $targetKmSaya = collect($targetKmSaya ?? []);
    $aktivitas = collect($aktivitas ?? []);
    $riwayatRealisasi = collect($riwayatRealisasi ?? $aktivitas);
    $kategoriCards = collect($kategoriCards ?? []);

    $namaAnggota = $namaAnggota ?? auth()->user()->username ?? 'Anggota';
    $tahun = (int) ($tahun ?? now()->year);
    $periode = $periode ?? 'tahun';
    $triwulan = (int) ($triwulan ?? 1);
    $semester = (int) ($semester ?? 1);

    $tahunOptions = collect($tahunOptions ?? [$tahun])
        ->map(fn ($item) => (int) $item)
        ->unique()
        ->sortDesc()
        ->values();

    $labelPeriode = $labelPeriode ?? ('Tahunan ' . $tahun);
    $keteranganPeriode = $keteranganPeriode ?? 'Data target dan realisasi ditampilkan untuk satu tahun penuh.';

    $labelStatusAktivitas = function ($status) {
        return match($status) {
            'Accepted' => 'Disetujui',
            'Submitted' => 'Diajukan',
            'On Progress' => 'Sedang Berjalan',
            'Rejected' => 'Ditolak',
            'Pending' => 'Menunggu Verifikasi',
            'Belum Mulai' => 'Belum Mulai',
            default => $status ?: '-',
        };
    };

    $classStatusAktivitas = function ($status) {
        return match($status) {
            'Accepted' => 'bg-success',
            'Submitted' => 'bg-primary',
            'On Progress', 'Pending' => 'bg-warning text-dark',
            'Rejected' => 'bg-danger',
            default => 'bg-secondary',
        };
    };

    $formatTriwulanDitambahkan = function ($tanggal) {
        if (empty($tanggal)) {
            return '-';
        }

        try {
            $bulan = \Carbon\Carbon::parse($tanggal)->month;
            return 'Triwulan ' . (int) ceil($bulan / 3);
        } catch (\Throwable $e) {
            return '-';
        }
    };
@endphp

<style>
    .aktivitas-km-table th,
    .aktivitas-km-table td,
    .target-km-table th,
    .target-km-table td,
    .history-activity-table th,
    .history-activity-table td {
        vertical-align: middle;
        font-size: 13px;
        white-space: nowrap;
    }

    .target-km-table,
    .aktivitas-km-table,
    .history-activity-table {
        min-width: 1250px;
    }

    .target-km-table td.detail-target,
    .aktivitas-km-table td.activity-title,
    .history-activity-table td.history-title {
        min-width: 240px;
        white-space: normal;
    }

    .page-filter-form {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
        margin: 0;
    }

    .page-filter-control {
        height: 38px;
        min-width: 122px;
        padding: 0 11px;
        border: 1px solid #CBD5E1;
        border-radius: 10px;
        background: #FFFFFF;
        color: #1E293B;
        font-size: 13px;
        font-weight: 700;
    }

    .page-filter-control.year {
        min-width: 102px;
    }

    .page-filter-form .btn {
        min-height: 38px;
        padding: 0 15px;
        font-size: 13px;
        font-weight: 800;
    }

    .period-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
        padding: 5px 9px;
        border: 1px solid #DBEAFE;
        border-radius: 999px;
        background: #EFF6FF;
        color: #2563EB;
        font-size: 12px;
        font-weight: 700;
    }

    .summary-total {
        font-size: 13px;
        white-space: nowrap;
    }

    .km-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .km-summary-card {
        min-height: 258px;
        overflow: hidden;
        padding: 15px;
        border: 1px solid #E2E8F0;
        border-top: 4px solid #5D8EF8;
        border-radius: 16px;
        background: linear-gradient(180deg, #FFFFFF 0%, #F9FBFF 100%);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .045);
    }

    .km-summary-title-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .km-summary-title {
        color: #334155;
        font-size: 14px;
        font-weight: 800;
    }

    .km-summary-subtitle {
        margin-top: 2px;
        color: #94A3B8;
        font-size: 10px;
        font-weight: 700;
    }

    .km-summary-percent {
        flex: 0 0 auto;
        min-width: 58px;
        padding: 7px 9px;
        border: 1px solid #D7E6FF;
        border-radius: 12px;
        background: #EDF4FF;
        color: #2563EB;
        font-size: 20px;
        font-weight: 900;
        line-height: 1;
        text-align: center;
    }

    .km-summary-progress {
        height: 8px;
        overflow: hidden;
        margin: 12px 0;
        border-radius: 999px;
        background: #E8EDF5;
    }

    .km-summary-progress-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #477EF7, #77A2FF);
    }

    .km-summary-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .km-summary-info {
        min-height: 56px;
        padding: 9px 10px;
        border: 1px solid #E2E8F0;
        border-radius: 11px;
        background: #F8FAFC;
    }

    .km-summary-info.target {
        border-color: #BFDBFE;
        background: #EFF6FF;
    }

    .km-summary-info.realisasi {
        border-color: #BBF7D0;
        background: #ECFDF5;
    }

    .km-summary-info.sisa-alert {
        border-color: #FECACA;
        background: #FEF2F2;
    }

    .km-summary-info.sisa-safe {
        border-color: #BBF7D0;
        background: #ECFDF5;
    }

    .km-summary-info.subkategori {
        border-color: #DDD6FE;
        background: #F5F3FF;
    }

    .km-summary-label {
        color: #64748B;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .km-summary-value {
        margin-top: 3px;
        color: #0F172A;
        font-size: 19px;
        font-weight: 900;
        line-height: 1;
    }

    .km-summary-info.target .km-summary-value { color: #2563EB; }
    .km-summary-info.realisasi .km-summary-value { color: #059669; }
    .km-summary-info.sisa-alert .km-summary-value { color: #DC2626; }
    .km-summary-info.sisa-safe .km-summary-value { color: #15803D; }
    .km-summary-info.subkategori .km-summary-value { color: #7C3AED; }

    .km-summary-note {
        margin: 11px 0 12px;
        min-height: 26px;
        font-size: 10px;
        font-weight: 800;
        line-height: 1.35;
    }

    .km-summary-note.success { color: #15803D; }
    .km-summary-note.danger { color: #DC2626; }
    .km-summary-note.secondary { color: #64748B; }

    .km-summary-card .btn {
        width: 100%;
        padding: 7px 10px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 800;
    }

    .target-group-header {
        background: #F3F6FB !important;
        text-align: center;
        font-weight: 800 !important;
    }

    .target-tw-start {
        border-left: 2px solid #CBD5E1 !important;
    }

    .target-tw-end {
        border-right: 2px solid #CBD5E1 !important;
    }

    .target-detail-category {
        margin-bottom: 7px;
        color: #0F172A;
        font-size: 14px;
        font-weight: 800;
    }

    .target-detail-row {
        display: flex;
        gap: 7px;
        margin-top: 4px;
        color: #64748B;
        font-size: 12px;
        line-height: 1.4;
    }

    .target-detail-row strong {
        min-width: 88px;
        color: #475569;
    }

    .target-deadline-list {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 7px;
    }

    .target-deadline {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 6px;
        border: 1px solid #DBEAFE;
        border-radius: 7px;
        background: #F8FAFC;
        color: #334155;
        font-size: 10px;
        font-weight: 700;
    }

    .target-period-number {
        color: #1D4ED8;
        font-size: 15px;
        font-weight: 800;
        text-align: center;
    }

    .target-realisasi-number {
        color: #059669;
        font-size: 15px;
        font-weight: 800;
        text-align: center;
    }

    .target-period-empty {
        color: #94A3B8;
        font-weight: 700;
        text-align: center;
    }

    .target-summary-number {
        font-size: 16px;
        font-weight: 800;
        text-align: center;
    }

    .target-summary-primary { color: #1D4ED8; }
    .target-summary-success { color: #15803D; }
    .target-summary-warning { color: #D97706; }

    .target-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 11px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .target-status-success { background: #DCFCE7; color: #15803D; }
    .target-status-warning { background: #FEF3C7; color: #B45309; }
    .target-status-danger { background: #FEE2E2; color: #B91C1C; }
    .target-status-secondary { background: #E2E8F0; color: #64748B; }

    .empty-state {
        padding: 28px;
        color: #64748B;
        text-align: center;
    }

    @media (max-width: 1200px) {
        .km-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 576px) {
        .km-summary-grid {
            grid-template-columns: 1fr;
        }

        .page-filter-form {
            justify-content: flex-start;
            width: 100%;
        }

        .page-filter-control,
        .page-filter-form .btn {
            flex: 1 1 100%;
            width: 100%;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <div class="page-heading mb-0">
        Aktivitas <span class="muted">KM</span>
    </div>

    <a href="/anggota/aktivitas-km/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Aktivitas
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ========================================================= --}}
{{-- RINGKASAN KM NAMA ANGGOTA --}}
{{-- ========================================================= --}}
<div id="ringkasan-km" class="card mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Ringkasan KM {{ $namaAnggota }}</h4>
            <p class="text-muted mb-0">
                Ringkasan target, realisasi disetujui, sisa target, dan capaian KM pada {{ $labelPeriode }}.
            </p>
            <span class="period-badge">
                <i class="bi bi-calendar3"></i>
                {{ $keteranganPeriode }}
            </span>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 flex-wrap">
            <div class="summary-total text-muted">
                Total:
                <strong>{{ $totalTarget ?? 0 }}</strong> target ·
                <strong class="text-success">{{ $totalRealisasi ?? 0 }}</strong> realisasi ·
                <strong class="text-warning">{{ $persentaseTotal ?? 0 }}%</strong> capaian
            </div>

            <form method="GET" action="{{ route('anggota.aktivitas-km.index') }}" class="page-filter-form">
                <select name="periode" id="periodeRingkasan" class="page-filter-control">
                    <option value="tahun" {{ $periode === 'tahun' ? 'selected' : '' }}>Tahunan</option>
                    <option value="triwulan" {{ $periode === 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                    <option value="semester" {{ $periode === 'semester' ? 'selected' : '' }}>Semester</option>
                </select>

                <select name="tahun" class="page-filter-control year">
                    @foreach($tahunOptions as $itemTahun)
                        <option value="{{ $itemTahun }}" {{ $tahun === (int) $itemTahun ? 'selected' : '' }}>
                            {{ $itemTahun }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="triwulan"
                    id="triwulanRingkasan"
                    class="page-filter-control year {{ $periode === 'triwulan' ? '' : 'd-none' }}">
                    @for($tw = 1; $tw <= 4; $tw++)
                        <option value="{{ $tw }}" {{ $triwulan === $tw ? 'selected' : '' }}>
                            Triwulan {{ $tw }}
                        </option>
                    @endfor
                </select>

                <select
                    name="semester"
                    id="semesterRingkasan"
                    class="page-filter-control year {{ $periode === 'semester' ? '' : 'd-none' }}">
                    <option value="1" {{ $semester === 1 ? 'selected' : '' }}>Semester 1</option>
                    <option value="2" {{ $semester === 2 ? 'selected' : '' }}>Semester 2</option>
                </select>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel-fill me-1"></i> Terapkan
                </button>
            </form>
        </div>
    </div>

    @include('partials.periode-saat-ini')
@include('partials.filter-diterapkan')

<div class="km-summary-grid">
        @forelse($kategoriCards as $card)
            @php
                $sisaClass = (int) ($card['sisa'] ?? 0) > 0 ? 'sisa-alert' : 'sisa-safe';
                $noteClass = $card['catatan_class'] ?? 'secondary';
            @endphp

            <div class="km-summary-card">
                <div class="km-summary-title-row">
                    <div>
                        <div class="km-summary-title">{{ $card['kategori'] ?? '-' }}</div>
                        <div class="km-summary-subtitle">Progress realisasi kategori KM</div>
                    </div>

                    <div class="km-summary-percent">{{ $card['persentase'] ?? 0 }}%</div>
                </div>

                <div class="km-summary-progress">
                    <div class="km-summary-progress-fill" style="width: {{ min((int) ($card['persentase'] ?? 0), 100) }}%;"></div>
                </div>

                <div class="km-summary-info-grid">
                    <div class="km-summary-info target">
                        <div class="km-summary-label">Target</div>
                        <div class="km-summary-value">{{ $card['target'] ?? 0 }}</div>
                    </div>

                    <div class="km-summary-info realisasi">
                        <div class="km-summary-label">Realisasi</div>
                        <div class="km-summary-value">{{ $card['realisasi'] ?? 0 }}</div>
                    </div>

                    <div class="km-summary-info {{ $sisaClass }}">
                        <div class="km-summary-label">Sisa</div>
                        <div class="km-summary-value">{{ $card['sisa'] ?? 0 }}</div>
                    </div>

                    <div class="km-summary-info subkategori">
                        <div class="km-summary-label">Sub Kategori</div>
                        <div class="km-summary-value">{{ $card['jumlah_subkategori'] ?? 0 }}</div>
                    </div>
                </div>

                <div class="km-summary-note {{ $noteClass }}">
                    @if(($card['catatan_class'] ?? '') === 'success')
                        <i class="bi bi-check-circle-fill me-1"></i>
                    @elseif(($card['catatan_class'] ?? '') === 'danger')
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    @else
                        <i class="bi bi-info-circle-fill me-1"></i>
                    @endif
                    {{ $card['catatan'] ?? '-' }}
                </div>

                <a href="#daftar-target-km" class="btn btn-primary">Lihat Detail</a>
            </div>
        @empty
            <div class="text-muted">Belum ada ringkasan KM pada {{ $labelPeriode }}.</div>
        @endforelse
    </div>
</div>

{{-- ========================================================= --}}
{{-- DAFTAR TARGET KM NAMA ANGGOTA --}}
{{-- ========================================================= --}}
<div id="daftar-target-km" class="card mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Daftar Target KM {{ $namaAnggota }}</h4>
            <p class="text-muted mb-0">
                Menampilkan target tahunan yang telah dibagikan oleh Ketua Lab, beserta target dan realisasi per triwulan.
            </p>
            <span class="period-badge">
                <i class="bi bi-calendar3"></i>
                Menampilkan data tahun {{ $tahun }}
            </span>
        </div>

        <form method="GET" action="{{ route('anggota.aktivitas-km.index') }}" class="page-filter-form">
            <input type="hidden" name="periode" value="{{ $periode }}">
            <input type="hidden" name="triwulan" value="{{ $triwulan }}">
            <input type="hidden" name="semester" value="{{ $semester }}">

            <select name="tahun" class="page-filter-control year" aria-label="Pilih tahun target">
                @foreach($tahunOptions as $itemTahun)
                    <option value="{{ $itemTahun }}" {{ $tahun === (int) $itemTahun ? 'selected' : '' }}>
                        {{ $itemTahun }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-funnel-fill me-1"></i> Filter
            </button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 target-km-table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Tahun</th>
                    <th rowspan="2">Detail Target KM</th>

                    <th colspan="4" class="target-group-header">Target KM per Triwulan</th>
                    <th colspan="4" class="target-group-header">Realisasi KM per Triwulan</th>

                    <th rowspan="2">Total Target</th>
                    <th rowspan="2">Total Realisasi</th>
                    <th rowspan="2">Sisa</th>
                    <th rowspan="2">Status</th>
                </tr>

                <tr>
                    @for($tw = 1; $tw <= 4; $tw++)
                        <th class="text-center {{ $tw === 1 ? 'target-tw-start' : '' }} {{ $tw === 4 ? 'target-tw-end' : '' }}">
                            TW {{ $tw }}
                        </th>
                    @endfor

                    @for($tw = 1; $tw <= 4; $tw++)
                        <th class="text-center {{ $tw === 1 ? 'target-tw-start' : '' }} {{ $tw === 4 ? 'target-tw-end' : '' }}">
                            TW {{ $tw }}
                        </th>
                    @endfor
                </tr>
            </thead>

            <tbody>
                @forelse($targetKmSaya as $index => $target)
                    @php
                        $targetTriwulan = [
                            1 => (int) ($target->target_tw_1 ?? 0),
                            2 => (int) ($target->target_tw_2 ?? 0),
                            3 => (int) ($target->target_tw_3 ?? 0),
                            4 => (int) ($target->target_tw_4 ?? 0),
                        ];

                        $realisasiTriwulan = [
                            1 => (int) ($target->realisasi_tw_1 ?? 0),
                            2 => (int) ($target->realisasi_tw_2 ?? 0),
                            3 => (int) ($target->realisasi_tw_3 ?? 0),
                            4 => (int) ($target->realisasi_tw_4 ?? 0),
                        ];

                        $tenggat = [
                            1 => $target->tanggal_selesai_tw1 ?? null,
                            2 => $target->tanggal_selesai_tw2 ?? null,
                            3 => $target->tanggal_selesai_tw3 ?? null,
                            4 => $target->tanggal_selesai_tw4 ?? null,
                        ];

                        $statusClass = match($target->status_class ?? 'secondary') {
                            'success' => 'target-status-success',
                            'warning' => 'target-status-warning',
                            'danger' => 'target-status-danger',
                            default => 'target-status-secondary',
                        };
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $target->tahun_km ?? '-' }}</td>

                        <td class="detail-target">
                            <div class="target-detail-category">{{ $target->kategori_km ?? '-' }}</div>

                            <div class="target-detail-row">
                                <strong>Sub Kategori:</strong>
                                <span>{{ $target->sub_kategori_km ?? '-' }}</span>
                            </div>

                            <div class="target-detail-row">
                                <strong>Keterangan:</strong>
                                <span>{{ $target->keterangan ?? '-' }}</span>
                            </div>

                            <div class="target-deadline-list">
                                @foreach($tenggat as $nomorTw => $tanggalTenggat)
                                    @if(!empty($tanggalTenggat))
                                        <span class="target-deadline">
                                            <i class="bi bi-calendar-event"></i>
                                            TW{{ $nomorTw }}: {{ \Carbon\Carbon::parse($tanggalTenggat)->format('d/m/Y') }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </td>

                        @foreach($targetTriwulan as $nomorTw => $jumlah)
                            <td class="{{ $nomorTw === 1 ? 'target-tw-start' : '' }} {{ $nomorTw === 4 ? 'target-tw-end' : '' }}">
                                <div class="{{ $jumlah > 0 ? 'target-period-number' : 'target-period-empty' }}">
                                    {{ $jumlah > 0 ? $jumlah : '-' }}
                                </div>
                            </td>
                        @endforeach

                        @foreach($realisasiTriwulan as $nomorTw => $jumlah)
                            <td class="{{ $nomorTw === 1 ? 'target-tw-start' : '' }} {{ $nomorTw === 4 ? 'target-tw-end' : '' }}">
                                <div class="{{ $jumlah > 0 ? 'target-realisasi-number' : 'target-period-empty' }}">
                                    {{ $jumlah > 0 ? $jumlah : '-' }}
                                </div>
                            </td>
                        @endforeach

                        <td>
                            <div class="target-summary-number target-summary-primary">{{ $target->jumlah_km ?? 0 }}</div>
                        </td>

                        <td>
                            <div class="target-summary-number target-summary-success">{{ $target->total_realisasi ?? 0 }}</div>
                        </td>

                        <td>
                            <div class="target-summary-number target-summary-warning">{{ $target->sisa_km ?? 0 }}</div>
                        </td>

                        <td>
                            <span class="target-status {{ $statusClass }}">
                                {{ $target->status_target ?? 'Belum Ada Target' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15">
                            <div class="empty-state">
                                Belum ada target KM yang dibagikan kepada {{ $namaAnggota }} pada tahun {{ $tahun }}.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ========================================================= --}}
{{-- DAFTAR AKTIVITAS KM NAMA ANGGOTA --}}
{{-- ========================================================= --}}
<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Daftar Aktivitas KM {{ $namaAnggota }}</h4>
            <p class="text-muted mb-0">
                Menampilkan aktivitas KM pada tahun {{ $tahun }}. Upload bukti aktivitas berupa PDF, PNG, JPG, atau JPEG.
                Jika berupa link, ubah terlebih dahulu menjadi format PDF.
            </p>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0 aktivitas-km-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tahun</th>
                    <th>Kategori</th>
                    <th>Sub Kategori</th>
                    <th>Judul Aktivitas</th>
                    <th>Status</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Selesai</th>
                    <th>Ditambahkan di Triwulan</th>
                    <th>Bukti</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($aktivitas as $index => $item)
                    @php
                        $status = $item->status_progress ?? 'On Progress';
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ !empty($item->tanggal_mulai) ? \Carbon\Carbon::parse($item->tanggal_mulai)->format('Y') : '-' }}</td>
                        <td>{{ $item->kategori_km ?? '-' }}</td>
                        <td>{{ $item->sub_kategori_km ?? '-' }}</td>

                        <td class="activity-title">
                            <strong>{{ $item->judul_aktivitas ?? '-' }}</strong>
                            @if(!empty($item->deskripsi_singkat))
                                <div class="small text-muted">
                                    {{ \Illuminate\Support\Str::limit($item->deskripsi_singkat, 80) }}
                                </div>
                            @endif
                        </td>

                        <td>
                            <span class="badge {{ $classStatusAktivitas($status) }}">
                                {{ $labelStatusAktivitas($status) }}
                            </span>

                            @if($status === 'Rejected' && !empty($item->catatan_verifikasi))
                                <div class="small text-danger mt-1" style="white-space: normal; max-width: 180px;">
                                    {{ \Illuminate\Support\Str::limit($item->catatan_verifikasi, 90) }}
                                </div>
                            @endif
                        </td>

                        <td>{{ !empty($item->tanggal_mulai) ? \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') : '-' }}</td>
                        <td>{{ !empty($item->tanggal_selesai) ? \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $formatTriwulanDitambahkan($item->created_at ?? $item->tanggal_mulai ?? null) }}</td>

                        <td>
                            @if(!empty($item->bukti_pdf_path) || !empty($item->bukti_file_path))
                                <a href="/bukti-km/{{ $item->id_aktivitas }}/download" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download me-1"></i> Download
                                </a>
                            @elseif(!empty($item->bukti_link))
                                <a href="{{ $item->bukti_link }}" target="_blank" class="btn btn-sm btn-outline-primary">Link</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @if(in_array($status, ['Accepted', 'Rejected'], true))
                                    <a href="/anggota/aktivitas-km/{{ $item->id_aktivitas }}/detail" class="btn btn-sm btn-outline-primary">
                                        Detail
                                    </a>
                                @endif

                                @if($status === 'Submitted')
                                    <span class="small text-primary fw-semibold">
                                        <i class="bi bi-hourglass-split me-1"></i> Menunggu verifikasi Ketua Lab
                                    </span>
                                @elseif($status === 'Accepted')
                                    <span class="small text-success fw-semibold">
                                        <i class="bi bi-lock-fill me-1"></i> Terkunci setelah disetujui
                                    </span>
                                @else
                                    <a href="/anggota/aktivitas-km/{{ $item->id_aktivitas }}/edit" class="btn btn-edit btn-sm">Edit</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            Belum ada aktivitas KM pada tahun {{ $tahun }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ========================================================= --}}
{{-- RIWAYAT REALISASI KM NAMA ANGGOTA --}}
{{-- ========================================================= --}}
<div id="riwayat-realisasi-km" class="card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Riwayat Realisasi KM {{ $namaAnggota }}</h4>
            <p class="text-muted mb-0">
                Riwayat aktivitas KM yang telah diinput pada tahun {{ $tahun }}, termasuk aktivitas yang masih diproses.
            </p>
        </div>

        <div class="small text-muted">
            Total aktivitas: <strong>{{ $riwayatRealisasi->count() }}</strong>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 history-activity-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori</th>
                    <th>Sub Kategori</th>
                    <th>Judul Aktivitas</th>
                    <th>Status</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Selesai</th>
                    <th>Ditambahkan di Triwulan</th>
                    <th>Update Terakhir</th>
                </tr>
            </thead>

            <tbody>
                @forelse($riwayatRealisasi as $index => $item)
                    @php
                        $status = $item->status_progress ?? 'On Progress';
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $item->kategori_km ?? '-' }}</td>
                        <td>{{ $item->sub_kategori_km ?? '-' }}</td>

                        <td class="history-title">
                            <strong>{{ $item->judul_aktivitas ?? '-' }}</strong>
                            @if(!empty($item->deskripsi_singkat))
                                <div class="small text-muted">
                                    {{ \Illuminate\Support\Str::limit($item->deskripsi_singkat, 100) }}
                                </div>
                            @endif
                        </td>

                        <td>
                            <span class="badge {{ $classStatusAktivitas($status) }}">
                                {{ $labelStatusAktivitas($status) }}
                            </span>
                        </td>

                        <td>{{ !empty($item->tanggal_mulai) ? \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') : '-' }}</td>
                        <td>{{ !empty($item->tanggal_selesai) ? \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $formatTriwulanDitambahkan($item->created_at ?? $item->tanggal_mulai ?? null) }}</td>
                        <td>{{ !empty($item->updated_at) ? \Carbon\Carbon::parse($item->updated_at)->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            Belum ada riwayat realisasi KM pada tahun {{ $tahun }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const periode = document.getElementById('periodeRingkasan');
        const triwulan = document.getElementById('triwulanRingkasan');
        const semester = document.getElementById('semesterRingkasan');

        if (!periode || !triwulan || !semester) {
            return;
        }

        function sinkronkanFilterPeriode() {
            triwulan.classList.toggle('d-none', periode.value !== 'triwulan');
            semester.classList.toggle('d-none', periode.value !== 'semester');
        }

        periode.addEventListener('change', sinkronkanFilterPeriode);
        sinkronkanFilterPeriode();
    });
</script>
@endsection
