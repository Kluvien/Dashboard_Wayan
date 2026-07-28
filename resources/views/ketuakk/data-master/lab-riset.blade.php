@extends('layouts.app')

@section('title', 'Data Lab Riset')

@section('content')
@include('ketuakk.data-master._styles')
<section class="ketuakk-master">
    <div class="ketuakk-master__header">
        <div>
            <div class="ketuakk-master__eyebrow">Data Master Ketua KK</div>
            <h1 class="ketuakk-master__title">Daftar Laboratorium Riset</h1>
            <p class="ketuakk-master__description">
                Halaman ini menampilkan daftar lab riset beserta jumlah dosen dan aktivitas KM yang tercatat.
            </p>
        </div>

        <a href="/ketuakk/dashboard" class="ketuakk-master__button">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
    <div class="table-responsive ketuakk-master__scroll">
        <table class="table align-middle mb-0 ketuakk-master__table">
            <thead><tr><th scope="col">No</th><th scope="col">Nama Lab Riset</th><th scope="col" class="text-end">Jumlah Dosen</th><th scope="col" class="text-end">Aktivitas KM</th><th scope="col" class="text-center">Aksi</th></tr></thead>
            <tbody>
            @forelse($dataLab as $index => $lab)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="ketuakk-master__cell--identity">{{ $lab['nama_lab'] }}</td>
                    <td class="ketuakk-master__cell--number">{{ $lab['jumlah_dosen'] }}</td>
                    <td class="ketuakk-master__cell--number">{{ $lab['jumlah_aktivitas'] }}</td>
                    <td class="ketuakk-master__cell--action"><a href="/ketuakk/data-lab-riset/{{ $lab['id_lab'] }}" class="ketuakk-master__button">Lihat Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="ketuakk-master__empty">Belum ada data laboratorium riset.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
