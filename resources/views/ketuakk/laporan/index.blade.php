@extends('layouts.app')

@section('title', 'Reports Ketua KK')

@section('content')
@php
$rekapKategori = collect($rekapKategori ?? []);
$laporanRows = collect($laporanRows ?? []);
$aktivitasRows = collect($aktivitasRows ?? []);
@endphp

<style>
    .report-card-value {
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 0;
    }

    .report-table th {
        white-space: nowrap;
        vertical-align: middle;
        font-size: 13px;
    }

    .report-table td {
        vertical-align: middle;
        font-size: 13px;
    }

    .progress-soft {
        height: 10px;
        border-radius: 999px;
        background: #E5E7EB;
        overflow: hidden;
    }

    .progress-soft-fill {
        height: 100%;
        border-radius: 999px;
        background: var(--blue);
    }
</style>

<div class="page-heading">
    Reports <span class="muted">Ketua KK</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Filter Reports Ketua KK</h4>
            <p class="text-muted mb-0">
                Periode laporan:
                <strong>{{ $labelPeriode ?? '-' }}</strong>
            </p>
        </div>

        <a href="/ketuakk/dashboard" class="btn btn-secondary">
            Kembali
        </a>
    </div>

    <form method="GET" action="/ketuakk/laporan">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">Jenis Reports</label>
                <select name="jenis_laporan" class="form-select">
                    <option value="keseluruhan_kk" {{ ($jenisLaporan ?? '') === 'keseluruhan_kk' ? 'selected' : '' }}>Keseluruhan KK</option>
                    <option value="per_lab_riset" {{ ($jenisLaporan ?? '') === 'per_lab_riset' ? 'selected' : '' }}>Per Lab Riset</option>
                    <option value="seluruh_lab_riset" {{ ($jenisLaporan ?? '') === 'seluruh_lab_riset' ? 'selected' : '' }}>Seluruh Lab Riset</option>
                    <option value="seluruh_anggota" {{ ($jenisLaporan ?? '') === 'seluruh_anggota' ? 'selected' : '' }}>Seluruh Anggota</option>
                    <option value="per_anggota" {{ ($jenisLaporan ?? '') === 'per_anggota' ? 'selected' : '' }}>Per Anggota</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">Tahun Mulai</label>
                <select name="tahun_mulai" class="form-select">
                    @foreach($tahunOptions ?? [now()->year] as $tahun)
                        <option value="{{ $tahun }}" {{ (int) ($tahunMulai ?? now()->year) === (int) $tahun ? 'selected' : '' }}>
                            {{ $tahun }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">Tahun Selesai</label>
                <select name="tahun_selesai" class="form-select">
                    @foreach($tahunOptions ?? [now()->year] as $tahun)
                        <option value="{{ $tahun }}" {{ (int) ($tahunSelesai ?? now()->year) === (int) $tahun ? 'selected' : '' }}>
                            {{ $tahun }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">Mode Periode</label>
                <select name="mode_periode" class="form-select">
                    <option value="tahun" {{ ($modePeriode ?? '') === 'tahun' ? 'selected' : '' }}>Tahunan</option>
                    <option value="semester" {{ ($modePeriode ?? '') === 'semester' ? 'selected' : '' }}>Semester</option>
                    <option value="triwulan" {{ ($modePeriode ?? '') === 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                </select>
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    Tampilkan Reports
                </button>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Semester Mulai</label>
                <select name="semester_mulai" class="form-select">
                    <option value="1" {{ (int) ($semesterMulai ?? 1) === 1 ? 'selected' : '' }}>Semester 1</option>
                    <option value="2" {{ (int) ($semesterMulai ?? 1) === 2 ? 'selected' : '' }}>Semester 2</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Semester Selesai</label>
                <select name="semester_selesai" class="form-select">
                    <option value="1" {{ (int) ($semesterSelesai ?? 2) === 1 ? 'selected' : '' }}>Semester 1</option>
                    <option value="2" {{ (int) ($semesterSelesai ?? 2) === 2 ? 'selected' : '' }}>Semester 2</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Triwulan Mulai</label>
                <select name="triwulan_mulai" class="form-select">
                    @for($i = 1; $i <= 4; $i++)
                        <option value="{{ $i }}" {{ (int) ($triwulanMulai ?? 1) === $i ? 'selected' : '' }}>
                            Triwulan {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Triwulan Selesai</label>
                <select name="triwulan_selesai" class="form-select">
                    @for($i = 1; $i <= 4; $i++)
                        <option value="{{ $i }}" {{ (int) ($triwulanSelesai ?? 4) === $i ? 'selected' : '' }}>
                            Triwulan {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Pilih Lab Riset</label>
                <select name="id_lab" class="form-select">
                    <option value="">Semua Lab Riset</option>
                    @foreach($labOptions ?? [] as $lab)
                        <option value="{{ $lab->id_lab }}" {{ (string) ($idLab ?? '') === (string) $lab->id_lab ? 'selected' : '' }}>
                            {{ $lab->nama_lab }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Pilih Anggota</label>
                <select name="id_user" class="form-select">
                    <option value="">Semua Anggota</option>
                    @foreach($anggotaOptions ?? [] as $anggota)
                        <option value="{{ $anggota->id_user }}" {{ (string) ($idUser ?? '') === (string) $anggota->id_user ? 'selected' : '' }}>
                            {{ $anggota->nama_dosen ?? $anggota->username }} - {{ $anggota->nama_lab ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Jumlah Lab</p>
            <p class="report-card-value">{{ $jumlahLab ?? 0 }}</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Jumlah Anggota</p>
            <p class="report-card-value">{{ $jumlahAnggota ?? 0 }}</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Total Target</p>
            <p class="report-card-value">{{ $totalTarget ?? 0 }}</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Total Realisasi</p>
            <p class="report-card-value text-success">{{ $totalRealisasi ?? 0 }}</p>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <p class="text-muted mb-1">Sisa Target</p>
            <p class="report-card-value text-warning">{{ $totalSisa ?? 0 }}</p>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <p class="text-muted mb-1">Persentase Capaian</p>
            <p class="report-card-value">{{ $persentaseTotal ?? 0 }}%</p>
        </div>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Rekap Kategori KM</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0 report-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori KM</th>
                    <th>Target</th>
                    <th>Realisasi</th>
                    <th>Sisa</th>
                    <th>Progress</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rekapKategori as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $item['kategori'] ?? '-' }}</td>
                        <td>{{ $item['target'] ?? 0 }}</td>
                        <td>{{ $item['realisasi'] ?? 0 }}</td>
                        <td>{{ $item['sisa'] ?? 0 }}</td>
                        <td style="min-width: 180px;">
                            <div class="progress-soft mb-1">
                                <div class="progress-soft-fill" style="width: {{ $item['persentase'] ?? 0 }}%;"></div>
                            </div>
                            <div class="small text-muted">{{ $item['persentase'] ?? 0 }}%</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Belum ada data rekap kategori.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(($jenisLaporan ?? 'keseluruhan_kk') !== 'keseluruhan_kk')
<div class="card mb-4">
    <h4 class="fw-bold mb-3">Detail Reports</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0 report-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Keterangan</th>
                    <th>Target</th>
                    <th>Realisasi</th>
                    <th>Sisa</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($laporanRows as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $item['nama'] ?? '-' }}</td>
                        <td>{{ $item['keterangan'] ?? '-' }}</td>
                        <td>{{ $item['target'] ?? 0 }}</td>
                        <td>{{ $item['realisasi'] ?? 0 }}</td>
                        <td>{{ $item['sisa'] ?? 0 }}</td>
                        <td style="min-width: 180px;">
                            <div class="progress-soft mb-1">
                                <div class="progress-soft-fill" style="width: {{ $item['persentase'] ?? 0 }}%;"></div>
                            </div>
                            <div class="small text-muted">{{ $item['persentase'] ?? 0 }}%</div>
                        </td>
                        <td>
                            @if(($item['status'] ?? '') === 'Tercapai')
                                <span class="badge bg-success">Tercapai</span>
                            @else
                                <span class="badge bg-warning text-dark">Belum Tercapai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Belum ada data detail reports.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

@if(($jenisLaporan ?? '') === 'per_anggota' && $aktivitasRows->count() > 0)
<div class="card">
    <h4 class="fw-bold mb-3">Riwayat Aktivitas Anggota</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0 report-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori</th>
                    <th>Sub Kategori</th>
                    <th>Judul Aktivitas</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Selesai</th>
                    <th>Status</th>
                    <th>Bukti</th>
                </tr>
            </thead>

            <tbody>
                @foreach($aktivitasRows as $index => $aktivitas)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $aktivitas->kategori_km ?? '-' }}</td>
                        <td>{{ $aktivitas->sub_kategori_km ?? '-' }}</td>
                        <td>{{ $aktivitas->judul_aktivitas ?? '-' }}</td>
                        <td>{{ $aktivitas->tanggal_mulai ?? '-' }}</td>
                        <td>{{ $aktivitas->tanggal_selesai ?? '-' }}</td>
                        <td>
                            <span class="badge bg-primary">
                                {{ $aktivitas->status_progress ?? '-' }}
                            </span>
                        </td>
                        <td>
                            @if(!empty($aktivitas->bukti_link))
                                <a href="{{ $aktivitas->bukti_link }}" target="_blank" class="btn btn-primary btn-sm">
                                    Lihat
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
