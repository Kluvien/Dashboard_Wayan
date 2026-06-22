@extends('layouts.app')

@section('title', 'Detail Capaian Anggota')

@section('content')
<div class="card">
    <h2>Detail Capaian Anggota</h2>
    <p>
        Halaman ini menampilkan detail target, realisasi, dan aktivitas KM anggota.
    </p>

    <h3>Data Anggota</h3>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <tr>
            <th>Nama Anggota</th>
            <td>{{ $anggota->nama_dosen ?? '-' }}</td>
        </tr>
        <tr>
            <th>NIDN</th>
            <td>{{ $anggota->nidn ?? '-' }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $anggota->email ?? '-' }}</td>
        </tr>
        <tr>
            <th>Laboratorium Riset</th>
            <td>{{ $anggota->nama_lab ?? '-' }}</td>
        </tr>
    </table>

    <br>

    <h3>Rekap Capaian Per Kategori</h3>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>Kategori KM</th>
                <th>Target</th>
                <th>Realisasi</th>
                <th>Persentase</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekap as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['kategori'] }}</td>
                    <td>{{ $item['target'] }}</td>
                    <td>{{ $item['realisasi'] }}</td>
                    <td>{{ $item['persentase'] }}%</td>
                    <td>{{ $item['status'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>

    <h3>Daftar Aktivitas KM</h3>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>Kategori KM</th>
                <th>Judul Aktivitas</th>
                <th>Deskripsi</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Selesai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($aktivitas as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->kategori_km }}</td>
                    <td>{{ $item->judul_aktivitas }}</td>
                    <td>{{ $item->deskripsi_singkat ?? '-' }}</td>
                    <td>{{ $item->tanggal_mulai }}</td>
                    <td>{{ $item->tanggal_selesai }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Belum ada aktivitas KM.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <br>

    <a href="/ketualab/monitoring-anggota">Kembali</a>
</div>
@endsection