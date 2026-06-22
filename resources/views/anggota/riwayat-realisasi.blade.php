@extends('layouts.app')

@section('title', 'Riwayat Realisasi')

@section('content')
<div class="card">
    <h2>Riwayat Realisasi KM</h2>
    <p>
        Halaman ini menampilkan riwayat aktivitas atau realisasi Kontrak Manajemen
        yang telah diinput oleh anggota.
    </p>

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
                    <td colspan="6">Belum ada riwayat realisasi KM.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection