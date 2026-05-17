@extends('layouts.app')

@section('title', 'Realisasi Kontrak Manajemen')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 fw-bold text-primary">Tugas Target & Realisasi Saya</h6>
    </div>
    <div class="card-body">
        <p class="text-muted small">Berikut adalah daftar target KM yang ditugaskan kepada Anda. Silakan update angka realisasi secara berkala.</p>
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th>Indikator Target</th>
                        <th width="10%">Target</th>
                        <th width="10%">Realisasi</th>
                        <th width="15%">Status</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-center align-middle">
                    @forelse($realisasis as $index => $r)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="text-start">{{ $r->indikator }}</td>
                        <td><span class="badge bg-secondary fs-6">{{ $r->target }}</span></td>
                        
                        <td><span class="badge bg-primary fs-6">{{ $r->realisasi }}</span></td>
                        
                        <td>
                            @if($r->status_realisasi == 'Tercapai')
                                <span class="badge bg-success">Tercapai</span>
                            @else
                                <span class="badge bg-warning text-dark">Belum Tercapai</span>
                            @endif
                        </td>
                        <td>
                            <a href="/anggota/realisasi-km/{{ $r->id_realisasi }}/edit" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-pencil-square me-1"></i> Update
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-muted py-4">
                            Belum ada target yang di-plot untuk Anda saat ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection