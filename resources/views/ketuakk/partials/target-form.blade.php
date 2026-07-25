@php
    $isEdit = isset($target) && $target;

    $value = function ($field, $default = '') use ($isEdit, $target) {
        return old($field, $isEdit ? ($target->{$field} ?? $default) : $default);
    };

    $dateValue = function ($field) use ($isEdit, $target) {
        $oldValue = old($field);

        if ($oldValue !== null) {
            return $oldValue;
        }

        if (!$isEdit || empty($target->{$field})) {
            return '';
        }

        return \Carbon\Carbon::parse($target->{$field})->format('Y-m-d');
    };

    $triwulanData = [
        1 => [
            'label' => 'Triwulan 1',
            'target' => 'triwulan_1',
            'mulai' => 'tanggal_mulai_tw1',
            'selesai' => 'tanggal_selesai_tw1',
        ],
        2 => [
            'label' => 'Triwulan 2',
            'target' => 'triwulan_2',
            'mulai' => 'tanggal_mulai_tw2',
            'selesai' => 'tanggal_selesai_tw2',
        ],
        3 => [
            'label' => 'Triwulan 3',
            'target' => 'triwulan_3',
            'mulai' => 'tanggal_mulai_tw3',
            'selesai' => 'tanggal_selesai_tw3',
        ],
        4 => [
            'label' => 'Triwulan 4',
            'target' => 'triwulan_4',
            'mulai' => 'tanggal_mulai_tw4',
            'selesai' => 'tanggal_selesai_tw4',
        ],
    ];
@endphp

<style>
    .deadline-card {
        border: 1px solid #D9E2F0;
        border-radius: 14px;
        background: #FBFCFF;
        padding: 18px;
        height: 100%;
    }

    .deadline-card-title {
        font-size: 16px;
        font-weight: 800;
        margin-bottom: 14px;
        color: #1E293B;
    }

    .deadline-note {
        font-size: 12px;
        color: #64748B;
        margin-top: 8px;
    }

    .deadline-card.is-disabled {
        opacity: 0.55;
        background: #F8FAFC;
    }
</style>

