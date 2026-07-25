<?php

namespace App\Http\Controllers;

use App\Exports\KetuaLabMultiSheetReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class KetuaLabReportController extends Controller
{
    private const KATEGORI_DEFAULT = [
        'Penelitian',
        'Publikasi',
        'Pengabdian',
        'Penunjang',
    ];

    /**
     * Halaman pusat laporan Ketua Lab.
     */
    public function index(Request $request)
    {
        $filters = $this->normalizeFilters($request);
        $report = $this->buildReport($filters);

        return view('ketualab.laporan.index', array_merge($report, [
            'filters' => $filters,
        ]));
    }

    /**
     * Mengunduh laporan dalam PDF, Excel multi-sheet, atau CSV ZIP multi-file.
     */
    public function download(Request $request)
    {
        $filters = $this->normalizeFilters($request);
        $format = $request->query('format', 'pdf');

        if (!in_array($format, ['pdf', 'xlsx', 'csv'], true)) {
            abort(422, 'Format unduhan tidak valid.');
        }

        $report = $this->buildReport($filters);
        $timestamp = now()->format('Ymd-His');

        if ($format === 'pdf') {
            return Pdf::loadView('ketualab.laporan.pdf', array_merge($report, [
                'filters' => $filters,
            ]))
                ->setPaper('a4', 'landscape')
                ->download('reports-ketua-lab-' . $timestamp . '.pdf');
        }

        if ($format === 'xlsx') {
            return Excel::download(
                new KetuaLabMultiSheetReportExport($report['exportSheets']),
                'reports-ketua-lab-' . $timestamp . '.xlsx'
            );
        }

        return $this->downloadCsvZip($report['exportSheets'], $timestamp);
    }

    /**
     * Menormalkan pilihan periode dan ruang lingkup PDF.
     * Ketua Lab hanya dapat melihat labnya sendiri.
     */
    private function normalizeFilters(Request $request): array
    {
        $validated = $request->validate([
            'tahun' => ['nullable', 'integer', 'min:2020', 'max:2100'],
            'mode_periode' => ['nullable', 'in:tahun,semester,triwulan'],
            'periode_nilai' => ['nullable', 'integer', 'min:1', 'max:4'],
            'jenis_laporan' => ['nullable', 'in:lab,anggota_semua,anggota_satu'],
            'id_user' => ['nullable', 'integer'],
        ]);

        $tahun = (int) ($validated['tahun'] ?? now()->year);
        $modePeriode = $validated['mode_periode'] ?? 'tahun';
        $jenisLaporan = $validated['jenis_laporan'] ?? 'lab';
        $periodeNilai = (int) ($validated['periode_nilai'] ?? 1);

        if ($modePeriode === 'tahun') {
            $periodeNilai = null;
        } elseif ($modePeriode === 'semester') {
            $periodeNilai = max(1, min(2, $periodeNilai));
        } else {
            $periodeNilai = max(1, min(4, $periodeNilai));
        }

        $idUser = !empty($validated['id_user']) ? (int) $validated['id_user'] : null;

        if ($jenisLaporan === 'anggota_satu' && !$idUser) {
            throw ValidationException::withMessages([
                'id_user' => 'Pilih anggota terlebih dahulu untuk laporan per anggota.',
            ]);
        }

        $tanggalMulai = Carbon::create($tahun, 1, 1)->startOfYear();
        $tanggalSelesai = Carbon::create($tahun, 12, 31)->endOfDay();
        $labelPeriode = 'Tahun ' . $tahun;

        if ($modePeriode === 'semester') {
            $bulanMulai = $periodeNilai === 1 ? 1 : 7;
            $bulanSelesai = $periodeNilai === 1 ? 6 : 12;

            $tanggalMulai = Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
            $tanggalSelesai = Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
            $labelPeriode = 'Semester ' . $periodeNilai . ' Tahun ' . $tahun;
        }

        if ($modePeriode === 'triwulan') {
            $bulanMulai = (($periodeNilai - 1) * 3) + 1;
            $bulanSelesai = $bulanMulai + 2;

            $tanggalMulai = Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
            $tanggalSelesai = Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
            $labelPeriode = 'Triwulan ' . $periodeNilai . ' Tahun ' . $tahun;
        }

        return [
            'tahun' => $tahun,
            'mode_periode' => $modePeriode,
            'periode_nilai' => $periodeNilai,
            'jenis_laporan' => $jenisLaporan,
            'id_user' => $idUser,
            'tanggal_mulai' => $tanggalMulai->toDateString(),
            'tanggal_selesai' => $tanggalSelesai->toDateString(),
            'label_periode' => $labelPeriode,
        ];
    }

    /**
     * Membangun seluruh data laporan Ketua Lab.
     */
    private function buildReport(array $filters): array
    {
        $userLogin = auth()->user();
        $idLab = $this->resolveIdLabKetua($userLogin);

        abort_unless($idLab, 403, 'Data Lab Riset Ketua Lab tidak ditemukan.');

        $lab = DB::table('laboratorium_riset')
            ->where('id_lab', $idLab)
            ->first();

        abort_unless($lab, 403, 'Lab Riset Ketua Lab tidak ditemukan.');

        $hasStatusProgress = Schema::hasColumn('aktivitas_km', 'status_progress');
        $hasSubKategoriAktivitas = Schema::hasColumn('aktivitas_km', 'sub_kategori_km');
        $hasBuktiLink = Schema::hasColumn('aktivitas_km', 'bukti_link');
        $hasBuktiFilePath = Schema::hasColumn('aktivitas_km', 'bukti_file_path');
        $hasBuktiPdfPath = Schema::hasColumn('aktivitas_km', 'bukti_pdf_path');
        $hasIdKmAnggotaAktivitas = Schema::hasColumn('aktivitas_km', 'id_km_anggota');

        $hasKmLabIdTarget = Schema::hasColumn('km_lab', 'id_target');
        $hasDeadlineMulaiTw1 = Schema::hasColumn('target_km', 'tanggal_mulai_tw1');
        $hasDeadlineSelesaiTw1 = Schema::hasColumn('target_km', 'tanggal_selesai_tw1');
        $hasDeadlineMulaiTw2 = Schema::hasColumn('target_km', 'tanggal_mulai_tw2');
        $hasDeadlineSelesaiTw2 = Schema::hasColumn('target_km', 'tanggal_selesai_tw2');
        $hasDeadlineMulaiTw3 = Schema::hasColumn('target_km', 'tanggal_mulai_tw3');
        $hasDeadlineSelesaiTw3 = Schema::hasColumn('target_km', 'tanggal_selesai_tw3');
        $hasDeadlineMulaiTw4 = Schema::hasColumn('target_km', 'tanggal_mulai_tw4');
        $hasDeadlineSelesaiTw4 = Schema::hasColumn('target_km', 'tanggal_selesai_tw4');
        $hasKmLabSubKategori = Schema::hasColumn('km_lab', 'sub_kategori_km');

        $hasKmLabTriwulan = $this->hasTriwulanColumns('km_lab');
        $hasKmAnggotaTriwulan = $this->hasTriwulanColumns('km_anggota');

        $tahunOptions = collect()
            ->merge(
                DB::table('km_lab')
                    ->where('id_lab', $idLab)
                    ->pluck('tahun_km')
            )
            ->merge(
                DB::table('aktivitas_km')
                    ->where('id_lab', $idLab)
                    ->selectRaw("strftime('%Y', tanggal_mulai) as tahun")
                    ->pluck('tahun')
            )
            ->push(now()->year)
            ->map(fn ($item) => (int) $item)
            ->unique()
            ->sortDesc()
            ->values();

        $anggotaOptions = $this->anggotaLabQuery($idLab)
            ->select(
                'u.id_user',
                'u.username',
                'd.nama_dosen',
                'd.nidn',
                'd.jad',
                DB::raw('COALESCE(u.id_lab, d.id_lab) as id_lab')
            )
            ->orderBy('d.nama_dosen')
            ->orderBy('u.username')
            ->get();

        $anggotaIds = $anggotaOptions
            ->pluck('id_user')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($filters['jenis_laporan'] === 'anggota_satu' && !$anggotaIds->contains($filters['id_user'])) {
            throw ValidationException::withMessages([
                'id_user' => 'Anggota yang dipilih tidak termasuk dalam Lab Riset Anda.',
            ]);
        }

        /*
        |------------------------------------------------------------------
        | Target KM yang diturunkan Ketua KK ke Lab
        |------------------------------------------------------------------
        */
        $kmLabQuery = DB::table('km_lab as kl')
            ->where('kl.id_lab', $idLab)
            ->where('kl.tahun_km', $filters['tahun'])
            ->where('kl.status_km', 'Aktif')
            ->select(
                'kl.id_km_lab',
                'kl.tahun_km',
                'kl.kategori_km',
                'kl.jumlah_km'
            )
            ->orderBy('kl.kategori_km');

        if ($hasKmLabSubKategori) {
            $kmLabQuery->addSelect('kl.sub_kategori_km');
        } else {
            $kmLabQuery->addSelect(DB::raw("'-' as sub_kategori_km"));
        }

        if ($hasKmLabTriwulan) {
            $kmLabQuery->addSelect(
                'kl.triwulan_1',
                'kl.triwulan_2',
                'kl.triwulan_3',
                'kl.triwulan_4'
            );
        } else {
            $kmLabQuery->addSelect(
                DB::raw('0 as triwulan_1'),
                DB::raw('0 as triwulan_2'),
                DB::raw('0 as triwulan_3'),
                DB::raw('0 as triwulan_4')
            );
        }

        if ($hasKmLabIdTarget) {
            $kmLabQuery
                ->leftJoin('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                ->addSelect(
                    'tk.keterangan',
                    $hasDeadlineMulaiTw1 ? 'tk.tanggal_mulai_tw1' : DB::raw('NULL as tanggal_mulai_tw1'),
                    $hasDeadlineSelesaiTw1 ? 'tk.tanggal_selesai_tw1' : DB::raw('NULL as tanggal_selesai_tw1'),
                    $hasDeadlineMulaiTw2 ? 'tk.tanggal_mulai_tw2' : DB::raw('NULL as tanggal_mulai_tw2'),
                    $hasDeadlineSelesaiTw2 ? 'tk.tanggal_selesai_tw2' : DB::raw('NULL as tanggal_selesai_tw2'),
                    $hasDeadlineMulaiTw3 ? 'tk.tanggal_mulai_tw3' : DB::raw('NULL as tanggal_mulai_tw3'),
                    $hasDeadlineSelesaiTw3 ? 'tk.tanggal_selesai_tw3' : DB::raw('NULL as tanggal_selesai_tw3'),
                    $hasDeadlineMulaiTw4 ? 'tk.tanggal_mulai_tw4' : DB::raw('NULL as tanggal_mulai_tw4'),
                    $hasDeadlineSelesaiTw4 ? 'tk.tanggal_selesai_tw4' : DB::raw('NULL as tanggal_selesai_tw4')
                );
        } else {
            $kmLabQuery->addSelect(
                DB::raw('NULL as keterangan'),
                DB::raw('NULL as tanggal_mulai_tw1'),
                DB::raw('NULL as tanggal_selesai_tw1'),
                DB::raw('NULL as tanggal_mulai_tw2'),
                DB::raw('NULL as tanggal_selesai_tw2'),
                DB::raw('NULL as tanggal_mulai_tw3'),
                DB::raw('NULL as tanggal_selesai_tw3'),
                DB::raw('NULL as tanggal_mulai_tw4'),
                DB::raw('NULL as tanggal_selesai_tw4')
            );
        }

        $kmLabRows = $kmLabQuery->get();
        $idKmLabList = $kmLabRows->pluck('id_km_lab')->values();

        $targetByKategori = $kmLabRows
            ->groupBy('kategori_km')
            ->map(function ($rows) use ($filters) {
                return $rows->sum(function ($row) use ($filters) {
                    return $this->periodTargetFromTriwulan([
                        1 => (int) ($row->triwulan_1 ?? 0),
                        2 => (int) ($row->triwulan_2 ?? 0),
                        3 => (int) ($row->triwulan_3 ?? 0),
                        4 => (int) ($row->triwulan_4 ?? 0),
                    ], $filters, (int) ($row->jumlah_km ?? 0));
                });
            });

        /*
        |------------------------------------------------------------------
        | Target yang telah dibagi Ketua Lab ke anggota
        |------------------------------------------------------------------
        */
        $assignByKategori = collect();
        $assignByKmLab = collect();
        $assignByUser = collect();

        if ($idKmLabList->isNotEmpty()) {
            $assignExpression = $this->periodExpression(
                'ka',
                'jumlah_km',
                $filters,
                $hasKmAnggotaTriwulan
            );

            $assignRows = DB::table('km_anggota as ka')
                ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                ->whereIn('ka.id_km_lab', $idKmLabList)
                ->where('kl.id_lab', $idLab)
                ->where('kl.tahun_km', $filters['tahun'])
                ->where('kl.status_km', 'Aktif')
                ->select(
                    'ka.id_user',
                    'ka.id_km_lab',
                    'kl.kategori_km',
                    DB::raw('COALESCE(' . $assignExpression . ', 0) as target_periode')
                )
                ->get();

            $assignByKategori = $assignRows
                ->groupBy('kategori_km')
                ->map(fn ($rows) => (int) $rows->sum('target_periode'));

            $assignByKmLab = $assignRows
                ->groupBy('id_km_lab')
                ->map(fn ($rows) => (int) $rows->sum('target_periode'));

            $assignByUser = $assignRows
                ->groupBy('id_user')
                ->map(fn ($rows) => (int) $rows->sum('target_periode'));
        }

        /*
        |------------------------------------------------------------------
        | Realisasi aktivitas yang telah Accepted
        |------------------------------------------------------------------
        */
        $activityQuery = DB::table('aktivitas_km as ak')
            ->join('users as u', 'ak.id_user', '=', 'u.id_user')
            ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
            ->whereBetween('ak.tanggal_mulai', [
                $filters['tanggal_mulai'],
                $filters['tanggal_selesai'],
            ])
            ->where(function ($query) use ($idLab) {
                $query->where('ak.id_lab', $idLab)
                    ->orWhere('u.id_lab', $idLab)
                    ->orWhere('d.id_lab', $idLab);
            })
            ->select(
                'ak.id_aktivitas',
                'ak.id_user',
                'ak.id_km_anggota',
                'ak.kategori_km',
                'ak.judul_aktivitas',
                'ak.deskripsi_singkat',
                'ak.tanggal_mulai',
                'ak.tanggal_selesai',
                'u.username',
                'd.nama_dosen',
                'd.nidn',
                'd.jad'
            );

        if ($hasSubKategoriAktivitas) {
            $activityQuery->addSelect('ak.sub_kategori_km');
        } else {
            $activityQuery->addSelect(DB::raw("'-' as sub_kategori_km"));
        }

        if ($hasStatusProgress) {
            $activityQuery
                ->where('ak.status_progress', 'Accepted')
                ->addSelect('ak.status_progress');
        } else {
            $activityQuery->addSelect(DB::raw("'Accepted' as status_progress"));
        }

        if ($hasBuktiLink) {
            $activityQuery->addSelect('ak.bukti_link');
        } else {
            $activityQuery->addSelect(DB::raw('NULL as bukti_link'));
        }

        if ($hasBuktiFilePath) {
            $activityQuery->addSelect('ak.bukti_file_path');
        } else {
            $activityQuery->addSelect(DB::raw('NULL as bukti_file_path'));
        }

        if ($hasBuktiPdfPath) {
            $activityQuery->addSelect('ak.bukti_pdf_path');
        } else {
            $activityQuery->addSelect(DB::raw('NULL as bukti_pdf_path'));
        }

        $activities = $activityQuery
            ->orderByDesc('ak.tanggal_mulai')
            ->orderByDesc('ak.created_at')
            ->get();

        $realisasiByKategori = $activities
            ->groupBy('kategori_km')
            ->map(fn ($rows) => (int) $rows->count());

        $realisasiByUser = $activities
            ->groupBy('id_user')
            ->map(fn ($rows) => (int) $rows->count());

        /*
        |------------------------------------------------------------------
        | Realisasi per target KM Lab dan per triwulan
        |------------------------------------------------------------------
        | Detail laporan Lab mengikuti format laporan Ketua KK: target dan
        | realisasi tetap tersaji per TW, total tahunan, serta periode aktif.
        */
        $realisasiByKmLab = collect();
        $realisasiTwByKmLab = [];
        $fallbackKmLabByKey = [];

        foreach ($kmLabRows as $kmLab) {
            $idKmLab = (int) $kmLab->id_km_lab;
            $realisasiTwByKmLab[$idKmLab] = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

            $fallbackKey = $this->targetFallbackKey(
                (string) ($kmLab->kategori_km ?? ''),
                (string) ($kmLab->sub_kategori_km ?? '')
            );

            if (!isset($fallbackKmLabByKey[$fallbackKey])) {
                $fallbackKmLabByKey[$fallbackKey] = $idKmLab;
            }
        }

        if ($idKmLabList->isNotEmpty()) {
            $tahunMulai = Carbon::create($filters['tahun'], 1, 1)->startOfYear()->toDateString();
            $tahunSelesai = Carbon::create($filters['tahun'], 12, 31)->endOfYear()->toDateString();

            $realisasiDetailQuery = DB::table('aktivitas_km as ak')
                ->join('users as u', 'ak.id_user', '=', 'u.id_user')
                ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
                ->whereBetween('ak.tanggal_mulai', [$tahunMulai, $tahunSelesai])
                ->where(function ($query) use ($idLab) {
                    $query->where('ak.id_lab', $idLab)
                        ->orWhere('u.id_lab', $idLab)
                        ->orWhere('d.id_lab', $idLab);
                })
                ->select(
                    'ak.kategori_km',
                    'ak.tanggal_mulai'
                );

            if ($hasSubKategoriAktivitas) {
                $realisasiDetailQuery->addSelect('ak.sub_kategori_km');
            } else {
                $realisasiDetailQuery->addSelect(DB::raw("'-' as sub_kategori_km"));
            }

            if ($hasIdKmAnggotaAktivitas) {
                $realisasiDetailQuery
                    ->leftJoin('km_anggota as ka', 'ak.id_km_anggota', '=', 'ka.id_km_anggota')
                    ->addSelect('ka.id_km_lab');
            } else {
                $realisasiDetailQuery->addSelect(DB::raw('NULL as id_km_lab'));
            }

            if ($hasStatusProgress) {
                $realisasiDetailQuery->where('ak.status_progress', 'Accepted');
            }

            foreach ($realisasiDetailQuery->get() as $aktivitasDetail) {
                $idKmLab = !empty($aktivitasDetail->id_km_lab)
                    ? (int) $aktivitasDetail->id_km_lab
                    : null;

                if (!$idKmLab || !isset($realisasiTwByKmLab[$idKmLab])) {
                    $fallbackKey = $this->targetFallbackKey(
                        (string) ($aktivitasDetail->kategori_km ?? ''),
                        (string) ($aktivitasDetail->sub_kategori_km ?? '')
                    );
                    $idKmLab = $fallbackKmLabByKey[$fallbackKey] ?? null;
                }

                if (!$idKmLab || !isset($realisasiTwByKmLab[$idKmLab])) {
                    continue;
                }

                try {
                    $triwulan = (int) ceil(Carbon::parse($aktivitasDetail->tanggal_mulai)->month / 3);
                } catch (\Throwable $exception) {
                    continue;
                }

                if (isset($realisasiTwByKmLab[$idKmLab][$triwulan])) {
                    $realisasiTwByKmLab[$idKmLab][$triwulan]++;
                }
            }
        }

        $realisasiByKmLab = collect($realisasiTwByKmLab)
            ->map(function (array $triwulanValues) use ($filters) {
                return $this->periodTargetFromTriwulan($triwulanValues, $filters, array_sum($triwulanValues));
            });

        /*
        |------------------------------------------------------------------
        | Rekap kategori serta detail target KM Lab
        |------------------------------------------------------------------
        */
        $rekapKategori = collect(self::KATEGORI_DEFAULT)->map(function ($kategori) use (
            $targetByKategori,
            $assignByKategori,
            $realisasiByKategori
        ) {
            $kmTurun = (int) ($targetByKategori[$kategori] ?? 0);
            $kmAssign = (int) ($assignByKategori[$kategori] ?? 0);
            $realisasi = (int) ($realisasiByKategori[$kategori] ?? 0);

            $sisaAssign = max($kmTurun - $kmAssign, 0);
            $sisaRealisasi = max($kmTurun - $realisasi, 0);
            $persentase = $kmTurun > 0
                ? min((int) round(($realisasi / $kmTurun) * 100), 100)
                : 0;

            return [
                'nama' => $kategori,
                'kategori' => $kategori,
                'km_turun' => $kmTurun,
                'km_assign' => $kmAssign,
                'sisa_assign' => $sisaAssign,
                'target' => $kmTurun,
                'realisasi' => $realisasi,
                'sisa' => $sisaRealisasi,
                'persentase' => $persentase,
                'status' => $kmTurun > 0 && $realisasi >= $kmTurun
                    ? 'Tercapai'
                    : 'Belum Tercapai',
            ];
        })->values();

        $targetLabRows = $kmLabRows->map(function ($row) use (
            $assignByKmLab,
            $realisasiByKmLab,
            $realisasiTwByKmLab,
            $filters
        ) {
            $targetTw = [
                1 => (int) ($row->triwulan_1 ?? 0),
                2 => (int) ($row->triwulan_2 ?? 0),
                3 => (int) ($row->triwulan_3 ?? 0),
                4 => (int) ($row->triwulan_4 ?? 0),
            ];

            $realisasiTw = $realisasiTwByKmLab[(int) $row->id_km_lab] ?? [
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
            ];

            $targetTotalTahunan = max((int) ($row->jumlah_km ?? 0), array_sum($targetTw));
            $targetPeriode = $this->periodTargetFromTriwulan($targetTw, $filters, $targetTotalTahunan);
            $sudahAssign = (int) ($assignByKmLab[$row->id_km_lab] ?? 0);
            $realisasiPeriode = (int) ($realisasiByKmLab[$row->id_km_lab] ?? 0);
            $realisasiTotalTahunan = array_sum($realisasiTw);
            $sisaAssign = max($targetTotalTahunan - $sudahAssign, 0);
            $sisaRealisasi = max($targetPeriode - $realisasiPeriode, 0);

            if ($targetPeriode <= 0) {
                $status = 'Tidak Ada Target';
            } elseif ($realisasiPeriode >= $targetPeriode) {
                $status = 'Tercapai';
            } elseif ($realisasiPeriode > 0) {
                $status = 'On Progress';
            } else {
                $status = 'Belum Mulai';
            }

            return [
                'id_km_lab' => (int) $row->id_km_lab,
                'kategori_km' => $row->kategori_km ?? '-',
                'sub_kategori_km' => $row->sub_kategori_km ?? '-',
                'keterangan' => $row->keterangan ?? '-',
                'target_tw1' => $targetTw[1],
                'target_tw2' => $targetTw[2],
                'target_tw3' => $targetTw[3],
                'target_tw4' => $targetTw[4],
                'target_total_tahunan' => $targetTotalTahunan,
                'target_periode' => $targetPeriode,
                'sudah_assign' => $sudahAssign,
                'sisa_assign' => $sisaAssign,
                'realisasi_tw1' => (int) ($realisasiTw[1] ?? 0),
                'realisasi_tw2' => (int) ($realisasiTw[2] ?? 0),
                'realisasi_tw3' => (int) ($realisasiTw[3] ?? 0),
                'realisasi_tw4' => (int) ($realisasiTw[4] ?? 0),
                'realisasi_total_tahunan' => $realisasiTotalTahunan,
                'realisasi_periode' => $realisasiPeriode,
                'sisa_realisasi' => $sisaRealisasi,
                'persentase' => $targetPeriode > 0
                    ? min((int) round(($realisasiPeriode / $targetPeriode) * 100), 100)
                    : 0,
                'status' => $status,
                'tanggal_mulai_tw1' => $this->formatReportDate($row->tanggal_mulai_tw1 ?? null),
                'tanggal_selesai_tw1' => $this->formatReportDate($row->tanggal_selesai_tw1 ?? null),
                'tanggal_mulai_tw2' => $this->formatReportDate($row->tanggal_mulai_tw2 ?? null),
                'tanggal_selesai_tw2' => $this->formatReportDate($row->tanggal_selesai_tw2 ?? null),
                'tanggal_mulai_tw3' => $this->formatReportDate($row->tanggal_mulai_tw3 ?? null),
                'tanggal_selesai_tw3' => $this->formatReportDate($row->tanggal_selesai_tw3 ?? null),
                'tanggal_mulai_tw4' => $this->formatReportDate($row->tanggal_mulai_tw4 ?? null),
                'tanggal_selesai_tw4' => $this->formatReportDate($row->tanggal_selesai_tw4 ?? null),
            ];
        })->values();

        $detailTargetKategori = $this->buildDetailTargetKategori($targetLabRows);

        /*
        |------------------------------------------------------------------
        | Rekap anggota Lab
        |------------------------------------------------------------------
        */
        $memberRows = $anggotaOptions->map(function ($anggota) use ($assignByUser, $realisasiByUser) {
            $target = (int) ($assignByUser[$anggota->id_user] ?? 0);
            $realisasi = (int) ($realisasiByUser[$anggota->id_user] ?? 0);

            return $this->summaryRow(
                $anggota->nama_dosen ?? $anggota->username ?? '-',
                'NIDN: ' . ($anggota->nidn ?? '-') . ' | JAD: ' . ($anggota->jad ?? '-'),
                $target,
                $realisasi,
                [
                    'id_user' => (int) $anggota->id_user,
                    'nidn' => $anggota->nidn ?? '-',
                    'jad' => $anggota->jad ?? '-',
                ]
            );
        })->values();

        $aktivitasRows = $activities->map(function ($activity) {
            $hasBukti = !empty($activity->bukti_pdf_path)
                || !empty($activity->bukti_file_path)
                || !empty($activity->bukti_link);

            return [
                'id_aktivitas' => (int) $activity->id_aktivitas,
                'id_user' => (int) $activity->id_user,
                'nama_anggota' => $activity->nama_dosen ?? $activity->username ?? '-',
                'nidn' => $activity->nidn ?? '-',
                'jad' => $activity->jad ?? '-',
                'kategori_km' => $activity->kategori_km ?? '-',
                'sub_kategori_km' => $activity->sub_kategori_km ?? '-',
                'judul_aktivitas' => $activity->judul_aktivitas ?? '-',
                'deskripsi_singkat' => $activity->deskripsi_singkat ?? '-',
                'tanggal_mulai' => $activity->tanggal_mulai ?? '-',
                'tanggal_selesai' => $activity->tanggal_selesai ?? '-',
                'status_progress' => $activity->status_progress ?? 'Accepted',
                'bukti_link' => $activity->bukti_link ?? '-',
                'bukti_tersedia' => $hasBukti ? 'Tersedia' : 'Tidak Ada',
                'bukti_url' => !empty($activity->bukti_link)
                    ? $activity->bukti_link
                    : ($hasBukti ? url('/bukti-km/' . $activity->id_aktivitas . '/download') : null),
            ];
        })->values();

        $scopeTitle = 'Keseluruhan Lab Riset';
        $laporanRows = collect();
        $pdfAktivitasRows = collect();

        if ($filters['jenis_laporan'] === 'anggota_semua') {
            $scopeTitle = 'Seluruh Anggota Lab';
            $laporanRows = $memberRows;
        }

        if ($filters['jenis_laporan'] === 'anggota_satu') {
            $scopeTitle = 'Anggota Terpilih';
            $laporanRows = $memberRows
                ->where('id_user', $filters['id_user'])
                ->values();
            $pdfAktivitasRows = $aktivitasRows
                ->where('id_user', $filters['id_user'])
                ->values();
        }

        $totalKmTurun = (int) $rekapKategori->sum('km_turun');
        $totalKmAssign = (int) $rekapKategori->sum('km_assign');
        $totalBelumAssign = max($totalKmTurun - $totalKmAssign, 0);
        $totalRealisasi = (int) $rekapKategori->sum('realisasi');
        $totalSisa = max($totalKmTurun - $totalRealisasi, 0);

        $summary = [
            'nama_lab' => $lab->nama_lab ?? '-',
            'jumlah_anggota' => $anggotaOptions->count(),
            'total_km_turun' => $totalKmTurun,
            'total_km_assign' => $totalKmAssign,
            'total_belum_assign' => $totalBelumAssign,
            'total_target' => $totalKmTurun,
            'total_realisasi' => $totalRealisasi,
            'total_sisa' => $totalSisa,
            'persentase' => $totalKmTurun > 0
                ? min((int) round(($totalRealisasi / $totalKmTurun) * 100), 100)
                : 0,
        ];

        $exportSheets = $this->buildExportSheets(
            $filters,
            $summary,
            $rekapKategori,
            $targetLabRows,
            $detailTargetKategori,
            $memberRows,
            $aktivitasRows
        );

        return compact(
            'filters',
            'lab',
            'tahunOptions',
            'anggotaOptions',
            'summary',
            'rekapKategori',
            'targetLabRows',
            'detailTargetKategori',
            'memberRows',
            'aktivitasRows',
            'laporanRows',
            'pdfAktivitasRows',
            'scopeTitle',
            'exportSheets'
        );
    }

    /**
     * Query anggota dengan fallback relasi users.id_lab atau dosen.id_lab.
     */
    private function anggotaLabQuery(int $idLab)
    {
        return DB::table('users as u')
            ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
            ->whereIn('u.role', ['Anggota', 'anggota'])
            ->where(function ($query) use ($idLab) {
                $query->where('u.id_lab', $idLab)
                    ->orWhere('d.id_lab', $idLab);
            });
    }

    private function resolveIdLabKetua($user): ?int
    {
        if (!empty($user->id_lab)) {
            return (int) $user->id_lab;
        }

        if (!empty($user->id_dosen)) {
            $idLab = DB::table('dosen')
                ->where('id_dosen', $user->id_dosen)
                ->value('id_lab');

            return $idLab ? (int) $idLab : null;
        }

        return null;
    }

    private function hasTriwulanColumns(string $table): bool
    {
        return Schema::hasColumn($table, 'triwulan_1')
            && Schema::hasColumn($table, 'triwulan_2')
            && Schema::hasColumn($table, 'triwulan_3')
            && Schema::hasColumn($table, 'triwulan_4');
    }

    private function periodExpression(
        string $alias,
        string $totalColumn,
        array $filters,
        bool $hasTriwulanColumns
    ): string {
        if ($filters['mode_periode'] === 'tahun' || !$hasTriwulanColumns) {
            return 'COALESCE(' . $alias . '.' . $totalColumn . ', 0)';
        }

        if ($filters['mode_periode'] === 'semester') {
            if ((int) $filters['periode_nilai'] === 1) {
                return 'COALESCE(' . $alias . '.triwulan_1, 0) + COALESCE(' . $alias . '.triwulan_2, 0)';
            }

            return 'COALESCE(' . $alias . '.triwulan_3, 0) + COALESCE(' . $alias . '.triwulan_4, 0)';
        }

        return 'COALESCE(' . $alias . '.triwulan_' . (int) $filters['periode_nilai'] . ', 0)';
    }

    private function periodTargetFromTriwulan(array $twValues, array $filters, int $fallbackTotal): int
    {
        $totalTriwulan = array_sum($twValues);

        if ($totalTriwulan <= 0) {
            return $filters['mode_periode'] === 'tahun'
                ? max($fallbackTotal, 0)
                : 0;
        }

        if ($filters['mode_periode'] === 'tahun') {
            return $totalTriwulan;
        }

        if ($filters['mode_periode'] === 'semester') {
            return (int) $filters['periode_nilai'] === 1
                ? (int) ($twValues[1] ?? 0) + (int) ($twValues[2] ?? 0)
                : (int) ($twValues[3] ?? 0) + (int) ($twValues[4] ?? 0);
        }

        return (int) ($twValues[(int) $filters['periode_nilai']] ?? 0);
    }

    private function summaryRow(
        string $nama,
        string $keterangan,
        int $target,
        int $realisasi,
        array $tambahan = []
    ): array {
        $sisa = max($target - $realisasi, 0);
        $persentase = $target > 0
            ? min((int) round(($realisasi / $target) * 100), 100)
            : 0;

        return array_merge([
            'nama' => $nama,
            'keterangan' => $keterangan,
            'target' => $target,
            'realisasi' => $realisasi,
            'sisa' => $sisa,
            'persentase' => $persentase,
            'status' => $target > 0 && $realisasi >= $target
                ? 'Tercapai'
                : 'Belum Tercapai',
        ], $tambahan);
    }

    /**
     * Menyusun target KM Lab ke dalam blok per kategori, seperti format detail
     * laporan Ketua KK. Unsur khusus Lab berupa KM sudah/belum dibagi tetap
     * dipertahankan pada setiap sub kategori.
     */
    private function buildDetailTargetKategori($targetLabRows): array
    {
        $kategoriOrder = collect(self::KATEGORI_DEFAULT)
            ->merge(collect($targetLabRows)->pluck('kategori_km')->filter())
            ->unique()
            ->values();

        $hasil = [];

        foreach ($kategoriOrder as $kategori) {
            $rows = collect($targetLabRows)
                ->where('kategori_km', $kategori)
                ->values();

            if ($rows->isEmpty()) {
                continue;
            }

            $detailRows = $rows->map(function (array $row, int $index) {
                return array_merge(['no' => $index + 1], $row);
            })->all();

            $hasil[] = [
                'kategori' => $kategori,
                'jumlah_sub_kategori' => count($detailRows),
                'target_periode' => (int) $rows->sum('target_periode'),
                'realisasi_periode' => (int) $rows->sum('realisasi_periode'),
                'sudah_assign' => (int) $rows->sum('sudah_assign'),
                'sisa_assign' => (int) $rows->sum('sisa_assign'),
                'rows' => $detailRows,
            ];
        }

        return $hasil;
    }

    private function targetFallbackKey(string $kategori, string $subKategori): string
    {
        return strtolower(trim($kategori)) . '|' . strtolower(trim($subKategori));
    }

    private function formatReportDate($date): string
    {
        if (empty($date)) {
            return '-';
        }

        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable $exception) {
            return '-';
        }
    }

    private function buildExportSheets(
        array $filters,
        array $summary,
        $rekapKategori,
        $targetLabRows,
        $detailTargetKategori,
        $memberRows,
        $aktivitasRows
    ): array {
        $ringkasanRows = [
            ['Nama Lab Riset', $summary['nama_lab']],
            ['Periode', $filters['label_periode']],
            ['Jumlah Anggota Lab', $summary['jumlah_anggota']],
            ['Total KM Turun', $summary['total_km_turun']],
            ['Total KM Sudah Dibagi', $summary['total_km_assign']],
            ['Total KM Belum Dibagi', $summary['total_belum_assign']],
            ['Target Periode', $summary['total_target']],
            ['Total Realisasi', $summary['total_realisasi']],
            ['Sisa Target', $summary['total_sisa']],
            ['Persentase Capaian', $summary['persentase'] . '%'],
        ];

        $kategoriRows = collect($rekapKategori)->map(function ($row) {
            return [
                $row['kategori'] ?? '-',
                $row['km_turun'] ?? 0,
                $row['km_assign'] ?? 0,
                $row['sisa_assign'] ?? 0,
                $row['target'] ?? 0,
                $row['realisasi'] ?? 0,
                $row['sisa'] ?? 0,
                ($row['persentase'] ?? 0) . '%',
                $row['status'] ?? 'Belum Tercapai',
            ];
        })->all();

        $targetRows = collect($targetLabRows)->map(function ($row) {
            return [
                $row['kategori_km'] ?? '-',
                $row['sub_kategori_km'] ?? '-',
                $row['keterangan'] ?? '-',
                $row['target_tw1'] ?? 0,
                $row['target_tw2'] ?? 0,
                $row['target_tw3'] ?? 0,
                $row['target_tw4'] ?? 0,
                $row['target_total_tahunan'] ?? 0,
                $row['target_periode'] ?? 0,
                $row['sudah_assign'] ?? 0,
                $row['sisa_assign'] ?? 0,
                $row['realisasi_tw1'] ?? 0,
                $row['realisasi_tw2'] ?? 0,
                $row['realisasi_tw3'] ?? 0,
                $row['realisasi_tw4'] ?? 0,
                $row['realisasi_total_tahunan'] ?? 0,
                $row['realisasi_periode'] ?? 0,
                $row['sisa_realisasi'] ?? 0,
                ($row['persentase'] ?? 0) . '%',
                $row['status'] ?? 'Belum Tercapai',
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

        $detailTargetRows = collect($detailTargetKategori)
            ->flatMap(function (array $group) {
                return collect($group['rows'] ?? [])->map(function (array $row) use ($group) {
                    return [
                        $group['kategori'] ?? '-',
                        $row['sub_kategori_km'] ?? '-',
                        $row['keterangan'] ?? '-',
                        $row['target_tw1'] ?? 0,
                        $row['target_tw2'] ?? 0,
                        $row['target_tw3'] ?? 0,
                        $row['target_tw4'] ?? 0,
                        $row['target_total_tahunan'] ?? 0,
                        $row['target_periode'] ?? 0,
                        $row['sudah_assign'] ?? 0,
                        $row['sisa_assign'] ?? 0,
                        $row['realisasi_tw1'] ?? 0,
                        $row['realisasi_tw2'] ?? 0,
                        $row['realisasi_tw3'] ?? 0,
                        $row['realisasi_tw4'] ?? 0,
                        $row['realisasi_total_tahunan'] ?? 0,
                        $row['realisasi_periode'] ?? 0,
                        $row['sisa_realisasi'] ?? 0,
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
                });
            })
            ->values()
            ->all();

        $memberExportRows = collect($memberRows)->map(function ($row) {
            return [
                $row['nama'] ?? '-',
                $row['nidn'] ?? '-',
                $row['jad'] ?? '-',
                $row['target'] ?? 0,
                $row['realisasi'] ?? 0,
                $row['sisa'] ?? 0,
                ($row['persentase'] ?? 0) . '%',
                $row['status'] ?? 'Belum Tercapai',
            ];
        })->all();

        $activityExportRows = collect($aktivitasRows)->map(function ($row) {
            return [
                $row['nama_anggota'] ?? '-',
                $row['nidn'] ?? '-',
                $row['jad'] ?? '-',
                $row['kategori_km'] ?? '-',
                $row['sub_kategori_km'] ?? '-',
                $row['judul_aktivitas'] ?? '-',
                $row['deskripsi_singkat'] ?? '-',
                $row['tanggal_mulai'] ?? '-',
                $row['tanggal_selesai'] ?? '-',
                $row['status_progress'] ?? '-',
                $row['bukti_url'] ?? '-',
            ];
        })->all();

        return [
            [
                'title' => 'Ringkasan Lab',
                'headings' => ['Indikator', 'Nilai'],
                'rows' => $ringkasanRows,
            ],
            [
                'title' => 'Rekap Kategori',
                'headings' => [
                    'Kategori KM',
                    'KM Turun',
                    'KM Sudah Dibagi',
                    'KM Belum Dibagi',
                    'Target Periode',
                    'Realisasi',
                    'Sisa',
                    'Progress',
                    'Status',
                ],
                'rows' => $kategoriRows,
            ],
            [
                'title' => 'Target KM Lab',
                'headings' => [
                    'Kategori',
                    'Sub Kategori / Jenis KM',
                    'Keterangan',
                    'Target TW 1',
                    'Target TW 2',
                    'Target TW 3',
                    'Target TW 4',
                    'Total Target Tahunan',
                    'Target Periode',
                    'Sudah Dibagi',
                    'Belum Dibagi',
                    'Realisasi TW 1',
                    'Realisasi TW 2',
                    'Realisasi TW 3',
                    'Realisasi TW 4',
                    'Total Realisasi Tahunan',
                    'Realisasi Periode',
                    'Sisa Periode',
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
                'rows' => $targetRows,
            ],
            [
                'title' => 'Detail per Kategori',
                'headings' => [
                    'Kategori',
                    'Sub Kategori / Jenis KM',
                    'Keterangan',
                    'Target TW 1',
                    'Target TW 2',
                    'Target TW 3',
                    'Target TW 4',
                    'Total Target Tahunan',
                    'Target Periode',
                    'Sudah Dibagi',
                    'Belum Dibagi',
                    'Realisasi TW 1',
                    'Realisasi TW 2',
                    'Realisasi TW 3',
                    'Realisasi TW 4',
                    'Total Realisasi Tahunan',
                    'Realisasi Periode',
                    'Sisa Periode',
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
                'rows' => $detailTargetRows,
            ],
            [
                'title' => 'Anggota Lab',
                'headings' => [
                    'Nama Anggota',
                    'NIDN',
                    'JAD',
                    'Target Periode',
                    'Realisasi',
                    'Sisa',
                    'Progress',
                    'Status',
                ],
                'rows' => $memberExportRows,
            ],
            [
                'title' => 'Aktivitas KM',
                'headings' => [
                    'Nama Anggota',
                    'NIDN',
                    'JAD',
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
        if (!class_exists('ZipArchive')) {
            abort(500, 'Ekstensi ZipArchive belum aktif pada PHP. Aktifkan extension=zip di php.ini untuk mengunduh CSV ZIP.');
        }

        $filename = 'reports-ketua-lab-' . $timestamp . '-csv.zip';
        $zipPath = storage_path('app/' . $filename);

        $zip = new \ZipArchive();
        $opened = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        if ($opened !== true) {
            abort(500, 'File ZIP CSV tidak dapat dibuat.');
        }

        foreach ($exportSheets as $index => $sheet) {
            $safeTitle = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $sheet['title']));
            $csvName = sprintf('%02d-%s.csv', $index + 1, trim($safeTitle, '-'));
            $zip->addFromString($csvName, $this->buildCsv($sheet['headings'], $sheet['rows']));
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
        $csv = stream_get_contents($handle);
        fclose($handle);

        return "\xEF\xBB\xBF" . $csv;
    }
}
