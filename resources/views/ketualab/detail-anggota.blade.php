@extends('layouts.app')

@section('title', 'Detail Capaian Anggota')

@section('content')
<div class="card">
    <h2>Detail Capaian Anggota</h2>
    <p>Halaman ini digunakan Ketua Lab untuk melihat detail target dan realisasi setiap anggota.</p>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <tr>
            <th>Nama Anggota</th>
            <td>Anggota 1</td>
        </tr>
        <tr>
            <th>Laboratorium</th>
            <td>BMS - Business Modelling & Simulation</td>
        </tr>
        <tr>
            <th>Total Capaian</th>
            <td>80%</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>Berjalan</td>
        </tr>
    </table>

    <br>

    <h3>Rincian Capaian</h3>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Kategori KM</th>
                <th>Target</th>
                <th>Realisasi</th>
                <th>Persentase</th>
                <th>Bukti/Laporan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Publikasi</td>
                <td>5</td>
                <td>4</td>
                <td>80%</td>
                <td>-</td>
            </tr>
        </tbody>
    </table>

    <br>

    <a href="/ketualab/monitoring-anggota">Kembali</a>
</div>
@endsection