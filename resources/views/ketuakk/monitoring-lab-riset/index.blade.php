@extends('layouts.app')

@section('title', 'Monitoring Lab Riset')

@section('content')
<div class="page-heading">
    Monitoring <span class="muted">Lab Riset</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1">Monitoring Capaian KM Lab Riset</h4>
            <p class="text-muted mb-0">
                Menampilkan capaian target dan realisasi Kontrak Manajemen seluruh lab riset tahun {{ $tahun }}.
            </p>
        </div>

        <a href="/ketuakk/dashboard" class="btn btn-primary">
            Kembali ke Dashboard
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Lab Riset</th>
                    <th>Jumlah Dosen</th>
                    <th>Total Target</th>
                    <th>Total Realisasi</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monitoringLabs as $index => $lab)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $lab['nama_lab'] }}</td>
                        <td>{{ $lab['jumlah_dosen'] }}</td>
                        <td>{{ $lab['total_target'] }}</td>
                        <td>{{ $lab['total_realisasi'] }}</td>
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
                        <td>
                            <a href="/ketuakk/monitoring-lab-riset/{{ $lab['id_lab'] }}" class="btn btn-primary btn-sm">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">Belum ada data lab riset.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection