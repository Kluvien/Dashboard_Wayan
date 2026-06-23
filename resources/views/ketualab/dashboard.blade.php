@extends('layouts.app')

@section('title', 'Dashboard Ketua Lab')

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
    Dashboard <span class="muted">Ketua Lab</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Ringkasan KM Lab Tahun {{ $tahun }}</h4>
            <p class="text-muted mb-0">
                Lab: {{ $lab->nama_lab ?? '-' }}
            </p>
        </div>

        <a href="/ketualab/penurunan-km" class="btn btn-primary">
            Bagi KM ke Anggota
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Jumlah Anggota</p>
            <h3 class="fw-bold mb-0">{{ $jumlahAnggota ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">KM Turun ke Lab</p>
            <h3 class="fw-bold mb-0">{{ $totalKmTurun ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">KM Sudah Assign</p>
            <h3 class="fw-bold mb-0">{{ $totalKmAssign ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Sisa Assign</p>
            <h3 class="fw-bold mb-0">{{ $totalSisaAssign ?? 0 }}</h3>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <p class="text-muted mb-1">Total Realisasi</p>
            <h3 class="fw-bold mb-0">{{ $totalRealisasi ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <p class="text-muted mb-1">Progress Assign</p>
            <h3 class="fw-bold mb-0">{{ min($persentaseAssign ?? 0, 100) }}%</h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <p class="text-muted mb-1">Progress Realisasi</p>
            <h3 class="fw-bold mb-0">{{ min($persentaseRealisasi ?? 0, 100) }}%</h3>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card h-100">
            <h4 class="fw-bold mb-3">Grafik KM per Kategori</h4>
            <div class="dashboard-chart-box">
                <canvas id="chartKategoriLab"></canvas>
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
    <h4 class="fw-bold mb-3">Grafik Progress Anggota Lab</h4>
    <div class="dashboard-chart-box">
        <canvas id="chartAnggotaLab"></canvas>
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Akses Cepat</h4>

    <div class="d-flex gap-2 flex-wrap">
        <a href="/ketualab/monitoring-lab" class="btn btn-primary">
            Monitoring Lab
        </a>

        <a href="/ketualab/monitoring-anggota" class="btn btn-primary">
            Monitoring Anggota
        </a>

        <a href="/ketualab/penurunan-km" class="btn btn-primary">
            Pembagian KM
        </a>

        <a href="/ketualab/laporan" class="btn btn-secondary">
            Laporan
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const kategoriLabels = @json($kategoriLabels ?? []);
        const kategoriKmTurun = @json($kategoriKmTurun ?? []);
        const kategoriKmAssign = @json($kategoriKmAssign ?? []);
        const kategoriRealisasi = @json($kategoriRealisasi ?? []);

        const anggotaLabels = @json($anggotaLabels ?? []);
        const anggotaKmAssign = @json($anggotaKmAssign ?? []);
        const anggotaRealisasi = @json($anggotaRealisasi ?? []);

        const statusAssignLabels = @json($statusAssignLabels ?? []);
        const statusAssignData = @json($statusAssignData ?? []);

        const chartKategoriElement = document.getElementById('chartKategoriLab');

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

        const chartAnggotaElement = document.getElementById('chartAnggotaLab');

        if (chartAnggotaElement) {
            new Chart(chartAnggotaElement, {
                type: 'bar',
                data: {
                    labels: anggotaLabels,
                    datasets: [{
                            label: 'KM Assign',
                            data: anggotaKmAssign,
                            backgroundColor: '#3b82f6'
                        },
                        {
                            label: 'Realisasi',
                            data: anggotaRealisasi,
                            backgroundColor: '#22c55e'
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