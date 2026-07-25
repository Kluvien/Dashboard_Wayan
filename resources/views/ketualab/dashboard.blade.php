@extends('layouts.app')

@section('title', 'Dashboard Ketua Lab')

@section('content')
@php
    $kategoriCards = collect($kategoriCards ?? []);
    $tahunOptions = collect($tahunOptions ?? [$tahun ?? now()->year]);

    $mode = $mode ?? 'tahunan';
    $triwulan = (int) ($triwulan ?? 1);
    $semester = (int) ($semester ?? 1);

    $periodeLabel = $periodeLabel ?? ('Tahun ' . ($tahun ?? now()->year));
    $periodeKeterangan = $periodeKeterangan
        ?? 'Data target, pembagian, dan realisasi ditampilkan untuk satu tahun penuh.';

    $namaLab = $lab->nama_lab ?? 'Lab Riset';

    $monitoringAnggotaRows = collect($monitoringAnggotaRows ?? []);
    $kategoriDetailCharts = collect($kategoriDetailCharts ?? []);

    $anggotaChartLabels = $anggotaChartLabels ?? [];
    $anggotaAchievementPercentages = $anggotaAchievementPercentages ?? [];

    $jumlahAnggota = (int) ($jumlahAnggota ?? 0);
    $jumlahAnggotaSelesai = (int) ($jumlahAnggotaSelesai ?? 0);
    $jumlahAnggotaBerjalan = (int) ($jumlahAnggotaBerjalan ?? 0);

    $totalTargetLab = (int) ($totalTargetLab ?? 0);
    $totalRealisasiLab = (int) ($totalRealisasiLab ?? 0);
    $totalSisaLab = (int) ($totalSisaLab ?? max($totalTargetLab - $totalRealisasiLab, 0));
    $persentaseRealisasi = (float) ($persentaseRealisasi ?? 0);

    $jumlahMenungguVerifikasi = (int) ($jumlahMenungguVerifikasi ?? 0);
    $pengajuanMenungguVerifikasi = collect($pengajuanMenungguVerifikasi ?? []);
    $riwayatVerifikasi = collect($riwayatVerifikasi ?? []);
@endphp

<style>
    .dashboard-header {
        padding: 18px 22px;
    }

    .dashboard-filter-form {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .dashboard-filter-control {
        min-width: 126px;
        height: 38px;
        padding: 0 11px;
        border: 1px solid #CBD5E1;
        border-radius: 10px;
        background: #FFFFFF;
        color: #1E293B;
        font-size: 14px;
        font-weight: 700;
        outline: none;
    }

    .dashboard-filter-control.small-control {
        min-width: 104px;
    }

    .dashboard-filter-control:focus {
        border-color: #477EF7;
        box-shadow: 0 0 0 3px rgba(71, 126, 247, 0.14);
    }

    .dashboard-period-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 9px;
        padding: 5px 9px;
        border: 1px solid #DBEAFE;
        border-radius: 999px;
        background: #EFF6FF;
        color: #2563EB;
        font-size: 11px;
        font-weight: 800;
    }

    /*
    |--------------------------------------------------------------------------
    | Ringkasan kategori KM
    |--------------------------------------------------------------------------
    */
    .dashboard-stat-grid {
        display: grid;
        /* Empat kategori aktif akan memenuhi satu baris tanpa menyisakan kolom kosong. */
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
        margin-bottom: 18px;
    }

    .dashboard-stat-card {
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 270px;
        padding: 17px;
        border: 1px solid #E2E8F0;
        border-radius: 18px;
        background: linear-gradient(180deg, #FFFFFF 0%, #FAFCFF 100%);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .dashboard-stat-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, #477EF7 0%, #7AA4FF 100%);
    }

    .dashboard-stat-card:hover {
        transform: translateY(-3px);
        border-color: #C7D7FF;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.10);
    }

    .dashboard-stat-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 10px;
    }

    .dashboard-stat-label {
        color: #475569;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.35;
    }

    .dashboard-stat-subtitle {
        margin-top: 2px;
        color: #94A3B8;
        font-size: 11px;
        font-weight: 600;
    }

    .dashboard-stat-value {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 64px;
        padding: 8px 10px;
        border: 1px solid #DCE7FF;
        border-radius: 13px;
        background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
        color: #2563EB;
        font-size: 22px;
        font-weight: 800;
        line-height: 1;
    }

    .dashboard-category-progress {
        height: 9px;
        border-radius: 999px;
        background: #E8EDF5;
        overflow: hidden;
        margin: 0 0 13px;
    }

    .dashboard-category-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #477EF7 0%, #6B9CFF 100%);
    }

    .dashboard-km-info {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
        margin-bottom: 12px;
    }

    .dashboard-km-item {
        min-height: 62px;
        padding: 9px 10px;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        background: #F8FAFC;
    }

    .dashboard-km-item-label {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 4px;
        color: #64748B;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.25px;
        text-transform: uppercase;
    }

    .dashboard-km-item-value {
        font-size: 21px;
        font-weight: 800;
        line-height: 1;
    }

    .dashboard-km-item.target-item {
        border-color: #BFDBFE;
        background: #EFF6FF;
    }

    .dashboard-km-item.realisasi-item {
        border-color: #BBF7D0;
        background: #ECFDF5;
    }

    .dashboard-km-item.dibagi-item {
        border-color: #DDD6FE;
        background: #F5F3FF;
    }

    .dashboard-km-item.belum-dibagi-alert {
        border-color: #FECACA;
        background: #FEF2F2;
    }

    .dashboard-km-item.belum-dibagi-done {
        border-color: #BBF7D0;
        background: #ECFDF5;
    }

    .text-target {
        color: #2563EB;
    }

    .text-realisasi {
        color: #059669;
    }

    .text-dibagi {
        color: #7C3AED;
    }

    .text-belum-dibagi {
        color: #DC2626;
    }

    .text-belum-dibagi-selesai {
        color: #15803D;
    }

    .dashboard-status-note {
        min-height: 33px;
        margin-top: auto;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.45;
    }

    .dashboard-status-note.alert {
        color: #DC2626;
    }

    .dashboard-status-note.done {
        color: #15803D;
    }

    .dashboard-category-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        margin-top: 10px;
        padding: 9px 12px;
        border-radius: 10px;
        background: linear-gradient(90deg, #477EF7 0%, #5B8CF8 100%);
        color: #FFFFFF !important;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        transition: transform 0.2s ease, filter 0.2s ease;
    }

    .dashboard-category-button:hover {
        transform: translateY(-1px);
        filter: brightness(0.97);
        color: #FFFFFF !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Chart dan Ringkasan
    |--------------------------------------------------------------------------
    */
    .dashboard-grid-main {
        display: grid;
        grid-template-columns: minmax(0, 1.55fr) minmax(360px, 1fr);
        gap: 18px;
        margin-bottom: 18px;
    }

    .dashboard-panel-title {
        color: #0F172A;
        font-size: 18px;
        font-weight: 800;
    }

    .dashboard-panel-subtitle {
        color: #64748B;
        font-size: 13px;
        line-height: 1.45;
    }

    .dashboard-chart-box {
        position: relative;
        min-height: 275px;
    }

    .dashboard-chart-box-small {
        position: relative;
        min-height: 245px;
    }

    .dashboard-category-chart-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 18px;
    }

    .dashboard-category-chart-grid .card:last-child:nth-child(odd) {
        grid-column: span 2;
    }

    .summary-box {
        height: 100%;
        padding: 14px;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        background: #FFFFFF;
    }

    .summary-box-label {
        color: #64748B;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.35;
    }

    .summary-box-value {
        margin-top: 5px;
        color: #0F172A;
        font-size: 27px;
        font-weight: 800;
        line-height: 1;
    }

    .summary-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
    }

    .summary-chip.success {
        border: 1px solid #BBF7D0;
        background: #ECFDF5;
        color: #15803D;
    }

    .summary-chip.warning {
        border: 1px solid #FDE68A;
        background: #FFFBEB;
        color: #B45309;
    }

    .progress-soft {
        height: 9px;
        overflow: hidden;
        border-radius: 999px;
        background: #E8EDF5;
    }

    .progress-soft-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #477EF7 0%, #6B9CFF 100%);
    }

    /*
    |--------------------------------------------------------------------------
    | Tabel Monitoring
    |--------------------------------------------------------------------------
    */
    .monitoring-table th {
        padding: 13px 10px;
        border-bottom: 1px solid #E2E8F0;
        color: #475569;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.15px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .monitoring-table td {
        padding: 13px 10px;
        border-bottom: 1px solid #F1F5F9;
        color: #334155;
        font-size: 13px;
        vertical-align: middle;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 11px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-success {
        background: #DCFCE7;
        color: #15803D;
    }

    .status-warning {
        background: #FEF3C7;
        color: #B45309;
    }

    .status-danger {
        background: #FEE2E2;
        color: #B91C1C;
    }

    .status-secondary {
        background: #E5E7EB;
        color: #475569;
    }


    /*
    |--------------------------------------------------------------------------
    | Panel verifikasi aktivitas KM
    |--------------------------------------------------------------------------
    */
    .approval-alert-card {
        margin-bottom: 18px;
        padding: 17px;
        border: 1px solid #FDE68A;
        border-left: 5px solid #F59E0B;
        border-radius: 16px;
        background: linear-gradient(135deg, #FFFBEB 0%, #FFFFFF 70%);
        box-shadow: 0 8px 18px rgba(180, 83, 9, 0.08);
    }

    .approval-alert-title {
        color: #92400E;
        font-size: 18px;
        font-weight: 900;
    }

    .approval-alert-subtitle {
        margin: 4px 0 0;
        color: #A16207;
        font-size: 13px;
        font-weight: 600;
    }

    .approval-alert-count {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        background: #FEF3C7;
        color: #92400E;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .approval-list {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 15px;
    }

    .approval-item {
        display: flex;
        flex-direction: column;
        min-height: 150px;
        padding: 13px;
        border: 1px solid #FDE68A;
        border-radius: 13px;
        background: rgba(255, 255, 255, 0.92);
    }

    .approval-item-name {
        color: #0F172A;
        font-size: 13px;
        font-weight: 900;
    }

    .approval-item-meta {
        margin-top: 3px;
        color: #78716C;
        font-size: 11px;
        font-weight: 700;
    }

    .approval-item-title {
        margin-top: 10px;
        color: #334155;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.35;
    }

    .approval-item-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-top: auto;
        padding-top: 12px;
    }

    .approval-time {
        color: #A16207;
        font-size: 10px;
        font-weight: 800;
    }

    .approval-detail-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 7px 9px;
        border-radius: 9px;
        background: #F59E0B;
        color: #FFFFFF !important;
        font-size: 11px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }

    .approval-detail-btn:hover {
        background: #D97706;
        color: #FFFFFF !important;
    }

    .verification-history-card {
        margin-bottom: 18px;
    }

    .verification-history-table th,
    .verification-history-table td {
        padding: 11px 10px;
        border-bottom: 1px solid #F1F5F9;
        font-size: 12px;
        vertical-align: middle;
    }

    .verification-history-table th {
        color: #475569;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .verification-note {
        max-width: 300px;
        color: #64748B;
        line-height: 1.4;
        white-space: normal;
    }

    .decision-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .decision-pill.accepted { color: #15803D; background: #DCFCE7; }
    .decision-pill.rejected { color: #B91C1C; background: #FEE2E2; }

    @media (max-width: 1200px) {
        .dashboard-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .approval-list {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-grid-main {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 992px) {
        .dashboard-category-chart-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-category-chart-grid .card:last-child:nth-child(odd) {
            grid-column: span 1;
        }
    }

    @media (max-width: 768px) {
        .dashboard-stat-grid {
            grid-template-columns: 1fr;
        }

        .approval-list {
            grid-template-columns: 1fr;
        }

        .dashboard-km-info {
            grid-template-columns: 1fr;
        }

        .dashboard-filter-form {
            width: 100%;
        }

        .dashboard-filter-control,
        .dashboard-filter-control.small-control {
            flex: 1 1 130px;
        }
    }
</style>

<div class="page-heading">
    Dashboard <span class="muted">Ketua Lab</span>
</div>

<div class="card dashboard-header mb-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Ringkasan KM Lab {{ $periodeLabel }}</h4>
            <p class="text-muted mb-0">
                Lab: {{ $namaLab }}
            </p>
            <span class="dashboard-period-badge">
                <i class="bi bi-calendar3"></i>
                {{ $periodeKeterangan }}
            </span>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
            <form method="GET" action="{{ url('/ketualab/dashboard') }}" class="dashboard-filter-form">
                <select name="mode" id="dashboardPeriodMode" class="dashboard-filter-control">
                    <option value="tahunan" {{ $mode === 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                    <option value="triwulan" {{ $mode === 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                    <option value="semester" {{ $mode === 'semester' ? 'selected' : '' }}>Semester</option>
                </select>

                <select name="tahun" class="dashboard-filter-control small-control">
                    @foreach($tahunOptions as $itemTahun)
                        <option value="{{ $itemTahun }}" {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                            {{ $itemTahun }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="triwulan"
                    id="dashboardTriwulanGroup"
                    class="dashboard-filter-control small-control {{ $mode === 'triwulan' ? '' : 'd-none' }}">
                    @for($tw = 1; $tw <= 4; $tw++)
                        <option value="{{ $tw }}" {{ $triwulan === $tw ? 'selected' : '' }}>
                            Triwulan {{ $tw }}
                        </option>
                    @endfor
                </select>

                <select
                    name="semester"
                    id="dashboardSemesterGroup"
                    class="dashboard-filter-control small-control {{ $mode === 'semester' ? '' : 'd-none' }}">
                    <option value="1" {{ $semester === 1 ? 'selected' : '' }}>Semester 1</option>
                    <option value="2" {{ $semester === 2 ? 'selected' : '' }}>Semester 2</option>
                </select>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel-fill me-1"></i>
                    Terapkan
                </button>
            </form>

            <a href="{{ url('/ketualab/penurunan-km') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Bagi KM ke Anggota
            </a>
        </div>
    </div>
</div>

@if($jumlahMenungguVerifikasi > 0)
    <div class="approval-alert-card">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="approval-alert-title">
                    <i class="bi bi-shield-exclamation me-1"></i>
                    Pengajuan KM Menunggu Verifikasi
                </div>
                <p class="approval-alert-subtitle">
                    Tinjau rincian dan bukti aktivitas sebelum mengambil keputusan. Setelah disetujui atau ditolak, pengajuan otomatis tidak lagi tampil di panel ini.
                </p>
            </div>

            <span class="approval-alert-count">
                <i class="bi bi-hourglass-split"></i>
                {{ $jumlahMenungguVerifikasi }} menunggu aksi
            </span>
        </div>

        <div class="approval-list">
            @foreach($pengajuanMenungguVerifikasi as $pengajuan)
                @php
                    $waktuAjukan = $pengajuan->diajukan_pada ?? $pengajuan->updated_at ?? $pengajuan->created_at ?? null;
                @endphp
                <div class="approval-item">
                    <div class="approval-item-name">{{ $pengajuan->nama_anggota ?? '-' }}</div>
                    <div class="approval-item-meta">
                        {{ $pengajuan->nidn ?? '-' }} · {{ $pengajuan->kategori_km ?? '-' }}{{ !empty($pengajuan->sub_kategori_km) ? ' · ' . $pengajuan->sub_kategori_km : '' }}
                    </div>
                    <div class="approval-item-title">{{ \Illuminate\Support\Str::limit($pengajuan->judul_aktivitas ?? '-', 72) }}</div>
                    <div class="approval-item-footer">
                        <span class="approval-time">
                            <i class="bi bi-clock-history me-1"></i>
                            {{ $waktuAjukan ? 'Diajukan ' . \Carbon\Carbon::parse($waktuAjukan)->format('d/m/Y H:i') : 'Baru diajukan' }}
                        </span>
                        <a href="{{ $pengajuan->detail_url }}" class="approval-detail-btn">
                            Detail
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        @if($jumlahMenungguVerifikasi > $pengajuanMenungguVerifikasi->count())
            <div class="small text-muted mt-3 fw-semibold">
                Menampilkan {{ $pengajuanMenungguVerifikasi->count() }} dari {{ $jumlahMenungguVerifikasi }} pengajuan yang menunggu verifikasi.
            </div>
        @endif
    </div>
@endif

@include('partials.periode-saat-ini')
@include('partials.filter-diterapkan')

<div class="dashboard-stat-grid">
    @forelse($kategoriCards as $item)
        @php
            $targetKategori = (int) ($item['target'] ?? 0);
            $realisasiKategori = (int) ($item['realisasi'] ?? 0);
            $dibagiKategori = (int) ($item['dibagi'] ?? 0);
            $belumDibagiKategori = (int) ($item['belum_dibagi'] ?? 0);
            $persentaseKategori = (float) ($item['persentase'] ?? 0);

            $belumDibagiClass = $belumDibagiKategori > 0
                ? 'belum-dibagi-alert'
                : 'belum-dibagi-done';

            $belumDibagiTextClass = $belumDibagiKategori > 0
                ? 'text-belum-dibagi'
                : 'text-belum-dibagi-selesai';
        @endphp

        <div class="dashboard-stat-card">
            <div class="dashboard-stat-card-top">
                <div>
                    <div class="dashboard-stat-label">{{ $item['kategori'] ?? '-' }}</div>
                    <div class="dashboard-stat-subtitle">
                        Progress realisasi kategori KM • {{ $periodeLabel }}
                    </div>
                </div>

                <div class="dashboard-stat-value">
                    {{ rtrim(rtrim(number_format($persentaseKategori, 1), '0'), '.') }}%
                </div>
            </div>

            <div class="dashboard-category-progress">
                <div
                    class="dashboard-category-progress-fill"
                    style="width: {{ min($persentaseKategori, 100) }}%;">
                </div>
            </div>

            <div class="dashboard-km-info">
                <div class="dashboard-km-item target-item">
                    <div class="dashboard-km-item-label">
                        <i class="bi bi-bullseye"></i>
                        Target
                    </div>
                    <div class="dashboard-km-item-value text-target">
                        {{ number_format($targetKategori, 0, ',', '.') }}
                    </div>
                </div>

                <div class="dashboard-km-item realisasi-item">
                    <div class="dashboard-km-item-label">
                        <i class="bi bi-check2-circle"></i>
                        Realisasi
                    </div>
                    <div class="dashboard-km-item-value text-realisasi">
                        {{ number_format($realisasiKategori, 0, ',', '.') }}
                    </div>
                </div>

                <div class="dashboard-km-item dibagi-item">
                    <div class="dashboard-km-item-label">
                        <i class="bi bi-arrow-right-circle"></i>
                        Sudah Dibagi
                    </div>
                    <div class="dashboard-km-item-value text-dibagi">
                        {{ number_format($dibagiKategori, 0, ',', '.') }}
                    </div>
                </div>

                <div class="dashboard-km-item {{ $belumDibagiClass }}">
                    <div class="dashboard-km-item-label">
                        <i class="bi bi-hourglass-split"></i>
                        Belum Dibagi
                    </div>
                    <div class="dashboard-km-item-value {{ $belumDibagiTextClass }}">
                        {{ number_format($belumDibagiKategori, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="dashboard-status-note {{ $belumDibagiKategori > 0 ? 'alert' : 'done' }}">
                @if($targetKategori <= 0)
                    <i class="bi bi-info-circle-fill me-1"></i>
                    Belum ada target KM pada kategori ini.
                @elseif($belumDibagiKategori > 0)
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Masih ada {{ number_format($belumDibagiKategori, 0, ',', '.') }} KM yang belum dibagi.
                @else
                    <i class="bi bi-check-circle-fill me-1"></i>
                    Seluruh target kategori sudah dibagi.
                @endif
            </div>

            <a href="{{ $item['detail_url'] ?? url('/ketualab/monitoring-lab') }}" class="dashboard-category-button">
                Lihat Detail
            </a>
        </div>
    @empty
        <div class="dashboard-stat-card">
            <div class="dashboard-stat-label">Data kategori KM</div>
            <div class="dashboard-stat-subtitle">Belum ada target KM pada periode ini.</div>
        </div>
    @endforelse
</div>

<div class="dashboard-grid-main">
    <div class="card">
        @include('partials.filter-diterapkan')
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-2">
            <div>
                <div class="dashboard-panel-title">Diagram Pencapaian Anggota Lab</div>
                <div class="dashboard-panel-subtitle mb-0">
                    Persentase pencapaian realisasi KM pada masing-masing anggota Lab untuk {{ $periodeLabel }}.
                </div>
            </div>

            <a
                href="{{ url('/ketualab/monitoring-anggota') }}?{{ http_build_query(['tahun' => $tahun, 'mode' => $mode, 'triwulan' => $mode === 'triwulan' ? $triwulan : null, 'semester' => $mode === 'semester' ? $semester : null]) }}"
                class="btn btn-primary btn-sm">
                Lihat Selengkapnya
            </a>
        </div>

        <div class="dashboard-chart-box">
            <canvas id="chartAnggotaAchievement"></canvas>
        </div>
    </div>

    <div class="card">
        @include('partials.filter-diterapkan')
        <h4 class="fw-bold mb-1">Ringkasan Lab</h4>
        <p class="text-muted mb-3">
            Rekap anggota, target, realisasi, dan progress total KM Lab.
        </p>

        <div class="row g-3">
            <div class="col-6">
                <div class="summary-box">
                    <div class="summary-box-label">Jumlah Anggota</div>
                    <div class="summary-box-value">{{ $jumlahAnggota }}</div>
                </div>
            </div>

            <div class="col-6">
                <div class="summary-box">
                    <div class="summary-box-label">Total Target</div>
                    <div class="summary-box-value text-primary">{{ $totalTargetLab }}</div>
                </div>
            </div>

            <div class="col-6">
                <div class="summary-box">
                    <div class="summary-box-label">Realisasi</div>
                    <div class="summary-box-value text-success">{{ $totalRealisasiLab }}</div>
                </div>
            </div>

            <div class="col-6">
                <div class="summary-box">
                    <div class="summary-box-label">Sisa</div>
                    <div class="summary-box-value text-warning">{{ $totalSisaLab }}</div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap mt-3">
            <span class="summary-chip success">
                <i class="bi bi-check-circle-fill"></i>
                Anggota Capai Target: {{ $jumlahAnggotaSelesai }}
            </span>

            <span class="summary-chip warning">
                <i class="bi bi-hourglass-split"></i>
                Anggota Sedang Proses: {{ $jumlahAnggotaBerjalan }}
            </span>
        </div>

        <div class="mt-4">
            <div class="d-flex justify-content-between mb-1">
                <span class="fw-bold">Progress Realisasi Lab ({{ $periodeLabel }})</span>
                <span class="fw-bold">{{ rtrim(rtrim(number_format($persentaseRealisasi, 1), '0'), '.') }}%</span>
            </div>

            <div class="progress-soft">
                <div
                    class="progress-soft-fill"
                    style="width: {{ min($persentaseRealisasi, 100) }}%;">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-category-chart-grid">
    @foreach($kategoriDetailCharts as $index => $chart)
        <div class="card">
            @include('partials.filter-diterapkan')
            <div class="dashboard-panel-title">{{ $chart['kategori'] ?? 'Kategori KM' }}</div>
            <div class="dashboard-panel-subtitle">
                Target dan realisasi berdasarkan sub kategori KM pada {{ $periodeLabel }}.
            </div>

            <div class="dashboard-chart-box-small">
                <canvas id="chartKategoriDetail{{ $index }}"></canvas>
            </div>
        </div>
    @endforeach
</div>

<div class="card verification-history-card">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Riwayat Aksi Verifikasi</h4>
            <p class="text-muted mb-0">Keputusan terbaru Ketua Lab atas pengajuan aktivitas KM anggota.</p>
        </div>

        <a href="{{ url('/ketualab/monitoring-anggota') }}?{{ http_build_query(['tahun' => $tahun, 'periode' => $mode === 'tahunan' ? 'tahun' : $mode, 'triwulan' => $mode === 'triwulan' ? $triwulan : null, 'semester' => $mode === 'semester' ? $semester : null]) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-people me-1"></i>
            Buka Monitoring Anggota
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 verification-history-table">
            <thead>
                <tr>
                    <th>Waktu Aksi</th>
                    <th>Anggota</th>
                    <th>Aktivitas KM</th>
                    <th>Keputusan</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayatVerifikasi as $riwayat)
                    @php
                        $diterima = ($riwayat->keputusan ?? '') === 'Accepted';
                    @endphp
                    <tr>
                        <td>{{ !empty($riwayat->waktu_aksi) ? \Carbon\Carbon::parse($riwayat->waktu_aksi)->format('d/m/Y H:i') : '-' }}</td>
                        <td class="fw-bold">{{ $riwayat->nama_anggota ?? '-' }}</td>
                        <td>
                            <div class="fw-bold">{{ $riwayat->judul_aktivitas ?? '-' }}</div>
                            <div class="small text-muted">{{ $riwayat->kategori_km ?? '-' }}{{ !empty($riwayat->sub_kategori_km) ? ' · ' . $riwayat->sub_kategori_km : '' }}</div>
                        </td>
                        <td>
                            <span class="decision-pill {{ $diterima ? 'accepted' : 'rejected' }}">
                                <i class="bi {{ $diterima ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                                {{ $diterima ? 'Disetujui' : 'Ditolak' }}
                            </span>
                        </td>
                        <td class="verification-note">{{ $riwayat->catatan_verifikasi ?: '-' }}</td>
                        <td>
                            <a href="{{ $riwayat->detail_url }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada aksi verifikasi yang tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Monitoring Anggota Lab</h4>
            <p class="text-muted mb-0">
                Menampilkan maksimal 10 anggota beserta target, realisasi, progress, dan status capaian KM pada {{ $periodeLabel }}.
            </p>
        </div>

        <a
            href="{{ url('/ketualab/monitoring-anggota') }}?{{ http_build_query(['tahun' => $tahun, 'mode' => $mode, 'triwulan' => $mode === 'triwulan' ? $triwulan : null, 'semester' => $mode === 'semester' ? $semester : null]) }}"
            class="btn btn-primary">
            Lihat Selengkapnya
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 monitoring-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Anggota</th>
                    <th>NIDN</th>
                    <th>JAD</th>
                    <th>Target</th>
                    <th>Realisasi</th>
                    <th>Sisa</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($monitoringAnggotaRows as $index => $item)
                    @php
                        $statusClass = $item['status_class'] ?? 'secondary';
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td class="fw-bold">
                            {{ $item['nama_dosen'] ?? ($item['username'] ?? '-') }}
                        </td>

                        <td>{{ $item['nidn'] ?? '-' }}</td>

                        <td>
                            <span class="badge bg-primary">
                                {{ $item['jad'] ?? '-' }}
                            </span>
                        </td>

                        <td>{{ $item['target'] ?? 0 }}</td>
                        <td>{{ $item['realisasi'] ?? 0 }}</td>
                        <td>{{ $item['sisa'] ?? 0 }}</td>

                        <td style="min-width: 160px;">
                            <div class="progress-soft mb-1">
                                <div
                                    class="progress-soft-fill"
                                    style="width: {{ min((float) ($item['progress'] ?? 0), 100) }}%;">
                                </div>
                            </div>

                            <div class="small text-muted">
                                {{ $item['progress'] ?? 0 }}%
                            </div>
                        </td>

                        <td>
                            <span class="status-pill status-{{ $statusClass }}">
                                {{ $item['status'] ?? 'Belum Ada KM' }}
                            </span>
                        </td>

                        <td>
                            <a
                                href="{{ url('/ketualab/monitoring-anggota/' . ($item['id_user'] ?? 0)) }}?{{ http_build_query(['tahun' => $tahun, 'mode' => $mode, 'triwulan' => $mode === 'triwulan' ? $triwulan : null, 'semester' => $mode === 'semester' ? $semester : null]) }}"
                                class="btn btn-primary btn-sm">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            Belum ada data anggota Lab.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modeSelect = document.getElementById('dashboardPeriodMode');
        const triwulanSelect = document.getElementById('dashboardTriwulanGroup');
        const semesterSelect = document.getElementById('dashboardSemesterGroup');

        const setPeriodControlVisibility = function() {
            const mode = modeSelect ? modeSelect.value : 'tahunan';

            if (triwulanSelect) {
                triwulanSelect.classList.toggle('d-none', mode !== 'triwulan');
            }

            if (semesterSelect) {
                semesterSelect.classList.toggle('d-none', mode !== 'semester');
            }
        };

        if (modeSelect) {
            modeSelect.addEventListener('change', setPeriodControlVisibility);
            setPeriodControlVisibility();
        }

        if (typeof Chart === 'undefined') {
            return;
        }

        const chartColors = {
            blue: '#477EF7',
            green: '#22C55E',
            grid: '#E5E7EB',
            text: '#64748B'
        };

        const anggotaLabels = @json($anggotaChartLabels);
        const anggotaValues = @json($anggotaAchievementPercentages);

        const anggotaCanvas = document.getElementById('chartAnggotaAchievement');

        if (anggotaCanvas) {
            new Chart(anggotaCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: anggotaLabels.length ? anggotaLabels : ['Belum ada anggota'],
                    datasets: [{
                        label: 'Pencapaian (%)',
                        data: anggotaValues.length ? anggotaValues : [0],
                        backgroundColor: chartColors.blue,
                        borderRadius: 8,
                        maxBarThickness: 62
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                color: chartColors.text
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                stepSize: 10,
                                color: chartColors.text,
                                callback: function(value) {
                                    return value + '%';
                                }
                            },
                            grid: {
                                color: chartColors.grid
                            }
                        },
                        x: {
                            ticks: {
                                color: chartColors.text
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        const kategoriCharts = @json($kategoriDetailCharts->values());

        kategoriCharts.forEach(function(chart, index) {
            const canvas = document.getElementById('chartKategoriDetail' + index);

            if (!canvas) {
                return;
            }

            new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: chart.labels || ['Belum ada data'],
                    datasets: [
                        {
                            label: 'Target',
                            data: chart.targets || [0],
                            backgroundColor: chartColors.blue,
                            borderRadius: 8
                        },
                        {
                            label: 'Realisasi',
                            data: chart.realisasi || [0],
                            backgroundColor: chartColors.green,
                            borderRadius: 8
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                color: chartColors.text
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: chartColors.text
                            },
                            grid: {
                                color: chartColors.grid
                            }
                        },
                        x: {
                            ticks: {
                                color: chartColors.text
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    });
</script>
@endsection
