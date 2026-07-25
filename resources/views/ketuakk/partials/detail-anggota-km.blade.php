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
        min-width: 1420px;
    }

    .monitoring-detail-table th {
        text-align: center;
        background: #F3F6FB;
    }

    .table-detail-km {
        min-width: 1080px;
    }

    .aktivitas-table {
        min-width: 1160px;
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
        .detail-period-filter,
        .detail-filter-group,
        .detail-filter-submit {
            width: 100%;
        }
    }
</style>

<div class="page-heading">
    {{ $pageTitle }} <span class="muted">{{ $pageMuted }}</span>
</div>

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">{{ $anggota->nama_dosen ?? $anggota->username }}</h4>
            <p class="text-muted mb-0">
                {{ $detailDescription }} Tahun {{ $tahun }}.
            </p>
        </div>

        <a href="{{ $backUrl }}" class="btn btn-secondary">
            Kembali
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table">
            <tbody>
                <tr>
                    <th style="width: 220px;">Nama Anggota</th>
                    <td>{{ $anggota->nama_dosen ?? $anggota->username }}</td>
                </tr>
                <tr>
                    <th>NIDN</th>
                    <td>{{ $anggota->nidn ?? '-' }}</td>
                </tr>
                <tr>
                    <th>JAD</th>
                    <td>
                        <span class="jad-badge">{{ $anggota->jad ?? '-' }}</span>
                    </td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $anggota->email ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Lab Riset</th>
                    <td>{{ $anggota->nama_lab ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td>{{ $anggota->role ?? 'Anggota' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

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

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="metric-card-title">Target Tahunan</div>
            <div class="metric-card-number text-primary">{{ $totalTargetTahunan }}</div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="metric-card-title">Target Periode</div>
            <div class="metric-card-number text-primary">{{ $totalTargetPeriode }}</div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="metric-card-title">Realisasi Accepted</div>
            <div class="metric-card-number text-success">{{ $totalRealisasi }}</div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="metric-card-title">Progress Periode</div>
            <div class="metric-card-number">{{ min($persentaseTotal, 100) }}%</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-1">Rekap Progress per Kategori</h4>
    <p class="text-muted mb-4">
        Target dan realisasi ditampilkan per {{ $periode === 'semester' ? 'semester' : 'triwulan' }}.
        Nilai “Target Periode” dan “Realisasi Periode” mengikuti filter aktif.
    </p>

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0 monitoring-detail-table">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Kategori KM</th>
                    <th colspan="{{ count($periodeColumns) }}">Target per {{ $periode === 'semester' ? 'Semester' : 'Triwulan' }}</th>
                    <th colspan="{{ count($periodeColumns) }}">Realisasi Accepted per {{ $periode === 'semester' ? 'Semester' : 'Triwulan' }}</th>
                    <th rowspan="2">Target Tahunan</th>
                    <th rowspan="2">Target Periode</th>
                    <th rowspan="2">Realisasi Periode</th>
                    <th rowspan="2">Sisa</th>
                    <th rowspan="2">Progress</th>
                    <th rowspan="2">Status</th>
                </tr>
                <tr>
                    @foreach($periodeColumns as $label)
                        <th>{{ $label }}</th>
                    @endforeach

                    @foreach($periodeColumns as $label)
                        <th>{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @forelse($rekap as $index => $item)
                    @php
                        $status = $item['status'] ?? 'Belum Mulai';
                        $progress = min((int) ($item['persentase'] ?? 0), 100);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $item['kategori'] }}</td>

                        @foreach($periodeColumns as $key => $label)
                            <td class="text-center text-primary fw-bold">{{ $item['target_periode_detail'][$key] ?? 0 }}</td>
                        @endforeach

                        @foreach($periodeColumns as $key => $label)
                            <td class="text-center text-success fw-bold">{{ $item['realisasi_periode_detail'][$key] ?? 0 }}</td>
                        @endforeach

                        <td class="text-center">{{ $item['target_tahunan'] }}</td>
                        <td class="text-center fw-bold">{{ $item['target_periode'] }}</td>
                        <td class="text-center text-success fw-bold">{{ $item['realisasi'] }}</td>
                        <td class="text-center {{ $item['sisa'] > 0 ? 'text-warning' : 'text-success' }} fw-bold">{{ $item['sisa'] }}</td>

                        <td>
                            <div class="progress-compact">
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ $progress }}%;"></div>
                                </div>
                                <div class="small text-muted mt-1">{{ $progress }}%</div>
                            </div>
                        </td>

                        <td class="text-center">
                            @if($status === 'Tercapai')
                                <span class="badge bg-success">Tercapai</span>
                            @elseif($status === 'On Progress')
                                <span class="badge bg-warning text-dark">On Progress</span>
                            @elseif($status === 'Belum Ada Target')
                                <span class="badge bg-secondary">Belum Ada Target</span>
                            @else
                                <span class="badge bg-danger">Belum Mulai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 8 + (count($periodeColumns) * 2) }}" class="text-center text-muted py-4">
                            Belum ada data rekap.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

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