<div class="card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ $formTitle }}</h4>
            <p class="text-muted mb-0">
                Atur target dan periode pelaksanaan KM pada setiap Triwulan.
            </p>
        </div>

        <a href="/ketuakk/target-km" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger rounded-4 mb-4">
            <strong class="d-block mb-2">Terjadi kesalahan:</strong>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $formAction }}" method="POST">
        @csrf

        @if($httpMethod !== 'POST')
            @method($httpMethod)
        @endif

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-bold">Tahun KM</label>

                <input
                    type="number"
                    name="tahun_km"
                    id="tahun_km"
                    class="form-control"
                    value="{{ $value('tahun_km', now()->year) }}"
                    min="2020"
                    max="2100"
                    required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Kategori KM</label>

                <select name="kategori_km" class="form-select" required>
                    <option value="">-- Pilih Kategori KM --</option>

                    @foreach($kategoriOptions as $kategori => $subKategoriList)
                        <option
                            value="{{ $kategori }}"
                            {{ $value('kategori_km') === $kategori ? 'selected' : '' }}>
                            {{ $kategori }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Jenis KM / Sub Kategori</label>

                <input
                    type="text"
                    name="indikator"
                    class="form-control"
                    value="{{ $value('indikator') }}"
                    placeholder="Contoh: Perkuliahan"
                    maxlength="255"
                    required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Keterangan KM</label>

                <input
                    type="text"
                    name="keterangan"
                    class="form-control"
                    value="{{ $value('keterangan') }}"
                    placeholder="Contoh: Jumlah perkuliahan aktif">
            </div>

            <div class="col-12">
                <div class="alert alert-info mb-0">
                    Deadline hanya wajib diisi bila jumlah target pada Triwulan tersebut lebih dari 0.
                </div>
            </div>

            @foreach($triwulanData as $nomor => $tw)
                <div class="col-lg-6">
                    <div
                        class="deadline-card js-deadline-card"
                        data-triwulan="{{ $nomor }}">

                        <div class="deadline-card-title">
                            {{ $tw['label'] }}
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Jumlah Target KM
                            </label>

                            <input
                                type="number"
                                name="{{ $tw['target'] }}"
                                id="{{ $tw['target'] }}"
                                class="form-control js-target-tw"
                                value="{{ $value($tw['target'], 0) }}"
                                min="0"
                                required>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Tanggal Mulai
                                </label>

                                <input
                                    type="date"
                                    name="{{ $tw['mulai'] }}"
                                    id="{{ $tw['mulai'] }}"
                                    class="form-control js-deadline-date"
                                    value="{{ $dateValue($tw['mulai']) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Tanggal Selesai
                                </label>

                                <input
                                    type="date"
                                    name="{{ $tw['selesai'] }}"
                                    id="{{ $tw['selesai'] }}"
                                    class="form-control js-deadline-date"
                                    value="{{ $dateValue($tw['selesai']) }}">
                            </div>
                        </div>

                        <div class="deadline-note">
                            Periode Triwulan akan otomatis mengikuti tahun KM yang dipilih.
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="col-12">
                <div class="alert alert-primary mb-0">
                    Total Target KM:
                    <strong id="totalTargetPreview">0</strong>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>
                {{ $submitLabel }}
            </button>

            <a href="/ketuakk/target-km" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tahunInput = document.getElementById('tahun_km');
        const targetInputs = document.querySelectorAll('.js-target-tw');
        const totalPreview = document.getElementById('totalTargetPreview');

        function pad(number) {
            return String(number).padStart(2, '0');
        }

        function buildQuarterDate(year, triwulan, isEndDate) {
            const startMonth = ((triwulan - 1) * 3) + 1;
            const month = isEndDate ? startMonth + 2 : startMonth;

            let day = 1;

            if (isEndDate) {
                day = new Date(year, month, 0).getDate();
            }

            return year + '-' + pad(month) + '-' + pad(day);
        }

        function syncDeadlineCard(triwulan) {
            const targetInput = document.getElementById('triwulan_' + triwulan);
            const mulaiInput = document.getElementById('tanggal_mulai_tw' + triwulan);
            const selesaiInput = document.getElementById('tanggal_selesai_tw' + triwulan);
            const card = document.querySelector(
                '.js-deadline-card[data-triwulan="' + triwulan + '"]'
            );

            if (!targetInput || !mulaiInput || !selesaiInput || !card) {
                return;
            }

            let tahun = parseInt(tahunInput.value || 0);

            if (!tahun || tahun < 2020) {
                tahun = new Date().getFullYear();
            }

            const jumlahTarget = parseInt(targetInput.value || 0);
            const aktif = jumlahTarget > 0;

            const tanggalMulaiDefault = buildQuarterDate(tahun, triwulan, false);
            const tanggalSelesaiDefault = buildQuarterDate(tahun, triwulan, true);

            mulaiInput.min = tanggalMulaiDefault;
            mulaiInput.max = tanggalSelesaiDefault;
            selesaiInput.min = tanggalMulaiDefault;
            selesaiInput.max = tanggalSelesaiDefault;

            mulaiInput.disabled = !aktif;
            selesaiInput.disabled = !aktif;

            mulaiInput.required = aktif;
            selesaiInput.required = aktif;

            card.classList.toggle('is-disabled', !aktif);

            if (!aktif) {
                mulaiInput.value = '';
                selesaiInput.value = '';
                return;
            }

            if (
                !mulaiInput.value ||
                mulaiInput.value < tanggalMulaiDefault ||
                mulaiInput.value > tanggalSelesaiDefault
            ) {
                mulaiInput.value = tanggalMulaiDefault;
            }

            if (
                !selesaiInput.value ||
                selesaiInput.value < tanggalMulaiDefault ||
                selesaiInput.value > tanggalSelesaiDefault
            ) {
                selesaiInput.value = tanggalSelesaiDefault;
            }
        }

        function updateTotal() {
            let total = 0;

            targetInputs.forEach(function(input) {
                total += parseInt(input.value || 0);
            });

            totalPreview.textContent = total;
        }

        function syncAll() {
            for (let triwulan = 1; triwulan <= 4; triwulan++) {
                syncDeadlineCard(triwulan);
            }

            updateTotal();
        }

        targetInputs.forEach(function(input) {
            input.addEventListener('input', syncAll);
        });

        tahunInput.addEventListener('change', syncAll);

        syncAll();
    });
</script>