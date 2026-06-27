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
</style>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="page-heading mb-0">
        Progress <span class="muted">KM Saya</span>
    </div>

    <a href="/anggota/dashboard" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Progress KM Saya</h4>
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
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="summary-card">
            <div class="summary-label">Total Target</div>
            <p class="summary-value">{{ $totalTarget }}</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="summary-card">
            <div class="summary-label">Total Realisasi Accepted</div>
            <p class="summary-value">{{ $totalRealisasi }}</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="summary-card">
            <div class="summary-label">Sisa Target</div>
            <p class="summary-value">{{ $totalSisa }}</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="summary-card">
            <div class="summary-label">Persentase Capaian</div>
            <p class="summary-value">{{ $persentaseTotal }}%</p>
            <div class="progress-soft mt-2">
                <div class="progress-soft-fill" style="width: {{ $persentaseTotal }}%;"></div>
            </div>
        </div>
    </div>
</div>

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
                        @if(!empty($item['bukti_link']))
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