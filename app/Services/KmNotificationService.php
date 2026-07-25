<?php

namespace App\Services;

use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KmNotificationService
{
    public static function notifyKetuaLabKmBaru(int $idKmLab): void
    {
        $kmLab = self::findKmLabWithTarget($idKmLab);

        if (!$kmLab) {
            return;
        }

        $ketuaLabIds = DB::table('users')
            ->where('role', 'Ketua Lab')
            ->where('id_lab', $kmLab->id_lab)
            ->pluck('id_user');

        if ($ketuaLabIds->isEmpty()) {
            return;
        }

        $tenggatText = self::buildDeadlineText($kmLab);

        $pesan = 'Anda menerima KM baru: '
            . ($kmLab->kategori_km ?? '-')
            . ' - '
            . ($kmLab->sub_kategori_km ?? '-')
            . '. Jumlah KM: '
            . (int) $kmLab->jumlah_km
            . '. Tenggat: '
            . $tenggatText
            . '.';

        foreach ($ketuaLabIds as $idUser) {
            self::createOnce([
                'id_user' => $idUser,
                'id_km_lab' => $kmLab->id_km_lab,
                'jenis_notifikasi' => 'km_baru_lab',
                'judul' => 'KM baru diturunkan ke Lab',
                'pesan' => $pesan,
                'kategori_km' => $kmLab->kategori_km,
                'sub_kategori_km' => $kmLab->sub_kategori_km,
                'jumlah_km' => (int) $kmLab->jumlah_km,
                'tanggal_tenggat' => self::firstRelevantDeadline($kmLab),
                'url_tujuan' => '/ketualab/penurunan-km',
                'kode_unik' => 'km-baru-lab-' . $kmLab->id_km_lab . '-user-' . $idUser,
            ]);
        }
    }

    public static function notifyAnggotaKmBaru(
        int $idKmAnggota,
        array $pembagianTriwulan,
        ?string $eventToken = null
    ): void {
        $kmAnggota = self::findKmAnggotaWithTarget($idKmAnggota);

        if (!$kmAnggota) {
            return;
        }

        $jumlahKmBaru = array_sum($pembagianTriwulan);

        $tenggatText = self::buildDeadlineText(
            $kmAnggota,
            $pembagianTriwulan
        );

        $rincianTriwulan = self::buildTriwulanAmountText(
            $pembagianTriwulan
        );

        $pesan = 'Anda menerima penugasan KM: '
            . ($kmAnggota->kategori_km ?? '-')
            . ' - '
            . ($kmAnggota->sub_kategori_km ?? '-')
            . '. Jumlah KM baru: '
            . $jumlahKmBaru
            . '. Pembagian: '
            . $rincianTriwulan
            . '. Tenggat: '
            . $tenggatText
            . '.';

        $token = $eventToken ?: now()->format('YmdHis') . '-' . uniqid();

        self::createOnce([
            'id_user' => $kmAnggota->id_user,
            'id_km_lab' => $kmAnggota->id_km_lab,
            'id_km_anggota' => $kmAnggota->id_km_anggota,
            'jenis_notifikasi' => 'km_baru_anggota',
            'judul' => 'KM baru dibagikan kepada Anda',
            'pesan' => $pesan,
            'kategori_km' => $kmAnggota->kategori_km,
            'sub_kategori_km' => $kmAnggota->sub_kategori_km,
            'jumlah_km' => $jumlahKmBaru,
            'tanggal_tenggat' => self::firstRelevantDeadline(
                $kmAnggota,
                $pembagianTriwulan
            ),
            'url_tujuan' => '/anggota/aktivitas-km',
            'kode_unik' => 'km-baru-anggota-' . $kmAnggota->id_km_anggota . '-' . $token,
        ]);
    }

    public static function sendDeadlineReminders(?Carbon $today = null): int
    {
        $today = ($today ?: Carbon::today())->startOfDay();
        $jumlahTerkirim = 0;

        $kmLabRows = DB::table('km_lab as kl')
            ->join('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
            ->where('kl.status_km', 'Aktif')
            ->select(
                'kl.id_km_lab',
                'kl.id_lab',
                'kl.kategori_km',
                'kl.sub_kategori_km',
                'kl.jumlah_km',
                'kl.triwulan_1',
                'kl.triwulan_2',
                'kl.triwulan_3',
                'kl.triwulan_4',
                'tk.tanggal_selesai_tw1',
                'tk.tanggal_selesai_tw2',
                'tk.tanggal_selesai_tw3',
                'tk.tanggal_selesai_tw4'
            )
            ->get();

        foreach ($kmLabRows as $kmLab) {
            foreach (self::getRelevantDeadlines($kmLab) as $deadlineData) {
                $deadline = $deadlineData['tanggal_tenggat']->copy()->startOfDay();

                $sisaHari = $today->diffInDays($deadline, false);

                if (!in_array($sisaHari, [10, 5, 1], true)) {
                    continue;
                }

                $triwulan = $deadlineData['triwulan'];
                $jumlahKmTriwulan = $deadlineData['jumlah_km'];
                $tanggalFormat = $deadline->format('d/m/Y');

                $ketuaLabIds = DB::table('users')
                    ->where('role', 'Ketua Lab')
                    ->where('id_lab', $kmLab->id_lab)
                    ->pluck('id_user');

                foreach ($ketuaLabIds as $idUser) {
                    $kodeUnik = implode('-', [
                        'reminder',
                        'lab',
                        $kmLab->id_km_lab,
                        'tw' . $triwulan,
                        'h' . $sisaHari,
                        'user' . $idUser,
                    ]);

                    $notifikasi = self::createOnce([
                        'id_user' => $idUser,
                        'id_km_lab' => $kmLab->id_km_lab,
                        'jenis_notifikasi' => 'peringatan_tenggat_h' . $sisaHari,
                        'judul' => 'Peringatan tenggat KM H-' . $sisaHari,
                        'pesan' => 'Tenggat Triwulan ' . $triwulan
                            . ' untuk KM '
                            . ($kmLab->kategori_km ?? '-')
                            . ' - '
                            . ($kmLab->sub_kategori_km ?? '-')
                            . ' akan berakhir pada '
                            . $tanggalFormat
                            . '. Jumlah KM Triwulan ' . $triwulan . ': '
                            . $jumlahKmTriwulan
                            . '.',
                        'kategori_km' => $kmLab->kategori_km,
                        'sub_kategori_km' => $kmLab->sub_kategori_km,
                        'jumlah_km' => $jumlahKmTriwulan,
                        'tanggal_tenggat' => $deadline->toDateString(),
                        'url_tujuan' => '/ketualab/penurunan-km',
                        'kode_unik' => $kodeUnik,
                    ]);

                    if ($notifikasi->wasRecentlyCreated) {
                        $jumlahTerkirim++;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Reminder untuk anggota hanya dikirim apabila anggota tersebut
                | benar-benar menerima KM pada Triwulan yang sedang diingatkan.
                |--------------------------------------------------------------------------
                */
                $assignments = DB::table('km_anggota')
                    ->where('id_km_lab', $kmLab->id_km_lab)
                    ->select(
                        'id_km_anggota',
                        'id_user',
                        'jumlah_km',
                        'triwulan_1',
                        'triwulan_2',
                        'triwulan_3',
                        'triwulan_4'
                    )
                    ->get();

                foreach ($assignments as $assignment) {
                    $jumlahKmAnggotaTw = (int) (
                        $assignment->{'triwulan_' . $triwulan}
                        ?? 0
                    );

                    if ($jumlahKmAnggotaTw <= 0) {
                        continue;
                    }

                    $kodeUnik = implode('-', [
                        'reminder',
                        'anggota',
                        $assignment->id_km_anggota,
                        'tw' . $triwulan,
                        'h' . $sisaHari,
                        'user' . $assignment->id_user,
                    ]);

                    $notifikasi = self::createOnce([
                        'id_user' => $assignment->id_user,
                        'id_km_lab' => $kmLab->id_km_lab,
                        'id_km_anggota' => $assignment->id_km_anggota,
                        'jenis_notifikasi' => 'peringatan_tenggat_h' . $sisaHari,
                        'judul' => 'Peringatan tenggat KM H-' . $sisaHari,
                        'pesan' => 'Tenggat Triwulan ' . $triwulan
                            . ' untuk KM '
                            . ($kmLab->kategori_km ?? '-')
                            . ' - '
                            . ($kmLab->sub_kategori_km ?? '-')
                            . ' akan berakhir pada '
                            . $tanggalFormat
                            . '. Jumlah KM Anda pada Triwulan ' . $triwulan . ': '
                            . $jumlahKmAnggotaTw
                            . '.',
                        'kategori_km' => $kmLab->kategori_km,
                        'sub_kategori_km' => $kmLab->sub_kategori_km,
                        'jumlah_km' => $jumlahKmAnggotaTw,
                        'tanggal_tenggat' => $deadline->toDateString(),
                        'url_tujuan' => '/anggota/aktivitas-km',
                        'kode_unik' => $kodeUnik,
                    ]);

                    if ($notifikasi->wasRecentlyCreated) {
                        $jumlahTerkirim++;
                    }
                }
            }
        }

        return $jumlahTerkirim;
    }

    private static function findKmLabWithTarget(int $idKmLab): ?object
    {
        return DB::table('km_lab as kl')
            ->join('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
            ->where('kl.id_km_lab', $idKmLab)
            ->select(
                'kl.id_km_lab',
                'kl.id_lab',
                'kl.kategori_km',
                'kl.sub_kategori_km',
                'kl.jumlah_km',
                'kl.triwulan_1',
                'kl.triwulan_2',
                'kl.triwulan_3',
                'kl.triwulan_4',
                'tk.tanggal_selesai_tw1',
                'tk.tanggal_selesai_tw2',
                'tk.tanggal_selesai_tw3',
                'tk.tanggal_selesai_tw4'
            )
            ->first();
    }

    private static function findKmAnggotaWithTarget(int $idKmAnggota): ?object
    {
        return DB::table('km_anggota as ka')
            ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
            ->join('target_km as tk', 'kl.id_target', '=', 'tk.id_target')
            ->where('ka.id_km_anggota', $idKmAnggota)
            ->select(
                'ka.id_km_anggota',
                'ka.id_user',
                'ka.jumlah_km as jumlah_km_anggota',

                'ka.triwulan_1 as anggota_triwulan_1',
                'ka.triwulan_2 as anggota_triwulan_2',
                'ka.triwulan_3 as anggota_triwulan_3',
                'ka.triwulan_4 as anggota_triwulan_4',

                'kl.id_km_lab',
                'kl.id_lab',
                'kl.kategori_km',
                'kl.sub_kategori_km',
                'kl.jumlah_km',

                'kl.triwulan_1',
                'kl.triwulan_2',
                'kl.triwulan_3',
                'kl.triwulan_4',

                'tk.tanggal_selesai_tw1',
                'tk.tanggal_selesai_tw2',
                'tk.tanggal_selesai_tw3',
                'tk.tanggal_selesai_tw4'
            )
            ->first();
    }

    private static function getRelevantDeadlines(
        object $km,
        ?array $triwulanOverride = null
    ): array {
        $deadlines = [];

        for ($triwulan = 1; $triwulan <= 4; $triwulan++) {
            if ($triwulanOverride !== null) {
                $jumlahKm = (int) ($triwulanOverride[$triwulan] ?? 0);
            } elseif (property_exists($km, 'anggota_triwulan_' . $triwulan)) {
                $jumlahKm = (int) (
                    $km->{'anggota_triwulan_' . $triwulan}
                    ?? 0
                );
            } else {
                $jumlahKm = (int) (
                    $km->{'triwulan_' . $triwulan}
                    ?? 0
                );
            }

            $tanggal = $km->{'tanggal_selesai_tw' . $triwulan} ?? null;

            if ($jumlahKm <= 0 || empty($tanggal)) {
                continue;
            }

            $deadlines[] = [
                'triwulan' => $triwulan,
                'jumlah_km' => $jumlahKm,
                'tanggal_tenggat' => Carbon::parse($tanggal),
            ];
        }

        return $deadlines;
    }

    private static function firstRelevantDeadline(
        object $km,
        ?array $triwulanOverride = null
    ): ?string {
        $deadlines = self::getRelevantDeadlines(
            $km,
            $triwulanOverride
        );

        if (empty($deadlines)) {
            return null;
        }

        return $deadlines[0]['tanggal_tenggat']->toDateString();
    }

    private static function buildDeadlineText(
        object $km,
        ?array $triwulanOverride = null
    ): string {
        $deadlines = self::getRelevantDeadlines(
            $km,
            $triwulanOverride
        );

        if (empty($deadlines)) {
            return 'belum ditentukan';
        }

        return collect($deadlines)
            ->map(function (array $item) {
                return 'TW' . $item['triwulan']
                    . ' (' . $item['tanggal_tenggat']->format('d/m/Y') . ')';
            })
            ->implode(', ');
    }

    private static function buildTriwulanAmountText(array $pembagianTriwulan): string
    {
        $items = [];

        for ($triwulan = 1; $triwulan <= 4; $triwulan++) {
            $jumlahKm = (int) ($pembagianTriwulan[$triwulan] ?? 0);

            if ($jumlahKm > 0) {
                $items[] = 'TW' . $triwulan . ': ' . $jumlahKm . ' KM';
            }
        }

        return empty($items)
            ? '-'
            : implode(', ', $items);
    }

    private static function createOnce(array $attributes): Notifikasi
    {
        return Notifikasi::firstOrCreate(
            ['kode_unik' => $attributes['kode_unik']],
            $attributes
        );
    }
}