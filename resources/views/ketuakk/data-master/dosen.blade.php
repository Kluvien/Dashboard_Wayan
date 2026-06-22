@extends('layouts.app')

@section('title', 'Data Dosen')

@section('content')
<div class="page-heading">
    Data <span class="muted">Anggota KK</span>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Daftar Data Anggota KK</h4>
            <p class="text-muted mb-0">
                Halaman ini digunakan untuk melihat dan mengelola data dosen anggota Kelompok Keahlian.
            </p>
        </div>

        <a href="/ketuakk/data-dosen/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Input Data Dosen
        </a>
    </div>

    <form action="/ketuakk/data-dosen" method="GET" class="mb-4">
        <div class="d-flex gap-2">
            <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Cari nama, NIDN, email, atau lab riset...">

            <button type="submit" class="btn btn-primary">
                Cari
            </button>

            <a href="/ketuakk/data-dosen" class="btn btn-secondary">
                Reset
            </a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="table-layout: fixed; width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 6%;">No</th>
                    <th style="width: 22%;">Nama Dosen</th>
                    <th style="width: 14%;">NIDN</th>
                    <th style="width: 23%;">Email</th>
                    <th style="width: 22%;">Lab Riset</th>
                    <th style="width: 13%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dosens as $index => $dosen)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $dosen->nama_dosen }}</td>
                        <td>{{ $dosen->nidn }}</td>
                        <td>{{ $dosen->email }}</td>
                        <td>{{ $dosen->nama_lab ?? '-' }}</td>
                        <td>
                            <div class="d-flex flex-column gap-2">
                                <a href="/ketuakk/data-dosen/{{ $dosen->id_dosen }}/edit" class="btn btn-edit btn-sm">
                                    Ubah
                                </a>

                                <form action="/ketuakk/data-dosen/{{ $dosen->id_dosen }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data dosen ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-delete btn-sm w-100">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Belum ada data dosen.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection