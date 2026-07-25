@extends('layouts.app')

@section('title', 'KM Lab Riset')

@section('content')
@php
    $dataLab = collect($dataLab ?? []);

    $kategoriDefault = $kategoriDefault ?? [
        'Penelitian',
        'Publikasi',
        'Pengabdian',
        'Penunjang',
    ];

    $rekapKategori = collect($rekapKategori ?? []);
    $riwayatPenurunanKm = collect($riwayatPenurunanKm ?? []);
@endphp

<style>
    .km-table th,
    .history-table th {
        white-space: nowrap;
        vertical-align: middle;
        font-size: 13px;
    }

    .km-table td,
    .history-table td {
        vertical-align: middle;
        font-size: 13px;
    }

    .group-header {
        background: #F3F6FB;
        text-align: center;
        font-weight: 800;
    }

    /*
    |--------------------------------------------------------------------------
    | Ringkasan Target KM per Kategori
    |--------------------------------------------------------------------------
    */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .summary-card {
        position: relative;
        overflow: hidden;
        min-height: 245px;
        padding: 18px;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        background: linear-gradient(180deg, #FFFFFF 0%, #FBFCFF 100%);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
    }

    .summary-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, #477EF7, #77A2FF);
    }

    .summary-title {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #475569;
        font-size: 14px;
        font-weight: 800;
        margin-bottom: 15px;
    }

    .summary-title::before {
        content: "";
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #477EF7;
        box-shadow: 0 0 0 4px #EAF1FF;
    }

    .summary-progress-row {
        display: flex;
        justify-content: space-between;
        align-items: end;
        gap: 12px;
        margin-bottom: 8px;
    }

    .summary-progress-label {
        color: #64748B;
        font-size: 12px;
        font-weight: 700;
    }

    .summary-progress-value {
        color: #0F172A;
        font-size: 30px;
        line-height: 1;
        font-weight: 800;
    }

    .summary-progress-bar {
        height: 9px;
        overflow: hidden;
        border-radius: 999px;
        background: #E8EDF5;
        margin-bottom: 15px;
    }

    .summary-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #477EF7, #76A3FF);
    }

    .summary-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
    }

    .summary-info-item {
        min-height: 65px;
        padding: 10px 11px;
        border: 1px solid #E2E8F0;
        border-radius: 11px;
        background: #F8FAFC;
    }

    .summary-info-item.full-width {
        grid-column: span 2;
    }

    .summary-info-label {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 5px;
        color: #64748B;
        font-size: 11px;
        font-weight: 800;
    }

    .summary-info-value {
        font-size: 21px;
        font-weight: 800;
        line-height: 1;
    }

    .summary-info-item.target {
        background: #EFF6FF;
        border-color: #BFDBFE;
    }

    .summary-info-item.target .summary-info-value {
        color: #2563EB;
    }

    .summary-info-item.turun {
        background: #ECFDF5;
        border-color: #BBF7D0;
    }

    .summary-info-item.turun .summary-info-value {
        color: #16A34A;
    }

    .summary-info-item.sisa-alert {
        background: #FEF2F2;
        border-color: #FECACA;
    }

    .summary-info-item.sisa-alert .summary-info-label,
    .summary-info-item.sisa-alert .summary-info-value {
        color: #DC2626;
    }

    .summary-info-item.sisa-done {
        background: #ECFDF5;
        border-color: #BBF7D0;
    }

    .summary-info-item.sisa-done .summary-info-label,
    .summary-info-item.sisa-done .summary-info-value {
        color: #15803D;
    }

    .summary-note {
        margin-top: 11px;
        font-size: 11px;
        font-weight: 700;
    }

    .summary-note.alert {
        color: #DC2626;
    }

    .summary-note.done {
        color: #15803D;
    }

    /*
    |--------------------------------------------------------------------------
    | Table Lab Riset
    |--------------------------------------------------------------------------
    */
    .lab-name {
        min-width: 220px;
        font-weight: 800;
        line-height: 1.35;
    }

    .progress-soft {
        width: 100%;
        min-width: 100px;
        height: 9px;
        overflow: hidden;
        border-radius: 999px;
        background: #E8EDF5;
    }

    .progress-soft-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #477EF7, #76A3FF);
    }

    .status-badge {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 6px 11px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-done {
        color: #15803D;
        background: #DCFCE7;
    }

    .status-progress {
        color: #B45309;
        background: #FEF3C7;
    }

    .status-empty {
        color: #64748B;
        background: #E2E8F0;
    }

    /*
    |--------------------------------------------------------------------------
    | Riwayat Penurunan KM
    |--------------------------------------------------------------------------
    */
    .history-card {
        margin-top: 20px;
    }

    .history-timestamp {
        display: inline-flex;
        flex-direction: column;
        gap: 2px;
        min-width: 130px;
        padding: 8px 10px;
        border: 1px solid #DBEAFE;
        border-radius: 10px;
        background: #EFF6FF;
    }

    .history-timestamp-date {
        color: #1D4ED8;
        font-size: 12px;
        font-weight: 800;
    }

    .history-timestamp-time {
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
    }

    .history-lab-name {
        min-width: 190px;
        color: #0F172A;
        font-weight: 800;
        line-height: 1.35;
    }

    .history-kategori {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #EEF4FF;
        color: #2563EB;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .history-subkategori {
        min-width: 150px;
        color: #334155;
        font-weight: 700;
    }

    .history-keterangan {
        min-width: 180px;
        max-width: 250px;
        white-space: normal;
        color: #64748B;
        line-height: 1.45;
    }

    .history-tw {
        text-align: center;
        color: #2563EB;
        font-size: 15px;
        font-weight: 800;
    }

    .history-total {
        color: #0F172A;
        font-size: 16px;
        font-weight: 900;
        text-align: center;
    }

    .status-active {
        color: #15803D;
        background: #DCFCE7;
    }

    .status-inactive {
        color: #64748B;
        background: #E2E8F0;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 7px;
        padding: 30px 15px;
        text-align: center;
        color: #64748B;
    }

    .empty-state i {
        color: #94A3B8;
        font-size: 36px;
    }

    @media (max-width: 768px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-heading">
    Kontrak Manajemen <span class="muted">Lab Riset</span>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-4 mb-4">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger rounded-4 mb-4">
        {{ session('error') }}
    </div>
@endif

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">KM Lab Riset Tahun {{ $tahun }}</h4>
            <p class="text-muted mb-0">
                Menampilkan seluruh Lab Riset beserta jumlah KM yang telah diturunkan dari Ketua KK.
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="/ketuakk/km-lab-riset/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Turunkan KM ke Lab
            </a>

            <form method="GET" action="/ketuakk/km-lab-riset" class="d-flex gap-2">
                <select name="tahun" class="form-select" style="min-width: 120px;">
                    @foreach($tahunOptions ?? [$tahun] as $itemTahun)
                        <option
                            value="{{ $itemTahun }}"
                            {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
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
</div>

@include('partials.periode-saat-ini')

<div class="summary-grid">
    @foreach($kategoriDefault as $kategori)
        @php
            $rekap = $rekapKategori->firstWhere('kategori', $kategori);

            $targetKk = (int) data_get($rekap, 'total_km_kk', 0);
            $totalTurun = (int) data_get($rekap, 'total_turun', 0);
            $sisa = (int) data_get($rekap, 'sisa', max($targetKk - $totalTurun, 0));

            $persentaseTurun = $targetKk > 0
                ? min(round(($totalTurun / $targetKk) * 100), 100)
                : 0;

            $sisaClass = $sisa > 0 ? 'sisa-alert' : 'sisa-done';
            $noteClass = $sisa > 0 ? 'alert' : 'done';
        @endphp

        <div class="summary-card">
            <div class="summary-title">
                {{ $kategori }}
            </div>

            <div class="summary-progress-row">
                <div>
                    <div class="summary-progress-label">
                        Progress Penurunan KM
                    </div>
                </div>

                <div class="summary-progress-value">
                    {{ $persentaseTurun }}%
                </div>
            </div>

            <div class="summary-progress-bar">
                <div
                    class="summary-progress-fill"
                    style="width: {{ $persentaseTurun }}%;">
                </div>
            </div>

            <div class="summary-info-grid">
                <div class="summary-info-item target">
                    <div class="summary-info-label">
                        <i class="bi bi-bullseye"></i>
                        Target KK
                    </div>

                    <div class="summary-info-value">
                        {{ number_format($targetKk, 0, ',', '.') }}
                    </div>
                </div>

                <div class="summary-info-item turun">
                    <div class="summary-info-label">
                        <i class="bi bi-arrow-down-circle"></i>
                        Sudah Diturunkan
                    </div>

                    <div class="summary-info-value">
                        {{ number_format($totalTurun, 0, ',', '.') }}
                    </div>
                </div>

                <div class="summary-info-item full-width {{ $sisaClass }}">
                    <div class="summary-info-label">
                        <i class="bi bi-exclamation-circle"></i>
                        Sisa Target Belum Turun
                    </div>

                    <div class="summary-info-value">
                        {{ number_format($sisa, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="summary-note {{ $noteClass }}">
                @if($sisa > 0)
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Masih ada {{ number_format($sisa, 0, ',', '.') }} KM yang perlu diturunkan ke Lab.
                @else
                    <i class="bi bi-check-circle-fill me-1"></i>
                    Seluruh target kategori ini sudah diturunkan ke Lab.
                @endif
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Daftar Lab Riset</h4>
            <p class="text-muted mb-0">
                Ketua KK dapat melihat penurunan KM ke setiap Lab Riset dan membuka detail pembagian KM anggota.
            </p>
        </div>

        <div class="small text-muted">
            Total Lab: <strong>{{ $dataLab->count() }}</strong>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Nama Lab Riset</th>
                    <th colspan="5" class="group-header">KM DITURUNKAN KE LAB</th>
                    <th rowspan="2">Total Turun</th>
                    <th rowspan="2">Sudah Dibagi ke Anggota</th>
                    <th rowspan="2">Sisa KM</th>
                    <th rowspan="2">Progress</th>
                    <th rowspan="2">Status</th>
                    <th rowspan="2">Aksi</th>
                </tr>

                <tr>
                    @foreach($kategoriDefault as $kategori)
                        <th>{{ $kategori }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @forelse($dataLab as $index => $lab)
                    @php
                        $totalTurun = (int) data_get($lab, 'total_turun', 0);
                        $totalAssign = (int) data_get($lab, 'total_assign', 0);
                        $sisaKm = (int) data_get($lab, 'sisa_km', 0);
                        $persentase = (int) data_get($lab, 'persentase', 0);
                        $status = data_get($lab, 'status', 'Belum Ada KM');

                        $statusClass = match ($status) {
                            'Selesai' => 'status-done',
                            'Belum Selesai' => 'status-progress',
                            default => 'status-empty',
                        };
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td>
                            <div class="lab-name">
                                {{ data_get($lab, 'nama_lab', '-') }}
                            </div>
                        </td>

                        @foreach($kategoriDefault as $kategori)
                            <td class="fw-bold text-center">
                                {{ data_get($lab, 'turun_per_kategori.' . $kategori, 0) }}
                            </td>
                        @endforeach

                        <td class="fw-bold text-center">
                            {{ $totalTurun }}
                        </td>

                        <td class="text-center">
                            {{ $totalAssign }}
                        </td>

                        <td class="text-center">
                            {{ $sisaKm }}
                        </td>

                        <td style="min-width: 145px;">
                            <div class="progress-soft mb-1">
                                <div
                                    class="progress-soft-fill"
                                    style="width: {{ min($persentase, 100) }}%;">
                                </div>
                            </div>

                            <div class="small text-muted text-center">
                                {{ min($persentase, 100) }}%
                            </div>
                        </td>

                        <td>
                            <span class="status-badge {{ $statusClass }}">
                                {{ $status }}
                            </span>
                        </td>

                        <td>
                            <a
                                href="/ketuakk/km-lab-riset/{{ data_get($lab, 'id_lab') }}?tahun={{ $tahun }}"
                                class="btn btn-primary btn-sm">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13">
                            <div class="empty-state">
                                <i class="bi bi-building"></i>
                                <strong>Belum ada Lab Riset.</strong>
                                <span>Tambahkan data Lab Riset terlebih dahulu pada menu Data Master.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card history-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Riwayat Penurunan KM ke Lab Riset</h4>
            <p class="text-muted mb-0">
                Riwayat target KM yang diturunkan Ketua KK kepada Lab Riset pada tahun {{ $tahun }}.
            </p>
        </div>

        <div class="small text-muted">
            Total Riwayat: <strong>{{ $riwayatPenurunanKm->count() }}</strong>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 history-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Waktu Penurunan</th>
                    <th>Lab Riset</th>
                    <th>Kategori KM</th>
                    <th>Sub Kategori / Jenis KM</th>
                    <th>Keterangan</th>
                    <th class="text-center">TW 1</th>
                    <th class="text-center">TW 2</th>
                    <th class="text-center">TW 3</th>
                    <th class="text-center">TW 4</th>
                    <th class="text-center">Total Turun</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($riwayatPenurunanKm as $index => $riwayat)
                    @php
                        $waktuPenurunan = !empty($riwayat->waktu_penurunan)
                            ? \Carbon\Carbon::parse($riwayat->waktu_penurunan)
                            : null;

                        $statusKm = $riwayat->status_km ?? 'Aktif';

                        $statusKmClass = $statusKm === 'Aktif'
                            ? 'status-active'
                            : 'status-inactive';
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td>
                            @if($waktuPenurunan)
                                <div class="history-timestamp">
                                    <span class="history-timestamp-date">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        {{ $waktuPenurunan->format('d/m/Y') }}
                                    </span>

                                    <span class="history-timestamp-time">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $waktuPenurunan->format('H:i') }}
                                    </span>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            <div class="history-lab-name">
                                {{ $riwayat->nama_lab ?? '-' }}
                            </div>
                        </td>

                        <td>
                            <span class="history-kategori">
                                <i class="bi bi-folder2-open"></i>
                                {{ $riwayat->kategori_km ?? '-' }}
                            </span>
                        </td>

                        <td>
                            <div class="history-subkategori">
                                {{ $riwayat->sub_kategori_km ?? '-' }}
                            </div>
                        </td>

                        <td>
                            <div class="history-keterangan">
                                {{ $riwayat->keterangan ?? '-' }}
                            </div>
                        </td>

                        <td class="history-tw">
                            {{ (int) ($riwayat->triwulan_1 ?? 0) }}
                        </td>

                        <td class="history-tw">
                            {{ (int) ($riwayat->triwulan_2 ?? 0) }}
                        </td>

                        <td class="history-tw">
                            {{ (int) ($riwayat->triwulan_3 ?? 0) }}
                        </td>

                        <td class="history-tw">
                            {{ (int) ($riwayat->triwulan_4 ?? 0) }}
                        </td>

                        <td>
                            <div class="history-total">
                                {{ (int) ($riwayat->jumlah_km ?? 0) }}
                            </div>
                        </td>

                        <td>
                            <span class="status-badge {{ $statusKmClass }}">
                                {{ $statusKm }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12">
                            <div class="empty-state">
                                <i class="bi bi-clock-history"></i>
                                <strong>Belum ada riwayat penurunan KM.</strong>
                                <span>
                                    Riwayat akan muncul setelah Ketua KK menurunkan KM kepada Lab Riset.
                                </span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection