@extends('layouts.app')

@section('title', 'Monitoring Anggota KK')

@section('content')
<div class="page-heading">
    Monitoring <span class="muted">Anggota KK</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1">Monitoring Capaian KM Anggota KK</h4>
            <p class="text-muted mb-0">
                Menampilkan capaian target dan realisasi seluruh anggota KK tahun {{ $tahun }}.
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
                    <th>Nama Anggota</th>
                    <th>NIDN</th>
                    <th>Email</th>
                    <th>Lab Riset</th>
                    <th>Total Target</th>
                    <th>Total Realisasi</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dataMonitoring as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $item['nama_dosen'] }}</td>
                        <td>{{ $item['nidn'] }}</td>
                        <td>{{ $item['email'] }}</td>
                        <td>{{ $item['nama_lab'] }}</td>
                        <td>{{ $item['total_target'] }}</td>
                        <td>{{ $item['total_realisasi'] }}</td>
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
                        <td>
                            <a href="/ketuakk/monitoring-anggota-kk/{{ $item['id_user'] }}" class="btn btn-primary btn-sm">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">Belum ada akun anggota KK.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection