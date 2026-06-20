@extends('layouts.app')

@section('title', 'Laporan Ketua Lab')

@section('content')
<div class="card">
    <h2>Laporan Capaian Laboratorium</h2>
    <p>Halaman ini digunakan Ketua Lab untuk melihat laporan capaian laboratorium dan anggota.</p>

    <form>
        <label for="tahun">Tahun KM</label>
        <select id="tahun" name="tahun">
            <option>2026</option>
            <option>2025</option>
        </select>

        <button type="button">Filter</button>
    </form>

    <br>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Kategori KM</th>
                <th>Total Target</th>
                <th>Total Realisasi</th>
                <th>Persentase</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Publikasi</td>
                <td>10</td>
                <td>7</td>
                <td>70%</td>
                <td>Berjalan</td>
            </tr>
        </tbody>
    </table>

    <br>

    <button type="button">Export PDF</button>
    <button type="button">Export Excel</button>
</div>
@endsection