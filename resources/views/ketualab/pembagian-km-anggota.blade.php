@extends('layouts.app')

@section('title', 'Pembagian KM Anggota')

@section('content')
<div class="page-heading">
    Pembagian <span class="muted">KM Anggota</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Pembagian Kontrak Manajemen Anggota Lab</h4>
            <p class="text-muted mb-0">
                Ketua Lab dapat membagi target KM kepada anggota berdasarkan JAD dosen.
            </p>
        </div>

        <a href="/ketualab/dashboard" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Form Target KM Lab</h4>

    <form action="/ketualab/penurunan-km" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Tahun KM</label>
                <input type="number" name="tahun_km" class="form-control" value="{{ old('tahun_km', $tahun) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Target Pendidikan</label>
                <input type="number" name="target_pendidikan" class="form-control" value="{{ old('target_pendidikan', 10) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Target Penelitian</label>
                <input type="number" name="target_penelitian" class="form-control" value="{{ old('target_penelitian', 10) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Target Publikasi</label>
                <input type="number" name="target_publikasi" class="form-control" value="{{ old('target_publikasi', 5) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Target Pengabdian</label>
                <input type="number" name="target_pengabdian" class="form-control" value="{{ old('target_pengabdian', 5) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Target Penunjang</label>
                <input type="number" name="target_penunjang" class="form-control" value="{{ old('target_penunjang', 5) }}">
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                Buat Pembagian Berdasarkan JAD
            </button>
        </div>
    </form>
</div>

<div class="card">
    <h4 class="fw-bold mb-1">Daftar Anggota Lab</h4>
    <p class="text-muted mb-4">
        Lab: {{ $lab->nama_lab ?? '-' }} | Tahun: {{ $tahun }}
    </p>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="table-layout: fixed; width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 20%;">Nama Anggota</th>
                    <th style="width: 13%;">NIDN</th>
                    <th style="width: 15%;">JAD</th>
                    <th style="width: 10%;">Bobot</th>
                    <th style="width: 12%;">Target</th>
                    <th style="width: 12%;">Realisasi</th>
                    <th style="width: 13%;">Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($dataAnggota as $index => $anggota)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $anggota['nama_dosen'] }}</td>
                        <td>{{ $anggota['nidn'] }}</td>
                        <td>
                            <span class="badge bg-primary">{{ $anggota['jad'] }}</span>
                            <div class="small text-muted mt-1">
                                {{ $anggota['jad_label'] }}
                            </div>
                        </td>
                        <td>{{ $anggota['bobot'] }}</td>
                        <td>{{ $anggota['total_target'] }}</td>
                        <td>{{ $anggota['total_realisasi'] }}</td>
                        <td>
                            @if($anggota['total_target'] > 0)
                                <span class="badge bg-success">Sudah Dibagi</span>
                            @else
                                <span class="badge bg-warning text-dark">Belum Dibagi</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Belum ada anggota pada lab ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection