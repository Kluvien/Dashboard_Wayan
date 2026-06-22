@extends('layouts.app')

@section('title', 'Tambah Aktivitas KM')

@section('content')
<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="page-heading mb-0">
            Tambah <span class="muted">Aktivitas KM</span>
        </div>

        <a href="/anggota/dashboard" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div> 

    <h2>Tambah Aktivitas KM</h2>
    <p>
        Form ini digunakan anggota untuk menambahkan aktivitas Kontrak Manajemen pribadi.
    </p>

    @if($errors->any())
        <div style="color: red;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form action="/anggota/aktivitas-km" method="POST">
        @csrf

        <div>
            <label>Kategori KM</label><br>
            <select name="kategori_km" required>
                <option value="">-- Pilih Kategori --</option>
                <option value="Pendidikan">Pendidikan</option>
                <option value="Penelitian">Penelitian</option>
                <option value="Publikasi">Publikasi</option>
                <option value="Pengabdian">Pengabdian</option>
                <option value="Penunjang">Penunjang</option>
            </select>
        </div>

        <br>

        <div>
            <label>Judul Aktivitas</label><br>
            <input type="text" name="judul_aktivitas" required style="width: 100%;">
        </div>

        <br>

        <div>
            <label>Deskripsi Singkat</label><br>
            <textarea name="deskripsi_singkat" rows="4" style="width: 100%;"></textarea>
        </div>

        <br>

        <div>
            <label>Tanggal Mulai</label><br>
            <input type="date" name="tanggal_mulai" required>
        </div>

        <br>

        <div>
            <label>Tanggal Selesai</label><br>
            <input type="date" name="tanggal_selesai" required>
        </div>

        <br>

        <button type="submit">Simpan</button>
        <a href="/anggota/aktivitas-km">Batal</a>
    </form>
</div>
@endsection