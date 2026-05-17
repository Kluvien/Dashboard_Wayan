@extends('layouts.app')

@section('title', 'Update Realisasi KM')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">Input Capaian Target</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-secondary border-0">
                    <small class="d-block text-muted">Indikator Target:</small>
                    <span class="fw-bold">{{ $realisasi->indikator }}</span>
                    <hr class="my-2">
                    <small class="d-block text-muted">Volume Target yang Harus Dicapai:</small>
                    <span class="badge bg-dark fs-6">{{ $realisasi->target }}</span>
                </div>

                <form action="/anggota/realisasi-km/{{ $realisasi->id_realisasi }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Angka Capaian Saat Ini</label>
                        <input type="number" name="realisasi" class="form-control form-control-lg" value="{{ $realisasi->realisasi }}" min="0" required>
                        <div class="form-text">Masukkan total angka yang sudah Anda kerjakan/capai. Status akan otomatis menjadi "Tercapai" jika angka memenuhi target.</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary px-4">Simpan Progress</button>
                    <a href="/anggota/realisasi-km" class="btn btn-light border px-4">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection