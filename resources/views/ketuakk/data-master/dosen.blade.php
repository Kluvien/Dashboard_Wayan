@extends('layouts.app')

@section('title', 'Data Dosen')

@section('content')
@php
$perPage = $perPage ?? request('per_page', 50);
$allowedPerPage = $allowedPerPage ?? [50, 100, 1000];
@endphp

<div class="page-heading">
    Data <span class="muted">Anggota KK</span>
</div>

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
        <div class="row g-2 align-items-center">
            <div class="col-12 col-lg-7">
                <input
                    type="text"
                    name="q"
                    value="{{ $q ?? '' }}"
                    class="form-control"
                    placeholder="Cari nama, NIDN, email, JAD, atau lab riset...">
            </div>

            <div class="col-12 col-lg-2">
                <select name="per_page" class="form-select">
                    @foreach($allowedPerPage as $option)
                    <option value="{{ $option }}" {{ (int) $perPage === (int) $option ? 'selected' : '' }}>
                        Tampilkan 1-{{ $option }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-lg-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        Cari
                    </button>

                    <a href="/ketuakk/data-dosen" class="btn btn-secondary flex-fill">
                        Reset
                    </a>
                </div>
            </div>
        </div>
    </form>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="text-muted small">
            @if($dosens->total() > 0)
            Menampilkan {{ $dosens->firstItem() }} - {{ $dosens->lastItem() }}
            dari {{ $dosens->total() }} data dosen
            @else
            Belum ada data dosen
            @endif
        </div>

        <div class="text-muted small">
            Halaman {{ $dosens->currentPage() }} dari {{ $dosens->lastPage() }}
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="table-layout: fixed; width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 9%;">No</th>
                    <th style="width: 20%;">Nama Dosen</th>
                    <th style="width: 11%;">NIDN</th>
                    <th style="width: 21%;">Email</th>
                    <th style="width: 9%;">JAD</th>
                    <th style="width: 22%;">Lab Riset</th>
                    <th style="width: 12%;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($dosens as $index => $dosen)
                <tr>
                    <td>
                        {{ $dosens->firstItem() + $index }}
                    </td>

                    <td class="fw-bold">
                        {{ $dosen->nama_dosen }}
                    </td>

                    <td>
                        {{ $dosen->nidn }}
                    </td>

                    <td style="word-break: break-word;">
                        {{ $dosen->email }}
                    </td>

                    <td>
                        <span class="badge bg-light text-dark border">
                            {{ $dosen->jad ?? '-' }}
                        </span>
                    </td>

                    <td>
                        {{ $dosen->nama_lab ?? '-' }}
                    </td>

                    <td>
                        <div class="d-flex flex-column gap-2">
                            <a href="/ketuakk/data-dosen/{{ $dosen->id_dosen }}/edit" class="btn btn-edit btn-sm">
                                Ubah
                            </a>

                            <form
                                action="/ketuakk/data-dosen/{{ $dosen->id_dosen }}"
                                method="POST"
                                class="js-delete-form"
                                data-message="Apakah Anda yakin ingin menghapus data dosen ini?">
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
                    <td colspan="7" class="text-center text-muted py-4">
                        Belum ada data dosen.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($dosens->hasPages())
    <div class="d-flex justify-content-end mt-4">
        {{ $dosens->links() }}
    </div>
    @endif
</div>
@endsection