<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Ketua KK</title>
    <style>
        @page {
            margin: 18px 22px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1E293B;
            font-size: 9px;
            line-height: 1.35;
        }

        h1 {
            font-size: 17px;
            margin: 0 0 3px 0;
            color: #173D77;
        }

        h2 {
            font-size: 12px;
            margin: 18px 0 7px 0;
            color: #173D77;
        }

        h3 {
            font-size: 10px;
            margin: 0;
            color: #1E3A8A;
        }

        .muted {
            color: #64748B;
            font-size: 9px;
        }

        .meta-box {
            margin-top: 10px;
            padding: 9px;
            border: 1px solid #CBD5E1;
            background: #F8FAFC;
        }

        .summary-table,
        .report-table,
        .deadline-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 5px;
            border: 1px solid #CBD5E1;
        }

        .summary-table td:first-child {
            width: 34%;
            font-weight: bold;
            background: #F1F5F9;
        }

        .report-table th,
        .report-table td,
        .deadline-table th,
        .deadline-table td {
            padding: 4px;
            border: 1px solid #CBD5E1;
            vertical-align: top;
        }

        .report-table th,
        .deadline-table th {
            background: #477EF7;
            color: #FFFFFF;
            font-weight: bold;
            text-align: center;
        }

        .group-heading {
            background: #EAF1FF !important;
            color: #1E3A8A !important;
        }

        .text-center {
            text-align: center;
        }

        .badge-success,
        .badge-warning,
        .badge-danger,
        .badge-secondary {
            padding: 3px 6px;
            border-radius: 8px;
            font-size: 8px;
        }

        .badge-success {
            color: #166534;
            background: #DCFCE7;
        }

        .badge-warning {
            color: #92400E;
            background: #FEF3C7;
        }

        .badge-danger {
            color: #991B1B;
            background: #FEE2E2;
        }

        .badge-secondary {
            color: #475569;
            background: #E2E8F0;
        }

        .category-block {
            margin-top: 13px;
            page-break-inside: avoid;
        }

        .category-header {
            padding: 7px 8px;
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-bottom: 0;
        }

        .category-meta {
            margin-top: 2px;
            color: #64748B;
            font-size: 8px;
        }

        .detail-table {
            table-layout: fixed;
            font-size: 6.5px;
        }

        .detail-table th,
        .detail-table td {
            padding: 3px 2px;
            border: 1px solid #CBD5E1;
            vertical-align: top;
            word-wrap: break-word;
        }

        .detail-table th {
            background: #477EF7;
            color: #FFFFFF;
            text-align: center;
            font-weight: bold;
        }

        .detail-table .subkategori {
            width: 10%;
        }

        .detail-table .keterangan {
            width: 14%;
        }

        .deadline-heading {
            margin: 8px 0 4px 0;
            font-weight: bold;
            color: #334155;
        }

        .deadline-table {
            font-size: 7px;
            table-layout: fixed;
        }

        .deadline-table th,
        .deadline-table td {
            padding: 4px;
        }

        .footer {
            margin-top: 16px;
            font-size: 8px;
            color: #64748B;
        }
    </style>
</head>
<body>
    @php
        $filters = $filters ?? [];
        $summary = $summary ?? [];
        $rekapKategori = collect($rekapKategori ?? []);
        $detailTargetKategori = collect($detailTargetKategori ?? []);
        $laporanRows = collect($laporanRows ?? []);
        $pdfAktivitasRows = collect($pdfAktivitasRows ?? []);
    @endphp

    <h1>Laporan Kontrak Manajemen Ketua KK</h1>
    <div class="muted">Kelompok Keahlian EIMS</div>

    <div class="meta-box">
        <strong>Ruang Lingkup:</strong> {{ $scopeTitle ?? 'Keseluruhan Kelompok Keahlian' }}<br>
        <strong>Periode:</strong> {{ $filters['label_periode'] ?? '-' }}<br>
        <strong>Tanggal Cetak:</strong> {{ now()->format('d/m/Y H:i') }}
    </div>

    <h2>Ringkasan Capaian</h2>
    <table class="summary-table">
        <tr>
            <td>Jumlah Lab Riset</td>
            <td>{{ $summary['jumlah_lab'] ?? 0 }}</td>
        </tr>
        <tr>
            <td>Jumlah Anggota KK</td>
            <td>{{ $summary['jumlah_anggota'] ?? 0 }}</td>
        </tr>
        <tr>
            <td>Total Target KK</td>
            <td>{{ $summary['total_target'] ?? 0 }}</td>
        </tr>
        <tr>
            <td>Total Realisasi</td>
            <td>{{ $summary['total_realisasi'] ?? 0 }}</td>
        </tr>
        <tr>
            <td>Sisa Target</td>
            <td>{{ $summary['total_sisa'] ?? 0 }}</td>
        </tr>
        <tr>
            <td>Persentase Capaian</td>
            <td>{{ $summary['persentase'] ?? 0 }}%</td>
        </tr>
    </table>

    <h2>Rekap Kategori KM</h2>
    <table class="report-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kategori KM</th>
                <th>Target</th>
                <th>Realisasi</th>
                <th>Sisa</th>
                <th>Progress</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekapKategori as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item['nama'] ?? '-' }}</td>
                    <td class="text-center">{{ $item['target'] ?? 0 }}</td>
                    <td class="text-center">{{ $item['realisasi'] ?? 0 }}</td>
                    <td class="text-center">{{ $item['sisa'] ?? 0 }}</td>
                    <td class="text-center">{{ $item['persentase'] ?? 0 }}%</td>
                    <td class="text-center">
                        @if(($item['status'] ?? '') === 'Tercapai')
                            <span class="badge-success">Tercapai</span>
                        @else
                            <span class="badge-warning">Belum Tercapai</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada data kategori KM.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rekap Detail Target KM per Kategori</h2>
    <div class="muted">
        Rincian sub kategori/jenis KM, keterangan, target dan realisasi per triwulan, serta tenggat penyelesaian.
    </div>

    @forelse($detailTargetKategori as $kategori)
        <div class="category-block">
            <div class="category-header">
                <h3>{{ $kategori['kategori'] ?? '-' }}</h3>
                <div class="category-meta">
                    {{ $kategori['jumlah_sub_kategori'] ?? 0 }} sub kategori/jenis KM
                    · Target periode: {{ $kategori['target_periode'] ?? 0 }}
                    · Realisasi periode: {{ $kategori['realisasi_periode'] ?? 0 }}
                </div>
            </div>

            <table class="detail-table" width="100%">
                <thead>
                    <tr>
                        <th rowspan="2">No</th>
                        <th rowspan="2" class="subkategori">Sub Kategori / Jenis KM</th>
                        <th rowspan="2" class="keterangan">Keterangan</th>
                        <th colspan="6" class="group-heading">Target KM</th>
                        <th colspan="6" class="group-heading">Realisasi KM</th>
                        <th rowspan="2">Sisa</th>
                        <th rowspan="2">%</th>
                        <th rowspan="2">Status</th>
                    </tr>
                    <tr>
                        <th>TW1</th>
                        <th>TW2</th>
                        <th>TW3</th>
                        <th>TW4</th>
                        <th>Total</th>
                        <th>Periode</th>
                        <th>TW1</th>
                        <th>TW2</th>
                        <th>TW3</th>
                        <th>TW4</th>
                        <th>Total</th>
                        <th>Periode</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategori['rows'] ?? [] as $row)
                        @php
                            $badgeClass = match($row['status'] ?? '') {
                                'Tercapai' => 'badge-success',
                                'On Progress' => 'badge-warning',
                                'Belum Mulai' => 'badge-danger',
                                default => 'badge-secondary',
                            };
                        @endphp
                        <tr>
                            <td class="text-center">{{ $row['no'] ?? '-' }}</td>
                            <td>{{ $row['sub_kategori'] ?? '-' }}</td>
                            <td>{{ $row['keterangan'] ?? '-' }}</td>

                            <td class="text-center">{{ $row['target_tw1'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['target_tw2'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['target_tw3'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['target_tw4'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['target_total_tahunan'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['target_periode'] ?? 0 }}</td>

                            <td class="text-center">{{ $row['realisasi_tw1'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['realisasi_tw2'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['realisasi_tw3'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['realisasi_tw4'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['realisasi_total_tahunan'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['realisasi_periode'] ?? 0 }}</td>

                            <td class="text-center">{{ $row['sisa_periode'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['persentase'] ?? 0 }}%</td>
                            <td class="text-center"><span class="{{ $badgeClass }}">{{ $row['status'] ?? '-' }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="18" class="text-center">Belum ada detail target KM dalam kategori ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="deadline-heading">Tenggat Penyelesaian per Triwulan</div>
            <table class="deadline-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Sub Kategori / Jenis KM</th>
                        <th>TW1</th>
                        <th>TW2</th>
                        <th>TW3</th>
                        <th>TW4</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kategori['rows'] ?? [] as $row)
                        <tr>
                            <td class="text-center">{{ $row['no'] ?? '-' }}</td>
                            <td>{{ $row['sub_kategori'] ?? '-' }}</td>
                            <td>{{ $row['tanggal_mulai_tw1'] ?? '-' }} — {{ $row['tanggal_selesai_tw1'] ?? '-' }}</td>
                            <td>{{ $row['tanggal_mulai_tw2'] ?? '-' }} — {{ $row['tanggal_selesai_tw2'] ?? '-' }}</td>
                            <td>{{ $row['tanggal_mulai_tw3'] ?? '-' }} — {{ $row['tanggal_selesai_tw3'] ?? '-' }}</td>
                            <td>{{ $row['tanggal_mulai_tw4'] ?? '-' }} — {{ $row['tanggal_selesai_tw4'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <p class="muted">Belum ada target KM yang dibuat pada periode ini.</p>
    @endforelse

    @if(($filters['jenis_laporan'] ?? 'kk') !== 'kk')
        <h2>Detail {{ $scopeTitle ?? 'Laporan' }}</h2>
        <table class="report-table">
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
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item['nama'] ?? '-' }}</td>
                        <td>{{ $item['keterangan'] ?? '-' }}</td>
                        <td class="text-center">{{ $item['target'] ?? 0 }}</td>
                        <td class="text-center">{{ $item['realisasi'] ?? 0 }}</td>
                        <td class="text-center">{{ $item['sisa'] ?? 0 }}</td>
                        <td class="text-center">{{ $item['persentase'] ?? 0 }}%</td>
                        <td class="text-center">
                            @if(($item['status'] ?? '') === 'Tercapai')
                                <span class="badge-success">Tercapai</span>
                            @else
                                <span class="badge-warning">Belum Tercapai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data pada ruang lingkup dan periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    @if(($filters['jenis_laporan'] ?? '') === 'anggota_satu')
        <h2>Riwayat Aktivitas Anggota</h2>
        <table class="report-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori</th>
                    <th>Sub Kategori</th>
                    <th>Judul Aktivitas</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Selesai</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pdfAktivitasRows as $index => $aktivitas)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $aktivitas['kategori_km'] ?? '-' }}</td>
                        <td>{{ $aktivitas['sub_kategori_km'] ?? '-' }}</td>
                        <td>{{ $aktivitas['judul_aktivitas'] ?? '-' }}</td>
                        <td>{{ $aktivitas['tanggal_mulai'] ?? '-' }}</td>
                        <td>{{ $aktivitas['tanggal_selesai'] ?? '-' }}</td>
                        <td class="text-center">{{ $aktivitas['status_progress'] ?? 'Accepted' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada aktivitas diterima pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="footer">
        Dokumen ini dihasilkan oleh Sistem Kontrak Manajemen EIMS.
    </div>
</body>
</html>
