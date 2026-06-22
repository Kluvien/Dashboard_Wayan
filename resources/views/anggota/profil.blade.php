@extends('layouts.app')

@section('title', 'Profil Anggota')

@section('content')
<div class="card">
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
@endsection