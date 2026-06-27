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

    .dashboard-category-progress {
        height: 8px;
        border-radius: 999px;
        background: #EEF1F6;
        overflow: hidden;
        margin: 12px 0 10px;
    }

    .dashboard-category-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: var(--blue);
    }

    .dashboard-category-row {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        font-size: 12px;
        color: var(--text-muted);
        margin-bottom: 10px;
    }

    .dashboard-category-button {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        padding: 8px 10px;
        border-radius: 10px;
        background: var(--blue);
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
    }

    .dashboard-category-button:hover {
        color: #fff;
        opacity: 0.9;
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

    .dashboard-chart-full {
        margin-bottom: 18px;
    }

    .dashboard-category-chart-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .dashboard-category-chart-grid .card:last-child:nth-child(odd) {
        grid-column: span 2;
    }

    .dashboard-chart-box-large {
        position: relative;
        width: 100%;
        height: 320px;
    }

    @media (max-width: 992px) {
        .dashboard-category-chart-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-category-chart-grid .card:last-child:nth-child(odd) {
            grid-column: span 1;
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
    @forelse($kategoriCards ?? [] as $item)
    <div class="dashboard-stat-card">
        <div class="dashboard-stat-label">{{ $item['kategori'] }}</div>

        <div class="dashboard-stat-value">
            {{ $item['persentase'] }}%
        </div>

        <div class="dashboard-category-progress">
            <div
                class="dashboard-category-progress-fill"
                style="width: {{ $item['persentase'] }}%;"></div>
        </div>

        <div class="dashboard-category-row">
            <span>Target: {{ $item['target'] }}</span>
            <span>Realisasi: {{ $item['realisasi'] }}</span>
        </div>

        <a href="/ketuakk/km-kk" class="dashboard-category-button">
            Lihat Detail
        </a>
    </div>
    @empty
    <div class="dashboard-stat-card">
        <div class="dashboard-stat-label">Data kategori KM</div>
        <div class="dashboard-stat-value">0</div>
        <div class="dashboard-stat-sub">Belum ada data.</div>
    </div>
    @endforelse
</div>

<div class="card dashboard-chart-full">
    <div class="dashboard-panel-title">Diagram Pencapaian Lab Riset</div>
    <div class="dashboard-panel-subtitle">
        Persentase pencapaian realisasi KM pada masing-masing Lab Riset tahun {{ $tahun }}.
    </div>

    <div class="dashboard-chart-box-large">
        <canvas id="chartLabAchievement"></canvas>
    </div>
</div>

<div class="dashboard-category-chart-grid">
    @foreach($kategoriDetailCharts ?? [] as $index => $chart)
    <div class="card">
        <div class="dashboard-panel-title">{{ $chart['kategori'] }}</div>
        <div class="dashboard-panel-subtitle">
            Target dan realisasi berdasarkan sub kategori KM.
        </div>

        <div class="dashboard-chart-box-small">
            <canvas id="chartKategoriDetail{{ $index }}"></canvas>
        </div>
    </div>
    @endforeach
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const labLabels = @json($labShortLabels ?? $labChartLabels ?? []);
        const labAchievementPercentages = @json($labAchievementPercentages ?? []);
        const kategoriDetailCharts = @json($kategoriDetailCharts ?? []);

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

        const chartLabAchievementElement = document.getElementById('chartLabAchievement');

        if (chartLabAchievementElement) {
            new Chart(chartLabAchievementElement, {
                type: 'bar',
                data: {
                    labels: labLabels,
                    datasets: [{
                        label: 'Pencapaian (%)',
                        data: labAchievementPercentages,
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

        kategoriDetailCharts.forEach(function(chart, index) {
            const chartElement = document.getElementById('chartKategoriDetail' + index);

            if (!chartElement) {
                return;
            }

            new Chart(chartElement, {
                type: 'bar',
                data: {
                    labels: chart.labels,
                    datasets: [{
                            label: 'Target',
                            data: chart.targets,
                            backgroundColor: blue,
                            borderRadius: 8
                        },
                        {
                            label: 'Realisasi',
                            data: chart.realisasi,
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