@extends('layouts.app')

@section('title', 'Laporan Ketua Lab')

@section('content')
<style>
    @media print {

        .sidebar,
        .topbar,
        .btn,
        form {
            display: none !important;
        }

        body {
            background: white !important;
        }

        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
            page-break-inside: avoid;
        }
    }
</style>

<div class="page-heading">
    Laporan <span class="muted">Ketua Lab</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Filter Laporan KM Lab</h4>
            <p class="text-muted mb-0">
                Laporan saat ini:
                <strong>{{ $labelPeriode ?? 'Tahunan' }}</strong>

                @if(isset($tanggalMulai) && isset($tanggalSelesai))
                | {{ $tanggalMulai->format('d/m/Y') }} - {{ $tanggalSelesai->format('d/m/Y') }}
                @endif
            </p>
        </div>

        <div class="d-flex gap-2">
            <button type="button" onclick="window.print()" class="btn btn-primary">
                Cetak Laporan
            </button>

            <a href="/ketualab/dashboard" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <form action="/ketualab/laporan" method="GET">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">Jenis Periode</label>
                <select name="periode" class="form-select">
                    <option value="tahun" {{ ($periode ?? 'tahun') == 'tahun' ? 'selected' : '' }}>Tahunan</option>
                    <option value="bulan" {{ ($periode ?? '') == 'bulan' ? 'selected' : '' }}>Bulanan</option>
                    <option value="triwulan" {{ ($periode ?? '') == 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                    <option value="semester" {{ ($periode ?? '') == 'semester' ? 'selected' : '' }}>Semester</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">Tahun</label>
                <input type="number" name="tahun" class="form-control" value="{{ $tahun ?? now()->year }}">
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">Bulan</label>
                <select name="bulan" class="form-select">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ ($bulan ?? now()->month) == $i ? 'selected' : '' }}>
                        {{ $i }}
                        </option>
                        @endfor
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">Triwulan</label>
                <select name="triwulan" class="form-select">
                    <option value="1" {{ ($triwulan ?? 1) == 1 ? 'selected' : '' }}>Triwulan 1</option>
                    <option value="2" {{ ($triwulan ?? 1) == 2 ? 'selected' : '' }}>Triwulan 2</option>
                    <option value="3" {{ ($triwulan ?? 1) == 3 ? 'selected' : '' }}>Triwulan 3</option>
                    <option value="4" {{ ($triwulan ?? 1) == 4 ? 'selected' : '' }}>Triwulan 4</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">Semester</label>
                <select name="semester" class="form-select">
                    <option value="1" {{ ($semester ?? 1) == 1 ? 'selected' : '' }}>Semester 1</option>
                    <option value="2" {{ ($semester ?? 1) == 2 ? 'selected' : '' }}>Semester 2</option>
                </select>
            </div>

            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    Filter
                </button>
            </div>
        </div>
    </form>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-1">Identitas Laporan</h4>
    <p class="text-muted mb-0">
        Lab Riset: <strong>{{ $lab->nama_lab ?? '-' }}</strong><br>
        Periode: <strong>{{ $labelPeriode ?? '-' }}</strong><br>
        Tanggal:
        @if(isset($tanggalMulai) && isset($tanggalSelesai))
        {{ $tanggalMulai->format('d/m/Y') }} - {{ $tanggalSelesai->format('d/m/Y') }}
        @else
        -
        @endif
    </p>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Jumlah Anggota</p>
            <h3 class="fw-bold mb-0">{{ $jumlahAnggota ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">KM Turun</p>
            <h3 class="fw-bold mb-0">{{ $totalKmTurun ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">KM Assign</p>
            <h3 class="fw-bold mb-0">{{ $totalKmAssign ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Sisa Assign</p>
            <h3 class="fw-bold mb-0">{{ $totalSisaAssign ?? 0 }}</h3>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <p class="text-muted mb-1">Target Periode</p>
            <h3 class="fw-bold mb-0">{{ $totalTargetPeriode ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <p class="text-muted mb-1">Realisasi</p>
            <h3 class="fw-bold mb-0">{{ $totalRealisasi ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <p class="text-muted mb-1">Progress Realisasi</p>
            <h3 class="fw-bold mb-0">{{ min($persentaseRealisasi ?? 0, 100) }}%</h3>
        </div>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Rekap Laporan per Kategori</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori KM</th>
                    <th>KM Turun</th>
                    <th>KM Assign</th>
                    <th>Sisa Assign</th>
                    <th>Target Periode</th>
                    <th>Realisasi</th>
                    <th>Sisa Realisasi</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rekapKategori as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="fw-bold">{{ $item['kategori'] }}</td>
                    <td>{{ $item['km_turun'] }}</td>
                    <td>{{ $item['km_assign'] }}</td>
                    <td>{{ $item['sisa_assign'] }}</td>
                    <td>{{ $item['target_periode'] }}</td>
                    <td>{{ $item['realisasi'] }}</td>
                    <td>{{ $item['sisa_realisasi'] }}</td>
                    <td>{{ $item['persentase'] }}%</td>
                    <td>
                        @if($item['status'] === 'Tercapai')
                        <span class="badge bg-success">Tercapai</span>
                        @else
                        <span class="badge bg-warning text-dark">Belum</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        Belum ada data kategori.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Rekap Laporan per Anggota</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Anggota</th>
                    <th>NIDN</th>
                    <th>JAD</th>
                    <th>KM Assign</th>
                    <th>Target Periode</th>
                    <th>Realisasi</th>
                    <th>Sisa</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rekapAnggota as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="fw-bold">{{ $item['nama_dosen'] }}</td>
                    <td>{{ $item['nidn'] }}</td>
                    <td>{{ $item['jad'] }}</td>
                    <td>{{ $item['km_assign'] }}</td>
                    <td>{{ $item['target_periode'] }}</td>
                    <td>{{ $item['realisasi'] }}</td>
                    <td>{{ $item['sisa'] }}</td>
                    <td>{{ $item['persentase'] }}%</td>
                    <td>
                        @if($item['status'] === 'Tercapai')
                        <span class="badge bg-success">Tercapai</span>
                        @else
                        <span class="badge bg-warning text-dark">Belum</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        Belum ada data anggota.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Daftar Aktivitas KM Anggota</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Anggota</th>
                    <th>Kategori</th>
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
                    <td class="fw-bold">
                        {{ $item->nama_dosen ?? $item->username }}
                        <div class="small text-muted">{{ $item->nidn ?? '-' }}</div>
                    </td>
                    <td>{{ $item->kategori_km }}</td>
                    <td>{{ $item->judul_aktivitas }}</td>
                    <td>{{ $item->deskripsi_singkat ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Belum ada aktivitas pada periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection