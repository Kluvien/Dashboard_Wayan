@extends('layouts.app')

@section('title', 'Dashboard Ketua Lab')

@section('content')
<style>
    .dashboard-compact-table {
        table-layout: fixed;
        width: 100%;
        font-size: 13px;
    }

    .dashboard-compact-table th,
    .dashboard-compact-table td {
        padding: 12px 10px !important;
        white-space: normal;
        word-break: normal;
        vertical-align: middle;
    }

    .dashboard-compact-table .progress {
        height: 9px;
        background: #E5E7EB;
    }

    .dashboard-status-badge {
        font-size: 12px;
        padding: 6px 8px;
        white-space: normal;
        line-height: 1.2;
    }

    .notification-box {
        min-height: 96px;
    }
</style>

<div class="page-heading">
    Dashboard <span class="muted">Ketua Lab</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">{{ $lab->nama_lab ?? 'Laboratorium Riset' }}</h4>
            <p class="text-muted mb-0">
                Ringkasan capaian Kontrak Manajemen laboratorium riset tahun {{ $tahun }}.
            </p>
        </div>

        <a href="/ketualab/monitoring-lab" class="btn btn-primary">
            Lihat Monitoring Lab
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="text-muted fw-bold mb-2">Jumlah Anggota</div>
            <div class="fs-2 fw-bold">{{ $jumlahAnggota }}</div>
            <div class="text-muted small">Anggota pada lab ini</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <div class="text-muted fw-bold mb-2">Total Target</div>
            <div class="fs-2 fw-bold">{{ $totalTargetLab }}</div>
            <div class="text-muted small">Target KM tahun {{ $tahun }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <div class="text-muted fw-bold mb-2">Total Realisasi</div>
            <div class="fs-2 fw-bold">{{ $totalRealisasiLab }}</div>
            <div class="text-muted small">Aktivitas KM tercatat</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <div class="text-muted fw-bold mb-2">Persentase Capaian</div>
            <div class="fs-2 fw-bold">{{ min($persentaseTotal, 100) }}%</div>
            <div class="text-muted small">Capaian lab riset</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8 col-lg-8">
        <div class="card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
                <h4 class="fw-bold mb-0">Progress KM Per Kategori</h4>

                <a href="/ketualab/laporan" class="btn btn-primary btn-sm">
                    Lihat Laporan
                </a>
            </div>

            <table class="table align-middle mb-0 dashboard-compact-table">
                <thead>
                    <tr>
                        <th style="width: 6%;">No</th>
                        <th style="width: 22%;">Kategori</th>
                        <th style="width: 10%;">Target</th>
                        <th style="width: 12%;">Realisasi</th>
                        <th style="width: 27%;">Progress</th>
                        <th style="width: 23%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekapKategori as $index => $item)
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
                                    <span class="badge bg-success dashboard-status-badge">Tercapai</span>
                                @else
                                    <span class="badge bg-danger dashboard-status-badge">Belum Tercapai</span>
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
            <h4 class="fw-bold mb-3">Pemberitahuan Sistem</h4>

            <div class="p-3 mb-3 rounded notification-box" style="background:#EAF1FF;">
                <div class="fw-bold">Monitoring lab aktif</div>
                <div class="text-muted small">
                    Data dashboard dihitung dari target KM dan aktivitas KM anggota.
                </div>
            </div>

            <div class="p-3 mb-3 rounded notification-box" style="background:#FFF7E6;">
                <div class="fw-bold">Target dari database</div>
                <div class="text-muted small">
                    Target diambil dari tabel target_km dan kontrak_manajemen.
                </div>
            </div>

            <div class="p-3 rounded notification-box" style="background:#EFFFFB;">
                <div class="fw-bold">Realisasi berjalan</div>
                <div class="text-muted small">
                    Saat ini ada {{ $totalRealisasiLab }} aktivitas KM pada lab ini.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
        <h4 class="fw-bold mb-0">Aktivitas KM Terbaru</h4>

        <a href="/ketualab/monitoring-anggota" class="btn btn-primary btn-sm">
            Monitoring Anggota
        </a>
    </div>

    <table class="table align-middle mb-0" style="table-layout: fixed; width: 100%; font-size: 14px;">
        <thead>
            <tr>
                <th style="width: 7%;">No</th>
                <th style="width: 20%;">Nama Anggota</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 28%;">Judul Aktivitas</th>
                <th style="width: 15%;">Tanggal Mulai</th>
                <th style="width: 15%;">Tanggal Selesai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($aktivitasTerbaru as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->nama_dosen ?? $item->username }}</td>
                    <td>{{ $item->kategori_km }}</td>
                    <td class="fw-bold">{{ $item->judul_aktivitas }}</td>
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