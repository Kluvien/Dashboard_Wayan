@extends('layouts.app')

@section('title', 'Detail Lab Riset')

@section('content')
@include('ketuakk.data-master._styles')
<section class="ketuakk-master">
    <div class="ketuakk-master__header">
        <div>
            <div class="ketuakk-master__eyebrow">Detail Lab Riset</div>
            <h1 class="ketuakk-master__title">{{ $lab->nama_lab }}</h1>
            <p class="ketuakk-master__description">
                Detail data dosen anggota dan aktivitas KM pada laboratorium riset ini.
            </p>
        </div>

        <a href="/ketuakk/data-lab-riset" class="ketuakk-master__button">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</section>

<section class="ketuakk-master">
    <div class="ketuakk-master__section-header"><h2 class="ketuakk-master__section-title">Daftar Dosen Anggota Lab</h2></div>

    <div class="table-responsive ketuakk-master__scroll">
        <table class="table align-middle mb-0 ketuakk-master__table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Dosen</th>
                    <th>NIDN</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dosen as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="ketuakk-master__cell--identity">{{ $item->nama_dosen }}</td>
                        <td>{{ $item->nidn }}</td>
                        <td>{{ $item->email }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="ketuakk-master__empty">
                            Belum ada dosen pada lab riset ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="ketuakk-master">
    <div class="ketuakk-master__section-header"><h2 class="ketuakk-master__section-title">Aktivitas KM Pada Lab Ini</h2></div>

    <div class="table-responsive ketuakk-master__scroll">
        <table class="table align-middle mb-0 ketuakk-master__table">
            <thead>
                <tr>
                    <th style="width: 6%;">No</th>
                    <th style="width: 18%;">Nama Anggota</th>
                    <th style="width: 12%;">Kategori</th>
                    <th style="width: 24%;">Judul Aktivitas</th>
                    <th style="width: 24%;">Deskripsi</th>
                    <th style="width: 8%;">Mulai</th>
                    <th style="width: 8%;">Selesai</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aktivitas as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->nama_dosen ?? $item->username }}</td>
                        <td>{{ $item->kategori_km }}</td>
                        <td class="ketuakk-master__cell--identity">{{ $item->judul_aktivitas }}</td>
                        <td>{{ $item->deskripsi_singkat ?? '-' }}</td>
                        <td>{{ $item->tanggal_mulai }}</td>
                        <td>{{ $item->tanggal_selesai }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="ketuakk-master__empty">
                            Belum ada aktivitas KM pada lab riset ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
