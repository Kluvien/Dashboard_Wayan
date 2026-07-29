@extends('layouts.app')

@section('title', 'Laporan Anggota')

@section('content')
@php
    $filters = $filters ?? [];
    $summary = $summary ?? [];
    $rekapKategori = collect($rekapKategori ?? []);
    $detailTargetKategori = collect($detailTargetKategori ?? []);
    $aktivitasRows = collect($aktivitasRows ?? []);
    $tahunOptions = collect($tahunOptions ?? [now()->year]);

    $modePeriode = $filters['mode_periode'] ?? 'tahun';
    $periodeNilai = $filters['periode_nilai'] ?? 1;

    $statusClass = function (?string $status): string {
        return match ($status) {
            'Tercapai' => 'status-success',
            'On Progress' => 'status-warning',
            'Belum Mulai' => 'status-danger',
            default => 'status-secondary',
        };
    };
@endphp

<style>
    .report-filter-card {
        border: 1px solid #DDE5F0;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
    }

    .report-filter-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 16px;
    }

    .filter-col-3 { grid-column: span 3; }
    .filter-col-4 { grid-column: span 4; }
    .filter-col-6 { grid-column: span 6; }
    .filter-col-12 { grid-column: span 12; }

    .report-filter-section {
        padding: 16px;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        background: #F8FAFD;
    }

    .report-filter-section-title {
        margin-bottom: 12px;
        color: #1E3A8A;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: .3px;
        text-transform: uppercase;
    }

    .report-filter-help {
        margin-top: 6px;
        color: #64748B;
        font-size: 12px;
    }

    .download-panel {
        padding: 16px;
        border: 1px solid #BFDBFE;
        border-radius: 14px;
        background: linear-gradient(135deg, #EFF6FF 0%, #F8FAFC 100%);
    }

    .download-panel-title {
        margin-bottom: 4px;
        color: #1E3A8A;
        font-size: 15px;
        font-weight: 800;
    }

    .download-panel-text {
        margin-bottom: 12px;
        color: #475569;
        font-size: 12px;
    }

    .report-card-value {
        margin-bottom: 0;
        color: #0F172A;
        font-size: 28px;
        font-weight: 800;
    }

    .report-stat-card {
        min-height: 116px;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        background: #FFF;
    }

    .report-stat-label {
        margin-bottom: 8px;
        color: #64748B;
        font-size: 13px;
    }

    .report-table th {
        white-space: nowrap;
        color: #334155;
        font-size: 12px;
        font-weight: 800;
    }

    .report-table td {
        vertical-align: middle;
        font-size: 12px;
    }

    .detail-category-card {
        overflow: hidden;
        border: 1px solid #DCE6F5;
        border-radius: 15px;
        background: #FFF;
    }

    .detail-category-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 14px 16px;
        border-bottom: 1px solid #DCE6F5;
        background: #F6F9FF;
    }

    .detail-category-title {
        margin: 0;
        color: #163B73;
        font-size: 16px;
        font-weight: 800;
    }

    .detail-category-meta {
        margin-top: 4px;
        color: #64748B;
        font-size: 12px;
    }

    .detail-progress {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 86px;
        padding: 6px 10px;
        border: 1px solid #BFDBFE;
        border-radius: 999px;
        background: #EFF6FF;
        color: #2563EB;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .table-group-title {
        background: #EDF4FF;
        color: #1E3A8A;
        text-align: center;
    }

    .deadline-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin: 2px 0;
        padding: 4px 7px;
        border: 1px solid #DBEAFE;
        border-radius: 8px;
        background: #F8FBFF;
        color: #2563EB;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-success { background: #DCFCE7; color: #15803D; }
    .status-warning { background: #FEF3C7; color: #B45309; }
    .status-danger { background: #FEE2E2; color: #DC2626; }
    .status-secondary { background: #E2E8F0; color: #475569; }

    .empty-row {
        padding: 28px 16px !important;
        color: #64748B;
        text-align: center;
    }

    @media (max-width: 991.98px) {
        .filter-col-3,
        .filter-col-4,
        .filter-col-6 {
            grid-column: span 12;
        }
    }
    .anggota-laporan__target-records{border-top:1px solid #D5DCE5}.anggota-laporan__target-record{padding:14px 16px 18px;border-bottom:1px solid #E5EAF0}.anggota-laporan__target-record header{display:flex;justify-content:space-between;gap:16px}.anggota-laporan__target-record h6{margin:0;color:#1F2937;font-size:15px}.anggota-laporan__target-record header p{margin:3px 0 0;color:#5B6472;font-size:14px}.anggota-laporan__target-record dl{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin:12px 0}.anggota-laporan__target-record dt{color:#5B6472;font-size:13px}.anggota-laporan__target-record dd{margin:2px 0 0;color:#1F2937;font-size:14px;font-weight:700;font-variant-numeric:tabular-nums}.anggota-laporan__progress{height:6px;overflow:hidden;border-radius:999px;background:#E5EAF0;margin-bottom:12px}.anggota-laporan__progress span{display:block;height:100%;background:#2457A6}.anggota-laporan__period-table{width:100%;border-collapse:collapse;color:#374151;font-size:14px}.anggota-laporan__period-table th,.anggota-laporan__period-table td{padding:9px 12px;border-bottom:1px solid #E5EAF0}.anggota-laporan__period-table thead th{background:#EEF2F6;font-size:13px;text-align:left}.anggota-laporan__period-table td:nth-child(2),.anggota-laporan__period-table td:nth-child(3){text-align:right;font-weight:700;font-variant-numeric:tabular-nums}@media(max-width:640px){.anggota-laporan__target-record header{flex-direction:column}.anggota-laporan__target-record dl{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>

<section class="card report-filter-card mb-4" aria-labelledby="anggota-report-title">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-4">
        <div>
            <p class="mb-1 text-primary fw-bold">Laporan Anggota</p>
            <h1 id="anggota-report-title" class="fw-bold mb-1 fs-4">Pusat Laporan KM Pribadi</h1>
            <p class="text-muted mb-0">
                Periode aktif: <strong>{{ $filters['label_periode'] ?? '-' }}</strong>
            </p>
        </div>

        <a href="/anggota/dashboard" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

    <form method="GET" action="{{ route('anggota.laporan.index') }}" id="reportFilterForm">
        <div class="report-filter-grid">
            <div class="filter-col-6">
                <div class="report-filter-section h-100">
                    <div class="report-filter-section-title">Ruang Lingkup Laporan</div>

                    <label class="form-label fw-semibold" for="reportScope">Jenis Laporan</label>
                    <select id="reportScope" class="form-select" disabled>
                        <option selected>Laporan Pribadi Saya</option>
                    </select>

                    <div class="report-filter-help">
                        Laporan hanya memuat target KM dan aktivitas milik akun Anggota yang sedang login.
                    </div>
                </div>
            </div>

            <div class="filter-col-6">
                <div class="report-filter-section h-100">
                    <div class="report-filter-section-title">Periode Laporan</div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="tahun">Tahun</label>
                            <select id="tahun" name="tahun" class="form-select">
                                @foreach($tahunOptions as $itemTahun)
                                    <option value="{{ $itemTahun }}" {{ (int) ($filters['tahun'] ?? now()->year) === (int) $itemTahun ? 'selected' : '' }}>
                                        {{ $itemTahun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="mode_periode">Format Waktu</label>
                            <select id="mode_periode" name="mode_periode" class="form-select">
                                <option value="tahun" {{ $modePeriode === 'tahun' ? 'selected' : '' }}>Dalam 1 Tahun</option>
                                <option value="semester" {{ $modePeriode === 'semester' ? 'selected' : '' }}>Per Semester</option>
                                <option value="triwulan" {{ $modePeriode === 'triwulan' ? 'selected' : '' }}>Per Triwulan</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="semesterGroup" style="{{ $modePeriode === 'semester' ? '' : 'display:none;' }}">
                            <label class="form-label fw-semibold" for="semesterValue">Pilih Semester</label>
                            <select id="semesterValue" class="form-select">
                                <option value="1" {{ (int) $periodeNilai === 1 ? 'selected' : '' }}>Semester 1</option>
                                <option value="2" {{ (int) $periodeNilai === 2 ? 'selected' : '' }}>Semester 2</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="triwulanGroup" style="{{ $modePeriode === 'triwulan' ? '' : 'display:none;' }}">
                            <label class="form-label fw-semibold" for="triwulanValue">Pilih Triwulan</label>
                            <select id="triwulanValue" class="form-select">
                                @for($tw = 1; $tw <= 4; $tw++)
                                    <option value="{{ $tw }}" {{ (int) $periodeNilai === $tw ? 'selected' : '' }}>Triwulan {{ $tw }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <input type="hidden" id="periode_nilai" name="periode_nilai" value="{{ $periodeNilai }}">
                </div>
            </div>

            <div class="filter-col-12 d-flex align-items-center justify-content-between gap-3 flex-wrap">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-eye me-1"></i>
                    Tampilkan Laporan
                </button>

                <div class="download-panel flex-grow-1" style="min-width: 280px;">
                    <div class="download-panel-title">Unduh Laporan</div>
                    <div class="download-panel-text">
                        PDF mengikuti periode yang dipilih. Excel memuat beberapa sheet, sedangkan CSV diunduh sebagai ZIP berisi beberapa file CSV.
                    </div>

                    @php
                        $downloadQuery = [
                            'tahun' => $filters['tahun'] ?? now()->year,
                            'mode_periode' => $filters['mode_periode'] ?? 'tahun',
                            'periode_nilai' => $filters['periode_nilai'],
                        ];
                    @endphp

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('anggota.laporan.download', array_merge($downloadQuery, ['format' => 'pdf'])) }}" class="btn btn-danger btn-sm">
                            <i class="bi bi-file-earmark-pdf me-1"></i>
                            Download PDF
                        </a>

                        <a href="{{ route('anggota.laporan.download', array_merge($downloadQuery, ['format' => 'xlsx'])) }}" class="btn btn-success btn-sm">
                            <i class="bi bi-file-earmark-excel me-1"></i>
                            Download Excel
                        </a>

                        <a href="{{ route('anggota.laporan.download', array_merge($downloadQuery, ['format' => 'csv'])) }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-file-earmark-zip me-1"></i>
                            Download CSV ZIP
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card report-stat-card p-3 h-100">
            <div class="report-stat-label">Jumlah Target Aktif</div>
            <div class="report-card-value text-primary">{{ $summary['jumlah_target_aktif'] ?? 0 }}</div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card report-stat-card p-3 h-100">
            <div class="report-stat-label">Jumlah Kategori Aktif</div>
            <div class="report-card-value">{{ $summary['jumlah_kategori_aktif'] ?? 0 }}</div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card report-stat-card p-3 h-100">
            <div class="report-stat-label">Total Target Periode</div>
            <div class="report-card-value text-primary">{{ $summary['total_target'] ?? 0 }}</div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card report-stat-card p-3 h-100">
            <div class="report-stat-label">Total Realisasi</div>
            <div class="report-card-value text-success">{{ $summary['total_realisasi'] ?? 0 }}</div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card report-stat-card p-3 h-100">
            <div class="report-stat-label">Sisa Target</div>
            <div class="report-card-value text-warning">{{ $summary['total_sisa'] ?? 0 }}</div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card report-stat-card p-3 h-100">
            <div class="report-stat-label">Persentase Capaian</div>
            <div class="report-card-value">{{ $summary['persentase'] ?? 0 }}%</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="p-3 pb-0">
        <h4 class="fw-bold mb-1">Identitas Laporan</h4>
        <p class="text-muted mb-0">Data anggota yang menjadi dasar laporan pribadi.</p>
    </div>

    <div class="table-responsive p-3">
        <table class="table report-table mb-0">
            <tbody>
                <tr>
                    <th style="width: 220px;">Nama Anggota</th>
                    <td>{{ $summary['nama_anggota'] ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Lab Riset</th>
                    <td>{{ $summary['nama_lab'] ?? '-' }}</td>
                </tr>
                <tr>
                    <th>NIDN</th>
                    <td>{{ $summary['nidn'] ?? '-' }}</td>
                </tr>
                <tr>
                    <th>JAD</th>
                    <td>{{ $summary['jad'] ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Periode</th>
                    <td>{{ $filters['label_periode'] ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <div class="p-3 pb-0">
        <h4 class="fw-bold mb-1">Rekap Kategori KM</h4>
        <p class="text-muted mb-0">Ringkasan target dan realisasi KM pribadi berdasarkan kategori pada periode laporan.</p>
    </div>

    <div class="table-responsive p-3">
        <table class="table report-table align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori KM</th>
                    <th>Jumlah Target</th>
                    <th>Target Tahunan</th>
                    <th>Target Periode</th>
                    <th>Realisasi</th>
                    <th>Sisa</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekapKategori as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-semibold">{{ $item['kategori'] ?? '-' }}</td>
                        <td>{{ $item['jumlah_target'] ?? 0 }}</td>
                        <td>{{ $item['target_tahunan'] ?? 0 }}</td>
                        <td>{{ $item['target_periode'] ?? 0 }}</td>
                        <td class="text-success fw-semibold">{{ $item['realisasi_periode'] ?? 0 }}</td>
                        <td class="text-warning fw-semibold">{{ $item['sisa'] ?? 0 }}</td>
                        <td>{{ $item['persentase'] ?? 0 }}%</td>
                        <td><span class="status-badge {{ $statusClass($item['status'] ?? '') }}">{{ $item['status'] ?? '-' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="empty-row">Belum ada data kategori KM.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mb-4">
    <h4 class="fw-bold mb-1">Rekap Detail Target KM per Kategori</h4>
    <p class="text-muted mb-3">Rincian sub kategori, pembagian target per triwulan, realisasi Accepted, tenggat, serta status capaian pribadi.</p>

    @foreach($detailTargetKategori as $kategori)
        <div class="detail-category-card mb-3">
            <div class="detail-category-header">
                <div>
                    <h5 class="detail-category-title">{{ $kategori['kategori'] ?? '-' }}</h5>
                    <div class="detail-category-meta">
                        {{ $kategori['jumlah_sub_kategori'] ?? 0 }} target KM · Target periode: {{ $kategori['target_periode'] ?? 0 }} · Realisasi: {{ $kategori['realisasi_periode'] ?? 0 }}
                    </div>
                </div>
                <span class="detail-progress">Progress {{ $kategori['persentase'] ?? 0 }}%</span>
            </div>

            <div class="anggota-laporan__target-records">
                        @forelse($kategori['rows'] ?? [] as $row)
                            @php $rowProgress=min(max((float)($row['persentase']??0),0),100); @endphp
                            <article class="anggota-laporan__target-record"><header><div><span>{{ $row['no']??'-' }}</span><h6>{{ $row['sub_kategori']??'-' }}</h6><p>{{ $row['keterangan']??'-' }}</p></div><span class="status-badge {{ $statusClass($row['status']??'') }}">{{ $row['status']??'-' }}</span></header><dl><div><dt>Target tahunan</dt><dd>{{ $row['target_total_tahunan']??0 }}</dd></div><div><dt>Target periode</dt><dd>{{ $row['target_periode']??0 }}</dd></div><div><dt>Realisasi tahunan</dt><dd>{{ $row['realisasi_total_tahunan']??0 }}</dd></div><div><dt>Realisasi periode</dt><dd>{{ $row['realisasi_periode']??0 }}</dd></div><div><dt>Sisa</dt><dd>{{ $row['sisa']??0 }}</dd></div><div><dt>Progress</dt><dd>{{ $row['persentase']??0 }}%</dd></div></dl><div class="anggota-laporan__progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $rowProgress }}" aria-label="Progress {{ $row['sub_kategori']??'target' }} {{ $row['persentase']??0 }} persen"><span style="width:{{ $rowProgress }}%"></span></div><table class="anggota-laporan__period-table"><thead><tr><th scope="col">Periode</th><th scope="col">Target</th><th scope="col">Realisasi</th><th scope="col">Tenggat</th></tr></thead><tbody>@for($tw=1;$tw<=4;$tw++)<tr><th scope="row">TW{{ $tw }}</th><td>{{ $row['target_tw'.$tw]??0 }}</td><td>{{ $row['realisasi_tw'.$tw]??0 }}</td><td>{{ $row['tanggal_mulai_tw'.$tw]??'-' }} — {{ $row['tanggal_selesai_tw'.$tw]??'-' }}</td></tr>@endfor</tbody></table></article>
                        @empty
                            <div class="empty-row">Belum ada target KM pada kategori ini.</div>
                        @endforelse
            </div>
        </div>
    @endforeach
</div>

<div class="card mb-4">
    <div class="p-3 pb-0">
        <h4 class="fw-bold mb-1">Riwayat Aktivitas KM</h4>
        <p class="text-muted mb-0">Aktivitas yang dibuat pada periode laporan, termasuk status prosesnya.</p>
    </div>

    <div class="table-responsive p-3">
        <table class="table report-table align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori</th>
                    <th>Sub Kategori</th>
                    <th>Judul Aktivitas</th>
                    <th>Deskripsi</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Selesai</th>
                    <th>Status</th>
                    <th>Bukti</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aktivitasRows as $row)
                    <tr>
                        <td>{{ $row['no'] ?? '-' }}</td>
                        <td>{{ $row['kategori_km'] ?? '-' }}</td>
                        <td>{{ $row['sub_kategori_km'] ?? '-' }}</td>
                        <td class="fw-semibold">{{ $row['judul_aktivitas'] ?? '-' }}</td>
                        <td>{{ $row['deskripsi_singkat'] ?? '-' }}</td>
                        <td>{{ $row['tanggal_mulai'] ?? '-' }}</td>
                        <td>{{ $row['tanggal_selesai'] ?? '-' }}</td>
                        <td><span class="status-badge {{ $statusClass(($row['status_progress'] ?? '') === 'Accepted' ? 'Tercapai' : (($row['status_progress'] ?? '') === 'On Progress' ? 'On Progress' : 'Belum Mulai')) }}">{{ $row['status_progress'] ?? '-' }}</span></td>
                        <td>
                            @if(($row['bukti'] ?? '-') !== '-')
                                <span class="text-primary"><i class="bi bi-paperclip me-1"></i>Tersedia</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="empty-row">Belum ada aktivitas KM pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    (function () {
        const mode = document.getElementById('mode_periode');
        const semesterGroup = document.getElementById('semesterGroup');
        const triwulanGroup = document.getElementById('triwulanGroup');
        const semesterValue = document.getElementById('semesterValue');
        const triwulanValue = document.getElementById('triwulanValue');
        const periodeValue = document.getElementById('periode_nilai');
        const form = document.getElementById('reportFilterForm');

        function syncMode() {
            const current = mode.value;
            semesterGroup.style.display = current === 'semester' ? '' : 'none';
            triwulanGroup.style.display = current === 'triwulan' ? '' : 'none';

            if (current === 'semester') {
                periodeValue.value = semesterValue.value;
            } else if (current === 'triwulan') {
                periodeValue.value = triwulanValue.value;
            } else {
                periodeValue.value = '';
            }
        }

        mode.addEventListener('change', syncMode);
        semesterValue.addEventListener('change', syncMode);
        triwulanValue.addEventListener('change', syncMode);
        form.addEventListener('submit', syncMode);
        syncMode();
    })();
</script>
@endsection
