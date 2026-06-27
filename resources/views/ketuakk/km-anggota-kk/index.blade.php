@extends('layouts.app')

@section('title', 'KM Anggota KK')

@section('content')
@php
$dataAnggota = collect($dataAnggota ?? []);
$kategoriDefault = $kategoriDefault ?? ['Pendidikan', 'Penelitian', 'Publikasi', 'Pengabdian', 'Penunjang'];
@endphp

<style>
    .km-table th {
        white-space: nowrap;
        vertical-align: middle;
        font-size: 13px;
    }

    .km-table td {
        vertical-align: middle;
        font-size: 13px;
    }

    .group-header {
        background: #F3F6FB;
        text-align: center;
        font-weight: 800;
    }

    .jad-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        background: #EAF1FF;
        color: #2563EB;
        font-weight: 700;
        font-size: 12px;
    }
</style>

<div class="page-heading">
    Kontrak Manajemen <span class="muted">Anggota KK</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">KM Anggota KK Tahun {{ $tahun }}</h4>
            <p class="text-muted mb-0">
                Menampilkan jumlah Kontrak Manajemen anggota KK berdasarkan kategori KM.
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <form method="GET" action="/ketuakk/km-anggota-kk" class="d-flex gap-2">
                <select name="tahun" class="form-select" style="min-width: 120px;">
                    @foreach($tahunOptions ?? [$tahun] as $itemTahun)
                    <option value="{{ $itemTahun }}" {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                        {{ $itemTahun }}
                    </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-primary">
                    Filter
                </button>
            </form>

            <a href="/ketuakk/dashboard" class="btn btn-secondary">
                Kembali
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table" style="width: 100%;">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Nama Anggota</th>
                    <th rowspan="2">NIDN</th>
                    <th rowspan="2">JAD</th>
                    <th rowspan="2">Email</th>
                    <th rowspan="2">Lab Riset</th>
                    <th colspan="5" class="group-header">JUMLAH KM</th>
                    <th rowspan="2">Total</th>
                    <th rowspan="2">Aksi</th>
                </tr>
                <tr>
                    @foreach($kategoriDefault as $kategori)
                    <th>{{ $kategori }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @forelse($dataAnggota as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $item['nama_dosen'] }}
                    </td>

                    <td>
                        {{ $item['nidn'] }}
                    </td>

                    <td>
                        <span class="jad-badge">
                            {{ $item['jad'] }}
                        </span>
                    </td>

                    <td>
                        {{ $item['email'] }}
                    </td>

                    <td>
                        {{ $item['nama_lab'] }}
                    </td>

                    @foreach($kategoriDefault as $kategori)
                    <td class="fw-bold text-center">
                        {{ $item['jumlah_km'][$kategori] ?? 0 }}
                    </td>
                    @endforeach

                    <td class="fw-bold text-center">
                        {{ $item['total_km'] ?? 0 }}
                    </td>

                    <td>
                        @if(!empty($item['id_user']))
                        <a href="/ketuakk/km-anggota-kk/{{ $item['id_user'] }}?tahun={{ $tahun }}" class="btn btn-primary btn-sm">
                            Detail
                        </a>
                        @else
                        <button class="btn btn-secondary btn-sm" disabled>
                            Detail
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" class="text-center text-muted py-4">
                        Belum ada data anggota KK.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection