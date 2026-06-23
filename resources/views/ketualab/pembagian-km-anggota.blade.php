@extends('layouts.app')

@section('title', 'Pembagian KM Anggota')

@section('content')
<div class="page-heading">
    Pembagian <span class="muted">KM Anggota</span>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <div class="fw-bold mb-1">Terjadi kesalahan:</div>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">KM yang Diturunkan ke Lab</h4>
            <p class="text-muted mb-0">
                Lab: {{ $lab->nama_lab ?? '-' }} | Tahun: {{ $tahun }}
            </p>
        </div>

        <a href="/ketualab/dashboard" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Daftar KM dari Ketua KK</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="table-layout: fixed; width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 18%;">Kategori KM</th>
                    <th style="width: 12%;">Total KM</th>
                    <th style="width: 12%;">Sudah Assign</th>
                    <th style="width: 12%;">Sisa KM</th>
                    <th style="width: 13%;">Status</th>
                    <th style="width: 28%;">Assign ke Anggota</th>
                </tr>
            </thead>

            <tbody>
                @forelse($dataKmLab as $index => $km)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $km['kategori_km'] }}
                    </td>

                    <td>
                        {{ $km['jumlah_km'] }}
                    </td>

                    <td>
                        {{ $km['sudah_assign'] }}
                    </td>

                    <td>
                        {{ $km['sisa_km'] }}
                    </td>

                    <td>
                        @if($km['status'] === 'Sudah Dibagi')
                        <span class="badge bg-success">Sudah Dibagi</span>
                        @else
                        <span class="badge bg-warning text-dark">Belum Selesai</span>
                        @endif
                    </td>

                    <td>
                        @if($km['sisa_km'] > 0)
                        <form action="/ketualab/penurunan-km" method="POST">
                            @csrf

                            <input type="hidden" name="id_km_lab" value="{{ $km['id_km_lab'] }}">

                            <div class="d-flex gap-2">
                                <select name="id_user" class="form-select form-select-sm">
                                    <option value="">Pilih Anggota</option>

                                    @foreach($anggota as $item)
                                    <option value="{{ $item->id_user }}">
                                        {{ $item->nama_dosen ?? $item->username }}
                                        - {{ $item->jad ?? 'AA' }}
                                    </option>
                                    @endforeach
                                </select>

                                <input
                                    type="number"
                                    name="jumlah_km"
                                    class="form-control form-control-sm"
                                    min="1"
                                    max="{{ $km['sisa_km'] }}"
                                    placeholder="Jumlah"
                                    style="width: 90px;">

                                <button type="submit" class="btn btn-primary btn-sm">
                                    Bagi
                                </button>
                            </div>
                        </form>
                        @else
                        <span class="text-muted">Semua KM sudah dibagi</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Belum ada KM yang diturunkan Ketua KK ke lab ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Daftar Anggota Lab</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="table-layout: fixed; width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 25%;">Nama Anggota</th>
                    <th style="width: 15%;">NIDN</th>
                    <th style="width: 25%;">Email</th>
                    <th style="width: 15%;">JAD</th>
                    <th style="width: 15%;">Bobot Saran</th>
                </tr>
            </thead>

            <tbody>
                @forelse($anggota as $index => $item)
                @php
                $jad = $item->jad ?? 'AA';

                $jadLabel = [
                'GB' => 'Guru Besar',
                'LK' => 'Lektor Kepala',
                'L' => 'Lektor',
                'AA' => 'Asisten Ahli',
                ];

                $bobotJad = [
                'GB' => 1.4,
                'LK' => 1.2,
                'L' => 1.0,
                'AA' => 0.8,
                ];
                @endphp

                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $item->nama_dosen ?? $item->username }}
                    </td>

                    <td>
                        {{ $item->nidn ?? '-' }}
                    </td>

                    <td>
                        {{ $item->email ?? '-' }}
                    </td>

                    <td>
                        <span class="badge bg-primary">{{ $jad }}</span>
                        <div class="small text-muted mt-1">
                            {{ $jadLabel[$jad] ?? 'Asisten Ahli' }}
                        </div>
                    </td>

                    <td>
                        {{ $bobotJad[$jad] ?? 0.8 }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Belum ada anggota pada lab ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection