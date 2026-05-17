@extends('layouts.app')

@section('title', 'Penurunan Kontrak Manajemen')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 fw-bold text-primary">Daftar Target dari Ketua KK (Untuk Distribusi Lab)</h6>
    </div>
    <div class="card-body">
        <p class="text-muted small">Berikut adalah indikator target yang diturunkan oleh Ketua KK. Silakan distribusikan atau pantau pembagian tugas ke anggota laboratorium.</p>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>Indikator Target KK</th>
                        <th width="15%">Volume Target</th>
                        <th width="20%">Aksi Distribusi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($targets as $index => $t)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $t->indikator }}</td>
                        <td><span class="badge bg-secondary fs-6">{{ $t->target }}</span></td>
                        <td>
                            <a href="/ketualab/penurunan-km/{{ $t->id_target }}/plot" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-diagram-3 me-1"></i> Plot Anggota
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Belum ada data target dari Ketua KK.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection