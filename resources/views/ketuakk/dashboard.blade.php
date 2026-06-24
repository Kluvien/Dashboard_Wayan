@extends('layouts.app')

@section('title', 'Dashboard Ketua KK')

@section('content')
<style>
    .dashboard-chart-box {
        position: relative;
        width: 100%;
        height: 340px;
    }

    .badge-warning-large {
        font-size: 14px;
        padding: 6px 12px;
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
                Menampilkan target, realisasi, dan status penurunan KM seluruh Kelompok Keahlian.
            </p>
        </div>

        <a href="/ketuakk/km-lab-riset/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Turunkan KM ke Lab
        </a>
    </div>
</div>

<!-- 5 KARTU RINGKASAN BARU -->
<div class="row g-3 mb-4">
    <!-- Kartu 1: Jumlah Anggota KK -->
    <div class="col-md-6 col-lg-2-4">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted small mb-2">Jumlah Anggota KK</p>
                <h2 class="fw-bold mb-0">{{ $jumlahAnggota ?? 0 }}</h2>
                <small class="text-muted">Anggota Aktif</small>
            </div>
        </div>
    </div>

    <!-- Kartu 2: Jumlah Lab Riset -->
    <div class="col-md-6 col-lg-2-4">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted small mb-2">Jumlah Lab Riset</p>
                <h2 class="fw-bold mb-0">{{ $jumlahLab ?? 0 }}</h2>
                <small class="text-muted">Lab Riset</small>
            </div>
        </div>
    </div>

    <!-- Kartu 3: Target KM KK Terpenuhi -->
    <div class="col-md-6 col-lg-2-4">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted small mb-2">Target KM KK Terpenuhi</p>
                <h2 class="fw-bold mb-1">{{ $totalRealisasiKm ?? 0 }} / {{ $totalTargetKm ?? 0 }}</h2>
                <small class="text-success fw-bold">{{ $persentaseRealisasi }}% Tercapai</small>
            </div>
        </div>
    </div>

    <!-- Kartu 4: Jumlah KM Belum Diturunkan -->
    <div class="col-md-6 col-lg-2-4">
        <div class="card h-100 border-warning">
            <div class="card-body">
                <p class="text-muted small mb-2">Jumlah KM Belum Diturunkan</p>
                <h2 class="fw-bold text-warning mb-1">{{ $totalBelumTurun ?? 0 }}</h2>
                @if($totalBelumTurun > 0)
                    <span class="badge bg-warning badge-warning-large">⚠️ Perlu Tindakan</span>
                @else
                    <span class="badge bg-success">✓ Selesai</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Kartu 5: Jumlah KM Sudah Diturunkan -->
    <div class="col-md-6 col-lg-2-4">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted small mb-2">Jumlah KM Sudah Diturunkan</p>
                <h2 class="fw-bold text-success mb-1">{{ $totalSudahTurun ?? 0 }}</h2>
                <small class="text-success">KM</small>
            </div>
        </div>
    </div>
</div>

<!-- GRAFIK 1: Target & Realisasi KK (BARU) -->
<div class="card mb-4">
    <h4 class="fw-bold mb-3">Grafik Target dan Realisasi KM Kelompok Keahlian EIMS</h4>
    <div class="dashboard-chart-box">
        <canvas id="chartKk"></canvas>
    </div>
</div>

<!-- GRAFIK 2: KM per Lab Riset -->
<div class="card mb-4">
    <h4 class="fw-bold mb-3">Grafik Target dan Realisasi KM per Lab Riset</h4>
    <div class="dashboard-chart-box">
        <canvas id="chartLab"></canvas>
    </div>
</div>

<!-- GRAFIK 3: KM per Kategori -->
<div class="card mb-4">
    <h4 class="fw-bold mb-3">Grafik Target dan Realisasi KM per Kategori</h4>
    <div class="dashboard-chart-box">
        <canvas id="chartKategori"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data dari backend
        const kkLabel = @json($chartKkLabel ?? []);
        const kkData = @json($chartKkData ?? []);

        const labLabels = @json($labChartLabels ?? []);
        const labTargets = @json($labTargets ?? []);
        const labRealisasi = @json($labRealisasi ?? []);

        const kategoriLabels = @json($kategoriLabels ?? []);
        const kategoriTargets = @json($kategoriTargets ?? []);
        const kategoriRealisasi = @json($kategoriRealisasi ?? []);

        // Chart 1: KK Target & Realisasi
        const chartKkElement = document.getElementById('chartKk');
        if (chartKkElement) {
            new Chart(chartKkElement, {
                type: 'bar',
                data: {
                    labels: kkLabel,
                    datasets: [{
                        label: 'Nilai',
                        data: kkData,
                        backgroundColor: ['#3b82f6', '#10b981'],
                        borderColor: ['#1e40af', '#059669'],
                        borderWidth: 1
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
                        }
                    }
                }
            });
        }

        // Chart 2: Lab Target & Realisasi
        const chartLabElement = document.getElementById('chartLab');
        if (chartLabElement) {
            new Chart(chartLabElement, {
                type: 'bar',
                data: {
                    labels: labLabels,
                    datasets: [{
                        label: 'Target',
                        data: labTargets,
                        backgroundColor: '#3b82f6'
                    },
                    {
                        label: 'Realisasi',
                        data: labRealisasi,
                        backgroundColor: '#10b981'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
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
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        // Chart 3: Kategori Target & Realisasi
        const chartKategoriElement = document.getElementById('chartKategori');
        if (chartKategoriElement) {
            new Chart(chartKategoriElement, {
                type: 'bar',
                data: {
                    labels: kategoriLabels,
                    datasets: [{
                        label: 'Target',
                        data: kategoriTargets,
                        backgroundColor: '#3b82f6'
                    },
                    {
                        label: 'Realisasi',
                        data: kategoriRealisasi,
                        backgroundColor: '#10b981'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
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
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    });
</script>

<style>
    .col-lg-2-4 {
        flex: 0 0 calc(20% - 0.75rem);
    }
    @media (max-width: 1200px) {
        .col-lg-2-4 {
            flex: 0 0 calc(50% - 0.75rem);
        }
    }
    @media (max-width: 768px) {
        .col-lg-2-4 {
            flex: 0 0 100%;
        }
    }
</style>

@endsection