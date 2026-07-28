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
    .ketuakk-target-form { max-width:1180px; overflow:hidden; border:1px solid #E2E8F0; border-radius:14px; background:#FFF; }
    .ketuakk-target-form__header { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; padding:20px 22px; border-bottom:1px solid #E2E8F0; flex-wrap:wrap; }
    .ketuakk-target-form__eyebrow { color:#2563EB; font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
    .ketuakk-target-form__title { margin:4px 0 0; color:#0F172A; font-size:22px; font-weight:700; }
    .ketuakk-target-form__description { margin:5px 0 0; color:#64748B; font-size:13px; }
    .ketuakk-target-form__back,.ketuakk-target-form__button { min-height:38px; display:inline-flex; align-items:center; justify-content:center; padding:0 13px; border:1px solid #CBD5E1; border-radius:8px; background:#FFF; color:#334155; font-size:12px; font-weight:700; text-decoration:none; }
    .ketuakk-target-form__button--primary { border-color:#2563EB; background:#2563EB; color:#FFF; }
    .ketuakk-target-form__body { padding:20px 22px; }
    .ketuakk-target-form__fields { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
    .ketuakk-target-form__field label { display:block; margin-bottom:6px; color:#334155; font-size:12px; font-weight:700; }
    .ketuakk-target-form__field .form-control,.ketuakk-target-form__field .form-select { min-height:42px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; }
    .ketuakk-target-form__quarters { grid-column:1/-1; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); border-top:1px solid #E2E8F0; }
    .ketuakk-target-form__quarter { padding:16px 18px; border-right:1px solid #EEF2F7; border-bottom:1px solid #EEF2F7; }
    .ketuakk-target-form__quarter:nth-child(even) { border-right:0; }
    .ketuakk-target-form__quarter-title { margin-bottom:12px; color:#0F172A; font-size:14px; font-weight:700; }
    .ketuakk-target-form__note { margin-top:8px; color:#64748B; font-size:12px; }
    .ketuakk-target-form__quarter.is-disabled { background:#F8FAFC; opacity:.65; }
    .ketuakk-target-form__actions { display:flex; gap:8px; padding-top:18px; border-top:1px solid #EEF2F7; margin-top:18px; flex-wrap:wrap; }
    @media(max-width:720px){.ketuakk-target-form__fields,.ketuakk-target-form__quarters{grid-template-columns:1fr}.ketuakk-target-form__quarter{border-right:0}.ketuakk-target-form__body{padding:18px 16px}}
</style>

<section class="ketuakk-target-form" aria-labelledby="target-form-title">
    <header class="ketuakk-target-form__header">
        <div>
            <div class="ketuakk-target-form__eyebrow">Kontrak Manajemen Ketua KK</div>
            <h1 id="target-form-title" class="ketuakk-target-form__title">{{ $formTitle }}</h1>
            <p class="ketuakk-target-form__description">Atur target dan periode pelaksanaan KM pada setiap Triwulan.</p>
        </div>
        <a href="/ketuakk/target-km" class="ketuakk-target-form__back">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </header>

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

    <form action="{{ $formAction }}" method="POST" class="ketuakk-target-form__body">
        @csrf

        @if($httpMethod !== 'POST')
            @method($httpMethod)
        @endif

        <div class="ketuakk-target-form__fields">
            <div class="ketuakk-target-form__field">
                <label for="tahun_km">Tahun KM</label>

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

            <div class="ketuakk-target-form__field">
                <label for="kategori_km">Kategori KM</label>

                <select name="kategori_km" id="kategori_km" class="form-select" required>
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

            <div class="ketuakk-target-form__field">
                <label for="indikator">Jenis KM / Sub Kategori</label>

                <input
                    type="text"
                    name="indikator"
                    id="indikator"
                    class="form-control"
                    value="{{ $value('indikator') }}"
                    placeholder="Contoh: Perkuliahan"
                    maxlength="255"
                    required>
            </div>

            <div class="ketuakk-target-form__field">
                <label for="keterangan">Keterangan KM</label>

                <input
                    type="text"
                    name="keterangan"
                    id="keterangan"
                    class="form-control"
                    value="{{ $value('keterangan') }}"
                    placeholder="Contoh: Jumlah perkuliahan aktif">
            </div>

            <div class="ketuakk-target-form__field" style="grid-column:1/-1">
                <div class="alert alert-info mb-0">
                    Deadline hanya wajib diisi bila jumlah target pada Triwulan tersebut lebih dari 0.
                </div>
            </div>

            <div class="ketuakk-target-form__quarters">
            @foreach($triwulanData as $nomor => $tw)
                    <div
                        class="ketuakk-target-form__quarter js-deadline-card"
                        data-triwulan="{{ $nomor }}">

                        <div class="ketuakk-target-form__quarter-title">
                            {{ $tw['label'] }}
                        </div>

                        <div class="mb-3">
                            <label for="{{ $tw['target'] }}" class="form-label fw-bold">
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
                                <label for="{{ $tw['mulai'] }}" class="form-label fw-bold">
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
                                <label for="{{ $tw['selesai'] }}" class="form-label fw-bold">
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

                        <div class="ketuakk-target-form__note">
                            Periode Triwulan akan otomatis mengikuti tahun KM yang dipilih.
                        </div>
                    </div>
            @endforeach
            </div>

            <div class="col-12">
                <div class="alert alert-primary mb-0">
                    Total Target KM:
                    <strong id="totalTargetPreview">0</strong>
                </div>
            </div>
        </div>

        <div class="ketuakk-target-form__actions">
            <button type="submit" class="ketuakk-target-form__button ketuakk-target-form__button--primary">
                <i class="bi bi-save me-1"></i>
                {{ $submitLabel }}
            </button>

            <a href="/ketuakk/target-km" class="ketuakk-target-form__button">
                Batal
            </a>
        </div>
    </form>
</section>

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
                const parsedValue = Number.parseInt(input.value, 10);
                const safeValue = Number.isFinite(parsedValue)
                    ? Math.max(0, parsedValue)
                    : 0;

                total += safeValue;
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
