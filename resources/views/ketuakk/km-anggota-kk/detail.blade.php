@extends('layouts.app')

@section('title', 'Detail KM Anggota KK')

@section('content')
@php
$kmDiterima = collect($kmDiterima ?? []);
$aktivitas = collect($aktivitas ?? []);
@endphp

<style>
    .km-table th {
        white-space: nowrap;
        vertical-align: middle;
        font-size: 13px;
    }

    .km-table td {
        vertical-align: middle;
        font-size: 13px;
    }

    .group-header {
        background: #F3F6FB;
        text-align: center;
        font-weight: 800;
    }

    .jad-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        background: #EAF1FF;
        color: #2563EB;
        font-weight: 700;
        font-size: 12px;
    }

    .progress-soft {
        height: 10px;
        border-radius: 999px;
        background: #E5E7EB;
        overflow: hidden;
        min-width: 120px;
    }

    .progress-soft-fill {
        height: 100%;
        border-radius: 999px;
        background: #477EF7;
    }
</style>

<div class="page-heading">
    Detail KM <span class="muted">Anggota KK</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">{{ $anggota->nama_dosen ?? $anggota->username }}</h4>
            <p class="text-muted mb-0">
                Detail target dan realisasi Kontrak Manajemen anggota KK tahun {{ $tahun }}.
            </p>
        </div>

        <a href="/ketuakk/km-anggota-kk?tahun={{ $tahun }}" class="btn btn-secondary">
            Kembali
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table">
            <tbody>
                <tr>
                    <th style="width: 220px;">Nama Anggota</th>
                    <td>{{ $anggota->nama_dosen ?? $anggota->username }}</td>
                </tr>
                <tr>
                    <th>NIDN</th>
                    <td>{{ $anggota->nidn ?? '-' }}</td>
                </tr>
                <tr>
                    <th>JAD</th>
                    <td>
                        <span class="jad-badge">
                            {{ $anggota->jad ?? '-' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $anggota->email ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Lab Riset</th>
                    <td>{{ $anggota->nama_lab ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td>{{ $anggota->role ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Daftar KM yang Diterima Anggota</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table" style="width: 100%;">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Kategori KM</th>
                    <th rowspan="2">Jenis KM</th>
                    <th rowspan="2">Sub Kategori</th>
                    <th colspan="4" class="group-header">Target KM per Triwulan</th>
                    <th colspan="4" class="group-header">Realisasi KM per Triwulan</th>
                    <th rowspan="2">Total Target</th>
                    <th rowspan="2">Total Realisasi</th>
                    <th rowspan="2">Progress</th>
                    <th rowspan="2">Status</th>
                </tr>
                <tr>
                    <th>TW1</th>
                    <th>TW2</th>
                    <th>TW3</th>
                    <th>TW4</th>
                    <th>TW1</th>
                    <th>TW2</th>
                    <th>TW3</th>
                    <th>TW4</th>
                </tr>
            </thead>

            <tbody>
                @forelse($kmDiterima as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $item['kategori_km'] ?? '-' }}
                    </td>

                    <td>
                        {{ $item['jenis_km'] ?? '-' }}
                    </td>

                    <td>
                        {{ $item['sub_kategori_km'] ?? '-' }}
                    </td>

                    <td class="text-center">{{ $item['target_tw1'] ?? 0 }}</td>
                    <td class="text-center">{{ $item['target_tw2'] ?? 0 }}</td>
                    <td class="text-center">{{ $item['target_tw3'] ?? 0 }}</td>
                    <td class="text-center">{{ $item['target_tw4'] ?? 0 }}</td>

                    <td class="text-center">{{ $item['realisasi_tw1'] ?? 0 }}</td>
                    <td class="text-center">{{ $item['realisasi_tw2'] ?? 0 }}</td>
                    <td class="text-center">{{ $item['realisasi_tw3'] ?? 0 }}</td>
                    <td class="text-center">{{ $item['realisasi_tw4'] ?? 0 }}</td>

                    <td class="fw-bold text-center">
                        {{ $item['total_target'] ?? 0 }}
                    </td>

                    <td class="fw-bold text-center">
                        {{ $item['total_realisasi'] ?? 0 }}
                    </td>

                    <td style="min-width: 160px;">
                        <div class="progress-soft mb-1">
                            <div class="progress-soft-fill" style="width: {{ $item['progress'] ?? 0 }}%;"></div>
                        </div>
                        <div class="small text-muted">
                            {{ $item['progress'] ?? 0 }}%
                        </div>
                    </td>

                    <td>
                        @if(($item['status'] ?? '') === 'Tercapai')
                        <span class="badge bg-success">Tercapai</span>
                        @else
                        <span class="badge bg-warning text-dark">Belum Tercapai</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="16" class="text-center text-muted py-4">
                        Belum ada KM yang diterima anggota ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Riwayat Aktivitas KM</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table" style="width: 100%;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori</th>
                    <th>Sub Kategori</th>
                    <th>Judul Aktivitas</th>
                    <th>Deskripsi</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Selesai</th>
                    <th>Status</th>
                    <th>Bukti</th>
                </tr>
            </thead>

            <tbody>
                @forelse($aktivitas as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="fw-bold">{{ $item->kategori_km ?? '-' }}</td>
                    <td>{{ $item->sub_kategori_km ?? '-' }}</td>
                    <td class="fw-bold">{{ $item->judul_aktivitas ?? '-' }}</td>
                    <td>{{ $item->deskripsi_singkat ?? '-' }}</td>
                    <td>{{ $item->tanggal_mulai ?? '-' }}</td>
                    <td>{{ $item->tanggal_selesai ?? '-' }}</td>
                    <td>
                        @php
                        $status = $item->status_progress ?? '-';
                        @endphp

                        @if($status === 'Accepted')
                        <span class="badge bg-success">Accepted</span>
                        @elseif($status === 'Rejected')
                        <span class="badge bg-danger">Rejected</span>
                        @elseif($status === 'Submitted')
                        <span class="badge bg-primary">Submitted</span>
                        @elseif($status === 'On Progress')
                        <span class="badge bg-warning text-dark">On Progress</span>
                        @else
                        <span class="badge bg-secondary">{{ $status }}</span>
                        @endif
                    </td>
                    <td>
                        @if(!empty($item->bukti_link))
                        <a href="{{ $item->bukti_link }}" target="_blank" class="btn btn-primary btn-sm">
                            Lihat
                        </a>
                        @else
                        -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        Belum ada aktivitas KM untuk anggota ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection