@extends('layouts.app')

@section('title', 'Profil Ketua Lab')

@section('content')
<div class="card">
    <h2>Profil Ketua Lab</h2>
    <p>
        Halaman ini menampilkan data profil Ketua Lab beserta Laboratorium Riset yang dipimpin.
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