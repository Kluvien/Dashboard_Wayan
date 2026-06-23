@extends('layouts.app')

@section('title', 'Dashboard Ketua KK')

@section('content')
<style>
    .dashboard-chart-box {
        position: relative;
        width: 100%;
        height: 340px;
    }

    .dashboard-chart-box-small {
        position: relative;
        width: 100%;
        height: 300px;
    }
</style>
<div class="page-heading">
    Dashboard <span class="muted">Ketua KK</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Ringkasan Kontrak Manajemen Tahun {{ $tahun }}</h4>
            <p class="text-muted mb-0">
                Menampilkan grafik penurunan KM, pembagian KM, dan realisasi aktivitas seluruh lab riset.
            </p>
        </div>

        <a href="/ketuakk/km-lab-riset/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Turunkan KM ke Lab
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Total Lab Riset</p>
            <h3 class="fw-bold mb-0">{{ $totalLab ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Total Anggota</p>
            <h3 class="fw-bold mb-0">{{ $totalAnggota ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Total KM Turun</p>
            <h3 class="fw-bold mb-0">{{ $totalKmTurun ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Total KM Assign</p>
            <h3 class="fw-bold mb-0">{{ $totalKmAssign ?? 0 }}</h3>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Sisa Assign</p>
            <h3 class="fw-bold mb-0">{{ $totalSisaAssign ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Total Realisasi</p>
            <h3 class="fw-bold mb-0">{{ $totalRealisasi ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Progress Assign</p>
            <h3 class="fw-bold mb-0">{{ min($persentaseAssign ?? 0, 100) }}%</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Progress Realisasi</p>
            <h3 class="fw-bold mb-0">{{ min($persentaseRealisasi ?? 0, 100) }}%</h3>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card h-100">
            <h4 class="fw-bold mb-3">Grafik KM per Lab Riset</h4>
            <div class="dashboard-chart-box">
                <canvas id="chartLabKm"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <h4 class="fw-bold mb-3">Status Assign KM</h4>
            <div class="dashboard-chart-box-small">
                <canvas id="chartStatusAssign"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Grafik KM per Kategori</h4>
    <div class="dashboard-chart-box">
        <canvas id="chartKategoriKm"></canvas>
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Catatan Dashboard</h4>
    <p class="text-muted mb-0">
        Grafik ini membaca data dari tabel KM baru:
        <strong>km_lab</strong>, <strong>km_anggota</strong>, dan <strong>aktivitas_km</strong>.
    </p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const labLabels = @json($labChartLabels ?? []);
        const labKmTurun = @json($labKmTurun ?? []);
        const labKmAssign = @json($labKmAssign ?? []);
        const labRealisasi = @json($labRealisasi ?? []);

        const kategoriLabels = @json($kategoriLabels ?? []);
        const kategoriKmTurun = @json($kategoriKmTurun ?? []);
        const kategoriKmAssign = @json($kategoriKmAssign ?? []);
        const kategoriRealisasi = @json($kategoriRealisasi ?? []);

        const statusAssignLabels = @json($statusAssignLabels ?? []);
        const statusAssignData = @json($statusAssignData ?? []);

        const chartLabElement = document.getElementById('chartLabKm');

        if (chartLabElement) {
            new Chart(chartLabElement, {
                type: 'bar',
                data: {
                    labels: labLabels,
                    datasets: [{
                            label: 'KM Turun',
                            data: labKmTurun,
                            backgroundColor: '#3b82f6'
                        },
                        {
                            label: 'KM Assign',
                            data: labKmAssign,
                            backgroundColor: '#22c55e'
                        },
                        {
                            label: 'Realisasi',
                            data: labRealisasi,
                            backgroundColor: '#f59e0b'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 20,
                            bottom: 10
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                maxRotation: 20,
                                minRotation: 0
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            min: 0,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        const chartKategoriElement = document.getElementById('chartKategoriKm');

        if (chartKategoriElement) {
            new Chart(chartKategoriElement, {
                type: 'bar',
                data: {
                    labels: kategoriLabels,
                    datasets: [{
                            label: 'KM Turun',
                            data: kategoriKmTurun,
                            backgroundColor: '#3b82f6'
                        },
                        {
                            label: 'KM Assign',
                            data: kategoriKmAssign,
                            backgroundColor: '#22c55e'
                        },
                        {
                            label: 'Realisasi',
                            data: kategoriRealisasi,
                            backgroundColor: '#f59e0b'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 20,
                            bottom: 10
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
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
                            min: 0,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        const chartStatusElement = document.getElementById('chartStatusAssign');

        if (chartStatusElement) {
            new Chart(chartStatusElement, {
                type: 'doughnut',
                data: {
                    labels: statusAssignLabels,
                    datasets: [{
                        data: statusAssignData,
                        backgroundColor: ['#22c55e', '#ef4444']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    });
</script>
@endsection