@extends('layouts.app')

@section('title', 'Detail Monitoring Lab Riset')

@section('content')
<div class="page-heading">
    Detail <span class="muted">Monitoring Lab Riset</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1">{{ $lab->nama_lab }}</h4>
            <p class="text-muted mb-0">
                Detail capaian Kontrak Manajemen lab riset tahun {{ $tahun }}.
            </p>
        </div>

        <a href="/ketuakk/monitoring-lab-riset" class="btn btn-primary">
            Kembali
        </a>
    </div>

    <h5 class="fw-bold mb-3">Rekap Per Kategori</h5>

    <div class="table-responsive mb-4">
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

    <h5 class="fw-bold mb-3">Daftar Aktivitas Anggota</h5>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Anggota</th>
                    <th>NIDN</th>
                    <th>Kategori</th>
                    <th>Judul Aktivitas</th>
                    <th>Deskripsi</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Selesai</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aktivitas as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->nama_dosen ?? $item->username }}</td>
                        <td>{{ $item->nidn ?? '-' }}</td>
                        <td>{{ $item->kategori_km }}</td>
                        <td class="fw-bold">{{ $item->judul_aktivitas }}</td>
                        <td>{{ $item->deskripsi_singkat ?? '-' }}</td>
                        <td>{{ $item->tanggal_mulai }}</td>
                        <td>{{ $item->tanggal_selesai }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">Belum ada aktivitas pada lab riset ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection