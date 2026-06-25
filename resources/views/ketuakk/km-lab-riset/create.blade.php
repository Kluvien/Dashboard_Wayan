@extends('layouts.app')

@section('title', 'Turunkan KM ke Lab Riset')

@section('content')
<style>
    .form-card {
        max-width: 980px;
    }

    .form-section-title {
        font-size: 14px;
        font-weight: 800;
        color: var(--blue);
        margin-bottom: 14px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .interactive-input,
    .interactive-select {
        border-radius: 12px !important;
        border: 1px solid #D8DEE8 !important;
        background: #ffffff;
        transition: all 0.2s ease;
        box-shadow: none !important;
        min-height: 50px;
    }

    .interactive-input:focus,
    .interactive-select:focus {
        border-color: var(--blue) !important;
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
        font-weight: 800;
        margin-bottom: 8px;
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
    Turunkan <span class="muted">KM ke Lab Riset</span>
</div>

<div class="card form-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Form Penurunan KM ke Lab Riset</h4>
            <p class="text-muted mb-0">
                Ketua KK menurunkan target Kontrak Manajemen kepada lab riset sesuai kategori.
            </p>
        </div>

        <a href="/ketuakk/km-lab-riset" class="btn btn-secondary">
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

    <form action="/ketuakk/km-lab-riset" method="POST">
        @csrf

        <div class="mb-4">
            <div class="form-section-title">Informasi Penurunan KM</div>

            <div class="row g-4">
                <div class="col-md-6">
                    <label for="tahun_km" class="form-label">Tahun KM</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-calendar3 field-icon"></i>
                        <input
                            type="number"
                            name="tahun_km"
                            id="tahun_km"
                            class="form-control interactive-input"
                            value="{{ old('tahun_km', now()->year) }}"
                            min="2020"
                            max="2100"
                            required>
                    </div>
                    <div class="helper-text">Tahun pelaksanaan Kontrak Manajemen.</div>
                </div>

                <div class="col-md-6">
                    <label for="id_lab" class="form-label">Lab Riset Tujuan</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-building field-icon"></i>
                        <select name="id_lab" id="id_lab" class="form-select interactive-select" required>
                            <option value="">-- Pilih Lab Riset --</option>
                            @foreach($labs as $lab)
                            <option value="{{ $lab->id_lab }}" {{ old('id_lab') == $lab->id_lab ? 'selected' : '' }}>
                                {{ $lab->nama_lab }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="helper-text">Pilih lab riset penerima target KM.</div>
                </div>

                <div class="col-md-6">
                    <label for="kategori_km" class="form-label">Kategori KM</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-grid field-icon"></i>
                        <select name="kategori_km" id="kategori_km" class="form-select interactive-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Pendidikan" {{ old('kategori_km') == 'Pendidikan' ? 'selected' : '' }}>Pendidikan</option>
                            <option value="Penelitian" {{ old('kategori_km') == 'Penelitian' ? 'selected' : '' }}>Penelitian</option>
                            <option value="Publikasi" {{ old('kategori_km') == 'Publikasi' ? 'selected' : '' }}>Publikasi</option>
                            <option value="Pengabdian" {{ old('kategori_km') == 'Pengabdian' ? 'selected' : '' }}>Pengabdian</option>
                            <option value="Penunjang" {{ old('kategori_km') == 'Penunjang' ? 'selected' : '' }}>Penunjang</option>
                        </select>
                    </div>
                    <div class="helper-text">Kategori ini akan menjadi dasar pembagian ke anggota.</div>
                </div>

                <div class="col-md-6">
                    <label for="sub_kategori_km" class="form-label">Sub Kategori KM</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-tags field-icon"></i>
                        <input
                            type="text"
                            name="sub_kategori_km"
                            id="sub_kategori_km"
                            class="form-control interactive-input"
                            value="{{ old('sub_kategori_km') }}"
                            placeholder="Contoh: Pendanaan Eksternal"
                            required>
                    </div>
                    <div class="helper-text">
                        Jelaskan indikator target atau sub kategori dari kategori KM yang dipilih.
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="jumlah_km" class="form-label">Jumlah KM</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-123 field-icon"></i>
                        <input
                            type="number"
                            name="jumlah_km"
                            id="jumlah_km"
                            class="form-control interactive-input"
                            value="{{ old('jumlah_km') }}"
                            min="1"
                            placeholder="Contoh: 5"
                            required>
                    </div>
                    <div class="helper-text">Masukkan jumlah target KM yang diturunkan.</div>
                </div>

                <div class="col-md-6">
                    <label for="status_km" class="form-label">Status KM</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-toggle-on field-icon"></i>
                        <select name="status_km" id="status_km" class="form-select interactive-select" required>
                            <option value="Aktif" {{ old('status_km', 'Aktif') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Tidak Aktif" {{ old('status_km') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="helper-text">Gunakan status aktif untuk target yang sedang berjalan.</div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan Penurunan KM
            </button>

            <a href="/ketuakk/km-lab-riset" class="btn btn-light-custom">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection