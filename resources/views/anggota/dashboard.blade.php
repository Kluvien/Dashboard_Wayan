@extends('layouts.app')

@section('title', 'Dashboard Anggota')

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
    Dashboard <span class="muted">Anggota</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">
                {{ $anggota->nama_dosen ?? $anggota->username ?? 'Anggota' }}
            </h4>

            <p class="text-muted mb-0">
                Lab: {{ $anggota->nama_lab ?? '-' }} |
                NIDN: {{ $anggota->nidn ?? '-' }} |
                JAD: {{ $anggota->jad ?? 'AA' }}
            </p>
        </div>

        <a href="/anggota/aktivitas-km" class="btn btn-primary">
            Input Aktivitas KM
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">KM Ditugaskan</p>
            <h3 class="fw-bold mb-0">{{ $totalTarget ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Realisasi</p>
            <h3 class="fw-bold mb-0">{{ $totalRealisasi ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Sisa KM</p>
            <h3 class="fw-bold mb-0">{{ $totalSisa ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Progress Total</p>
            <h3 class="fw-bold mb-0">{{ min($persentaseTotal ?? 0, 100) }}%</h3>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card h-100">
            <h4 class="fw-bold mb-3">Grafik KM Saya per Kategori</h4>

            <div class="dashboard-chart-box">
                <canvas id="chartKategoriAnggota"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <h4 class="fw-bold mb-3">Status Progress KM</h4>

            <div class="dashboard-chart-box-small">
                <canvas id="chartStatusProgress"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Grafik Aktivitas Bulanan Tahun {{ $tahun }}</h4>

    <div class="dashboard-chart-box">
        <canvas id="chartAktivitasBulanan"></canvas>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <h4 class="fw-bold mb-3">Riwayat KM yang Diberikan</h4>

            <div class="table-responsive">
                <table class="table align-middle mb-0" style="width: 100%; font-size: 14px;">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($riwayatAssign as $assign)
                        <tr>
                            <td class="fw-bold">{{ $assign->kategori_km }}</td>
                            <td>{{ $assign->jumlah_km }}</td>
                            <td>{{ \Carbon\Carbon::parse($assign->created_at)->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                Belum ada KM yang diberikan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <a href="/anggota/progress-km" class="btn btn-primary btn-sm">
                    Lihat Progress KM
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <h4 class="fw-bold mb-3">Aktivitas KM Terbaru</h4>

            <div class="table-responsive">
                <table class="table align-middle mb-0" style="width: 100%; font-size: 14px;">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Judul</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($aktivitasTerbaru as $aktivitas)
                        <tr>
                            <td class="fw-bold">{{ $aktivitas->kategori_km }}</td>
                            <td>{{ $aktivitas->judul_aktivitas }}</td>
                            <td>{{ \Carbon\Carbon::parse($aktivitas->tanggal_mulai)->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                Belum ada aktivitas KM.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <a href="/anggota/aktivitas-km" class="btn btn-primary btn-sm">
                    Kelola Aktivitas
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Akses Cepat</h4>

    <div class="d-flex gap-2 flex-wrap">
        <a href="/anggota/progress-km" class="btn btn-primary">
            Progress KM
        </a>

        <a href="/anggota/aktivitas-km" class="btn btn-primary">
            Aktivitas KM
        </a>

        <a href="/anggota/riwayat-realisasi" class="btn btn-primary">
            Riwayat Realisasi
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const kategoriLabels = @json($kategoriLabels ?? []);
        const kategoriTarget = @json($kategoriTarget ?? []);
        const kategoriRealisasi = @json($kategoriRealisasi ?? []);
        const kategoriSisa = @json($kategoriSisa ?? []);

        const bulanLabels = @json($bulanLabels ?? []);
        const aktivitasBulanan = @json($aktivitasBulanan ?? []);

        const statusProgressLabels = @json($statusProgressLabels ?? []);
        const statusProgressData = @json($statusProgressData ?? []);

        const chartKategoriElement = document.getElementById('chartKategoriAnggota');

        if (chartKategoriElement) {
            new Chart(chartKategoriElement, {
                type: 'bar',
                data: {
                    labels: kategoriLabels,
                    datasets: [{
                            label: 'KM Ditugaskan',
                            data: kategoriTarget,
                            backgroundColor: '#3b82f6'
                        },
                        {
                            label: 'Realisasi',
                            data: kategoriRealisasi,
                            backgroundColor: '#22c55e'
                        },
                        {
                            label: 'Sisa',
                            data: kategoriSisa,
                            backgroundColor: '#ef4444'
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

        const chartAktivitasElement = document.getElementById('chartAktivitasBulanan');

        if (chartAktivitasElement) {
            new Chart(chartAktivitasElement, {
                type: 'line',
                data: {
                    labels: bulanLabels,
                    datasets: [{
                        label: 'Aktivitas KM',
                        data: aktivitasBulanan,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.15)',
                        fill: true,
                        tension: 0.35
                    }]
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

        const chartStatusElement = document.getElementById('chartStatusProgress');

        if (chartStatusElement) {
            new Chart(chartStatusElement, {
                type: 'doughnut',
                data: {
                    labels: statusProgressLabels,
                    datasets: [{
                        data: statusProgressData,
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