@extends('layouts.app')

@section('title', 'Data Dosen')

@section('content')
<div class="card">
    <h2>Data Dosen</h2>
    <p>
        Halaman ini menampilkan daftar dosen yang terhubung dengan Laboratorium Riset.
    </p>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Dosen</th>
                <th>NIDN</th>
                <th>Email</th>
                <th>Laboratorium Riset</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dosens as $index => $dosen)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $dosen->nama_dosen }}</td>
                    <td>{{ $dosen->nidn }}</td>
                    <td>{{ $dosen->email }}</td>
                    <td>{{ $dosen->nama_lab ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada data dosen.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection