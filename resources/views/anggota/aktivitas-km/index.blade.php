@extends('layouts.app')

@section('title', 'Aktivitas KM Saya')

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
    Aktivitas <span class="muted">KM Saya</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Daftar Aktivitas KM</h4>
            <p class="text-muted mb-0">
                Halaman ini digunakan untuk melihat, menambah, dan mengelola aktivitas Kontrak Manajemen Anda.
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="/anggota/dashboard" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>

            <a href="/anggota/aktivitas-km/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Tambah Aktivitas
            </a>
        </div>
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Aktivitas KM yang Telah Diinput</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 16%;">Kategori KM</th>
                    <th style="width: 22%;">Judul Aktivitas</th>
                    <th style="width: 22%;">Deskripsi Singkat</th>
                    <th style="width: 12%;">Mulai</th>
                    <th style="width: 12%;">Selesai</th>
                    <th style="width: 11%;">Aksi</th>
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

                    <td>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="/anggota/aktivitas-km/{{ $item->id_aktivitas }}/edit" class="btn btn-edit btn-sm">
                                Edit
                            </a>

                            <form
                                action="/anggota/aktivitas-km/{{ $item->id_aktivitas }}"
                                method="POST"
                                class="js-delete-form"
                                data-message="Apakah Anda yakin ingin menghapus aktivitas KM ini?">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-delete btn-sm">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Belum ada aktivitas KM.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection