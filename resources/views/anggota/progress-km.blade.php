@extends('layouts.app')

@section('title', 'Progress KM Saya')

@section('content')
<style>
    .summary-card {
        border: 1px solid #E5E7EB;
        border-radius: 16px;
        padding: 18px;
        background: #fff;
        height: 100%;
    }

    .summary-label {
        color: #6B7280;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .summary-value {
        font-size: 28px;
        font-weight: 800;
        color: #111827;
        margin: 0;
    }

    .progress-soft {
        height: 10px;
        border-radius: 999px;
        background: #E5E7EB;
        overflow: hidden;
    }

    .progress-soft-fill {
        height: 100%;
        border-radius: 999px;
        background: #477EF7;
    }

    .km-table th {
        white-space: nowrap;
        vertical-align: middle;
        font-size: 13px;
    }

    .km-table td {
        vertical-align: middle;
        font-size: 13px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-belum {
        background: #F3F4F6;
        color: #4B5563;
    }

    .status-progress {
        background: #FFF4D6;
        color: #A66A00;
    }

    .status-submitted {
        background: #E8F1FF;
        color: #2563EB;
    }

    .status-accepted,
    .status-tercapai {
        background: #E8F8EF;
        color: #15803D;
    }

    .status-rejected {
        background: #FEECEC;
        color: #B91C1C;
    }

    .anggota-progress__overview, .anggota-progress__section { background: #FFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; }
    .anggota-progress__header { display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; padding: 20px 22px; }
    .anggota-progress__eyebrow { margin: 0 0 5px; color: #2563EB; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .anggota-progress__title { margin: 0; color: #0F172A; font-size: 24px; font-weight: 700; }
    .anggota-progress__metrics { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); border-top: 1px solid #EEF2F7; }
    .anggota-progress__metric { padding: 16px 22px; border-right: 1px solid #EEF2F7; }
    .anggota-progress__metric:last-child { border-right: 0; }
    .anggota-progress__metric-label { color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .anggota-progress__metric-value { display: block; margin-top: 5px; color: #0F172A; font-size: 24px; font-weight: 700; font-variant-numeric: tabular-nums; }
    .anggota-progress__bar { height: 6px; margin-top: 9px; overflow: hidden; border-radius: 999px; background: #E2E8F0; }
    .anggota-progress__bar-fill { height: 100%; background: #2563EB; }
    @media (max-width: 767.98px) { .anggota-progress__header { flex-direction: column; } .anggota-progress__metrics { grid-template-columns: repeat(2, 1fr); } }
</style>

<section class="anggota-progress__overview mb-4" aria-labelledby="anggota-progress-title">
    <header class="anggota-progress__header">
        <div>
            <p class="anggota-progress__eyebrow">Capaian Kontrak Manajemen</p>
            <h1 id="anggota-progress-title" class="anggota-progress__title">Progress KM Saya</h1>
            <p class="text-muted mb-0">
                Halaman ini menampilkan target KM yang diterima, aktivitas yang sudah diinput, dan status progres terbaru.
            </p>
        </div>

        <form method="GET" action="/anggota/progress-km" class="d-flex align-items-center gap-2">
            <label class="fw-bold mb-0">Tahun</label>
            <select name="tahun" class="form-select" style="min-width: 120px;" onchange="this.form.submit()">
                @foreach($tahunOptions as $itemTahun)
                <option value="{{ $itemTahun }}" {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                    {{ $itemTahun }}
                </option>
                @endforeach
            </select>
        </form>
        <a href="/anggota/dashboard" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </header>
    <div class="anggota-progress__metrics">
        <div class="anggota-progress__metric">
            <span class="anggota-progress__metric-label">Total Target</span>
            <span class="anggota-progress__metric-value">{{ $totalTarget }}</span>
        </div>
        <div class="anggota-progress__metric">
            <span class="anggota-progress__metric-label">Total Realisasi Accepted</span>
            <span class="anggota-progress__metric-value">{{ $totalRealisasi }}</span>
        </div>
        <div class="anggota-progress__metric">
            <span class="anggota-progress__metric-label">Sisa Target</span>
            <span class="anggota-progress__metric-value">{{ $totalSisa }}</span>
        </div>
        <div class="anggota-progress__metric">
            <span class="anggota-progress__metric-label">Persentase Capaian</span>
            <span class="anggota-progress__metric-value">{{ $persentaseTotal }}%</span>
            <div class="anggota-progress__bar" role="progressbar" aria-label="Persentase capaian KM" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $persentaseTotal }}">
                <div class="anggota-progress__bar-fill" style="width: {{ min($persentaseTotal, 100) }}%;"></div>
            </div>
        </div>
    </div>
</section>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Progress Per Kategori KM</h4>

    <div class="row g-3">
        @foreach($progressKategori as $item)
        <div class="col-md-4 col-lg-2">
            <div class="summary-card">
                <div class="summary-label">{{ $item['kategori'] }}</div>
                <div class="small text-muted mb-1">
                    Target: <strong>{{ $item['target'] }}</strong>
                </div>
                <div class="small text-muted mb-2">
                    Realisasi: <strong>{{ $item['realisasi'] }}</strong>
                </div>

                <div class="progress-soft">
                    <div class="progress-soft-fill" style="width: {{ $item['persentase'] }}%;"></div>
                </div>

                <div class="small fw-bold mt-2">
                    {{ $item['persentase'] }}%
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">Daftar KM yang Saya Terima</h4>
            <p class="text-muted mb-0">
                Tabel ini menampilkan KM yang ditugaskan kepada Anda beserta progres aktivitas terakhir.
            </p>
        </div>

        <a href="/anggota/aktivitas-km/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Update Aktivitas
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tahun</th>
                    <th>Kategori KM</th>
                    <th>Sub Kategori</th>
                    <th>Target</th>
                    <th>Accepted</th>
                    <th>Sisa</th>
                    <th>Progress</th>
                    <th>Status Terakhir</th>
                    <th>Aktivitas Terakhir</th>
                    <th>Bukti</th>
                </tr>
            </thead>

            <tbody>
                @forelse($daftarProgressKm as $index => $item)
                @php
                $statusClass = match($item['status_capaian']) {
                'Tercapai' => 'status-tercapai',
                'Accepted' => 'status-accepted',
                'Submitted' => 'status-submitted',
                'Rejected' => 'status-rejected',
                'On Progress' => 'status-progress',
                default => 'status-belum',
                };
                @endphp

                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['tahun'] }}</td>
                    <td class="fw-bold">{{ $item['kategori'] }}</td>
                    <td>{{ $item['sub_kategori'] }}</td>
                    <td>{{ $item['target'] }}</td>
                    <td>{{ $item['realisasi'] }}</td>
                    <td>{{ $item['sisa'] }}</td>
                    <td style="min-width: 140px;">
                        <div class="progress-soft mb-1">
                            <div class="progress-soft-fill" style="width: {{ $item['persentase'] }}%;"></div>
                        </div>
                        <span class="small fw-bold">{{ $item['persentase'] }}%</span>
                    </td>
                    <td>
                        <span class="status-badge {{ $statusClass }}">
                            {{ $item['status_capaian'] }}
                        </span>
                    </td>
                    <td>
                        {{ $item['judul_terakhir'] }}
                        @if($item['total_aktivitas'] > 0)
                        <div class="text-muted small">
                            {{ $item['total_aktivitas'] }} aktivitas diinput
                        </div>
                        @endif
                    </td>
                    <td>
                        @if(!empty($item['bukti_pdf_path']) || !empty($item['bukti_file_path']))
                        <a href="/bukti-km/{{ $item['id_aktivitas_terakhir'] }}/download" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download me-1"></i> Download
                        </a>
                        @elseif(!empty($item['bukti_link']))
                        <a href="{{ $item['bukti_link'] }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            Link
                        </a>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center text-muted py-4">
                        Belum ada KM yang ditugaskan kepada Anda pada tahun {{ $tahun }}.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
