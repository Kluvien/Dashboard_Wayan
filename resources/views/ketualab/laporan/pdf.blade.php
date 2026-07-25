<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Ketua Lab</title>
    <style>
        @page { margin: 18px 22px; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1F2937;
        }

        h1 { font-size: 18px; margin: 0 0 4px; }
        h2 { font-size: 12px; margin: 18px 0 8px; }
        .muted { color: #64748B; }

        .meta-box {
            margin-top: 10px;
            padding: 10px 12px;
            border: 1px solid #CBD5E1;
            background: #F8FAFC;
        }

        .summary-table,
        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 6px;
            border: 1px solid #CBD5E1;
        }

        .summary-table td:first-child {
            width: 38%;
            background: #F1F5F9;
            font-weight: bold;
        }

        .report-table th,
        .report-table td {
            padding: 5px;
            border: 1px solid #CBD5E1;
            vertical-align: top;
        }

        .report-table th {
            background: #477EF7;
            color: #FFFFFF;
            text-align: center;
            font-weight: bold;
        }

        .text-center { text-align: center; }

        .badge-success {
            color: #166534;
            background: #DCFCE7;
            padding: 3px 5px;
            border-radius: 8px;
        }

        .badge-warning {
            color: #92400E;
            background: #FEF3C7;
            padding: 3px 5px;
            border-radius: 8px;
        }

        .badge-danger {
            color: #991B1B;
            background: #FEE2E2;
            padding: 3px 5px;
            border-radius: 8px;
        }

        .badge-secondary {
            color: #475569;
            background: #E2E8F0;
            padding: 3px 5px;
            border-radius: 8px;
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

        .category-header h3 {
            margin: 0;
            color: #1E3A8A;
            font-size: 10px;
        }

        .category-meta {
            margin-top: 2px;
            color: #64748B;
            font-size: 8px;
        }

        .detail-table {
            width: 100%;
            table-layout: fixed;
            font-size: 6px;
            border-collapse: collapse;
        }

        .detail-table th,
        .detail-table td,
        .deadline-table th,
        .deadline-table td {
            padding: 3px 2px;
            border: 1px solid #CBD5E1;
            vertical-align: top;
            word-wrap: break-word;
        }

        .detail-table th,
        .deadline-table th {
            background: #477EF7;
            color: #FFFFFF;
            text-align: center;
            font-weight: bold;
        }

        .detail-table .group-heading {
            background: #EAF1FF !important;
            color: #1E3A8A !important;
        }

        .deadline-heading {
            margin: 8px 0 4px 0;
            font-weight: bold;
            color: #334155;
        }

        .deadline-table {
            width: 100%;
            table-layout: fixed;
            font-size: 7px;
            border-collapse: collapse;
        }

        .footer {
            margin-top: 18px;
            color: #64748B;
            font-size: 8px;
        }
    </style>
</head>
<body>
    @php
        $filters = $filters ?? [];
        $summary = $summary ?? [];
        $rekapKategori = collect($rekapKategori ?? []);
        $targetLabRows = collect($targetLabRows ?? []);
        $detailTargetKategori = collect($detailTargetKategori ?? []);
        $laporanRows = collect($laporanRows ?? []);
        $pdfAktivitasRows = collect($pdfAktivitasRows ?? []);
    @endphp

    <h1>Laporan Kontrak Manajemen Ketua Lab</h1>
    <div class="muted">{{ $lab->nama_lab ?? 'Lab Riset EIMS' }}</div>

    <div class="meta-box">
        <strong>Ruang Lingkup:</strong> {{ $scopeTitle ?? 'Keseluruhan Lab Riset' }}<br>
        <strong>Periode:</strong> {{ $filters['label_periode'] ?? '-' }}<br>
        <strong>Tanggal Cetak:</strong> {{ now()->format('d/m/Y H:i') }}
    </div>

    <h2>Ringkasan Capaian Lab</h2>
    <table class="summary-table">
        <tr><td>Jumlah Anggota Lab</td><td>{{ $summary['jumlah_anggota'] ?? 0 }}</td></tr>
        <tr><td>Total KM Turun</td><td>{{ $summary['total_km_turun'] ?? 0 }}</td></tr>
        <tr><td>Total KM Sudah Dibagi</td><td>{{ $summary['total_km_assign'] ?? 0 }}</td></tr>
        <tr><td>Total KM Belum Dibagi</td><td>{{ $summary['total_belum_assign'] ?? 0 }}</td></tr>
        <tr><td>Target Periode</td><td>{{ $summary['total_target'] ?? 0 }}</td></tr>
        <tr><td>Total Realisasi Diterima</td><td>{{ $summary['total_realisasi'] ?? 0 }}</td></tr>
        <tr><td>Sisa Target</td><td>{{ $summary['total_sisa'] ?? 0 }}</td></tr>
        <tr><td>Persentase Capaian</td><td>{{ $summary['persentase'] ?? 0 }}%</td></tr>
    </table>

    <h2>Rekap Kategori KM</h2>
    <table class="report-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kategori</th>
                <th>KM Turun</th>
                <th>Sudah Dibagi</th>
                <th>Belum Dibagi</th>
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
                    <td>{{ $item['kategori'] ?? '-' }}</td>
                    <td class="text-center">{{ $item['km_turun'] ?? 0 }}</td>
                    <td class="text-center">{{ $item['km_assign'] ?? 0 }}</td>
                    <td class="text-center">{{ $item['sisa_assign'] ?? 0 }}</td>
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
                <tr><td colspan="10" class="text-center">Belum ada data kategori KM.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rekap Detail Target KM per Kategori</h2>
    <div class="muted">
        Rincian sub kategori/jenis KM Lab, keterangan, target dan realisasi per triwulan, pembagian kepada anggota, serta tenggat penyelesaian.
    </div>

    @forelse($detailTargetKategori as $kategori)
        <div class="category-block">
            <div class="category-header">
                <h3>{{ $kategori['kategori'] ?? '-' }}</h3>
                <div class="category-meta">
                    {{ $kategori['jumlah_sub_kategori'] ?? 0 }} sub kategori/jenis KM
                    · Target periode: {{ $kategori['target_periode'] ?? 0 }}
                    · Realisasi periode: {{ $kategori['realisasi_periode'] ?? 0 }}
                    · Sudah dibagi: {{ $kategori['sudah_assign'] ?? 0 }}
                    · Belum dibagi: {{ $kategori['sisa_assign'] ?? 0 }}
                </div>
            </div>

            <table class="detail-table">
                <thead>
                    <tr>
                        <th rowspan="2">No</th>
                        <th rowspan="2">Sub Kategori / Jenis KM</th>
                        <th rowspan="2">Keterangan</th>
                        <th colspan="6" class="group-heading">Target KM</th>
                        <th colspan="2" class="group-heading">Pembagian</th>
                        <th colspan="6" class="group-heading">Realisasi KM</th>
                        <th rowspan="2">Sisa</th>
                        <th rowspan="2">%</th>
                        <th rowspan="2">Status</th>
                    </tr>
                    <tr>
                        <th>TW1</th><th>TW2</th><th>TW3</th><th>TW4</th><th>Total</th><th>Periode</th>
                        <th>Sudah</th><th>Belum</th>
                        <th>TW1</th><th>TW2</th><th>TW3</th><th>TW4</th><th>Total</th><th>Periode</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(($kategori['rows'] ?? []) as $row)
                        <tr>
                            <td class="text-center">{{ $row['no'] ?? '-' }}</td>
                            <td>{{ $row['sub_kategori_km'] ?? '-' }}</td>
                            <td>{{ $row['keterangan'] ?? '-' }}</td>
                            <td class="text-center">{{ $row['target_tw1'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['target_tw2'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['target_tw3'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['target_tw4'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['target_total_tahunan'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['target_periode'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['sudah_assign'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['sisa_assign'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['realisasi_tw1'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['realisasi_tw2'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['realisasi_tw3'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['realisasi_tw4'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['realisasi_total_tahunan'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['realisasi_periode'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['sisa_realisasi'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['persentase'] ?? 0 }}%</td>
                            <td class="text-center">
                                @if(($row['status'] ?? '') === 'Tercapai')
                                    <span class="badge-success">Tercapai</span>
                                @elseif(($row['status'] ?? '') === 'On Progress')
                                    <span class="badge-warning">On Progress</span>
                                @elseif(($row['status'] ?? '') === 'Tidak Ada Target')
                                    <span class="badge-secondary">Tidak Ada Target</span>
                                @else
                                    <span class="badge-danger">Belum Mulai</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
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
                    @foreach(($kategori['rows'] ?? []) as $row)
                        <tr>
                            <td class="text-center">{{ $row['no'] ?? '-' }}</td>
                            <td>{{ $row['sub_kategori_km'] ?? '-' }}</td>
                            <td>{{ $row['tanggal_mulai_tw1'] ?? '-' }} → {{ $row['tanggal_selesai_tw1'] ?? '-' }}</td>
                            <td>{{ $row['tanggal_mulai_tw2'] ?? '-' }} → {{ $row['tanggal_selesai_tw2'] ?? '-' }}</td>
                            <td>{{ $row['tanggal_mulai_tw3'] ?? '-' }} → {{ $row['tanggal_selesai_tw3'] ?? '-' }}</td>
                            <td>{{ $row['tanggal_mulai_tw4'] ?? '-' }} → {{ $row['tanggal_selesai_tw4'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="muted" style="margin-top: 8px;">Belum ada target KM yang diturunkan ke Lab pada periode ini.</div>
    @endforelse

    @if(($filters['jenis_laporan'] ?? 'lab') !== 'lab')
        <h2>Detail {{ $scopeTitle ?? 'Laporan' }}</h2>
        <table class="report-table">
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
                </tr>
            </thead>
            <tbody>
                @forelse($laporanRows as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item['nama'] ?? '-' }}</td>
                        <td>{{ $item['nidn'] ?? '-' }}</td>
                        <td>{{ $item['jad'] ?? '-' }}</td>
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
                    <tr><td colspan="9" class="text-center">Tidak ada data anggota pada periode ini.</td></tr>
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
                    <th>Bukti</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pdfAktivitasRows as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item['kategori_km'] ?? '-' }}</td>
                        <td>{{ $item['sub_kategori_km'] ?? '-' }}</td>
                        <td>{{ $item['judul_aktivitas'] ?? '-' }}</td>
                        <td>{{ $item['tanggal_mulai'] ?? '-' }}</td>
                        <td>{{ $item['tanggal_selesai'] ?? '-' }}</td>
                        <td class="text-center">{{ $item['status_progress'] ?? 'Accepted' }}</td>
                        <td class="text-center">{{ $item['bukti_tersedia'] ?? 'Tidak Ada' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center">Belum ada aktivitas diterima pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="footer">Dokumen ini dihasilkan oleh Sistem Kontrak Manajemen EIMS.</div>
</body>
</html>
