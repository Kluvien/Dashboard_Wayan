@extends('layouts.app')

@section('title', 'Dashboard Ketua Lab')

@section('content')
@php
$kategoriCards = collect($kategoriCards ?? []);
$anggotaProgressRows = collect($anggotaProgressRows ?? []);
$kategoriChartData = $kategoriChartData ?? [];
@endphp

<style>
    .dashboard-header {
        padding: 18px 22px;
    }

    .category-card-title {
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 4px;
    }

    .category-card-value {
        font-size: 30px;
        font-weight: 800;
        margin-bottom: 4px;
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
        background: var(--blue);
    }

    .dashboard-category-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .dashboard-grid-main {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 18px;
        margin-bottom: 18px;
    }

    .dashboard-category-chart-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .dashboard-chart-box {
        position: relative;
        width: 100%;
        height: 300px;
    }

    .dashboard-chart-box-small {
        position: relative;
        width: 100%;
        height: 250px;
    }

    .member-progress-table th,
    .member-progress-table td {
        white-space: nowrap;
        vertical-align: middle;
        font-size: 13px;
    }

    @media (max-width: 1200px) {

        .dashboard-category-grid,
        .dashboard-grid-main,
        .dashboard-category-chart-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {

        .dashboard-category-grid,
        .dashboard-grid-main,
        .dashboard-category-chart-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-heading">
    Dashboard <span class="muted">Ketua Lab</span>
</div>

<div class="card dashboard-header mb-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Ringkasan KM Lab Tahun {{ $tahun }}</h4>
            <p class="text-muted mb-0">
                Lab: <strong>{{ $lab->nama_lab ?? '-' }}</strong>
            </p>
        </div>

        <form action="/ketualab/dashboard" method="GET" class="d-flex gap-2">
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

            <a href="/ketualab/penurunan-km" class="btn btn-primary text-nowrap px-3">
                Bagi KM ke Anggota
            </a>
        </form>
    </div>
</div>

<div class="dashboard-category-grid">
    @foreach($kategoriCards as $item)
    <div class="card h-100">
        <p class="category-card-title">{{ $item['kategori'] }}</p>

        <p class="category-card-value">
            {{ $item['progress'] }}%
        </p>

        <div class="progress-soft my-2">
            <div class="progress-soft-fill" style="width: {{ $item['progress'] }}%;"></div>
        </div>

        <div class="d-flex justify-content-between small text-muted mb-3">
            <span>Target: {{ $item['target'] }}</span>
            <span>Realisasi: {{ $item['realisasi'] }}</span>
        </div>

        <a href="/ketualab/monitoring-lab?tahun={{ $tahun }}" class="btn btn-primary w-100">
            Lihat Detail
        </a>
    </div>
    @endforeach
</div>

<div class="dashboard-grid-main">
    <div class="card">
        <h4 class="fw-bold mb-1">Diagram Pencapaian Anggota Lab</h4>
        <p class="text-muted mb-3">
            Persentase pencapaian realisasi KM anggota pada tahun {{ $tahun }}.
        </p>

        <div class="dashboard-chart-box">
            <canvas id="chartProgressAnggota"></canvas>
        </div>
    </div>

    <div class="card">
        <h4 class="fw-bold mb-1">Ringkasan Lab</h4>
        <p class="text-muted mb-3">
            Total target, realisasi, dan sisa KM lab.
        </p>

        <div class="row g-3">
            <div class="col-6">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small">Jumlah Anggota</div>
                    <div class="fs-3 fw-bold">{{ $jumlahAnggota ?? 0 }}</div>
                </div>
            </div>

            <div class="col-6">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small">Total Target</div>
                    <div class="fs-3 fw-bold">{{ $totalTargetKm ?? 0 }}</div>
                </div>
            </div>

            <div class="col-6">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small">Realisasi</div>
                    <div class="fs-3 fw-bold text-success">{{ $totalRealisasiKm ?? 0 }}</div>
                </div>
            </div>

            <div class="col-6">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small">Sisa</div>
                    <div class="fs-3 fw-bold text-warning">{{ $totalSisaKm ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <div class="d-flex justify-content-between mb-1">
                <span class="fw-bold">Progress Realisasi</span>
                <span class="fw-bold">{{ $persentaseRealisasi ?? 0 }}%</span>
            </div>

            <div class="progress-soft">
                <div class="progress-soft-fill" style="width: {{ $persentaseRealisasi ?? 0 }}%;"></div>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-category-chart-grid mb-4">
    @foreach($kategoriCards as $item)
    <div class="card">
        <h4 class="fw-bold mb-1">{{ $item['kategori'] }}</h4>
        <p class="text-muted mb-3">
            Target dan realisasi berdasarkan sub kategori KM.
        </p>

        <div class="dashboard-chart-box-small">
            <canvas id="chartKategori{{ $loop->index }}"></canvas>
        </div>
    </div>
    @endforeach
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Progress Anggota Lab</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0 member-progress-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Anggota</th>
                    <th>NIDN</th>
                    <th>JAD</th>
                    <th>Target</th>
                    <th>Realisasi</th>
                    <th>Sisa</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($anggotaProgressRows as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="fw-bold">{{ $item['nama_dosen'] }}</td>
                    <td>{{ $item['nidn'] }}</td>
                    <td>
                        <span class="badge bg-primary">{{ $item['jad'] }}</span>
                    </td>
                    <td>{{ $item['target'] }}</td>
                    <td>{{ $item['realisasi'] }}</td>
                    <td>{{ $item['sisa'] }}</td>
                    <td style="min-width: 180px;">
                        <div class="progress-soft mb-1">
                            <div class="progress-soft-fill" style="width: {{ $item['progress'] }}%;"></div>
                        </div>
                        <div class="small text-muted">{{ $item['progress'] }}%</div>
                    </td>
                    <td>
                        @if($item['status'] === 'Tercapai')
                        <span class="badge bg-success">Tercapai</span>
                        @elseif($item['status'] === 'Sedang Progress')
                        <span class="badge bg-warning text-dark">Sedang Progress</span>
                        @else
                        <span class="badge bg-secondary">Belum Ada KM</span>
                        @endif
                    </td>
                    <td>
                        <a href="/ketualab/detail-anggota/{{ $item['id_user'] }}" class="btn btn-primary btn-sm">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        Belum ada anggota pada lab ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const anggotaLabels = @json($anggotaLabels ?? []);
        const anggotaProgress = @json($anggotaProgress ?? []);
        const kategoriChartData = @json($kategoriChartData ?? []);

        const blue = '#477EF7';
        const green = '#22C55E';

        const defaultOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        usePointStyle: true
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        };

        const chartProgressAnggota = document.getElementById('chartProgressAnggota');

        if (chartProgressAnggota) {
            new Chart(chartProgressAnggota, {
                type: 'bar',
                data: {
                    labels: anggotaLabels,
                    datasets: [{
                        label: 'Pencapaian (%)',
                        data: anggotaProgress,
                        backgroundColor: blue,
                        borderRadius: 8
                    }]
                },
                options: {
                    ...defaultOptions,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        Object.keys(kategoriChartData).forEach(function(kategori, index) {
            const canvas = document.getElementById('chartKategori' + index);

            if (!canvas) return;

            const data = kategoriChartData[kategori];

            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                            label: 'Target',
                            data: data.target,
                            backgroundColor: blue,
                            borderRadius: 8
                        },
                        {
                            label: 'Realisasi',
                            data: data.realisasi,
                            backgroundColor: green,
                            borderRadius: 8
                        }
                    ]
                },
                options: defaultOptions
            });
        });
    });
</script>
@endsection