@extends('layouts.app')

@section('title', 'Ubah Data Dosen')

@section('content')
@include('ketuakk.data-master._styles')

@if($errors->any())
<div class="ketuakk-master__validation" role="alert">
    <div class="fw-bold mb-1">Terjadi kesalahan:</div>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<section class="ketuakk-master">
    <div class="ketuakk-master__header">
        <div>
            <div class="ketuakk-master__eyebrow">Data Master Dosen</div>
            <h1 class="ketuakk-master__title">Ubah Data Dosen</h1>
            <p class="ketuakk-master__description">
                Perbarui data dosen anggota Kelompok Keahlian.
            </p>
        </div>

        <a href="/ketuakk/data-dosen" class="ketuakk-master__button">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <form action="/ketuakk/data-dosen/{{ $dosen->id_dosen }}" method="POST" class="ketuakk-master__form">
        @csrf
        @method('PUT')

        <div class="ketuakk-master__form-grid">
        <div class="ketuakk-master__field ketuakk-master__field--full">
            <label for="nama_dosen">Nama Dosen</label>
            <input
                type="text"
                id="nama_dosen"
                name="nama_dosen"
                value="{{ old('nama_dosen', $dosen->nama_dosen) }}"
                class="form-control"
                placeholder="Masukkan nama dosen">
        </div>

        <div class="ketuakk-master__field">
            <label for="nidn">NIDN</label>
            <input
                type="text"
                id="nidn"
                name="nidn"
                value="{{ old('nidn', $dosen->nidn) }}"
                class="form-control"
                placeholder="Masukkan NIDN">
        </div>

        <div class="ketuakk-master__field">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $dosen->email) }}"
                class="form-control"
                placeholder="Masukkan email">
        </div>

        <div class="ketuakk-master__field">
            <label for="jad">Jabatan Akademik Dosen</label>
            <select name="jad" id="jad" class="form-select">
                <option value="">-- Pilih JAD --</option>
                <option value="GB" {{ old('jad', $dosen->jad ?? 'AA') == 'GB' ? 'selected' : '' }}>
                    Guru Besar (GB)
                </option>
                <option value="LK" {{ old('jad', $dosen->jad ?? 'AA') == 'LK' ? 'selected' : '' }}>
                    Lektor Kepala (LK)
                </option>
                <option value="L" {{ old('jad', $dosen->jad ?? 'AA') == 'L' ? 'selected' : '' }}>
                    Lektor (L)
                </option>
                <option value="AA" {{ old('jad', $dosen->jad ?? 'AA') == 'AA' ? 'selected' : '' }}>
                    Asisten Ahli (AA)
                </option>
                <option value="NJFA" {{ old('jad', $dosen->jad ?? 'AA') == 'NJFA' ? 'selected' : '' }}>
                    Non-Jabatan Fungsional Akademik (NJFA)
                </option>
            </select>
        </div>

        <div class="ketuakk-master__field">
            <label for="id_lab">Lab Riset</label>
            <select name="id_lab" id="id_lab" class="form-select">
                <option value="">-- Pilih Lab Riset --</option>
                @foreach($labs as $lab)
                <option value="{{ $lab->id_lab }}" {{ old('id_lab', $dosen->id_lab) == $lab->id_lab ? 'selected' : '' }}>
                    {{ $lab->nama_lab }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="ketuakk-master__form-actions">
            <button type="submit" class="ketuakk-master__button ketuakk-master__button--primary">
                Simpan Perubahan
            </button>

            <a href="/ketuakk/data-dosen" class="ketuakk-master__button">
                Batal
            </a>
        </div>
        </div>
    </form>
</section>
@endsection
