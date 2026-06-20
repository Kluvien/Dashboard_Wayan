@extends('layouts.app')

@section('title', 'Data Lab Riset')

@section('content')
<div class="card">
    <h2>Data Laboratorium Riset</h2>
    <p>
        Halaman ini menampilkan daftar Laboratorium Riset yang berada di bawah
        Kelompok Keahlian.
    </p>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>ID Lab</th>
                <th>Nama Laboratorium Riset</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laboratorium as $index => $lab)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $lab->id_lab }}</td>
                    <td>{{ $lab->nama_lab }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Belum ada data laboratorium riset.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection