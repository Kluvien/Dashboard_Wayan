@extends('layouts.app')

@section('title', 'Profil Ketua KK')

@section('content')
<div class="page-heading">
    Profil <span class="muted">Ketua KK</span>
</div>

<div class="card mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="avatar" style="width: 64px; height: 64px; font-size: 24px;">
            {{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}
        </div>

        <div>
            <h4 class="fw-bold mb-1">{{ $user->username ?? '-' }}</h4>
            <p class="text-muted mb-0">{{ $user->role ?? 'Ketua KK' }}</p>
        </div>
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Informasi Profil</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="width: 100%; font-size: 14px;">
            <tbody>
                <tr>
                    <th style="width: 220px;">Username</th>
                    <td>{{ $user->username ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Role</th>
                    <td>{{ $user->role ?? '-' }}</td>
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
                    <th>JAD</th>
                    <td>
                        <span class="badge bg-primary">
                            {{ $dosen->jad ?? 'AA' }}
                        </span>
                    </td>
                </tr>

                <tr>
                    <th>Lab Riset</th>
                    <td>{{ $lab->nama_lab ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    <a href="{{ url()->previous() }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>
@endsection