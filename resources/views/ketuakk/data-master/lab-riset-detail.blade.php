@extends('layouts.app')

@section('title', 'Detail Lab Riset')

@section('content')
<div class="page-heading">
    Detail <span class="muted">Lab Riset</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">{{ $lab->nama_lab }}</h4>
            <p class="text-muted mb-0">
                Detail data dosen anggota dan aktivitas KM pada laboratorium riset ini.
            </p>
        </div>

        <a href="/ketuakk/data-lab-riset" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Daftar Dosen Anggota Lab</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Dosen</th>
                    <th>NIDN</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dosen as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $item->nama_dosen }}</td>
                        <td>{{ $item->nidn }}</td>
                        <td>{{ $item->email }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Belum ada dosen pada lab riset ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Aktivitas KM Pada Lab Ini</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="table-layout: fixed; width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 6%;">No</th>
                    <th style="width: 18%;">Nama Anggota</th>
                    <th style="width: 12%;">Kategori</th>
                    <th style="width: 24%;">Judul Aktivitas</th>
                    <th style="width: 24%;">Deskripsi</th>
                    <th style="width: 8%;">Mulai</th>
                    <th style="width: 8%;">Selesai</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aktivitas as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->nama_dosen ?? $item->username }}</td>
                        <td>{{ $item->kategori_km }}</td>
                        <td class="fw-bold">{{ $item->judul_aktivitas }}</td>
                        <td>{{ $item->deskripsi_singkat ?? '-' }}</td>
                        <td>{{ $item->tanggal_mulai }}</td>
                        <td>{{ $item->tanggal_selesai }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Belum ada aktivitas KM pada lab riset ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection