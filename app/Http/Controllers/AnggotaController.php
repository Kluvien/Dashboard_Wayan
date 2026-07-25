<?php

namespace App\Http\Controllers;

use App\Models\RealisasiKm;
use App\Models\TargetKm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnggotaController extends Controller
{
    /**
     * Dashboard anggota dengan filter Tahunan, Triwulan, dan Semester.
     */
    public function dashboard(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();
        $idUser = $user->id_user;

        /*
        |--------------------------------------------------------------------------
        | Filter waktu
        |--------------------------------------------------------------------------
        */
        $tahun = (int) $request->query('tahun', now()->year);
        $periode = strtolower((string) $request->query('periode', 'tahun'));

        if (! in_array($periode, ['tahun', 'triwulan', 'semester'], true)) {
            $periode = 'tahun';
        }

        $triwulan = (int) $request->query('triwulan', (int) ceil(now()->month / 3));
        $triwulan = max(1, min(4, $triwulan));

        $semester = (int) $request->query('semester', now()->month <= 6 ? 1 : 2);
        $semester = max(1, min(2, $semester));

        if ($periode === 'triwulan') {
            $bulanMulai = (($triwulan - 1) * 3) + 1;
            $bulanSelesai = $bulanMulai + 2;
            $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
            $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
            $triwulanTerpilih = [$triwulan];
            $labelPeriode = 'Triwulan ' . $triwulan . ' Tahun ' . $tahun;
            $keteranganPeriode = 'Data target dan realisasi ditampilkan untuk Triwulan ' . $triwulan . '.';
        } elseif ($periode === 'semester') {
            $bulanMulai = $semester === 1 ? 1 : 7;
            $bulanSelesai = $semester === 1 ? 6 : 12;
            $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
            $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
            $triwulanTerpilih = $semester === 1 ? [1, 2] : [3, 4];
            $labelPeriode = 'Semester ' . $semester . ' Tahun ' . $tahun;
            $keteranganPeriode = 'Data target dan realisasi ditampilkan untuk Semester ' . $semester . '.';
        } else {
            $periode = 'tahun';
            $tanggalMulai = \Carbon\Carbon::create($tahun, 1, 1)->startOfYear();
            $tanggalSelesai = \Carbon\Carbon::create($tahun, 12, 31)->endOfYear();
            $triwulanTerpilih = [1, 2, 3, 4];
            $labelPeriode = 'Tahunan ' . $tahun;
            $keteranganPeriode = 'Data target dan realisasi ditampilkan untuk satu tahun penuh.';
        }

        /*
        |--------------------------------------------------------------------------
        | Data anggota dan Lab Riset
        |--------------------------------------------------------------------------
        */
        $anggotaInfo = DB::table('users as u')
            ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
            ->leftJoin('laboratorium_riset as lr', function ($join) {
                $join->on('u.id_lab', '=', 'lr.id_lab')
                    ->orOn('d.id_lab', '=', 'lr.id_lab');
            })
            ->where('u.id_user', $idUser)
            ->select(
                'u.id_user',
                'u.username',
                'u.id_dosen',
                'u.id_lab as user_id_lab',
                'd.nama_dosen',
                'd.nidn',
                'd.email',
                'd.jad',
                'd.id_lab as dosen_id_lab',
                'lr.nama_lab'
            )
            ->first();

        $idLab = $user->id_lab
            ?? ($anggotaInfo->user_id_lab ?? null)
            ?? ($anggotaInfo->dosen_id_lab ?? null);

        $namaAnggota = $anggotaInfo->nama_dosen
            ?? $anggotaInfo->username
            ?? 'Anggota';

        $namaLab = $anggotaInfo->nama_lab ?? '-';
        $jadAnggota = $anggotaInfo->jad ?? '-';
        $nidnAnggota = $anggotaInfo->nidn ?? '-';

        /*
        |--------------------------------------------------------------------------
        | Pilihan tahun dari target KM yang pernah dibagikan ke anggota
        |--------------------------------------------------------------------------
        */
        $tahunOptions = DB::table('km_anggota as ka')
            ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
            ->where('ka.id_user', $idUser)
            ->whereNotNull('kl.tahun_km')
            ->where('kl.kategori_km', '!=', 'Pendidikan')
            ->distinct()
            ->orderByDesc('kl.tahun_km')
            ->pluck('kl.tahun_km')
            ->map(fn ($item) => (int) $item)
            ->push((int) now()->year)
            ->push($tahun)
            ->unique()
            ->sortDesc()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Pemeriksaan kolom database
        |--------------------------------------------------------------------------
        */
        $hasKmLabIdTarget = Schema::hasColumn('km_lab', 'id_target');
        $hasKmLabSubKategori = Schema::hasColumn('km_lab', 'sub_kategori_km');

        $hasKmAnggotaTriwulan =
            Schema::hasColumn('km_anggota', 'triwulan_1') &&
            Schema::hasColumn('km_anggota', 'triwulan_2') &&
            Schema::hasColumn('km_anggota', 'triwulan_3') &&
            Schema::hasColumn('km_anggota', 'triwulan_4');

        $hasKmLabTriwulan =
            Schema::hasColumn('km_lab', 'triwulan_1') &&
            Schema::hasColumn('km_lab', 'triwulan_2') &&
            Schema::hasColumn('km_lab', 'triwulan_3') &&
            Schema::hasColumn('km_lab', 'triwulan_4');

        $hasAktivitasIdKmAnggota = Schema::hasColumn('aktivitas_km', 'id_km_anggota');
        $hasAktivitasStatus = Schema::hasColumn('aktivitas_km', 'status_progress');

        $hasDeadlineTw1 = Schema::hasColumn('target_km', 'tanggal_selesai_tw1');
        $hasDeadlineTw2 = Schema::hasColumn('target_km', 'tanggal_selesai_tw2');
        $hasDeadlineTw3 = Schema::hasColumn('target_km', 'tanggal_selesai_tw3');
        $hasDeadlineTw4 = Schema::hasColumn('target_km', 'tanggal_selesai_tw4');

        /*
        |--------------------------------------------------------------------------
        | Rekap aktivitas per target untuk periode aktif
        |--------------------------------------------------------------------------
        */
        $realisasiPerTarget = collect();

        if ($hasAktivitasIdKmAnggota) {
            $queryRealisasi = DB::table('aktivitas_km')
                ->where('id_user', $idUser)
                ->whereNotNull('id_km_anggota')
                ->whereDate('tanggal_mulai', '>=', $tanggalMulai->toDateString())
                ->whereDate('tanggal_mulai', '<=', $tanggalSelesai->toDateString())
                ->select(
                    'id_km_anggota',
                    DB::raw('COUNT(*) as total_aktivitas')
                );

            if ($hasAktivitasStatus) {
                $queryRealisasi->addSelect(
                    DB::raw("SUM(CASE WHEN status_progress = 'Accepted' THEN 1 ELSE 0 END) as total_realisasi"),
                    DB::raw("SUM(CASE WHEN status_progress = 'Submitted' THEN 1 ELSE 0 END) as total_submitted"),
                    DB::raw("SUM(CASE WHEN status_progress = 'On Progress' THEN 1 ELSE 0 END) as total_on_progress")
                );
            } else {
                $queryRealisasi->addSelect(
                    DB::raw('COUNT(*) as total_realisasi'),
                    DB::raw('0 as total_submitted'),
                    DB::raw('0 as total_on_progress')
                );
            }

            $realisasiPerTarget = $queryRealisasi
                ->groupBy('id_km_anggota')
                ->get()
                ->keyBy('id_km_anggota');
        }

        /*
        |--------------------------------------------------------------------------
        | Realisasi Accepted tahunan per target dan per triwulan
        |--------------------------------------------------------------------------
        | Dipakai oleh tabel Daftar Target KM agar seluruh target dan
        | realisasi TW1-TW4 tetap terlihat, meskipun filter dashboard
        | sedang menggunakan Triwulan atau Semester tertentu.
        */
        $realisasiTahunanPerTarget = [];

        if ($hasAktivitasIdKmAnggota) {
            $aktivitasTahunanQuery = DB::table('aktivitas_km as ak')
                ->join('km_anggota as ka', 'ak.id_km_anggota', '=', 'ka.id_km_anggota')
                ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                ->where('ak.id_user', $idUser)
                ->where('kl.tahun_km', $tahun)
                ->where('kl.status_km', 'Aktif')
                ->where('kl.kategori_km', '!=', 'Pendidikan')
                ->whereNotNull('ak.id_km_anggota')
                ->whereNotNull('ak.tanggal_mulai')
                ->whereDate('ak.tanggal_mulai', '>=', \Carbon\Carbon::create($tahun, 1, 1)->toDateString())
                ->whereDate('ak.tanggal_mulai', '<=', \Carbon\Carbon::create($tahun, 12, 31)->toDateString())
                ->select(
                    'ak.id_km_anggota',
                    'ak.tanggal_mulai'
                );

            if ($hasAktivitasStatus) {
                $aktivitasTahunanQuery->addSelect('ak.status_progress');
            }

            $aktivitasTahunan = $aktivitasTahunanQuery->get();

            foreach ($aktivitasTahunan as $aktivitasTahunanItem) {
                $idKmAnggota = (int) $aktivitasTahunanItem->id_km_anggota;

                if (!isset($realisasiTahunanPerTarget[$idKmAnggota])) {
                    $realisasiTahunanPerTarget[$idKmAnggota] = [
                        'total_aktivitas' => 0,
                        'total_realisasi' => 0,
                        'realisasi_tw' => [
                            1 => 0,
                            2 => 0,
                            3 => 0,
                            4 => 0,
                        ],
                    ];
                }

                $realisasiTahunanPerTarget[$idKmAnggota]['total_aktivitas']++;

                $isAccepted = !$hasAktivitasStatus
                    || (($aktivitasTahunanItem->status_progress ?? null) === 'Accepted');

                if ($isAccepted) {
                    $realisasiTahunanPerTarget[$idKmAnggota]['total_realisasi']++;

                    try {
                        $bulanAktivitas = \Carbon\Carbon::parse($aktivitasTahunanItem->tanggal_mulai)->month;
                        $nomorTriwulan = (int) ceil($bulanAktivitas / 3);

                        $realisasiTahunanPerTarget[$idKmAnggota]['realisasi_tw'][$nomorTriwulan]++;
                    } catch (\Throwable $e) {
                        // Abaikan tanggal aktivitas yang tidak valid.
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Target KM anggota pada tahun yang dipilih
        |--------------------------------------------------------------------------
        */
        $targetQuery = DB::table('km_anggota as ka')
            ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
            ->leftJoin('laboratorium_riset as lr', 'kl.id_lab', '=', 'lr.id_lab')
            ->where('ka.id_user', $idUser)
            ->where('kl.tahun_km', $tahun)
            ->where('kl.status_km', 'Aktif')
            ->where('kl.kategori_km', '!=', 'Pendidikan')
            ->select(
                'ka.id_km_anggota',
                'ka.id_km_lab',
                'ka.id_user',
                'ka.jumlah_km',
                'ka.created_at as tanggal_assign',
                'kl.tahun_km',
                'kl.kategori_km',
                'lr.nama_lab'
            )
            ->orderBy('kl.kategori_km')
            ->orderBy('ka.created_at');

        if ($hasKmLabSubKategori) {
            $targetQuery->addSelect('kl.sub_kategori_km');
        } else {
            $targetQuery->addSelect(DB::raw("'-' as sub_kategori_km"));
        }

        if ($hasKmLabIdTarget) {
            $targetQuery->leftJoin('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                ->addSelect(DB::raw("COALESCE(NULLIF(tk.keterangan, ''), '-') as keterangan"));

            $targetQuery->addSelect(
                $hasDeadlineTw1 ? 'tk.tanggal_selesai_tw1' : DB::raw('NULL as tanggal_selesai_tw1'),
                $hasDeadlineTw2 ? 'tk.tanggal_selesai_tw2' : DB::raw('NULL as tanggal_selesai_tw2'),
                $hasDeadlineTw3 ? 'tk.tanggal_selesai_tw3' : DB::raw('NULL as tanggal_selesai_tw3'),
                $hasDeadlineTw4 ? 'tk.tanggal_selesai_tw4' : DB::raw('NULL as tanggal_selesai_tw4')
            );
        } else {
            $targetQuery->addSelect(
                DB::raw("'-' as keterangan"),
                DB::raw('NULL as tanggal_selesai_tw1'),
                DB::raw('NULL as tanggal_selesai_tw2'),
                DB::raw('NULL as tanggal_selesai_tw3'),
                DB::raw('NULL as tanggal_selesai_tw4')
            );
        }

        if ($hasKmAnggotaTriwulan) {
            $targetQuery->addSelect(
                'ka.triwulan_1',
                'ka.triwulan_2',
                'ka.triwulan_3',
                'ka.triwulan_4'
            );
        } elseif ($hasKmLabTriwulan) {
            $targetQuery->addSelect(
                'kl.triwulan_1',
                'kl.triwulan_2',
                'kl.triwulan_3',
                'kl.triwulan_4'
            );
        } else {
            $targetQuery->addSelect(
                DB::raw('0 as triwulan_1'),
                DB::raw('0 as triwulan_2'),
                DB::raw('0 as triwulan_3'),
                DB::raw('0 as triwulan_4')
            );
        }

        $targetKmDasar = $targetQuery->get();

        /*
        |--------------------------------------------------------------------------
        | Fallback pembagian target jika data lama belum memiliki TW1-TW4
        |--------------------------------------------------------------------------
        */
        $bagiRataTriwulan = function (int $jumlahKm): array {
            $jumlahKm = max(0, $jumlahKm);
            $dasar = intdiv($jumlahKm, 4);
            $sisa = $jumlahKm % 4;

            return [
                1 => $dasar + ($sisa >= 1 ? 1 : 0),
                2 => $dasar + ($sisa >= 2 ? 1 : 0),
                3 => $dasar + ($sisa >= 3 ? 1 : 0),
                4 => $dasar,
            ];
        };

        /*
        |--------------------------------------------------------------------------
        | Data tabel target tahunan: Target KM TW1-TW4 dan Realisasi KM TW1-TW4
        |--------------------------------------------------------------------------
        */
        $targetKmTahunan = $targetKmDasar
            ->map(function ($item) use ($realisasiTahunanPerTarget, $bagiRataTriwulan) {
                $item = clone $item;

                $jumlahKmTahunan = (int) ($item->jumlah_km ?? 0);

                $targetPerTriwulan = [
                    1 => (int) ($item->triwulan_1 ?? 0),
                    2 => (int) ($item->triwulan_2 ?? 0),
                    3 => (int) ($item->triwulan_3 ?? 0),
                    4 => (int) ($item->triwulan_4 ?? 0),
                ];

                if (array_sum($targetPerTriwulan) <= 0 && $jumlahKmTahunan > 0) {
                    $targetPerTriwulan = $bagiRataTriwulan($jumlahKmTahunan);
                }

                if ($jumlahKmTahunan <= 0) {
                    $jumlahKmTahunan = array_sum($targetPerTriwulan);
                }

                $rekapTahunan = $realisasiTahunanPerTarget[(int) $item->id_km_anggota] ?? [
                    'total_aktivitas' => 0,
                    'total_realisasi' => 0,
                    'realisasi_tw' => [1 => 0, 2 => 0, 3 => 0, 4 => 0],
                ];

                $totalAktivitasTahunan = (int) ($rekapTahunan['total_aktivitas'] ?? 0);
                $totalRealisasiTahunan = (int) ($rekapTahunan['total_realisasi'] ?? 0);
                $sisaTahunan = max($jumlahKmTahunan - $totalRealisasiTahunan, 0);

                if ($jumlahKmTahunan <= 0) {
                    $statusTahunan = 'Belum Ada Target';
                    $statusTahunanClass = 'secondary';
                } elseif ($totalRealisasiTahunan >= $jumlahKmTahunan) {
                    $statusTahunan = 'Tercapai';
                    $statusTahunanClass = 'success';
                } elseif ($totalAktivitasTahunan > 0) {
                    $statusTahunan = 'Sedang Berjalan';
                    $statusTahunanClass = 'warning';
                } else {
                    $statusTahunan = 'Belum Mulai';
                    $statusTahunanClass = 'danger';
                }

                $item->target_tw_1 = $targetPerTriwulan[1];
                $item->target_tw_2 = $targetPerTriwulan[2];
                $item->target_tw_3 = $targetPerTriwulan[3];
                $item->target_tw_4 = $targetPerTriwulan[4];

                $item->realisasi_tw_1 = (int) ($rekapTahunan['realisasi_tw'][1] ?? 0);
                $item->realisasi_tw_2 = (int) ($rekapTahunan['realisasi_tw'][2] ?? 0);
                $item->realisasi_tw_3 = (int) ($rekapTahunan['realisasi_tw'][3] ?? 0);
                $item->realisasi_tw_4 = (int) ($rekapTahunan['realisasi_tw'][4] ?? 0);

                $item->jumlah_km_tahunan = $jumlahKmTahunan;
                $item->total_aktivitas_tahunan = $totalAktivitasTahunan;
                $item->total_realisasi_tahunan = $totalRealisasiTahunan;
                $item->sisa_km_tahunan = $sisaTahunan;
                $item->status_tahunan = $statusTahunan;
                $item->status_tahunan_class = $statusTahunanClass;

                return $item;
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Data untuk ringkasan, card, grafik, dan filter periode aktif
        |--------------------------------------------------------------------------
        */
        $targetKmSaya = $targetKmTahunan
            ->map(function ($item) use ($realisasiPerTarget, $triwulanTerpilih) {
                $item = clone $item;

                $targetPeriode = 0;
                $targetPerTriwulan = [
                    1 => (int) ($item->target_tw_1 ?? 0),
                    2 => (int) ($item->target_tw_2 ?? 0),
                    3 => (int) ($item->target_tw_3 ?? 0),
                    4 => (int) ($item->target_tw_4 ?? 0),
                ];

                foreach ($triwulanTerpilih as $nomorTriwulan) {
                    $targetPeriode += (int) ($targetPerTriwulan[$nomorTriwulan] ?? 0);
                }

                $rekapPeriode = $realisasiPerTarget->get($item->id_km_anggota);

                $totalAktivitas = (int) ($rekapPeriode->total_aktivitas ?? 0);
                $totalRealisasi = (int) ($rekapPeriode->total_realisasi ?? 0);
                $totalSubmitted = (int) ($rekapPeriode->total_submitted ?? 0);
                $totalOnProgress = (int) ($rekapPeriode->total_on_progress ?? 0);
                $sisa = max($targetPeriode - $totalRealisasi, 0);

                $persentase = $targetPeriode > 0
                    ? (int) round(($totalRealisasi / $targetPeriode) * 100)
                    : 0;

                if ($targetPeriode <= 0) {
                    $status = 'Belum Ada Target';
                    $statusClass = 'secondary';
                } elseif ($totalRealisasi >= $targetPeriode) {
                    $status = 'Tercapai';
                    $statusClass = 'success';
                } elseif ($totalAktivitas > 0 || $totalSubmitted > 0 || $totalOnProgress > 0) {
                    $status = 'Sedang Berjalan';
                    $statusClass = 'warning';
                } else {
                    $status = 'Belum Mulai';
                    $statusClass = 'danger';
                }

                $item->target_periode = $targetPeriode;
                $item->total_aktivitas = $totalAktivitas;
                $item->total_realisasi = $totalRealisasi;
                $item->total_submitted = $totalSubmitted;
                $item->total_on_progress = $totalOnProgress;
                $item->sisa_km = $sisa;
                $item->persentase = min($persentase, 100);
                $item->status_target = $status;
                $item->status_class = $statusClass;

                return $item;
            })
            ->filter(fn ($item) => (int) $item->target_periode > 0)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Ringkasan dashboard
        |--------------------------------------------------------------------------
        */
        $kategoriDefault = [
            'Penelitian',
            'Publikasi',
            'Pengabdian',
            'Penunjang',
        ];

        $totalTarget = (int) $targetKmSaya->sum('target_periode');
        $totalRealisasi = (int) $targetKmSaya->sum('total_realisasi');
        $totalSisa = max($totalTarget - $totalRealisasi, 0);

        $persentaseTotal = $totalTarget > 0
            ? (int) round(($totalRealisasi / $totalTarget) * 100)
            : 0;

        $jumlahTargetAktif = (int) $targetKmSaya->count();
        $jumlahKategoriAktif = (int) $targetKmSaya
            ->pluck('kategori_km')
            ->filter()
            ->unique()
            ->count();

        $jumlahSubmitted = (int) $targetKmSaya->sum('total_submitted');
        $jumlahOnProgress = (int) $targetKmSaya->sum('total_on_progress');

        $filterQuery = http_build_query([
            'periode' => $periode,
            'tahun' => $tahun,
            'triwulan' => $triwulan,
            'semester' => $semester,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Card per kategori KM
        |--------------------------------------------------------------------------
        */
        $kategoriCards = collect($kategoriDefault)->map(function ($kategori) use ($targetKmSaya, $filterQuery) {
            $rows = $targetKmSaya->where('kategori_km', $kategori);

            $target = (int) $rows->sum('target_periode');
            $realisasi = (int) $rows->sum('total_realisasi');
            $sisa = max($target - $realisasi, 0);

            $persentase = $target > 0
                ? (int) round(($realisasi / $target) * 100)
                : 0;

            $jumlahSubKategori = (int) $rows
                ->pluck('sub_kategori_km')
                ->filter()
                ->unique()
                ->count();

            if ($target > 0 && $realisasi >= $target) {
                $catatan = 'Seluruh target kategori pada periode ini sudah tercapai.';
                $catatanClass = 'success';
            } elseif ($target > 0) {
                $catatan = 'Masih ada ' . $sisa . ' KM yang belum selesai pada periode ini.';
                $catatanClass = 'danger';
            } else {
                $catatan = 'Belum ada target pada kategori ini untuk periode terpilih.';
                $catatanClass = 'secondary';
            }

            return [
                'kategori' => $kategori,
                'target' => $target,
                'realisasi' => $realisasi,
                'sisa' => $sisa,
                'persentase' => min($persentase, 100),
                'jumlah_subkategori' => $jumlahSubKategori,
                'catatan' => $catatan,
                'catatan_class' => $catatanClass,
                'detail_url' => url('/anggota/progress-km?' . $filterQuery . '&kategori=' . urlencode($kategori)),
            ];
        });

        $chartKategori = [
            'labels' => $kategoriCards->pluck('kategori')->values()->all(),
            'target' => $kategoriCards->pluck('target')->map(fn ($item) => (int) $item)->values()->all(),
            'realisasi' => $kategoriCards->pluck('realisasi')->map(fn ($item) => (int) $item)->values()->all(),
            'sisa' => $kategoriCards->pluck('sisa')->map(fn ($item) => (int) $item)->values()->all(),
        ];

        $detailKategoriCharts = collect($kategoriDefault)->map(function ($kategori) use ($targetKmSaya) {
            $rows = $targetKmSaya->where('kategori_km', $kategori);

            $grouped = $rows
                ->groupBy(fn ($item) => $item->sub_kategori_km ?: 'Tanpa Sub Kategori')
                ->map(function ($items, $subKategori) {
                    return [
                        'sub_kategori' => $subKategori,
                        'target' => (int) $items->sum('target_periode'),
                        'realisasi' => (int) $items->sum('total_realisasi'),
                    ];
                })
                ->values();

            return [
                'kategori' => $kategori,
                'labels' => $grouped->pluck('sub_kategori')->values()->all(),
                'target' => $grouped->pluck('target')->map(fn ($item) => (int) $item)->values()->all(),
                'realisasi' => $grouped->pluck('realisasi')->map(fn ($item) => (int) $item)->values()->all(),
            ];
        })->values();

        /*
        |--------------------------------------------------------------------------
        | Riwayat aktivitas anggota pada periode yang dipilih
        |--------------------------------------------------------------------------
        | Keterangan KM diambil dari target_km melalui relasi:
        | aktivitas_km -> km_anggota -> km_lab -> target_km.
        */
        $historyQuery = DB::table('aktivitas_km as ak')
            ->where('ak.id_user', $idUser)
            ->where('ak.kategori_km', '!=', 'Pendidikan')
            ->whereDate('ak.tanggal_mulai', '>=', $tanggalMulai->toDateString())
            ->whereDate('ak.tanggal_mulai', '<=', $tanggalSelesai->toDateString())
            ->orderByDesc('ak.updated_at')
            ->orderByDesc('ak.created_at')
            ->limit(15)
            ->select(
                'ak.id_aktivitas',
                'ak.kategori_km',
                'ak.sub_kategori_km',
                'ak.judul_aktivitas',
                'ak.status_progress',
                'ak.tanggal_mulai',
                'ak.tanggal_selesai',
                'ak.created_at',
                'ak.updated_at'
            );

        if ($hasAktivitasIdKmAnggota) {
            $historyQuery
                ->leftJoin('km_anggota as ka', 'ak.id_km_anggota', '=', 'ka.id_km_anggota')
                ->leftJoin('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab');

            if ($hasKmLabIdTarget) {
                $historyQuery
                    ->leftJoin('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                    ->addSelect(
                        DB::raw("COALESCE(NULLIF(tk.keterangan, ''), '-') as keterangan_km")
                    );
            } else {
                $historyQuery->addSelect(DB::raw("'-' as keterangan_km"));
            }
        } else {
            $historyQuery->addSelect(DB::raw("'-' as keterangan_km"));
        }

        $historyAktivitas = $historyQuery->get();

        /*
        |--------------------------------------------------------------------------
        | Status hasil verifikasi untuk Dashboard Anggota
        |--------------------------------------------------------------------------
        | Panel ini hanya menampilkan notifikasi hasil verifikasi yang belum dibaca.
        | Sesudah Dashboard dibuka, notifikasi ditandai sudah dibaca agar panel tidak
        | muncul lagi pada kunjungan berikutnya. Riwayat tetap tersimpan di menu
        | Notifikasi dan pada halaman detail aktivitas.
        */
        $statusPengajuanKm = collect();

        if (Schema::hasTable('notifikasi')) {
            $kolomNotifikasi = Schema::getColumnListing('notifikasi');

            $kolomMinimalAda =
                in_array('id_notifikasi', $kolomNotifikasi, true) &&
                in_array('id_user', $kolomNotifikasi, true) &&
                in_array('jenis_notifikasi', $kolomNotifikasi, true) &&
                in_array('dibaca_pada', $kolomNotifikasi, true);

            if ($kolomMinimalAda) {
                $notifikasiVerifikasi = DB::table('notifikasi')
                    ->where('id_user', $idUser)
                    ->whereNull('dibaca_pada')
                    ->whereIn('jenis_notifikasi', [
                        'aktivitas_km_disetujui',
                        'aktivitas_km_ditolak',
                    ])
                    ->orderByDesc('created_at')
                    ->limit(6)
                    ->get();

                $idAktivitasDariNotifikasi = $notifikasiVerifikasi
                    ->map(function ($notifikasi) {
                        $kodeUnik = (string) ($notifikasi->kode_unik ?? '');
                        $urlTujuan = (string) ($notifikasi->url_tujuan ?? '');

                        if (preg_match('/^AKM-(?:ACC|TOLAK)-(\d+)-/i', $kodeUnik, $matches)) {
                            return (int) $matches[1];
                        }

                        if (preg_match('#/anggota/aktivitas-km/(\d+)/detail#', $urlTujuan, $matches)) {
                            return (int) $matches[1];
                        }

                        return null;
                    })
                    ->filter()
                    ->unique()
                    ->values();

                $aktivitasVerifikasiById = collect();

                if ($idAktivitasDariNotifikasi->isNotEmpty()) {
                    $hasKolomVerifikasi =
                        Schema::hasColumn('aktivitas_km', 'catatan_verifikasi') &&
                        Schema::hasColumn('aktivitas_km', 'diverifikasi_pada');

                    $queryAktivitasVerifikasi = DB::table('aktivitas_km as ak')
                        ->where('ak.id_user', $idUser)
                        ->whereIn('ak.id_aktivitas', $idAktivitasDariNotifikasi)
                        ->select(
                            'ak.id_aktivitas',
                            'ak.kategori_km',
                            'ak.sub_kategori_km',
                            'ak.judul_aktivitas',
                            'ak.status_progress',
                            'ak.tanggal_mulai',
                            'ak.tanggal_selesai',
                            'ak.created_at',
                            'ak.updated_at',
                            $hasKolomVerifikasi
                                ? 'ak.catatan_verifikasi'
                                : DB::raw('NULL as catatan_verifikasi'),
                            $hasKolomVerifikasi
                                ? 'ak.diverifikasi_pada'
                                : DB::raw('NULL as diverifikasi_pada')
                        );

                    if ($hasAktivitasIdKmAnggota) {
                        $queryAktivitasVerifikasi
                            ->leftJoin('km_anggota as ka', 'ak.id_km_anggota', '=', 'ka.id_km_anggota')
                            ->leftJoin('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab');

                        if ($hasKmLabIdTarget) {
                            $queryAktivitasVerifikasi
                                ->leftJoin('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                                ->addSelect(
                                    DB::raw("COALESCE(NULLIF(tk.keterangan, ''), '-') as keterangan_km")
                                );
                        } else {
                            $queryAktivitasVerifikasi->addSelect(DB::raw("'-' as keterangan_km"));
                        }
                    } else {
                        $queryAktivitasVerifikasi->addSelect(DB::raw("'-' as keterangan_km"));
                    }

                    $aktivitasVerifikasiById = $queryAktivitasVerifikasi
                        ->get()
                        ->keyBy('id_aktivitas');
                }

                $idNotifikasiDitampilkan = collect();

                foreach ($notifikasiVerifikasi as $notifikasi) {
                    $kodeUnik = (string) ($notifikasi->kode_unik ?? '');
                    $urlTujuan = (string) ($notifikasi->url_tujuan ?? '');
                    $idAktivitas = null;

                    if (preg_match('/^AKM-(?:ACC|TOLAK)-(\d+)-/i', $kodeUnik, $matches)) {
                        $idAktivitas = (int) $matches[1];
                    } elseif (preg_match('#/anggota/aktivitas-km/(\d+)/detail#', $urlTujuan, $matches)) {
                        $idAktivitas = (int) $matches[1];
                    }

                    $aktivitasVerifikasi = $idAktivitas
                        ? $aktivitasVerifikasiById->get($idAktivitas)
                        : null;

                    if (! $aktivitasVerifikasi) {
                        continue;
                    }

                    $status = (string) ($aktivitasVerifikasi->status_progress ?? '');

                    if (! in_array($status, ['Accepted', 'Rejected'], true)) {
                        continue;
                    }

                    $statusPengajuanKm->push((object) [
                        'id_notifikasi' => $notifikasi->id_notifikasi,
                        'id_aktivitas' => $aktivitasVerifikasi->id_aktivitas,
                        'status_progress' => $status,
                        'judul_aktivitas' => $aktivitasVerifikasi->judul_aktivitas ?? '-',
                        'kategori_km' => $aktivitasVerifikasi->kategori_km ?? '-',
                        'sub_kategori_km' => $aktivitasVerifikasi->sub_kategori_km ?? '-',
                        'keterangan_km' => $aktivitasVerifikasi->keterangan_km ?? '-',
                        'catatan_verifikasi' => $aktivitasVerifikasi->catatan_verifikasi ?? null,
                        'diverifikasi_pada' => $aktivitasVerifikasi->diverifikasi_pada
                            ?? $notifikasi->created_at
                            ?? null,
                        'detail_url' => url('/anggota/aktivitas-km/' . $aktivitasVerifikasi->id_aktivitas . '/detail'),
                    ]);

                    $idNotifikasiDitampilkan->push($notifikasi->id_notifikasi);
                }

                if ($idNotifikasiDitampilkan->isNotEmpty()) {
                    $payloadDibaca = ['dibaca_pada' => now()];

                    if (in_array('updated_at', $kolomNotifikasi, true)) {
                        $payloadDibaca['updated_at'] = now();
                    }

                    DB::table('notifikasi')
                        ->whereIn('id_notifikasi', $idNotifikasiDitampilkan->unique()->values())
                        ->update($payloadDibaca);
                }
            }
        }

        return view('anggota.dashboard', compact(
            'tahun',
            'periode',
            'triwulan',
            'semester',
            'tahunOptions',
            'tanggalMulai',
            'tanggalSelesai',
            'labelPeriode',
            'keteranganPeriode',
            'user',
            'idLab',
            'namaAnggota',
            'namaLab',
            'jadAnggota',
            'nidnAnggota',
            'jumlahTargetAktif',
            'jumlahKategoriAktif',
            'jumlahSubmitted',
            'jumlahOnProgress',
            'totalTarget',
            'totalRealisasi',
            'totalSisa',
            'persentaseTotal',
            'kategoriCards',
            'chartKategori',
            'detailKategoriCharts',
            'targetKmSaya',
            'targetKmTahunan',
            'historyAktivitas',
            'statusPengajuanKm'
        ));
    }

    public function indexRealisasi()
    {
        $realisasis = RealisasiKm::join('target_km', 'realisasi_km.id_target', '=', 'target_km.id_target')
            ->where('realisasi_km.id_dosen', Auth::user()->id_dosen)
            ->get();

        return view('anggota.realisasi', compact('realisasis'));
    }

    public function editRealisasi($id)
    {
        $realisasi = RealisasiKm::join('target_km', 'realisasi_km.id_target', '=', 'target_km.id_target')
            ->where('realisasi_km.id_realisasi', $id)
            ->firstOrFail();

        return view('anggota.realisasi_edit', compact('realisasi'));
    }

    public function updateRealisasi(Request $request, $id)
    {
        $request->validate([
            'realisasi' => 'required|integer|min:0',
        ]);

        $realisasi = RealisasiKm::findOrFail($id);
        $realisasi->realisasi = $request->realisasi;

        $target = TargetKm::findOrFail($realisasi->id_target);

        $realisasi->status_realisasi = $request->realisasi >= $target->target
            ? 'Tercapai'
            : 'Belum Tercapai';

        $realisasi->save();

        return redirect('/anggota/realisasi-km')
            ->with('success', 'Progress capaian berhasil diupdate!');
    }
}
