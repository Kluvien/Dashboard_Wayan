@extends('layouts.app')

@section('title', 'Kelola Target KM')

@section('content')
<div class="page-heading">
    Kelola <span class="muted">Target KM</span>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Daftar Target Kontrak Manajemen</h4>
            <p class="text-muted mb-0">
                Halaman ini digunakan untuk mengelola indikator dan nilai target Kontrak Manajemen.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="/ketuakk/km-kk" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>

            <a href="/ketuakk/target-km/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Tambah Target
            </a>
        </div>
    </div>

    <hr>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Indikator Target</th>
                    <th width="15%">Nilai Target</th>
                    <th width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($targets as $index => $t)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $t->indikator }}</td>
                        <td>{{ $t->target }}</td>
                        <td>
                            <a href="/ketuakk/target-km/{{ $t->id_target }}/edit" class="btn btn-warning btn-sm text-white">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <form action="/ketuakk/target-km/{{ $t->id_target }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus target ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Belum ada data target KM yang ditambahkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection