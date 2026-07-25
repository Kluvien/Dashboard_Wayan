<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TargetKmController;
use App\Http\Controllers\KetuaLabController;
use App\Http\Controllers\KetuaLabReportController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\AnggotaReportController;
use App\Http\Controllers\KetuaKkController;
use App\Http\Controllers\KetuaKkReportController;
use App\Http\Controllers\NotifikasiController;

Route::get('/', function () {
    return view('welcome'); // Halaman awal bawaan Laravel
});

// Route untuk Login
Route::get('/login', [AuthController::class, 'index'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::post('/logout', [AuthController::class, 'logout']);


if (!function_exists('km_eims_sanitize_filename')) {
    function km_eims_sanitize_filename(?string $filename): string
    {
        $filename = $filename ?: 'bukti-km';
        $filename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $filename);
        return trim($filename, '-_') ?: 'bukti-km';
    }
}

if (!function_exists('km_eims_build_pdf_with_jpeg')) {
    function km_eims_build_pdf_with_jpeg(string $jpegPath, string $pdfPath): bool
    {
        $size = @getimagesize($jpegPath);

        if (!$size) {
            return false;
        }

        [$imageWidth, $imageHeight] = $size;
        $jpegData = file_get_contents($jpegPath);

        if ($jpegData === false) {
            return false;
        }

        $maxPageWidth = 595;
        $maxPageHeight = 842;
        $scale = min($maxPageWidth / max($imageWidth, 1), $maxPageHeight / max($imageHeight, 1), 1);
        $pageWidth = max(1, round($imageWidth * $scale));
        $pageHeight = max(1, round($imageHeight * $scale));

        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$pageWidth} {$pageHeight}] /Resources << /XObject << /Im0 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
        $objects[] = "4 0 obj\n<< /Type /XObject /Subtype /Image /Width {$imageWidth} /Height {$imageHeight} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($jpegData) . " >>\nstream\n" . $jpegData . "\nendstream\nendobj\n";

        $content = "q\n{$pageWidth} 0 0 {$pageHeight} 0 0 cm\n/Im0 Do\nQ\n";
        $objects[] = "5 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n" . $content . "endstream\nendobj\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return file_put_contents($pdfPath, $pdf) !== false;
    }
}

if (!function_exists('km_eims_convert_image_to_pdf')) {
    function km_eims_convert_image_to_pdf(string $sourcePath, string $pdfPath, string $extension): bool
    {
        if (!function_exists('imagejpeg')) {
            return false;
        }

        $extension = strtolower($extension);
        $image = null;

        if (in_array($extension, ['jpg', 'jpeg'], true) && function_exists('imagecreatefromjpeg')) {
            $image = @imagecreatefromjpeg($sourcePath);
        }

        if ($extension === 'png' && function_exists('imagecreatefrompng')) {
            $image = @imagecreatefrompng($sourcePath);
        }

        if (!$image) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $canvas = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $white);
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);

        $tmpJpeg = storage_path('app/bukti-km/tmp-' . uniqid('', true) . '.jpg');

        if (!is_dir(dirname($tmpJpeg))) {
            mkdir(dirname($tmpJpeg), 0755, true);
        }

        $jpegCreated = imagejpeg($canvas, $tmpJpeg, 90);
        imagedestroy($image);
        imagedestroy($canvas);

        if (!$jpegCreated) {
            return false;
        }

        $pdfCreated = km_eims_build_pdf_with_jpeg($tmpJpeg, $pdfPath);
        @unlink($tmpJpeg);

        return $pdfCreated;
    }
}

if (!function_exists('km_eims_store_bukti_file')) {
    function km_eims_store_bukti_file(\Illuminate\Http\Request $request, string $fieldName = 'bukti_file'): array
    {
        if (!$request->hasFile($fieldName)) {
            return [];
        }

        $file = $request->file($fieldName);
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $baseName = pathinfo(km_eims_sanitize_filename($originalName), PATHINFO_FILENAME);
        $directory = 'bukti-km/' . date('Y') . '/' . date('m');
        $uniqueName = now()->format('YmdHis') . '-' . uniqid() . '-' . $baseName;
        $storedName = $uniqueName . '.' . $extension;
        $storedPath = $file->storeAs($directory, $storedName, 'local');

        $pdfPath = $storedPath;

        if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            $relativePdfPath = $directory . '/' . $uniqueName . '.pdf';
            $absolutePdfPath = storage_path('app/' . $relativePdfPath);

            if (!is_dir(dirname($absolutePdfPath))) {
                mkdir(dirname($absolutePdfPath), 0755, true);
            }

            if (km_eims_convert_image_to_pdf(storage_path('app/' . $storedPath), $absolutePdfPath, $extension)) {
                $pdfPath = $relativePdfPath;
            }
        }

        return [
            'bukti_file_path' => $storedPath,
            'bukti_pdf_path' => $pdfPath,
            'bukti_file_nama_asli' => $originalName,
            'bukti_file_mime' => $file->getMimeType(),
            'bukti_file_size' => $file->getSize(),
        ];
    }
}


if (!function_exists('km_eims_bagi_rata_ke_triwulan')) {
    function km_eims_bagi_rata_ke_triwulan(int $jumlah): array
    {
        $jumlah = max($jumlah, 0);
        $dasar = intdiv($jumlah, 4);
        $sisa = $jumlah % 4;

        return [
            1 => $dasar + ($sisa >= 1 ? 1 : 0),
            2 => $dasar + ($sisa >= 2 ? 1 : 0),
            3 => $dasar + ($sisa >= 3 ? 1 : 0),
            4 => $dasar,
        ];
    }
}

if (!function_exists('km_eims_get_ketuakk_anggota_detail_data')) {
    function km_eims_get_ketuakk_anggota_detail_data(\Illuminate\Http\Request $request, int $id): array
    {
        $tahun = (int) $request->query('tahun', now()->year);
        $periode = (string) $request->query('periode', 'triwulan');

        if (!in_array($periode, ['tahun', 'triwulan', 'semester'], true)) {
            $periode = 'tahun';
        }

        $triwulan = (int) $request->query('triwulan', ceil(now()->month / 3));
        $triwulan = max(1, min(4, $triwulan));

        $semester = (int) $request->query('semester', now()->month <= 6 ? 1 : 2);
        $semester = max(1, min(2, $semester));

        if ($periode === 'triwulan') {
            $bulanMulai = (($triwulan - 1) * 3) + 1;
            $bulanSelesai = $bulanMulai + 2;
            $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
            $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
            $labelPeriode = 'Triwulan ' . $triwulan . ' Tahun ' . $tahun;
            $periodeColumns = [1 => 'TW1', 2 => 'TW2', 3 => 'TW3', 4 => 'TW4'];
        } elseif ($periode === 'semester') {
            $bulanMulai = $semester === 1 ? 1 : 7;
            $bulanSelesai = $semester === 1 ? 6 : 12;
            $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
            $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();
            $labelPeriode = 'Semester ' . $semester . ' Tahun ' . $tahun;
            $periodeColumns = [1 => 'Semester 1', 2 => 'Semester 2'];
        } else {
            $periode = 'tahun';
            $tanggalMulai = \Carbon\Carbon::create($tahun, 1, 1)->startOfYear();
            $tanggalSelesai = \Carbon\Carbon::create($tahun, 12, 31)->endOfYear();
            $labelPeriode = 'Tahunan ' . $tahun;
            $periodeColumns = [1 => 'TW1', 2 => 'TW2', 3 => 'TW3', 4 => 'TW4'];
        }

        /** @var \App\Models\User $userLogin */
        $userLogin = auth()->user();

        $ketuaKk = \Illuminate\Support\Facades\DB::table('dosen')
            ->where('id_dosen', $userLogin->id_dosen)
            ->first();

        abort_unless($ketuaKk, 403, 'Data Ketua KK tidak ditemukan.');

        $idKk = (int) $ketuaKk->id_kk;

        $anggota = \Illuminate\Support\Facades\DB::table('users as u')
            ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
            ->leftJoin('laboratorium_riset as lr_user', 'u.id_lab', '=', 'lr_user.id_lab')
            ->leftJoin('laboratorium_riset as lr_dosen', 'd.id_lab', '=', 'lr_dosen.id_lab')
            ->where('u.id_user', $id)
            ->whereIn('u.role', ['Anggota', 'anggota'])
            ->where(function ($query) use ($idKk) {
                $query->where('d.id_kk', $idKk)
                    ->orWhere('lr_user.id_kk', $idKk)
                    ->orWhere('lr_dosen.id_kk', $idKk);
            })
            ->select(
                'u.id_user',
                'u.id_dosen',
                'u.username',
                'u.role',
                \Illuminate\Support\Facades\DB::raw('COALESCE(u.id_lab, d.id_lab) as id_lab'),
                'd.nama_dosen',
                'd.nidn',
                'd.email',
                'd.jad',
                \Illuminate\Support\Facades\DB::raw('COALESCE(lr_user.nama_lab, lr_dosen.nama_lab) as nama_lab')
            )
            ->first();

        abort_unless($anggota, 404, 'Data anggota KK tidak ditemukan.');

        $kategoriDefault = [
            'Penelitian',
            'Publikasi',
            'Pengabdian',
            'Penunjang',
        ];

        $tahunDariTarget = \Illuminate\Support\Facades\DB::table('km_anggota as ka')
            ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
            ->where('ka.id_user', $anggota->id_user)
            ->pluck('kl.tahun_km');

        $tahunDariAktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
            ->where('id_user', $anggota->id_user)
            ->whereNotNull('tanggal_mulai')
            ->pluck('tanggal_mulai')
            ->map(function ($tanggal) {
                return (int) \Carbon\Carbon::parse($tanggal)->year;
            });

        $tahunOptions = collect()
            ->merge($tahunDariTarget)
            ->merge($tahunDariAktivitas)
            ->push(now()->year)
            ->push($tahun)
            ->map(fn ($item) => (int) $item)
            ->unique()
            ->sortDesc()
            ->values();

        $hasKmAnggotaTriwulan =
            \Illuminate\Support\Facades\Schema::hasColumn('km_anggota', 'triwulan_1') &&
            \Illuminate\Support\Facades\Schema::hasColumn('km_anggota', 'triwulan_2') &&
            \Illuminate\Support\Facades\Schema::hasColumn('km_anggota', 'triwulan_3') &&
            \Illuminate\Support\Facades\Schema::hasColumn('km_anggota', 'triwulan_4');

        $hasKmLabTriwulan =
            \Illuminate\Support\Facades\Schema::hasColumn('km_lab', 'triwulan_1') &&
            \Illuminate\Support\Facades\Schema::hasColumn('km_lab', 'triwulan_2') &&
            \Illuminate\Support\Facades\Schema::hasColumn('km_lab', 'triwulan_3') &&
            \Illuminate\Support\Facades\Schema::hasColumn('km_lab', 'triwulan_4');

        $hasKmLabIdTarget = \Illuminate\Support\Facades\Schema::hasColumn('km_lab', 'id_target');
        $hasTargetKeterangan = \Illuminate\Support\Facades\Schema::hasColumn('target_km', 'keterangan');
        $hasAktivitasIdKmAnggota = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'id_km_anggota');
        $hasStatusProgress = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'status_progress');

        $queryAssign = \Illuminate\Support\Facades\DB::table('km_anggota as ka')
            ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
            ->where('ka.id_user', $anggota->id_user)
            ->where('kl.tahun_km', $tahun)
            ->where('kl.status_km', 'Aktif')
            ->select(
                'ka.id_km_anggota',
                'ka.id_km_lab',
                'ka.jumlah_km',
                'ka.created_at as tanggal_assign',
                'kl.tahun_km',
                'kl.kategori_km',
                'kl.sub_kategori_km',
                'kl.status_km'
            );

        if ($hasKmLabIdTarget) {
            $queryAssign->leftJoin('target_km as tk', 'kl.id_target', '=', 'tk.id_target');

            if ($hasTargetKeterangan) {
                $queryAssign->addSelect(
                    \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(tk.keterangan, ''), '-') as keterangan")
                );
            } else {
                $queryAssign->addSelect(\Illuminate\Support\Facades\DB::raw("'-' as keterangan"));
            }
        } else {
            $queryAssign->addSelect(\Illuminate\Support\Facades\DB::raw("'-' as keterangan"));
        }

        if ($hasKmAnggotaTriwulan) {
            $queryAssign->addSelect('ka.triwulan_1', 'ka.triwulan_2', 'ka.triwulan_3', 'ka.triwulan_4');
        } elseif ($hasKmLabTriwulan) {
            $queryAssign->addSelect('kl.triwulan_1', 'kl.triwulan_2', 'kl.triwulan_3', 'kl.triwulan_4');
        } else {
            $queryAssign->addSelect(
                \Illuminate\Support\Facades\DB::raw('0 as triwulan_1'),
                \Illuminate\Support\Facades\DB::raw('0 as triwulan_2'),
                \Illuminate\Support\Facades\DB::raw('0 as triwulan_3'),
                \Illuminate\Support\Facades\DB::raw('0 as triwulan_4')
            );
        }

        $assignments = $queryAssign
            ->orderBy('ka.created_at', 'desc')
            ->get();

        $targetTwPerKategori = [];
        $targetTahunanPerKategori = [];

        foreach ($kategoriDefault as $kategori) {
            $targetTwPerKategori[$kategori] = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            $targetTahunanPerKategori[$kategori] = 0;
        }

        foreach ($assignments as $assign) {
            $kategori = $assign->kategori_km;

            if (!array_key_exists($kategori, $targetTwPerKategori)) {
                $targetTwPerKategori[$kategori] = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
                $targetTahunanPerKategori[$kategori] = 0;
            }

            $jumlahKm = (int) ($assign->jumlah_km ?? 0);
            $targetTw = [
                1 => (int) ($assign->triwulan_1 ?? 0),
                2 => (int) ($assign->triwulan_2 ?? 0),
                3 => (int) ($assign->triwulan_3 ?? 0),
                4 => (int) ($assign->triwulan_4 ?? 0),
            ];

            if (array_sum($targetTw) <= 0 && $jumlahKm > 0) {
                $targetTw = km_eims_bagi_rata_ke_triwulan($jumlahKm);
            }

            foreach ([1, 2, 3, 4] as $nomorTw) {
                $targetTwPerKategori[$kategori][$nomorTw] += $targetTw[$nomorTw];
            }

            $targetTahunanPerKategori[$kategori] += $jumlahKm;
        }

        $realisasiTwPerKategori = [];

        foreach ($kategoriDefault as $kategori) {
            $realisasiTwPerKategori[$kategori] = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        }

        if ($hasAktivitasIdKmAnggota) {
            $queryAktivitasRealisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km as ak')
                ->join('km_anggota as ka', 'ak.id_km_anggota', '=', 'ka.id_km_anggota')
                ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                ->where('ak.id_user', $anggota->id_user)
                ->where('kl.tahun_km', $tahun)
                ->whereDate('ak.tanggal_mulai', '>=', \Carbon\Carbon::create($tahun, 1, 1)->toDateString())
                ->whereDate('ak.tanggal_mulai', '<=', \Carbon\Carbon::create($tahun, 12, 31)->toDateString())
                ->select('ak.id_aktivitas', 'kl.kategori_km', 'ak.tanggal_mulai');

            if ($hasStatusProgress) {
                $queryAktivitasRealisasi->where('ak.status_progress', 'Accepted');
            }

            $queryAktivitasRealisasi
                ->get()
                ->unique('id_aktivitas')
                ->each(function ($aktivitas) use (&$realisasiTwPerKategori) {
                    $kategori = $aktivitas->kategori_km;
                    $nomorTw = (int) ceil(\Carbon\Carbon::parse($aktivitas->tanggal_mulai)->month / 3);

                    if (!isset($realisasiTwPerKategori[$kategori])) {
                        $realisasiTwPerKategori[$kategori] = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
                    }

                    $realisasiTwPerKategori[$kategori][$nomorTw]++;
                });
        }

        if ($periode === 'semester') {
            $ubahKePeriode = function (array $nilaiTw): array {
                return [
                    1 => (int) ($nilaiTw[1] ?? 0) + (int) ($nilaiTw[2] ?? 0),
                    2 => (int) ($nilaiTw[3] ?? 0) + (int) ($nilaiTw[4] ?? 0),
                ];
            };
        } else {
            $ubahKePeriode = function (array $nilaiTw): array {
                return [
                    1 => (int) ($nilaiTw[1] ?? 0),
                    2 => (int) ($nilaiTw[2] ?? 0),
                    3 => (int) ($nilaiTw[3] ?? 0),
                    4 => (int) ($nilaiTw[4] ?? 0),
                ];
            };
        }

        $rekap = [];

        foreach ($kategoriDefault as $kategori) {
            $targetTw = $targetTwPerKategori[$kategori] ?? [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            $realisasiTw = $realisasiTwPerKategori[$kategori] ?? [1 => 0, 2 => 0, 3 => 0, 4 => 0];

            $targetDetail = $ubahKePeriode($targetTw);
            $realisasiDetail = $ubahKePeriode($realisasiTw);
            $targetTahunan = (int) ($targetTahunanPerKategori[$kategori] ?? 0);

            if ($periode === 'triwulan') {
                $targetPeriode = (int) ($targetTw[$triwulan] ?? 0);
                $realisasiPeriode = (int) ($realisasiTw[$triwulan] ?? 0);
            } elseif ($periode === 'semester') {
                $targetPeriode = (int) ($targetDetail[$semester] ?? 0);
                $realisasiPeriode = (int) ($realisasiDetail[$semester] ?? 0);
            } else {
                $targetPeriode = array_sum($targetTw);
                $realisasiPeriode = array_sum($realisasiTw);
            }

            $sisa = max($targetPeriode - $realisasiPeriode, 0);
            $persentase = $targetPeriode > 0
                ? min(round(($realisasiPeriode / $targetPeriode) * 100), 100)
                : 0;

            if ($targetPeriode <= 0) {
                $status = 'Belum Ada Target';
            } elseif ($realisasiPeriode >= $targetPeriode) {
                $status = 'Tercapai';
            } elseif ($realisasiPeriode > 0) {
                $status = 'On Progress';
            } else {
                $status = 'Belum Mulai';
            }

            $rekap[] = [
                'kategori' => $kategori,
                'target_tahunan' => $targetTahunan,
                'target_periode' => $targetPeriode,
                'realisasi' => $realisasiPeriode,
                'sisa' => $sisa,
                'persentase' => $persentase,
                'status' => $status,
                'target_periode_detail' => $targetDetail,
                'realisasi_periode_detail' => $realisasiDetail,
            ];
        }

        $totalTargetTahunan = array_sum(array_column($rekap, 'target_tahunan'));
        $totalTargetPeriode = array_sum(array_column($rekap, 'target_periode'));
        $totalRealisasi = array_sum(array_column($rekap, 'realisasi'));
        $totalSisa = array_sum(array_column($rekap, 'sisa'));
        $persentaseTotal = $totalTargetPeriode > 0
            ? min(round(($totalRealisasi / $totalTargetPeriode) * 100), 100)
            : 0;

        $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
            ->where('id_user', $anggota->id_user)
            ->whereDate('tanggal_mulai', '>=', \Carbon\Carbon::create($tahun, 1, 1)->toDateString())
            ->whereDate('tanggal_mulai', '<=', \Carbon\Carbon::create($tahun, 12, 31)->toDateString())
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('created_at')
            ->get();

        return compact(
            'anggota',
            'tahun',
            'tahunOptions',
            'periode',
            'triwulan',
            'semester',
            'tanggalMulai',
            'tanggalSelesai',
            'labelPeriode',
            'periodeColumns',
            'rekap',
            'aktivitas',
            'totalTargetTahunan',
            'totalTargetPeriode',
            'totalRealisasi',
            'totalSisa',
            'persentaseTotal'
        ) + [
            'riwayatAssign' => $assignments,
        ];
    }
}

Route::middleware(['auth'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Notifikasi
    |--------------------------------------------------------------------------
    */
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])
        ->name('notifikasi.index');

    Route::get('/notifikasi/{id}/baca', [NotifikasiController::class, 'baca'])
        ->whereNumber('id')
        ->name('notifikasi.baca');

    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])
        ->name('notifikasi.baca-semua');


    Route::get('/bukti-km/{id}/download', function ($id) {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
            ->where('id_aktivitas', $id)
            ->first();

        if (!$aktivitas) {
            abort(404);
        }

        $bolehAkses = false;

        if ($user->role === 'Anggota' && (int) $aktivitas->id_user === (int) $user->id_user) {
            $bolehAkses = true;
        }

        if ($user->role === 'Ketua Lab' && (int) $aktivitas->id_lab === (int) $user->id_lab) {
            $bolehAkses = true;
        }

        if ($user->role === 'Ketua KK') {
            $bolehAkses = true;
        }

        if (!$bolehAkses) {
            abort(403);
        }

        $path = $aktivitas->bukti_pdf_path ?: ($aktivitas->bukti_file_path ?? null);

        if (!$path || !\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
            abort(404, 'File bukti tidak ditemukan.');
        }

        $safeTitle = km_eims_sanitize_filename($aktivitas->judul_aktivitas ?? 'bukti-km');
        $downloadName = $safeTitle . '.pdf';

        if (!str_ends_with(strtolower($path), '.pdf')) {
            $downloadName = $safeTitle . '.' . pathinfo($path, PATHINFO_EXTENSION);
        }

        return \Illuminate\Support\Facades\Storage::disk('local')->download($path, $downloadName);
    })->name('bukti-km.download');

    // Halaman Utama Pembagi
    Route::get('/', function () {
        $role = auth()->user()->role;

        if ($role === 'Ketua KK') {
            return redirect('/ketuakk/dashboard');
        }

        if ($role === 'Ketua Lab') {
            return redirect('/ketualab/dashboard');
        }

        if ($role === 'Anggota') {
            return redirect('/anggota/dashboard');
        }

        return redirect('/login');
    });

    // RUANG KHUSUS KETUA KK
    Route::middleware(['auth', 'role:Ketua KK'])->group(function () {
        Route::get('/ketuakk/profil', function () {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $dosen = null;
            $lab = null;

            if (!empty($user->id_dosen)) {
                $dosen = \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_dosen', $user->id_dosen)
                    ->first();
            }

            if (!empty($user->id_lab)) {
                $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                    ->where('id_lab', $user->id_lab)
                    ->first();
            }

            return view('ketuakk.profil', compact('user', 'dosen', 'lab'));
        });
        Route::get('/ketuakk/dashboard', function (\Illuminate\Http\Request $request) {
            /*
            |--------------------------------------------------------------------------
            | Filter periode dashboard
            |--------------------------------------------------------------------------
            | tahunan  : seluruh TW1–TW4 pada tahun yang dipilih
            | triwulan : hanya triwulan yang dipilih
            | semester : Semester 1 = TW1+TW2, Semester 2 = TW3+TW4
            */
            $tahun = (int) $request->query('tahun', now()->year);
            $mode = strtolower(trim((string) $request->query('mode', 'tahunan')));

            if (!in_array($mode, ['tahunan', 'triwulan', 'semester'], true)) {
                $mode = 'tahunan';
            }

            $triwulan = (int) $request->query('triwulan', 1);
            $semester = (int) $request->query('semester', 1);

            if (!in_array($triwulan, [1, 2, 3, 4], true)) {
                $triwulan = 1;
            }

            if (!in_array($semester, [1, 2], true)) {
                $semester = 1;
            }

            $triwulanTerpilih = match ($mode) {
                'triwulan' => [$triwulan],
                'semester' => $semester === 1 ? [1, 2] : [3, 4],
                default => [1, 2, 3, 4],
            };

            $periodeLabel = match ($mode) {
                'triwulan' => 'Triwulan ' . $triwulan . ' Tahun ' . $tahun,
                'semester' => 'Semester ' . $semester . ' Tahun ' . $tahun,
                default => 'Tahun ' . $tahun,
            };

            $periodeKeterangan = match ($mode) {
                'triwulan' => 'Data target, penurunan, dan realisasi ditampilkan untuk Triwulan ' . $triwulan . '.',
                'semester' => 'Data target, penurunan, dan realisasi ditampilkan untuk Semester ' . $semester . '.',
                default => 'Data target, penurunan, dan realisasi ditampilkan untuk satu tahun penuh.',
            };

            $bulanMulai = match ($mode) {
                'triwulan' => (($triwulan - 1) * 3) + 1,
                'semester' => $semester === 1 ? 1 : 7,
                default => 1,
            };

            $bulanSelesai = match ($mode) {
                'triwulan' => $bulanMulai + 2,
                'semester' => $semester === 1 ? 6 : 12,
                default => 12,
            };

            $tanggalMulaiPeriode = \Carbon\Carbon::create($tahun, $bulanMulai, 1)
                ->startOfMonth()
                ->toDateString();

            $tanggalSelesaiPeriode = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)
                ->endOfMonth()
                ->toDateString();

            $kategoriDefault = [
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            /** @var \App\Models\User $userLogin */
            $userLogin = auth()->user();

            $ketuaKk = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_dosen', $userLogin->id_dosen)
                ->first();

            abort_unless($ketuaKk, 403, 'Data Ketua KK tidak ditemukan.');

            $idKk = (int) $ketuaKk->id_kk;

            $tahunOptions = collect()
                ->merge(
                    \Illuminate\Support\Facades\DB::table('kontrak_manajemen')
                        ->where('id_dosen', $userLogin->id_dosen)
                        ->select('tahun_km')
                        ->distinct()
                        ->pluck('tahun_km')
                )
                ->merge(
                    \Illuminate\Support\Facades\DB::table('km_lab as kl')
                        ->join('laboratorium_riset as lr', 'kl.id_lab', '=', 'lr.id_lab')
                        ->where('lr.id_kk', $idKk)
                        ->select('kl.tahun_km')
                        ->distinct()
                        ->pluck('tahun_km')
                )
                ->push(now()->year)
                ->push($tahun)
                ->map(fn ($item) => (int) $item)
                ->unique()
                ->sortDesc()
                ->values();

            $buatEkspresiPeriodeTarget = function (string $alias) use ($mode, $triwulanTerpilih): string {
                if ($mode === 'tahunan') {
                    return "COALESCE({$alias}.target, 0)";
                }

                return implode(' + ', array_map(
                    fn ($tw) => "COALESCE({$alias}.triwulan_{$tw}, 0)",
                    $triwulanTerpilih
                ));
            };

            $buatEkspresiPeriodeKmLab = function (string $alias) use ($mode, $triwulanTerpilih): string {
                if ($mode === 'tahunan') {
                    return "COALESCE({$alias}.jumlah_km, 0)";
                }

                return implode(' + ', array_map(
                    fn ($tw) => "COALESCE({$alias}.triwulan_{$tw}, 0)",
                    $triwulanTerpilih
                ));
            };

            $targetPeriodeTarget = $buatEkspresiPeriodeTarget('target_km');
            $targetPeriodeTk = $buatEkspresiPeriodeTarget('tk');
            $kmLabPeriodeKl = $buatEkspresiPeriodeKmLab('kl');

            $hasStatusProgress = \Illuminate\Support\Facades\Schema::hasColumn(
                'aktivitas_km',
                'status_progress'
            );

            /*
            |--------------------------------------------------------------------------
            | Target KM Ketua KK sesuai periode aktif
            |--------------------------------------------------------------------------
            */
            $targetKmRows = \Illuminate\Support\Facades\DB::table('target_km')
                ->join(
                    'kontrak_manajemen',
                    'target_km.id_km',
                    '=',
                    'kontrak_manajemen.id_km'
                )
                ->where('kontrak_manajemen.id_dosen', $userLogin->id_dosen)
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->select(
                    'target_km.id_target',
                    'target_km.kategori_km',
                    'target_km.indikator',
                    'target_km.target',
                    'target_km.triwulan_1',
                    'target_km.triwulan_2',
                    'target_km.triwulan_3',
                    'target_km.triwulan_4',
                    \Illuminate\Support\Facades\DB::raw("({$targetPeriodeTarget}) as target_periode")
                )
                ->orderBy('target_km.kategori_km')
                ->orderBy('target_km.indikator')
                ->get();

            $totalTargetKm = (int) $targetKmRows->sum('target_periode');

            /*
            |--------------------------------------------------------------------------
            | Realisasi valid sesuai periode aktif
            |--------------------------------------------------------------------------
            */
            $buildRealisasiQuery = function () use (
                $userLogin,
                $tahun,
                $hasStatusProgress,
                $tanggalMulaiPeriode,
                $tanggalSelesaiPeriode,
                $targetPeriodeTk
            ) {
                $query = \Illuminate\Support\Facades\DB::table('aktivitas_km as ak')
                    ->join('km_anggota as ka', 'ak.id_km_anggota', '=', 'ka.id_km_anggota')
                    ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                    ->join('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                    ->join('kontrak_manajemen as km', 'tk.id_km', '=', 'km.id_km')
                    ->where('km.id_dosen', $userLogin->id_dosen)
                    ->where('km.tahun_km', $tahun)
                    ->where('kl.status_km', 'Aktif')
                    ->whereRaw("({$targetPeriodeTk}) > 0")
                    ->whereBetween('ak.tanggal_mulai', [
                        $tanggalMulaiPeriode,
                        $tanggalSelesaiPeriode,
                    ]);

                if ($hasStatusProgress) {
                    $query->where('ak.status_progress', 'Accepted');
                }

                return $query;
            };

            $totalRealisasiKm = (int) $buildRealisasiQuery()
                ->count('ak.id_aktivitas');

            $totalSisaKm = max($totalTargetKm - $totalRealisasiKm, 0);

            $persentaseRealisasi = $totalTargetKm > 0
                ? min(round(($totalRealisasiKm / $totalTargetKm) * 100, 1), 100)
                : 0;

            /*
            |--------------------------------------------------------------------------
            | Data Lab Riset dan anggota KK
            |--------------------------------------------------------------------------
            */
            $labs = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_kk', $idKk)
                ->orderBy('id_lab')
                ->get();

            $jumlahLab = $labs->count();

            $anggotaKk = \Illuminate\Support\Facades\DB::table('users as u')
                ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
                ->leftJoin('laboratorium_riset as lr_user', 'u.id_lab', '=', 'lr_user.id_lab')
                ->leftJoin('laboratorium_riset as lr_dosen', 'd.id_lab', '=', 'lr_dosen.id_lab')
                ->whereIn('u.role', ['Anggota', 'anggota'])
                ->where(function ($query) use ($idKk) {
                    $query->where('d.id_kk', $idKk)
                        ->orWhere('lr_user.id_kk', $idKk)
                        ->orWhere('lr_dosen.id_kk', $idKk);
                })
                ->select(
                    'u.id_user',
                    'u.username',
                    \Illuminate\Support\Facades\DB::raw('COALESCE(u.id_lab, d.id_lab) as id_lab'),
                    'd.nama_dosen',
                    'd.nidn',
                    'd.jad',
                    \Illuminate\Support\Facades\DB::raw('COALESCE(lr_user.nama_lab, lr_dosen.nama_lab) as nama_lab')
                )
                ->distinct()
                ->orderBy('nama_lab')
                ->orderBy('d.nama_dosen')
                ->get();

            $jumlahAnggotaKk = $anggotaKk->count();

            /*
            |--------------------------------------------------------------------------
            | Ringkasan dan diagram Lab Riset sesuai periode aktif
            |--------------------------------------------------------------------------
            */
            $jumlahLabSelesai = 0;
            $labChartLabels = [];
            $labShortLabels = [];
            $labTargets = [];
            $labRealisasi = [];
            $labAchievementPercentages = [];
            $rekapLab = [];

            foreach ($labs as $lab) {
                $targetLab = (int) \Illuminate\Support\Facades\DB::table('km_lab as kl')
                    ->join('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                    ->join('kontrak_manajemen as km', 'tk.id_km', '=', 'km.id_km')
                    ->where('kl.id_lab', $lab->id_lab)
                    ->where('kl.status_km', 'Aktif')
                    ->where('km.id_dosen', $userLogin->id_dosen)
                    ->where('km.tahun_km', $tahun)
                    ->whereRaw("({$targetPeriodeTk}) > 0")
                    ->sum(\Illuminate\Support\Facades\DB::raw($kmLabPeriodeKl));

                $realisasiLab = (int) $buildRealisasiQuery()
                    ->where('kl.id_lab', $lab->id_lab)
                    ->count('ak.id_aktivitas');

                $sisaLab = max($targetLab - $realisasiLab, 0);

                $persentaseLab = $targetLab > 0
                    ? min(round(($realisasiLab / $targetLab) * 100), 100)
                    : 0;

                if ($targetLab > 0 && $realisasiLab >= $targetLab) {
                    $jumlahLabSelesai++;
                }

                $namaSingkat = str_contains($lab->nama_lab, ' - ')
                    ? trim(explode(' - ', $lab->nama_lab)[0])
                    : $lab->nama_lab;

                $labChartLabels[] = $lab->nama_lab;
                $labShortLabels[] = $namaSingkat;
                $labTargets[] = $targetLab;
                $labRealisasi[] = $realisasiLab;
                $labAchievementPercentages[] = $persentaseLab;

                $rekapLab[] = [
                    'id_lab' => $lab->id_lab,
                    'nama_lab' => $lab->nama_lab,
                    'nama_singkat' => $namaSingkat,
                    'target' => $targetLab,
                    'realisasi' => $realisasiLab,
                    'sisa' => $sisaLab,
                    'persentase' => $persentaseLab,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Monitoring anggota KK sesuai periode aktif
            |--------------------------------------------------------------------------
            | km_anggota belum menyimpan pembagian per triwulan. Untuk filter
            | triwulan/semester, target anggota dibatasi oleh target KM Lab
            | yang tersedia pada periode tersebut.
            */
            $semuaMonitoringAnggota = [];
            $jumlahAnggotaSelesai = 0;

            $targetAnggotaPeriodeExpression = $mode === 'tahunan'
                ? 'COALESCE(ka.jumlah_km, 0)'
                : "CASE
                    WHEN COALESCE(kl.jumlah_km, 0) <= 0 THEN 0
                    WHEN COALESCE(ka.jumlah_km, 0) <= ({$kmLabPeriodeKl})
                        THEN COALESCE(ka.jumlah_km, 0)
                    ELSE ({$kmLabPeriodeKl})
                END";

            foreach ($anggotaKk as $anggota) {
                $targetAnggota = (int) \Illuminate\Support\Facades\DB::table('km_anggota as ka')
                    ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                    ->join('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                    ->join('kontrak_manajemen as km', 'tk.id_km', '=', 'km.id_km')
                    ->where('ka.id_user', $anggota->id_user)
                    ->where('kl.status_km', 'Aktif')
                    ->where('km.id_dosen', $userLogin->id_dosen)
                    ->where('km.tahun_km', $tahun)
                    ->whereRaw("({$targetPeriodeTk}) > 0")
                    ->sum(\Illuminate\Support\Facades\DB::raw($targetAnggotaPeriodeExpression));

                $realisasiAnggota = (int) $buildRealisasiQuery()
                    ->where('ak.id_user', $anggota->id_user)
                    ->count('ak.id_aktivitas');

                $sisaAnggota = max($targetAnggota - $realisasiAnggota, 0);

                $progressAnggota = $targetAnggota > 0
                    ? min(round(($realisasiAnggota / $targetAnggota) * 100), 100)
                    : 0;

                if ($targetAnggota <= 0) {
                    $statusAnggota = 'Belum Ada KM';
                    $statusClass = 'secondary';
                } elseif ($realisasiAnggota <= 0) {
                    $statusAnggota = 'Belum Mulai';
                    $statusClass = 'danger';
                } elseif ($realisasiAnggota >= $targetAnggota) {
                    $statusAnggota = 'Selesai';
                    $statusClass = 'success';
                    $jumlahAnggotaSelesai++;
                } else {
                    $statusAnggota = 'On Progress';
                    $statusClass = 'warning';
                }

                $semuaMonitoringAnggota[] = [
                    'id_user' => $anggota->id_user,
                    'nama_dosen' => $anggota->nama_dosen ?? $anggota->username,
                    'nidn' => $anggota->nidn ?? '-',
                    'jad' => $anggota->jad ?? 'AA',
                    'nama_lab' => $anggota->nama_lab ?? '-',
                    'target' => $targetAnggota,
                    'realisasi' => $realisasiAnggota,
                    'sisa' => $sisaAnggota,
                    'progress' => $progressAnggota,
                    'status' => $statusAnggota,
                    'status_class' => $statusClass,
                ];
            }

            $monitoringAnggotaRows = collect($semuaMonitoringAnggota)
                ->take(10)
                ->values();

            /*
            |--------------------------------------------------------------------------
            | Card kategori dashboard
            |--------------------------------------------------------------------------
            */
            $targetKategoriByNama = $targetKmRows
                ->groupBy('kategori_km')
                ->map(fn ($items) => (int) $items->sum('target_periode'));

            $realisasiKategoriByNama = $buildRealisasiQuery()
                ->select(
                    'tk.kategori_km',
                    \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT ak.id_aktivitas) as total_realisasi')
                )
                ->groupBy('tk.kategori_km')
                ->get()
                ->mapWithKeys(fn ($item) => [
                    $item->kategori_km => (int) $item->total_realisasi,
                ]);

            $diturunkanKategoriByNama = \Illuminate\Support\Facades\DB::table('km_lab as kl')
                ->join('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                ->join('kontrak_manajemen as km', 'tk.id_km', '=', 'km.id_km')
                ->where('km.id_dosen', $userLogin->id_dosen)
                ->where('km.tahun_km', $tahun)
                ->where('kl.status_km', 'Aktif')
                ->whereRaw("({$targetPeriodeTk}) > 0")
                ->select(
                    'tk.kategori_km',
                    \Illuminate\Support\Facades\DB::raw("COALESCE(SUM({$kmLabPeriodeKl}), 0) as total_diturunkan")
                )
                ->groupBy('tk.kategori_km')
                ->get()
                ->mapWithKeys(fn ($item) => [
                    $item->kategori_km => (int) $item->total_diturunkan,
                ]);

            $kategoriLabels = [];
            $kategoriTargets = [];
            $kategoriRealisasi = [];
            $kategoriCards = [];

            foreach ($kategoriDefault as $kategori) {
                $targetKategori = (int) ($targetKategoriByNama[$kategori] ?? 0);
                $realisasiKategori = (int) ($realisasiKategoriByNama[$kategori] ?? 0);
                $diturunkanKategori = (int) ($diturunkanKategoriByNama[$kategori] ?? 0);

                $sisaKategori = max($targetKategori - $realisasiKategori, 0);
                $belumTurunKategori = max($targetKategori - $diturunkanKategori, 0);

                $persentaseKategori = $targetKategori > 0
                    ? min(round(($realisasiKategori / $targetKategori) * 100), 100)
                    : 0;

                $kategoriLabels[] = $kategori;
                $kategoriTargets[] = $targetKategori;
                $kategoriRealisasi[] = $realisasiKategori;

                $kategoriCards[] = [
                    'kategori' => $kategori,
                    'target' => $targetKategori,
                    'realisasi' => $realisasiKategori,
                    'diturunkan' => $diturunkanKategori,
                    'belum_turun' => $belumTurunKategori,
                    'sisa' => $sisaKategori,
                    'persentase' => $persentaseKategori,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Grafik target dan realisasi per sub kategori
            |--------------------------------------------------------------------------
            */
            $targetSubKategoriRows = $targetKmRows
                ->groupBy(fn ($item) => $item->kategori_km . '|' . $item->indikator)
                ->map(function ($items) {
                    $first = $items->first();

                    return [
                        'kategori_km' => $first->kategori_km,
                        'indikator' => $first->indikator,
                        'total_target' => (int) $items->sum('target_periode'),
                    ];
                })
                ->values();

            $realisasiSubKategoriRows = $buildRealisasiQuery()
                ->select(
                    'tk.kategori_km',
                    'tk.indikator',
                    \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT ak.id_aktivitas) as total_realisasi')
                )
                ->groupBy('tk.kategori_km', 'tk.indikator')
                ->get();

            $kategoriDetailCharts = [];

            foreach ($kategoriDefault as $kategori) {
                $targetBySub = collect($targetSubKategoriRows)
                    ->where('kategori_km', $kategori)
                    ->mapWithKeys(fn ($item) => [
                        ($item['indikator'] ?: '-') => (int) $item['total_target'],
                    ]);

                $realisasiBySub = $realisasiSubKategoriRows
                    ->where('kategori_km', $kategori)
                    ->mapWithKeys(fn ($item) => [
                        ($item->indikator ?: '-') => (int) $item->total_realisasi,
                    ]);

                $labels = $targetBySub->keys()
                    ->merge($realisasiBySub->keys())
                    ->unique()
                    ->values();

                if ($labels->isEmpty()) {
                    $labels = collect(['Belum ada data']);
                }

                $kategoriDetailCharts[] = [
                    'kategori' => $kategori,
                    'labels' => $labels->toArray(),
                    'targets' => $labels
                        ->map(fn ($label) => (int) ($targetBySub[$label] ?? 0))
                        ->toArray(),
                    'realisasi' => $labels
                        ->map(fn ($label) => (int) ($realisasiBySub[$label] ?? 0))
                        ->toArray(),
                ];
            }

            $chartKkLabel = ['Target KM', 'Realisasi KM', 'Sisa KM'];
            $chartKkData = [$totalTargetKm, $totalRealisasiKm, $totalSisaKm];

            $filterQuery = http_build_query(array_filter([
                'tahun' => $tahun,
                'mode' => $mode,
                'triwulan' => $mode === 'triwulan' ? $triwulan : null,
                'semester' => $mode === 'semester' ? $semester : null,
            ], fn ($value) => $value !== null));

            return view('ketuakk.dashboard', compact(
                'tahun',
                'tahunOptions',
                'mode',
                'triwulan',
                'semester',
                'triwulanTerpilih',
                'periodeLabel',
                'periodeKeterangan',
                'tanggalMulaiPeriode',
                'tanggalSelesaiPeriode',
                'filterQuery',
                'jumlahLab',
                'jumlahAnggotaKk',
                'jumlahLabSelesai',
                'jumlahAnggotaSelesai',
                'totalTargetKm',
                'totalRealisasiKm',
                'totalSisaKm',
                'persentaseRealisasi',
                'chartKkLabel',
                'chartKkData',
                'labChartLabels',
                'labShortLabels',
                'labTargets',
                'labRealisasi',
                'rekapLab',
                'kategoriLabels',
                'kategoriTargets',
                'kategoriRealisasi',
                'kategoriCards',
                'labAchievementPercentages',
                'kategoriDetailCharts',
                'monitoringAnggotaRows'
            ));
        });
        Route::get('/ketuakk/target-km', [TargetKmController::class, 'index']);
        Route::get('/ketuakk/target-km/create', [TargetKmController::class, 'create']);
        Route::post('/ketuakk/target-km', [TargetKmController::class, 'store']);
        Route::get('/ketuakk/target-km/{id}/edit', [TargetKmController::class, 'edit']);
        Route::put('/ketuakk/target-km/{id}', [TargetKmController::class, 'update']);
        Route::delete('/ketuakk/target-km/{id}', [TargetKmController::class, 'destroy']);
        Route::get('/ketuakk/data-lab-riset', function () {
            $laboratorium = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            $dataLab = [];

            foreach ($laboratorium as $lab) {
                $jumlahDosen = \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_lab', $lab->id_lab)
                    ->count();

                $jumlahAktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                    ->where('id_lab', $lab->id_lab)
                    ->count();

                $dataLab[] = [
                    'id_lab' => $lab->id_lab,
                    'nama_lab' => $lab->nama_lab,
                    'jumlah_dosen' => $jumlahDosen,
                    'jumlah_aktivitas' => $jumlahAktivitas,
                ];
            }

            return view('ketuakk.data-master.lab-riset', compact('dataLab'));
        });

        Route::get('/ketuakk/data-lab-riset/{id}', function ($id) {
            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $id)
                ->first();

            if (!$lab) {
                abort(404);
            }

            $dosen = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_lab', $id)
                ->orderBy('nama_dosen')
                ->get();

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->leftJoin('users', 'aktivitas_km.id_user', '=', 'users.id_user')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->where('aktivitas_km.id_lab', $id)
                ->select(
                    'aktivitas_km.id_aktivitas',
                    'aktivitas_km.kategori_km',
                    'aktivitas_km.judul_aktivitas',
                    'aktivitas_km.deskripsi_singkat',
                    'aktivitas_km.tanggal_mulai',
                    'aktivitas_km.tanggal_selesai',
                    'aktivitas_km.bukti_file_path',
                    'aktivitas_km.bukti_pdf_path',
                    'aktivitas_km.bukti_link',
                    'users.username',
                    'dosen.nama_dosen',
                    'dosen.nidn'
                )
                ->orderBy('aktivitas_km.tanggal_mulai', 'desc')
                ->get();

            return view('ketuakk.data-master.lab-riset-detail', compact(
                'lab',
                'dosen',
                'aktivitas'
            ));
        });
        Route::get('/ketuakk/data-dosen', function (\Illuminate\Http\Request $request) {
            $q = $request->query('q');

            /*
            |--------------------------------------------------------------------------
            | Jumlah data per halaman dibuat tetap 20 data.
            |--------------------------------------------------------------------------
            */
            $perPage = 20;

            $dosens = \Illuminate\Support\Facades\DB::table('dosen')
                ->leftJoin(
                    'laboratorium_riset',
                    'dosen.id_lab',
                    '=',
                    'laboratorium_riset.id_lab'
                )
                ->select(
                    'dosen.id_dosen',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'dosen.jad',
                    'laboratorium_riset.nama_lab'
                )
                ->when($q, function ($query) use ($q) {
                    $query->where(function ($subQuery) use ($q) {
                        $subQuery->where('dosen.nama_dosen', 'like', '%' . $q . '%')
                            ->orWhere('dosen.nidn', 'like', '%' . $q . '%')
                            ->orWhere('dosen.email', 'like', '%' . $q . '%')
                            ->orWhere('dosen.jad', 'like', '%' . $q . '%')
                            ->orWhere(
                                'laboratorium_riset.nama_lab',
                                'like',
                                '%' . $q . '%'
                            );
                    });
                })
                ->orderBy('dosen.id_dosen', 'asc')
                ->paginate($perPage)
                ->withQueryString();

            return view('ketuakk.data-master.dosen', compact(
                'dosens',
                'q'
            ));
        });

        Route::get('/ketuakk/data-dosen/create', function () {
            $labs = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            return view('ketuakk.data-master.dosen-create', compact('labs'));
        });

        Route::post('/ketuakk/data-dosen', function (\Illuminate\Http\Request $request) {
            $request->validate([
                'nama_dosen' => 'required|string|max:255',
                'nidn' => 'required|string|max:50',
                'email' => 'required|email|max:255',
                'id_lab' => 'required|exists:laboratorium_riset,id_lab',
                'jad' => 'required|in:GB,LK,L,AA,NJFA',
            ]);

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $request->id_lab)
                ->first();

            \Illuminate\Support\Facades\DB::table('dosen')->insert([
                'id_kk' => $lab->id_kk ?? 1,
                'id_lab' => $request->id_lab,
                'nama_dosen' => $request->nama_dosen,
                'nidn' => $request->nidn,
                'email' => $request->email,
                'jad' => $request->jad,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect('/ketuakk/data-dosen')->with('success', 'Data dosen berhasil ditambahkan.');
        });

        Route::get('/ketuakk/data-dosen/{id}/edit', function ($id) {
            $dosen = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_dosen', $id)
                ->first();

            if (!$dosen) {
                abort(404);
            }

            $labs = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            return view('ketuakk.data-master.dosen-edit', compact('dosen', 'labs'));
        });

        Route::put('/ketuakk/data-dosen/{id}', function (\Illuminate\Http\Request $request, $id) {
            $request->validate([
                'nama_dosen' => 'required|string|max:255',
                'nidn' => 'required|string|max:50',
                'email' => 'required|email|max:255',
                'id_lab' => 'required|exists:laboratorium_riset,id_lab',
                'jad' => 'required|in:GB,LK,L,AA,NJFA',
            ]);

            $dosen = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_dosen', $id)
                ->first();

            if (!$dosen) {
                abort(404);
            }

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $request->id_lab)
                ->first();

            \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_dosen', $id)
                ->update([
                    'id_kk' => $lab->id_kk ?? 1,
                    'id_lab' => $request->id_lab,
                    'nama_dosen' => $request->nama_dosen,
                    'nidn' => $request->nidn,
                    'email' => $request->email,
                    'jad' => $request->jad,
                    'updated_at' => now(),
                ]);

            return redirect('/ketuakk/data-dosen')->with('success', 'Data dosen berhasil diperbarui.');
        });
        Route::get('/ketuakk/data-kelompok-keahlian', function () {
            $kelompokKeahlian = \Illuminate\Support\Facades\DB::table('kelompok_keahlian')->get();

            return view('ketuakk.data-master.kelompok-keahlian', compact('kelompokKeahlian'));
        });
        Route::get('/ketuakk/monitoring-lab-riset', function (\Illuminate\Http\Request $request) {
            $tahun = (int) $request->query('tahun', now()->year);
            $periode = $request->query('periode', 'triwulan');

            if (!in_array($periode, ['triwulan', 'semester'])) {
                $periode = 'triwulan';
            }

            $kategoriDefault = [
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            /** @var \App\Models\User $userLogin */
            $userLogin = auth()->user();

            $ketuaKk = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_dosen', $userLogin->id_dosen)
                ->first();

            abort_unless($ketuaKk, 403, 'Data Ketua KK tidak ditemukan.');

            $idKk = $ketuaKk->id_kk;

            $periodeColumns = $periode === 'semester'
                ? [
                    1 => 'Semester 1',
                    2 => 'Semester 2',
                ]
                : [
                    1 => 'TW1',
                    2 => 'TW2',
                    3 => 'TW3',
                    4 => 'TW4',
                ];

            $labelPeriode = $periode === 'semester'
                ? 'Semester Tahun ' . $tahun
                : 'Triwulan Tahun ' . $tahun;

            $tahunOptions = collect(
                \Illuminate\Support\Facades\DB::table('km_lab')
                    ->join('laboratorium_riset', 'km_lab.id_lab', '=', 'laboratorium_riset.id_lab')
                    ->where('laboratorium_riset.id_kk', $idKk)
                    ->select('km_lab.tahun_km')
                    ->distinct()
                    ->orderBy('km_lab.tahun_km', 'desc')
                    ->pluck('tahun_km')
            )
                ->push(now()->year)
                ->map(fn ($item) => (int) $item)
                ->unique()
                ->sortDesc()
                ->values();

            $hasStatusProgress = \Illuminate\Support\Facades\Schema::hasColumn(
                'aktivitas_km',
                'status_progress'
            );

            $hasTriwulanKmLab = \Illuminate\Support\Facades\Schema::hasColumn(
                'km_lab',
                'triwulan_1'
            );

            $getRangeTanggal = function ($key) use ($tahun, $periode) {
                if ($periode === 'semester') {
                    $bulanMulai = (int) $key === 1 ? 1 : 7;
                    $bulanSelesai = (int) $key === 1 ? 6 : 12;

                    return [
                        \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth()->toDateString(),
                        \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth()->toDateString(),
                    ];
                }

                $bulanMulai = (((int) $key - 1) * 3) + 1;
                $bulanSelesai = $bulanMulai + 2;

                return [
                    \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth()->toDateString(),
                    \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth()->toDateString(),
                ];
            };

            $ubahTriwulanKePeriode = function ($twValues) use ($periode) {
                if ($periode === 'semester') {
                    return [
                        1 => ($twValues[1] ?? 0) + ($twValues[2] ?? 0),
                        2 => ($twValues[3] ?? 0) + ($twValues[4] ?? 0),
                    ];
                }

                return [
                    1 => $twValues[1] ?? 0,
                    2 => $twValues[2] ?? 0,
                    3 => $twValues[3] ?? 0,
                    4 => $twValues[4] ?? 0,
                ];
            };

            $buatDataKosong = function () use ($kategoriDefault, $periodeColumns) {
                $data = [];

                foreach ($kategoriDefault as $kategori) {
                    $data[$kategori] = [
                        'target' => [],
                        'realisasi' => [],
                    ];

                    foreach ($periodeColumns as $key => $label) {
                        $data[$kategori]['target'][$key] = 0;
                        $data[$kategori]['realisasi'][$key] = 0;
                    }
                }

                return $data;
            };

            $labs = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_kk', $idKk)
                ->orderBy('id_lab')
                ->get();

            $monitoringLabs = [];
            $rekapKategori = [];

            foreach ($kategoriDefault as $kategori) {
                $rekapKategori[$kategori] = [
                    'kategori' => $kategori,
                    'target' => 0,
                    'realisasi' => 0,
                    'progress' => 0,
                ];
            }

            foreach ($labs as $lab) {
                $jumlahAnggota = \Illuminate\Support\Facades\DB::table('users')
                    ->whereIn('role', ['Anggota', 'anggota'])
                    ->where('id_lab', $lab->id_lab)
                    ->count();

                $dataPerKategori = $buatDataKosong();

                /*
                * Target diambil dari km_lab.
                * Artinya target berdasarkan jumlah yang benar-benar
                * diturunkan oleh Ketua KK ke Lab Riset.
                */
                $kmLabRows = \Illuminate\Support\Facades\DB::table('km_lab')
                    ->where('id_lab', $lab->id_lab)
                    ->where('tahun_km', $tahun)
                    ->where('status_km', 'Aktif')
                    ->get();

                foreach ($kmLabRows as $km) {
                    if (!isset($dataPerKategori[$km->kategori_km])) {
                        continue;
                    }

                    /*
                    * Mengambil pembagian TW1-TW4 langsung dari km_lab.
                    * Tidak lagi mengambil angka dari target_km Ketua KK.
                    */
                    if ($hasTriwulanKmLab) {
                        $twValues = [
                            1 => (int) ($km->triwulan_1 ?? 0),
                            2 => (int) ($km->triwulan_2 ?? 0),
                            3 => (int) ($km->triwulan_3 ?? 0),
                            4 => (int) ($km->triwulan_4 ?? 0),
                        ];
                    } else {
                        /*
                        * Fallback hanya untuk struktur database lama
                        * yang belum memiliki kolom triwulan di km_lab.
                        */
                        $total = (int) ($km->jumlah_km ?? 0);
                        $base = intdiv($total, 4);
                        $sisa = $total % 4;

                        $twValues = [
                            1 => $base + ($sisa >= 1 ? 1 : 0),
                            2 => $base + ($sisa >= 2 ? 1 : 0),
                            3 => $base + ($sisa >= 3 ? 1 : 0),
                            4 => $base,
                        ];
                    }

                    $periodeValues = $ubahTriwulanKePeriode($twValues);

                    foreach ($periodeColumns as $key => $label) {
                        $dataPerKategori[$km->kategori_km]['target'][$key] +=
                            (int) ($periodeValues[$key] ?? 0);
                    }
                }

                foreach ($kategoriDefault as $kategori) {
                    foreach ($periodeColumns as $key => $label) {
                        [$tanggalMulai, $tanggalSelesai] = $getRangeTanggal($key);

                        $queryRealisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                            ->where('id_lab', $lab->id_lab)
                            ->where('kategori_km', $kategori)
                            ->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai]);

                        if ($hasStatusProgress) {
                            $queryRealisasi->where('status_progress', 'Accepted');
                        }

                        $dataPerKategori[$kategori]['realisasi'][$key] =
                            (int) $queryRealisasi->count();
                    }
                }

                $totalTargetLab = 0;
                $totalRealisasiLab = 0;

                foreach ($kategoriDefault as $kategori) {
                    $targetKategoriLab = array_sum($dataPerKategori[$kategori]['target']);
                    $realisasiKategoriLab = array_sum($dataPerKategori[$kategori]['realisasi']);

                    $totalTargetLab += $targetKategoriLab;
                    $totalRealisasiLab += $realisasiKategoriLab;

                    $rekapKategori[$kategori]['target'] += $targetKategoriLab;
                    $rekapKategori[$kategori]['realisasi'] += $realisasiKategoriLab;
                }

                $monitoringLabs[] = [
                    'id_lab' => $lab->id_lab,
                    'nama_lab' => $lab->nama_lab,
                    'jumlah_anggota' => $jumlahAnggota,
                    'data' => $dataPerKategori,
                    'total_target' => $totalTargetLab,
                    'total_realisasi' => $totalRealisasiLab,
                    'progress' => $totalTargetLab > 0
                        ? min(round(($totalRealisasiLab / $totalTargetLab) * 100), 100)
                        : 0,
                ];
            }

            foreach ($rekapKategori as $kategori => $item) {
                $rekapKategori[$kategori]['progress'] = $item['target'] > 0
                    ? min(round(($item['realisasi'] / $item['target']) * 100), 100)
                    : 0;
            }

            $rekapKategori = array_values($rekapKategori);

            return view('ketuakk.monitoring-lab-riset.index', compact(
                'tahun',
                'tahunOptions',
                'periode',
                'periodeColumns',
                'labelPeriode',
                'kategoriDefault',
                'monitoringLabs',
                'rekapKategori'
            ));
        });
        Route::get('/ketuakk/monitoring-lab-riset/{id_lab}', function (\Illuminate\Http\Request $request, $id_lab) {
            /** @var \App\Models\User $userLogin */
            $userLogin = auth()->user();

            $ketuaKk = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_dosen', $userLogin->id_dosen)
                ->first();

            abort_unless($ketuaKk, 403, 'Data Ketua KK tidak ditemukan.');

            $tahun = (int) $request->query('tahun', now()->year);
            $periode = $request->query('periode', 'triwulan');

            if (!in_array($periode, ['triwulan', 'semester'], true)) {
                $periode = 'triwulan';
            }

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $id_lab)
                ->where('id_kk', $ketuaKk->id_kk)
                ->first();

            abort_unless($lab, 404, 'Lab Riset tidak ditemukan.');

            $kategoriDefault = [
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $periodeColumns = $periode === 'semester'
                ? [1 => 'Semester 1', 2 => 'Semester 2']
                : [1 => 'TW1', 2 => 'TW2', 3 => 'TW3', 4 => 'TW4'];

            $labelPeriode = $periode === 'semester'
                ? 'Semester Tahun ' . $tahun
                : 'Triwulan Tahun ' . $tahun;

            $tahunOptions = collect(
                \Illuminate\Support\Facades\DB::table('km_lab')
                    ->where('id_lab', $lab->id_lab)
                    ->select('tahun_km')
                    ->distinct()
                    ->pluck('tahun_km')
            )
                ->push(now()->year)
                ->push($tahun)
                ->map(fn ($item) => (int) $item)
                ->unique()
                ->sortDesc()
                ->values();

            $jumlahAnggota = \Illuminate\Support\Facades\DB::table('users')
                ->whereIn('role', ['Anggota', 'anggota'])
                ->where('id_lab', $lab->id_lab)
                ->count();

            $hasKmLabIdTarget = \Illuminate\Support\Facades\Schema::hasColumn('km_lab', 'id_target');
            $hasKmLabTriwulan = \Illuminate\Support\Facades\Schema::hasColumn('km_lab', 'triwulan_1');
            $hasKmAnggotaTriwulan = \Illuminate\Support\Facades\Schema::hasColumn('km_anggota', 'triwulan_1');
            $hasAktivitasIdKmAnggota = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'id_km_anggota');
            $hasStatusProgress = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'status_progress');

            $queryKmLab = \Illuminate\Support\Facades\DB::table('km_lab as kl')
                ->where('kl.id_lab', $lab->id_lab)
                ->where('kl.tahun_km', $tahun)
                ->where('kl.status_km', 'Aktif')
                ->select(
                    'kl.id_km_lab',
                    'kl.id_lab',
                    'kl.tahun_km',
                    'kl.kategori_km',
                    'kl.sub_kategori_km',
                    'kl.jumlah_km'
                );

            if ($hasKmLabIdTarget) {
                $queryKmLab
                    ->leftJoin('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                    ->addSelect(
                        \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(tk.keterangan, ''), '-') as keterangan"),
                        'tk.tanggal_selesai_tw1',
                        'tk.tanggal_selesai_tw2',
                        'tk.tanggal_selesai_tw3',
                        'tk.tanggal_selesai_tw4'
                    );
            } else {
                $queryKmLab->addSelect(
                    \Illuminate\Support\Facades\DB::raw("'-' as keterangan"),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw1'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw2'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw3'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw4')
                );
            }

            if ($hasKmLabTriwulan) {
                $queryKmLab->addSelect(
                    'kl.triwulan_1',
                    'kl.triwulan_2',
                    'kl.triwulan_3',
                    'kl.triwulan_4'
                );
            } else {
                $queryKmLab->addSelect(
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_1'),
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_2'),
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_3'),
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_4')
                );
            }

            $kmRows = $queryKmLab
                ->orderBy('kl.kategori_km')
                ->orderBy('kl.sub_kategori_km')
                ->get();

            $idKmLabList = $kmRows->pluck('id_km_lab')->filter()->values();

            $assignPerKmLab = collect();
            $assignPerMember = collect();

            if ($idKmLabList->isNotEmpty()) {
                $assignPerKmLab = \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->whereIn('id_km_lab', $idKmLabList)
                    ->select(
                        'id_km_lab',
                        \Illuminate\Support\Facades\DB::raw('SUM(jumlah_km) as total_assign')
                    )
                    ->groupBy('id_km_lab')
                    ->pluck('total_assign', 'id_km_lab');

                $assignPerMember = \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                    ->whereIn('km_anggota.id_km_lab', $idKmLabList)
                    ->select(
                        'km_anggota.id_user',
                        \Illuminate\Support\Facades\DB::raw('SUM(km_anggota.jumlah_km) as total_assign')
                    )
                    ->groupBy('km_anggota.id_user')
                    ->pluck('total_assign', 'id_user');
            }

            $realisasiPerKmLabTw = [];
            $realisasiPerMember = collect();

            if ($hasAktivitasIdKmAnggota && $idKmLabList->isNotEmpty()) {
                $queryAktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km as ak')
                    ->join('km_anggota as ka', 'ak.id_km_anggota', '=', 'ka.id_km_anggota')
                    ->whereIn('ka.id_km_lab', $idKmLabList)
                    ->whereYear('ak.tanggal_mulai', $tahun)
                    ->select(
                        'ka.id_km_lab',
                        'ka.id_user',
                        'ak.tanggal_mulai'
                    );

                if ($hasStatusProgress) {
                    $queryAktivitas->where('ak.status_progress', 'Accepted');
                }

                $queryAktivitas
                    ->orderBy('ak.tanggal_mulai')
                    ->get()
                    ->each(function ($aktivitas) use (&$realisasiPerKmLabTw, &$realisasiPerMember) {
                        $idKmLab = (int) $aktivitas->id_km_lab;
                        $idUser = (int) $aktivitas->id_user;
                        $bulan = (int) \Carbon\Carbon::parse($aktivitas->tanggal_mulai)->month;
                        $tw = (int) ceil($bulan / 3);

                        if (!isset($realisasiPerKmLabTw[$idKmLab])) {
                            $realisasiPerKmLabTw[$idKmLab] = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
                        }

                        $realisasiPerKmLabTw[$idKmLab][$tw]++;

                        $realisasiPerMember[$idUser] = (int) ($realisasiPerMember[$idUser] ?? 0) + 1;
                    });
            }

            $ubahKePeriode = function (array $twValues) use ($periode) {
                if ($periode === 'semester') {
                    return [
                        1 => (int) ($twValues[1] ?? 0) + (int) ($twValues[2] ?? 0),
                        2 => (int) ($twValues[3] ?? 0) + (int) ($twValues[4] ?? 0),
                    ];
                }

                return [
                    1 => (int) ($twValues[1] ?? 0),
                    2 => (int) ($twValues[2] ?? 0),
                    3 => (int) ($twValues[3] ?? 0),
                    4 => (int) ($twValues[4] ?? 0),
                ];
            };

            $detailPerKategori = collect();

            foreach ($kategoriDefault as $kategori) {
                $detailPerKategori->put($kategori, []);
            }

            foreach ($kmRows as $km) {
                $twTarget = [
                    1 => (int) ($km->triwulan_1 ?? 0),
                    2 => (int) ($km->triwulan_2 ?? 0),
                    3 => (int) ($km->triwulan_3 ?? 0),
                    4 => (int) ($km->triwulan_4 ?? 0),
                ];

                if (array_sum($twTarget) <= 0) {
                    $total = (int) ($km->jumlah_km ?? 0);
                    $base = intdiv($total, 4);
                    $sisa = $total % 4;

                    $twTarget = [
                        1 => $base + ($sisa >= 1 ? 1 : 0),
                        2 => $base + ($sisa >= 2 ? 1 : 0),
                        3 => $base + ($sisa >= 3 ? 1 : 0),
                        4 => $base,
                    ];
                }

                $twRealisasi = $realisasiPerKmLabTw[(int) $km->id_km_lab] ?? [1 => 0, 2 => 0, 3 => 0, 4 => 0];

                $targetPeriode = $ubahKePeriode($twTarget);
                $realisasiPeriode = $ubahKePeriode($twRealisasi);

                $deadlineTw = [
                    1 => $km->tanggal_selesai_tw1 ?? null,
                    2 => $km->tanggal_selesai_tw2 ?? null,
                    3 => $km->tanggal_selesai_tw3 ?? null,
                    4 => $km->tanggal_selesai_tw4 ?? null,
                ];

                $deadlinePeriode = [];

                if ($periode === 'semester') {
                    $deadlinePeriode = [
                        1 => !empty($deadlineTw[2]) ? \Carbon\Carbon::parse($deadlineTw[2])->format('d/m/Y') : '-',
                        2 => !empty($deadlineTw[4]) ? \Carbon\Carbon::parse($deadlineTw[4])->format('d/m/Y') : '-',
                    ];
                } else {
                    foreach ($deadlineTw as $key => $tanggal) {
                        $deadlinePeriode[$key] = !empty($tanggal)
                            ? \Carbon\Carbon::parse($tanggal)->format('d/m/Y')
                            : '-';
                    }
                }

                $totalTarget = array_sum($targetPeriode);
                $totalRealisasi = array_sum($realisasiPeriode);
                $sudahDibagi = (int) ($assignPerKmLab[$km->id_km_lab] ?? 0);
                $belumDibagi = max($totalTarget - $sudahDibagi, 0);
                $sisa = max($totalTarget - $totalRealisasi, 0);
                $persentase = $totalTarget > 0
                    ? min(round(($totalRealisasi / $totalTarget) * 100), 100)
                    : 0;

                $deadlineTerakhir = collect($deadlineTw)
                    ->filter()
                    ->map(fn ($tanggal) => \Carbon\Carbon::parse($tanggal))
                    ->sort()
                    ->last();

                if ($totalTarget > 0 && $totalRealisasi >= $totalTarget) {
                    $status = 'Tercapai';
                } elseif ($belumDibagi > 0) {
                    $status = 'Belum Dibagi';
                } elseif ($totalRealisasi > 0) {
                    $status = $deadlineTerakhir && now()->startOfDay()->gt($deadlineTerakhir->copy()->endOfDay())
                        ? 'Lewat Tenggat'
                        : 'On Progress';
                } else {
                    $status = $deadlineTerakhir && now()->startOfDay()->gt($deadlineTerakhir->copy()->endOfDay())
                        ? 'Lewat Tenggat'
                        : 'Belum Mulai';
                }

                $listKategori = collect($detailPerKategori->get($km->kategori_km, []));

                $listKategori->push([
                    'id_km_lab' => (int) $km->id_km_lab,
                    'sub_kategori' => $km->sub_kategori_km ?? '-',
                    'keterangan' => $km->keterangan ?? '-',
                    'target_periode' => $targetPeriode,
                    'realisasi_periode' => $realisasiPeriode,
                    'deadline_periode' => $deadlinePeriode,
                    'total_target' => $totalTarget,
                    'total_realisasi' => $totalRealisasi,
                    'sudah_dibagi' => $sudahDibagi,
                    'belum_dibagi' => $belumDibagi,
                    'sisa' => $sisa,
                    'persentase' => $persentase,
                    'status' => $status,
                ]);

                $detailPerKategori->put($km->kategori_km, $listKategori->values()->all());
            }

            $totalKmTurun = $kmRows->sum('jumlah_km');
            $totalKmDibagi = (int) $assignPerKmLab->sum();
            $totalBelumDibagi = max($totalKmTurun - $totalKmDibagi, 0);
            $totalRealisasi = collect($detailPerKategori)
                ->flatten(1)
                ->sum(fn ($item) => (int) ($item['total_realisasi'] ?? 0));
            $persentaseLab = $totalKmTurun > 0
                ? min(round(($totalRealisasi / $totalKmTurun) * 100), 100)
                : 0;

            $anggotaLab = \Illuminate\Support\Facades\DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->whereIn('users.role', ['Anggota', 'anggota'])
                ->where('users.id_lab', $lab->id_lab)
                ->select(
                    'users.id_user',
                    'users.username',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.jad'
                )
                ->orderBy('dosen.nama_dosen')
                ->get()
                ->map(function ($anggota) use ($assignPerMember, $realisasiPerMember) {
                    $target = (int) ($assignPerMember[$anggota->id_user] ?? 0);
                    $realisasi = (int) ($realisasiPerMember[$anggota->id_user] ?? 0);
                    $sisa = max($target - $realisasi, 0);
                    $persentase = $target > 0
                        ? min(round(($realisasi / $target) * 100), 100)
                        : 0;

                    return [
                        'nama' => $anggota->nama_dosen ?? $anggota->username,
                        'nidn' => $anggota->nidn ?? '-',
                        'jad' => $anggota->jad ?? 'AA',
                        'target' => $target,
                        'realisasi' => $realisasi,
                        'sisa' => $sisa,
                        'persentase' => $persentase,
                        'status' => $target <= 0
                            ? 'Belum Mulai'
                            : ($realisasi >= $target ? 'Tercapai' : ($realisasi > 0 ? 'On Progress' : 'Belum Mulai')),
                    ];
                });

            return view('ketuakk.monitoring-lab-riset.detail', compact(
                'lab',
                'tahun',
                'tahunOptions',
                'periode',
                'periodeColumns',
                'labelPeriode',
                'kategoriDefault',
                'jumlahAnggota',
                'detailPerKategori',
                'totalKmTurun',
                'totalKmDibagi',
                'totalBelumDibagi',
                'totalRealisasi',
                'persentaseLab',
                'anggotaLab'
            ));
        });
        Route::get('/ketuakk/monitoring-anggota-kk', function (\Illuminate\Http\Request $request) {
            $tahun = (int) $request->query('tahun', now()->year);
            $periode = $request->query('periode', 'triwulan');

            if (!in_array($periode, ['triwulan', 'semester'])) {
                $periode = 'triwulan';
            }

            $kategoriDefault = [
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $periodeColumns = $periode === 'semester'
                ? [
                    1 => 'Semester 1',
                    2 => 'Semester 2',
                ]
                : [
                    1 => 'TW1',
                    2 => 'TW2',
                    3 => 'TW3',
                    4 => 'TW4',
                ];

            $labelPeriode = $periode === 'semester'
                ? 'Semester Tahun ' . $tahun
                : 'Triwulan Tahun ' . $tahun;

            /** @var \App\Models\User $userLogin */
            $userLogin = auth()->user();

            $ketuaKk = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_dosen', $userLogin->id_dosen)
                ->first();

            abort_unless($ketuaKk, 403, 'Data Ketua KK tidak ditemukan.');

            $idKk = $ketuaKk->id_kk;

            $labs = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_kk', $idKk)
                ->orderBy('id_lab')
                ->get();

            $idLabList = $labs->pluck('id_lab')->values();

            $tahunOptions = collect(
                \Illuminate\Support\Facades\DB::table('km_lab')
                    ->join(
                        'laboratorium_riset',
                        'km_lab.id_lab',
                        '=',
                        'laboratorium_riset.id_lab'
                    )
                    ->where('laboratorium_riset.id_kk', $idKk)
                    ->select('km_lab.tahun_km')
                    ->distinct()
                    ->orderBy('km_lab.tahun_km', 'desc')
                    ->pluck('km_lab.tahun_km')
            )
                ->push(now()->year)
                ->map(fn ($item) => (int) $item)
                ->unique()
                ->sortDesc()
                ->values();

            $hasStatusProgress = \Illuminate\Support\Facades\Schema::hasColumn(
                'aktivitas_km',
                'status_progress'
            );

            $hasIdKmAnggotaAktivitas = \Illuminate\Support\Facades\Schema::hasColumn(
                'aktivitas_km',
                'id_km_anggota'
            );

            $hasTriwulanKmLab = \Illuminate\Support\Facades\Schema::hasColumn(
                'km_lab',
                'triwulan_1'
            );

            $getRangeTanggal = function ($key) use ($tahun, $periode) {
                if ($periode === 'semester') {
                    $bulanMulai = (int) $key === 1 ? 1 : 7;
                    $bulanSelesai = (int) $key === 1 ? 6 : 12;

                    return [
                        \Carbon\Carbon::create($tahun, $bulanMulai, 1)
                            ->startOfMonth()
                            ->toDateString(),

                        \Carbon\Carbon::create($tahun, $bulanSelesai, 1)
                            ->endOfMonth()
                            ->toDateString(),
                    ];
                }

                $bulanMulai = (((int) $key - 1) * 3) + 1;
                $bulanSelesai = $bulanMulai + 2;

                return [
                    \Carbon\Carbon::create($tahun, $bulanMulai, 1)
                        ->startOfMonth()
                        ->toDateString(),

                    \Carbon\Carbon::create($tahun, $bulanSelesai, 1)
                        ->endOfMonth()
                        ->toDateString(),
                ];
            };

            $ubahTriwulanKePeriode = function ($twValues) use ($periode) {
                if ($periode === 'semester') {
                    return [
                        1 => ($twValues[1] ?? 0) + ($twValues[2] ?? 0),
                        2 => ($twValues[3] ?? 0) + ($twValues[4] ?? 0),
                    ];
                }

                return [
                    1 => $twValues[1] ?? 0,
                    2 => $twValues[2] ?? 0,
                    3 => $twValues[3] ?? 0,
                    4 => $twValues[4] ?? 0,
                ];
            };

            /*
            |--------------------------------------------------------------------------
            | Membagi target Lab ke target anggota secara proporsional.
            |--------------------------------------------------------------------------
            | Contoh:
            | KM Lab = 4, pembagian TW = 1,1,1,1
            | Anggota menerima KM = 2
            | Maka target anggota dibagi proporsional ke periode yang tersedia.
            */
            $distribusiTargetAnggota = function ($jumlahAnggota, $twLabValues) {
                $jumlahAnggota = (int) $jumlahAnggota;

                $hasil = [
                    1 => 0,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                ];

                if ($jumlahAnggota <= 0) {
                    return $hasil;
                }

                $totalLab = array_sum($twLabValues);

                /*
                | Fallback untuk data KM Lab lama yang belum punya pembagian
                | Triwulan secara lengkap.
                */
                if ($totalLab <= 0) {
                    $base = intdiv($jumlahAnggota, 4);
                    $sisa = $jumlahAnggota % 4;

                    return [
                        1 => $base + ($sisa >= 1 ? 1 : 0),
                        2 => $base + ($sisa >= 2 ? 1 : 0),
                        3 => $base + ($sisa >= 3 ? 1 : 0),
                        4 => $base,
                    ];
                }

                $pecahan = [];
                $totalSementara = 0;

                foreach ([1, 2, 3, 4] as $key) {
                    $nilaiAsli = ($jumlahAnggota * ($twLabValues[$key] ?? 0)) / $totalLab;
                    $nilaiBulat = (int) floor($nilaiAsli);

                    $hasil[$key] = $nilaiBulat;
                    $totalSementara += $nilaiBulat;

                    $pecahan[$key] = $nilaiAsli - $nilaiBulat;
                }

                $sisaPembagian = $jumlahAnggota - $totalSementara;

                arsort($pecahan);

                foreach (array_keys($pecahan) as $key) {
                    if ($sisaPembagian <= 0) {
                        break;
                    }

                    $hasil[$key]++;
                    $sisaPembagian--;
                }

                return $hasil;
            };

            $buatDataKosong = function () use ($kategoriDefault, $periodeColumns) {
                $data = [];

                foreach ($kategoriDefault as $kategori) {
                    $data[$kategori] = [
                        'target' => [],
                        'realisasi' => [],
                    ];

                    foreach ($periodeColumns as $key => $label) {
                        $data[$kategori]['target'][$key] = 0;
                        $data[$kategori]['realisasi'][$key] = 0;
                    }
                }

                return $data;
            };

            $anggota = \Illuminate\Support\Facades\DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->leftJoin(
                    'laboratorium_riset',
                    'users.id_lab',
                    '=',
                    'laboratorium_riset.id_lab'
                )
                ->whereIn('users.role', ['Anggota', 'anggota'])
                ->whereIn('users.id_lab', $idLabList)
                ->select(
                    'users.id_user',
                    'users.id_dosen',
                    'users.username',
                    'users.id_lab',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'dosen.jad',
                    'laboratorium_riset.nama_lab'
                )
                ->orderBy('laboratorium_riset.nama_lab')
                ->orderBy('dosen.nama_dosen')
                ->get();

            $rekapKategori = [];

            foreach ($kategoriDefault as $kategori) {
                $rekapKategori[$kategori] = [
                    'kategori' => $kategori,
                    'target' => 0,
                    'realisasi' => 0,
                    'progress' => 0,
                ];
            }

            $dataMonitoring = [];

            foreach ($anggota as $item) {
                $dataPerKategori = $buatDataKosong();

                $idAssignPerKategori = [];

                foreach ($kategoriDefault as $kategori) {
                    $idAssignPerKategori[$kategori] = [];
                }

                $assignmentQuery = \Illuminate\Support\Facades\DB::table('km_anggota as ka')
                    ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                    ->where('ka.id_user', $item->id_user)
                    ->where('kl.tahun_km', $tahun)
                    ->where('kl.status_km', 'Aktif')
                    ->select(
                        'ka.id_km_anggota',
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

                foreach ($assignments as $assignment) {
                    $kategori = $assignment->kategori_km;

                    if (!isset($dataPerKategori[$kategori])) {
                        continue;
                    }

                    $idAssignPerKategori[$kategori][] = $assignment->id_km_anggota;

                    if ($hasTriwulanKmLab) {
                        $twLabValues = [
                            1 => (int) ($assignment->triwulan_1 ?? 0),
                            2 => (int) ($assignment->triwulan_2 ?? 0),
                            3 => (int) ($assignment->triwulan_3 ?? 0),
                            4 => (int) ($assignment->triwulan_4 ?? 0),
                        ];
                    } else {
                        $jumlahLab = (int) ($assignment->jumlah_km_lab ?? 0);

                        $base = intdiv($jumlahLab, 4);
                        $sisa = $jumlahLab % 4;

                        $twLabValues = [
                            1 => $base + ($sisa >= 1 ? 1 : 0),
                            2 => $base + ($sisa >= 2 ? 1 : 0),
                            3 => $base + ($sisa >= 3 ? 1 : 0),
                            4 => $base,
                        ];
                    }

                    $twAnggota = $distribusiTargetAnggota(
                        (int) ($assignment->jumlah_km_anggota ?? 0),
                        $twLabValues
                    );

                    $periodeAnggota = $ubahTriwulanKePeriode($twAnggota);

                    foreach ($periodeColumns as $key => $label) {
                        $dataPerKategori[$kategori]['target'][$key] +=
                            (int) ($periodeAnggota[$key] ?? 0);
                    }
                }

                foreach ($kategoriDefault as $kategori) {
                    foreach ($periodeColumns as $key => $label) {
                        [$tanggalMulai, $tanggalSelesai] = $getRangeTanggal($key);

                        $queryRealisasi = \Illuminate\Support\Facades\DB::table('aktivitas_km as ak')
                            ->where('ak.id_user', $item->id_user)
                            ->where('ak.kategori_km', $kategori)
                            ->whereBetween('ak.tanggal_mulai', [
                                $tanggalMulai,
                                $tanggalSelesai,
                            ]);

                        if ($hasStatusProgress) {
                            $queryRealisasi->where('ak.status_progress', 'Accepted');
                        }

                        /*
                        | Jika aktivitas sudah punya id_km_anggota, maka realisasi
                        | dihitung hanya dari KM yang memang di-assign ke anggota.
                        */
                        if ($hasIdKmAnggotaAktivitas) {
                            $idAssign = $idAssignPerKategori[$kategori] ?? [];

                            if (count($idAssign) > 0) {
                                $queryRealisasi->whereIn('ak.id_km_anggota', $idAssign);
                            } else {
                                $queryRealisasi->whereRaw('1 = 0');
                            }
                        }

                        $dataPerKategori[$kategori]['realisasi'][$key] =
                            (int) $queryRealisasi->count();
                    }
                }

                $totalTarget = 0;
                $totalRealisasi = 0;

                foreach ($kategoriDefault as $kategori) {
                    $targetKategori = array_sum($dataPerKategori[$kategori]['target']);
                    $realisasiKategori = array_sum($dataPerKategori[$kategori]['realisasi']);

                    $totalTarget += $targetKategori;
                    $totalRealisasi += $realisasiKategori;

                    $rekapKategori[$kategori]['target'] += $targetKategori;
                    $rekapKategori[$kategori]['realisasi'] += $realisasiKategori;
                }

                $sisa = max($totalTarget - $totalRealisasi, 0);

                $persentase = $totalTarget > 0
                    ? min(round(($totalRealisasi / $totalTarget) * 100), 100)
                    : 0;

                if ($totalTarget <= 0) {
                    $statusProgress = 'Belum Ada KM';
                    $statusClass = 'secondary';
                } elseif ($totalRealisasi <= 0) {
                    $statusProgress = 'Belum Mulai';
                    $statusClass = 'danger';
                } elseif ($totalRealisasi >= $totalTarget) {
                    $statusProgress = 'Selesai';
                    $statusClass = 'success';
                } else {
                    $statusProgress = 'Sedang Progress';
                    $statusClass = 'warning';
                }

                $dataMonitoring[] = [
                    'id_user' => $item->id_user,
                    'username' => $item->username,
                    'nama_dosen' => $item->nama_dosen ?? $item->username,
                    'nidn' => $item->nidn ?? '-',
                    'email' => $item->email ?? '-',
                    'jad' => $item->jad ?? 'AA',
                    'nama_lab' => $item->nama_lab ?? '-',
                    'data' => $dataPerKategori,
                    'total_target' => $totalTarget,
                    'total_realisasi' => $totalRealisasi,
                    'sisa' => $sisa,
                    'persentase' => $persentase,
                    'status_progress' => $statusProgress,
                    'status_class' => $statusClass,
                ];
            }

            foreach ($rekapKategori as $kategori => $item) {
                $rekapKategori[$kategori]['progress'] = $item['target'] > 0
                    ? min(round(($item['realisasi'] / $item['target']) * 100), 100)
                    : 0;
            }

            $rekapKategori = array_values($rekapKategori);

            $jumlahAnggota = count($dataMonitoring);

            $jumlahSelesai = collect($dataMonitoring)
                ->where('status_progress', 'Selesai')
                ->count();

            $jumlahProgress = collect($dataMonitoring)
                ->where('status_progress', 'Sedang Progress')
                ->count();

            $jumlahBelumMulai = collect($dataMonitoring)
                ->filter(function ($item) {
                    return in_array(
                        $item['status_progress'],
                        ['Belum Mulai', 'Belum Ada KM']
                    );
                })
                ->count();

            return view('ketuakk.monitoring-anggota-kk.index', compact(
                'tahun',
                'tahunOptions',
                'periode',
                'periodeColumns',
                'labelPeriode',
                'kategoriDefault',
                'rekapKategori',
                'dataMonitoring',
                'jumlahAnggota',
                'jumlahSelesai',
                'jumlahProgress',
                'jumlahBelumMulai'
            ));
        });
        /*
        |--------------------------------------------------------------------------
        | DETAIL MONITORING ANGGOTA KK
        |--------------------------------------------------------------------------
        | GANTI route lama /ketuakk/monitoring-anggota-kk/{id} dengan route ini.
        | Filter hanya memakai Tahunan, Triwulan, dan Semester.
        */
        Route::get('/ketuakk/monitoring-anggota-kk/{id}', function (\Illuminate\Http\Request $request, $id) {
            $data = km_eims_get_ketuakk_anggota_detail_data($request, (int) $id);

            $data['pageTitle'] = 'Detail Monitoring';
            $data['pageMuted'] = 'Anggota KK';
            $data['detailDescription'] = 'Detail target, realisasi, dan aktivitas Kontrak Manajemen anggota KK.';
            $data['detailAction'] = '/ketuakk/monitoring-anggota-kk/' . $data['anggota']->id_user;
            $data['backUrl'] = '/ketuakk/monitoring-anggota-kk?' . http_build_query([
                'tahun' => $data['tahun'],
                'periode' => $data['periode'],
            ]);

            return view('ketuakk.monitoring-anggota-kk.detail', $data);
        })->name('ketuakk.monitoring-anggota-kk.detail');
        Route::get('/ketuakk/km-lab-riset/create', function (\Illuminate\Http\Request $request) {
            $userLogin = auth()->user();

            $ketuaKk = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_dosen', $userLogin->id_dosen)
                ->first();

            abort_unless($ketuaKk, 403, 'Data Ketua KK tidak ditemukan.');

            $labs = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_kk', $ketuaKk->id_kk)
                ->orderBy('id_lab')
                ->get();

            $targetOptions = \Illuminate\Support\Facades\DB::table('target_km')
                ->join(
                    'kontrak_manajemen',
                    'target_km.id_km',
                    '=',
                    'kontrak_manajemen.id_km'
                )
                ->select(
                    'target_km.id_target',
                    'target_km.kategori_km',
                    'target_km.indikator',
                    'target_km.keterangan',
                    'target_km.target',
                    'target_km.triwulan_1',
                    'target_km.triwulan_2',
                    'target_km.triwulan_3',
                    'target_km.triwulan_4',
                    'kontrak_manajemen.tahun_km'
                )
                ->where('kontrak_manajemen.id_dosen', $userLogin->id_dosen)
                ->orderBy('kontrak_manajemen.tahun_km', 'desc')
                ->orderBy('target_km.kategori_km')
                ->orderBy('target_km.indikator')
                ->get();

            $idTargetList = $targetOptions
                ->pluck('id_target')
                ->filter()
                ->values();

            $turunPerTarget = collect();

            if ($idTargetList->isNotEmpty()) {
                $turunPerTarget = \Illuminate\Support\Facades\DB::table('km_lab')
                    ->whereIn('id_target', $idTargetList)
                    ->select(
                        'id_target',
                        \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(triwulan_1), 0) as turun_tw1'),
                        \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(triwulan_2), 0) as turun_tw2'),
                        \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(triwulan_3), 0) as turun_tw3'),
                        \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(triwulan_4), 0) as turun_tw4')
                    )
                    ->groupBy('id_target')
                    ->get()
                    ->keyBy('id_target');
            }

            $targetOptions = $targetOptions->map(function ($target) use ($turunPerTarget) {
                $turun = $turunPerTarget->get($target->id_target);

                $target->turun_tw1 = (int) ($turun->turun_tw1 ?? 0);
                $target->turun_tw2 = (int) ($turun->turun_tw2 ?? 0);
                $target->turun_tw3 = (int) ($turun->turun_tw3 ?? 0);
                $target->turun_tw4 = (int) ($turun->turun_tw4 ?? 0);

                $target->sisa_tw1 = max((int) $target->triwulan_1 - $target->turun_tw1, 0);
                $target->sisa_tw2 = max((int) $target->triwulan_2 - $target->turun_tw2, 0);
                $target->sisa_tw3 = max((int) $target->triwulan_3 - $target->turun_tw3, 0);
                $target->sisa_tw4 = max((int) $target->triwulan_4 - $target->turun_tw4, 0);

                return $target;
            });

            $idTargetTerpilih = (int) $request->query('id_target', 0);

            $targetValid = $targetOptions->contains(function ($target) use ($idTargetTerpilih) {
                return (int) $target->id_target === $idTargetTerpilih;
            });

            if (!$targetValid) {
                $idTargetTerpilih = null;
            }

            return view('ketuakk.km-lab-riset.create', compact(
                'labs',
                'targetOptions',
                'idTargetTerpilih'
            ));
        });

        Route::post('/ketuakk/km-lab-riset', function (\Illuminate\Http\Request $request) {
            $request->validate([
                'id_lab' => 'required|exists:laboratorium_riset,id_lab',
                'id_target' => 'required|integer|exists:target_km,id_target',
                'triwulan_1' => 'required|integer|min:0',
                'triwulan_2' => 'required|integer|min:0',
                'triwulan_3' => 'required|integer|min:0',
                'triwulan_4' => 'required|integer|min:0',
                'status_km' => 'required|in:Aktif,Tidak Aktif',
            ]);

            $userLogin = auth()->user();

            $ketuaKk = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_dosen', $userLogin->id_dosen)
                ->first();

            abort_unless($ketuaKk, 403, 'Data Ketua KK tidak ditemukan.');

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $request->id_lab)
                ->where('id_kk', $ketuaKk->id_kk)
                ->first();

            if (!$lab) {
                return back()
                    ->withErrors([
                        'id_lab' => 'Lab Riset yang dipilih bukan bagian dari Kelompok Keahlian Anda.',
                    ])
                    ->withInput();
            }

            $target = \Illuminate\Support\Facades\DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->select(
                    'target_km.id_target',
                    'target_km.kategori_km',
                    'target_km.indikator',
                    'target_km.target',
                    'target_km.triwulan_1',
                    'target_km.triwulan_2',
                    'target_km.triwulan_3',
                    'target_km.triwulan_4',
                    'kontrak_manajemen.tahun_km'
                )
                ->where('target_km.id_target', $request->id_target)
                ->where('kontrak_manajemen.id_dosen', $userLogin->id_dosen)
                ->first();

            if (!$target) {
                return back()
                    ->withErrors([
                        'id_target' => 'Target KM tidak ditemukan atau bukan milik Ketua KK yang sedang login.',
                    ])
                    ->withInput();
            }

            $tw1 = (int) $request->triwulan_1;
            $tw2 = (int) $request->triwulan_2;
            $tw3 = (int) $request->triwulan_3;
            $tw4 = (int) $request->triwulan_4;

            $jumlahKm = $tw1 + $tw2 + $tw3 + $tw4;

            if ($jumlahKm <= 0) {
                return back()
                    ->withErrors([
                        'triwulan_1' => 'Minimal salah satu jumlah KM per triwulan harus diisi.',
                    ])
                    ->withInput();
            }

            /*
            * Semua data KM Lab yang sudah dibuat tetap dihitung,
            * baik status Aktif maupun Tidak Aktif, agar tidak ada
            * pembagian target yang melebihi target Ketua KK.
            */
            $sudahTurun = \Illuminate\Support\Facades\DB::table('km_lab')
                ->where('id_target', $target->id_target)
                ->select(
                    \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(triwulan_1), 0) as total_tw1'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(triwulan_2), 0) as total_tw2'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(triwulan_3), 0) as total_tw3'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(triwulan_4), 0) as total_tw4')
                )
                ->first();

            $sisaTw1 = max((int) $target->triwulan_1 - (int) $sudahTurun->total_tw1, 0);
            $sisaTw2 = max((int) $target->triwulan_2 - (int) $sudahTurun->total_tw2, 0);
            $sisaTw3 = max((int) $target->triwulan_3 - (int) $sudahTurun->total_tw3, 0);
            $sisaTw4 = max((int) $target->triwulan_4 - (int) $sudahTurun->total_tw4, 0);

            $errors = [];

            if ($tw1 > $sisaTw1) {
                $errors['triwulan_1'] = 'Jumlah KM Triwulan 1 melebihi sisa target. Sisa yang dapat diturunkan: ' . $sisaTw1 . '.';
            }

            if ($tw2 > $sisaTw2) {
                $errors['triwulan_2'] = 'Jumlah KM Triwulan 2 melebihi sisa target. Sisa yang dapat diturunkan: ' . $sisaTw2 . '.';
            }

            if ($tw3 > $sisaTw3) {
                $errors['triwulan_3'] = 'Jumlah KM Triwulan 3 melebihi sisa target. Sisa yang dapat diturunkan: ' . $sisaTw3 . '.';
            }

            if ($tw4 > $sisaTw4) {
                $errors['triwulan_4'] = 'Jumlah KM Triwulan 4 melebihi sisa target. Sisa yang dapat diturunkan: ' . $sisaTw4 . '.';
            }

            if (!empty($errors)) {
                return back()
                    ->withErrors($errors)
                    ->withInput();
            }

            \Illuminate\Support\Facades\DB::table('km_lab')->insert([
                'id_target' => $target->id_target,
                'id_lab' => $lab->id_lab,
                'tahun_km' => $target->tahun_km,
                'kategori_km' => $target->kategori_km,
                'sub_kategori_km' => $target->indikator,
                'triwulan_1' => $tw1,
                'triwulan_2' => $tw2,
                'triwulan_3' => $tw3,
                'triwulan_4' => $tw4,
                'jumlah_km' => $jumlahKm,
                'status_km' => $request->status_km,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect('/ketuakk/km-lab-riset?tahun=' . $target->tahun_km)
                ->with('success', 'KM berhasil diturunkan ke Lab Riset berdasarkan pembagian per triwulan.');
        });
        Route::get('/ketuakk/km-lab-riset', function () {
            $tahun = (int) request('tahun', now()->year);

            $kategoriDefault = [
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $tahunOptions = collect()
                ->merge(
                    \Illuminate\Support\Facades\DB::table('kontrak_manajemen')
                        ->select('tahun_km')
                        ->distinct()
                        ->pluck('tahun_km')
                )
                ->merge(
                    \Illuminate\Support\Facades\DB::table('km_lab')
                        ->select('tahun_km')
                        ->distinct()
                        ->pluck('tahun_km')
                )
                ->push(now()->year)
                ->unique()
                ->sortDesc()
                ->values();

            if (! $tahunOptions->contains($tahun)) {
                $tahunOptions->push($tahun);
                $tahunOptions = $tahunOptions->unique()->sortDesc()->values();
            }

            $totalKmKkPerKategori = \Illuminate\Support\Facades\DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->select(
                    'target_km.kategori_km',
                    \Illuminate\Support\Facades\DB::raw('SUM(target_km.target) as total_target')
                )
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->groupBy('target_km.kategori_km')
                ->pluck('total_target', 'kategori_km');

            $kmTurunPerLabKategori = \Illuminate\Support\Facades\DB::table('km_lab')
                ->select(
                    'id_lab',
                    'kategori_km',
                    \Illuminate\Support\Facades\DB::raw('SUM(jumlah_km) as total_turun')
                )
                ->where('tahun_km', $tahun)
                ->where('status_km', 'Aktif')
                ->groupBy('id_lab', 'kategori_km')
                ->get()
                ->groupBy('id_lab');

            $assignPerLab = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->select(
                    'km_lab.id_lab',
                    \Illuminate\Support\Facades\DB::raw('SUM(km_anggota.jumlah_km) as total_assign')
                )
                ->where('km_lab.tahun_km', $tahun)
                ->where('km_lab.status_km', 'Aktif')
                ->groupBy('km_lab.id_lab')
                ->pluck('total_assign', 'id_lab');

            $labs = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->orderBy('id_lab')
                ->get();

            $dataLab = [];

            foreach ($labs as $lab) {
                $turunGroup = $kmTurunPerLabKategori->get($lab->id_lab, collect());

                $kkPerKategori = [];
                $turunPerKategori = [];

                foreach ($kategoriDefault as $kategori) {
                    $kkPerKategori[$kategori] = (int) ($totalKmKkPerKategori[$kategori] ?? 0);

                    $turunItem = $turunGroup->firstWhere('kategori_km', $kategori);
                    $turunPerKategori[$kategori] = (int) ($turunItem->total_turun ?? 0);
                }

                $totalTurun = array_sum($turunPerKategori);
                $totalAssign = (int) ($assignPerLab[$lab->id_lab] ?? 0);
                $sisaKm = max($totalTurun - $totalAssign, 0);

                $persentase = $totalTurun > 0
                    ? min(round(($totalAssign / $totalTurun) * 100), 100)
                    : 0;

                if ($totalTurun <= 0) {
                    $status = 'Belum Ada KM';
                } elseif ($sisaKm <= 0) {
                    $status = 'Selesai';
                } else {
                    $status = 'Belum Selesai';
                }

                $dataLab[] = [
                    'id_lab' => $lab->id_lab,
                    'nama_lab' => $lab->nama_lab,
                    'kk_per_kategori' => $kkPerKategori,
                    'turun_per_kategori' => $turunPerKategori,
                    'total_turun' => $totalTurun,
                    'total_assign' => $totalAssign,
                    'sisa_km' => $sisaKm,
                    'persentase' => $persentase,
                    'status' => $status,
                ];
            }

            $rekapKategori = [];

            foreach ($kategoriDefault as $kategori) {
                $totalKk = (int) ($totalKmKkPerKategori[$kategori] ?? 0);

                $totalTurun = \Illuminate\Support\Facades\DB::table('km_lab')
                    ->where('tahun_km', $tahun)
                    ->where('status_km', 'Aktif')
                    ->where('kategori_km', $kategori)
                    ->sum('jumlah_km');

                $rekapKategori[] = [
                    'kategori' => $kategori,
                    'total_km_kk' => $totalKk,
                    'total_turun' => (int) $totalTurun,
                    'sisa' => max($totalKk - (int) $totalTurun, 0),
                ];
            }
            /*
            |--------------------------------------------------------------------------
            | Riwayat Penurunan KM ke Lab Riset
            |--------------------------------------------------------------------------
            | Mengambil seluruh data penurunan dari km_lab untuk Lab milik Ketua KK.
            | Tidak difilter status agar riwayat lama tetap terlihat.
            */
            $labIds = $labs->pluck('id_lab')
                ->filter()
                ->values();

            $riwayatPenurunanKm = collect();

            if ($labIds->isNotEmpty()) {
                $riwayatPenurunanKm = \Illuminate\Support\Facades\DB::table('km_lab as kl')
                    ->leftJoin(
                        'laboratorium_riset as lr',
                        'kl.id_lab',
                        '=',
                        'lr.id_lab'
                    )
                    ->leftJoin(
                        'target_km as tk',
                        'kl.id_target',
                        '=',
                        'tk.id_target'
                    )
                    ->whereIn('kl.id_lab', $labIds)
                    ->where('kl.tahun_km', $tahun)
                    ->select(
                        'kl.id_km_lab',
                        'kl.id_lab',
                        'lr.nama_lab',
                        'kl.kategori_km',
                        \Illuminate\Support\Facades\DB::raw("
                            COALESCE(
                                NULLIF(kl.sub_kategori_km, ''),
                                NULLIF(tk.indikator, ''),
                                '-'
                            ) as sub_kategori_km
                        "),
                        \Illuminate\Support\Facades\DB::raw("
                            COALESCE(NULLIF(tk.keterangan, ''), '-') as keterangan
                        "),
                        \Illuminate\Support\Facades\DB::raw('COALESCE(kl.triwulan_1, 0) as triwulan_1'),
                        \Illuminate\Support\Facades\DB::raw('COALESCE(kl.triwulan_2, 0) as triwulan_2'),
                        \Illuminate\Support\Facades\DB::raw('COALESCE(kl.triwulan_3, 0) as triwulan_3'),
                        \Illuminate\Support\Facades\DB::raw('COALESCE(kl.triwulan_4, 0) as triwulan_4'),
                        \Illuminate\Support\Facades\DB::raw('COALESCE(kl.jumlah_km, 0) as jumlah_km'),
                        'kl.status_km',
                        \Illuminate\Support\Facades\DB::raw("
                            COALESCE(kl.created_at, kl.updated_at) as waktu_penurunan
                        ")
                    )
                    ->orderByDesc('kl.created_at')
                    ->orderByDesc('kl.id_km_lab')
                    ->get();
            }
            return view('ketuakk.km-lab-riset.index', compact(
                'dataLab',
                'tahun',
                'tahunOptions',
                'kategoriDefault',
                'rekapKategori',
                'riwayatPenurunanKm'
            ));
        });
        Route::delete('/ketuakk/km-lab-riset/{id}', function ($id) {
            $kmLab = \Illuminate\Support\Facades\DB::table('km_lab')
                ->where('id_km_lab', $id)
                ->first();

            if (!$kmLab) {
                return redirect('/ketuakk/km-lab-riset')
                    ->with('error', 'Data KM lab tidak ditemukan.');
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
                \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->where('id_km_lab', $id)
                    ->delete();

                \Illuminate\Support\Facades\DB::table('km_lab')
                    ->where('id_km_lab', $id)
                    ->delete();
            });

            return redirect('/ketuakk/km-lab-riset')
                ->with('success', 'KM yang diturunkan ke lab berhasil dihapus.');
        });
        Route::get('/ketuakk/km-lab-riset/{id}', function ($id) {
            $tahun = (int) request('tahun', now()->year);

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $id)
                ->first();

            if (!$lab) {
                abort(404);
            }

            $kategoriDefault = [
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $jenisByKategori = [
                'Penelitian' => 'Penelitian',
                'Publikasi' => 'Jurnal',
                'Pengabdian' => 'Pengabdian',
                'Penunjang' => 'Penunjang',
            ];

            $targetReferensi = \Illuminate\Support\Facades\DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->select(
                    'target_km.kategori_km',
                    'target_km.indikator',
                    'target_km.keterangan',
                    'target_km.triwulan_1',
                    'target_km.triwulan_2',
                    'target_km.triwulan_3',
                    'target_km.triwulan_4',
                    'target_km.target'
                )
                ->get()
                ->keyBy(function ($item) {
                    return $item->kategori_km . '|' . $item->indikator;
                });

            $daftarKmTurunRaw = \Illuminate\Support\Facades\DB::table('km_lab')
                ->leftJoin('target_km', 'km_lab.id_target', '=', 'target_km.id_target')
                ->where('km_lab.id_lab', $id)
                ->where('km_lab.tahun_km', $tahun)
                ->where('km_lab.status_km', 'Aktif')
                ->select(
                    'km_lab.*',
                    'target_km.keterangan as keterangan_target'
                )
                ->orderBy('km_lab.created_at', 'desc')
                ->get();

            $daftarKmTurun = [];

            foreach ($daftarKmTurunRaw as $km) {
                $sudahAssign = \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->where('id_km_lab', $km->id_km_lab)
                    ->sum('jumlah_km');

                $sisaKm = max($km->jumlah_km - $sudahAssign, 0);

                $persentase = $km->jumlah_km > 0
                    ? min(round(($sudahAssign / $km->jumlah_km) * 100), 100)
                    : 0;

                $subKategori = $km->sub_kategori_km ?? '-';
                $targetKey = $km->kategori_km . '|' . $subKategori;
                $targetData = $targetReferensi->get($targetKey);

                $daftarKmTurun[] = [
                    'id_km_lab' => $km->id_km_lab,
                    'kategori_km' => $km->kategori_km,
                    'jenis_km' => $jenisByKategori[$km->kategori_km] ?? '-',
                    'sub_kategori_km' => $km->sub_kategori_km ?? '-',
                    'keterangan' => $km->keterangan_target ?? '-',
                    'triwulan_1' => (int) ($km->triwulan_1 ?? 0),
                    'triwulan_2' => (int) ($km->triwulan_2 ?? 0),
                    'triwulan_3' => (int) ($km->triwulan_3 ?? 0),
                    'triwulan_4' => (int) ($km->triwulan_4 ?? 0),
                    'jumlah_km' => (int) $km->jumlah_km,
                    'tahun_km' => $km->tahun_km,
                    'status_km' => $km->status_km,
                    'created_at' => $km->created_at,
                    'sudah_assign' => $sudahAssign,
                    'sisa_km' => $sisaKm,
                    'persentase' => $persentase,
                ];
            }

            $rekapKategori = collect($daftarKmTurun)
                ->groupBy('kategori_km')
                ->map(function ($items, $kategori) {
                    return [
                        'kategori' => $kategori,
                        'total_km' => $items->sum('jumlah_km'),
                        'sudah_assign' => $items->sum('sudah_assign'),
                        'sisa_km' => $items->sum('sisa_km'),
                        'persentase' => $items->sum('jumlah_km') > 0
                            ? min(round(($items->sum('sudah_assign') / $items->sum('jumlah_km')) * 100), 100)
                            : 0,
                    ];
                })
                ->values();

            foreach ($kategoriDefault as $kategori) {
                if (!$rekapKategori->firstWhere('kategori', $kategori)) {
                    $rekapKategori->push([
                        'kategori' => $kategori,
                        'total_km' => 0,
                        'sudah_assign' => 0,
                        'sisa_km' => 0,
                        'persentase' => 0,
                    ]);
                }
            }

            $riwayatAssign = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->join('users', 'km_anggota.id_user', '=', 'users.id_user')
                ->leftJoin('dosen', 'km_anggota.id_dosen', '=', 'dosen.id_dosen')
                ->where('km_lab.id_lab', $id)
                ->where('km_lab.tahun_km', $tahun)
                ->select(
                    'km_anggota.id_km_anggota',
                    'km_anggota.jumlah_km',
                    'km_anggota.created_at',
                    'km_lab.kategori_km',
                    'km_lab.sub_kategori_km',
                    'users.username',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'dosen.jad'
                )
                ->orderBy('km_anggota.created_at', 'desc')
                ->get();

            $anggota = \Illuminate\Support\Facades\DB::table('users')
                ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
                ->where('users.id_lab', $id)
                ->whereIn('users.role', ['anggota', 'Anggota'])
                ->select(
                    'users.id_user',
                    'users.username',
                    'dosen.nama_dosen',
                    'dosen.nidn',
                    'dosen.email',
                    'dosen.jad'
                )
                ->orderBy('dosen.nama_dosen')
                ->get();

            $totalKmTurun = collect($daftarKmTurun)->sum('jumlah_km');
            $totalKmAssign = collect($daftarKmTurun)->sum('sudah_assign');
            $totalSisaKm = max($totalKmTurun - $totalKmAssign, 0);
            $persentaseTotal = $totalKmTurun > 0
                ? min(round(($totalKmAssign / $totalKmTurun) * 100), 100)
                : 0;

            return view('ketuakk.km-lab-riset.detail', compact(
                'lab',
                'tahun',
                'daftarKmTurun',
                'rekapKategori',
                'totalKmTurun',
                'totalKmAssign',
                'totalSisaKm',
                'persentaseTotal',
                'riwayatAssign',
                'anggota'
            ));
        });
        Route::get('/ketuakk/km-anggota-kk', function () {
            $tahun = (int) request('tahun', now()->year);

            $kategoriDefault = [
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $tahunOptions = collect(
                \Illuminate\Support\Facades\DB::table('km_lab')
                    ->select('tahun_km')
                    ->distinct()
                    ->orderBy('tahun_km', 'desc')
                    ->pluck('tahun_km')
            )
                ->push(now()->year)
                ->unique()
                ->sortDesc()
                ->values();

            $userLogin = auth()->user();

            $ketuaKkData = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_dosen', $userLogin->id_dosen)
                ->first();

            $anggota = \Illuminate\Support\Facades\DB::table('users as u')
                ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
                ->leftJoin('laboratorium_riset as lr_user', 'u.id_lab', '=', 'lr_user.id_lab')
                ->leftJoin('laboratorium_riset as lr_dosen', 'd.id_lab', '=', 'lr_dosen.id_lab')
                ->whereIn('u.role', ['Anggota', 'anggota'])
                ->where(function ($query) use ($ketuaKkData) {
                    $idKk = $ketuaKkData->id_kk ?? null;

                    $query->where('d.id_kk', $idKk)
                        ->orWhere('lr_user.id_kk', $idKk)
                        ->orWhere('lr_dosen.id_kk', $idKk);
                })
                ->select(
                    'u.id_user',
                    'd.id_dosen',
                    'u.username',
                    \Illuminate\Support\Facades\DB::raw('COALESCE(u.id_lab, d.id_lab) as id_lab'),
                    'd.nama_dosen',
                    'd.nidn',
                    'd.email',
                    'd.jad',
                    \Illuminate\Support\Facades\DB::raw('COALESCE(lr_user.nama_lab, lr_dosen.nama_lab) as nama_lab')
                )
                ->distinct()
                ->orderBy('nama_lab')
                ->orderBy('d.nama_dosen')
                ->get();

            $dataAnggota = [];

            foreach ($anggota as $item) {
                $jumlahKmPerKategori = [];

                foreach ($kategoriDefault as $kategori) {
                    $jumlahKmPerKategori[$kategori] = 0;
                }

                if (!empty($item->id_user)) {
                    $targetPerKategori = \Illuminate\Support\Facades\DB::table('km_anggota')
                        ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                        ->select(
                            'km_lab.kategori_km',
                            \Illuminate\Support\Facades\DB::raw('SUM(km_anggota.jumlah_km) as total_km')
                        )
                        ->where('km_anggota.id_user', $item->id_user)
                        ->where('km_lab.tahun_km', $tahun)
                        ->where('km_lab.status_km', 'Aktif')
                        ->groupBy('km_lab.kategori_km')
                        ->pluck('total_km', 'kategori_km');

                    foreach ($kategoriDefault as $kategori) {
                        $jumlahKmPerKategori[$kategori] = (int) ($targetPerKategori[$kategori] ?? 0);
                    }
                }

                $dataAnggota[] = [
                    'id_user' => $item->id_user,
                    'nama_dosen' => $item->nama_dosen ?? $item->username ?? '-',
                    'nidn' => $item->nidn ?? '-',
                    'jad' => $item->jad ?? '-',
                    'email' => $item->email ?? '-',
                    'nama_lab' => $item->nama_lab ?? '-',
                    'jumlah_km' => $jumlahKmPerKategori,
                    'total_km' => array_sum($jumlahKmPerKategori),
                ];
            }

            return view('ketuakk.km-anggota-kk.index', compact(
                'dataAnggota',
                'tahun',
                'tahunOptions',
                'kategoriDefault'
            ));
        });
        Route::get('/ketuakk/km-anggota-kk/{id}', function (\Illuminate\Http\Request $request, $id) {
            $data = km_eims_get_ketuakk_anggota_detail_data($request, (int) $id);

            $data['pageTitle'] = 'Detail KM';
            $data['pageMuted'] = 'Anggota KK';
            $data['detailDescription'] = 'Detail target, realisasi, dan aktivitas Kontrak Manajemen anggota KK.';
            $data['detailAction'] = '/ketuakk/km-anggota-kk/' . $data['anggota']->id_user;
            $data['backUrl'] = '/ketuakk/km-anggota-kk?' . http_build_query([
                'tahun' => $data['tahun'],
            ]);

            return view('ketuakk.km-anggota-kk.detail', $data);
        });
        Route::get('/ketuakk/km-kk', function () {
            $tahun = (int) request('tahun', now()->year);

            $kategoriDefault = [
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $jenisByKategori = [
                'Penelitian' => 'Penelitian',
                'Publikasi' => 'Publikasi',
                'Pengabdian' => 'Pengabdian',
                'Penunjang' => 'Penunjang',
            ];

            /** @var \App\Models\User $userLogin */
            $userLogin = auth()->user();

            $dosenLogin = \Illuminate\Support\Facades\DB::table('dosen')
                ->where('id_dosen', $userLogin->id_dosen)
                ->first();

            abort_unless($dosenLogin, 403, 'Data Ketua KK tidak ditemukan.');

            $idKk = $dosenLogin->id_kk;

            $hasKeteranganColumn = \Illuminate\Support\Facades\Schema::hasColumn(
                'target_km',
                'keterangan'
            );

            $tahunOptions = collect()
                ->merge(
                    \Illuminate\Support\Facades\DB::table('kontrak_manajemen')
                        ->where('id_dosen', $userLogin->id_dosen)
                        ->select('tahun_km')
                        ->distinct()
                        ->pluck('tahun_km')
                )
                ->merge(
                    \Illuminate\Support\Facades\DB::table('km_lab')
                        ->join(
                            'laboratorium_riset',
                            'km_lab.id_lab',
                            '=',
                            'laboratorium_riset.id_lab'
                        )
                        ->where('laboratorium_riset.id_kk', $idKk)
                        ->select('km_lab.tahun_km')
                        ->distinct()
                        ->pluck('km_lab.tahun_km')
                )
                ->push(now()->year)
                ->map(fn ($item) => (int) $item)
                ->unique()
                ->sortDesc()
                ->values();

            /*
            |--------------------------------------------------------------------------
            | Target KM Ketua KK + Deadline Triwulan
            |--------------------------------------------------------------------------
            */
            $targetQuery = \Illuminate\Support\Facades\DB::table('target_km')
                ->join(
                    'kontrak_manajemen',
                    'target_km.id_km',
                    '=',
                    'kontrak_manajemen.id_km'
                )
                ->where('kontrak_manajemen.id_dosen', $userLogin->id_dosen)
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->select(
                    'target_km.id_target',
                    'target_km.kategori_km',
                    'target_km.indikator',
                    'target_km.triwulan_1',
                    'target_km.triwulan_2',
                    'target_km.triwulan_3',
                    'target_km.triwulan_4',
                    'target_km.target',
                    'target_km.tanggal_mulai_tw1',
                    'target_km.tanggal_selesai_tw1',
                    'target_km.tanggal_mulai_tw2',
                    'target_km.tanggal_selesai_tw2',
                    'target_km.tanggal_mulai_tw3',
                    'target_km.tanggal_selesai_tw3',
                    'target_km.tanggal_mulai_tw4',
                    'target_km.tanggal_selesai_tw4'
                );

            if ($hasKeteranganColumn) {
                $targetQuery->addSelect('target_km.keterangan');
            }

            $targetRawRows = $targetQuery
                ->orderByRaw("CASE target_km.kategori_km
                    WHEN 'Penelitian' THEN 1
                    WHEN 'Publikasi' THEN 2
                    WHEN 'Pengabdian' THEN 3
                    WHEN 'Penunjang' THEN 4
                    ELSE 5
                END")
                ->orderBy('target_km.indikator')
                ->get();

            $idTargetList = $targetRawRows
                ->pluck('id_target')
                ->filter()
                ->values();

            $turunPerTarget = collect();

            if ($idTargetList->isNotEmpty()) {
                $turunPerTarget = \Illuminate\Support\Facades\DB::table('km_lab')
                    ->whereIn('id_target', $idTargetList)
                    ->select(
                        'id_target',
                        \Illuminate\Support\Facades\DB::raw(
                            'COALESCE(SUM(jumlah_km), 0) as total_turun'
                        )
                    )
                    ->groupBy('id_target')
                    ->pluck('total_turun', 'id_target');
            }

            $targetRows = $targetRawRows->map(function ($item) use (
                $jenisByKategori,
                $hasKeteranganColumn,
                $turunPerTarget
            ) {
                $totalTarget = (int) ($item->target ?? 0);
                $sudahTurun = (int) ($turunPerTarget[$item->id_target] ?? 0);
                $sisaBelumTurun = max($totalTarget - $sudahTurun, 0);

                return [
                    'id_target' => $item->id_target,
                    'kategori_km' => $item->kategori_km ?? '-',
                    'jenis_km' => $jenisByKategori[$item->kategori_km] ?? ($item->kategori_km ?? '-'),
                    'sub_kategori_km' => $item->indikator ?? '-',
                    'keterangan' => $hasKeteranganColumn
                        ? ($item->keterangan ?? '-')
                        : '-',

                    'triwulan_1' => (int) ($item->triwulan_1 ?? 0),
                    'triwulan_2' => (int) ($item->triwulan_2 ?? 0),
                    'triwulan_3' => (int) ($item->triwulan_3 ?? 0),
                    'triwulan_4' => (int) ($item->triwulan_4 ?? 0),

                    'tanggal_mulai_tw1' => $item->tanggal_mulai_tw1 ?? null,
                    'tanggal_selesai_tw1' => $item->tanggal_selesai_tw1 ?? null,
                    'tanggal_mulai_tw2' => $item->tanggal_mulai_tw2 ?? null,
                    'tanggal_selesai_tw2' => $item->tanggal_selesai_tw2 ?? null,
                    'tanggal_mulai_tw3' => $item->tanggal_mulai_tw3 ?? null,
                    'tanggal_selesai_tw3' => $item->tanggal_selesai_tw3 ?? null,
                    'tanggal_mulai_tw4' => $item->tanggal_mulai_tw4 ?? null,
                    'tanggal_selesai_tw4' => $item->tanggal_selesai_tw4 ?? null,

                    'total_target' => $totalTarget,
                    'sudah_turun' => $sudahTurun,
                    'sisa_belum_turun' => $sisaBelumTurun,
                ];
            });

            /*
            |--------------------------------------------------------------------------
            | Rekap Lab Riset
            |--------------------------------------------------------------------------
            */
            $labList = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_kk', $idKk)
                ->orderBy('id_lab')
                ->get();

            $rekapLab = $labList->map(function ($lab) use (
                $tahun,
                $kategoriDefault,
                $userLogin
            ) {
                $kmLabRows = \Illuminate\Support\Facades\DB::table('km_lab as kl')
                    ->join('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                    ->join(
                        'kontrak_manajemen as km',
                        'tk.id_km',
                        '=',
                        'km.id_km'
                    )
                    ->where('kl.id_lab', $lab->id_lab)
                    ->where('kl.tahun_km', $tahun)
                    ->where('km.id_dosen', $userLogin->id_dosen)
                    ->select(
                        'kl.id_km_lab',
                        'kl.jumlah_km',
                        'tk.kategori_km'
                    )
                    ->get();

                $jumlahPerKategori = [];

                foreach ($kategoriDefault as $kategori) {
                    $jumlahPerKategori[$kategori] = 0;
                }

                foreach ($kmLabRows as $row) {
                    if (isset($jumlahPerKategori[$row->kategori_km])) {
                        $jumlahPerKategori[$row->kategori_km] +=
                            (int) ($row->jumlah_km ?? 0);
                    }
                }

                $totalTurun = (int) $kmLabRows->sum('jumlah_km');

                $sudahDibagiKeAnggota = (int) \Illuminate\Support\Facades\DB::table('km_anggota as ka')
                    ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                    ->join('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                    ->join(
                        'kontrak_manajemen as km',
                        'tk.id_km',
                        '=',
                        'km.id_km'
                    )
                    ->where('kl.id_lab', $lab->id_lab)
                    ->where('kl.tahun_km', $tahun)
                    ->where('km.id_dosen', $userLogin->id_dosen)
                    ->sum('ka.jumlah_km');

                $sisaKm = max($totalTurun - $sudahDibagiKeAnggota, 0);

                $progress = $totalTurun > 0
                    ? min(round(($sudahDibagiKeAnggota / $totalTurun) * 100), 100)
                    : 0;

                if ($totalTurun <= 0) {
                    $status = 'Belum Ada KM';
                } elseif ($sisaKm <= 0) {
                    $status = 'Selesai';
                } else {
                    $status = 'Belum Selesai';
                }

                return [
                    'id_lab' => $lab->id_lab,
                    'nama_lab' => $lab->nama_lab,
                    'jumlah_per_kategori' => $jumlahPerKategori,
                    'total_turun' => $totalTurun,
                    'sudah_dibagi_ke_anggota' => $sudahDibagiKeAnggota,
                    'sisa_km' => $sisaKm,
                    'progress' => $progress,
                    'status' => $status,
                ];
            });

            return view('ketuakk.km-kk.index', compact(
                'tahun',
                'tahunOptions',
                'targetRows',
                'rekapLab',
                'kategoriDefault'
            ));
        });
        Route::get('/ketuakk/laporan', [KetuaKkReportController::class, 'index'])
            ->name('ketuakk.laporan.index');
        Route::get('/ketuakk/laporan/download', [KetuaKkReportController::class, 'download'])
            ->name('ketuakk.laporan.download');
        Route::delete('/ketuakk/data-dosen/{id}', function ($id) {
            try {
                $dosen = \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_dosen', $id)
                    ->first();

                if (!$dosen) {
                    return redirect('/ketuakk/data-dosen')
                        ->with('error', 'Data dosen tidak ditemukan.');
                }

                $dipakaiUser = \Illuminate\Support\Facades\DB::table('users')
                    ->where('id_dosen', $id)
                    ->exists();

                if ($dipakaiUser) {
                    return redirect('/ketuakk/data-dosen')
                        ->with('error', 'Data dosen tidak bisa dihapus karena masih terhubung dengan akun user.');
                }
                \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_dosen', $id)
                    ->delete();

                return redirect('/ketuakk/data-dosen')
                    ->with('success', 'Data dosen berhasil dihapus.');
            } catch (\Exception $e) {
                return redirect('/ketuakk/data-dosen')
                    ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
            }
        });
    });


    // RUANG KHUSUS KETUA LAB
    Route::middleware(['auth', 'role:Ketua Lab'])->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Dashboard Ketua Lab
        |--------------------------------------------------------------------------
        | Letakkan di dalam Route::middleware(['auth', 'role:Ketua Lab'])->group(...)
        | GANTI SELURUH route lama /ketualab/dashboard yang masih berupa closure.
        */
        Route::get('/ketualab/dashboard', [KetuaLabController::class, 'dashboard'])
            ->name('ketualab.dashboard');

        Route::get('/ketualab/penurunan-km', [KetuaLabController::class, 'pembagianKmAnggota']);
        Route::post('/ketualab/penurunan-km', [KetuaLabController::class, 'simpanPembagianKmAnggota']);
        Route::delete('/ketualab/penurunan-km/assign/{id}', function (\Illuminate\Http\Request $request, $id) {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $dosenLogin = ! empty($user->id_dosen)
                ? \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_dosen', $user->id_dosen)
                    ->first()
                : null;

            $idLab = $user->id_lab ?? ($dosenLogin->id_lab ?? null);

            abort_unless($idLab, 403, 'Lab Riset Ketua Lab tidak ditemukan.');

            $assign = \Illuminate\Support\Facades\DB::table('km_anggota as ka')
                ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                ->where('ka.id_km_anggota', $id)
                ->where('kl.id_lab', $idLab)
                ->select('ka.id_km_anggota', 'kl.tahun_km')
                ->first();

            if (! $assign) {
                return redirect('/ketualab/penurunan-km?tahun=' . (int) $request->input('tahun', now()->year))
                    ->with('error', 'Data assign KM tidak ditemukan atau bukan milik lab Anda.');
            }

            \Illuminate\Support\Facades\DB::table('km_anggota')
                ->where('id_km_anggota', $assign->id_km_anggota)
                ->delete();

            return redirect('/ketualab/penurunan-km?tahun=' . (int) $assign->tahun_km)
                ->with('success', 'Assign KM anggota berhasil dihapus.');
        });
        Route::get('/ketualab/penurunan-km/{id}/plot', [KetuaLabController::class, 'createPlot']);
        Route::post('/ketualab/penurunan-km/{id}/plot', [KetuaLabController::class, 'storePlot']);
        Route::get('/ketualab/monitoring-lab', function (\Illuminate\Http\Request $request) {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            /*
            |--------------------------------------------------------------------------
            | Identitas Ketua Lab dan Lab Riset
            |--------------------------------------------------------------------------
            */
            $idLab = $user->id_lab
                ?? \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_dosen', $user->id_dosen)
                    ->value('id_lab');

            abort_unless($idLab, 403, 'Lab Riset Ketua Lab tidak ditemukan.');

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $idLab)
                ->first();

            abort_unless($lab, 404, 'Data Lab Riset tidak ditemukan.');

            /*
            |--------------------------------------------------------------------------
            | Filter Waktu: Tahunan / Triwulan / Semester
            |--------------------------------------------------------------------------
            */
            $tahun = (int) $request->query('tahun', now()->year);

            $periode = $request->query('periode', 'tahun');
            $periode = in_array($periode, ['tahun', 'triwulan', 'semester'], true)
                ? $periode
                : 'tahun';

            $triwulan = (int) $request->query('triwulan', ceil(now()->month / 3));
            $triwulan = max(1, min(4, $triwulan));

            $semester = (int) $request->query('semester', now()->month <= 6 ? 1 : 2);
            $semester = max(1, min(2, $semester));

            $triwulanAktif = match ($periode) {
                'triwulan' => [$triwulan],
                'semester'  => $semester === 1 ? [1, 2] : [3, 4],
                default     => [1, 2, 3, 4],
            };

            $bulanMulai = min($triwulanAktif) * 3 - 2;
            $bulanSelesai = max($triwulanAktif) * 3;

            $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfDay();
            $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();

            $labelPeriode = match ($periode) {
                'triwulan' => 'Triwulan ' . $triwulan . ' Tahun ' . $tahun,
                'semester' => 'Semester ' . $semester . ' Tahun ' . $tahun,
                default => 'Tahunan ' . $tahun,
            };

            $periodeSingkat = match ($periode) {
                'triwulan' => 'TW ' . $triwulan,
                'semester' => 'Semester ' . $semester,
                default => 'Tahun ' . $tahun,
            };

            $periodeInfo = match ($periode) {
                'triwulan' => 'Data target, realisasi, dan pembagian KM ditampilkan untuk Triwulan ' . $triwulan . '.',
                'semester' => 'Data target, realisasi, dan pembagian KM ditampilkan untuk Semester ' . $semester . '.',
                default => 'Data target, realisasi, dan pembagian KM ditampilkan untuk satu tahun penuh.',
            };

            $tahunOptions = collect()
                ->merge(
                    \Illuminate\Support\Facades\DB::table('km_lab')
                        ->where('id_lab', $idLab)
                        ->select('tahun_km')
                        ->distinct()
                        ->pluck('tahun_km')
                )
                ->merge(
                    \Illuminate\Support\Facades\DB::table('aktivitas_km')
                        ->where('id_lab', $idLab)
                        ->selectRaw('CAST(strftime("%Y", tanggal_mulai) AS INTEGER) as tahun')
                        ->whereNotNull('tanggal_mulai')
                        ->distinct()
                        ->pluck('tahun')
                )
                ->push(now()->year)
                ->push($tahun)
                ->filter()
                ->unique()
                ->sortDesc()
                ->values();

            /*
            |--------------------------------------------------------------------------
            | Anggota Lab
            |--------------------------------------------------------------------------
            | Sebagian data anggota dapat terhubung langsung melalui users.id_lab,
            | sebagian lain melalui dosen.id_lab. Keduanya dihitung agar aman.
            |--------------------------------------------------------------------------
            */
            $anggotaLab = \Illuminate\Support\Facades\DB::table('users as u')
                ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
                ->where('u.role', 'Anggota')
                ->where(function ($query) use ($idLab) {
                    $query->where('u.id_lab', $idLab)
                        ->orWhere('d.id_lab', $idLab);
                })
                ->select('u.id_user')
                ->distinct()
                ->get();

            $jumlahAnggota = $anggotaLab->count();

            $kategoriDefault = [
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            /*
            |--------------------------------------------------------------------------
            | Detail KM yang diterima Lab dari Ketua KK
            |--------------------------------------------------------------------------
            */
            $kmLabRows = \Illuminate\Support\Facades\DB::table('km_lab as kl')
                ->leftJoin('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                ->where('kl.id_lab', $idLab)
                ->where('kl.tahun_km', $tahun)
                ->where('kl.status_km', 'Aktif')
                ->select(
                    'kl.id_km_lab',
                    'kl.id_target',
                    'kl.kategori_km',
                    'kl.sub_kategori_km',
                    'kl.jumlah_km',
                    'kl.triwulan_1',
                    'kl.triwulan_2',
                    'kl.triwulan_3',
                    'kl.triwulan_4',
                    'kl.created_at as waktu_turun',
                    'tk.keterangan',
                    'tk.tanggal_mulai_tw1',
                    'tk.tanggal_mulai_tw2',
                    'tk.tanggal_mulai_tw3',
                    'tk.tanggal_mulai_tw4',
                    'tk.tanggal_selesai_tw1',
                    'tk.tanggal_selesai_tw2',
                    'tk.tanggal_selesai_tw3',
                    'tk.tanggal_selesai_tw4'
                )
                ->orderBy('kl.kategori_km')
                ->orderBy('kl.sub_kategori_km')
                ->orderBy('kl.id_km_lab')
                ->get();

            $idKmLabList = $kmLabRows->pluck('id_km_lab')->filter()->values();

            /*
            |--------------------------------------------------------------------------
            | Jumlah KM yang telah dibagi kepada anggota
            |--------------------------------------------------------------------------
            | Pembagian dihitung dari total target tahun terpilih karena tabel
            | km_anggota menyimpan jumlah pembagian total per target KM Lab.
            |--------------------------------------------------------------------------
            */
            $assignPerKmLab = collect();

            if ($idKmLabList->isNotEmpty()) {
                $assignPerKmLab = \Illuminate\Support\Facades\DB::table('km_anggota')
                    ->whereIn('id_km_lab', $idKmLabList)
                    ->select(
                        'id_km_lab',
                        \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(jumlah_km), 0) as total_assign')
                    )
                    ->groupBy('id_km_lab')
                    ->pluck('total_assign', 'id_km_lab');
            }

            /*
            |--------------------------------------------------------------------------
            | Realisasi aktivitas per target KM Lab dan per triwulan
            |--------------------------------------------------------------------------
            | Aktivitas Accepted dihitung sebagai realisasi. Data lama tanpa relasi
            | id_km_anggota masih dicoba dicocokkan melalui kategori + sub kategori.
            |--------------------------------------------------------------------------
            */
            $hasIdKmAnggota = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'id_km_anggota');
            $hasStatusProgress = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'status_progress');

            $aktivitasRows = collect();

            if ($idKmLabList->isNotEmpty()) {
                $queryAktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km as ak')
                    ->where('ak.id_lab', $idLab)
                    ->whereBetween('ak.tanggal_mulai', [
                        \Carbon\Carbon::create($tahun, 1, 1)->toDateString(),
                        \Carbon\Carbon::create($tahun, 12, 31)->toDateString(),
                    ])
                    ->select(
                        'ak.kategori_km',
                        'ak.sub_kategori_km',
                        'ak.tanggal_mulai'
                    );

                if ($hasIdKmAnggota) {
                    $queryAktivitas
                        ->leftJoin('km_anggota as ka', 'ak.id_km_anggota', '=', 'ka.id_km_anggota')
                        ->addSelect('ka.id_km_lab');
                } else {
                    $queryAktivitas->addSelect(\Illuminate\Support\Facades\DB::raw('NULL as id_km_lab'));
                }

                if ($hasStatusProgress) {
                    $queryAktivitas->where('ak.status_progress', 'Accepted');
                }

                $aktivitasRows = $queryAktivitas->get();
            }

            $normalizeKey = static function ($kategori, $subKategori): string {
                return mb_strtolower(trim((string) $kategori) . '|' . trim((string) $subKategori));
            };

            $kmLabLookup = [];
            foreach ($kmLabRows as $row) {
                $key = $normalizeKey($row->kategori_km, $row->sub_kategori_km);
                $kmLabLookup[$key] = $row->id_km_lab;

                $keyKategoriSaja = $normalizeKey($row->kategori_km, '');
                $kmLabLookup[$keyKategoriSaja] = $kmLabLookup[$keyKategoriSaja] ?? $row->id_km_lab;
            }

            $realisasiPerKmLab = [];

            foreach ($aktivitasRows as $aktivitas) {
                $idKmLab = (int) ($aktivitas->id_km_lab ?? 0);

                if ($idKmLab <= 0) {
                    $keyLengkap = $normalizeKey($aktivitas->kategori_km, $aktivitas->sub_kategori_km);
                    $keyKategori = $normalizeKey($aktivitas->kategori_km, '');

                    $idKmLab = (int) (
                        $kmLabLookup[$keyLengkap]
                        ?? $kmLabLookup[$keyKategori]
                        ?? 0
                    );
                }

                if ($idKmLab <= 0 || empty($aktivitas->tanggal_mulai)) {
                    continue;
                }

                $bulanAktivitas = \Carbon\Carbon::parse($aktivitas->tanggal_mulai)->month;
                $twAktivitas = (int) ceil($bulanAktivitas / 3);

                if (! isset($realisasiPerKmLab[$idKmLab])) {
                    $realisasiPerKmLab[$idKmLab] = [
                        1 => 0,
                        2 => 0,
                        3 => 0,
                        4 => 0,
                    ];
                }

                $realisasiPerKmLab[$idKmLab][$twAktivitas]++;
            }

            /*
            |--------------------------------------------------------------------------
            | Rincian per kategori + ringkasan card kategori
            |--------------------------------------------------------------------------
            */
            $detailKategori = [];
            $kategoriCards = [];

            foreach ($kategoriDefault as $kategori) {
                $rowsKategori = $kmLabRows
                    ->where('kategori_km', $kategori)
                    ->values();

                $detailRows = [];

                foreach ($rowsKategori as $row) {
                    $targetTw = [
                        1 => (int) ($row->triwulan_1 ?? 0),
                        2 => (int) ($row->triwulan_2 ?? 0),
                        3 => (int) ($row->triwulan_3 ?? 0),
                        4 => (int) ($row->triwulan_4 ?? 0),
                    ];

                    $realisasiTw = [
                        1 => (int) data_get($realisasiPerKmLab, $row->id_km_lab . '.1', 0),
                        2 => (int) data_get($realisasiPerKmLab, $row->id_km_lab . '.2', 0),
                        3 => (int) data_get($realisasiPerKmLab, $row->id_km_lab . '.3', 0),
                        4 => (int) data_get($realisasiPerKmLab, $row->id_km_lab . '.4', 0),
                    ];

                    $targetTahunan = array_sum($targetTw);
                    $targetPeriode = array_sum(array_intersect_key($targetTw, array_flip($triwulanAktif)));

                    $realisasiTahunan = array_sum($realisasiTw);
                    $realisasiPeriode = array_sum(array_intersect_key($realisasiTw, array_flip($triwulanAktif)));

                    $sudahDibagi = (int) ($assignPerKmLab[$row->id_km_lab] ?? 0);
                    $belumDibagi = max($targetTahunan - $sudahDibagi, 0);

                    $persentase = $targetPeriode > 0
                        ? min((int) round(($realisasiPeriode / $targetPeriode) * 100), 100)
                        : 0;

                    $tenggatList = [];
                    $tanggalTenggatAkhir = null;

                    foreach ($triwulanAktif as $tw) {
                        $mulaiField = 'tanggal_mulai_tw' . $tw;
                        $selesaiField = 'tanggal_selesai_tw' . $tw;

                        $mulai = $row->{$mulaiField} ?? null;
                        $selesai = $row->{$selesaiField} ?? null;

                        if ($mulai || $selesai) {
                            $labelTenggat = 'TW' . $tw . ': ';

                            if ($mulai) {
                                $labelTenggat .= \Carbon\Carbon::parse($mulai)->format('d/m/Y');
                            } else {
                                $labelTenggat .= '-';
                            }

                            $labelTenggat .= ' — ';

                            if ($selesai) {
                                $selesaiCarbon = \Carbon\Carbon::parse($selesai);
                                $labelTenggat .= $selesaiCarbon->format('d/m/Y');

                                if (! $tanggalTenggatAkhir || $selesaiCarbon->greaterThan($tanggalTenggatAkhir)) {
                                    $tanggalTenggatAkhir = $selesaiCarbon;
                                }
                            } else {
                                $labelTenggat .= '-';
                            }

                            $tenggatList[] = $labelTenggat;
                        }
                    }

                    $tenggatPeriode = $tenggatList
                        ? implode(' | ', $tenggatList)
                        : '-';

                    if ($targetPeriode <= 0) {
                        $status = 'Tidak Ada Target';
                        $statusClass = 'secondary';
                    } elseif ($realisasiPeriode >= $targetPeriode) {
                        $status = 'Tercapai';
                        $statusClass = 'success';
                    } elseif ($realisasiPeriode > 0) {
                        $status = 'On Progress';
                        $statusClass = 'warning';
                    } else {
                        $status = 'Belum Mulai';
                        $statusClass = 'danger';
                    }

                    if ($targetPeriode <= 0) {
                        $statusTenggat = 'Tidak Ada Target';
                        $statusTenggatClass = 'secondary';
                    } elseif (! $tanggalTenggatAkhir) {
                        $statusTenggat = 'Tenggat Belum Diatur';
                        $statusTenggatClass = 'secondary';
                    } elseif ($realisasiPeriode >= $targetPeriode) {
                        $statusTenggat = 'Tercapai';
                        $statusTenggatClass = 'success';
                    } elseif (now()->startOfDay()->greaterThan($tanggalTenggatAkhir->copy()->startOfDay())) {
                        $statusTenggat = 'Lewat Tenggat';
                        $statusTenggatClass = 'danger';
                    } elseif (now()->startOfDay()->diffInDays($tanggalTenggatAkhir->copy()->startOfDay(), false) <= 10) {
                        $statusTenggat = 'Mendekati Tenggat';
                        $statusTenggatClass = 'warning';
                    } else {
                        $statusTenggat = 'Dalam Periode';
                        $statusTenggatClass = 'info';
                    }

                    $detailRows[] = [
                        'id_km_lab' => (int) $row->id_km_lab,
                        'sub_kategori_km' => $row->sub_kategori_km ?: '-',
                        'keterangan' => $row->keterangan ?: '-',
                        'target_tw' => $targetTw,
                        'target_tahunan' => $targetTahunan,
                        'target_periode' => $targetPeriode,
                        'realisasi_tw' => $realisasiTw,
                        'realisasi_tahunan' => $realisasiTahunan,
                        'realisasi_periode' => $realisasiPeriode,
                        'sudah_dibagi' => $sudahDibagi,
                        'belum_dibagi' => $belumDibagi,
                        'persentase' => $persentase,
                        'status' => $status,
                        'status_class' => $statusClass,
                        'tenggat_periode' => $tenggatPeriode,
                        'status_tenggat' => $statusTenggat,
                        'status_tenggat_class' => $statusTenggatClass,
                    ];
                }

                $targetPeriodeKategori = array_sum(array_column($detailRows, 'target_periode'));
                $realisasiPeriodeKategori = array_sum(array_column($detailRows, 'realisasi_periode'));
                $sudahDibagiKategori = array_sum(array_column($detailRows, 'sudah_dibagi'));
                $belumDibagiKategori = array_sum(array_column($detailRows, 'belum_dibagi'));
                $targetTahunanKategori = array_sum(array_column($detailRows, 'target_tahunan'));

                $persentaseKategori = $targetPeriodeKategori > 0
                    ? min((int) round(($realisasiPeriodeKategori / $targetPeriodeKategori) * 100), 100)
                    : 0;

                if ($targetPeriodeKategori <= 0) {
                    $catatan = 'Belum ada target KM pada kategori ini untuk ' . $periodeSingkat . '.';
                    $catatanClass = 'muted';
                } elseif ($belumDibagiKategori > 0) {
                    $catatan = 'Masih ada ' . $belumDibagiKategori . ' KM yang belum dibagi kepada anggota.';
                    $catatanClass = 'danger';
                } elseif ($realisasiPeriodeKategori >= $targetPeriodeKategori) {
                    $catatan = 'Target periode sudah tercapai.';
                    $catatanClass = 'success';
                } else {
                    $catatan = 'Pembagian sudah selesai, realisasi masih berjalan.';
                    $catatanClass = 'warning';
                }

                $kategoriCards[] = [
                    'kategori' => $kategori,
                    'target' => $targetPeriodeKategori,
                    'realisasi' => $realisasiPeriodeKategori,
                    'sudah_dibagi' => $sudahDibagiKategori,
                    'belum_dibagi' => $belumDibagiKategori,
                    'target_tahunan' => $targetTahunanKategori,
                    'persentase' => $persentaseKategori,
                    'catatan' => $catatan,
                    'catatan_class' => $catatanClass,
                    'anchor' => 'rekap-' . \Illuminate\Support\Str::slug($kategori),
                ];

                $detailKategori[] = [
                    'kategori' => $kategori,
                    'anchor' => 'rekap-' . \Illuminate\Support\Str::slug($kategori),
                    'summary' => [
                        'target_periode' => $targetPeriodeKategori,
                        'realisasi_periode' => $realisasiPeriodeKategori,
                        'sudah_dibagi' => $sudahDibagiKategori,
                        'belum_dibagi' => $belumDibagiKategori,
                        'persentase' => $persentaseKategori,
                    ],
                    'items' => $detailRows,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Ringkasan Umum Monitoring Lab
            |--------------------------------------------------------------------------
            */
            $totalKmTurun = array_sum(array_column($kategoriCards, 'target_tahunan'));
            $totalKmAssign = array_sum(array_column($kategoriCards, 'sudah_dibagi'));
            $totalSisaAssign = array_sum(array_column($kategoriCards, 'belum_dibagi'));
            $totalTargetPeriode = array_sum(array_column($kategoriCards, 'target'));
            $totalRealisasiPeriode = array_sum(array_column($kategoriCards, 'realisasi'));

            $persentaseAssign = $totalKmTurun > 0
                ? min((int) round(($totalKmAssign / $totalKmTurun) * 100), 100)
                : 0;

            $persentaseTotal = $totalTargetPeriode > 0
                ? min((int) round(($totalRealisasiPeriode / $totalTargetPeriode) * 100), 100)
                : 0;

            return view('ketualab.monitoring-lab', compact(
                'lab',
                'jumlahAnggota',
                'tahun',
                'tahunOptions',
                'periode',
                'triwulan',
                'semester',
                'triwulanAktif',
                'tanggalMulai',
                'tanggalSelesai',
                'labelPeriode',
                'periodeInfo',
                'periodeSingkat',
                'kategoriCards',
                'detailKategori',
                'totalKmTurun',
                'totalKmAssign',
                'totalSisaAssign',
                'totalTargetPeriode',
                'totalRealisasiPeriode',
                'persentaseAssign',
                'persentaseTotal'
            ));
        })->name('ketualab.monitoring-lab');

        Route::get('/ketualab/monitoring-anggota', function (\Illuminate\Http\Request $request) {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            /*
            |--------------------------------------------------------------------------
            | Identitas Lab Ketua Lab
            |--------------------------------------------------------------------------
            | Beberapa akun menyimpan id_lab pada tabel users, sebagian lain melalui
            | tabel dosen. Fallback ini menjaga data anggota Lab tetap terbaca.
            */
            $idLab = $user->id_lab;

            if (! $idLab && $user->id_dosen) {
                $idLab = \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_dosen', $user->id_dosen)
                    ->value('id_lab');
            }

            abort_unless($idLab, 403, 'Data Lab Riset tidak ditemukan.');

            $tahun = (int) $request->query('tahun', now()->year);

            $periode = $request->query('periode', 'tahun');
            $periode = in_array($periode, ['tahun', 'triwulan', 'semester'], true)
                ? $periode
                : 'tahun';

            $triwulan = max(1, min(4, (int) $request->query('triwulan', ceil(now()->month / 3))));
            $semester = max(1, min(2, (int) $request->query('semester', now()->month <= 6 ? 1 : 2)));

            $search = trim((string) $request->query('search', ''));

            /*
            |--------------------------------------------------------------------------
            | Rentang Waktu dan Triwulan Aktif
            |--------------------------------------------------------------------------
            */
            if ($periode === 'triwulan') {
                $bulanMulai = (($triwulan - 1) * 3) + 1;
                $bulanSelesai = $bulanMulai + 2;

                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();

                $triwulanAktif = [$triwulan];
                $labelPeriode = 'Triwulan ' . $triwulan . ' Tahun ' . $tahun;
                $labelMode = 'Data target, pembagian, dan realisasi ditampilkan untuk Triwulan ' . $triwulan . '.';
            } elseif ($periode === 'semester') {
                $bulanMulai = $semester === 1 ? 1 : 7;
                $bulanSelesai = $semester === 1 ? 6 : 12;

                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();

                $triwulanAktif = $semester === 1 ? [1, 2] : [3, 4];
                $labelPeriode = 'Semester ' . $semester . ' Tahun ' . $tahun;
                $labelMode = 'Data target, pembagian, dan realisasi ditampilkan untuk Semester ' . $semester . '.';
            } else {
                $periode = 'tahun';
                $tanggalMulai = \Carbon\Carbon::create($tahun, 1, 1)->startOfYear();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, 12, 31)->endOfYear();

                $triwulanAktif = [1, 2, 3, 4];
                $labelPeriode = 'Tahunan ' . $tahun;
                $labelMode = 'Data target, pembagian, dan realisasi ditampilkan untuk satu tahun penuh.';
            }

            $kategoriDefault = [
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            /*
            |--------------------------------------------------------------------------
            | Pilihan Tahun
            |--------------------------------------------------------------------------
            */
            $tahunOptions = collect()
                ->merge(
                    \Illuminate\Support\Facades\DB::table('km_lab')
                        ->where('id_lab', $idLab)
                        ->select('tahun_km')
                        ->distinct()
                        ->pluck('tahun_km')
                )
                ->merge(
                    \Illuminate\Support\Facades\DB::table('aktivitas_km')
                        ->where('id_lab', $idLab)
                        ->selectRaw("strftime('%Y', tanggal_mulai) as tahun_km")
                        ->distinct()
                        ->pluck('tahun_km')
                )
                ->push(now()->year)
                ->push($tahun)
                ->filter()
                ->unique()
                ->sortDesc()
                ->values();

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $idLab)
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Pemeriksaan Struktur Tabel agar kompatibel dengan project saat ini
            |--------------------------------------------------------------------------
            */
            $hasKaTriwulan = \Illuminate\Support\Facades\Schema::hasColumns('km_anggota', [
                'triwulan_1',
                'triwulan_2',
                'triwulan_3',
                'triwulan_4',
            ]);

            $hasKlTriwulan = \Illuminate\Support\Facades\Schema::hasColumns('km_lab', [
                'triwulan_1',
                'triwulan_2',
                'triwulan_3',
                'triwulan_4',
            ]);

            $hasIdTargetKmLab = \Illuminate\Support\Facades\Schema::hasColumn('km_lab', 'id_target');
            $hasSubKategoriKmLab = \Illuminate\Support\Facades\Schema::hasColumn('km_lab', 'sub_kategori_km');

            $hasAktivitasIdKmAnggota = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'id_km_anggota');
            $hasAktivitasStatus = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'status_progress');

            $hasDeadlineTw1 = \Illuminate\Support\Facades\Schema::hasColumn('target_km', 'tanggal_selesai_tw1');
            $hasDeadlineTw2 = \Illuminate\Support\Facades\Schema::hasColumn('target_km', 'tanggal_selesai_tw2');
            $hasDeadlineTw3 = \Illuminate\Support\Facades\Schema::hasColumn('target_km', 'tanggal_selesai_tw3');
            $hasDeadlineTw4 = \Illuminate\Support\Facades\Schema::hasColumn('target_km', 'tanggal_selesai_tw4');

            /*
            |--------------------------------------------------------------------------
            | Daftar Anggota Lab + Search
            |--------------------------------------------------------------------------
            */
            $anggotaQuery = \Illuminate\Support\Facades\DB::table('users as u')
                ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
                ->where('u.role', 'Anggota')
                ->where(function ($query) use ($idLab) {
                    $query->where('u.id_lab', $idLab)
                        ->orWhere('d.id_lab', $idLab);
                })
                ->select(
                    'u.id_user',
                    'u.id_dosen',
                    'u.username',
                    \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(d.nama_dosen, ''), u.username) as nama_anggota"),
                    \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(d.nidn, ''), '-') as nidn"),
                    \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(d.email, ''), '-') as email"),
                    \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(d.jad, ''), 'AA') as jad")
                )
                ->distinct();

            if ($search !== '') {
                $keyword = '%' . $search . '%';

                $anggotaQuery->where(function ($query) use ($keyword) {
                    $query->where('u.username', 'like', $keyword)
                        ->orWhere('d.nama_dosen', 'like', $keyword)
                        ->orWhere('d.nidn', 'like', $keyword)
                        ->orWhere('d.jad', 'like', $keyword);
                });
            }

            $anggota = $anggotaQuery
                ->orderBy('nama_anggota')
                ->get();

            $idAnggota = $anggota->pluck('id_user')->filter()->values();

            /*
            |--------------------------------------------------------------------------
            | Data KM Turun ke Lab
            |--------------------------------------------------------------------------
            */
            $kmLabQuery = \Illuminate\Support\Facades\DB::table('km_lab as kl')
                ->where('kl.id_lab', $idLab)
                ->where('kl.tahun_km', $tahun)
                ->where('kl.status_km', 'Aktif')
                ->select(
                    'kl.id_km_lab',
                    'kl.id_lab',
                    'kl.kategori_km',
                    'kl.jumlah_km'
                );

            if ($hasSubKategoriKmLab) {
                $kmLabQuery->addSelect('kl.sub_kategori_km');
            } else {
                $kmLabQuery->addSelect(\Illuminate\Support\Facades\DB::raw("'-' as sub_kategori_km"));
            }

            if ($hasKlTriwulan) {
                $kmLabQuery->addSelect(
                    \Illuminate\Support\Facades\DB::raw('COALESCE(kl.triwulan_1, 0) as triwulan_1'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(kl.triwulan_2, 0) as triwulan_2'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(kl.triwulan_3, 0) as triwulan_3'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(kl.triwulan_4, 0) as triwulan_4')
                );
            } else {
                $kmLabQuery->addSelect(
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_1'),
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_2'),
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_3'),
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_4')
                );
            }

            if ($hasIdTargetKmLab) {
                $kmLabQuery->leftJoin('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                    ->addSelect(
                        \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(tk.keterangan, ''), '-') as keterangan")
                    );

                $kmLabQuery->addSelect(
                    $hasDeadlineTw1 ? 'tk.tanggal_selesai_tw1' : \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw1'),
                    $hasDeadlineTw2 ? 'tk.tanggal_selesai_tw2' : \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw2'),
                    $hasDeadlineTw3 ? 'tk.tanggal_selesai_tw3' : \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw3'),
                    $hasDeadlineTw4 ? 'tk.tanggal_selesai_tw4' : \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw4')
                );
            } else {
                $kmLabQuery->addSelect(
                    \Illuminate\Support\Facades\DB::raw("'-' as keterangan"),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw1'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw2'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw3'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw4')
                );
            }

            $kmLabRows = $kmLabQuery
                ->orderBy('kl.kategori_km')
                ->orderBy('kl.id_km_lab')
                ->get()
                ->map(function ($row) use ($triwulanAktif, $hasKlTriwulan) {
                    $row->target_periode = $hasKlTriwulan
                        ? collect($triwulanAktif)->sum(fn ($tw) => (int) data_get($row, 'triwulan_' . $tw, 0))
                        : (int) $row->jumlah_km;

                    return $row;
                });

            /*
            |--------------------------------------------------------------------------
            | Target KM yang sudah dibagi ke Anggota
            |--------------------------------------------------------------------------
            */
            $assignQuery = \Illuminate\Support\Facades\DB::table('km_anggota as ka')
                ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                ->join('users as u', 'ka.id_user', '=', 'u.id_user')
                ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
                ->where('kl.id_lab', $idLab)
                ->where('kl.tahun_km', $tahun)
                ->where('kl.status_km', 'Aktif');

            if ($idAnggota->isNotEmpty()) {
                $assignQuery->whereIn('ka.id_user', $idAnggota);
            } else {
                $assignQuery->whereRaw('1 = 0');
            }

            $assignQuery->select(
                'ka.id_km_anggota',
                'ka.id_user',
                'ka.id_km_lab',
                'ka.jumlah_km',
                'kl.kategori_km',
                \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(d.nama_dosen, ''), u.username) as nama_anggota"),
                \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(d.nidn, ''), '-') as nidn"),
                \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(d.jad, ''), 'AA') as jad")
            );

            if ($hasSubKategoriKmLab) {
                $assignQuery->addSelect('kl.sub_kategori_km');
            } else {
                $assignQuery->addSelect(\Illuminate\Support\Facades\DB::raw("'-' as sub_kategori_km"));
            }

            if ($hasKaTriwulan) {
                $assignQuery->addSelect(
                    \Illuminate\Support\Facades\DB::raw('COALESCE(ka.triwulan_1, 0) as triwulan_1'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(ka.triwulan_2, 0) as triwulan_2'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(ka.triwulan_3, 0) as triwulan_3'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(ka.triwulan_4, 0) as triwulan_4')
                );
            } else {
                $assignQuery->addSelect(
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_1'),
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_2'),
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_3'),
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_4')
                );
            }

            if ($hasIdTargetKmLab) {
                $assignQuery->leftJoin('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                    ->addSelect(
                        \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(tk.keterangan, ''), '-') as keterangan")
                    );

                $assignQuery->addSelect(
                    $hasDeadlineTw1 ? 'tk.tanggal_selesai_tw1' : \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw1'),
                    $hasDeadlineTw2 ? 'tk.tanggal_selesai_tw2' : \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw2'),
                    $hasDeadlineTw3 ? 'tk.tanggal_selesai_tw3' : \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw3'),
                    $hasDeadlineTw4 ? 'tk.tanggal_selesai_tw4' : \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw4')
                );
            } else {
                $assignQuery->addSelect(
                    \Illuminate\Support\Facades\DB::raw("'-' as keterangan"),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw1'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw2'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw3'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw4')
                );
            }

            $assignRows = $assignQuery
                ->orderBy('nama_anggota')
                ->orderBy('kl.kategori_km')
                ->get()
                ->map(function ($row) use ($triwulanAktif, $hasKaTriwulan) {
                    $row->target_periode = $hasKaTriwulan
                        ? collect($triwulanAktif)->sum(fn ($tw) => (int) data_get($row, 'triwulan_' . $tw, 0))
                        : (int) $row->jumlah_km;

                    return $row;
                });

            /*
            |--------------------------------------------------------------------------
            | Realisasi Aktivitas pada Periode Terpilih
            |--------------------------------------------------------------------------
            */
            $realisasiByKmAnggota = collect();
            $realisasiByUserKategori = collect();

            if ($idAnggota->isNotEmpty()) {
                $aktivitasQuery = \Illuminate\Support\Facades\DB::table('aktivitas_km as ak')
                    ->whereIn('ak.id_user', $idAnggota)
                    ->whereBetween('ak.tanggal_mulai', [
                        $tanggalMulai->toDateString(),
                        $tanggalSelesai->toDateString(),
                    ]);

                if ($hasAktivitasStatus) {
                    $aktivitasQuery->where('ak.status_progress', 'Accepted');
                }

                if ($hasAktivitasIdKmAnggota) {
                    $realisasiByKmAnggota = (clone $aktivitasQuery)
                        ->whereNotNull('ak.id_km_anggota')
                        ->select(
                            'ak.id_km_anggota',
                            \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_realisasi')
                        )
                        ->groupBy('ak.id_km_anggota')
                        ->get()
                        ->keyBy('id_km_anggota');
                }

                $realisasiByUserKategori = (clone $aktivitasQuery)
                    ->select(
                        'ak.id_user',
                        'ak.kategori_km',
                        \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_realisasi')
                    )
                    ->groupBy('ak.id_user', 'ak.kategori_km')
                    ->get()
                    ->keyBy(fn ($row) => $row->id_user . '|' . $row->kategori_km);
            }

            /*
            |--------------------------------------------------------------------------
            | Rincian target per anggota dan per kategori
            |--------------------------------------------------------------------------
            */
            $rincianTargetAnggota = $assignRows->map(function ($row) use ($realisasiByKmAnggota, $realisasiByUserKategori, $hasAktivitasIdKmAnggota, $triwulanAktif) {
                if ($hasAktivitasIdKmAnggota) {
                    $realisasi = (int) data_get($realisasiByKmAnggota->get($row->id_km_anggota), 'total_realisasi', 0);
                } else {
                    $realisasi = (int) data_get(
                        $realisasiByUserKategori->get($row->id_user . '|' . $row->kategori_km),
                        'total_realisasi',
                        0
                    );
                }

                $targetPeriode = (int) ($row->target_periode ?? 0);
                $sisa = max($targetPeriode - $realisasi, 0);
                $persentase = $targetPeriode > 0
                    ? min((int) round(($realisasi / $targetPeriode) * 100), 100)
                    : 0;

                $tenggatAktif = collect($triwulanAktif)
                    ->map(function ($tw) use ($row) {
                        $field = 'tanggal_selesai_tw' . $tw;
                        $tanggal = data_get($row, $field);

                        if (! $tanggal) {
                            return null;
                        }

                        return [
                            'triwulan' => 'TW ' . $tw,
                            'tanggal' => \Carbon\Carbon::parse($tanggal),
                        ];
                    })
                    ->filter()
                    ->values();

                $tenggatTerdekat = $tenggatAktif
                    ->sortBy('tanggal')
                    ->first();

                if ($targetPeriode <= 0) {
                    $status = 'Belum Ada Target';
                    $status_class = 'secondary';
                } elseif ($realisasi >= $targetPeriode) {
                    $status = 'Tercapai';
                    $status_class = 'success';
                } elseif ($realisasi > 0) {
                    $status = 'On Progress';
                    $status_class = 'warning';
                } else {
                    $status = 'Belum Mulai';
                    $status_class = 'danger';
                }

                if ($tenggatTerdekat) {
                    $sisaHari = now()->startOfDay()->diffInDays($tenggatTerdekat['tanggal']->copy()->startOfDay(), false);

                    if ($status !== 'Tercapai' && $sisaHari < 0) {
                        $statusTenggat = 'Lewat Tenggat';
                        $tenggat_class = 'danger';
                    } elseif ($status !== 'Tercapai' && $sisaHari <= 10) {
                        $statusTenggat = 'Mendekati Tenggat';
                        $tenggat_class = 'warning';
                    } else {
                        $statusTenggat = 'Aman';
                        $tenggat_class = 'success';
                    }
                } else {
                    $statusTenggat = '-';
                    $tenggat_class = 'secondary';
                }

                $row->realisasi_periode = $realisasi;
                $row->sisa_periode = $sisa;
                $row->persentase = $persentase;
                $row->status = $status;
                $row->status_class = $status_class;
                $row->tenggat_aktif = $tenggatAktif;
                $row->tenggat_terdekat = $tenggatTerdekat;
                $row->status_tenggat = $statusTenggat;
                $row->tenggat_class = $tenggat_class;

                return $row;
            });

            /*
            |--------------------------------------------------------------------------
            | Monitoring utama per anggota
            |--------------------------------------------------------------------------
            */
            $dataMonitoring = $anggota->map(function ($anggotaItem) use ($rincianTargetAnggota) {
                $items = $rincianTargetAnggota
                    ->where('id_user', $anggotaItem->id_user)
                    ->values();

                $target = (int) $items->sum('target_periode');
                $realisasi = (int) $items->sum('realisasi_periode');
                $sisa = max($target - $realisasi, 0);

                $persentase = $target > 0
                    ? min((int) round(($realisasi / $target) * 100), 100)
                    : 0;

                if ($target <= 0) {
                    $status = 'Belum Ada Target';
                    $statusClass = 'secondary';
                } elseif ($realisasi >= $target) {
                    $status = 'Tercapai';
                    $statusClass = 'success';
                } elseif ($realisasi > 0) {
                    $status = 'On Progress';
                    $statusClass = 'warning';
                } else {
                    $status = 'Belum Mulai';
                    $statusClass = 'danger';
                }

                return [
                    'id_user' => $anggotaItem->id_user,
                    'username' => $anggotaItem->username,
                    'nama_dosen' => $anggotaItem->nama_anggota,
                    'nidn' => $anggotaItem->nidn,
                    'email' => $anggotaItem->email,
                    'jad' => $anggotaItem->jad,
                    'total_km_assign' => (int) $items->sum('jumlah_km'),
                    'target_periode' => $target,
                    'total_realisasi' => $realisasi,
                    'sisa' => $sisa,
                    'persentase' => $persentase,
                    'status' => $status,
                    'status_class' => $statusClass,
                ];
            })->values();

            /*
            |--------------------------------------------------------------------------
            | Ringkasan per kategori untuk lima card utama
            |--------------------------------------------------------------------------
            */
            $kategoriCards = collect($kategoriDefault)->map(function ($kategori) use ($kmLabRows, $rincianTargetAnggota) {
                $kmTurun = (int) $kmLabRows
                    ->where('kategori_km', $kategori)
                    ->sum('target_periode');

                $itemKategori = $rincianTargetAnggota
                    ->where('kategori_km', $kategori)
                    ->values();

                $sudahDibagi = (int) $itemKategori->sum('target_periode');
                $belumDibagi = max($kmTurun - $sudahDibagi, 0);
                $realisasi = (int) $itemKategori->sum('realisasi_periode');

                $persentase = $kmTurun > 0
                    ? min((int) round(($realisasi / $kmTurun) * 100), 100)
                    : 0;

                if ($kmTurun <= 0) {
                    $note = 'Belum ada target pada kategori ini.';
                    $note_class = 'success';
                } elseif ($belumDibagi > 0) {
                    $note = 'Masih ada ' . $belumDibagi . ' KM yang belum dibagi.';
                    $note_class = 'danger';
                } elseif ($realisasi >= $kmTurun) {
                    $note = 'Target kategori telah tercapai.';
                    $note_class = 'success';
                } else {
                    $note = 'Target sudah dibagi, realisasi masih berjalan.';
                    $note_class = 'warning';
                }

                return [
                    'kategori' => $kategori,
                    'target' => $kmTurun,
                    'realisasi' => $realisasi,
                    'sudah_dibagi' => $sudahDibagi,
                    'belum_dibagi' => $belumDibagi,
                    'persentase' => $persentase,
                    'note' => $note,
                    'note_class' => $note_class,
                ];
            })->values();

            $jumlahAnggota = $dataMonitoring->count();
            $totalKmAssignLab = (int) $dataMonitoring->sum('total_km_assign');
            $totalTargetPeriode = (int) $dataMonitoring->sum('target_periode');
            $totalRealisasiPeriode = (int) $dataMonitoring->sum('total_realisasi');
            $totalSisa = max($totalTargetPeriode - $totalRealisasiPeriode, 0);

            $persentaseTotal = $totalTargetPeriode > 0
                ? min((int) round(($totalRealisasiPeriode / $totalTargetPeriode) * 100), 100)
                : 0;

            $anggotaTercapai = $dataMonitoring
                ->filter(fn ($item) => $item['target_periode'] > 0 && $item['status'] === 'Tercapai')
                ->count();

            $anggotaOnProgress = $dataMonitoring
                ->filter(fn ($item) => $item['status'] === 'On Progress')
                ->count();

            $anggotaBelumMulai = $dataMonitoring
                ->filter(fn ($item) => $item['target_periode'] > 0 && $item['status'] === 'Belum Mulai')
                ->count();

            /*
            |--------------------------------------------------------------------------
            | Rincian per Kategori dengan pagination mandiri
            |--------------------------------------------------------------------------
            | Setiap kategori hanya menampilkan maksimal 10 rincian target anggota
            | per halaman. Halaman tiap kategori memakai parameter query berbeda,
            | sehingga perpindahan halaman tiap kategori tidak memengaruhi kategori lain.
            */
            $perPageKategori = 10;

            $kategoriDetail = trim((string) $request->query('kategori_detail', ''));
            if (! in_array($kategoriDetail, $kategoriDefault, true)) {
                $kategoriDetail = null;
            }

            $kategoriDitampilkan = $kategoriDetail
                ? [$kategoriDetail]
                : $kategoriDefault;

            $rincianPerKategori = collect($kategoriDefault)
                ->mapWithKeys(function ($kategori) use (
                    $rincianTargetAnggota,
                    $request,
                    $perPageKategori
                ) {
                    $pageKey = 'page_' . \Illuminate\Support\Str::slug($kategori, '_');

                    $semuaRincian = $rincianTargetAnggota
                        ->where('kategori_km', $kategori)
                        ->values();

                    $totalData = $semuaRincian->count();
                    $lastPage = max(1, (int) ceil($totalData / $perPageKategori));

                    $currentPage = max(
                        1,
                        min((int) $request->query($pageKey, 1), $lastPage)
                    );

                    $barisAwal = ($currentPage - 1) * $perPageKategori;

                    $rows = $semuaRincian
                        ->slice($barisAwal, $perPageKategori)
                        ->values();

                    return [
                        $kategori => [
                            'rows' => $rows,
                            'total' => $totalData,
                            'from' => $totalData > 0 ? $barisAwal + 1 : 0,
                            'to' => $totalData > 0
                                ? min($barisAwal + $perPageKategori, $totalData)
                                : 0,
                            'current_page' => $currentPage,
                            'last_page' => $lastPage,
                            'page_key' => $pageKey,
                        ],
                    ];
                });

            /*
            |--------------------------------------------------------------------------
            | Pengajuan baru yang membutuhkan aksi verifikasi
            |--------------------------------------------------------------------------
            | Tidak dibatasi filter periode agar pengajuan baru tetap terlihat di
            | Monitoring Anggota walaupun Ketua Lab sedang melihat periode lain.
            */
            $pengajuanMenungguVerifikasi = collect();
            $jumlahMenungguVerifikasi = 0;

            if ($hasAktivitasStatus) {
                $queryPengajuanVerifikasi = \Illuminate\Support\Facades\DB::table('aktivitas_km as ak')
                    ->join('users as u', 'ak.id_user', '=', 'u.id_user')
                    ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
                    ->where('ak.id_lab', $idLab)
                    ->where('ak.status_progress', 'Submitted')
                    ->where(function ($query) {
                        $query->where('ak.kategori_km', '!=', 'Pendidikan')
                            ->orWhereNull('ak.kategori_km');
                    })
                    ->select(
                        'ak.id_aktivitas',
                        'ak.id_user',
                        'ak.kategori_km',
                        'ak.sub_kategori_km',
                        'ak.judul_aktivitas',
                        'ak.diajukan_pada',
                        'ak.created_at',
                        'ak.updated_at',
                        \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(d.nama_dosen, ''), u.username) as nama_anggota"),
                        \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(d.nidn, ''), '-') as nidn")
                    );

                $jumlahMenungguVerifikasi = (clone $queryPengajuanVerifikasi)->count();

                $pengajuanMenungguVerifikasi = $queryPengajuanVerifikasi
                    ->orderByDesc('ak.diajukan_pada')
                    ->orderByDesc('ak.updated_at')
                    ->limit(12)
                    ->get();
            }

            /*
            |--------------------------------------------------------------------------
            | Riwayat keputusan verifikasi terbaru
            |--------------------------------------------------------------------------
            */
            $riwayatVerifikasi = collect();

            if (\Illuminate\Support\Facades\Schema::hasTable('riwayat_verifikasi_aktivitas_km')) {
                $riwayatVerifikasi = \Illuminate\Support\Facades\DB::table('riwayat_verifikasi_aktivitas_km as rv')
                    ->join('aktivitas_km as ak', 'rv.id_aktivitas', '=', 'ak.id_aktivitas')
                    ->join('users as ua', 'rv.id_user_anggota', '=', 'ua.id_user')
                    ->leftJoin('dosen as da', 'ua.id_dosen', '=', 'da.id_dosen')
                    ->leftJoin('users as uv', 'rv.id_verifikator', '=', 'uv.id_user')
                    ->leftJoin('dosen as dv', 'uv.id_dosen', '=', 'dv.id_dosen')
                    ->where('rv.id_lab', $idLab)
                    ->where(function ($query) {
                        $query->where('ak.kategori_km', '!=', 'Pendidikan')
                            ->orWhereNull('ak.kategori_km');
                    })
                    ->select(
                        'rv.id_riwayat',
                        'rv.id_aktivitas',
                        'rv.keputusan',
                        'rv.catatan_verifikasi',
                        'rv.created_at as waktu_aksi',
                        'ak.judul_aktivitas',
                        'ak.kategori_km',
                        'ak.sub_kategori_km',
                        \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(da.nama_dosen, ''), ua.username) as nama_anggota"),
                        \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(dv.nama_dosen, ''), uv.username, '-') as nama_verifikator")
                    )
                    ->orderByDesc('rv.created_at')
                    ->limit(12)
                    ->get();
            }

            return view('ketualab.monitoring-anggota', compact(
                'lab',
                'tahun',
                'tahunOptions',
                'periode',
                'triwulan',
                'semester',
                'search',
                'tanggalMulai',
                'tanggalSelesai',
                'labelPeriode',
                'labelMode',
                'kategoriCards',
                'dataMonitoring',
                'jumlahAnggota',
                'totalKmAssignLab',
                'totalTargetPeriode',
                'totalRealisasiPeriode',
                'totalSisa',
                'persentaseTotal',
                'anggotaTercapai',
                'anggotaOnProgress',
                'anggotaBelumMulai',
                'rincianPerKategori',
                'kategoriDetail',
                'kategoriDitampilkan',
                'perPageKategori',
                'jumlahMenungguVerifikasi',
                'pengajuanMenungguVerifikasi',
                'riwayatVerifikasi'
            ));
        })->name('ketualab.monitoring-anggota');

        Route::get('/ketualab/monitoring-anggota/{id}', function (\Illuminate\Http\Request $request, $id) {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            /*
            |----------------------------------------------------------------------
            | Identitas Lab Ketua Lab
            |----------------------------------------------------------------------
            */
            $idLab = $user->id_lab;

            if (! $idLab && $user->id_dosen) {
                $idLab = \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_dosen', $user->id_dosen)
                    ->value('id_lab');
            }

            abort_unless($idLab, 403, 'Data Lab Riset tidak ditemukan.');

            /*
            |----------------------------------------------------------------------
            | Parameter filter
            |----------------------------------------------------------------------
            | "mode=tahunan" ikut diterima agar URL lama tidak lagi menghasilkan 404.
            */
            $tahun = (int) $request->query('tahun', now()->year);

            $periodeRequest = (string) $request->query(
                'periode',
                $request->query('mode', 'tahun')
            );

            $periode = match ($periodeRequest) {
                'tahunan', 'tahun' => 'tahun',
                'triwulan' => 'triwulan',
                'semester' => 'semester',
                default => 'tahun',
            };

            $triwulan = max(1, min(4, (int) $request->query('triwulan', ceil(now()->month / 3))));
            $semester = max(1, min(2, (int) $request->query('semester', now()->month <= 6 ? 1 : 2)));

            if ($periode === 'triwulan') {
                $bulanMulai = (($triwulan - 1) * 3) + 1;
                $bulanSelesai = $bulanMulai + 2;

                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();

                $triwulanAktif = [$triwulan];
                $labelPeriode = 'Triwulan ' . $triwulan . ' Tahun ' . $tahun;
            } elseif ($periode === 'semester') {
                $bulanMulai = $semester === 1 ? 1 : 7;
                $bulanSelesai = $semester === 1 ? 6 : 12;

                $tanggalMulai = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();

                $triwulanAktif = $semester === 1 ? [1, 2] : [3, 4];
                $labelPeriode = 'Semester ' . $semester . ' Tahun ' . $tahun;
            } else {
                $periode = 'tahun';
                $tanggalMulai = \Carbon\Carbon::create($tahun, 1, 1)->startOfYear();
                $tanggalSelesai = \Carbon\Carbon::create($tahun, 12, 31)->endOfYear();

                $triwulanAktif = [1, 2, 3, 4];
                $labelPeriode = 'Tahunan ' . $tahun;
            }

            $tanggalMulaiTahun = \Carbon\Carbon::create($tahun, 1, 1)->startOfYear();
            $tanggalSelesaiTahun = \Carbon\Carbon::create($tahun, 12, 31)->endOfYear();

            $kategoriDefault = [
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            /*
            |----------------------------------------------------------------------
            | Pastikan anggota benar-benar berada pada Lab Ketua Lab yang login
            |----------------------------------------------------------------------
            */
            $anggota = \Illuminate\Support\Facades\DB::table('users as u')
                ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
                ->where('u.id_user', $id)
                ->where('u.role', 'Anggota')
                ->where(function ($query) use ($idLab) {
                    $query->where('u.id_lab', $idLab)
                        ->orWhere('d.id_lab', $idLab);
                })
                ->select(
                    'u.id_user',
                    'u.username',
                    'u.id_dosen',
                    \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(d.nama_dosen, ''), u.username) as nama_anggota"),
                    \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(d.nidn, ''), '-') as nidn"),
                    \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(d.email, ''), '-') as email"),
                    \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(d.jad, ''), 'AA') as jad")
                )
                ->first();

            abort_unless($anggota, 404, 'Anggota Lab tidak ditemukan.');

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $idLab)
                ->first();

            /*
            |----------------------------------------------------------------------
            | Tahun yang tersedia
            |----------------------------------------------------------------------
            */
            $tahunOptions = collect()
                ->merge(
                    \Illuminate\Support\Facades\DB::table('km_anggota as ka')
                        ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                        ->where('ka.id_user', $anggota->id_user)
                        ->where('kl.id_lab', $idLab)
                        ->where('kl.kategori_km', '!=', 'Pendidikan')
                        ->pluck('kl.tahun_km')
                )
                ->merge(
                    \Illuminate\Support\Facades\DB::table('aktivitas_km')
                        ->where('id_user', $anggota->id_user)
                        ->where('kategori_km', '!=', 'Pendidikan')
                        ->whereNotNull('tanggal_mulai')
                        ->pluck('tanggal_mulai')
                        ->map(function ($tanggal) {
                            try {
                                return \Carbon\Carbon::parse($tanggal)->year;
                            } catch (\Throwable $e) {
                                return null;
                            }
                        })
                        ->filter()
                )
                ->push(now()->year)
                ->push($tahun)
                ->filter()
                ->map(fn ($item) => (int) $item)
                ->unique()
                ->sortDesc()
                ->values();

            /*
            |----------------------------------------------------------------------
            | Kompatibilitas struktur database
            |----------------------------------------------------------------------
            */
            $hasKaTriwulan = \Illuminate\Support\Facades\Schema::hasColumns('km_anggota', [
                'triwulan_1',
                'triwulan_2',
                'triwulan_3',
                'triwulan_4',
            ]);

            $hasKlTriwulan = \Illuminate\Support\Facades\Schema::hasColumns('km_lab', [
                'triwulan_1',
                'triwulan_2',
                'triwulan_3',
                'triwulan_4',
            ]);

            $hasIdTargetKmLab = \Illuminate\Support\Facades\Schema::hasColumn('km_lab', 'id_target');
            $hasSubKategoriKmLab = \Illuminate\Support\Facades\Schema::hasColumn('km_lab', 'sub_kategori_km');

            $hasAktivitasIdKmAnggota = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'id_km_anggota');
            $hasAktivitasStatus = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'status_progress');

            $hasDeadlineTw1 = \Illuminate\Support\Facades\Schema::hasColumn('target_km', 'tanggal_selesai_tw1');
            $hasDeadlineTw2 = \Illuminate\Support\Facades\Schema::hasColumn('target_km', 'tanggal_selesai_tw2');
            $hasDeadlineTw3 = \Illuminate\Support\Facades\Schema::hasColumn('target_km', 'tanggal_selesai_tw3');
            $hasDeadlineTw4 = \Illuminate\Support\Facades\Schema::hasColumn('target_km', 'tanggal_selesai_tw4');

            /*
            |----------------------------------------------------------------------
            | Target KM yang telah diberikan kepada anggota
            |----------------------------------------------------------------------
            */
            $targetQuery = \Illuminate\Support\Facades\DB::table('km_anggota as ka')
                ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                ->where('ka.id_user', $anggota->id_user)
                ->where('kl.id_lab', $idLab)
                ->where('kl.tahun_km', $tahun)
                ->where('kl.status_km', 'Aktif')
                ->where('kl.kategori_km', '!=', 'Pendidikan')
                ->select(
                    'ka.id_km_anggota',
                    'ka.id_km_lab',
                    'ka.jumlah_km',
                    'ka.created_at as tanggal_assign',
                    'kl.tahun_km',
                    'kl.kategori_km'
                );

            if ($hasSubKategoriKmLab) {
                $targetQuery->addSelect('kl.sub_kategori_km');
            } else {
                $targetQuery->addSelect(\Illuminate\Support\Facades\DB::raw("'-' as sub_kategori_km"));
            }

            if ($hasKaTriwulan) {
                $targetQuery->addSelect(
                    \Illuminate\Support\Facades\DB::raw('COALESCE(ka.triwulan_1, 0) as target_tw_1'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(ka.triwulan_2, 0) as target_tw_2'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(ka.triwulan_3, 0) as target_tw_3'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(ka.triwulan_4, 0) as target_tw_4')
                );
            } elseif ($hasKlTriwulan) {
                $targetQuery->addSelect(
                    \Illuminate\Support\Facades\DB::raw('COALESCE(kl.triwulan_1, 0) as target_tw_1'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(kl.triwulan_2, 0) as target_tw_2'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(kl.triwulan_3, 0) as target_tw_3'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(kl.triwulan_4, 0) as target_tw_4')
                );
            } else {
                $targetQuery->addSelect(
                    \Illuminate\Support\Facades\DB::raw('0 as target_tw_1'),
                    \Illuminate\Support\Facades\DB::raw('0 as target_tw_2'),
                    \Illuminate\Support\Facades\DB::raw('0 as target_tw_3'),
                    \Illuminate\Support\Facades\DB::raw('0 as target_tw_4')
                );
            }

            if ($hasIdTargetKmLab) {
                $targetQuery->leftJoin('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                    ->addSelect(
                        \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(tk.keterangan, ''), '-') as keterangan"),
                        $hasDeadlineTw1 ? 'tk.tanggal_selesai_tw1' : \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw1'),
                        $hasDeadlineTw2 ? 'tk.tanggal_selesai_tw2' : \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw2'),
                        $hasDeadlineTw3 ? 'tk.tanggal_selesai_tw3' : \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw3'),
                        $hasDeadlineTw4 ? 'tk.tanggal_selesai_tw4' : \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw4')
                    );
            } else {
                $targetQuery->addSelect(
                    \Illuminate\Support\Facades\DB::raw("'-' as keterangan"),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw1'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw2'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw3'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw4')
                );
            }

            $targetRows = $targetQuery
                ->orderBy('kl.kategori_km')
                ->orderBy('kl.id_km_lab')
                ->get();

            /*
            |----------------------------------------------------------------------
            | Seluruh aktivitas tahun terpilih untuk realisasi TW1-TW4
            |----------------------------------------------------------------------
            */
            $aktivitasTahunanQuery = \Illuminate\Support\Facades\DB::table('aktivitas_km as ak')
                ->where('ak.id_user', $anggota->id_user)
                ->whereNotNull('ak.tanggal_mulai')
                ->whereDate('ak.tanggal_mulai', '>=', $tanggalMulaiTahun->toDateString())
                ->whereDate('ak.tanggal_mulai', '<=', $tanggalSelesaiTahun->toDateString())
                ->where(function ($query) {
                    $query->where('ak.kategori_km', '!=', 'Pendidikan')
                        ->orWhereNull('ak.kategori_km');
                })
                ->select(
                    'ak.id_aktivitas',
                    'ak.id_km_anggota',
                    'ak.kategori_km',
                    'ak.sub_kategori_km',
                    'ak.judul_aktivitas',
                    'ak.deskripsi_singkat',
                    'ak.tanggal_mulai',
                    'ak.tanggal_selesai',
                    'ak.bukti_link',
                    'ak.bukti_file_path',
                    'ak.bukti_pdf_path',
                    'ak.bukti_file_nama_asli',
                    'ak.diajukan_pada',
                    'ak.diverifikasi_pada',
                    'ak.diverifikasi_oleh',
                    'ak.catatan_verifikasi',
                    'ak.created_at',
                    'ak.updated_at'
                );

            if ($hasAktivitasStatus) {
                $aktivitasTahunanQuery->addSelect('ak.status_progress');
            } else {
                $aktivitasTahunanQuery->addSelect(\Illuminate\Support\Facades\DB::raw("'Accepted' as status_progress"));
            }

            $aktivitasTahunan = $aktivitasTahunanQuery
                ->orderByDesc('ak.tanggal_mulai')
                ->orderByDesc('ak.created_at')
                ->get();

            $realisasiPerTarget = [];
            $aktivitasPerTarget = [];

            foreach ($aktivitasTahunan as $aktivitas) {
                $idKmAnggota = (int) ($aktivitas->id_km_anggota ?? 0);

                if ($idKmAnggota <= 0) {
                    continue;
                }

                try {
                    $twAktivitas = (int) ceil(\Carbon\Carbon::parse($aktivitas->tanggal_mulai)->month / 3);
                } catch (\Throwable $e) {
                    continue;
                }

                if (!isset($aktivitasPerTarget[$idKmAnggota])) {
                    $aktivitasPerTarget[$idKmAnggota] = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
                    $realisasiPerTarget[$idKmAnggota] = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
                }

                $aktivitasPerTarget[$idKmAnggota][$twAktivitas]++;

                $isAccepted = !$hasAktivitasStatus || (($aktivitas->status_progress ?? null) === 'Accepted');

                if ($isAccepted) {
                    $realisasiPerTarget[$idKmAnggota][$twAktivitas]++;
                }
            }

            /*
            |----------------------------------------------------------------------
            | Rincian target anggota dan ringkasan per kategori
            |----------------------------------------------------------------------
            */
            $rincianTarget = $targetRows->map(function ($row) use (
                $triwulanAktif,
                $realisasiPerTarget,
                $aktivitasPerTarget
            ) {
                $idKmAnggota = (int) $row->id_km_anggota;

                $targetTw = [
                    1 => (int) ($row->target_tw_1 ?? 0),
                    2 => (int) ($row->target_tw_2 ?? 0),
                    3 => (int) ($row->target_tw_3 ?? 0),
                    4 => (int) ($row->target_tw_4 ?? 0),
                ];

                /*
                | Jika target lama belum memiliki pembagian TW, distribusikan secara
                | merata supaya target periode tetap dapat dihitung.
                */
                if (array_sum($targetTw) <= 0 && (int) $row->jumlah_km > 0) {
                    $dasar = intdiv((int) $row->jumlah_km, 4);
                    $sisa = (int) $row->jumlah_km % 4;

                    $targetTw = [
                        1 => $dasar + ($sisa >= 1 ? 1 : 0),
                        2 => $dasar + ($sisa >= 2 ? 1 : 0),
                        3 => $dasar + ($sisa >= 3 ? 1 : 0),
                        4 => $dasar,
                    ];
                }

                $realisasiTw = $realisasiPerTarget[$idKmAnggota] ?? [1 => 0, 2 => 0, 3 => 0, 4 => 0];
                $aktivitasTw = $aktivitasPerTarget[$idKmAnggota] ?? [1 => 0, 2 => 0, 3 => 0, 4 => 0];

                $targetPeriode = collect($triwulanAktif)->sum(fn ($tw) => (int) ($targetTw[$tw] ?? 0));
                $realisasiPeriode = collect($triwulanAktif)->sum(fn ($tw) => (int) ($realisasiTw[$tw] ?? 0));
                $aktivitasPeriode = collect($triwulanAktif)->sum(fn ($tw) => (int) ($aktivitasTw[$tw] ?? 0));

                $sisa = max($targetPeriode - $realisasiPeriode, 0);
                $persentase = $targetPeriode > 0
                    ? min((int) round(($realisasiPeriode / $targetPeriode) * 100), 100)
                    : 0;

                if ($targetPeriode <= 0) {
                    $status = 'Belum Ada Target';
                    $statusClass = 'secondary';
                } elseif ($realisasiPeriode >= $targetPeriode) {
                    $status = 'Tercapai';
                    $statusClass = 'success';
                } elseif ($aktivitasPeriode > 0) {
                    $status = 'Sedang Berjalan';
                    $statusClass = 'warning';
                } else {
                    $status = 'Belum Mulai';
                    $statusClass = 'danger';
                }

                $row->target_tw = $targetTw;
                $row->realisasi_tw = $realisasiTw;
                $row->target_periode = $targetPeriode;
                $row->realisasi_periode = $realisasiPeriode;
                $row->sisa = $sisa;
                $row->persentase = $persentase;
                $row->status = $status;
                $row->status_class = $statusClass;

                return $row;
            });

            $rekapKategori = collect($kategoriDefault)->map(function ($kategori) use ($rincianTarget) {
                $rows = $rincianTarget->where('kategori_km', $kategori)->values();

                $target = (int) $rows->sum('target_periode');
                $realisasi = (int) $rows->sum('realisasi_periode');
                $sisa = max($target - $realisasi, 0);

                return [
                    'kategori' => $kategori,
                    'target' => $target,
                    'realisasi' => $realisasi,
                    'sisa' => $sisa,
                    'persentase' => $target > 0
                        ? min((int) round(($realisasi / $target) * 100), 100)
                        : 0,
                ];
            })->values();

            $totalTargetTahunan = (int) $rincianTarget->sum('jumlah_km');
            $totalTargetPeriode = (int) $rincianTarget->sum('target_periode');
            $totalRealisasi = (int) $rincianTarget->sum('realisasi_periode');
            $totalSisa = max($totalTargetPeriode - $totalRealisasi, 0);
            $persentaseTotal = $totalTargetPeriode > 0
                ? min((int) round(($totalRealisasi / $totalTargetPeriode) * 100), 100)
                : 0;

            /*
            |----------------------------------------------------------------------
            | Riwayat aktivitas pada periode aktif
            |----------------------------------------------------------------------
            */
            $riwayatAktivitas = $aktivitasTahunan
                ->filter(function ($aktivitas) use ($tanggalMulai, $tanggalSelesai) {
                    try {
                        $tanggal = \Carbon\Carbon::parse($aktivitas->tanggal_mulai);
                        return $tanggal->greaterThanOrEqualTo($tanggalMulai)
                            && $tanggal->lessThanOrEqualTo($tanggalSelesai);
                    } catch (\Throwable $e) {
                        return false;
                    }
                })
                ->values();

            return view('ketualab.monitoring-anggota-detail', compact(
                'lab',
                'anggota',
                'tahun',
                'tahunOptions',
                'periode',
                'triwulan',
                'semester',
                'tanggalMulai',
                'tanggalSelesai',
                'labelPeriode',
                'triwulanAktif',
                'rincianTarget',
                'rekapKategori',
                'riwayatAktivitas',
                'totalTargetTahunan',
                'totalTargetPeriode',
                'totalRealisasi',
                'totalSisa',
                'persentaseTotal'
            ));
        }) ->whereNumber('id')
            ->name('ketualab.monitoring-anggota.detail');

        /*
        |--------------------------------------------------------------------------
        | Detail aktivitas KM untuk verifikasi
        |--------------------------------------------------------------------------
        | Halaman ini dapat dibuka dari Dashboard, Monitoring Anggota, maupun
        | detail anggota. Ketua Lab membaca bukti dan rincian aktivitas terlebih
        | dahulu, lalu mengambil keputusan pada halaman yang sama.
        */
        Route::get('/ketualab/aktivitas-km/{id}/detail', function (\Illuminate\Http\Request $request, $id) {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $idLab = $user->id_lab;

            if (! $idLab && $user->id_dosen) {
                $idLab = \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_dosen', $user->id_dosen)
                    ->value('id_lab');
            }

            abort_unless($idLab, 403, 'Data Lab Riset tidak ditemukan.');

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km as ak')
                ->join('users as u', 'ak.id_user', '=', 'u.id_user')
                ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
                ->leftJoin('km_anggota as ka', 'ak.id_km_anggota', '=', 'ka.id_km_anggota')
                ->leftJoin('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                ->leftJoin('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                ->where('ak.id_aktivitas', $id)
                ->where('ak.id_lab', $idLab)
                ->where(function ($query) {
                    $query->where('ak.kategori_km', '!=', 'Pendidikan')
                        ->orWhereNull('ak.kategori_km');
                })
                ->select(
                    'ak.id_aktivitas',
                    'ak.id_user',
                    'ak.id_lab',
                    'ak.id_km_anggota',
                    'ak.kategori_km',
                    'ak.sub_kategori_km',
                    'ak.judul_aktivitas',
                    'ak.deskripsi_singkat',
                    'ak.tanggal_mulai',
                    'ak.tanggal_selesai',
                    'ak.bukti_link',
                    'ak.bukti_file_path',
                    'ak.bukti_pdf_path',
                    'ak.bukti_file_nama_asli',
                    'ak.status_progress',
                    'ak.diajukan_pada',
                    'ak.diverifikasi_pada',
                    'ak.diverifikasi_oleh',
                    'ak.catatan_verifikasi',
                    'ak.created_at',
                    'ak.updated_at',
                    'kl.tahun_km',
                    \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(tk.keterangan, ''), '-') as keterangan_target"),
                    \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(d.nama_dosen, ''), u.username) as nama_anggota"),
                    \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(d.nidn, ''), '-') as nidn_anggota"),
                    \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(d.email, ''), '-') as email_anggota")
                )
                ->first();

            abort_unless($aktivitas, 404, 'Aktivitas KM tidak ditemukan atau bukan milik Lab Anda.');

            $riwayatAktivitas = collect();

            if (\Illuminate\Support\Facades\Schema::hasTable('riwayat_verifikasi_aktivitas_km')) {
                $riwayatAktivitas = \Illuminate\Support\Facades\DB::table('riwayat_verifikasi_aktivitas_km as rv')
                    ->leftJoin('users as uv', 'rv.id_verifikator', '=', 'uv.id_user')
                    ->leftJoin('dosen as dv', 'uv.id_dosen', '=', 'dv.id_dosen')
                    ->where('rv.id_aktivitas', $aktivitas->id_aktivitas)
                    ->select(
                        'rv.id_riwayat',
                        'rv.keputusan',
                        'rv.catatan_verifikasi',
                        'rv.created_at as waktu_aksi',
                        \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(dv.nama_dosen, ''), uv.username, '-') as nama_verifikator")
                    )
                    ->orderByDesc('rv.created_at')
                    ->get();
            }

            $asal = $request->query('from') === 'dashboard' ? 'dashboard' : 'monitoring';
            $tahun = (int) $request->query('tahun', now()->year);
            $mode = (string) $request->query('mode', $request->query('periode', 'tahun'));
            $periode = match ($mode) {
                'tahunan', 'tahun' => 'tahun',
                'triwulan' => 'triwulan',
                'semester' => 'semester',
                default => 'tahun',
            };
            $triwulan = max(1, min(4, (int) $request->query('triwulan', ceil(now()->month / 3))));
            $semester = max(1, min(2, (int) $request->query('semester', now()->month <= 6 ? 1 : 2)));

            if ($asal === 'dashboard') {
                $backUrl = route('ketualab.dashboard', [
                    'tahun' => $tahun,
                    'mode' => $periode === 'tahun' ? 'tahunan' : $periode,
                    'triwulan' => $triwulan,
                    'semester' => $semester,
                ]);
            } else {
                $backUrl = route('ketualab.monitoring-anggota', [
                    'tahun' => $tahun,
                    'periode' => $periode,
                    'triwulan' => $triwulan,
                    'semester' => $semester,
                ]);
            }

            return view('ketualab.aktivitas-km-detail', compact(
                'aktivitas',
                'riwayatAktivitas',
                'asal',
                'backUrl',
                'tahun',
                'periode',
                'triwulan',
                'semester'
            ));
        })->whereNumber('id')->name('ketualab.aktivitas-km.detail');

        Route::patch('/ketualab/aktivitas-km/{id}/verifikasi', [\App\Http\Controllers\AktivitasKmController::class, 'verifikasi'])
            ->whereNumber('id')
            ->name('ketualab.aktivitas-km.verifikasi');

        /*
        | URL lama tetap diarahkan ke detail baru agar link yang sudah terlanjur ada
        | tidak lagi menghasilkan 404.
        */
        Route::get('/ketualab/detail-anggota/{id}', function (\Illuminate\Http\Request $request, $id) {
            $periodeLama = (string) $request->query(
                'periode',
                $request->query('mode', 'tahun')
            );

            $periode = match ($periodeLama) {
                'tahunan', 'tahun' => 'tahun',
                'triwulan' => 'triwulan',
                'semester' => 'semester',
                default => 'tahun',
            };

            return redirect()->route('ketualab.monitoring-anggota.detail', [
                'id' => $id,
                'tahun' => (int) $request->query('tahun', now()->year),
                'periode' => $periode,
                'triwulan' => max(1, min(4, (int) $request->query('triwulan', ceil(now()->month / 3)))),
                'semester' => max(1, min(2, (int) $request->query('semester', now()->month <= 6 ? 1 : 2))),
            ]);
        })->whereNumber('id')->name('ketualab.detail-anggota.legacy');
        Route::get('/ketualab/laporan', [KetuaLabReportController::class, 'index'])
            ->name('ketualab.laporan.index');

        Route::get('/ketualab/laporan/download', [KetuaLabReportController::class, 'download'])
            ->name('ketualab.laporan.download');
        Route::get('/ketualab/profil', function () {
            $user = auth()->user();

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $user->id_lab)
                ->first();

            return view('ketualab.profil', compact('user', 'lab'));
        });
    });


    // RUANG KHUSUS ANGGOTA
    Route::middleware(['auth', 'role:Anggota'])->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Dashboard Anggota
        |--------------------------------------------------------------------------
        | Menggunakan AnggotaController agar seluruh variabel dashboard:
        | kategori, grafik, target KM, tenggat, dan riwayat aktivitas tersedia.
        */
        Route::get('/anggota/dashboard', [AnggotaController::class, 'dashboard'])
            ->name('anggota.dashboard');


        /*
        |--------------------------------------------------------------------------
        | Laporan Anggota
        |--------------------------------------------------------------------------
        */
        Route::get('/anggota/laporan', [AnggotaReportController::class, 'index'])
            ->name('anggota.laporan.index');

        Route::get('/anggota/laporan/download', [AnggotaReportController::class, 'download'])
            ->name('anggota.laporan.download');

        /*
        |--------------------------------------------------------------------------
        | Daftar Aktivitas KM Anggota
        |--------------------------------------------------------------------------
        | Route GET ini sebelumnya hilang. Tanpanya, redirect setelah simpan
        | aktivitas dan menu "Aktivitas KM" akan menghasilkan 404.
        */
        Route::get('/anggota/aktivitas-km', function (\Illuminate\Http\Request $request) {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            /*
            |--------------------------------------------------------------------------
            | Filter halaman
            |--------------------------------------------------------------------------
            | - Ringkasan KM memakai periode: Tahunan / Triwulan / Semester.
            | - Daftar Target, Daftar Aktivitas, dan Riwayat memakai tahun yang sama.
            */
            $tahun = (int) $request->query('tahun', now()->year);
            $periode = (string) $request->query('periode', 'tahun');

            if (!in_array($periode, ['tahun', 'triwulan', 'semester'], true)) {
                $periode = 'tahun';
            }

            $triwulan = (int) $request->query('triwulan', ceil(now()->month / 3));
            $triwulan = max(1, min(4, $triwulan));

            $semester = (int) $request->query('semester', now()->month <= 6 ? 1 : 2);
            $semester = max(1, min(2, $semester));

            $tanggalMulaiTahun = \Carbon\Carbon::create($tahun, 1, 1)->startOfDay();
            $tanggalSelesaiTahun = \Carbon\Carbon::create($tahun, 12, 31)->endOfDay();

            if ($periode === 'triwulan') {
                $bulanMulai = (($triwulan - 1) * 3) + 1;
                $bulanSelesai = $bulanMulai + 2;

                $tanggalMulaiPeriode = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
                $tanggalSelesaiPeriode = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();

                $labelPeriode = 'Triwulan ' . $triwulan . ' Tahun ' . $tahun;
                $keteranganPeriode = 'Data target dan realisasi ditampilkan untuk Triwulan ' . $triwulan . '.';
            } elseif ($periode === 'semester') {
                $bulanMulai = $semester === 1 ? 1 : 7;
                $bulanSelesai = $semester === 1 ? 6 : 12;

                $tanggalMulaiPeriode = \Carbon\Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
                $tanggalSelesaiPeriode = \Carbon\Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth();

                $labelPeriode = 'Semester ' . $semester . ' Tahun ' . $tahun;
                $keteranganPeriode = 'Data target dan realisasi ditampilkan untuk Semester ' . $semester . '.';
            } else {
                $periode = 'tahun';
                $tanggalMulaiPeriode = $tanggalMulaiTahun->copy();
                $tanggalSelesaiPeriode = $tanggalSelesaiTahun->copy();

                $labelPeriode = 'Tahunan ' . $tahun;
                $keteranganPeriode = 'Data target dan realisasi ditampilkan untuk satu tahun penuh.';
            }

            /*
            |--------------------------------------------------------------------------
            | Nama anggota untuk judul halaman
            |--------------------------------------------------------------------------
            */
            $dataAnggota = \Illuminate\Support\Facades\DB::table('users as u')
                ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
                ->where('u.id_user', $user->id_user)
                ->select('u.username', 'd.nama_dosen')
                ->first();

            $namaAnggota = trim((string) ($dataAnggota->nama_dosen ?? '')) !== ''
                ? $dataAnggota->nama_dosen
                : ($dataAnggota->username ?? $user->username ?? 'Anggota');

            /*
            |--------------------------------------------------------------------------
            | Pilihan tahun
            |--------------------------------------------------------------------------
            */
            $tahunTarget = \Illuminate\Support\Facades\DB::table('km_anggota as ka')
                ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                ->where('ka.id_user', $user->id_user)
                ->whereNotNull('kl.tahun_km')
                ->pluck('kl.tahun_km');

            $tahunAktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_user', $user->id_user)
                ->whereNotNull('tanggal_mulai')
                ->pluck('tanggal_mulai')
                ->map(function ($tanggal) {
                    try {
                        return \Carbon\Carbon::parse($tanggal)->year;
                    } catch (\Throwable $e) {
                        return null;
                    }
                })
                ->filter();

            $tahunOptions = collect()
                ->merge($tahunTarget)
                ->merge($tahunAktivitas)
                ->push(now()->year)
                ->push($tahun)
                ->filter()
                ->map(fn ($item) => (int) $item)
                ->unique()
                ->sortDesc()
                ->values();

            /*
            |--------------------------------------------------------------------------
            | Cek struktur kolom supaya kompatibel dengan struktur database saat ini
            |--------------------------------------------------------------------------
            */
            $hasKmLabIdTarget = \Illuminate\Support\Facades\Schema::hasColumn('km_lab', 'id_target');

            $hasKmAnggotaTriwulan =
                \Illuminate\Support\Facades\Schema::hasColumn('km_anggota', 'triwulan_1') &&
                \Illuminate\Support\Facades\Schema::hasColumn('km_anggota', 'triwulan_2') &&
                \Illuminate\Support\Facades\Schema::hasColumn('km_anggota', 'triwulan_3') &&
                \Illuminate\Support\Facades\Schema::hasColumn('km_anggota', 'triwulan_4');

            $hasDeadlineTw1 = \Illuminate\Support\Facades\Schema::hasColumn('target_km', 'tanggal_selesai_tw1');
            $hasDeadlineTw2 = \Illuminate\Support\Facades\Schema::hasColumn('target_km', 'tanggal_selesai_tw2');
            $hasDeadlineTw3 = \Illuminate\Support\Facades\Schema::hasColumn('target_km', 'tanggal_selesai_tw3');
            $hasDeadlineTw4 = \Illuminate\Support\Facades\Schema::hasColumn('target_km', 'tanggal_selesai_tw4');

            $hasAktivitasIdKmAnggota = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'id_km_anggota');
            $hasStatusProgress = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'status_progress');

            /*
            |--------------------------------------------------------------------------
            | Target KM anggota untuk tahun yang dipilih
            |--------------------------------------------------------------------------
            */
            $targetQuery = \Illuminate\Support\Facades\DB::table('km_anggota as ka')
                ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                ->where('ka.id_user', $user->id_user)
                ->where('kl.status_km', 'Aktif')
                ->where('kl.tahun_km', $tahun)
                ->select(
                    'ka.id_km_anggota',
                    'ka.jumlah_km',
                    'ka.created_at as tanggal_assign',
                    'kl.tahun_km',
                    'kl.kategori_km',
                    'kl.sub_kategori_km'
                );

            if ($hasKmLabIdTarget) {
                $targetQuery
                    ->leftJoin('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                    ->addSelect(
                        \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(tk.keterangan, ''), '-') as keterangan"),
                        $hasDeadlineTw1
                            ? 'tk.tanggal_selesai_tw1'
                            : \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw1'),
                        $hasDeadlineTw2
                            ? 'tk.tanggal_selesai_tw2'
                            : \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw2'),
                        $hasDeadlineTw3
                            ? 'tk.tanggal_selesai_tw3'
                            : \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw3'),
                        $hasDeadlineTw4
                            ? 'tk.tanggal_selesai_tw4'
                            : \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw4')
                    );
            } else {
                $targetQuery->addSelect(
                    \Illuminate\Support\Facades\DB::raw("'-' as keterangan"),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw1'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw2'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw3'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw4')
                );
            }

            if ($hasKmAnggotaTriwulan) {
                $targetQuery->addSelect(
                    'ka.triwulan_1',
                    'ka.triwulan_2',
                    'ka.triwulan_3',
                    'ka.triwulan_4'
                );
            } else {
                $targetQuery->addSelect(
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_1'),
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_2'),
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_3'),
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_4')
                );
            }

            $targetKmSaya = $targetQuery
                ->orderBy('kl.kategori_km')
                ->orderBy('kl.sub_kategori_km')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Fallback distribusi target per triwulan
            |--------------------------------------------------------------------------
            | Dipakai jika data target lama belum mempunyai nilai TW1-TW4.
            */
            $bagiRataTriwulan = function (int $jumlah): array {
                $jumlah = max(0, $jumlah);
                $dasar = intdiv($jumlah, 4);
                $sisa = $jumlah % 4;

                return [
                    1 => $dasar + ($sisa >= 1 ? 1 : 0),
                    2 => $dasar + ($sisa >= 2 ? 1 : 0),
                    3 => $dasar + ($sisa >= 3 ? 1 : 0),
                    4 => $dasar,
                ];
            };

            /*
            |--------------------------------------------------------------------------
            | Aktivitas terkait target: target/realisasi per TW
            |--------------------------------------------------------------------------
            */
            $aktivitasTarget = collect();

            if ($hasAktivitasIdKmAnggota) {
                $aktivitasTargetQuery = \Illuminate\Support\Facades\DB::table('aktivitas_km as ak')
                    ->join('km_anggota as ka', 'ak.id_km_anggota', '=', 'ka.id_km_anggota')
                    ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                    ->where('ak.id_user', $user->id_user)
                    ->where('kl.tahun_km', $tahun)
                    ->whereNotNull('ak.id_km_anggota')
                    ->whereNotNull('ak.tanggal_mulai')
                    ->whereDate('ak.tanggal_mulai', '>=', $tanggalMulaiTahun->toDateString())
                    ->whereDate('ak.tanggal_mulai', '<=', $tanggalSelesaiTahun->toDateString())
                    ->select(
                        'ak.id_km_anggota',
                        'ak.tanggal_mulai',
                        'ak.created_at'
                    );

                if ($hasStatusProgress) {
                    $aktivitasTargetQuery->addSelect('ak.status_progress');
                }

                $aktivitasTarget = $aktivitasTargetQuery->get();
            }

            $realisasiPerTarget = [];

            foreach ($aktivitasTarget as $aktivitasTargetItem) {
                $idKmAnggota = (int) $aktivitasTargetItem->id_km_anggota;

                if (!isset($realisasiPerTarget[$idKmAnggota])) {
                    $realisasiPerTarget[$idKmAnggota] = [
                        'total_aktivitas' => 0,
                        'total_realisasi' => 0,
                        'realisasi_tw' => [1 => 0, 2 => 0, 3 => 0, 4 => 0],
                    ];
                }

                $realisasiPerTarget[$idKmAnggota]['total_aktivitas']++;

                $isAccepted = !$hasStatusProgress || (($aktivitasTargetItem->status_progress ?? null) === 'Accepted');

                if ($isAccepted) {
                    $realisasiPerTarget[$idKmAnggota]['total_realisasi']++;

                    try {
                        $bulanAktivitas = \Carbon\Carbon::parse($aktivitasTargetItem->tanggal_mulai)->month;
                        $twAktivitas = (int) ceil($bulanAktivitas / 3);
                        $realisasiPerTarget[$idKmAnggota]['realisasi_tw'][$twAktivitas]++;
                    } catch (\Throwable $e) {
                        // Tanggal tidak valid tidak dimasukkan ke realisasi per triwulan.
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Lengkapi setiap target dengan realisasi per TW dan status tahunan
            |--------------------------------------------------------------------------
            */
            $targetKmSaya = $targetKmSaya->map(function ($item) use ($realisasiPerTarget, $bagiRataTriwulan) {
                $idKmAnggota = (int) $item->id_km_anggota;
                $jumlahKm = (int) ($item->jumlah_km ?? 0);

                $targetTw = [
                    1 => (int) ($item->triwulan_1 ?? 0),
                    2 => (int) ($item->triwulan_2 ?? 0),
                    3 => (int) ($item->triwulan_3 ?? 0),
                    4 => (int) ($item->triwulan_4 ?? 0),
                ];

                if (array_sum($targetTw) <= 0 && $jumlahKm > 0) {
                    $targetTw = $bagiRataTriwulan($jumlahKm);
                }

                $realisasi = $realisasiPerTarget[$idKmAnggota] ?? [
                    'total_aktivitas' => 0,
                    'total_realisasi' => 0,
                    'realisasi_tw' => [1 => 0, 2 => 0, 3 => 0, 4 => 0],
                ];

                $totalAktivitas = (int) $realisasi['total_aktivitas'];
                $totalRealisasi = (int) $realisasi['total_realisasi'];
                $sisa = max($jumlahKm - $totalRealisasi, 0);

                if ($jumlahKm <= 0) {
                    $status = 'Belum Ada Target';
                    $statusClass = 'secondary';
                } elseif ($totalRealisasi >= $jumlahKm) {
                    $status = 'Tercapai';
                    $statusClass = 'success';
                } elseif ($totalAktivitas > 0) {
                    $status = 'Sedang Berjalan';
                    $statusClass = 'warning';
                } else {
                    $status = 'Belum Mulai';
                    $statusClass = 'danger';
                }

                $item->target_tw_1 = $targetTw[1];
                $item->target_tw_2 = $targetTw[2];
                $item->target_tw_3 = $targetTw[3];
                $item->target_tw_4 = $targetTw[4];

                $item->realisasi_tw_1 = (int) ($realisasi['realisasi_tw'][1] ?? 0);
                $item->realisasi_tw_2 = (int) ($realisasi['realisasi_tw'][2] ?? 0);
                $item->realisasi_tw_3 = (int) ($realisasi['realisasi_tw'][3] ?? 0);
                $item->realisasi_tw_4 = (int) ($realisasi['realisasi_tw'][4] ?? 0);

                $item->total_aktivitas = $totalAktivitas;
                $item->total_realisasi = $totalRealisasi;
                $item->sisa_km = $sisa;
                $item->status_target = $status;
                $item->status_class = $statusClass;

                return $item;
            });

            /*
            |--------------------------------------------------------------------------
            | Ringkasan KM per kategori sesuai periode yang dipilih
            |--------------------------------------------------------------------------
            */
            $kategoriDefault = [
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $kategoriCards = collect($kategoriDefault)->map(function ($kategori) use (
                $targetKmSaya,
                $periode,
                $triwulan,
                $semester
            ) {
                $rows = $targetKmSaya->where('kategori_km', $kategori);

                $target = 0;
                $realisasi = 0;

                foreach ($rows as $row) {
                    $targetPerTw = [
                        1 => (int) ($row->target_tw_1 ?? 0),
                        2 => (int) ($row->target_tw_2 ?? 0),
                        3 => (int) ($row->target_tw_3 ?? 0),
                        4 => (int) ($row->target_tw_4 ?? 0),
                    ];

                    $realisasiPerTw = [
                        1 => (int) ($row->realisasi_tw_1 ?? 0),
                        2 => (int) ($row->realisasi_tw_2 ?? 0),
                        3 => (int) ($row->realisasi_tw_3 ?? 0),
                        4 => (int) ($row->realisasi_tw_4 ?? 0),
                    ];

                    if ($periode === 'triwulan') {
                        $target += $targetPerTw[$triwulan] ?? 0;
                        $realisasi += $realisasiPerTw[$triwulan] ?? 0;
                    } elseif ($periode === 'semester') {
                        $daftarTw = $semester === 1 ? [1, 2] : [3, 4];

                        foreach ($daftarTw as $tw) {
                            $target += $targetPerTw[$tw] ?? 0;
                            $realisasi += $realisasiPerTw[$tw] ?? 0;
                        }
                    } else {
                        $target += (int) ($row->jumlah_km ?? 0);
                        $realisasi += (int) ($row->total_realisasi ?? 0);
                    }
                }

                $sisa = max($target - $realisasi, 0);
                $persentase = $target > 0
                    ? min((int) round(($realisasi / $target) * 100), 100)
                    : 0;

                $jumlahSubKategori = (int) $rows
                    ->pluck('sub_kategori_km')
                    ->filter()
                    ->unique()
                    ->count();

                if ($target <= 0) {
                    $catatan = 'Belum ada target pada periode ini.';
                    $catatanClass = 'secondary';
                } elseif ($realisasi >= $target) {
                    $catatan = 'Target kategori pada periode ini telah tercapai.';
                    $catatanClass = 'success';
                } elseif ($realisasi > 0) {
                    $catatan = 'Masih ada ' . $sisa . ' KM yang sedang dikejar pada periode ini.';
                    $catatanClass = 'danger';
                } else {
                    $catatan = 'Belum ada realisasi pada periode ini.';
                    $catatanClass = 'danger';
                }

                return [
                    'kategori' => $kategori,
                    'target' => $target,
                    'realisasi' => $realisasi,
                    'sisa' => $sisa,
                    'persentase' => $persentase,
                    'jumlah_subkategori' => $jumlahSubKategori,
                    'catatan' => $catatan,
                    'catatan_class' => $catatanClass,
                ];
            })->values();

            $totalTarget = (int) $kategoriCards->sum('target');
            $totalRealisasi = (int) $kategoriCards->sum('realisasi');
            $totalSisa = max($totalTarget - $totalRealisasi, 0);
            $persentaseTotal = $totalTarget > 0
                ? min((int) round(($totalRealisasi / $totalTarget) * 100), 100)
                : 0;

            /*
            |--------------------------------------------------------------------------
            | Daftar aktivitas dan riwayat aktivitas sesuai tahun
            |--------------------------------------------------------------------------
            */
            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_user', $user->id_user)
                ->whereBetween('tanggal_mulai', [
                    $tanggalMulaiTahun->toDateString(),
                    $tanggalSelesaiTahun->toDateString(),
                ])
                ->orderByDesc('tanggal_mulai')
                ->orderByDesc('created_at')
                ->get();

            $riwayatRealisasi = $aktivitas;

            return view('anggota.aktivitas-km.index', compact(
                'namaAnggota',
                'aktivitas',
                'riwayatRealisasi',
                'targetKmSaya',
                'tahun',
                'tahunOptions',
                'periode',
                'triwulan',
                'semester',
                'labelPeriode',
                'keteranganPeriode',
                'kategoriCards',
                'totalTarget',
                'totalRealisasi',
                'totalSisa',
                'persentaseTotal'
            ));
        })->name('anggota.aktivitas-km.index');


        Route::get('/anggota/riwayat-realisasi', function () {
            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_user', auth()->user()->id_user)
                ->orderBy('tanggal_mulai', 'desc')
                ->get();

            return view('anggota.riwayat-realisasi', compact('aktivitas'));
        });
        Route::get('/anggota/profil', function () {
            $user = auth()->user();

            $lab = \Illuminate\Support\Facades\DB::table('laboratorium_riset')
                ->where('id_lab', $user->id_lab)
                ->first();

            $dosen = null;

            if ($user->id_dosen) {
                $dosen = \Illuminate\Support\Facades\DB::table('dosen')
                    ->where('id_dosen', $user->id_dosen)
                    ->first();
            }

            return view('anggota.profil', compact('user', 'lab', 'dosen'));
        });
        Route::get('/anggota/aktivitas-km/create', function () {
            $user = auth()->user();

            $hasTriwulanKmAnggota =
                \Illuminate\Support\Facades\Schema::hasColumn('km_anggota', 'triwulan_1') &&
                \Illuminate\Support\Facades\Schema::hasColumn('km_anggota', 'triwulan_2') &&
                \Illuminate\Support\Facades\Schema::hasColumn('km_anggota', 'triwulan_3') &&
                \Illuminate\Support\Facades\Schema::hasColumn('km_anggota', 'triwulan_4');

            $kmOptionsQuery = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->leftJoin('laboratorium_riset', 'km_lab.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('km_anggota.id_user', $user->id_user)
                ->where('km_lab.status_km', 'Aktif')
                ->where('km_lab.kategori_km', '!=', 'Pendidikan')
                ->select(
                    'km_anggota.id_km_anggota',
                    'km_anggota.jumlah_km as jumlah_km_anggota',
                    'km_lab.id_lab',
                    'km_lab.tahun_km',
                    'km_lab.kategori_km',
                    'km_lab.sub_kategori_km',
                    'laboratorium_riset.nama_lab'
                );

            if ($hasTriwulanKmAnggota) {
                $kmOptionsQuery->addSelect(
                    'km_anggota.triwulan_1',
                    'km_anggota.triwulan_2',
                    'km_anggota.triwulan_3',
                    'km_anggota.triwulan_4'
                );
            } else {
                $kmOptionsQuery->addSelect(
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_1'),
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_2'),
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_3'),
                    \Illuminate\Support\Facades\DB::raw('0 as triwulan_4')
                );
            }

            $kmOptions = $kmOptionsQuery
                ->orderByDesc('km_lab.tahun_km')
                ->orderBy('km_lab.kategori_km')
                ->orderBy('km_lab.sub_kategori_km')
                ->get();

            $realisasiDisetujui = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_user', $user->id_user)
                ->where('status_progress', 'Accepted')
                ->whereNotNull('id_km_anggota')
                ->select('id_km_anggota', 'tanggal_mulai')
                ->get()
                ->groupBy('id_km_anggota');

            $bagiRataKeTriwulan = function (int $jumlah): array {
                $jumlah = max($jumlah, 0);
                $dasar = intdiv($jumlah, 4);
                $sisa = $jumlah % 4;

                return [
                    1 => $dasar + ($sisa >= 1 ? 1 : 0),
                    2 => $dasar + ($sisa >= 2 ? 1 : 0),
                    3 => $dasar + ($sisa >= 3 ? 1 : 0),
                    4 => $dasar,
                ];
            };

            $kmOptions = $kmOptions->map(function ($km) use ($realisasiDisetujui, $bagiRataKeTriwulan) {
                $targetPerTriwulan = [
                    1 => (int) ($km->triwulan_1 ?? 0),
                    2 => (int) ($km->triwulan_2 ?? 0),
                    3 => (int) ($km->triwulan_3 ?? 0),
                    4 => (int) ($km->triwulan_4 ?? 0),
                ];

                if (array_sum($targetPerTriwulan) <= 0) {
                    $targetPerTriwulan = $bagiRataKeTriwulan((int) ($km->jumlah_km_anggota ?? 0));
                }

                $realisasiPerTriwulan = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

                foreach ($realisasiDisetujui->get($km->id_km_anggota, collect()) as $aktivitas) {
                    if (empty($aktivitas->tanggal_mulai)) {
                        continue;
                    }

                    $tanggalMulai = \Carbon\Carbon::parse($aktivitas->tanggal_mulai);

                    if ((int) $tanggalMulai->year !== (int) $km->tahun_km) {
                        continue;
                    }

                    $nomorTriwulan = (int) ceil($tanggalMulai->month / 3);
                    $realisasiPerTriwulan[$nomorTriwulan]++;
                }

                foreach ([1, 2, 3, 4] as $nomorTriwulan) {
                    $km->{'target_tw_' . $nomorTriwulan} = $targetPerTriwulan[$nomorTriwulan];
                    $km->{'sisa_tw_' . $nomorTriwulan} = max(
                        $targetPerTriwulan[$nomorTriwulan] - $realisasiPerTriwulan[$nomorTriwulan],
                        0
                    );
                }

                return $km;
            });

            return view('anggota.aktivitas-km.create', compact('kmOptions'));
        });

        Route::post('/anggota/aktivitas-km', [\App\Http\Controllers\AktivitasKmController::class, 'store']);

        Route::get('/anggota/aktivitas-km/{id}/detail', function ($id) {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $hasAktivitasIdKmAnggota = \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'id_km_anggota');
            $hasKmLabIdTarget = \Illuminate\Support\Facades\Schema::hasColumn('km_lab', 'id_target');
            $hasVerificationFields =
                \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'catatan_verifikasi') &&
                \Illuminate\Support\Facades\Schema::hasColumn('aktivitas_km', 'diverifikasi_pada');

            $detailQuery = \Illuminate\Support\Facades\DB::table('aktivitas_km as ak')
                ->where('ak.id_aktivitas', $id)
                ->where('ak.id_user', $user->id_user)
                ->where('ak.kategori_km', '!=', 'Pendidikan')
                ->select(
                    'ak.id_aktivitas',
                    'ak.id_user',
                    'ak.id_lab',
                    'ak.id_km_anggota',
                    'ak.kategori_km',
                    'ak.sub_kategori_km',
                    'ak.judul_aktivitas',
                    'ak.deskripsi_singkat',
                    'ak.tanggal_mulai',
                    'ak.tanggal_selesai',
                    'ak.bukti_link',
                    'ak.bukti_file_path',
                    'ak.bukti_pdf_path',
                    'ak.bukti_file_nama_asli',
                    'ak.status_progress',
                    'ak.created_at',
                    'ak.updated_at',
                    $hasVerificationFields
                        ? 'ak.catatan_verifikasi'
                        : \Illuminate\Support\Facades\DB::raw('NULL as catatan_verifikasi'),
                    $hasVerificationFields
                        ? 'ak.diajukan_pada'
                        : \Illuminate\Support\Facades\DB::raw('NULL as diajukan_pada'),
                    $hasVerificationFields
                        ? 'ak.diverifikasi_pada'
                        : \Illuminate\Support\Facades\DB::raw('NULL as diverifikasi_pada')
                );

            if ($hasAktivitasIdKmAnggota) {
                $detailQuery
                    ->leftJoin('km_anggota as ka', 'ak.id_km_anggota', '=', 'ka.id_km_anggota')
                    ->leftJoin('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
                    ->leftJoin('laboratorium_riset as lr', 'kl.id_lab', '=', 'lr.id_lab')
                    ->addSelect(
                        'kl.tahun_km',
                        \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(lr.nama_lab, ''), '-') as nama_lab")
                    );

                if ($hasKmLabIdTarget) {
                    $detailQuery
                        ->leftJoin('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
                        ->addSelect(
                            \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(tk.keterangan, ''), '-') as keterangan_km"),
                            'tk.tanggal_selesai_tw1',
                            'tk.tanggal_selesai_tw2',
                            'tk.tanggal_selesai_tw3',
                            'tk.tanggal_selesai_tw4'
                        );
                } else {
                    $detailQuery->addSelect(
                        \Illuminate\Support\Facades\DB::raw("'-' as keterangan_km"),
                        \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw1'),
                        \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw2'),
                        \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw3'),
                        \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw4')
                    );
                }
            } else {
                $detailQuery->addSelect(
                    \Illuminate\Support\Facades\DB::raw('NULL as tahun_km'),
                    \Illuminate\Support\Facades\DB::raw("'-' as nama_lab"),
                    \Illuminate\Support\Facades\DB::raw("'-' as keterangan_km"),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw1'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw2'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw3'),
                    \Illuminate\Support\Facades\DB::raw('NULL as tanggal_selesai_tw4')
                );
            }

            $aktivitas = $detailQuery->first();

            abort_unless($aktivitas, 404, 'Detail aktivitas KM tidak ditemukan.');

            /*
            |------------------------------------------------------------------
            | Membaca notifikasi hasil verifikasi terkait aktivitas ini.
            |------------------------------------------------------------------
            */
            if (\Illuminate\Support\Facades\Schema::hasTable('notifikasi')) {
                $kolomNotifikasi = \Illuminate\Support\Facades\Schema::getColumnListing('notifikasi');

                if (
                    in_array('id_user', $kolomNotifikasi, true) &&
                    in_array('dibaca_pada', $kolomNotifikasi, true) &&
                    in_array('kode_unik', $kolomNotifikasi, true)
                ) {
                    $payloadBaca = ['dibaca_pada' => now()];

                    if (in_array('updated_at', $kolomNotifikasi, true)) {
                        $payloadBaca['updated_at'] = now();
                    }

                    \Illuminate\Support\Facades\DB::table('notifikasi')
                        ->where('id_user', $user->id_user)
                        ->whereNull('dibaca_pada')
                        ->where(function ($query) use ($aktivitas) {
                            $query->where('kode_unik', 'like', 'AKM-ACC-' . $aktivitas->id_aktivitas . '-%')
                                ->orWhere('kode_unik', 'like', 'AKM-TOLAK-' . $aktivitas->id_aktivitas . '-%');
                        })
                        ->update($payloadBaca);
                }
            }

            $riwayatVerifikasi = collect();

            if (\Illuminate\Support\Facades\Schema::hasTable('riwayat_verifikasi_aktivitas_km')) {
                $riwayatVerifikasi = \Illuminate\Support\Facades\DB::table('riwayat_verifikasi_aktivitas_km as rv')
                    ->leftJoin('users as uv', 'rv.id_verifikator', '=', 'uv.id_user')
                    ->leftJoin('dosen as dv', 'uv.id_dosen', '=', 'dv.id_dosen')
                    ->where('rv.id_aktivitas', $aktivitas->id_aktivitas)
                    ->select(
                        'rv.keputusan',
                        'rv.catatan_verifikasi',
                        'rv.created_at as waktu_aksi',
                        \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(dv.nama_dosen, ''), uv.username, '-') as nama_verifikator")
                    )
                    ->orderByDesc('rv.created_at')
                    ->get();
            }

            return view('anggota.aktivitas-km.detail-verifikasi', compact(
                'aktivitas',
                'riwayatVerifikasi'
            ));
        })->whereNumber('id')->name('anggota.aktivitas-km.detail');

        Route::get('/anggota/aktivitas-km/{id}/edit', function ($id) {
            $user = auth()->user();

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_aktivitas', $id)
                ->where('id_user', $user->id_user)
                ->first();

            if (! $aktivitas) {
                abort(404);
            }

            if (in_array((string) ($aktivitas->status_progress ?? 'On Progress'), ['Submitted', 'Accepted'], true)) {
                return redirect('/anggota/aktivitas-km')
                    ->with('error', 'Aktivitas yang sudah diajukan atau disetujui tidak dapat diedit. Tunggu hasil verifikasi Ketua Lab.');
            }

            $kmOptions = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->leftJoin('laboratorium_riset', 'km_lab.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('km_anggota.id_user', $user->id_user)
                ->select(
                    'km_anggota.id_km_anggota',
                    'km_anggota.jumlah_km as jumlah_km_anggota',
                    'km_lab.id_lab',
                    'km_lab.tahun_km',
                    'km_lab.kategori_km',
                    'km_lab.sub_kategori_km',
                    'laboratorium_riset.nama_lab'
                )
                ->orderByDesc('km_lab.tahun_km')
                ->orderBy('km_lab.kategori_km')
                ->get();

            return view('anggota.aktivitas-km.edit', compact('aktivitas', 'kmOptions'));
        });

        Route::put('/anggota/aktivitas-km/{id}', [\App\Http\Controllers\AktivitasKmController::class, 'update']);
        Route::get('/anggota/progress-km', function () {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $idUser = $user->id_user;
            $tahun = (int) request('tahun', now()->year);

            $kategoriDefault = [
                'Penelitian',
                'Publikasi',
                'Pengabdian',
                'Penunjang',
            ];

            $tahunOptions = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->where('km_anggota.id_user', $idUser)
                ->select('km_lab.tahun_km')
                ->distinct()
                ->orderBy('km_lab.tahun_km', 'desc')
                ->pluck('tahun_km');

            if ($tahunOptions->isEmpty()) {
                $tahunOptions = collect([now()->year]);
            }

            if (! $tahunOptions->contains($tahun)) {
                $tahunOptions->push($tahun);
                $tahunOptions = $tahunOptions->unique()->sortDesc()->values();
            }

            $daftarKm = \Illuminate\Support\Facades\DB::table('km_anggota')
                ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
                ->leftJoin('laboratorium_riset', 'km_lab.id_lab', '=', 'laboratorium_riset.id_lab')
                ->where('km_anggota.id_user', $idUser)
                ->where('km_lab.tahun_km', $tahun)
                ->select(
                    'km_anggota.id_km_anggota',
                    'km_anggota.jumlah_km',
                    'km_lab.tahun_km',
                    'km_lab.kategori_km',
                    'km_lab.sub_kategori_km',
                    'km_lab.status_km',
                    'laboratorium_riset.nama_lab'
                )
                ->orderBy('km_lab.kategori_km')
                ->orderBy('km_lab.sub_kategori_km')
                ->get();

            $aktivitas = \Illuminate\Support\Facades\DB::table('aktivitas_km')
                ->where('id_user', $idUser)
                ->whereNotNull('id_km_anggota')
                ->get()
                ->groupBy('id_km_anggota');

            $progressKategori = [];

            foreach ($kategoriDefault as $kategori) {
                $progressKategori[$kategori] = [
                    'kategori' => $kategori,
                    'target' => 0,
                    'realisasi' => 0,
                    'persentase' => 0,
                ];
            }

            $daftarProgressKm = [];

            foreach ($daftarKm as $km) {
                $aktivitasKm = $aktivitas->get($km->id_km_anggota, collect());

                $totalAktivitas = $aktivitasKm->count();

                $totalAccepted = $aktivitasKm
                    ->where('status_progress', 'Accepted')
                    ->count();

                $aktivitasTerakhir = $aktivitasKm
                    ->sortByDesc('updated_at')
                    ->first();

                $statusTerakhir = $aktivitasTerakhir->status_progress ?? 'Belum Mulai';

                $target = (int) $km->jumlah_km;
                $realisasi = (int) $totalAccepted;
                $sisa = max($target - $realisasi, 0);

                $persentase = $target > 0
                    ? min(round(($realisasi / $target) * 100), 100)
                    : 0;

                if ($target > 0 && $realisasi >= $target) {
                    $statusCapaian = 'Tercapai';
                } elseif ($totalAktivitas > 0) {
                    $statusCapaian = $statusTerakhir;
                } else {
                    $statusCapaian = 'Belum Mulai';
                }

                $daftarProgressKm[] = [
                    'id_km_anggota' => $km->id_km_anggota,
                    'tahun' => $km->tahun_km,
                    'lab' => $km->nama_lab ?? '-',
                    'kategori' => $km->kategori_km,
                    'sub_kategori' => $km->sub_kategori_km ?? '-',
                    'target' => $target,
                    'realisasi' => $realisasi,
                    'sisa' => $sisa,
                    'total_aktivitas' => $totalAktivitas,
                    'status_terakhir' => $statusTerakhir,
                    'status_capaian' => $statusCapaian,
                    'persentase' => $persentase,
                    'judul_terakhir' => $aktivitasTerakhir->judul_aktivitas ?? '-',
                    'id_aktivitas_terakhir' => $aktivitasTerakhir->id_aktivitas ?? null,
                    'bukti_link' => $aktivitasTerakhir->bukti_link ?? null,
                    'bukti_file_path' => $aktivitasTerakhir->bukti_file_path ?? null,
                    'bukti_pdf_path' => $aktivitasTerakhir->bukti_pdf_path ?? null,
                ];

                if (isset($progressKategori[$km->kategori_km])) {
                    $progressKategori[$km->kategori_km]['target'] += $target;
                    $progressKategori[$km->kategori_km]['realisasi'] += $realisasi;
                }
            }

            foreach ($progressKategori as $kategori => $item) {
                $target = $item['target'];
                $realisasi = $item['realisasi'];

                $progressKategori[$kategori]['persentase'] = $target > 0
                    ? min(round(($realisasi / $target) * 100), 100)
                    : 0;
            }

            $progressKategori = array_values($progressKategori);

            $totalTarget = array_sum(array_column($daftarProgressKm, 'target'));
            $totalRealisasi = array_sum(array_column($daftarProgressKm, 'realisasi'));
            $totalSisa = array_sum(array_column($daftarProgressKm, 'sisa'));

            $persentaseTotal = $totalTarget > 0
                ? min(round(($totalRealisasi / $totalTarget) * 100), 100)
                : 0;

            return view('anggota.progress-km', compact(
                'tahun',
                'tahunOptions',
                'progressKategori',
                'daftarProgressKm',
                'totalTarget',
                'totalRealisasi',
                'totalSisa',
                'persentaseTotal'
            ));
        });
    });
});
