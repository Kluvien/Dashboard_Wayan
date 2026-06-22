@extends('layouts.app')

@section('title', 'Ubah Data Dosen')

@section('content')
<div class="page-heading">
    Ubah <span class="muted">Data Dosen</span>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <div class="fw-bold mb-1">Terjadi kesalahan:</div>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Form Ubah Data Dosen</h4>
            <p class="text-muted mb-0">
                Perbarui data dosen anggota Kelompok Keahlian.
            </p>
        </div>

        <a href="/ketuakk/data-dosen" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <form action="/ketuakk/data-dosen/{{ $dosen->id_dosen }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label fw-bold">Nama Dosen</label>
            <input 
                type="text" 
                name="nama_dosen" 
                value="{{ old('nama_dosen', $dosen->nama_dosen) }}" 
                class="form-control"
                placeholder="Masukkan nama dosen"
            >
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">NIDN</label>
            <input 
                type="text" 
                name="nidn" 
                value="{{ old('nidn', $dosen->nidn) }}" 
                class="form-control"
                placeholder="Masukkan NIDN"
            >
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Email</label>
            <input 
                type="email" 
                name="email" 
                value="{{ old('email', $dosen->email) }}" 
                class="form-control"
                placeholder="Masukkan email"
            >
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Jabatan Akademik Dosen</label>
            <select name="jad" class="form-select">
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
            </select>
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold">Lab Riset</label>
            <select name="id_lab" class="form-select">
                <option value="">-- Pilih Lab Riset --</option>
                @foreach($labs as $lab)
                    <option value="{{ $lab->id_lab }}" {{ old('id_lab', $dosen->id_lab) == $lab->id_lab ? 'selected' : '' }}>
                        {{ $lab->nama_lab }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                Simpan Perubahan
            </button>

            <a href="/ketuakk/data-dosen" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection