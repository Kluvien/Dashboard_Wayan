@extends('layouts.app')

@section('title', 'Detail KM Lab Riset')

@section('content')
@php
$daftarKmTurun = collect($daftarKmTurun ?? []);
$rekapKategori = collect($rekapKategori ?? []);
$riwayatAssign = collect($riwayatAssign ?? []);
$anggota = collect($anggota ?? []);

$totalKmTurun = $totalKmTurun ?? $rekapKategori->sum(fn($item) => $item['total_km'] ?? 0);
$totalKmAssign = $totalKmAssign ?? $rekapKategori->sum(fn($item) => $item['sudah_assign'] ?? 0);
$totalSisaKm = $totalSisaKm ?? max($totalKmTurun - $totalKmAssign, 0);
$persentaseTotal = $persentaseTotal ?? ($totalKmTurun > 0 ? round(($totalKmAssign / $totalKmTurun) * 100) : 0);
@endphp

<style>
    .km-table th {
        white-space: nowrap;
        vertical-align: middle;
    }

    .km-table td {
        vertical-align: middle;
    }
</style>

<div class="page-heading">
    Detail KM <span class="muted">Lab Riset</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">{{ $lab->nama_lab ?? '-' }}</h4>
            <p class="text-muted mb-0">
                Rekap penurunan dan pembagian KM Lab Riset tahun {{ $tahun ?? now()->year }}.
            </p>
        </div>

        <a href="/ketuakk/km-lab-riset" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Total KM Turun</p>
            <h3 class="fw-bold mb-0">{{ $totalKmTurun }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Sudah Assign</p>
            <h3 class="fw-bold mb-0">{{ $totalKmAssign }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Sisa KM</p>
            <h3 class="fw-bold mb-0">{{ $totalSisaKm }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <p class="text-muted mb-1">Progress Assign</p>
            <h3 class="fw-bold mb-0">{{ min($persentaseTotal, 100) }}%</h3>
        </div>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Daftar KM Turun dari Ketua KK</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 18%;">Kategori KM</th>
                    <th style="width: 10%;">Total KM</th>
                    <th style="width: 12%;">Sudah Assign</th>
                    <th style="width: 10%;">Sisa KM</th>
                    <th style="width: 13%;">Progress</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 10%;">Tanggal</th>
                    <th style="width: 10%;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($daftarKmTurun as $index => $km)
                @php
                $total = $km['jumlah_km'] ?? 0;
                $assign = $km['sudah_assign'] ?? 0;
                $sisa = $km['sisa_km'] ?? max($total - $assign, 0);
                $persen = $km['persentase'] ?? ($total > 0 ? round(($assign / $total) * 100) : 0);
                @endphp

                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $km['kategori_km'] ?? '-' }}
                    </td>

                    <td>{{ $total }}</td>

                    <td>{{ $assign }}</td>

                    <td>{{ $sisa }}</td>

                    <td>
                        <div class="progress mb-1" style="height: 8px;">
                            <div class="progress-bar" style="width: {{ min($persen, 100) }}%;"></div>
                        </div>
                        <div class="small text-muted">{{ min($persen, 100) }}%</div>
                    </td>

                    <td>
                        @if($sisa <= 0)
                            <span class="badge bg-success">Selesai</span>
                            @else
                            <span class="badge bg-warning text-dark">Belum</span>
                            @endif
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($km['created_at'])->format('d/m/Y') }}
                    </td>

                    <td>
                        <form
                            action="/ketuakk/km-lab-riset/{{ $km['id_km_lab'] }}"
                            method="POST"
                            class="js-delete-form"
                            data-message="Apakah Anda yakin ingin menghapus KM turun ini? Semua assign anggota yang terkait juga akan terhapus.">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-delete btn-sm w-100">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        Belum ada KM yang diturunkan ke lab ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Rekap KM per Kategori</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori KM</th>
                    <th>Total KM</th>
                    <th>Sudah Assign</th>
                    <th>Sisa KM</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rekapKategori as $index => $item)
                @php
                $total = $item['total_km'] ?? 0;
                $assign = $item['sudah_assign'] ?? 0;
                $sisa = $item['sisa_km'] ?? max($total - $assign, 0);
                $persen = $item['persentase'] ?? ($total > 0 ? round(($assign / $total) * 100) : 0);
                @endphp

                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="fw-bold">{{ $item['kategori'] }}</td>
                    <td>{{ $total }}</td>
                    <td>{{ $assign }}</td>
                    <td>{{ $sisa }}</td>
                    <td>
                        <div class="progress mb-1" style="height: 8px;">
                            <div class="progress-bar" style="width: {{ min($persen, 100) }}%;"></div>
                        </div>
                        <div class="small text-muted">{{ min($persen, 100) }}%</div>
                    </td>
                    <td>
                        @if($total > 0 && $sisa <= 0)
                            <span class="badge bg-success">Selesai</span>
                            @else
                            <span class="badge bg-warning text-dark">Belum</span>
                            @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Belum ada data KM pada lab ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Riwayat Assign KM ke Anggota</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori KM</th>
                    <th>Nama Anggota</th>
                    <th>NIDN</th>
                    <th>JAD</th>
                    <th>Jumlah KM</th>
                    <th>Tanggal</th>
                </tr>
            </thead>

            <tbody>
                @forelse($riwayatAssign as $index => $assign)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="fw-bold">{{ $assign->kategori_km }}</td>
                    <td>{{ $assign->nama_dosen ?? $assign->username }}</td>
                    <td>{{ $assign->nidn ?? '-' }}</td>
                    <td>
                        <span class="badge bg-primary">
                            {{ $assign->jad ?? 'AA' }}
                        </span>
                    </td>
                    <td>{{ $assign->jumlah_km }}</td>
                    <td>{{ \Carbon\Carbon::parse($assign->created_at)->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Belum ada KM yang dibagikan ke anggota.
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
        <table class="table align-middle mb-0 km-table" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Anggota</th>
                    <th>NIDN</th>
                    <th>Email</th>
                    <th>JAD</th>
                    <th>Total Assign</th>
                </tr>
            </thead>

            <tbody>
                @forelse($anggota as $index => $item)
                @php
                $totalAssignAnggota = $riwayatAssign
                ->where('nidn', $item->nidn)
                ->sum('jumlah_km');
                @endphp

                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="fw-bold">{{ $item->nama_dosen ?? $item->username }}</td>
                    <td>{{ $item->nidn ?? '-' }}</td>
                    <td>{{ $item->email ?? '-' }}</td>
                    <td>
                        <span class="badge bg-primary">
                            {{ $item->jad ?? 'AA' }}
                        </span>
                    </td>
                    <td>{{ $totalAssignAnggota }}</td>
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