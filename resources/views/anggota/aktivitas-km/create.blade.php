@extends('layouts.app')

@section('title', 'Tambah Aktivitas KM')

@section('content')
<style>
    .form-card {
        max-width: 980px;
    }

    .form-section-title {
        font-size: 14px;
        font-weight: 700;
        color: #477EF7;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .interactive-input,
    .interactive-select,
    .interactive-textarea {
        border-radius: 12px !important;
        border: 1px solid #D8DEE8 !important;
        background: #fff;
        transition: all 0.2s ease;
        box-shadow: none !important;
    }

    .interactive-input,
    .interactive-select {
        min-height: 48px;
    }

    .interactive-textarea {
        min-height: 140px;
        resize: vertical;
    }

    .interactive-input:focus,
    .interactive-select:focus,
    .interactive-textarea:focus {
        border-color: #477EF7 !important;
        box-shadow: 0 0 0 0.2rem rgba(71, 126, 247, 0.15) !important;
        background: #FCFDFF;
    }

    .field-icon {
        position: absolute;
        top: 50%;
        left: 14px;
        transform: translateY(-50%);
        color: #8A8D91;
        z-index: 2;
    }

    .field-with-icon .form-control,
    .field-with-icon .form-select {
        padding-left: 42px;
    }

    .helper-text {
        font-size: 13px;
        color: #8A8D91;
        margin-top: 6px;
    }

    .form-label {
        font-weight: 700;
        margin-bottom: 8px;
        color: #20242A;
    }

    .sticky-action {
        position: sticky;
        bottom: 0;
        background: #fff;
        padding-top: 18px;
    }

    .btn-light-custom {
        background: #EEF2F7;
        border: 1px solid #D8DEE8;
        color: #20242A;
    }

    .btn-light-custom:hover {
        background: #E4EAF3;
    }
</style>

<div class="page-heading">
    Tambah <span class="muted">Aktivitas KM</span>
</div>

<div class="card mb-4 form-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Form Tambah Aktivitas KM</h4>
            <p class="text-muted mb-0">
                Isi data aktivitas Kontrak Manajemen yang sudah Anda kerjakan.
            </p>
        </div>

        <a href="/anggota/aktivitas-km" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger rounded-4 mb-4">
        <strong class="d-block mb-2">Terjadi kesalahan:</strong>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="/anggota/aktivitas-km" method="POST">
        @csrf

        <div class="mb-4">
            <div class="form-section-title">Informasi Utama</div>
            <div class="row g-4">
                <div class="col-md-6">
                    <label for="kategori_km" class="form-label">Kategori KM</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-grid field-icon"></i>
                        <select name="kategori_km" id="kategori_km"
                            class="form-select interactive-select @error('kategori_km') is-invalid @enderror" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Pendidikan" {{ old('kategori_km') == 'Pendidikan' ? 'selected' : '' }}>Pendidikan</option>
                            <option value="Penelitian" {{ old('kategori_km') == 'Penelitian' ? 'selected' : '' }}>Penelitian</option>
                            <option value="Publikasi" {{ old('kategori_km') == 'Publikasi' ? 'selected' : '' }}>Publikasi</option>
                            <option value="Pengabdian" {{ old('kategori_km') == 'Pengabdian' ? 'selected' : '' }}>Pengabdian</option>
                            <option value="Penunjang" {{ old('kategori_km') == 'Penunjang' ? 'selected' : '' }}>Penunjang</option>
                        </select>
                    </div>
                    <div class="helper-text">Pilih kategori sesuai KM yang ditugaskan kepada Anda.</div>
                    @error('kategori_km')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="judul_aktivitas" class="form-label">Judul Aktivitas</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-pencil-square field-icon"></i>
                        <input type="text" name="judul_aktivitas" id="judul_aktivitas"
                            class="form-control interactive-input @error('judul_aktivitas') is-invalid @enderror"
                            value="{{ old('judul_aktivitas') }}"
                            placeholder="Contoh: Menyusun laporan penelitian" required>
                    </div>
                    <div class="helper-text">Gunakan judul singkat, jelas, dan mudah dipahami.</div>
                    @error('judul_aktivitas')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="form-section-title">Deskripsi Aktivitas</div>
            <div>
                <label for="deskripsi_singkat" class="form-label">Deskripsi Singkat</label>
                <textarea name="deskripsi_singkat" id="deskripsi_singkat"
                    class="form-control interactive-textarea @error('deskripsi_singkat') is-invalid @enderror"
                    placeholder="Jelaskan secara singkat aktivitas yang Anda lakukan...">{{ old('deskripsi_singkat') }}</textarea>
                <div class="helper-text">
                    Isi dengan ringkasan pekerjaan, output, atau tujuan aktivitas.
                </div>
                @error('deskripsi_singkat')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-4">
            <div class="form-section-title">Periode Pelaksanaan</div>
            <div class="row g-4">
                <div class="col-md-6">
                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-calendar-event field-icon"></i>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                            class="form-control interactive-input @error('tanggal_mulai') is-invalid @enderror"
                            value="{{ old('tanggal_mulai') }}" required>
                    </div>
                    @error('tanggal_mulai')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-calendar-check field-icon"></i>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                            class="form-control interactive-input @error('tanggal_selesai') is-invalid @enderror"
                            value="{{ old('tanggal_selesai') }}" required>
                    </div>
                    @error('tanggal_selesai')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="sticky-action">
            <div class="d-flex flex-wrap gap-2 pt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Simpan Aktivitas
                </button>

                <a href="/anggota/aktivitas-km" class="btn btn-light-custom">
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>
@endsection