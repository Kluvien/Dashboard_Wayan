@extends('layouts.app')

@section('title', 'Data Kelompok Keahlian')

@section('content')
<div class="card">
    <h2>Data Kelompok Keahlian</h2>
    <p>
        Halaman ini menampilkan data Kelompok Keahlian yang menjadi induk dari Laboratorium Riset.
    </p>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>ID KK</th>
                <th>Nama Kelompok Keahlian</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kelompokKeahlian as $index => $kk)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $kk->id_kk }}</td>
                    <td>{{ $kk->nama_kk }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Belum ada data kelompok keahlian.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection