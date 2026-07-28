@extends('layouts.app')

@section('title', 'Turunkan KM ke Lab Riset')

@section('content')
@php
    $targetOptions = collect($targetOptions ?? []);
    $selectedTargetId = old('id_target', $idTargetTerpilih ?? request('id_target'));
@endphp

<style>
    .ketuakk-lab-assign-form { max-width:1080px; overflow:hidden; border:1px solid #E2E8F0; border-radius:14px; background:#FFF; }
    .ketuakk-lab-assign-form__header { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; padding:20px 22px; border-bottom:1px solid #E2E8F0; flex-wrap:wrap; }
    .ketuakk-lab-assign-form__title { margin:0; color:#0F172A; font-size:22px; font-weight:700; }
    .ketuakk-lab-assign-form__description { margin:5px 0 0; color:#64748B; font-size:13px; }
    .ketuakk-lab-assign-form__body { padding:20px 22px; }
    .form-card {
        max-width: 1080px;
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
    .interactive-select,
    .interactive-textarea {
        border-radius: 12px !important;
        border: 1px solid #D8DEE8 !important;
        background: #ffffff;
        transition: all 0.2s ease;
        box-shadow: none !important;
        min-height: 50px;
    }

    .interactive-textarea {
        min-height: 86px;
        padding-top: 12px;
    }

    .interactive-input:focus,
    .interactive-select:focus,
    .interactive-textarea:focus {
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

    .triwulan-info-card {
        border: 1px solid #DCE4F2;
        border-radius: 12px;
        padding: 14px;
        background: #F8FAFD;
        height: 100%;
    }

    .triwulan-info-label {
        font-size: 12px;
        color: #64748B;
        margin-bottom: 5px;
    }

    .triwulan-info-value {
        font-size: 22px;
        font-weight: 800;
        color: #1E293B;
    }

    .total-km-box {
        border-radius: 12px;
        background: #E8F4FF;
        border: 1px solid #B7E1FB;
        padding: 14px 16px;
        font-weight: 700;
    }
</style>

<section class="ketuakk-lab-assign-form" aria-labelledby="lab-assign-title">
    <header class="ketuakk-lab-assign-form__header">
        <div>
            <h1 id="lab-assign-title" class="ketuakk-lab-assign-form__title">Form Penurunan KM ke Lab Riset</h1>
            <p class="ketuakk-lab-assign-form__description">
                Ketua KK membagi target KM kepada Lab Riset berdasarkan periode triwulan.
            </p>
        </div>

        <a href="/ketuakk/km-lab-riset" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </header>

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

    <form action="/ketuakk/km-lab-riset" method="POST" class="ketuakk-lab-assign-form__body">
        @csrf

        <div class="mb-4">
            <div class="form-section-title">Informasi Penurunan KM</div>

            <div class="row g-4">
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

                    <div class="helper-text">
                        Pilih Lab Riset penerima target KM.
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="status_km" class="form-label">Status KM</label>

                    <div class="position-relative field-with-icon">
                        <i class="bi bi-toggle-on field-icon"></i>

                        <select name="status_km" id="status_km" class="form-select interactive-select" required>
                            <option value="Aktif" {{ old('status_km', 'Aktif') === 'Aktif' ? 'selected' : '' }}>
                                Aktif
                            </option>

                            <option value="Tidak Aktif" {{ old('status_km') === 'Tidak Aktif' ? 'selected' : '' }}>
                                Tidak Aktif
                            </option>
                        </select>
                    </div>

                    <div class="helper-text">
                        Gunakan status aktif untuk target yang sedang berjalan.
                    </div>
                </div>

                <div class="col-md-12">
                    <label for="id_target" class="form-label">Pilih Data Target KM Ketua KK</label>

                    <div class="position-relative field-with-icon">
                        <i class="bi bi-list-check field-icon"></i>

                        <select name="id_target" id="id_target" class="form-select interactive-select" required>
                            <option value="">-- Pilih Target KM --</option>

                            @foreach($targetOptions as $target)
                                <option
                                    value="{{ $target->id_target }}"
                                    data-tahun="{{ $target->tahun_km }}"
                                    data-kategori="{{ $target->kategori_km }}"
                                    data-sub="{{ $target->indikator }}"
                                    data-keterangan="{{ $target->keterangan ?? '' }}"
                                    data-total="{{ $target->target }}"
                                    data-tw1-sisa="{{ $target->sisa_tw1 }}"
                                    data-tw2-sisa="{{ $target->sisa_tw2 }}"
                                    data-tw3-sisa="{{ $target->sisa_tw3 }}"
                                    data-tw4-sisa="{{ $target->sisa_tw4 }}"
                                    {{ (string) $selectedTargetId === (string) $target->id_target ? 'selected' : '' }}>
                                    @php
                                        $sisaTargetBelumTurun =
                                            (int) ($target->sisa_tw1 ?? 0) +
                                            (int) ($target->sisa_tw2 ?? 0) +
                                            (int) ($target->sisa_tw3 ?? 0) +
                                            (int) ($target->sisa_tw4 ?? 0);
                                    @endphp
                                    {{ $target->tahun_km }} - {{ $target->kategori_km }} - {{ $target->indikator }} | Total Target: {{ $target->target }} | Sisa Target Belum Turun: {{ $sisaTargetBelumTurun }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="helper-text">
                        Target dapat dipilih langsung dari halaman Kelompok Keahlian.
                    </div>

                    @if($targetOptions->isEmpty())
                        <div class="alert alert-warning mt-3 mb-0">
                            Belum ada Target KM. Tambahkan Target KM terlebih dahulu pada menu Kelompok Keahlian.
                        </div>
                    @endif
                </div>

                <div class="col-md-4">
                    <label for="tahun_km" class="form-label">Tahun KM</label>

                    <div class="position-relative field-with-icon">
                        <i class="bi bi-calendar3 field-icon"></i>

                        <input
                            type="number"
                            id="tahun_km"
                            class="form-control interactive-input"
                            value="{{ old('tahun_km') }}"
                            readonly>
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="kategori_km" class="form-label">Kategori KM</label>

                    <div class="position-relative field-with-icon">
                        <i class="bi bi-grid field-icon"></i>

                        <input
                            type="text"
                            id="kategori_km"
                            class="form-control interactive-input"
                            value="{{ old('kategori_km') }}"
                            readonly>
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="sub_kategori_km" class="form-label">Sub Kategori KM</label>

                    <div class="position-relative field-with-icon">
                        <i class="bi bi-tags field-icon"></i>

                        <input
                            type="text"
                            id="sub_kategori_km"
                            class="form-control interactive-input"
                            value="{{ old('sub_kategori_km') }}"
                            readonly>
                    </div>
                </div>

                <div class="col-md-12">
                    <label for="keterangan_km" class="form-label">Keterangan Target KM</label>

                    <textarea
                        id="keterangan_km"
                        class="form-control interactive-textarea"
                        placeholder="Keterangan target KM akan tampil di sini."
                        readonly>{{ old('keterangan') }}</textarea>

                    <div class="helper-text">
                        Keterangan mengikuti Target KM yang dipilih.
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="form-section-title">Sisa Target KM yang Dapat Didistribusikan</div>

            <div class="row g-3">
                <div class="col-md-3">
                    <div class="triwulan-info-card">
                        <div class="triwulan-info-label">Sisa Triwulan 1</div>
                        <div class="triwulan-info-value" id="sisaInfoTw1">0</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="triwulan-info-card">
                        <div class="triwulan-info-label">Sisa Triwulan 2</div>
                        <div class="triwulan-info-value" id="sisaInfoTw2">0</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="triwulan-info-card">
                        <div class="triwulan-info-label">Sisa Triwulan 3</div>
                        <div class="triwulan-info-value" id="sisaInfoTw3">0</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="triwulan-info-card">
                        <div class="triwulan-info-label">Sisa Triwulan 4</div>
                        <div class="triwulan-info-value" id="sisaInfoTw4">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="form-section-title">Pembagian Jumlah KM ke Lab Riset</div>

            <div class="row g-4">
                <div class="col-md-3">
                    <label for="triwulan_1" class="form-label">Jumlah KM Triwulan 1</label>

                    <input
                        type="number"
                        name="triwulan_1"
                        id="triwulan_1"
                        class="form-control interactive-input"
                        value="{{ old('triwulan_1', 0) }}"
                        min="0"
                        required>

                    <div class="helper-text">Maksimal sesuai sisa target TW1.</div>
                </div>

                <div class="col-md-3">
                    <label for="triwulan_2" class="form-label">Jumlah KM Triwulan 2</label>

                    <input
                        type="number"
                        name="triwulan_2"
                        id="triwulan_2"
                        class="form-control interactive-input"
                        value="{{ old('triwulan_2', 0) }}"
                        min="0"
                        required>

                    <div class="helper-text">Maksimal sesuai sisa target TW2.</div>
                </div>

                <div class="col-md-3">
                    <label for="triwulan_3" class="form-label">Jumlah KM Triwulan 3</label>

                    <input
                        type="number"
                        name="triwulan_3"
                        id="triwulan_3"
                        class="form-control interactive-input"
                        value="{{ old('triwulan_3', 0) }}"
                        min="0"
                        required>

                    <div class="helper-text">Maksimal sesuai sisa target TW3.</div>
                </div>

                <div class="col-md-3">
                    <label for="triwulan_4" class="form-label">Jumlah KM Triwulan 4</label>

                    <input
                        type="number"
                        name="triwulan_4"
                        id="triwulan_4"
                        class="form-control interactive-input"
                        value="{{ old('triwulan_4', 0) }}"
                        min="0"
                        required>

                    <div class="helper-text">Maksimal sesuai sisa target TW4.</div>
                </div>

                <div class="col-md-12">
                    <div class="total-km-box">
                        Total KM yang Didistribusikan:
                        <span id="jumlahKmTotal">0</span>
                    </div>

                    <input type="hidden" id="jumlah_km" name="jumlah_km" value="0">
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>
                Turunkan KM
            </button>

            <a href="/ketuakk/km-lab-riset" class="btn btn-light-custom">
                Batal
            </a>
        </div>
    </form>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const targetSelect = document.getElementById('id_target');

        const tahunInput = document.getElementById('tahun_km');
        const kategoriInput = document.getElementById('kategori_km');
        const subKategoriInput = document.getElementById('sub_kategori_km');
        const keteranganInput = document.getElementById('keterangan_km');

        const tw1Input = document.getElementById('triwulan_1');
        const tw2Input = document.getElementById('triwulan_2');
        const tw3Input = document.getElementById('triwulan_3');
        const tw4Input = document.getElementById('triwulan_4');

        const totalInput = document.getElementById('jumlah_km');
        const totalText = document.getElementById('jumlahKmTotal');

        const sisaInfoTw1 = document.getElementById('sisaInfoTw1');
        const sisaInfoTw2 = document.getElementById('sisaInfoTw2');
        const sisaInfoTw3 = document.getElementById('sisaInfoTw3');
        const sisaInfoTw4 = document.getElementById('sisaInfoTw4');

        function angka(value) {
            return parseInt(value || 0, 10);
        }

        function updateTotal() {
            const total =
                angka(tw1Input.value) +
                angka(tw2Input.value) +
                angka(tw3Input.value) +
                angka(tw4Input.value);

            totalInput.value = total;
            totalText.textContent = total;
        }

        function resetFormInfo() {
            tahunInput.value = '';
            kategoriInput.value = '';
            subKategoriInput.value = '';
            keteranganInput.value = '';

            tw1Input.max = 0;
            tw2Input.max = 0;
            tw3Input.max = 0;
            tw4Input.max = 0;

            sisaInfoTw1.textContent = '0';
            sisaInfoTw2.textContent = '0';
            sisaInfoTw3.textContent = '0';
            sisaInfoTw4.textContent = '0';
        }

        function isiDataTarget(resetNilaiTriwulan = false) {
            const option = targetSelect.options[targetSelect.selectedIndex];

            if (!option || !option.value) {
                resetFormInfo();
                updateTotal();
                return;
            }

            const sisaTw1 = angka(option.dataset.tw1Sisa);
            const sisaTw2 = angka(option.dataset.tw2Sisa);
            const sisaTw3 = angka(option.dataset.tw3Sisa);
            const sisaTw4 = angka(option.dataset.tw4Sisa);

            tahunInput.value = option.dataset.tahun || '';
            kategoriInput.value = option.dataset.kategori || '';
            subKategoriInput.value = option.dataset.sub || '';
            keteranganInput.value = option.dataset.keterangan || '-';

            tw1Input.max = sisaTw1;
            tw2Input.max = sisaTw2;
            tw3Input.max = sisaTw3;
            tw4Input.max = sisaTw4;

            sisaInfoTw1.textContent = sisaTw1;
            sisaInfoTw2.textContent = sisaTw2;
            sisaInfoTw3.textContent = sisaTw3;
            sisaInfoTw4.textContent = sisaTw4;

            if (resetNilaiTriwulan) {
                tw1Input.value = 0;
                tw2Input.value = 0;
                tw3Input.value = 0;
                tw4Input.value = 0;
            }

            updateTotal();
        }

        targetSelect.addEventListener('change', function() {
            isiDataTarget(true);
        });

        [tw1Input, tw2Input, tw3Input, tw4Input].forEach(function(input) {
            input.addEventListener('input', updateTotal);
        });

        isiDataTarget(false);
        updateTotal();
    });
</script>
@endsection
