@extends('layouts.app')

@section('title', 'Dashboard Ketua KK')

@section('content')
<style>
    .dashboard-header {
        padding: 18px 22px;
    }

    .dashboard-stat-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .dashboard-stat-card {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 18px;
        min-height: 132px;
    }

    .dashboard-stat-label {
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 8px;
        line-height: 1.35;
    }

    .dashboard-stat-value {
        font-size: 30px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 8px;
    }

    .dashboard-stat-sub {
        font-size: 12px;
        color: var(--text-muted);
    }

    .dashboard-grid-main {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 18px;
        margin-bottom: 18px;
    }

    .dashboard-grid-bottom {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .dashboard-panel-title {
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .dashboard-panel-subtitle {
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 14px;
    }

    .dashboard-chart-box {
        position: relative;
        width: 100%;
        height: 280px;
    }

    .dashboard-chart-box-small {
        position: relative;
        width: 100%;
        height: 230px;
    }

    .lab-progress-item {
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
    }

    .lab-progress-item:last-child {
        border-bottom: none;
    }

    .lab-progress-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }

    .lab-name {
        font-weight: 800;
        font-size: 14px;
    }

    .lab-meta {
        color: var(--text-muted);
        font-size: 12px;
    }

    .progress {
        height: 9px;
        border-radius: 99px;
        background: #EEF1F6;
    }

    .progress-bar {
        border-radius: 99px;
        background: var(--blue);
    }

    .badge-soft-success {
        background: #E9F8EF;
        color: #16A34A;
        border-radius: 99px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 800;
    }

    .badge-soft-warning {
        background: #FFF5DA;
        color: #D97706;
        border-radius: 99px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 800;
    }

    @media (max-width: 1200px) {
        .dashboard-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-grid-main,
        .dashboard-grid-bottom {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .dashboard-stat-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-heading">
    Dashboard <span class="muted">Ketua KK</span>
</div>

<div class="card dashboard-header mb-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Ringkasan Kontrak Manajemen {{ $tahun }}</h4>
            <p class="text-muted mb-0">
                Monitoring target KM, realisasi, dan capaian setiap Lab Riset dalam Kelompok Keahlian.
            </p>
        </div>

        <a href="/ketuakk/km-lab-riset/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Turunkan KM ke Lab
        </a>
    </div>
</div>

<div class="dashboard-stat-grid">
    <div class="dashboard-stat-card">
        <div class="dashboard-stat-label">Jumlah Anggota KK</div>
        <div class="dashboard-stat-value">{{ $jumlahAnggota ?? 0 }}</div>
        <div class="dashboard-stat-sub">Dosen terdaftar</div>
    </div>

    <div class="dashboard-stat-card">
        <div class="dashboard-stat-label">Jumlah Lab Riset</div>
        <div class="dashboard-stat-value">{{ $jumlahLab ?? 0 }}</div>
        <div class="dashboard-stat-sub">Lab aktif</div>
    </div>

    <div class="dashboard-stat-card">
        <div class="dashboard-stat-label">Total Target KM</div>
        <div class="dashboard-stat-value">{{ $totalTargetKm ?? 0 }}</div>
        <div class="dashboard-stat-sub">KM diturunkan ke lab</div>
    </div>

    <div class="dashboard-stat-card">
        <div class="dashboard-stat-label">Total Realisasi KM</div>
        <div class="dashboard-stat-value text-success">{{ $totalRealisasiKm ?? 0 }}</div>
        <div class="dashboard-stat-sub">{{ $persentaseRealisasi ?? 0 }}% dari target</div>
    </div>

    <div class="dashboard-stat-card">
        <div class="dashboard-stat-label">Sisa KM</div>
        <div class="dashboard-stat-value text-warning">{{ $totalSisaKm ?? 0 }}</div>

        @if(($totalSisaKm ?? 0) > 0)
        <span class="badge-soft-warning">Perlu realisasi</span>
        @else
        <span class="badge-soft-success">Selesai</span>
        @endif
    </div>
</div>

<div class="dashboard-grid-main">
    <div class="card">
        <div class="dashboard-panel-title">Grafik Target dan Realisasi Lab. Riset</div>
        <div class="dashboard-panel-subtitle">
            Perbandingan target KM yang diturunkan dan realisasi aktivitas setiap lab.
        </div>

        <div class="dashboard-chart-box">
            <canvas id="chartLab"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="dashboard-panel-title">Capaian Lab Riset</div>
        <div class="dashboard-panel-subtitle">
            Ringkasan persentase realisasi per lab.
        </div>

        @forelse($rekapLab ?? [] as $lab)
        <div class="lab-progress-item">
            <div class="lab-progress-head">
                <div>
                    <div class="lab-name">{{ $lab['nama_singkat'] }}</div>
                    <div class="lab-meta">
                        Target {{ $lab['target'] }} |
                        Realisasi {{ $lab['realisasi'] }} |
                        Sisa {{ $lab['sisa'] }}
                    </div>
                </div>

                <strong>{{ $lab['persentase'] }}%</strong>
            </div>

            <div class="progress">
                <div
                    class="progress-bar"
                    role="progressbar"
                    style="width: {{ $lab['persentase'] }}%;"
                    aria-valuenow="{{ $lab['persentase'] }}"
                    aria-valuemin="0"
                    aria-valuemax="100"></div>
            </div>
        </div>
        @empty
        <p class="text-muted mb-0">Belum ada data lab riset.</p>
        @endforelse
    </div>
</div>

<div class="dashboard-grid-bottom">
    <div class="card">
        <div class="dashboard-panel-title">Ringkasan Target dan Realisasi KK</div>
        <div class="dashboard-panel-subtitle">
            Total target, realisasi, dan sisa KM tahun {{ $tahun }}.
        </div>

        <div class="dashboard-chart-box-small">
            <canvas id="chartKk"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="dashboard-panel-title">Target dan Realisasi per Kategori</div>
        <div class="dashboard-panel-subtitle">
            Sebaran capaian KM berdasarkan kategori kegiatan.
        </div>

        <div class="dashboard-chart-box-small">
            <canvas id="chartKategori"></canvas>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartKkLabel = @json($chartKkLabel ?? []);
        const chartKkData = @json($chartKkData ?? []);

        const labLabels = @json($labShortLabels ?? $labChartLabels ?? []);
        const labTargets = @json($labTargets ?? []);
        const labRealisasi = @json($labRealisasi ?? []);

        const kategoriLabels = @json($kategoriLabels ?? []);
        const kategoriTargets = @json($kategoriTargets ?? []);
        const kategoriRealisasi = @json($kategoriRealisasi ?? []);

        const blue = '#477EF7';
        const green = '#22C55E';
        const orange = '#F59E0B';

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

        const chartLabElement = document.getElementById('chartLab');

        if (chartLabElement) {
            new Chart(chartLabElement, {
                type: 'bar',
                data: {
                    labels: labLabels,
                    datasets: [{
                            label: 'Target',
                            data: labTargets,
                            backgroundColor: blue,
                            borderRadius: 8
                        },
                        {
                            label: 'Realisasi',
                            data: labRealisasi,
                            backgroundColor: green,
                            borderRadius: 8
                        }
                    ]
                },
                options: defaultOptions
            });
        }

        const chartKkElement = document.getElementById('chartKk');

        if (chartKkElement) {
            new Chart(chartKkElement, {
                type: 'bar',
                data: {
                    labels: chartKkLabel,
                    datasets: [{
                        label: 'Jumlah KM',
                        data: chartKkData,
                        backgroundColor: [blue, green, orange],
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        const chartKategoriElement = document.getElementById('chartKategori');

        if (chartKategoriElement) {
            new Chart(chartKategoriElement, {
                type: 'bar',
                data: {
                    labels: kategoriLabels,
                    datasets: [{
                            label: 'Target',
                            data: kategoriTargets,
                            backgroundColor: blue,
                            borderRadius: 8
                        },
                        {
                            label: 'Realisasi',
                            data: kategoriRealisasi,
                            backgroundColor: green,
                            borderRadius: 8
                        }
                    ]
                },
                options: defaultOptions
            });
        }
    });
</script>
@endsection