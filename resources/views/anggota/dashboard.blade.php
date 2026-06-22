@extends('layouts.app')

@section('title', 'Dashboard Anggota')

@section('content')
<style>
    .anggota-dashboard-table {
        table-layout: fixed;
        width: 100%;
        font-size: 14px;
    }

    .anggota-dashboard-table th,
    .anggota-dashboard-table td {
        padding: 12px 10px !important;
        white-space: normal;
        vertical-align: middle;
    }

    .anggota-dashboard-table .progress {
        height: 9px;
        background: #E5E7EB;
    }

    .status-badge {
        font-size: 12px;
        padding: 6px 8px;
        line-height: 1.2;
    }
</style>

<div class="page-heading">
    Dashboard <span class="muted">Anggota</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">
                {{ $dosen->nama_dosen ?? $user->username }}
            </h4>
            <p class="text-muted mb-0">
                {{ $dosen->nama_lab ?? 'Lab Riset belum terhubung' }} · Tahun KM {{ $tahun }}
            </p>
        </div>

        <a href="/anggota/aktivitas-km/create" class="btn btn-primary">
            Tambah Aktivitas KM
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="text-muted fw-bold mb-2">Total Target</div>
            <div class="fs-2 fw-bold">{{ $totalTarget }}</div>
            <div class="text-muted small">Target KM tahun {{ $tahun }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <div class="text-muted fw-bold mb-2">Total Realisasi</div>
            <div class="fs-2 fw-bold">{{ $totalRealisasi }}</div>
            <div class="text-muted small">Aktivitas KM tercatat</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <div class="text-muted fw-bold mb-2">Persentase Capaian</div>
            <div class="fs-2 fw-bold">{{ min($persentaseTotal, 100) }}%</div>
            <div class="text-muted small">Capaian keseluruhan</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <div class="text-muted fw-bold mb-2">Kategori Terbaik</div>
            <div class="fs-4 fw-bold">
                {{ $kategoriTerbaik['kategori'] ?? '-' }}
            </div>
            <div class="text-muted small">
                {{ $kategoriTerbaik['persentase'] ?? 0 }}% capaian
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8 col-lg-8">
        <div class="card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
                <h4 class="fw-bold mb-0">Progress KM Saya</h4>

                <a href="/anggota/progress-km" class="btn btn-primary btn-sm">
                    Lihat Detail Progress
                </a>
            </div>

            <table class="table align-middle mb-0 anggota-dashboard-table">
                <thead>
                    <tr>
                        <th style="width: 7%;">No</th>
                        <th style="width: 23%;">Kategori</th>
                        <th style="width: 12%;">Target</th>
                        <th style="width: 14%;">Realisasi</th>
                        <th style="width: 26%;">Progress</th>
                        <th style="width: 18%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($progress as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-bold">{{ $item['kategori'] }}</td>
                            <td>{{ $item['target'] }}</td>
                            <td>{{ $item['realisasi'] }}</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ $item['persentase'] }}%;"></div>
                                </div>
                                <div class="small mt-1">{{ $item['persentase'] }}%</div>
                            </td>
                            <td>
                                @if($item['status'] === 'Tercapai')
                                    <span class="badge bg-success status-badge">Tercapai</span>
                                @else
                                    <span class="badge bg-danger status-badge">Belum Tercapai</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-xl-4 col-lg-4">
        <div class="card h-100">
            <h4 class="fw-bold mb-3">Informasi Akun</h4>

            <table class="table align-middle mb-0">
                <tbody>
                    <tr>
                        <th>Username</th>
                        <td>{{ $user->username }}</td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td>{{ $user->role }}</td>
                    </tr>
                    <tr>
                        <th>NIDN</th>
                        <td>{{ $dosen->nidn ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $dosen->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Lab Riset</th>
                        <td>{{ $dosen->nama_lab ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-3 d-grid gap-2">
                <a href="/anggota/profil" class="btn btn-primary btn-sm">
                    Lihat Profil
                </a>
                <a href="/anggota/riwayat-realisasi" class="btn btn-secondary btn-sm">
                    Riwayat Realisasi
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
        <h4 class="fw-bold mb-0">Aktivitas KM Terbaru</h4>

        <a href="/anggota/aktivitas-km" class="btn btn-primary btn-sm">
            Kelola Aktivitas
        </a>
    </div>

    <table class="table align-middle mb-0" style="table-layout: fixed; width: 100%; font-size: 14px;">
        <thead>
            <tr>
                <th style="width: 7%;">No</th>
                <th style="width: 18%;">Kategori</th>
                <th style="width: 28%;">Judul Aktivitas</th>
                <th style="width: 25%;">Deskripsi</th>
                <th style="width: 11%;">Mulai</th>
                <th style="width: 11%;">Selesai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($aktivitasTerbaru as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->kategori_km }}</td>
                    <td class="fw-bold">{{ $item->judul_aktivitas }}</td>
                    <td>{{ $item->deskripsi_singkat ?? '-' }}</td>
                    <td>{{ $item->tanggal_mulai }}</td>
                    <td>{{ $item->tanggal_selesai }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Belum ada aktivitas KM terbaru.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection