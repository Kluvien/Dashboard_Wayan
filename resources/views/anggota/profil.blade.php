@extends('layouts.app')

@section('title', 'Profil Anggota')

@section('content')
<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="page-heading mb-0">
            Profil <span class="muted">Anggota</span>
        </div>
    </div>

    <h2>Profil Anggota</h2>
    <p>
        Halaman ini menampilkan informasi profil anggota Kelompok Keahlian.
    </p>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <tr>
            <th>Username</th>
            <td>{{ $user->username }}</td>
        </tr>
        <tr>
            <th>Role</th>
            <td>{{ $user->role }}</td>
        </tr>
        <tr>
            <th>Nama Dosen</th>
            <td>{{ $dosen->nama_dosen ?? '-' }}</td>
        </tr>
        <tr>
            <th>NIDN</th>
            <td>{{ $dosen->nidn ?? '-' }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $dosen->email ?? '-' }}</td>
        </tr>
        <tr>
            <th>Laboratorium Riset</th>
            <td>{{ $lab->nama_lab ?? '-' }}</td>
        </tr>
    </table>
</div>

<div class="mt-4">
    <a href="{{ url()->previous() }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>
@endsection