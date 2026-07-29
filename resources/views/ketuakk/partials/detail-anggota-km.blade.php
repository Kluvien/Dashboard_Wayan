@php
    $periodeColumns = $periodeColumns ?? [1 => 'TW1', 2 => 'TW2', 3 => 'TW3', 4 => 'TW4'];
    $rekap = collect($rekap ?? []);
    $riwayatAssign = collect($riwayatAssign ?? []);
    $aktivitas = collect($aktivitas ?? []);
    $pageTitle = $pageTitle ?? 'Detail KM';
    $pageMuted = $pageMuted ?? 'Anggota KK';
    $detailDescription = $detailDescription ?? 'Detail target, realisasi, dan aktivitas Kontrak Manajemen anggota KK.';
    $detailAction = $detailAction ?? url()->current();
    $backUrl = $backUrl ?? '/ketuakk/km-anggota-kk?tahun=' . $tahun;
@endphp

<style>
    .ketuakk-member-detail__overview { overflow:hidden; border:1px solid #E2E8F0; border-radius:14px; background:#FFF; margin-bottom:16px; }
    .ketuakk-member-detail__header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; padding:20px 22px; border-bottom:1px solid #E2E8F0; flex-wrap:wrap; }
    .ketuakk-member-detail__eyebrow { color:#2563EB; font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
    .ketuakk-member-detail__title { margin:4px 0 0; color:#0F172A; font-size:22px; font-weight:700; }
    .ketuakk-member-detail__description { margin:5px 0 0; color:#64748B; font-size:13px; }
    .ketuakk-member-detail__back { min-height:38px; display:inline-flex; align-items:center; padding:0 13px; border:1px solid #CBD5E1; border-radius:8px; color:#334155; text-decoration:none; font-size:12px; font-weight:700; }
    .ketuakk-member-detail__identity { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); }
    .ketuakk-member-detail__identity-item { padding:13px 18px; border-right:1px solid #EEF2F7; border-bottom:1px solid #EEF2F7; }
    .ketuakk-member-detail__identity-label { display:block; color:#64748B; font-size:11px; }
    .ketuakk-member-detail__identity-value { display:block; margin-top:3px; color:#0F172A; font-size:13px; font-weight:600; overflow-wrap:anywhere; }
    .ketuakk-member-detail__summary { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); margin-bottom:16px; overflow:hidden; border:1px solid #E2E8F0; border-radius:14px; background:#FFF; }
    .ketuakk-member-detail__metric { padding:16px 18px; border-right:1px solid #EEF2F7; }
    .ketuakk-member-detail__metric:last-child { border-right:0; }
    .detail-period-filter {
        display: flex;
        align-items: end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .detail-filter-group {
        min-width: 150px;
        flex: 1 1 160px;
    }

    .detail-filter-group label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 700;
    }

    .detail-filter-submit {
        min-width: 122px;
        height: 42px;
        font-weight: 700;
    }

    .detail-period-control {
        height: 42px;
        border-color: #CBD5E1;
    }

    .km-table th,
    .km-table td,
    .monitoring-detail-table th,
    .monitoring-detail-table td,
    .table-detail-km th,
    .table-detail-km td {
        vertical-align: middle;
        font-size: 13px;
    }

    .km-table th,
    .monitoring-detail-table th,
    .table-detail-km th {
        white-space: nowrap;
        font-weight: 800;
    }

    .monitoring-detail-table {
        width: 100%;
    }

    .monitoring-detail-table th {
        text-align: center;
        background: #F3F6FB;
    }

    .table-detail-km {
        width: 100%;
    }

    .aktivitas-table {
        width: 100%;
    }

    .jad-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        background: #EAF1FF;
        color: #2563EB;
        font-weight: 700;
        font-size: 12px;
    }

    .metric-card-title {
        color: #64748B;
        font-size: 13px;
        margin-bottom: 8px;
    }

    .metric-card-number {
        font-size: 28px;
        font-weight: 800;
        line-height: 1;
    }

    .progress-compact {
        width: 130px;
        min-width: 130px;
    }

    .progress-compact .progress {
        height: 8px;
        border-radius: 999px;
        overflow: hidden;
        background: #E5E7EB;
    }

    .bukti-action-group {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }

    .bukti-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }

    .bukti-download-btn {
        border: 1px solid #BFDBFE;
        background: #477EF7;
        color: #fff;
    }

    .bukti-download-btn:hover {
        background: #2563EB;
        color: #fff;
    }

    .bukti-link-btn {
        border: 1px solid #BBF7D0;
        background: #ECFDF5;
        color: #059669;
    }

    .bukti-link-btn:hover {
        background: #DCFCE7;
        color: #047857;
    }

    @media (max-width: 576px) {
        .ketuakk-member-detail__identity,.ketuakk-member-detail__summary { grid-template-columns:1fr; }
        .detail-period-filter,
        .detail-filter-group,
        .detail-filter-submit {
            width: 100%;
        }
    }
    .ketuakk-member-detail__category-section { margin-bottom: 24px; overflow: hidden; border: 1px solid #D5DCE5; border-radius: 14px; background: #fff; padding: 20px 22px 22px; }
    .ketuakk-member-detail__section-title { margin: 0; color: #1F2937; font-size: 18px; font-weight: 700; }
    .ketuakk-member-detail__section-description { margin: 5px 0 18px; color: #5B6472; font-size: 14px; line-height: 1.5; }
    .ketuakk-member-detail__category-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 18px; }
    .ketuakk-member-detail__category { min-width: 0; border-top: 1px solid #D5DCE5; }
    .ketuakk-member-detail__category header { display: flex; justify-content: space-between; gap: 12px; align-items: center; padding: 12px 0 8px; }
    .ketuakk-member-detail__category header div { display: flex; gap: 8px; align-items: center; }
    .ketuakk-member-detail__category h3 { margin: 0; color: #1F2937; font-size: 15px; font-weight: 700; }
    .ketuakk-member-detail__status { color: #5B6472; font-size: 13px; font-weight: 600; }
    .ketuakk-member-detail__category-metrics { display: grid; grid-template-columns: repeat(5,minmax(0,1fr)); gap: 8px; margin: 0 0 10px; }
    .ketuakk-member-detail__category-metrics dt { color: #5B6472; font-size: 13px; }
    .ketuakk-member-detail__category-metrics dd { margin: 2px 0 0; color: #1F2937; font-size: 14px; font-weight: 700; font-variant-numeric: tabular-nums; }
    .ketuakk-member-detail__progress { height: 6px; overflow: hidden; border-radius: 999px; background: #E5EAF0; margin-bottom: 12px; }
    .ketuakk-member-detail__progress span { display: block; height: 100%; background: #2457A6; }
    .ketuakk-member-detail__period-table { width: 100%; border-collapse: collapse; color: #374151; font-size: 14px; }
    .ketuakk-member-detail__period-table th,
    .ketuakk-member-detail__period-table td { padding: 9px 12px; border-bottom: 1px solid #E5EAF0; }
    .ketuakk-member-detail__period-table thead th { background: #EEF2F6; font-size: 13px; font-weight: 700; text-align: left; }
    .ketuakk-member-detail__period-table td { text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; }
    @media (max-width: 900px) { .ketuakk-member-detail__category-grid { grid-template-columns: 1fr; } }
    @media (max-width: 640px) {
        .ketuakk-member-detail__category-section { padding: 16px; }
        .ketuakk-member-detail__category-metrics { grid-template-columns: repeat(2,minmax(0,1fr)); }
    }
</style>

<section class="ketuakk-member-detail__overview" aria-labelledby="member-detail-title">
    <header class="ketuakk-member-detail__header">
        <div>
            <div class="ketuakk-member-detail__eyebrow">{{ $pageTitle }} {{ $pageMuted }}</div>
            <h1 id="member-detail-title" class="ketuakk-member-detail__title">{{ $anggota->nama_dosen ?? $anggota->username }}</h1>
            <p class="ketuakk-member-detail__description">{{ $detailDescription }} Tahun {{ $tahun }}.</p>
        </div>

        <a href="{{ $backUrl }}" class="ketuakk-member-detail__back">
            Kembali
        </a>
    </header>
    <div class="ketuakk-member-detail__identity">
        @foreach([
            'Nama Anggota' => ($anggota->nama_dosen ?? $anggota->username),
            'NIDN' => ($anggota->nidn ?? '-'),
            'JAD' => ($anggota->jad ?? '-'),
            'Email' => ($anggota->email ?? '-'),
            'Lab Riset' => ($anggota->nama_lab ?? '-'),
            'Role' => ($anggota->role ?? 'Anggota'),
        ] as $identityLabel => $identityValue)
            <div class="ketuakk-member-detail__identity-item">
                <span class="ketuakk-member-detail__identity-label">{{ $identityLabel }}</span>
                <span class="ketuakk-member-detail__identity-value">{{ $identityValue }}</span>
            </div>
        @endforeach
    </div>
</section>

<div class="card mb-4">
    <div class="mb-3">
        <h4 class="fw-bold mb-1">Filter Periode Detail Anggota</h4>
        <p class="text-muted mb-0">
            Detail saat ini: <strong>{{ $labelPeriode }}</strong> |
            {{ $tanggalMulai->format('d/m/Y') }} - {{ $tanggalSelesai->format('d/m/Y') }}
        </p>
    </div>

    <form action="{{ $detailAction }}" method="GET" class="detail-period-filter" id="detailPeriodeForm">
        <div class="detail-filter-group">
            <label for="periode">Jenis Periode</label>
            <select name="periode" id="periode" class="form-select detail-period-control">
                <option value="tahun" {{ $periode === 'tahun' ? 'selected' : '' }}>Tahunan</option>
                <option value="triwulan" {{ $periode === 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                <option value="semester" {{ $periode === 'semester' ? 'selected' : '' }}>Semester</option>
            </select>
        </div>

        <div class="detail-filter-group">
            <label for="tahun">Tahun</label>
            <select name="tahun" id="tahun" class="form-select detail-period-control">
                @foreach($tahunOptions as $itemTahun)
                    <option value="{{ $itemTahun }}" {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                        {{ $itemTahun }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="detail-filter-group {{ $periode === 'triwulan' ? '' : 'd-none' }}" id="triwulanWrapper">
            <label for="triwulan">Triwulan</label>
            <select name="triwulan" id="triwulan" class="form-select detail-period-control">
                <option value="1" {{ $triwulan === 1 ? 'selected' : '' }}>Triwulan 1</option>
                <option value="2" {{ $triwulan === 2 ? 'selected' : '' }}>Triwulan 2</option>
                <option value="3" {{ $triwulan === 3 ? 'selected' : '' }}>Triwulan 3</option>
                <option value="4" {{ $triwulan === 4 ? 'selected' : '' }}>Triwulan 4</option>
            </select>
        </div>

        <div class="detail-filter-group {{ $periode === 'semester' ? '' : 'd-none' }}" id="semesterWrapper">
            <label for="semester">Semester</label>
            <select name="semester" id="semester" class="form-select detail-period-control">
                <option value="1" {{ $semester === 1 ? 'selected' : '' }}>Semester 1</option>
                <option value="2" {{ $semester === 2 ? 'selected' : '' }}>Semester 2</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary detail-filter-submit">
            <i class="bi bi-funnel-fill me-1"></i> Terapkan
        </button>
    </form>
</div>

<div class="ketuakk-member-detail__summary">
    <div class="ketuakk-member-detail__metric">
            <div class="metric-card-title">Target Tahunan</div>
            <div class="metric-card-number text-primary">{{ $totalTargetTahunan }}</div>
    </div>

    <div class="ketuakk-member-detail__metric">
            <div class="metric-card-title">Target Periode</div>
            <div class="metric-card-number text-primary">{{ $totalTargetPeriode }}</div>
    </div>

    <div class="ketuakk-member-detail__metric">
            <div class="metric-card-title">Realisasi Accepted</div>
            <div class="metric-card-number text-success">{{ $totalRealisasi }}</div>
    </div>

    <div class="ketuakk-member-detail__metric">
            <div class="metric-card-title">Progress Periode</div>
            <div class="metric-card-number">{{ min($persentaseTotal, 100) }}%</div>
    </div>
</div>

<section class="ketuakk-member-detail__category-section">
    <h2 class="ketuakk-member-detail__section-title">Rekap Progress per Kategori</h2>
    <p class="ketuakk-member-detail__section-description">
        Target dan realisasi ditampilkan per {{ $periode === 'semester' ? 'semester' : 'triwulan' }}.
        Nilai “Target Periode” dan “Realisasi Periode” mengikuti filter aktif.
    </p>

    <div class="ketuakk-member-detail__category-grid">
        @forelse($rekap as $index => $item)
                    @php
                        $status = $item['status'] ?? 'Belum Mulai';
                        $progress = min((int) ($item['persentase'] ?? 0), 100);
                    @endphp
            <article class="ketuakk-member-detail__category">
                <header><div><span>{{ $index + 1 }}</span><h3>{{ $item['kategori'] }}</h3></div><span class="ketuakk-member-detail__status">{{ $status }}</span></header>
                <dl class="ketuakk-member-detail__category-metrics">
                    <div><dt>Target tahunan</dt><dd>{{ $item['target_tahunan'] }}</dd></div>
                    <div><dt>Target periode</dt><dd>{{ $item['target_periode'] }}</dd></div>
                    <div><dt>Realisasi</dt><dd>{{ $item['realisasi'] }}</dd></div>
                    <div><dt>Sisa</dt><dd>{{ $item['sisa'] }}</dd></div>
                    <div><dt>Progress</dt><dd>{{ $progress }}%</dd></div>
                </dl>
                <div class="ketuakk-member-detail__progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}" aria-label="Progress kategori {{ $item['kategori'] }} {{ $progress }} persen"><span style="width: {{ $progress }}%"></span></div>
                <table class="ketuakk-member-detail__period-table">
                    <thead><tr><th scope="col">Periode</th><th scope="col">Target</th><th scope="col">Realisasi</th></tr></thead>
                    <tbody>
                        @foreach($periodeColumns as $key => $label)
                            <tr><th scope="row">{{ $label }}</th><td>{{ $item['target_periode_detail'][$key] ?? 0 }}</td><td>{{ $item['realisasi_periode_detail'][$key] ?? 0 }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </article>
                @empty
                    <div class="ketuakk-member-detail__empty">Belum ada data rekap.</div>
                @endforelse
    </div>
</section>

<div class="card mb-4">
    <h4 class="fw-bold mb-1">Riwayat KM yang Diberikan</h4>
    <p class="text-muted mb-3">
        Menampilkan rincian KM yang telah dibagikan kepada anggota ini pada tahun {{ $tahun }}.
    </p>

    <div class="table-responsive">
        <table class="table align-middle mb-0 table-detail-km">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori KM</th>
                    <th>Sub Kategori / Jenis KM</th>
                    <th>Keterangan</th>
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
                        <td class="fw-bold">{{ $assign->kategori_km ?? '-' }}</td>
                        <td>{{ $assign->sub_kategori_km ?? '-' }}</td>
                        <td>{{ $assign->keterangan ?? '-' }}</td>
                        <td class="fw-bold">{{ $assign->jumlah_km ?? 0 }}</td>
                        <td>{{ $assign->tahun_km ?? '-' }}</td>
                        <td>
                            @if(($assign->status_km ?? '') === 'Aktif')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>{{ !empty($assign->tanggal_assign) ? \Carbon\Carbon::parse($assign->tanggal_assign)->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Belum ada KM yang diberikan ke anggota ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-1">Riwayat Aktivitas KM</h4>
    <p class="text-muted mb-3">
        Menampilkan riwayat aktivitas anggota pada tahun {{ $tahun }}.
    </p>

    <div class="table-responsive">
        <table class="table align-middle mb-0 aktivitas-table">
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
                @forelse($aktivitas as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $item->kategori_km ?? '-' }}</td>
                        <td>{{ $item->sub_kategori_km ?? '-' }}</td>
                        <td class="fw-bold">{{ $item->judul_aktivitas ?? '-' }}</td>
                        <td>{{ $item->deskripsi_singkat ?? '-' }}</td>
                        <td>{{ !empty($item->tanggal_mulai) ? \Carbon\Carbon::parse($item->tanggal_mulai)->format('Y-m-d') : '-' }}</td>
                        <td>{{ !empty($item->tanggal_selesai) ? \Carbon\Carbon::parse($item->tanggal_selesai)->format('Y-m-d') : '-' }}</td>
                        <td>
                            @php $statusAktivitas = $item->status_progress ?? '-'; @endphp

                            @if($statusAktivitas === 'Accepted')
                                <span class="badge bg-success">Accepted</span>
                            @elseif($statusAktivitas === 'Rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @elseif($statusAktivitas === 'Submitted')
                                <span class="badge bg-primary">Submitted</span>
                            @elseif($statusAktivitas === 'On Progress')
                                <span class="badge bg-warning text-dark">On Progress</span>
                            @else
                                <span class="badge bg-secondary">{{ $statusAktivitas }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="bukti-action-group">
                                @if(!empty($item->bukti_pdf_path) || !empty($item->bukti_file_path))
                                    <a href="{{ route('bukti-km.download', $item->id_aktivitas) }}" class="bukti-action-btn bukti-download-btn">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                @endif

                                @if(!empty($item->bukti_link))
                                    <a href="{{ $item->bukti_link }}" target="_blank" rel="noopener" class="bukti-action-btn bukti-link-btn">
                                        <i class="bi bi-box-arrow-up-right"></i> Lihat
                                    </a>
                                @endif

                                @if(empty($item->bukti_pdf_path) && empty($item->bukti_file_path) && empty($item->bukti_link))
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            Belum ada aktivitas KM untuk anggota ini pada periode tersebut.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const periode = document.getElementById('periode');
        const triwulanWrapper = document.getElementById('triwulanWrapper');
        const semesterWrapper = document.getElementById('semesterWrapper');

        if (!periode || !triwulanWrapper || !semesterWrapper) {
            return;
        }

        function setVisibility() {
            triwulanWrapper.classList.toggle('d-none', periode.value !== 'triwulan');
            semesterWrapper.classList.toggle('d-none', periode.value !== 'semester');
        }

        periode.addEventListener('change', setVisibility);
        setVisibility();
    });
</script>
