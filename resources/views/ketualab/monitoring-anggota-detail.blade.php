@extends('layouts.app')

@section('title', 'Detail Monitoring Anggota Lab')

@section('content')
@php
    $rincianTarget = collect($rincianTarget ?? []);
    $rekapKategori = collect($rekapKategori ?? []);
    $riwayatAktivitas = collect($riwayatAktivitas ?? []);

    $tahun = (int) ($tahun ?? now()->year);
    $periode = $periode ?? 'tahun';
    $triwulan = (int) ($triwulan ?? 1);
    $semester = (int) ($semester ?? 1);
    $triwulanAktif = collect($triwulanAktif ?? [1, 2, 3, 4]);

    $statusLabel = function ($status) {
        return match($status) {
            'Accepted' => 'Disetujui',
            'Submitted' => 'Diajukan',
            'On Progress' => 'Sedang Berjalan',
            'Rejected' => 'Ditolak',
            'Pending' => 'Menunggu Verifikasi',
            default => $status ?: '-',
        };
    };

    $statusClass = function ($status) {
        return match($status) {
            'Accepted', 'Tercapai' => 'success',
            'Submitted' => 'primary',
            'On Progress', 'Pending', 'Sedang Berjalan' => 'warning',
            'Rejected' => 'danger',
            'Belum Mulai' => 'danger',
            default => 'secondary',
        };
    };

    $periodeInfo = match($periode) {
        'triwulan' => 'Target periode dihitung dari Triwulan ' . $triwulan . '.',
        'semester' => 'Target periode dihitung dari Semester ' . $semester . '.',
        default => 'Target periode dihitung dari keseluruhan tahun.',
    };
@endphp

<style>
    .detail-monitoring-page {
        padding-bottom: 28px;
    }

    .detail-card,
    .detail-summary-card,
    .detail-table-card {
        border: 1px solid #E2E8F0;
        border-radius: 17px;
        background: #FFFFFF;
        box-shadow: 0 5px 15px rgba(15, 23, 42, .04);
    }

    .detail-card {
        padding: 18px;
        margin-bottom: 16px;
    }

    .detail-title {
        color: #0F172A;
        font-size: 20px;
        font-weight: 900;
        margin-bottom: 4px;
    }

    .detail-subtitle {
        color: #64748B;
        font-size: 13px;
        margin: 0;
    }

    .detail-member-name {
        color: #0F172A;
        font-size: 18px;
        font-weight: 900;
    }

    .detail-member-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 14px;
        margin-top: 6px;
        color: #64748B;
        font-size: 13px;
        font-weight: 700;
    }

    .detail-period-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
        padding: 6px 10px;
        border: 1px solid #BFDBFE;
        border-radius: 999px;
        background: #EFF6FF;
        color: #2563EB;
        font-size: 12px;
        font-weight: 800;
    }

    .detail-filter-grid {
        display: grid;
        grid-template-columns: minmax(155px, 1fr) minmax(120px, .8fr) minmax(160px, 1fr) minmax(160px, 1fr) auto;
        align-items: end;
        gap: 10px;
    }

    .detail-filter-label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 12px;
        font-weight: 800;
    }

    .detail-filter-control {
        height: 40px;
        border-color: #CBD5E1;
        border-radius: 10px;
        font-size: 14px;
    }

    .detail-btn-primary,
    .detail-btn-secondary {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 15px;
        border: 0;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }

    .detail-btn-primary {
        background: #4F7DF3;
        color: #FFFFFF;
    }

    .detail-btn-primary:hover {
        background: #3E6DE8;
        color: #FFFFFF;
    }

    .detail-btn-secondary {
        background: #6B7280;
        color: #FFFFFF;
    }

    .detail-btn-secondary:hover {
        background: #4B5563;
        color: #FFFFFF;
    }

    .detail-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }

    .detail-summary-card {
        padding: 15px;
    }

    .detail-summary-label {
        color: #64748B;
        font-size: 12px;
        font-weight: 800;
    }

    .detail-summary-value {
        margin-top: 7px;
        color: #0F172A;
        font-size: 24px;
        font-weight: 900;
        line-height: 1;
    }

    .detail-summary-value.primary { color: #2563EB; }
    .detail-summary-value.success { color: #059669; }
    .detail-summary-value.warning { color: #D97706; }

    .detail-table-card {
        padding: 17px;
        margin-bottom: 16px;
    }

    .detail-section-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .detail-section-title {
        color: #111827;
        font-size: 18px;
        font-weight: 900;
        margin-bottom: 3px;
    }

    .detail-section-desc {
        color: #64748B;
        font-size: 13px;
        margin: 0;
    }

    .detail-table-wrap {
        overflow-x: auto;
    }

    .detail-table {
        width: 100%;
        min-width: 1380px;
        border-collapse: collapse;
    }

    .detail-table th {
        padding: 10px 8px;
        color: #334155;
        border-bottom: 1px solid #E2E8F0;
        background: #F8FAFC;
        font-size: 10px;
        font-weight: 900;
        text-align: center;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .detail-table th.group {
        background: #EFF6FF;
        color: #1D4ED8;
    }

    .detail-table td {
        padding: 11px 8px;
        color: #334155;
        border-bottom: 1px solid #F1F5F9;
        font-size: 12px;
        vertical-align: top;
    }

    .detail-table td.center {
        text-align: center;
        vertical-align: middle;
    }

    .category-name {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 10px;
        border: 1px solid #BFDBFE;
        border-radius: 999px;
        background: #EFF6FF;
        color: #1D4ED8;
        font-size: 12px;
        font-weight: 900;
    }

    .target-name {
        color: #0F172A;
        font-weight: 900;
        line-height: 1.35;
    }

    .keterangan-text {
        max-width: 220px;
        color: #64748B;
        line-height: 1.45;
    }

    .deadline-list {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 7px;
    }

    .deadline-item {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 6px;
        border: 1px solid #DBEAFE;
        border-radius: 7px;
        background: #F8FAFC;
        color: #2563EB;
        font-size: 10px;
        font-weight: 800;
    }

    .number-target {
        color: #1D4ED8;
        font-size: 14px;
        font-weight: 900;
    }

    .number-realisasi {
        color: #059669;
        font-size: 14px;
        font-weight: 900;
    }

    .number-sisa {
        color: #D97706;
        font-size: 14px;
        font-weight: 900;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 84px;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .status-success { color: #15803D; background: #DCFCE7; }
    .status-warning { color: #B45309; background: #FEF3C7; }
    .status-danger { color: #DC2626; background: #FEE2E2; }
    .status-primary { color: #1D4ED8; background: #DBEAFE; }
    .status-secondary { color: #64748B; background: #E2E8F0; }

    .rekap-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .rekap-category {
        padding: 13px;
        border: 1px solid #E2E8F0;
        border-top: 4px solid #5A88FF;
        border-radius: 14px;
        background: #FBFDFF;
    }

    .rekap-name {
        color: #334155;
        font-size: 14px;
        font-weight: 900;
    }

    .rekap-percent {
        margin-top: 8px;
        color: #2563EB;
        font-size: 22px;
        font-weight: 900;
    }

    .rekap-progress {
        height: 8px;
        overflow: hidden;
        margin: 8px 0;
        border-radius: 999px;
        background: #E2E8F0;
    }

    .rekap-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #4F7DF3, #79A0FF);
    }

    .rekap-meta {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        color: #64748B;
        font-size: 11px;
        font-weight: 800;
    }

    .verification-action-stack {
        display: flex;
        flex-direction: column;
        gap: 7px;
        min-width: 185px;
    }

    .verification-form {
        margin: 0;
    }

    .verification-btn {
        width: 100%;
        min-height: 34px;
        border: 0;
        border-radius: 8px;
        padding: 7px 10px;
        color: #FFFFFF;
        font-size: 11px;
        font-weight: 900;
    }

    .verification-btn.accept { background: #16A34A; }
    .verification-btn.accept:hover { background: #15803D; }
    .verification-btn.reject { background: #DC2626; }
    .verification-btn.reject:hover { background: #B91C1C; }

    .verification-reject {
        border: 1px solid #FECACA;
        border-radius: 8px;
        background: #FFF7F7;
    }

    .verification-reject summary {
        padding: 7px 10px;
        color: #B91C1C;
        cursor: pointer;
        font-size: 11px;
        font-weight: 900;
    }

    .verification-reject-body {
        padding: 0 10px 10px;
    }

    .verification-note {
        width: 100%;
        min-height: 68px;
        margin-bottom: 7px;
        padding: 7px 8px;
        border: 1px solid #FCA5A5;
        border-radius: 7px;
        resize: vertical;
        font-size: 11px;
    }

    .verification-meta {
        max-width: 210px;
        color: #64748B;
        font-size: 11px;
        line-height: 1.45;
        white-space: normal;
    }

    .empty-state {
        padding: 26px 12px;
        color: #94A3B8;
        font-size: 13px;
        font-weight: 700;
        text-align: center;
    }

    @media (max-width: 1100px) {
        .detail-filter-grid,
        .detail-summary-grid,
        .rekap-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 650px) {
        .detail-filter-grid,
        .detail-summary-grid,
        .rekap-grid {
            grid-template-columns: 1fr;
        }

        .detail-btn-primary,
        .detail-btn-secondary {
            width: 100%;
        }
    }
</style>

<div class="detail-monitoring-page">
    <div class="page-heading">
        Detail Monitoring <span class="muted">Anggota Lab</span>
    </div>

    <div class="detail-card">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="detail-member-name">{{ $anggota->nama_anggota ?? '-' }}</div>
                <div class="detail-member-meta">
                    <span>Lab: {{ $lab->nama_lab ?? '-' }}</span>
                    <span>NIDN: {{ $anggota->nidn ?? '-' }}</span>
                    <span>JAD: {{ $anggota->jad ?? '-' }}</span>
                    <span>Email: {{ $anggota->email ?? '-' }}</span>
                </div>
            </div>

            <a
                href="{{ route('ketualab.monitoring-anggota', ['tahun' => $tahun, 'periode' => $periode, 'triwulan' => $triwulan, 'semester' => $semester]) }}"
                class="detail-btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-section-head">
            <div>
                <div class="detail-title">Filter Periode Detail Anggota</div>
                <p class="detail-subtitle">
                    Detail saat ini: <strong>{{ $labelPeriode }}</strong>
                    | {{ $tanggalMulai->format('d/m/Y') }} - {{ $tanggalSelesai->format('d/m/Y') }}
                </p>
                <span class="detail-period-pill">
                    <i class="bi bi-calendar-range"></i>
                    {{ $periodeInfo }}
                </span>
            </div>
        </div>

        <form method="GET" action="{{ route('ketualab.monitoring-anggota.detail', ['id' => $anggota->id_user]) }}">
            <div class="detail-filter-grid">
                <div>
                    <label class="detail-filter-label" for="periode">Jenis Periode</label>
                    <select name="periode" id="periode" class="form-select detail-filter-control">
                        <option value="tahun" {{ $periode === 'tahun' ? 'selected' : '' }}>Tahunan</option>
                        <option value="triwulan" {{ $periode === 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                        <option value="semester" {{ $periode === 'semester' ? 'selected' : '' }}>Semester</option>
                    </select>
                </div>

                <div>
                    <label class="detail-filter-label" for="tahun">Tahun</label>
                    <select name="tahun" id="tahun" class="form-select detail-filter-control">
                        @foreach($tahunOptions as $itemTahun)
                            <option value="{{ $itemTahun }}" {{ $tahun === (int) $itemTahun ? 'selected' : '' }}>
                                {{ $itemTahun }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="triwulanGroup">
                    <label class="detail-filter-label" for="triwulan">Triwulan</label>
                    <select name="triwulan" id="triwulan" class="form-select detail-filter-control">
                        @for($tw = 1; $tw <= 4; $tw++)
                            <option value="{{ $tw }}" {{ $triwulan === $tw ? 'selected' : '' }}>
                                Triwulan {{ $tw }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div id="semesterGroup">
                    <label class="detail-filter-label" for="semester">Semester</label>
                    <select name="semester" id="semester" class="form-select detail-filter-control">
                        <option value="1" {{ $semester === 1 ? 'selected' : '' }}>Semester 1</option>
                        <option value="2" {{ $semester === 2 ? 'selected' : '' }}>Semester 2</option>
                    </select>
                </div>

                <button type="submit" class="detail-btn-primary">
                    <i class="bi bi-funnel-fill"></i>
                    Terapkan
                </button>
            </div>
        </form>
    </div>

    <div class="detail-summary-grid">
        <div class="detail-summary-card">
            <div class="detail-summary-label">Target Tahunan</div>
            <div class="detail-summary-value primary">{{ $totalTargetTahunan }}</div>
        </div>

        <div class="detail-summary-card">
            <div class="detail-summary-label">Target Periode</div>
            <div class="detail-summary-value primary">{{ $totalTargetPeriode }}</div>
        </div>

        <div class="detail-summary-card">
            <div class="detail-summary-label">Realisasi Disetujui</div>
            <div class="detail-summary-value success">{{ $totalRealisasi }}</div>
        </div>

        <div class="detail-summary-card">
            <div class="detail-summary-label">Sisa Target Periode</div>
            <div class="detail-summary-value warning">{{ $totalSisa }}</div>
        </div>
    </div>

    <div class="detail-table-card">
        <div class="detail-section-head">
            <div>
                <div class="detail-section-title">Rekap Progress per Kategori</div>
                <p class="detail-section-desc">Rekap target dan realisasi anggota untuk {{ $labelPeriode }}.</p>
            </div>

            <span class="detail-period-pill" style="margin-top:0;">
                <i class="bi bi-bar-chart-fill"></i>
                Progress Total: {{ $persentaseTotal }}%
            </span>
        </div>

        @include('partials.periode-saat-ini')
@include('partials.filter-diterapkan')

<div class="rekap-grid">
            @foreach($rekapKategori as $item)
                <div class="rekap-category">
                    <div class="rekap-name">{{ $item['kategori'] }}</div>
                    <div class="rekap-percent">{{ $item['persentase'] }}%</div>
                    <div class="rekap-progress">
                        <div class="rekap-progress-fill" style="width: {{ min((int) $item['persentase'], 100) }}%;"></div>
                    </div>
                    <div class="rekap-meta">
                        <span>Target: {{ $item['target'] }}</span>
                        <span>Realisasi: {{ $item['realisasi'] }}</span>
                        <span>Sisa: {{ $item['sisa'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="detail-table-card">
        <div class="detail-section-head">
            <div>
                <div class="detail-section-title">Detail Target KM Anggota</div>
                <p class="detail-section-desc">
                    Menampilkan target serta realisasi disetujui per triwulan. Kolom target periode dan realisasi periode mengikuti filter aktif.
                </p>
            </div>
        </div>

        <div class="detail-table-wrap">
            <table class="detail-table">
                <thead>
                    <tr>
                        <th rowspan="2">No</th>
                        <th rowspan="2">Kategori</th>
                        <th rowspan="2">Sub Kategori / Jenis KM</th>
                        <th rowspan="2">Keterangan</th>
                        <th colspan="4" class="group">Target KM per Triwulan</th>
                        <th colspan="4" class="group">Realisasi Disetujui per Triwulan</th>
                        <th rowspan="2">Target Periode</th>
                        <th rowspan="2">Realisasi Periode</th>
                        <th rowspan="2">Sisa</th>
                        <th rowspan="2">Status</th>
                    </tr>
                    <tr>
                        @for($tw = 1; $tw <= 4; $tw++)
                            <th>TW {{ $tw }}</th>
                        @endfor
                        @for($tw = 1; $tw <= 4; $tw++)
                            <th>TW {{ $tw }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @forelse($rincianTarget as $index => $row)
                        @php
                            $deadline = [
                                1 => $row->tanggal_selesai_tw1 ?? null,
                                2 => $row->tanggal_selesai_tw2 ?? null,
                                3 => $row->tanggal_selesai_tw3 ?? null,
                                4 => $row->tanggal_selesai_tw4 ?? null,
                            ];
                        @endphp
                        <tr>
                            <td class="center">{{ $index + 1 }}</td>
                            <td class="center">
                                <span class="category-name">{{ $row->kategori_km }}</span>
                            </td>
                            <td>
                                <div class="target-name">{{ $row->sub_kategori_km ?: '-' }}</div>
                                <div class="deadline-list">
                                    @foreach($deadline as $tw => $tanggal)
                                        @if($tanggal)
                                            <span class="deadline-item">
                                                <i class="bi bi-calendar-event"></i>
                                                TW{{ $tw }}: {{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                            <td class="keterangan-text">{{ $row->keterangan ?: '-' }}</td>

                            @for($tw = 1; $tw <= 4; $tw++)
                                <td class="center">
                                    <span class="number-target">{{ (int) ($row->target_tw[$tw] ?? 0) }}</span>
                                </td>
                            @endfor

                            @for($tw = 1; $tw <= 4; $tw++)
                                <td class="center">
                                    <span class="number-realisasi">{{ (int) ($row->realisasi_tw[$tw] ?? 0) }}</span>
                                </td>
                            @endfor

                            <td class="center"><span class="number-target">{{ $row->target_periode }}</span></td>
                            <td class="center"><span class="number-realisasi">{{ $row->realisasi_periode }}</span></td>
                            <td class="center"><span class="number-sisa">{{ $row->sisa }}</span></td>
                            <td class="center">
                                <span class="status-pill status-{{ $row->status_class }}">
                                    {{ $row->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="17">
                                <div class="empty-state">Belum ada target KM untuk anggota ini pada tahun {{ $tahun }}.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="detail-table-card">
        <div class="detail-section-head">
            <div>
                <div class="detail-section-title">Riwayat Aktivitas KM</div>
                <p class="detail-section-desc">Aktivitas anggota pada {{ $labelPeriode }}. Realisasi hanya dihitung setelah Ketua Lab menyetujui aktivitas.</p>
            </div>
        </div>

        <div class="detail-table-wrap">
            <table class="detail-table" style="min-width: 1480px;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kategori</th>
                        <th>Sub Kategori</th>
                        <th>Judul Aktivitas</th>
                        <th>Bukti</th>
                        <th>Status</th>
                        <th>Catatan Verifikasi</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Ditambahkan di Triwulan</th>
                        <th>Pembaruan Terakhir</th>
                        <th>Rincian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayatAktivitas as $index => $aktivitas)
                        @php
                            $ditambahkanTw = '-';
                            if (!empty($aktivitas->created_at)) {
                                try {
                                    $ditambahkanTw = 'Triwulan ' . (int) ceil(\Carbon\Carbon::parse($aktivitas->created_at)->month / 3);
                                } catch (\Throwable $e) {
                                    $ditambahkanTw = '-';
                                }
                            }

                            $statusAktivitas = $aktivitas->status_progress ?? '-';
                            $label = $statusLabel($statusAktivitas);
                            $class = $statusClass($statusAktivitas);
                        @endphp
                        <tr>
                            <td class="center">{{ $index + 1 }}</td>
                            <td>{{ $aktivitas->kategori_km ?: '-' }}</td>
                            <td>{{ $aktivitas->sub_kategori_km ?: '-' }}</td>
                            <td>
                                <div class="target-name">{{ $aktivitas->judul_aktivitas ?: '-' }}</div>
                                @if(!empty($aktivitas->deskripsi_singkat))
                                    <div class="keterangan-text">{{ \Illuminate\Support\Str::limit($aktivitas->deskripsi_singkat, 90) }}</div>
                                @endif
                            </td>
                            <td class="center">
                                @if(!empty($aktivitas->bukti_pdf_path) || !empty($aktivitas->bukti_file_path))
                                    <a href="/bukti-km/{{ $aktivitas->id_aktivitas }}/download" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download me-1"></i> Unduh
                                    </a>
                                @elseif(!empty($aktivitas->bukti_link))
                                    <a href="{{ $aktivitas->bukti_link }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-link-45deg me-1"></i> Buka Link
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="center"><span class="status-pill status-{{ $class }}">{{ $label }}</span></td>
                            <td>
                                @if(!empty($aktivitas->catatan_verifikasi))
                                    <div class="verification-meta">{{ $aktivitas->catatan_verifikasi }}</div>
                                @elseif($statusAktivitas === 'Submitted')
                                    <div class="verification-meta">Belum diverifikasi.</div>
                                @elseif($statusAktivitas === 'Accepted')
                                    <div class="verification-meta">Disetujui{{ !empty($aktivitas->diverifikasi_pada) ? ' pada ' . \Carbon\Carbon::parse($aktivitas->diverifikasi_pada)->format('d/m/Y H:i') : '' }}.</div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="center">{{ $aktivitas->tanggal_mulai ? \Carbon\Carbon::parse($aktivitas->tanggal_mulai)->format('d/m/Y') : '-' }}</td>
                            <td class="center">{{ $aktivitas->tanggal_selesai ? \Carbon\Carbon::parse($aktivitas->tanggal_selesai)->format('d/m/Y') : '-' }}</td>
                            <td class="center">{{ $ditambahkanTw }}</td>
                            <td class="center">{{ $aktivitas->updated_at ? \Carbon\Carbon::parse($aktivitas->updated_at)->format('d/m/Y H:i') : '-' }}</td>
                            <td class="center">
                                <a
                                    href="{{ route('ketualab.aktivitas-km.detail', [
                                        'id' => $aktivitas->id_aktivitas,
                                        'from' => 'monitoring',
                                        'tahun' => $tahun,
                                        'periode' => $periode,
                                        'triwulan' => $triwulan,
                                        'semester' => $semester,
                                    ]) }}"
                                    class="detail-btn-primary"
                                    style="min-height:34px; padding:0 10px; font-size:11px;">
                                    <i class="bi bi-eye me-1"></i>
                                    {{ $statusAktivitas === 'Submitted' ? 'Detail & Verifikasi' : 'Lihat Detail' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12">
                                <div class="empty-state">Belum ada aktivitas KM pada periode ini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const periode = document.getElementById('periode');
        const triwulanGroup = document.getElementById('triwulanGroup');
        const semesterGroup = document.getElementById('semesterGroup');

        function syncPeriodControls() {
            if (!periode || !triwulanGroup || !semesterGroup) {
                return;
            }

            triwulanGroup.style.display = periode.value === 'triwulan' ? 'block' : 'none';
            semesterGroup.style.display = periode.value === 'semester' ? 'block' : 'none';
        }

        if (periode) {
            periode.addEventListener('change', syncPeriodControls);
            syncPeriodControls();
        }
    });
</script>
@endsection
