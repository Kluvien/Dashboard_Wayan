@extends('layouts.app')

@section('title', 'Laporan Ketua Lab')

@section('content')
<div class="card">
    <h2>Laporan Capaian Laboratorium</h2>
    <p>
        Halaman ini menampilkan laporan capaian Kontrak Manajemen berdasarkan
        aktivitas KM anggota pada laboratorium riset yang dipimpin Ketua Lab.
    </p>

    <h3>Informasi Laboratorium</h3>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <tr>
            <th>Laboratorium Riset</th>
            <td>{{ $lab->nama_lab ?? '-' }}</td>
        </tr>
        <tr>
            <th>Jumlah Anggota</th>
            <td>{{ $jumlahAnggota }}</td>
        </tr>
    </table>

    <br>

    <h3>Ringkasan Capaian</h3>

    <div style="display: flex; gap: 16px; margin-bottom: 20px;">
        <div style="border: 1px solid #ddd; padding: 16px; width: 33%;">
            <h3>Total Target</h3>
            <p style="font-size: 24px;">{{ $totalTargetLab }}</p>
        </div>

        <div style="border: 1px solid #ddd; padding: 16px; width: 33%;">
            <h3>Total Realisasi</h3>
            <p style="font-size: 24px;">{{ $totalRealisasiLab }}</p>
        </div>

        <div style="border: 1px solid #ddd; padding: 16px; width: 33%;">
            <h3>Persentase Capaian</h3>
            <p style="font-size: 24px;">{{ min($persentaseTotal, 100) }}%</p>
        </div>
    </div>

    <h3>Rekap Per Kategori</h3>

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
            @foreach($rekapKategori as $index => $item)
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

    <h3>Daftar Aktivitas Anggota</h3>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Anggota</th>
                <th>NIDN</th>
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
                    <td>{{ $item->nama_dosen ?? $item->username }}</td>
                    <td>{{ $item->nidn ?? '-' }}</td>
                    <td>{{ $item->kategori_km }}</td>
                    <td>{{ $item->judul_aktivitas }}</td>
                    <td>{{ $item->deskripsi_singkat ?? '-' }}</td>
                    <td>{{ $item->tanggal_mulai }}</td>
                    <td>{{ $item->tanggal_selesai }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Belum ada aktivitas KM pada laboratorium ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <br>

    <button type="button" onclick="window.print()">Cetak Laporan</button>
</div>
@endsection