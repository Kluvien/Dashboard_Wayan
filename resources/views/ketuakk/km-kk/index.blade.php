@extends('layouts.app')

@section('title', 'KM Kelompok Keahlian')

@section('content')
<div class="page-heading">
    Kontrak Manajemen <span class="muted">Kelompok Keahlian</span>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="text-muted fw-bold mb-2">Total Target KM</div>
            <div class="fs-2 fw-bold">{{ $totalTargetKm }}</div>
            <div class="text-muted small">Target aktif tahun {{ $tahun }}</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="text-muted fw-bold mb-2">Total Realisasi KM</div>
            <div class="fs-2 fw-bold">{{ $totalRealisasiKm }}</div>
            <div class="text-muted small">Berdasarkan aktivitas KM anggota</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="text-muted fw-bold mb-2">Persentase Capaian</div>
            <div class="fs-2 fw-bold">{{ min($persentaseTotal, 100) }}%</div>
            <div class="text-muted small">Capaian keseluruhan KK</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1">Pencapaian KM Kelompok Keahlian</h4>
            <p class="text-muted mb-0">
                Rekap target dan realisasi Kontrak Manajemen seluruh Kelompok Keahlian tahun {{ $tahun }}.
            </p>
        </div>

        <a href="/ketuakk/target-km" class="btn btn-primary">
            Kelola Target KM
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori KM</th>
                    <th>Target</th>
                    <th>Realisasi</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekapKategori as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
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

<div class="card">
    <h4 class="fw-bold mb-3">Rangkuman Pencapaian Lab Riset</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Lab Riset</th>
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
@endsection