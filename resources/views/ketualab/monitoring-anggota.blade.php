@extends('layouts.app')

@section('title', 'Monitoring Anggota')

@section('content')
<div class="card">
    <h2>Monitoring KM Anggota</h2>
    <p>Halaman ini digunakan Ketua Lab untuk memantau capaian KM seluruh anggota laboratorium.</p>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Nama Anggota</th>
                <th>Target</th>
                <th>Realisasi</th>
                <th>Persentase</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Anggota 1</td>
                <td>10</td>
                <td>8</td>
                <td>80%</td>
                <td>Berjalan</td>
                <td><a href="/ketualab/detail-anggota">Detail</a></td>
            </tr>
        </tbody>
    </table>
</div>
@endsection