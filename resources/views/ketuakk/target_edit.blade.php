@extends('layouts.app')

@section('title', 'Edit Target KM')

@section('content')
<div class="page-heading">
    Edit <span class="muted">Target KM</span>
</div>

<div class="card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Form Edit Target KM</h4>
            <p class="text-muted mb-0">
                Perbarui target berdasarkan tahun, kategori, sub kategori, dan pembagian per triwulan.
            </p>
        </div>

        <a href="/ketuakk/target-km?tahun={{ $target->tahun_km }}" class="btn btn-secondary">
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

    <form action="/ketuakk/target-km/{{ $target->id_target }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-bold">Tahun KM</label>
                <input
                    type="number"
                    name="tahun_km"
                    class="form-control"
                    value="{{ old('tahun_km', $target->tahun_km) }}"
                    min="2020"
                    max="2100"
                    required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Kategori KM</label>
                <select name="kategori_km" id="kategoriKm" class="form-select" required>
                    <option value="">-- Pilih Kategori KM --</option>
                    @foreach($kategoriOptions as $kategori => $subKategoriList)
                    <option value="{{ $kategori }}" {{ old('kategori_km', $target->kategori_km) == $kategori ? 'selected' : '' }}>
                        {{ $kategori }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-bold">Jenis KM / Sub Kategori</label>
                <select name="indikator" id="subKategoriKm" class="form-select" required>
                    <option value="">-- Pilih kategori terlebih dahulu --</option>
                </select>
                <div class="text-muted small mt-1">
                    Pilihan sub kategori akan menyesuaikan kategori KM yang dipilih.
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Triwulan 1</label>
                <input
                    type="number"
                    name="triwulan_1"
                    class="form-control js-triwulan"
                    value="{{ old('triwulan_1', $target->triwulan_1 ?? 0) }}"
                    min="0"
                    required>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Triwulan 2</label>
                <input
                    type="number"
                    name="triwulan_2"
                    class="form-control js-triwulan"
                    value="{{ old('triwulan_2', $target->triwulan_2 ?? 0) }}"
                    min="0"
                    required>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Triwulan 3</label>
                <input
                    type="number"
                    name="triwulan_3"
                    class="form-control js-triwulan"
                    value="{{ old('triwulan_3', $target->triwulan_3 ?? 0) }}"
                    min="0"
                    required>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Triwulan 4</label>
                <input
                    type="number"
                    name="triwulan_4"
                    class="form-control js-triwulan"
                    value="{{ old('triwulan_4', $target->triwulan_4 ?? 0) }}"
                    min="0"
                    required>
            </div>

            <div class="col-md-12">
                <div class="alert alert-info mb-0">
                    Total Target:
                    <strong><span id="totalTargetPreview">{{ $target->target ?? 0 }}</span></strong>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Perbarui Target
            </button>

            <a href="/ketuakk/target-km?tahun={{ $target->tahun_km }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const kategoriOptions = {
            !!json_encode($kategoriOptions) !!
        };
        const selectedKategori = {
            !!json_encode(old('kategori_km') ?? $target - > kategori_km) !!
        };
        const selectedSubKategori = {
            !!json_encode(old('indikator') ?? $target - > indikator) !!
        };

        const kategoriSelect = document.getElementById('kategoriKm');
        const subKategoriSelect = document.getElementById('subKategoriKm');
        const triwulanInputs = document.querySelectorAll('.js-triwulan');
        const totalPreview = document.getElementById('totalTargetPreview');

        function renderSubKategori(selectedKategori, selectedSubKategori = '') {
            subKategoriSelect.innerHTML = '';

            if (!selectedKategori || !kategoriOptions[selectedKategori]) {
                subKategoriSelect.innerHTML = '<option value="">-- Pilih kategori terlebih dahulu --</option>';
                return;
            }

            subKategoriSelect.innerHTML = '<option value="">-- Pilih Jenis KM / Sub Kategori --</option>';

            kategoriOptions[selectedKategori].forEach(function(item) {
                const option = document.createElement('option');
                option.value = item;
                option.textContent = item;

                if (item === selectedSubKategori) {
                    option.selected = true;
                }

                subKategoriSelect.appendChild(option);
            });
        }

        function updateTotal() {
            let total = 0;

            triwulanInputs.forEach(function(input) {
                total += parseInt(input.value || 0);
            });

            totalPreview.textContent = total;
        }

        kategoriSelect.addEventListener('change', function() {
            renderSubKategori(this.value);
        });

        triwulanInputs.forEach(function(input) {
            input.addEventListener('input', updateTotal);
        });

        renderSubKategori(selectedKategori, selectedSubKategori);
        updateTotal();
    });
</script>
@endsection