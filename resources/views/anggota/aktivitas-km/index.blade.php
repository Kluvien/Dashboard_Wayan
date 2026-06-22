@extends('layouts.app')

@section('title', 'Aktivitas KM Saya')

@section('content')
<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="page-heading mb-0">
            Aktivitas <span class="muted">KM</span>
        </div>

        <div class="d-flex gap-2">
            <a href="/anggota/dashboard" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>

            <a href="/anggota/aktivitas-km/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Tambah Aktivitas
            </a>
        </div>
    </div>
    
    <h2>Aktivitas KM Saya</h2>
    <p>
        Halaman ini digunakan anggota untuk melihat daftar aktivitas Kontrak Manajemen
        yang telah diinput.
    </p>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <a href="/anggota/aktivitas-km/create">+ Tambah Aktivitas KM</a>

    <br><br>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>Kategori KM</th>
                <th>Judul Aktivitas</th>
                <th>Deskripsi Singkat</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Selesai</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($aktivitas as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->kategori_km }}</td>
                <td>{{ $item->judul_aktivitas }}</td>
                <td>{{ $item->deskripsi_singkat ?? '-' }}</td>
                <td>{{ $item->tanggal_mulai }}</td>
                <td>{{ $item->tanggal_selesai }}</td>
                <td>
                    <a href="/anggota/aktivitas-km/{{ $item->id_aktivitas }}/edit">Edit</a>

                    <form action="/anggota/aktivitas-km/{{ $item->id_aktivitas }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Yakin ingin menghapus aktivitas ini?')">
                            Hapus
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">Belum ada aktivitas KM.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection