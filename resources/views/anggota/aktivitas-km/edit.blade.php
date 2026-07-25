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

    .upload-file-input {
        min-height: 52px;
        padding: 5px 12px 5px 46px !important;
        line-height: 40px;
    }

    .upload-file-input::file-selector-button {
        height: 40px;
        margin: -1px 12px -1px 0;
        padding: 0 18px;
        color: #20242A;
        background: #EEF2F7;
        border: 0;
        border-right: 1px solid #D8DEE8;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .upload-file-input:hover::file-selector-button {
        background: #E4EAF3;
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
        line-height: 1.45;
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

    .km-preview-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 16px;
    }

    .status-word-approved {
        color: #15803D;
        font-weight: 800;
    }

    .status-word-rejected {
        color: #DC2626;
        font-weight: 800;
    }

    @media (max-width: 992px) {
        .km-preview-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 576px) {
        .km-preview-grid {
            grid-template-columns: 1fr;
        }
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
    <form action="/anggota/aktivitas-km/{{ $aktivitas->id_aktivitas }}" method="POST" enctype="multipart/form-data">
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
                                data-target-tw1="{{ $km->target_tw_1 ?? 0 }}"
                                data-target-tw2="{{ $km->target_tw_2 ?? 0 }}"
                                data-target-tw3="{{ $km->target_tw_3 ?? 0 }}"
                                data-target-tw4="{{ $km->target_tw_4 ?? 0 }}"
                                data-sisa-tw1="{{ $km->sisa_tw_1 ?? 0 }}"
                                data-sisa-tw2="{{ $km->sisa_tw_2 ?? 0 }}"
                                data-sisa-tw3="{{ $km->sisa_tw_3 ?? 0 }}"
                                data-sisa-tw4="{{ $km->sisa_tw_4 ?? 0 }}"
                                {{ (int) old('id_km_anggota', $aktivitas->id_km_anggota) === (int) $km->id_km_anggota ? 'selected' : '' }}>
                                {{ $km->tahun_km }} - {{ $km->kategori_km }} - {{ $km->sub_kategori_km }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="helper-text">
                        Pilihan ini berasal dari KM yang sudah dibagikan oleh Ketua Lab kepada Anda.
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="km-preview-grid">
                        <div>
                            <label class="form-label">Tahun KM</label>
                            <div class="position-relative field-with-icon">
                                <i class="bi bi-calendar3 field-icon"></i>
                                <input type="text" id="preview_tahun" class="form-control interactive-input" readonly>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Kategori KM</label>
                            <div class="position-relative field-with-icon">
                                <i class="bi bi-grid field-icon"></i>
                                <input type="text" id="preview_kategori" class="form-control interactive-input" readonly>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Sub Kategori</label>
                            <div class="position-relative field-with-icon">
                                <i class="bi bi-tags field-icon"></i>
                                <input type="text" id="preview_sub" class="form-control interactive-input" readonly>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Target Triwulan Ini</label>
                            <div class="position-relative field-with-icon">
                                <i class="bi bi-calendar-range field-icon"></i>
                                <input type="text" id="preview_target_triwulan" class="form-control interactive-input" readonly>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Sisa Triwulan Ini</label>
                            <div class="position-relative field-with-icon">
                                <i class="bi bi-hourglass-split field-icon"></i>
                                <input type="text" id="preview_sisa_triwulan" class="form-control interactive-input" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="helper-text">
                        Target dan sisa mengikuti triwulan dari Tanggal Mulai yang dipilih.
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
                    <label for="status_progress" class="form-label">Status Aktivitas</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-flag field-icon"></i>
                        <select name="status_progress" id="status_progress" class="form-select interactive-select" required>
                            <option value="On Progress" {{ old('status_progress', $aktivitas->status_progress ?? 'On Progress') === 'On Progress' ? 'selected' : '' }}>
                                Sedang Berjalan
                            </option>
                            <option value="Submitted" {{ old('status_progress', $aktivitas->status_progress ?? '') === 'Submitted' ? 'selected' : '' }}>
                                Ajukan Verifikasi
                            </option>
                        </select>
                    </div>
                    <div class="helper-text">
                        Status <span class="status-word-approved">Disetujui</span> atau
                        <span class="status-word-rejected">Ditolak</span> hanya dapat ditetapkan oleh Ketua Lab.
                        Pengajuan verifikasi wajib memiliki bukti file atau tautan.
                    </div>
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
                    <label for="bukti_file" class="form-label">Upload Bukti KM</label>
                    <div class="position-relative field-with-icon">
                        <i class="bi bi-file-earmark-arrow-up field-icon"></i>
                        <input
                            type="file"
                            name="bukti_file"
                            id="bukti_file"
                            class="form-control interactive-input upload-file-input"
                            accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg">
                    </div>
                    <div class="helper-text">
                        Upload bukti berupa PDF, PNG, JPG, atau JPEG. Jika upload gambar, sistem akan menyiapkan file PDF untuk diunduh Ketua Lab/Ketua KK. Maksimal 10 MB.
                    </div>

                    @if(!empty($aktivitas->bukti_pdf_path) || !empty($aktivitas->bukti_file_path))
                        <div class="mt-2">
                            <a href="/bukti-km/{{ $aktivitas->id_aktivitas }}/download" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i> Download Bukti Saat Ini
                            </a>
                            <span class="small text-muted ms-2">
                                {{ $aktivitas->bukti_file_nama_asli ?? 'File bukti tersimpan' }}
                            </span>
                        </div>
                    @endif
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
        const tanggalMulaiInput = document.getElementById('tanggal_mulai');

        if (!kmSelect) {
            return;
        }

        const tahunInput = document.getElementById('preview_tahun');
        const kategoriInput = document.getElementById('preview_kategori');
        const subInput = document.getElementById('preview_sub');
        const targetTriwulanInput = document.getElementById('preview_target_triwulan');
        const sisaTriwulanInput = document.getElementById('preview_sisa_triwulan');

        function getTriwulanAktif() {
            const nilaiTanggal = tanggalMulaiInput?.value;
            const tanggal = nilaiTanggal
                ? new Date(nilaiTanggal + 'T00:00:00')
                : new Date();

            return Math.floor(tanggal.getMonth() / 3) + 1;
        }

        function fillKmInfo() {
            const selectedOption = kmSelect.options[kmSelect.selectedIndex];

            if (!selectedOption || !selectedOption.value) {
                tahunInput.value = '';
                kategoriInput.value = '';
                subInput.value = '';
                targetTriwulanInput.value = '';
                sisaTriwulanInput.value = '';
                return;
            }

            const triwulanAktif = getTriwulanAktif();

            tahunInput.value = selectedOption.dataset.tahun || '';
            kategoriInput.value = selectedOption.dataset.kategori || '';
            subInput.value = selectedOption.dataset.sub || '';
            targetTriwulanInput.value = selectedOption.dataset['targetTw' + triwulanAktif] || 0;
            sisaTriwulanInput.value = selectedOption.dataset['sisaTw' + triwulanAktif] || 0;
        }

        kmSelect.addEventListener('change', fillKmInfo);
        tanggalMulaiInput?.addEventListener('change', fillKmInfo);
        fillKmInfo();
    });
</script>
@endsection
