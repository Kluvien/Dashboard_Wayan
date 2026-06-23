@extends('layouts.app')

@section('title', 'Riwayat Realisasi')

@section('content')
<style>
    .km-table th {
        white-space: nowrap;
        vertical-align: middle;
    }

    .km-table td {
        vertical-align: middle;
    }
</style>

<div class="page-heading">
    Riwayat <span class="muted">Realisasi KM</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Riwayat Realisasi KM Saya</h4>
            <p class="text-muted mb-0">
                Menampilkan seluruh aktivitas KM yang telah Anda input sebagai realisasi Kontrak Manajemen.
            </p>
        </div>

        <a href="/anggota/dashboard" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Daftar Riwayat Realisasi</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 6%;">No</th>
                    <th style="width: 18%;">Kategori KM</th>
                    <th style="width: 24%;">Judul Aktivitas</th>
                    <th style="width: 28%;">Deskripsi</th>
                    <th style="width: 12%;">Tanggal Mulai</th>
                    <th style="width: 12%;">Tanggal Selesai</th>
                </tr>
            </thead>

            <tbody>
                @forelse($aktivitas as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $item->kategori_km ?? '-' }}
                    </td>

                    <td>
                        {{ $item->judul_aktivitas ?? '-' }}
                    </td>

                    <td>
                        {{ $item->deskripsi_singkat ?? '-' }}
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') }}
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Belum ada riwayat realisasi KM.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection