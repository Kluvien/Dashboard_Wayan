<?php

namespace App\Http\Controllers;

use App\Exports\AnggotaMultiSheetReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class AnggotaReportController extends Controller
{
    private const KATEGORI_DEFAULT = [
        'Penelitian',
        'Publikasi',
        'Pengabdian',
        'Penunjang',
    ];

    /**
     * Halaman pusat laporan milik Anggota KK.
     */
    public function index(Request $request)
    {
        $filters = $this->normalizeFilters($request);
        $report = $this->buildReport($filters);

        return view('anggota.laporan.index', array_merge($report, [
            'filters' => $filters,
        ]));
    }

    /**
     * Download laporan dalam PDF, Excel multi-sheet, atau CSV ZIP.
     */
    public function download(Request $request)
    {
        $filters = $this->normalizeFilters($request);
        $format = strtolower((string) $request->query('format', 'pdf'));

        if (!in_array($format, ['pdf', 'xlsx', 'csv'], true)) {
            abort(422, 'Format unduhan tidak valid.');
        }

        $report = $this->buildReport($filters);
        $timestamp = now()->format('Ymd-His');

        if ($format === 'pdf') {
            return Pdf::loadView('anggota.laporan.pdf', array_merge($report, [
                'filters' => $filters,
            ]))
                ->setPaper('a4', 'landscape')
                ->download('laporan-km-anggota-' . $timestamp . '.pdf');
        }

        if ($format === 'xlsx') {
            return Excel::download(
                new AnggotaMultiSheetReportExport($report['exportSheets']),
                'laporan-km-anggota-' . $timestamp . '.xlsx'
            );
        }

        return $this->downloadCsvZip($report['exportSheets'], $timestamp);
    }

    /**
     * Menyiapkan filter tahun, semester, atau triwulan.
     */
    private function normalizeFilters(Request $request): array
    {
        $validated = $request->validate([
            'tahun' => ['nullable', 'integer', 'min:2020', 'max:2100'],
            'mode_periode' => ['nullable', 'in:tahun,semester,triwulan'],
            'periode_nilai' => ['nullable', 'integer', 'min:1', 'max:4'],
        ]);

        $tahun = (int) ($validated['tahun'] ?? now()->year);
        $modePeriode = (string) ($validated['mode_periode'] ?? 'tahun');
        $periodeNilai = (int) ($validated['periode_nilai'] ?? 1);

        if ($modePeriode === 'tahun') {
            $periodeNilai = null;
        } elseif ($modePeriode === 'semester') {
            $periodeNilai = max(1, min(2, $periodeNilai));
        } else {
            $periodeNilai = max(1, min(4, $periodeNilai));
        }

        $tanggalMulai = Carbon::create($tahun, 1, 1)->startOfDay();
        $tanggalSelesai = Carbon::create($tahun, 12, 31)->endOfDay();
        $triwulanTerpilih = [1, 2, 3, 4];
        $labelPeriode = 'Tahun ' . $tahun;
        $formatWaktu = 'Dalam 1 Tahun';

        if ($modePeriode === 'semester') {
            $bulanMulai = $periodeNilai === 1 ? 1 : 7;
            $bulanSelesai = $periodeNilai === 1 ? 6 : 12;
            $tanggalMulai = Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
            $tanggalSelesai = Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
            $triwulanTerpilih = $periodeNilai === 1 ? [1, 2] : [3, 4];
            $labelPeriode = 'Semester ' . $periodeNilai . ' Tahun ' . $tahun;
            $formatWaktu = 'Semester ' . $periodeNilai;
        }

        if ($modePeriode === 'triwulan') {
            $bulanMulai = (($periodeNilai - 1) * 3) + 1;
            $bulanSelesai = $bulanMulai + 2;
            $tanggalMulai = Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
            $tanggalSelesai = Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
            $triwulanTerpilih = [$periodeNilai];
            $labelPeriode = 'Triwulan ' . $periodeNilai . ' Tahun ' . $tahun;
            $formatWaktu = 'Triwulan ' . $periodeNilai;
        }

        return [
            'tahun' => $tahun,
            'mode_periode' => $modePeriode,
            'periode_nilai' => $periodeNilai,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'triwulan_terpilih' => $triwulanTerpilih,
            'label_periode' => $labelPeriode,
            'format_waktu' => $formatWaktu,
        ];
    }

    /**
     * Membentuk seluruh data laporan hanya untuk anggota yang sedang login.
     */
    private function buildReport(array $filters): array
    {
        $user = auth()->user();
        abort_unless($user, 403, 'Silakan login terlebih dahulu.');

        $idUser = (int) $user->id_user;
        $tahun = (int) $filters['tahun'];

        $anggota = $this->anggotaInfo($idUser);
        $tahunOptions = $this->tahunOptions($idUser, $tahun);

        $hasIdTarget = Schema::hasColumn('km_lab', 'id_target');
        $hasSubKategori = Schema::hasColumn('km_lab', 'sub_kategori_km');
        $hasKmAnggotaTriwulan = $this->hasTriwulanColumns('km_anggota');
        $hasKmLabTriwulan = $this->hasTriwulanColumns('km_lab');
        $hasStatusProgress = Schema::hasColumn('aktivitas_km', 'status_progress');
        $hasIdKmAnggotaAktivitas = Schema::hasColumn('aktivitas_km', 'id_km_anggota');

        $targetQuery = DB::table('km_anggota as ka')
            ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
            ->leftJoin('laboratorium_riset as lr', 'kl.id_lab', '=', 'lr.id_lab')
            ->where('ka.id_user', $idUser)
            ->where('kl.tahun_km', $tahun)
            ->where('kl.status_km', 'Aktif')
            ->select(
                'ka.id_km_anggota',
                'ka.id_km_lab',
                'ka.jumlah_km',
                'ka.created_at as tanggal_assign',
                'kl.tahun_km',
                'kl.kategori_km',
                'lr.nama_lab'
            );

        if ($hasSubKategori) {
            $targetQuery->addSelect('kl.sub_kategori_km');
        } else {
            $targetQuery->addSelect(DB::raw("'-' as sub_kategori_km"));
        }

        if ($hasIdTarget) {
            $targetQuery->leftJoin('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                ->addSelect(DB::raw("COALESCE(NULLIF(tk.keterangan, ''), '-') as keterangan"));

            foreach (range(1, 4) as $tw) {
                $targetQuery->addSelect(
                    Schema::hasColumn('target_km', 'tanggal_mulai_tw' . $tw)
                        ? 'tk.tanggal_mulai_tw' . $tw
                        : DB::raw('NULL as tanggal_mulai_tw' . $tw),
                    Schema::hasColumn('target_km', 'tanggal_selesai_tw' . $tw)
                        ? 'tk.tanggal_selesai_tw' . $tw
                        : DB::raw('NULL as tanggal_selesai_tw' . $tw)
                );
            }
        } else {
            $targetQuery->addSelect(DB::raw("'-' as keterangan"));

            foreach (range(1, 4) as $tw) {
                $targetQuery->addSelect(
                    DB::raw('NULL as tanggal_mulai_tw' . $tw),
                    DB::raw('NULL as tanggal_selesai_tw' . $tw)
                );
            }
        }

        if ($hasKmAnggotaTriwulan) {
            foreach (range(1, 4) as $tw) {
                $targetQuery->addSelect('ka.triwulan_' . $tw);
            }
        } elseif ($hasKmLabTriwulan) {
            foreach (range(1, 4) as $tw) {
                $targetQuery->addSelect('kl.triwulan_' . $tw);
            }
        } else {
            foreach (range(1, 4) as $tw) {
                $targetQuery->addSelect(DB::raw('0 as triwulan_' . $tw));
            }
        }

        $targetRaw = $targetQuery
            ->orderBy('kl.kategori_km')
            ->orderBy('kl.sub_kategori_km')
            ->orderBy('ka.id_km_anggota')
            ->get();

        $activityYear = DB::table('aktivitas_km as ak')
            ->where('ak.id_user', $idUser)
            ->whereDate('ak.tanggal_mulai', '>=', Carbon::create($tahun, 1, 1)->toDateString())
            ->whereDate('ak.tanggal_mulai', '<=', Carbon::create($tahun, 12, 31)->toDateString())
            ->orderByDesc('ak.tanggal_mulai')
            ->orderByDesc('ak.created_at')
            ->get();

        $activityStats = $this->buildActivityStats($activityYear, $hasStatusProgress, $hasIdKmAnggotaAktivitas);

        $targetRows = $targetRaw->map(function ($target) use ($activityStats, $filters) {
            $idTarget = (int) $target->id_km_anggota;
            $stats = $activityStats[$idTarget] ?? $this->emptyActivityStats();

            $twValues = [];
            foreach (range(1, 4) as $tw) {
                $twValues[$tw] = (int) ($target->{'triwulan_' . $tw} ?? 0);
            }

            $targetTotal = (int) ($target->jumlah_km ?? 0);

            if (array_sum($twValues) <= 0 && $targetTotal > 0) {
                $twValues = $this->splitEvenly($targetTotal, 4);
            }

            $targetPeriode = 0;
            foreach ($filters['triwulan_terpilih'] as $tw) {
                $targetPeriode += $twValues[$tw] ?? 0;
            }

            $realisasiPeriode = 0;
            $aktivitasPeriode = 0;
            $submittedPeriode = 0;
            $onProgressPeriode = 0;

            foreach ($filters['triwulan_terpilih'] as $tw) {
                $realisasiPeriode += (int) ($stats['accepted_tw'][$tw] ?? 0);
                $aktivitasPeriode += (int) ($stats['activities_tw'][$tw] ?? 0);
                $submittedPeriode += (int) ($stats['submitted_tw'][$tw] ?? 0);
                $onProgressPeriode += (int) ($stats['on_progress_tw'][$tw] ?? 0);
            }

            $sisa = max($targetPeriode - $realisasiPeriode, 0);
            $persentase = $targetPeriode > 0
                ? min((int) round(($realisasiPeriode / $targetPeriode) * 100), 100)
                : 0;

            if ($targetPeriode <= 0) {
                $status = 'Belum Ada Target';
            } elseif ($realisasiPeriode >= $targetPeriode) {
                $status = 'Tercapai';
            } elseif ($aktivitasPeriode > 0) {
                $status = 'On Progress';
            } else {
                $status = 'Belum Mulai';
            }

            $row = [
                'id_km_anggota' => $idTarget,
                'tahun' => (int) ($target->tahun_km ?? 0),
                'kategori' => (string) ($target->kategori_km ?? '-'),
                'sub_kategori' => (string) ($target->sub_kategori_km ?? '-'),
                'keterangan' => (string) ($target->keterangan ?? '-'),
                'nama_lab' => (string) ($target->nama_lab ?? '-'),
                'tanggal_assign' => $this->formatReportDate($target->tanggal_assign ?? null),
                'target_tw1' => $twValues[1],
                'target_tw2' => $twValues[2],
                'target_tw3' => $twValues[3],
                'target_tw4' => $twValues[4],
                'target_total_tahunan' => $targetTotal,
                'target_periode' => $targetPeriode,
                'realisasi_tw1' => (int) ($stats['accepted_tw'][1] ?? 0),
                'realisasi_tw2' => (int) ($stats['accepted_tw'][2] ?? 0),
                'realisasi_tw3' => (int) ($stats['accepted_tw'][3] ?? 0),
                'realisasi_tw4' => (int) ($stats['accepted_tw'][4] ?? 0),
                'realisasi_total_tahunan' => (int) ($stats['accepted_total'] ?? 0),
                'realisasi_periode' => $realisasiPeriode,
                'total_aktivitas_periode' => $aktivitasPeriode,
                'submitted_periode' => $submittedPeriode,
                'on_progress_periode' => $onProgressPeriode,
                'sisa' => $sisa,
                'persentase' => $persentase,
                'status' => $status,
            ];

            foreach (range(1, 4) as $tw) {
                $row['tanggal_mulai_tw' . $tw] = $this->formatReportDate($target->{'tanggal_mulai_tw' . $tw} ?? null);
                $row['tanggal_selesai_tw' . $tw] = $this->formatReportDate($target->{'tanggal_selesai_tw' . $tw} ?? null);
            }

            return $row;
        })->values();

        $rekapKategori = collect(self::KATEGORI_DEFAULT)->map(function (string $kategori) use ($targetRows) {
            $rows = $targetRows->where('kategori', $kategori);
            $targetTahunan = (int) $rows->sum('target_total_tahunan');
            $targetPeriode = (int) $rows->sum('target_periode');
            $realisasiTahunan = (int) $rows->sum('realisasi_total_tahunan');
            $realisasiPeriode = (int) $rows->sum('realisasi_periode');
            $sisa = max($targetPeriode - $realisasiPeriode, 0);
            $persentase = $targetPeriode > 0
                ? min((int) round(($realisasiPeriode / $targetPeriode) * 100), 100)
                : 0;

            return [
                'kategori' => $kategori,
                'jumlah_target' => $rows->count(),
                'target_tahunan' => $targetTahunan,
                'target_periode' => $targetPeriode,
                'realisasi_tahunan' => $realisasiTahunan,
                'realisasi_periode' => $realisasiPeriode,
                'sisa' => $sisa,
                'persentase' => $persentase,
                'status' => $targetPeriode <= 0
                    ? 'Belum Ada Target'
                    : ($realisasiPeriode >= $targetPeriode ? 'Tercapai' : ($realisasiPeriode > 0 ? 'On Progress' : 'Belum Mulai')),
            ];
        })->values();

        $detailTargetKategori = $this->buildDetailTargetKategori($targetRows, $rekapKategori);

        $activitiesPeriod = $activityYear
            ->filter(function ($activity) use ($filters) {
                if (empty($activity->tanggal_mulai)) {
                    return false;
                }

                try {
                    $tanggal = Carbon::parse($activity->tanggal_mulai)->startOfDay();
                } catch (\Throwable $e) {
                    return false;
                }

                return $tanggal->betweenIncluded(
                    $filters['tanggal_mulai']->copy()->startOfDay(),
                    $filters['tanggal_selesai']->copy()->endOfDay()
                );
            })
            ->values();

        $aktivitasRows = $activitiesPeriod->map(function ($activity, int $index) use ($hasStatusProgress) {
            return [
                'no' => $index + 1,
                'kategori_km' => (string) ($activity->kategori_km ?? '-'),
                'sub_kategori_km' => (string) ($activity->sub_kategori_km ?? '-'),
                'judul_aktivitas' => (string) ($activity->judul_aktivitas ?? '-'),
                'deskripsi_singkat' => (string) ($activity->deskripsi_singkat ?? '-'),
                'tanggal_mulai' => $this->formatReportDate($activity->tanggal_mulai ?? null),
                'tanggal_selesai' => $this->formatReportDate($activity->tanggal_selesai ?? null),
                'status_progress' => $hasStatusProgress
                    ? ((string) ($activity->status_progress ?? 'On Progress'))
                    : 'Accepted',
                'bukti' => !empty($activity->bukti_link)
                    ? (string) $activity->bukti_link
                    : (!empty($activity->bukti_file_nama_asli) ? (string) $activity->bukti_file_nama_asli : '-'),
            ];
        })->values();

        $totalTarget = (int) $targetRows->sum('target_periode');
        $totalRealisasi = (int) $targetRows->sum('realisasi_periode');
        $totalSisa = max($totalTarget - $totalRealisasi, 0);
        $persentase = $totalTarget > 0
            ? min((int) round(($totalRealisasi / $totalTarget) * 100), 100)
            : 0;

        $summary = [
            'nama_anggota' => $anggota['nama_anggota'],
            'nama_lab' => $anggota['nama_lab'],
            'nidn' => $anggota['nidn'],
            'jad' => $anggota['jad'],
            'jumlah_target_aktif' => $targetRows->count(),
            'jumlah_kategori_aktif' => $targetRows->pluck('kategori')->filter()->unique()->count(),
            'target_tahunan' => (int) $targetRows->sum('target_total_tahunan'),
            'total_target' => $totalTarget,
            'total_realisasi' => $totalRealisasi,
            'total_sisa' => $totalSisa,
            'persentase' => $persentase,
            'submitted' => (int) $targetRows->sum('submitted_periode'),
            'on_progress' => (int) $targetRows->sum('on_progress_periode'),
        ];

        $exportSheets = $this->buildExportSheets(
            $filters,
            $summary,
            $rekapKategori,
            $targetRows,
            $aktivitasRows
        );

        return [
            'tahunOptions' => $tahunOptions,
            'summary' => $summary,
            'rekapKategori' => $rekapKategori,
            'detailTargetKategori' => $detailTargetKategori,
            'targetRows' => $targetRows,
            'aktivitasRows' => $aktivitasRows,
            'exportSheets' => $exportSheets,
            'scopeTitle' => 'Laporan Pribadi Anggota',
        ];
    }

    private function anggotaInfo(int $idUser): array
    {
        $anggota = DB::table('users as u')
            ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
            ->leftJoin('laboratorium_riset as lr', function ($join) {
                $join->on('u.id_lab', '=', 'lr.id_lab')
                    ->orOn('d.id_lab', '=', 'lr.id_lab');
            })
            ->where('u.id_user', $idUser)
            ->select(
                'u.username',
                'd.nama_dosen',
                'd.nidn',
                'd.jad',
                'lr.nama_lab'
            )
            ->first();

        return [
            'nama_anggota' => $anggota->nama_dosen ?? $anggota->username ?? 'Anggota',
            'nama_lab' => $anggota->nama_lab ?? '-',
            'nidn' => $anggota->nidn ?? '-',
            'jad' => $anggota->jad ?? '-',
        ];
    }

    private function tahunOptions(int $idUser, int $tahun): Collection
    {
        $tahunTarget = DB::table('km_anggota as ka')
            ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
            ->where('ka.id_user', $idUser)
            ->whereNotNull('kl.tahun_km')
            ->pluck('kl.tahun_km');

        $tahunAktivitas = DB::table('aktivitas_km')
            ->where('id_user', $idUser)
            ->whereNotNull('tanggal_mulai')
            ->pluck('tanggal_mulai')
            ->map(function ($tanggal) {
                try {
                    return Carbon::parse($tanggal)->year;
                } catch (\Throwable $e) {
                    return null;
                }
            })
            ->filter();

        return collect()
            ->merge($tahunTarget)
            ->merge($tahunAktivitas)
            ->push((int) now()->year)
            ->push($tahun)
            ->filter()
            ->map(fn ($item) => (int) $item)
            ->unique()
            ->sortDesc()
            ->values();
    }

    private function buildActivityStats(Collection $activities, bool $hasStatus, bool $hasIdKmAnggota): array
    {
        $stats = [];

        if (! $hasIdKmAnggota) {
            return $stats;
        }

        foreach ($activities as $activity) {
            $idTarget = (int) ($activity->id_km_anggota ?? 0);

            if ($idTarget <= 0) {
                continue;
            }

            try {
                $tw = (int) ceil(Carbon::parse($activity->tanggal_mulai)->month / 3);
            } catch (\Throwable $e) {
                continue;
            }

            if (!isset($stats[$idTarget])) {
                $stats[$idTarget] = $this->emptyActivityStats();
            }

            $stats[$idTarget]['activities_tw'][$tw]++;
            $stats[$idTarget]['activities_total']++;

            $status = strtolower(trim((string) ($activity->status_progress ?? 'Accepted')));

            if (! $hasStatus || $status === 'accepted') {
                $stats[$idTarget]['accepted_tw'][$tw]++;
                $stats[$idTarget]['accepted_total']++;
            }

            if ($hasStatus && $status === 'submitted') {
                $stats[$idTarget]['submitted_tw'][$tw]++;
                $stats[$idTarget]['submitted_total']++;
            }

            if ($hasStatus && $status === 'on progress') {
                $stats[$idTarget]['on_progress_tw'][$tw]++;
                $stats[$idTarget]['on_progress_total']++;
            }
        }

        return $stats;
    }

    private function emptyActivityStats(): array
    {
        return [
            'activities_tw' => [1 => 0, 2 => 0, 3 => 0, 4 => 0],
            'accepted_tw' => [1 => 0, 2 => 0, 3 => 0, 4 => 0],
            'submitted_tw' => [1 => 0, 2 => 0, 3 => 0, 4 => 0],
            'on_progress_tw' => [1 => 0, 2 => 0, 3 => 0, 4 => 0],
            'activities_total' => 0,
            'accepted_total' => 0,
            'submitted_total' => 0,
            'on_progress_total' => 0,
        ];
    }

    private function buildDetailTargetKategori(Collection $targetRows, Collection $rekapKategori): Collection
    {
        return collect(self::KATEGORI_DEFAULT)->map(function (string $kategori) use ($targetRows, $rekapKategori) {
            $rows = $targetRows
                ->where('kategori', $kategori)
                ->values()
                ->map(function (array $row, int $index) {
                    $row['no'] = $index + 1;
                    return $row;
                });

            $rekap = $rekapKategori->firstWhere('kategori', $kategori) ?? [];

            return [
                'kategori' => $kategori,
                'jumlah_sub_kategori' => $rows->count(),
                'target_periode' => (int) ($rekap['target_periode'] ?? 0),
                'realisasi_periode' => (int) ($rekap['realisasi_periode'] ?? 0),
                'persentase' => (int) ($rekap['persentase'] ?? 0),
                'status' => (string) ($rekap['status'] ?? 'Belum Ada Target'),
                'rows' => $rows,
            ];
        })->values();
    }

    private function buildExportSheets(
        array $filters,
        array $summary,
        Collection $rekapKategori,
        Collection $targetRows,
        Collection $aktivitasRows
    ): array {
        $ringkasanRows = [
            ['Nama Anggota', $summary['nama_anggota'] ?? '-'],
            ['Lab Riset', $summary['nama_lab'] ?? '-'],
            ['NIDN', $summary['nidn'] ?? '-'],
            ['JAD', $summary['jad'] ?? '-'],
            ['Periode', $filters['label_periode'] ?? '-'],
            ['Jumlah Target Aktif', $summary['jumlah_target_aktif'] ?? 0],
            ['Jumlah Kategori Aktif', $summary['jumlah_kategori_aktif'] ?? 0],
            ['Total Target', $summary['total_target'] ?? 0],
            ['Total Realisasi', $summary['total_realisasi'] ?? 0],
            ['Sisa Target', $summary['total_sisa'] ?? 0],
            ['Persentase Capaian', ($summary['persentase'] ?? 0) . '%'],
            ['Submitted', $summary['submitted'] ?? 0],
            ['On Progress', $summary['on_progress'] ?? 0],
        ];

        $kategoriRows = $rekapKategori->map(function (array $row) {
            return [
                $row['kategori'] ?? '-',
                $row['jumlah_target'] ?? 0,
                $row['target_tahunan'] ?? 0,
                $row['target_periode'] ?? 0,
                $row['realisasi_tahunan'] ?? 0,
                $row['realisasi_periode'] ?? 0,
                $row['sisa'] ?? 0,
                ($row['persentase'] ?? 0) . '%',
                $row['status'] ?? '-',
            ];
        })->all();

        $targetExportRows = $targetRows->map(function (array $row) {
            return [
                $row['tahun'] ?? '-',
                $row['kategori'] ?? '-',
                $row['sub_kategori'] ?? '-',
                $row['keterangan'] ?? '-',
                $row['target_tw1'] ?? 0,
                $row['target_tw2'] ?? 0,
                $row['target_tw3'] ?? 0,
                $row['target_tw4'] ?? 0,
                $row['target_total_tahunan'] ?? 0,
                $row['target_periode'] ?? 0,
                $row['realisasi_tw1'] ?? 0,
                $row['realisasi_tw2'] ?? 0,
                $row['realisasi_tw3'] ?? 0,
                $row['realisasi_tw4'] ?? 0,
                $row['realisasi_total_tahunan'] ?? 0,
                $row['realisasi_periode'] ?? 0,
                $row['sisa'] ?? 0,
                ($row['persentase'] ?? 0) . '%',
                $row['status'] ?? '-',
                $row['tanggal_mulai_tw1'] ?? '-',
                $row['tanggal_selesai_tw1'] ?? '-',
                $row['tanggal_mulai_tw2'] ?? '-',
                $row['tanggal_selesai_tw2'] ?? '-',
                $row['tanggal_mulai_tw3'] ?? '-',
                $row['tanggal_selesai_tw3'] ?? '-',
                $row['tanggal_mulai_tw4'] ?? '-',
                $row['tanggal_selesai_tw4'] ?? '-',
            ];
        })->all();

        $activityExportRows = $aktivitasRows->map(function (array $row) {
            return [
                $row['kategori_km'] ?? '-',
                $row['sub_kategori_km'] ?? '-',
                $row['judul_aktivitas'] ?? '-',
                $row['deskripsi_singkat'] ?? '-',
                $row['tanggal_mulai'] ?? '-',
                $row['tanggal_selesai'] ?? '-',
                $row['status_progress'] ?? '-',
                $row['bukti'] ?? '-',
            ];
        })->all();

        return [
            [
                'title' => 'Ringkasan Saya',
                'headings' => ['Indikator', 'Nilai'],
                'rows' => $ringkasanRows,
            ],
            [
                'title' => 'Rekap Kategori',
                'headings' => [
                    'Kategori KM',
                    'Jumlah Target',
                    'Target Tahunan',
                    'Target Periode',
                    'Realisasi Tahunan',
                    'Realisasi Periode',
                    'Sisa',
                    'Progress',
                    'Status',
                ],
                'rows' => $kategoriRows,
            ],
            [
                'title' => 'Detail Target KM',
                'headings' => [
                    'Tahun',
                    'Kategori',
                    'Sub Kategori / Jenis KM',
                    'Keterangan',
                    'Target TW 1',
                    'Target TW 2',
                    'Target TW 3',
                    'Target TW 4',
                    'Total Target Tahunan',
                    'Target Periode',
                    'Realisasi TW 1',
                    'Realisasi TW 2',
                    'Realisasi TW 3',
                    'Realisasi TW 4',
                    'Total Realisasi Tahunan',
                    'Realisasi Periode',
                    'Sisa',
                    'Progress',
                    'Status',
                    'Tanggal Mulai TW 1',
                    'Tenggat TW 1',
                    'Tanggal Mulai TW 2',
                    'Tenggat TW 2',
                    'Tanggal Mulai TW 3',
                    'Tenggat TW 3',
                    'Tanggal Mulai TW 4',
                    'Tenggat TW 4',
                ],
                'rows' => $targetExportRows,
            ],
            [
                'title' => 'Riwayat Aktivitas',
                'headings' => [
                    'Kategori',
                    'Sub Kategori',
                    'Judul Aktivitas',
                    'Deskripsi',
                    'Tanggal Mulai',
                    'Tanggal Selesai',
                    'Status',
                    'Bukti',
                ],
                'rows' => $activityExportRows,
            ],
        ];
    }

    private function downloadCsvZip(array $exportSheets, string $timestamp)
    {
        if (!class_exists(\ZipArchive::class)) {
            abort(500, 'Ekstensi ZipArchive belum aktif pada PHP. Aktifkan extension=zip di php.ini untuk mengunduh CSV ZIP.');
        }

        $filename = 'laporan-km-anggota-' . $timestamp . '-csv.zip';
        $zipPath = storage_path('app/' . $filename);
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'File ZIP CSV tidak dapat dibuat.');
        }

        foreach ($exportSheets as $index => $sheet) {
            $safeTitle = strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '-', (string) ($sheet['title'] ?? 'laporan')));
            $csvName = sprintf('%02d-%s.csv', $index + 1, trim($safeTitle, '-'));
            $zip->addFromString($csvName, $this->buildCsv(
                (array) ($sheet['headings'] ?? []),
                (array) ($sheet['rows'] ?? [])
            ));
        }

        $zip->close();

        return response()
            ->download($zipPath, $filename)
            ->deleteFileAfterSend(true);
    }

    private function buildCsv(array $headings, array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headings);

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = (string) stream_get_contents($handle);
        fclose($handle);

        return "\xEF\xBB\xBF" . $csv;
    }

    private function hasTriwulanColumns(string $table): bool
    {
        return Schema::hasColumn($table, 'triwulan_1')
            && Schema::hasColumn($table, 'triwulan_2')
            && Schema::hasColumn($table, 'triwulan_3')
            && Schema::hasColumn($table, 'triwulan_4');
    }

    private function splitEvenly(int $total, int $parts): array
    {
        $total = max($total, 0);
        $parts = max($parts, 1);
        $base = intdiv($total, $parts);
        $sisa = $total % $parts;
        $result = [];

        for ($i = 1; $i <= $parts; $i++) {
            $result[$i] = $base + ($i <= $sisa ? 1 : 0);
        }

        return $result;
    }

    private function formatReportDate($date): string
    {
        if (empty($date)) {
            return '-';
        }

        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable $e) {
            return '-';
        }
    }
}
