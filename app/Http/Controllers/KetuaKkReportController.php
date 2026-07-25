<?php

namespace App\Http\Controllers;

use App\Exports\KetuaKkMultiSheetReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;

class KetuaKkReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->normalizeFilters($request);
        $report = $this->buildReport($filters);

        return view('ketuakk.laporan.index', array_merge($report, [
            'filters' => $filters,
        ]));
    }

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
            $filename = 'reports-ketua-kk-' . $timestamp . '.pdf';

            return Pdf::loadView('ketuakk.laporan.pdf', array_merge($report, [
                'filters' => $filters,
            ]))
                ->setPaper('a4', 'landscape')
                ->download($filename);
        }

        if ($format === 'xlsx') {
            $filename = 'reports-ketua-kk-' . $timestamp . '.xlsx';

            return Excel::download(
                new KetuaKkMultiSheetReportExport($report['exportSheets']),
                $filename
            );
        }

        return $this->downloadCsvZip($report['exportSheets'], $timestamp);
    }

    private function normalizeFilters(Request $request): array
    {
        $validated = $request->validate([
            'tahun' => ['nullable', 'integer', 'min:2020', 'max:2100'],
            'mode_periode' => ['nullable', 'in:tahun,semester,triwulan'],
            'periode_nilai' => ['nullable', 'integer', 'min:1', 'max:4'],
            'jenis_laporan' => ['nullable', 'in:kk,lab_semua,lab_satu,anggota_semua,anggota_satu'],
            'id_lab' => ['nullable', 'integer'],
            'id_user' => ['nullable', 'integer'],
        ]);

        $tahun = (int) ($validated['tahun'] ?? now()->year);
        $modePeriode = $validated['mode_periode'] ?? 'tahun';
        $jenisLaporan = $validated['jenis_laporan'] ?? 'kk';
        $periodeNilai = (int) ($validated['periode_nilai'] ?? 1);

        if ($modePeriode === 'semester') {
            $periodeNilai = max(1, min(2, $periodeNilai));
        }

        if ($modePeriode === 'triwulan') {
            $periodeNilai = max(1, min(4, $periodeNilai));
        }

        if ($modePeriode === 'tahun') {
            $periodeNilai = null;
        }

        $idLab = !empty($validated['id_lab']) ? (int) $validated['id_lab'] : null;
        $idUser = !empty($validated['id_user']) ? (int) $validated['id_user'] : null;

        if ($jenisLaporan === 'lab_satu' && !$idLab) {
            throw ValidationException::withMessages([
                'id_lab' => 'Pilih Lab Riset terlebih dahulu untuk laporan per lab.',
            ]);
        }

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
            'id_lab' => $idLab,
            'id_user' => $idUser,
            'tanggal_mulai' => $tanggalMulai->toDateString(),
            'tanggal_selesai' => $tanggalSelesai->toDateString(),
            'label_periode' => $labelPeriode,
        ];
    }

    private function buildReport(array $filters): array
    {
        $kategoriDefault = [
            'Penelitian',
            'Publikasi',
            'Pengabdian',
            'Penunjang',
        ];

        $userLogin = auth()->user();

        $ketuaKk = DB::table('dosen')
            ->where('id_dosen', $userLogin->id_dosen)
            ->first();

        abort_unless($ketuaKk, 403, 'Data Ketua KK tidak ditemukan.');

        $idKk = $ketuaKk->id_kk;
        $hasStatusProgress = Schema::hasColumn('aktivitas_km', 'status_progress');
        $hasTriwulanTarget = Schema::hasColumns('target_km', [
            'triwulan_1',
            'triwulan_2',
            'triwulan_3',
            'triwulan_4',
        ]);
        $hasTriwulanKmLab = Schema::hasColumns('km_lab', [
            'triwulan_1',
            'triwulan_2',
            'triwulan_3',
            'triwulan_4',
        ]);
        $hasSubKategoriAktivitas = Schema::hasColumn('aktivitas_km', 'sub_kategori_km');
        $hasBuktiLink = Schema::hasColumn('aktivitas_km', 'bukti_link');

        $tahunOptions = collect()
            ->merge(
                DB::table('kontrak_manajemen')
                    ->where('id_dosen', $userLogin->id_dosen)
                    ->pluck('tahun_km')
            )
            ->merge(
                DB::table('km_lab as kl')
                    ->join('laboratorium_riset as lr', 'kl.id_lab', '=', 'lr.id_lab')
                    ->where('lr.id_kk', $idKk)
                    ->pluck('kl.tahun_km')
            )
            ->push(now()->year)
            ->map(fn ($item) => (int) $item)
            ->unique()
            ->sortDesc()
            ->values();

        $labOptions = DB::table('laboratorium_riset')
            ->where('id_kk', $idKk)
            ->orderBy('nama_lab')
            ->get();

        $labIds = $labOptions->pluck('id_lab')->map(fn ($id) => (int) $id)->values();

        $anggotaOptions = DB::table('users as u')
            ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
            ->leftJoin('laboratorium_riset as lr', 'u.id_lab', '=', 'lr.id_lab')
            ->whereIn('u.role', ['Anggota', 'anggota'])
            ->where('lr.id_kk', $idKk)
            ->select(
                'u.id_user',
                'u.username',
                'u.id_lab',
                'd.nama_dosen',
                'd.nidn',
                'd.jad',
                'lr.nama_lab'
            )
            ->orderBy('lr.nama_lab')
            ->orderBy('d.nama_dosen')
            ->get();

        $targetExpressionKk = $this->periodExpression(
            'tk',
            'target_km',
            'target',
            $filters,
            $hasTriwulanTarget
        );

        $targetByCategory = DB::table('target_km as tk')
            ->join('kontrak_manajemen as km', 'tk.id_km', '=', 'km.id_km')
            ->where('km.id_dosen', $userLogin->id_dosen)
            ->where('km.tahun_km', $filters['tahun'])
            ->select(
                'tk.kategori_km',
                DB::raw('COALESCE(SUM(' . $targetExpressionKk . '), 0) as total_target')
            )
            ->groupBy('tk.kategori_km')
            ->pluck('total_target', 'kategori_km');

        $activityQuery = DB::table('aktivitas_km as ak')
            ->join('users as u', 'ak.id_user', '=', 'u.id_user')
            ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
            ->leftJoin('laboratorium_riset as lr', 'u.id_lab', '=', 'lr.id_lab')
            ->where('lr.id_kk', $idKk)
            ->whereBetween('ak.tanggal_mulai', [
                $filters['tanggal_mulai'],
                $filters['tanggal_selesai'],
            ]);

        if ($hasStatusProgress) {
            $activityQuery->where('ak.status_progress', 'Accepted');
        }

        $activityQuery->select(
            'ak.id_aktivitas',
            'ak.id_user',
            'u.id_lab',
            'u.username',
            'd.nama_dosen',
            'd.nidn',
            'lr.nama_lab',
            'ak.kategori_km',
            'ak.judul_aktivitas',
            'ak.deskripsi_singkat',
            'ak.tanggal_mulai',
            'ak.tanggal_selesai'
        );

        if ($hasSubKategoriAktivitas) {
            $activityQuery->addSelect('ak.sub_kategori_km');
        }

        if ($hasStatusProgress) {
            $activityQuery->addSelect('ak.status_progress');
        }

        if ($hasBuktiLink) {
            $activityQuery->addSelect('ak.bukti_link');
        }

        $activities = $activityQuery
            ->orderBy('ak.tanggal_mulai', 'desc')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Rekap Detail Target KM per Kategori
        |--------------------------------------------------------------------------
        | Menampilkan setiap sub kategori/jenis KM yang dibuat Ketua KK,
        | keterangan, target dan realisasi per triwulan, serta tenggatnya.
        */
        $detailTargetKategori = $this->buildTargetCategoryDetails(
            (int) $userLogin->id_dosen,
            (int) $idKk,
            $filters,
            $kategoriDefault
        );

        $realisasiByKategori = $activities
            ->groupBy('kategori_km')
            ->map(fn ($rows) => $rows->count());

        $realisasiByLab = $activities
            ->groupBy('id_lab')
            ->map(fn ($rows) => $rows->count());

        $realisasiByUser = $activities
            ->groupBy('id_user')
            ->map(fn ($rows) => $rows->count());

        $totalTargetKk = (int) collect($targetByCategory)->sum();
        $totalRealisasiKk = $activities->count();

        $rekapKategori = [];
        foreach ($kategoriDefault as $kategori) {
            $target = (int) ($targetByCategory[$kategori] ?? 0);
            $realisasi = (int) ($realisasiByKategori[$kategori] ?? 0);

            $rekapKategori[] = $this->summaryRow($kategori, 'Kategori KM', $target, $realisasi);
        }

        $targetExpressionLab = $this->periodExpression(
            'kl',
            'km_lab',
            'jumlah_km',
            $filters,
            $hasTriwulanKmLab
        );

        $labTargetRows = DB::table('km_lab as kl')
            ->join('laboratorium_riset as lr', 'kl.id_lab', '=', 'lr.id_lab')
            ->where('lr.id_kk', $idKk)
            ->where('kl.tahun_km', $filters['tahun'])
            ->where('kl.status_km', 'Aktif')
            ->select(
                'kl.id_lab',
                DB::raw('COALESCE(SUM(' . $targetExpressionLab . '), 0) as total_target')
            )
            ->groupBy('kl.id_lab')
            ->pluck('total_target', 'id_lab');

        $labRows = $labOptions->map(function ($lab) use ($labTargetRows, $realisasiByLab) {
            $target = (int) ($labTargetRows[$lab->id_lab] ?? 0);
            $realisasi = (int) ($realisasiByLab[$lab->id_lab] ?? 0);

            return $this->summaryRow(
                $lab->nama_lab,
                'Lab Riset',
                $target,
                $realisasi,
                [
                    'id_lab' => (int) $lab->id_lab,
                ]
            );
        })->values();

        $assignmentQuery = DB::table('km_anggota as ka')
            ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
            ->where('kl.tahun_km', $filters['tahun'])
            ->where('kl.status_km', 'Aktif')
            ->whereIn('kl.id_lab', $labIds)
            ->select(
                'ka.id_user',
                'ka.jumlah_km as jumlah_km_anggota',
                'kl.jumlah_km as jumlah_km_lab',
                'kl.kategori_km'
            );

        if ($hasTriwulanKmLab) {
            $assignmentQuery->addSelect(
                'kl.triwulan_1',
                'kl.triwulan_2',
                'kl.triwulan_3',
                'kl.triwulan_4'
            );
        }

        $assignments = $assignmentQuery->get();

        $memberTargetByUser = [];
        $memberTargetCategoryByUser = [];

        foreach ($anggotaOptions as $anggota) {
            $memberTargetByUser[$anggota->id_user] = 0;
            $memberTargetCategoryByUser[$anggota->id_user] = array_fill_keys($kategoriDefault, 0);
        }

        foreach ($assignments as $assignment) {
            $idUser = (int) $assignment->id_user;
            $kategori = $assignment->kategori_km;

            if (!array_key_exists($idUser, $memberTargetByUser)) {
                continue;
            }

            if ($hasTriwulanKmLab) {
                $twLab = [
                    1 => (int) ($assignment->triwulan_1 ?? 0),
                    2 => (int) ($assignment->triwulan_2 ?? 0),
                    3 => (int) ($assignment->triwulan_3 ?? 0),
                    4 => (int) ($assignment->triwulan_4 ?? 0),
                ];
            } else {
                $twLab = $this->splitEvenly((int) $assignment->jumlah_km_lab, 4);
            }

            $twAnggota = $this->distributeAssignedKm(
                (int) $assignment->jumlah_km_anggota,
                $twLab
            );

            $targetPeriode = $this->periodTargetFromTriwulan($twAnggota, $filters);

            $memberTargetByUser[$idUser] += $targetPeriode;

            if (array_key_exists($kategori, $memberTargetCategoryByUser[$idUser])) {
                $memberTargetCategoryByUser[$idUser][$kategori] += $targetPeriode;
            }
        }

        $realisasiKategoriByUser = [];
        foreach ($anggotaOptions as $anggota) {
            $realisasiKategoriByUser[$anggota->id_user] = array_fill_keys($kategoriDefault, 0);
        }

        foreach ($activities as $activity) {
            $idUser = (int) $activity->id_user;
            $kategori = $activity->kategori_km;

            if (isset($realisasiKategoriByUser[$idUser][$kategori])) {
                $realisasiKategoriByUser[$idUser][$kategori]++;
            }
        }

        $memberRows = $anggotaOptions->map(function ($anggota) use (
            $memberTargetByUser,
            $realisasiByUser,
            $memberTargetCategoryByUser,
            $realisasiKategoriByUser,
            $kategoriDefault
        ) {
            $target = (int) ($memberTargetByUser[$anggota->id_user] ?? 0);
            $realisasi = (int) ($realisasiByUser[$anggota->id_user] ?? 0);

            $row = $this->summaryRow(
                $anggota->nama_dosen ?? $anggota->username,
                $anggota->nama_lab ?? '-',
                $target,
                $realisasi,
                [
                    'id_user' => (int) $anggota->id_user,
                    'nidn' => $anggota->nidn ?? '-',
                    'jad' => $anggota->jad ?? '-',
                ]
            );

            $row['target_per_kategori'] = $memberTargetCategoryByUser[$anggota->id_user] ?? array_fill_keys($kategoriDefault, 0);
            $row['realisasi_per_kategori'] = $realisasiKategoriByUser[$anggota->id_user] ?? array_fill_keys($kategoriDefault, 0);

            return $row;
        })->values();

        $aktivitasRows = $activities->map(function ($activity) {
            return [
                'nama_anggota' => $activity->nama_dosen ?? $activity->username,
                'nama_lab' => $activity->nama_lab ?? '-',
                'kategori_km' => $activity->kategori_km ?? '-',
                'sub_kategori_km' => $activity->sub_kategori_km ?? '-',
                'judul_aktivitas' => $activity->judul_aktivitas ?? '-',
                'deskripsi_singkat' => $activity->deskripsi_singkat ?? '-',
                'tanggal_mulai' => $activity->tanggal_mulai ?? '-',
                'tanggal_selesai' => $activity->tanggal_selesai ?? '-',
                'status_progress' => $activity->status_progress ?? 'Accepted',
                'bukti_link' => $activity->bukti_link ?? '-',
                'id_user' => (int) $activity->id_user,
            ];
        })->values();

        $scopeTitle = 'Keseluruhan Kelompok Keahlian';
        $laporanRows = collect();
        $pdfAktivitasRows = collect();

        if ($filters['jenis_laporan'] === 'lab_semua') {
            $scopeTitle = 'Seluruh Lab Riset';
            $laporanRows = $labRows;
        }

        if ($filters['jenis_laporan'] === 'lab_satu') {
            $scopeTitle = 'Lab Riset Terpilih';
            $laporanRows = $labRows
                ->where('id_lab', $filters['id_lab'])
                ->values();
        }

        if ($filters['jenis_laporan'] === 'anggota_semua') {
            $scopeTitle = 'Seluruh Anggota KK';
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

        $summary = [
            'jumlah_lab' => $labOptions->count(),
            'jumlah_anggota' => $anggotaOptions->count(),
            'total_target' => $totalTargetKk,
            'total_realisasi' => $totalRealisasiKk,
            'total_sisa' => max($totalTargetKk - $totalRealisasiKk, 0),
            'persentase' => $totalTargetKk > 0
                ? min(round(($totalRealisasiKk / $totalTargetKk) * 100), 100)
                : 0,
        ];

        $exportSheets = $this->buildExportSheets(
            $filters,
            $summary,
            $rekapKategori,
            $detailTargetKategori,
            $labRows,
            $memberRows,
            $aktivitasRows
        );

        return compact(
            'filters',
            'tahunOptions',
            'labOptions',
            'anggotaOptions',
            'rekapKategori',
            'detailTargetKategori',
            'labRows',
            'memberRows',
            'aktivitasRows',
            'laporanRows',
            'pdfAktivitasRows',
            'summary',
            'scopeTitle',
            'exportSheets'
        );
    }

    /**
     * Menyusun rekap detail target KM yang dibuat Ketua KK.
     *
     * Target diambil dari target_km. Realisasi dicocokkan secara langsung
     * melalui aktivitas_km -> km_anggota -> km_lab -> target_km. Untuk data
     * lama yang belum mempunyai id_km_anggota, sistem memakai fallback
     * kategori + sub kategori/indikator.
     */
    private function buildTargetCategoryDetails(
        int $idDosenKetuaKk,
        int $idKk,
        array $filters,
        array $kategoriDefault
    ): array {
        $hasTriwulanTarget = Schema::hasColumns('target_km', [
            'triwulan_1',
            'triwulan_2',
            'triwulan_3',
            'triwulan_4',
        ]);

        $hasKeteranganTarget = Schema::hasColumn('target_km', 'keterangan');
        $hasIdKmAnggotaAktivitas = Schema::hasColumn('aktivitas_km', 'id_km_anggota');
        $hasIdTargetKmLab = Schema::hasColumn('km_lab', 'id_target');
        $hasStatusProgress = Schema::hasColumn('aktivitas_km', 'status_progress');
        $hasSubKategoriAktivitas = Schema::hasColumn('aktivitas_km', 'sub_kategori_km');

        $hasDeadlineTw1 = Schema::hasColumn('target_km', 'tanggal_mulai_tw1')
            && Schema::hasColumn('target_km', 'tanggal_selesai_tw1');
        $hasDeadlineTw2 = Schema::hasColumn('target_km', 'tanggal_mulai_tw2')
            && Schema::hasColumn('target_km', 'tanggal_selesai_tw2');
        $hasDeadlineTw3 = Schema::hasColumn('target_km', 'tanggal_mulai_tw3')
            && Schema::hasColumn('target_km', 'tanggal_selesai_tw3');
        $hasDeadlineTw4 = Schema::hasColumn('target_km', 'tanggal_mulai_tw4')
            && Schema::hasColumn('target_km', 'tanggal_selesai_tw4');

        $targetQuery = DB::table('target_km as tk')
            ->join('kontrak_manajemen as km', 'tk.id_km', '=', 'km.id_km')
            ->where('km.id_dosen', $idDosenKetuaKk)
            ->where('km.tahun_km', $filters['tahun'])
            ->select(
                'tk.id_target',
                'tk.kategori_km',
                'tk.indikator',
                'tk.target'
            )
            ->orderBy('tk.kategori_km')
            ->orderBy('tk.indikator');

        $targetQuery->addSelect(
            $hasKeteranganTarget
                ? 'tk.keterangan'
                : DB::raw('NULL as keterangan')
        );

        if ($hasTriwulanTarget) {
            $targetQuery->addSelect(
                'tk.triwulan_1',
                'tk.triwulan_2',
                'tk.triwulan_3',
                'tk.triwulan_4'
            );
        } else {
            $targetQuery->addSelect(
                DB::raw('0 as triwulan_1'),
                DB::raw('0 as triwulan_2'),
                DB::raw('0 as triwulan_3'),
                DB::raw('0 as triwulan_4')
            );
        }

        $targetQuery->addSelect(
            $hasDeadlineTw1
                ? 'tk.tanggal_mulai_tw1'
                : DB::raw('NULL as tanggal_mulai_tw1'),
            $hasDeadlineTw1
                ? 'tk.tanggal_selesai_tw1'
                : DB::raw('NULL as tanggal_selesai_tw1'),
            $hasDeadlineTw2
                ? 'tk.tanggal_mulai_tw2'
                : DB::raw('NULL as tanggal_mulai_tw2'),
            $hasDeadlineTw2
                ? 'tk.tanggal_selesai_tw2'
                : DB::raw('NULL as tanggal_selesai_tw2'),
            $hasDeadlineTw3
                ? 'tk.tanggal_mulai_tw3'
                : DB::raw('NULL as tanggal_mulai_tw3'),
            $hasDeadlineTw3
                ? 'tk.tanggal_selesai_tw3'
                : DB::raw('NULL as tanggal_selesai_tw3'),
            $hasDeadlineTw4
                ? 'tk.tanggal_mulai_tw4'
                : DB::raw('NULL as tanggal_mulai_tw4'),
            $hasDeadlineTw4
                ? 'tk.tanggal_selesai_tw4'
                : DB::raw('NULL as tanggal_selesai_tw4')
        );

        $targetRows = $targetQuery->get();

        if ($targetRows->isEmpty()) {
            return [];
        }

        $targetById = [];
        $fallbackTargetId = [];

        foreach ($targetRows as $target) {
            $targetById[(int) $target->id_target] = $target;

            $fallbackKey = $this->targetFallbackKey(
                (string) ($target->kategori_km ?? ''),
                (string) ($target->indikator ?? '')
            );

            if (!isset($fallbackTargetId[$fallbackKey])) {
                $fallbackTargetId[$fallbackKey] = (int) $target->id_target;
            }
        }

        $realisasiByTarget = [];
        foreach (array_keys($targetById) as $idTarget) {
            $realisasiByTarget[$idTarget] = [
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
            ];
        }

        $tahunMulai = Carbon::create($filters['tahun'], 1, 1)->startOfYear()->toDateString();
        $tahunSelesai = Carbon::create($filters['tahun'], 12, 31)->endOfYear()->toDateString();

        $aktivitasDetailQuery = DB::table('aktivitas_km as ak')
            ->join('users as u', 'ak.id_user', '=', 'u.id_user')
            ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
            ->leftJoin('laboratorium_riset as lr', function ($join) {
                $join->on('u.id_lab', '=', 'lr.id_lab')
                    ->orOn('d.id_lab', '=', 'lr.id_lab');
            })
            ->where('lr.id_kk', $idKk)
            ->whereBetween('ak.tanggal_mulai', [$tahunMulai, $tahunSelesai])
            ->select(
                'ak.id_aktivitas',
                'ak.id_user',
                'ak.kategori_km',
                'ak.tanggal_mulai'
            );

        if ($hasSubKategoriAktivitas) {
            $aktivitasDetailQuery->addSelect('ak.sub_kategori_km');
        } else {
            $aktivitasDetailQuery->addSelect(DB::raw('NULL as sub_kategori_km'));
        }

        if ($hasIdKmAnggotaAktivitas && $hasIdTargetKmLab) {
            $aktivitasDetailQuery
                ->leftJoin('km_anggota as ka', 'ak.id_km_anggota', '=', 'ka.id_km_anggota')
                ->leftJoin('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                ->addSelect('kl.id_target as linked_id_target');
        } else {
            $aktivitasDetailQuery->addSelect(DB::raw('NULL as linked_id_target'));
        }

        if ($hasStatusProgress) {
            $aktivitasDetailQuery->where('ak.status_progress', 'Accepted');
        }

        if ($filters['jenis_laporan'] === 'lab_satu' && !empty($filters['id_lab'])) {
            $aktivitasDetailQuery->where('lr.id_lab', $filters['id_lab']);
        }

        if ($filters['jenis_laporan'] === 'anggota_satu' && !empty($filters['id_user'])) {
            $aktivitasDetailQuery->where('ak.id_user', $filters['id_user']);
        }

        $aktivitasDetail = $aktivitasDetailQuery->get();

        foreach ($aktivitasDetail as $aktivitas) {
            $idTarget = !empty($aktivitas->linked_id_target)
                ? (int) $aktivitas->linked_id_target
                : null;

            if (!$idTarget || !isset($targetById[$idTarget])) {
                $fallbackKey = $this->targetFallbackKey(
                    (string) ($aktivitas->kategori_km ?? ''),
                    (string) ($aktivitas->sub_kategori_km ?? '')
                );

                $idTarget = $fallbackTargetId[$fallbackKey] ?? null;
            }

            if (!$idTarget || !isset($realisasiByTarget[$idTarget])) {
                continue;
            }

            try {
                $triwulan = (int) ceil(Carbon::parse($aktivitas->tanggal_mulai)->month / 3);
            } catch (\Throwable $exception) {
                continue;
            }

            if (isset($realisasiByTarget[$idTarget][$triwulan])) {
                $realisasiByTarget[$idTarget][$triwulan]++;
            }
        }

        $kategoriOrder = collect($kategoriDefault)
            ->merge(
                $targetRows->pluck('kategori_km')
                    ->filter()
                    ->values()
            )
            ->unique()
            ->values();

        $detailTargetKategori = [];

        foreach ($kategoriOrder as $kategori) {
            $rowsDalamKategori = $targetRows
                ->filter(fn ($target) => (string) $target->kategori_km === (string) $kategori)
                ->values();

            if ($rowsDalamKategori->isEmpty()) {
                continue;
            }

            $detailRows = $rowsDalamKategori->map(function ($target, int $index) use ($realisasiByTarget, $filters) {
                $idTarget = (int) $target->id_target;

                $targetTw = [
                    1 => (int) ($target->triwulan_1 ?? 0),
                    2 => (int) ($target->triwulan_2 ?? 0),
                    3 => (int) ($target->triwulan_3 ?? 0),
                    4 => (int) ($target->triwulan_4 ?? 0),
                ];

                $realisasiTw = $realisasiByTarget[$idTarget] ?? [
                    1 => 0,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                ];

                $totalTargetTahunan = max(
                    (int) ($target->target ?? 0),
                    array_sum($targetTw)
                );

                $targetPeriode = $this->periodTargetFromTriwulan($targetTw, $filters);
                $realisasiPeriode = $this->periodTargetFromTriwulan($realisasiTw, $filters);
                $sisaPeriode = max($targetPeriode - $realisasiPeriode, 0);
                $persentase = $targetPeriode > 0
                    ? min((int) round(($realisasiPeriode / $targetPeriode) * 100), 100)
                    : 0;

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
                    'no' => $index + 1,
                    'id_target' => $idTarget,
                    'sub_kategori' => $target->indikator ?? '-',
                    'keterangan' => $target->keterangan ?? '-',
                    'target_tw1' => $targetTw[1],
                    'target_tw2' => $targetTw[2],
                    'target_tw3' => $targetTw[3],
                    'target_tw4' => $targetTw[4],
                    'target_total_tahunan' => $totalTargetTahunan,
                    'target_periode' => $targetPeriode,
                    'realisasi_tw1' => (int) ($realisasiTw[1] ?? 0),
                    'realisasi_tw2' => (int) ($realisasiTw[2] ?? 0),
                    'realisasi_tw3' => (int) ($realisasiTw[3] ?? 0),
                    'realisasi_tw4' => (int) ($realisasiTw[4] ?? 0),
                    'realisasi_total_tahunan' => array_sum($realisasiTw),
                    'realisasi_periode' => $realisasiPeriode,
                    'sisa_periode' => $sisaPeriode,
                    'persentase' => $persentase,
                    'status' => $status,
                    'tanggal_mulai_tw1' => $this->formatReportDate($target->tanggal_mulai_tw1 ?? null),
                    'tanggal_selesai_tw1' => $this->formatReportDate($target->tanggal_selesai_tw1 ?? null),
                    'tanggal_mulai_tw2' => $this->formatReportDate($target->tanggal_mulai_tw2 ?? null),
                    'tanggal_selesai_tw2' => $this->formatReportDate($target->tanggal_selesai_tw2 ?? null),
                    'tanggal_mulai_tw3' => $this->formatReportDate($target->tanggal_mulai_tw3 ?? null),
                    'tanggal_selesai_tw3' => $this->formatReportDate($target->tanggal_selesai_tw3 ?? null),
                    'tanggal_mulai_tw4' => $this->formatReportDate($target->tanggal_mulai_tw4 ?? null),
                    'tanggal_selesai_tw4' => $this->formatReportDate($target->tanggal_selesai_tw4 ?? null),
                ];
            })->all();

            $detailTargetKategori[] = [
                'kategori' => $kategori,
                'jumlah_sub_kategori' => count($detailRows),
                'target_periode' => array_sum(array_column($detailRows, 'target_periode')),
                'realisasi_periode' => array_sum(array_column($detailRows, 'realisasi_periode')),
                'rows' => $detailRows,
            ];
        }

        return $detailTargetKategori;
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

    private function periodExpression(
        string $alias,
        string $table,
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

    private function splitEvenly(int $total, int $parts): array
    {
        $base = intdiv(max($total, 0), $parts);
        $sisa = max($total, 0) % $parts;
        $hasil = [];

        for ($index = 1; $index <= $parts; $index++) {
            $hasil[$index] = $base + ($index <= $sisa ? 1 : 0);
        }

        return $hasil;
    }

    private function distributeAssignedKm(int $jumlahAnggota, array $twLab): array
    {
        $hasil = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        $jumlahAnggota = max($jumlahAnggota, 0);
        $totalLab = array_sum($twLab);

        if ($jumlahAnggota <= 0) {
            return $hasil;
        }

        if ($totalLab <= 0) {
            return $this->splitEvenly($jumlahAnggota, 4);
        }

        $pecahan = [];
        $totalSementara = 0;

        foreach ([1, 2, 3, 4] as $tw) {
            $nilaiAsli = ($jumlahAnggota * (int) ($twLab[$tw] ?? 0)) / $totalLab;
            $nilaiBulat = (int) floor($nilaiAsli);

            $hasil[$tw] = $nilaiBulat;
            $totalSementara += $nilaiBulat;
            $pecahan[$tw] = $nilaiAsli - $nilaiBulat;
        }

        $sisa = $jumlahAnggota - $totalSementara;
        arsort($pecahan);

        foreach (array_keys($pecahan) as $tw) {
            if ($sisa <= 0) {
                break;
            }

            $hasil[$tw]++;
            $sisa--;
        }

        return $hasil;
    }

    private function periodTargetFromTriwulan(array $twValues, array $filters): int
    {
        if ($filters['mode_periode'] === 'tahun') {
            return array_sum($twValues);
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
            ? min(round(($realisasi / $target) * 100), 100)
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

    private function buildExportSheets(
        array $filters,
        array $summary,
        array $rekapKategori,
        array $detailTargetKategori,
        $labRows,
        $memberRows,
        $aktivitasRows
    ): array {
        $ringkasanRows = [
            ['Periode', $filters['label_periode']],
            ['Jumlah Lab Riset', $summary['jumlah_lab']],
            ['Jumlah Anggota KK', $summary['jumlah_anggota']],
            ['Total Target KK', $summary['total_target']],
            ['Total Realisasi', $summary['total_realisasi']],
            ['Sisa Target', $summary['total_sisa']],
            ['Persentase Capaian', $summary['persentase'] . '%'],
        ];

        $kategoriRows = collect($rekapKategori)->map(function ($row) {
            return [
                $row['nama'] ?? '-',
                $row['target'] ?? 0,
                $row['realisasi'] ?? 0,
                $row['sisa'] ?? 0,
                ($row['persentase'] ?? 0) . '%',
                $row['status'] ?? 'Belum Tercapai',
            ];
        })->all();

        $labExportRows = collect($labRows)->map(function ($row) {
            return [
                $row['nama'] ?? '-',
                $row['target'] ?? 0,
                $row['realisasi'] ?? 0,
                $row['sisa'] ?? 0,
                ($row['persentase'] ?? 0) . '%',
                $row['status'] ?? 'Belum Tercapai',
            ];
        })->all();

        $memberExportRows = collect($memberRows)->map(function ($row) {
            return [
                $row['nama'] ?? '-',
                $row['nidn'] ?? '-',
                $row['jad'] ?? '-',
                $row['keterangan'] ?? '-',
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
                $row['nama_lab'] ?? '-',
                $row['kategori_km'] ?? '-',
                $row['sub_kategori_km'] ?? '-',
                $row['judul_aktivitas'] ?? '-',
                $row['deskripsi_singkat'] ?? '-',
                $row['tanggal_mulai'] ?? '-',
                $row['tanggal_selesai'] ?? '-',
                $row['status_progress'] ?? '-',
                $row['bukti_link'] ?? '-',
            ];
        })->all();

        $detailTargetRows = collect($detailTargetKategori)
            ->flatMap(function (array $group) {
                return collect($group['rows'] ?? [])->map(function (array $row) use ($group) {
                    return [
                        $group['kategori'] ?? '-',
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
                        $row['sisa_periode'] ?? 0,
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

        return [
            [
                'title' => 'Ringkasan KK',
                'headings' => ['Indikator', 'Nilai'],
                'rows' => $ringkasanRows,
            ],
            [
                'title' => 'Rekap Kategori',
                'headings' => [
                    'Kategori KM',
                    'Target',
                    'Realisasi',
                    'Sisa',
                    'Progress',
                    'Status',
                ],
                'rows' => $kategoriRows,
            ],
            [
                'title' => 'Detail Target KM',
                'headings' => [
                    'Kategori KM',
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
                'title' => 'Lab Riset',
                'headings' => [
                    'Nama Lab Riset',
                    'Target',
                    'Realisasi',
                    'Sisa',
                    'Progress',
                    'Status',
                ],
                'rows' => $labExportRows,
            ],
            [
                'title' => 'Anggota KK',
                'headings' => [
                    'Nama Anggota',
                    'NIDN',
                    'JAD',
                    'Lab Riset',
                    'Target',
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
                    'Lab Riset',
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

        $filename = 'reports-ketua-kk-' . $timestamp . '-csv.zip';
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