@extends('layouts.app')

@section('title', 'Edit Aktivitas KM')

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
        min-height: 130px;
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
    Edit <span class="muted">Aktivitas KM</span>
</div>

<div class="card mb-4 form-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Form Edit Aktivitas KM</h4>
            <p class="text-muted mb-0">
                Perbarui KM yang dikerjakan dan update progres aktivitas Anda.
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

    @if($kmOptions->isEmpty())
    <div class="alert alert-warning rounded-4 mb-0">
        Belum ada KM yang ditugaskan kepada akun Anda. Ketua Lab perlu membagikan KM terlebih dahulu.
    </div>
    @else
    <form action="/anggota/aktivitas-km/{{ $aktivitas->id_aktivitas }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <div class="form-section-title">Pilih KM yang Dikerjakan</div>

            <div class="row g-4">
                <div class="col-md-12">
                    <label for="id_km_anggota" class="form-label">KM yang Diterima</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-list-check field-icon"></i>
                        <select name="id_km_anggota" id="id_km_anggota" class="form-select interactive-select" required>
                            <option value="">-- Pilih KM --</option>
                            @foreach($kmOptions as $km)
                            <option
                                value="{{ $km->id_km_anggota }}"
                                data-tahun="{{ $km->tahun_km }}"
                                data-lab="{{ $km->nama_lab }}"
                                data-kategori="{{ $km->kategori_km }}"
                                data-sub="{{ $km->sub_kategori_km }}"
                                data-jumlah="{{ $km->jumlah_km_anggota }}"
                                {{ (int) old('id_km_anggota', $aktivitas->id_km_anggota) === (int) $km->id_km_anggota ? 'selected' : '' }}>
                                {{ $km->tahun_km }} - {{ $km->kategori_km }} - {{ $km->sub_kategori_km }} | Target: {{ $km->jumlah_km_anggota }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="helper-text">
                        Pilihan ini berasal dari KM yang sudah dibagikan oleh Ketua Lab kepada Anda.
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tahun KM</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-calendar3 field-icon"></i>
                        <input type="text" id="preview_tahun" class="form-control interactive-input" readonly>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Kategori KM</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-grid field-icon"></i>
                        <input type="text" id="preview_kategori" class="form-control interactive-input" readonly>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Sub Kategori</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-tags field-icon"></i>
                        <input type="text" id="preview_sub" class="form-control interactive-input" readonly>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Target KM</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-123 field-icon"></i>
                        <input type="text" id="preview_jumlah" class="form-control interactive-input" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="form-section-title">Detail Progres Aktivitas</div>

            <div class="row g-4">
                <div class="col-md-6">
                    <label for="judul_aktivitas" class="form-label">Judul KM / Aktivitas</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-pencil-square field-icon"></i>
                        <input
                            type="text"
                            name="judul_aktivitas"
                            id="judul_aktivitas"
                            class="form-control interactive-input"
                            value="{{ old('judul_aktivitas', $aktivitas->judul_aktivitas) }}"
                            required>
                    </div>
                    <div class="helper-text">Isi judul progres atau pekerjaan KM yang sedang dilakukan.</div>
                </div>

                <div class="col-md-6">
                    <label for="status_progress" class="form-label">Status Progress</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-flag field-icon"></i>
                        <select name="status_progress" id="status_progress" class="form-select interactive-select" required>
                            <option value="On Progress" {{ old('status_progress', $aktivitas->status_progress ?? 'On Progress') == 'On Progress' ? 'selected' : '' }}>On Progress</option>
                            <option value="Submitted" {{ old('status_progress', $aktivitas->status_progress ?? '') == 'Submitted' ? 'selected' : '' }}>Submitted</option>
                            <option value="Accepted" {{ old('status_progress', $aktivitas->status_progress ?? '') == 'Accepted' ? 'selected' : '' }}>Accepted</option>
                            <option value="Rejected" {{ old('status_progress', $aktivitas->status_progress ?? '') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="helper-text">Pilih status perkembangan aktivitas KM.</div>
                </div>

                <div class="col-md-12">
                    <label for="deskripsi_singkat" class="form-label">Deskripsi KM</label>
                    <textarea
                        name="deskripsi_singkat"
                        id="deskripsi_singkat"
                        class="form-control interactive-textarea"
                        placeholder="Jelaskan progres, kendala, output, atau rencana lanjutan dari KM ini...">{{ old('deskripsi_singkat', $aktivitas->deskripsi_singkat) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-calendar-event field-icon"></i>
                        <input
                            type="date"
                            name="tanggal_mulai"
                            id="tanggal_mulai"
                            class="form-control interactive-input"
                            value="{{ old('tanggal_mulai', $aktivitas->tanggal_mulai) }}"
                            required>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-calendar-check field-icon"></i>
                        <input
                            type="date"
                            name="tanggal_selesai"
                            id="tanggal_selesai"
                            class="form-control interactive-input"
                            value="{{ old('tanggal_selesai', $aktivitas->tanggal_selesai) }}"
                            required>
                    </div>
                </div>

                <div class="col-md-12">
                    <label for="bukti_link" class="form-label">Bukti / Link Progress</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-link-45deg field-icon"></i>
                        <input
                            type="url"
                            name="bukti_link"
                            id="bukti_link"
                            class="form-control interactive-input"
                            value="{{ old('bukti_link', $aktivitas->bukti_link ?? '') }}"
                            placeholder="Contoh: https://drive.google.com/...">
                    </div>
                    <div class="helper-text">
                        Bisa berupa link progress, publikasi, dokumentasi, Google Drive, atau bukti lainnya.
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 pt-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Perbarui Aktivitas
            </button>

            <a href="/anggota/aktivitas-km" class="btn btn-light-custom">
                Batal
            </a>
        </div>
    </form>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const kmSelect = document.getElementById('id_km_anggota');

        if (!kmSelect) {
            return;
        }

        const tahunInput = document.getElementById('preview_tahun');
        const kategoriInput = document.getElementById('preview_kategori');
        const subInput = document.getElementById('preview_sub');
        const jumlahInput = document.getElementById('preview_jumlah');

        function fillKmInfo() {
            const selectedOption = kmSelect.options[kmSelect.selectedIndex];

            if (!selectedOption || !selectedOption.value) {
                tahunInput.value = '';
                kategoriInput.value = '';
                subInput.value = '';
                jumlahInput.value = '';
                return;
            }

            tahunInput.value = selectedOption.dataset.tahun || '';
            kategoriInput.value = selectedOption.dataset.kategori || '';
            subInput.value = selectedOption.dataset.sub || '';
            jumlahInput.value = selectedOption.dataset.jumlah || '';
        }

        kmSelect.addEventListener('change', fillKmInfo);
        fillKmInfo();
    });
</script>
@endsection