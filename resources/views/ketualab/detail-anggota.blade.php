@extends('layouts.app')

@section('title', 'Detail Anggota Lab')

@section('content')
<div class="page-heading">
    Detail <span class="muted">Anggota Lab</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">{{ $anggota->nama_dosen ?? $anggota->username }}</h4>
            <p class="text-muted mb-0">
                Lab: {{ $anggota->nama_lab ?? '-' }} |
                NIDN: {{ $anggota->nidn ?? '-' }} |
                JAD: {{ $anggota->jad ?? 'AA' }}
            </p>
        </div>

        <a href="/ketualab/monitoring-anggota" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Filter Periode Detail Anggota</h4>
            <p class="text-muted mb-0">
                Detail saat ini:
                <strong>{{ $labelPeriode ?? 'Tahunan' }}</strong>

                @if(isset($tanggalMulai) && isset($tanggalSelesai))
                | {{ $tanggalMulai->format('d/m/Y') }} - {{ $tanggalSelesai->format('d/m/Y') }}
                @endif
            </p>
        </div>
    </div>

    <form action="/ketualab/detail-anggota/{{ $anggota->id_user }}" method="GET">
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

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Target Tahunan</p>
            <h3 class="fw-bold mb-0">{{ $totalTargetTahunan ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Target Periode</p>
            <h3 class="fw-bold mb-0">{{ $totalTargetPeriode ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Realisasi</p>
            <h3 class="fw-bold mb-0">{{ $totalRealisasi ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Progress</p>
            <h3 class="fw-bold mb-0">{{ min($persentaseTotal ?? 0, 100) }}%</h3>
        </div>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Rekap Progress per Kategori</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 22%;">Kategori KM</th>
                    <th style="width: 15%;">Target Tahunan</th>
                    <th style="width: 15%;">Target Periode</th>
                    <th style="width: 12%;">Realisasi</th>
                    <th style="width: 11%;">Sisa</th>
                    <th style="width: 12%;">Progress</th>
                    <th style="width: 8%;">Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rekap as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $item['kategori'] }}
                    </td>

                    <td>
                        {{ $item['target_tahunan'] }}
                    </td>

                    <td>
                        {{ $item['target_periode'] }}
                    </td>

                    <td>
                        {{ $item['realisasi'] }}
                    </td>

                    <td>
                        {{ $item['sisa'] }}
                    </td>

                    <td>
                        <div class="progress mb-1" style="height: 8px;">
                            <div
                                class="progress-bar"
                                role="progressbar"
                                style="width: {{ min($item['persentase'], 100) }}%;"></div>
                        </div>

                        <div class="small text-muted">
                            {{ $item['persentase'] }}%
                        </div>
                    </td>

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
                    <td colspan="8" class="text-center text-muted py-4">
                        Belum ada data rekap.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Riwayat KM yang Diberikan</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori KM</th>
                    <th>Jumlah KM</th>
                    <th>Tahun KM</th>
                    <th>Status KM</th>
                    <th>Tanggal Assign</th>
                </tr>
            </thead>

            <tbody>
                @forelse($riwayatAssign as $index => $assign)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $assign->kategori_km }}
                    </td>

                    <td>
                        {{ $assign->jumlah_km }}
                    </td>

                    <td>
                        {{ $assign->tahun_km }}
                    </td>

                    <td>
                        @if($assign->status_km === 'Aktif')
                        <span class="badge bg-success">Aktif</span>
                        @else
                        <span class="badge bg-secondary">Nonaktif</span>
                        @endif
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($assign->created_at)->format('d/m/Y') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Belum ada KM yang diberikan ke anggota ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Aktivitas KM Anggota</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th>No</th>
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
                        {{ $item->kategori_km }}
                    </td>

                    <td>
                        {{ $item->judul_aktivitas }}
                    </td>

                    <td>
                        {{ $item->deskripsi_singkat ?? '-' }}
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') }}
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Belum ada aktivitas pada periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection