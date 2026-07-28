@extends('layouts.app')

@section('title', 'Plot Anggota Target KM')

@section('content')
<style>
    .ketualab-plot-form { max-width: 860px; background: #FFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; }
    .ketualab-plot-form__header { padding: 20px 22px; border-bottom: 1px solid #EEF2F7; }
    .ketualab-plot-form__eyebrow { margin: 0 0 5px; color: #2563EB; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .ketualab-plot-form__title { margin: 0; color: #0F172A; font-size: 24px; font-weight: 700; }
    .ketualab-plot-form__description { margin: 7px 0 0; color: #64748B; font-size: 13px; }
    .ketualab-plot-form__context { display: grid; grid-template-columns: minmax(0, 1fr) minmax(140px, .35fr); gap: 1px; background: #E2E8F0; border-bottom: 1px solid #E2E8F0; }
    .ketualab-plot-form__metric { padding: 14px 22px; background: #F8FAFC; }
    .ketualab-plot-form__label { display: block; margin-bottom: 4px; color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .ketualab-plot-form__value { color: #0F172A; font-size: 14px; font-weight: 600; }
    .ketualab-plot-form__body { padding: 22px; }
    .ketualab-plot-form__field label { color: #334155; font-size: 13px; font-weight: 700; }
    .ketualab-plot-form__field .form-select { min-height: 42px; border-color: #CBD5E1; border-radius: 9px; color: #334155; }
    .ketualab-plot-form__field .form-select:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
    .ketualab-plot-form__help { margin-top: 7px; color: #64748B; font-size: 12px; }
    .ketualab-plot-form__actions { display: flex; gap: 9px; padding-top: 18px; margin-top: 20px; border-top: 1px solid #EEF2F7; }
    .ketualab-plot-form__primary, .ketualab-plot-form__secondary { min-height: 38px; padding: 8px 15px; border-radius: 8px; font-size: 13px; font-weight: 700; }
    .ketualab-plot-form__secondary { color: #334155; background: #FFF; border-color: #CBD5E1; }
    @media (max-width: 575.98px) {
        .ketualab-plot-form__context { grid-template-columns: 1fr; }
        .ketualab-plot-form__actions { flex-direction: column; }
        .ketualab-plot-form__actions .btn { width: 100%; }
    }
</style>

<section class="ketualab-plot-form" aria-labelledby="ketualab-plot-form-title">
    <header class="ketualab-plot-form__header">
        <p class="ketualab-plot-form__eyebrow">Kontrak Manajemen Lab</p>
        <h1 id="ketualab-plot-form-title" class="ketualab-plot-form__title">Plot Target ke Anggota</h1>
        <p class="ketualab-plot-form__description">Tentukan anggota laboratorium yang bertanggung jawab menjalankan target ini.</p>
    </header>

    <div class="ketualab-plot-form__context">
        <div class="ketualab-plot-form__metric">
            <span class="ketualab-plot-form__label">Indikator Target</span>
            <span class="ketualab-plot-form__value">{{ $target->indikator }}</span>
        </div>
        <div class="ketualab-plot-form__metric">
            <span class="ketualab-plot-form__label">Volume Target</span>
            <span class="ketualab-plot-form__value">{{ $target->target }}</span>
        </div>
    </div>

    <form action="/ketualab/penurunan-km/{{ $target->id_target }}/plot" method="POST" class="ketualab-plot-form__body">
        @csrf
        <div class="ketualab-plot-form__field">
            <label for="id_dosen" class="form-label">Pilih Dosen (Anggota Lab)</label>
            <select id="id_dosen" name="id_dosen" class="form-select" required>
                <option value="">-- Pilih Anggota yang Bertanggung Jawab --</option>
                @foreach($anggotas as $anggota)
                    <option value="{{ $anggota->id_dosen }}">{{ $anggota->username }} (NIDN: {{ $anggota->id_dosen }})</option>
                @endforeach
            </select>
            <div class="ketualab-plot-form__help">Pilih anggota yang akan mengeksekusi target ini.</div>
        </div>
        <div class="ketualab-plot-form__actions">
            <button type="submit" class="btn btn-primary ketualab-plot-form__primary">Simpan Plotting</button>
            <a href="/ketualab/penurunan-km" class="btn btn-outline-secondary ketualab-plot-form__secondary">Batal</a>
        </div>
    </form>
</section>
@endsection
