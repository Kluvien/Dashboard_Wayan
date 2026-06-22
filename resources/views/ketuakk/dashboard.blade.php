@extends('layouts.app')

@section('title', 'Dashboard Ketua KK')

@section('content')
<div class="page-heading">
    Dashboard <span class="muted">Ketua KK</span>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="text-muted fw-bold mb-2">Jumlah Lab Riset</div>
            <div class="fs-2 fw-bold">{{ $totalLab }}</div>
            <div class="text-muted small">Total laboratorium riset aktif</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="text-muted fw-bold mb-2">Jumlah Dosen</div>
            <div class="fs-2 fw-bold">{{ $totalDosen }}</div>
            <div class="text-muted small">Total dosen anggota KK</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="text-muted fw-bold mb-2">Target KM</div>
            <div class="fs-2 fw-bold">{{ $totalTargetKm }}</div>
            <div class="text-muted small">Total target tahun {{ $tahun }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="text-muted fw-bold mb-2">Capaian KM</div>
            <div class="fs-2 fw-bold">{{ min($rataCapaian, 100) }}%</div>
            <div class="text-muted small">{{ $totalRealisasiKm }} realisasi dari {{ $totalTargetKm }} target</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">Pencapaian Kontrak Manajemen KK</h4>
                <span class="badge bg-primary">Tahun {{ $tahun }}</span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Target</th>
                            <th>Realisasi</th>
                            <th>Progress</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekapKategori as $item)
                            <tr>
                                <td class="fw-bold">{{ $item['kategori'] }}</td>
                                <td>{{ $item['target'] }}</td>
                                <td>{{ $item['realisasi'] }}</td>
                                <td style="min-width: 180px;">
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" style="width: {{ $item['persentase'] }}%;"></div>
                                    </div>
                                    <div class="small mt-1">{{ $item['persentase'] }}%</div>
                                </td>
                                <td>
                                    @if($item['status'] === 'Tercapai')
                                        <span class="status-success">Tercapai</span>
                                    @else
                                        <span class="status-danger">Belum Tercapai</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <h4 class="fw-bold mb-3">Pemberitahuan Sistem</h4>

            <div class="p-3 mb-3 rounded" style="background:#EAF1FF;">
                <div class="fw-bold">Monitoring KM aktif</div>
                <div class="text-muted small">
                    Sistem menghitung capaian berdasarkan aktivitas KM anggota dan target KM aktif tahun {{ $tahun }}.
                </div>
            </div>

            <div class="p-3 mb-3 rounded" style="background:#FFF7E6;">
                <div class="fw-bold">Target dari database</div>
                <div class="text-muted small">
                    Data target diambil dari tabel target_km dan kontrak_manajemen.
                </div>
            </div>

            <div class="p-3 rounded" style="background:#EFFFFB;">
                <div class="fw-bold">Total realisasi</div>
                <div class="text-muted small">
                    Saat ini terdapat {{ $totalRealisasiKm }} aktivitas KM yang sudah tercatat.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Pencapaian Kontrak Manajemen Lab Riset</h4>
        <a href="/ketuakk/data-lab-riset" class="btn btn-primary">Lihat Data Lab</a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Lab Riset</th>
                    <th>Jumlah Dosen</th>
                    <th>Target</th>
                    <th>Realisasi</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekapLab as $index => $lab)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $lab['nama_lab'] }}</td>
                        <td>{{ $lab['jumlah_dosen'] }}</td>
                        <td>{{ $lab['target'] }}</td>
                        <td>{{ $lab['realisasi'] }}</td>
                        <td style="min-width: 180px;">
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar" style="width: {{ $lab['persentase'] }}%;"></div>
                            </div>
                            <div class="small mt-1">{{ $lab['persentase'] }}%</div>
                        </td>
                        <td>
                            @if($lab['status'] === 'Tercapai')
                                <span class="status-success">Tercapai</span>
                            @else
                                <span class="status-danger">Belum Tercapai</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Kontrak Manajemen Masuk</h4>
        <a href="/ketuakk/target-km" class="btn btn-primary">Kelola Target</a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Dosen</th>
                    <th>Lab Riset</th>
                    <th>Tahun</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kontrakMasuk as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->nama_dosen ?? '-' }}</td>
                        <td>{{ $item->nama_lab ?? '-' }}</td>
                        <td>{{ $item->tahun_km }}</td>
                        <td>
                            <span class="badge bg-primary">{{ $item->status_km }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Belum ada kontrak manajemen.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection