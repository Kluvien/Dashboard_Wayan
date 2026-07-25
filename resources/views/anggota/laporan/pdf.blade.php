<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan KM Anggota</title>
    <style>
        @page { margin: 18px 22px; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #1E293B; font-size: 8.5px; line-height: 1.35; }
        h1 { margin: 0 0 3px; color: #173D77; font-size: 17px; }
        h2 { margin: 18px 0 7px; color: #173D77; font-size: 12px; }
        h3 { margin: 0; color: #1E3A8A; font-size: 10px; }
        .muted { color: #64748B; font-size: 8.5px; }
        .meta-box { margin-top: 10px; padding: 9px; border: 1px solid #CBD5E1; background: #F8FAFC; }
        .summary-table, .report-table, .detail-table, .deadline-table { width: 100%; border-collapse: collapse; }
        .summary-table td { padding: 5px; border: 1px solid #CBD5E1; }
        .summary-table td:first-child { width: 34%; font-weight: bold; background: #EFF6FF; }
        .report-table th, .report-table td, .detail-table th, .detail-table td, .deadline-table th, .deadline-table td { padding: 4px; border: 1px solid #CBD5E1; vertical-align: middle; }
        .report-table th, .detail-table th, .deadline-table th { color: #FFFFFF; background: #477EF7; font-size: 7.5px; }
        .group-heading { background: #356FE6 !important; }
        .category-block { margin-top: 11px; border: 1px solid #D5E3F6; page-break-inside: avoid; }
        .category-header { padding: 7px; background: #EFF6FF; border-bottom: 1px solid #D5E3F6; }
        .category-meta { margin-top: 3px; color: #64748B; font-size: 8px; }
        .deadline-heading { margin-top: 8px; padding: 5px 7px; color: #173D77; font-weight: bold; }
        .badge { display: inline-block; padding: 3px 6px; border-radius: 8px; font-size: 7px; font-weight: bold; }
        .badge-success { color: #166534; background: #DCFCE7; }
        .badge-warning { color: #92400E; background: #FEF3C7; }
        .badge-danger { color: #B91C1C; background: #FEE2E2; }
        .badge-secondary { color: #475569; background: #E2E8F0; }
        .text-center { text-align: center; }
        .footer { margin-top: 15px; color: #64748B; font-size: 8px; }
    </style>
</head>
<body>
    @php
        $summary = $summary ?? [];
        $filters = $filters ?? [];
        $rekapKategori = collect($rekapKategori ?? []);
        $detailTargetKategori = collect($detailTargetKategori ?? []);
        $aktivitasRows = collect($aktivitasRows ?? []);
        $badge = function (?string $status): string {
            return match ($status) {
                'Tercapai', 'Accepted' => 'badge-success',
                'On Progress', 'Submitted' => 'badge-warning',
                'Belum Mulai', 'Rejected' => 'badge-danger',
                default => 'badge-secondary',
            };
        };
    @endphp

    <h1>Laporan Kontrak Manajemen Anggota</h1>
    <div class="muted">Sistem Kontrak Manajemen EIMS</div>

    <div class="meta-box">
        <strong>Nama Anggota:</strong> {{ $summary['nama_anggota'] ?? '-' }}<br>
        <strong>Lab Riset:</strong> {{ $summary['nama_lab'] ?? '-' }}<br>
        <strong>NIDN / JAD:</strong> {{ $summary['nidn'] ?? '-' }} / {{ $summary['jad'] ?? '-' }}<br>
        <strong>Periode:</strong> {{ $filters['label_periode'] ?? '-' }}<br>
        <strong>Tanggal Cetak:</strong> {{ now()->format('d/m/Y H:i') }}
    </div>

    <h2>Ringkasan Capaian</h2>
    <table class="summary-table">
        <tr><td>Jumlah Target Aktif</td><td>{{ $summary['jumlah_target_aktif'] ?? 0 }}</td></tr>
        <tr><td>Jumlah Kategori Aktif</td><td>{{ $summary['jumlah_kategori_aktif'] ?? 0 }}</td></tr>
        <tr><td>Total Target Periode</td><td>{{ $summary['total_target'] ?? 0 }}</td></tr>
        <tr><td>Total Realisasi</td><td>{{ $summary['total_realisasi'] ?? 0 }}</td></tr>
        <tr><td>Sisa Target</td><td>{{ $summary['total_sisa'] ?? 0 }}</td></tr>
        <tr><td>Persentase Capaian</td><td>{{ $summary['persentase'] ?? 0 }}%</td></tr>
        <tr><td>Submitted</td><td>{{ $summary['submitted'] ?? 0 }}</td></tr>
        <tr><td>On Progress</td><td>{{ $summary['on_progress'] ?? 0 }}</td></tr>
    </table>

    <h2>Rekap Kategori KM</h2>
    <table class="report-table">
        <thead>
            <tr>
                <th>No</th><th>Kategori KM</th><th>Jumlah Target</th><th>Target Periode</th><th>Realisasi</th><th>Sisa</th><th>Progress</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapKategori as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row['kategori'] ?? '-' }}</td>
                    <td class="text-center">{{ $row['jumlah_target'] ?? 0 }}</td>
                    <td class="text-center">{{ $row['target_periode'] ?? 0 }}</td>
                    <td class="text-center">{{ $row['realisasi_periode'] ?? 0 }}</td>
                    <td class="text-center">{{ $row['sisa'] ?? 0 }}</td>
                    <td class="text-center">{{ $row['persentase'] ?? 0 }}%</td>
                    <td class="text-center"><span class="badge {{ $badge($row['status'] ?? '') }}">{{ $row['status'] ?? '-' }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Rekap Detail Target KM per Kategori</h2>
    <div class="muted">Rincian sub kategori, target dan realisasi per triwulan, serta tenggat penyelesaian.</div>

    @foreach($detailTargetKategori as $kategori)
        <div class="category-block">
            <div class="category-header">
                <h3>{{ $kategori['kategori'] ?? '-' }}</h3>
                <div class="category-meta">
                    {{ $kategori['jumlah_sub_kategori'] ?? 0 }} target KM · Target periode: {{ $kategori['target_periode'] ?? 0 }} · Realisasi periode: {{ $kategori['realisasi_periode'] ?? 0 }}
                </div>
            </div>

            <table class="detail-table">
                <thead>
                    <tr>
                        <th rowspan="2">No</th>
                        <th rowspan="2">Sub Kategori</th>
                        <th rowspan="2">Keterangan</th>
                        <th colspan="6" class="group-heading">Target KM</th>
                        <th colspan="6" class="group-heading">Realisasi Accepted</th>
                        <th rowspan="2">Sisa</th><th rowspan="2">%</th><th rowspan="2">Status</th>
                    </tr>
                    <tr>
                        <th>TW1</th><th>TW2</th><th>TW3</th><th>TW4</th><th>Total</th><th>Periode</th>
                        <th>TW1</th><th>TW2</th><th>TW3</th><th>TW4</th><th>Total</th><th>Periode</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategori['rows'] ?? [] as $row)
                        <tr>
                            <td class="text-center">{{ $row['no'] ?? '-' }}</td>
                            <td>{{ $row['sub_kategori'] ?? '-' }}</td>
                            <td>{{ $row['keterangan'] ?? '-' }}</td>
                            <td class="text-center">{{ $row['target_tw1'] ?? 0 }}</td><td class="text-center">{{ $row['target_tw2'] ?? 0 }}</td><td class="text-center">{{ $row['target_tw3'] ?? 0 }}</td><td class="text-center">{{ $row['target_tw4'] ?? 0 }}</td><td class="text-center">{{ $row['target_total_tahunan'] ?? 0 }}</td><td class="text-center">{{ $row['target_periode'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['realisasi_tw1'] ?? 0 }}</td><td class="text-center">{{ $row['realisasi_tw2'] ?? 0 }}</td><td class="text-center">{{ $row['realisasi_tw3'] ?? 0 }}</td><td class="text-center">{{ $row['realisasi_tw4'] ?? 0 }}</td><td class="text-center">{{ $row['realisasi_total_tahunan'] ?? 0 }}</td><td class="text-center">{{ $row['realisasi_periode'] ?? 0 }}</td>
                            <td class="text-center">{{ $row['sisa'] ?? 0 }}</td><td class="text-center">{{ $row['persentase'] ?? 0 }}%</td><td class="text-center"><span class="badge {{ $badge($row['status'] ?? '') }}">{{ $row['status'] ?? '-' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="18" class="text-center">Belum ada target KM dalam kategori ini.</td></tr>
                    @endforelse
                </tbody>
            </table>

            @if(!empty($kategori['rows']) && count($kategori['rows']) > 0)
                <div class="deadline-heading">Tenggat Penyelesaian per Triwulan</div>
                <table class="deadline-table">
                    <thead><tr><th>No</th><th>Sub Kategori</th><th>TW1</th><th>TW2</th><th>TW3</th><th>TW4</th></tr></thead>
                    <tbody>
                        @foreach($kategori['rows'] as $row)
                            <tr>
                                <td class="text-center">{{ $row['no'] ?? '-' }}</td>
                                <td>{{ $row['sub_kategori'] ?? '-' }}</td>
                                @for($tw = 1; $tw <= 4; $tw++)
                                    <td>{{ $row['tanggal_mulai_tw' . $tw] ?? '-' }} — {{ $row['tanggal_selesai_tw' . $tw] ?? '-' }}</td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach

    <h2>Riwayat Aktivitas KM</h2>
    <table class="report-table">
        <thead>
            <tr><th>No</th><th>Kategori</th><th>Sub Kategori</th><th>Judul Aktivitas</th><th>Tanggal Mulai</th><th>Tanggal Selesai</th><th>Status</th><th>Bukti</th></tr>
        </thead>
        <tbody>
            @forelse($aktivitasRows as $row)
                <tr>
                    <td class="text-center">{{ $row['no'] ?? '-' }}</td>
                    <td>{{ $row['kategori_km'] ?? '-' }}</td>
                    <td>{{ $row['sub_kategori_km'] ?? '-' }}</td>
                    <td>{{ $row['judul_aktivitas'] ?? '-' }}</td>
                    <td>{{ $row['tanggal_mulai'] ?? '-' }}</td>
                    <td>{{ $row['tanggal_selesai'] ?? '-' }}</td>
                    <td class="text-center"><span class="badge {{ $badge($row['status_progress'] ?? '') }}">{{ $row['status_progress'] ?? '-' }}</span></td>
                    <td>{{ $row['bukti'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">Belum ada aktivitas KM pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Dokumen ini dihasilkan oleh Sistem Kontrak Manajemen EIMS.</div>
</body>
</html>
