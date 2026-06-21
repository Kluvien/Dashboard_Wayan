@extends('layouts.app')

@section('title', 'Edit Aktivitas KM')

@section('content')
<div class="card">
    <h2>Edit Aktivitas KM</h2>
    <p>
        Form ini digunakan anggota untuk memperbarui aktivitas Kontrak Manajemen pribadi.
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

    <form action="/anggota/aktivitas-km/{{ $aktivitas->id_aktivitas }}" method="POST">
        @csrf
        @method('PUT')

        <div>
            <label>Kategori KM</label><br>
            <select name="kategori_km" required>
                <option value="">-- Pilih Kategori --</option>
                <option value="Pendidikan" {{ $aktivitas->kategori_km == 'Pendidikan' ? 'selected' : '' }}>Pendidikan</option>
                <option value="Penelitian" {{ $aktivitas->kategori_km == 'Penelitian' ? 'selected' : '' }}>Penelitian</option>
                <option value="Publikasi" {{ $aktivitas->kategori_km == 'Publikasi' ? 'selected' : '' }}>Publikasi</option>
                <option value="Pengabdian" {{ $aktivitas->kategori_km == 'Pengabdian' ? 'selected' : '' }}>Pengabdian</option>
                <option value="Penunjang" {{ $aktivitas->kategori_km == 'Penunjang' ? 'selected' : '' }}>Penunjang</option>
            </select>
        </div>

        <br>

        <div>
            <label>Judul Aktivitas</label><br>
            <input type="text" name="judul_aktivitas" value="{{ $aktivitas->judul_aktivitas }}" required style="width: 100%;">
        </div>

        <br>

        <div>
            <label>Deskripsi Singkat</label><br>
            <textarea name="deskripsi_singkat" rows="4" style="width: 100%;">{{ $aktivitas->deskripsi_singkat }}</textarea>
        </div>

        <br>

        <div>
            <label>Tanggal Mulai</label><br>
            <input type="date" name="tanggal_mulai" value="{{ $aktivitas->tanggal_mulai }}" required>
        </div>

        <br>

        <div>
            <label>Tanggal Selesai</label><br>
            <input type="date" name="tanggal_selesai" value="{{ $aktivitas->tanggal_selesai }}" required>
        </div>

        <br>

        <button type="submit">Update</button>
        <a href="/anggota/aktivitas-km">Batal</a>
    </form>
</div>
@endsection