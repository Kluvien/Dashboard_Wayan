@extends('layouts.app')

@section('title', 'Turunkan KM ke Lab Riset')

@section('content')
<div class="page-heading">
    Turunkan <span class="muted">KM ke Lab Riset</span>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <div class="fw-bold mb-1">Terjadi kesalahan:</div>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Form Penurunan KM ke Lab Riset</h4>
            <p class="text-muted mb-0">
                Ketua KK menurunkan jumlah KM ke laboratorium riset tertentu.
            </p>
        </div>

        <a href="/ketuakk/km-lab-riset" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <form action="/ketuakk/km-lab-riset" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-bold">Tahun KM</label>
            <input type="number" name="tahun_km" value="{{ old('tahun_km', now()->year) }}" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Lab Riset Tujuan</label>
            <select name="id_lab" class="form-select">
                <option value="">-- Pilih Lab Riset --</option>
                @foreach($labs as $lab)
                    <option value="{{ $lab->id_lab }}" {{ old('id_lab') == $lab->id_lab ? 'selected' : '' }}>
                        {{ $lab->nama_lab }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Kategori KM</label>
            <select name="kategori_km" class="form-select">
                <option value="">-- Pilih Kategori --</option>
                <option value="Pendidikan" {{ old('kategori_km') == 'Pendidikan' ? 'selected' : '' }}>Pendidikan</option>
                <option value="Penelitian" {{ old('kategori_km') == 'Penelitian' ? 'selected' : '' }}>Penelitian</option>
                <option value="Publikasi" {{ old('kategori_km') == 'Publikasi' ? 'selected' : '' }}>Publikasi</option>
                <option value="Pengabdian" {{ old('kategori_km') == 'Pengabdian' ? 'selected' : '' }}>Pengabdian</option>
                <option value="Penunjang" {{ old('kategori_km') == 'Penunjang' ? 'selected' : '' }}>Penunjang</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Jumlah KM</label>
            <input type="number" name="jumlah_km" value="{{ old('jumlah_km') }}" class="form-control" placeholder="Contoh: 5">
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold">Status KM</label>
            <select name="status_km" class="form-select">
                <option value="Aktif" {{ old('status_km') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="Nonaktif" {{ old('status_km') == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                Simpan Penurunan KM
            </button>

            <a href="/ketuakk/km-lab-riset" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection