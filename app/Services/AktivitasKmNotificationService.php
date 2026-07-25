<?php

namespace App\Services;

use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AktivitasKmNotificationService
{
    /**
     * Notifikasi saat anggota pertama kali membuat aktivitas KM.
     * Dipakai ketika status awal aktivitas selain Submitted.
     */
    public static function notifyAktivitasBaru(int $idAktivitas): void
    {
        self::kirimNotifikasiAktivitas($idAktivitas, 'baru');
    }

    /**
     * Notifikasi saat anggota mensubmit aktivitas KM.
     * Dipakai saat status aktivitas berubah menjadi Submitted.
     */
    public static function notifyAktivitasDisubmit(int $idAktivitas): void
    {
        self::kirimNotifikasiAktivitas($idAktivitas, 'disubmit');
    }

    private static function kirimNotifikasiAktivitas(int $idAktivitas, string $jenisEvent): void
    {
        $aktivitas = self::findAktivitas($idAktivitas);

        if (!$aktivitas) {
            return;
        }

        $namaAnggota = trim((string) ($aktivitas->nama_dosen ?? $aktivitas->username ?? 'Anggota'));
        $namaLab = trim((string) ($aktivitas->nama_lab ?? 'Lab Riset'));
        $kategoriKm = trim((string) ($aktivitas->kategori_km ?? '-'));
        $subKategoriKm = trim((string) ($aktivitas->sub_kategori_km ?? '-'));
        $judulAktivitas = trim((string) ($aktivitas->judul_aktivitas ?? '-'));
        $statusLabel = self::formatStatus($aktivitas->status_progress ?? null);
        $periodeKegiatan = self::formatPeriode(
            $aktivitas->tanggal_mulai ?? null,
            $aktivitas->tanggal_selesai ?? null
        );
        $waktuInput = self::formatTimestamp($aktivitas->created_at ?? null);

        $tahun = self::resolveYear(
            $aktivitas->tanggal_mulai ?? null,
            $aktivitas->created_at ?? null
        );

        $aksi = $jenisEvent === 'disubmit'
            ? 'mensubmit'
            : 'menambahkan';

        $jenisNotifikasi = $jenisEvent === 'disubmit'
            ? 'aktivitas_km_disubmit'
            : 'aktivitas_km_baru';

        $judulUntukLab = $jenisEvent === 'disubmit'
            ? 'Aktivitas KM disubmit anggota'
            : 'Aktivitas KM baru dari anggota';

        $judulUntukKk = $jenisEvent === 'disubmit'
            ? 'Aktivitas KM disubmit di Lab Riset'
            : 'Aktivitas KM baru di Lab Riset';

        $pesanDasar = $namaAnggota
            . ' baru saja ' . $aksi . ' aktivitas KM.'
            . ' Kategori KM: ' . $kategoriKm . '.'
            . ' Sub kategori: ' . $subKategoriKm . '.'
            . ' Judul aktivitas: ' . $judulAktivitas . '.'
            . ' Status: ' . $statusLabel . '.'
            . ' Periode kegiatan: ' . $periodeKegiatan . '.'
            . ' Waktu input: ' . $waktuInput . '.';

        /*
        |------------------------------------------------------------------
        | Kirim ke Ketua Lab dari Lab anggota terkait
        |------------------------------------------------------------------
        */
        $ketuaLabIds = DB::table('users')
            ->where('role', 'Ketua Lab')
            ->where('id_lab', $aktivitas->id_lab)
            ->pluck('id_user');

        foreach ($ketuaLabIds as $idUserKetuaLab) {
            self::createOnce([
                'id_user' => $idUserKetuaLab,
                'id_km_lab' => $aktivitas->id_km_lab,
                'id_km_anggota' => $aktivitas->id_km_anggota,
                'jenis_notifikasi' => $jenisNotifikasi,
                'judul' => $judulUntukLab,
                'pesan' => $pesanDasar,
                'kategori_km' => $kategoriKm,
                'sub_kategori_km' => $subKategoriKm,
                'jumlah_km' => 0,
                'tanggal_tenggat' => null,
                'url_tujuan' => '/ketualab/detail-anggota/' . $aktivitas->id_user . '?tahun=' . $tahun,
                'kode_unik' => implode('-', [
                    'aktivitas',
                    $jenisEvent,
                    'lab',
                    $idAktivitas,
                    'user',
                    $idUserKetuaLab,
                ]),
            ]);
        }

        /*
        |------------------------------------------------------------------
        | Kirim ke Ketua KK pemilik Lab terkait
        |------------------------------------------------------------------
        */
        if (!empty($aktivitas->id_kk)) {
            $ketuaKkIds = DB::table('users as u')
                ->join('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
                ->where('u.role', 'Ketua KK')
                ->where('d.id_kk', $aktivitas->id_kk)
                ->distinct()
                ->pluck('u.id_user');

            $pesanKetuaKk = 'Lab Riset: ' . $namaLab . '. ' . $pesanDasar;

            foreach ($ketuaKkIds as $idUserKetuaKk) {
                self::createOnce([
                    'id_user' => $idUserKetuaKk,
                    'id_km_lab' => $aktivitas->id_km_lab,
                    'id_km_anggota' => $aktivitas->id_km_anggota,
                    'jenis_notifikasi' => $jenisNotifikasi,
                    'judul' => $judulUntukKk,
                    'pesan' => $pesanKetuaKk,
                    'kategori_km' => $kategoriKm,
                    'sub_kategori_km' => $subKategoriKm,
                    'jumlah_km' => 0,
                    'tanggal_tenggat' => null,
                    'url_tujuan' => '/ketuakk/monitoring-anggota-kk/' . $aktivitas->id_user
                        . '?tahun=' . $tahun . '&periode=tahun',
                    'kode_unik' => implode('-', [
                        'aktivitas',
                        $jenisEvent,
                        'kk',
                        $idAktivitas,
                        'user',
                        $idUserKetuaKk,
                    ]),
                ]);
            }
        }
    }

    private static function findAktivitas(int $idAktivitas): ?object
    {
        return DB::table('aktivitas_km as ak')
            ->leftJoin('users as u', 'ak.id_user', '=', 'u.id_user')
            ->leftJoin('dosen as d', 'u.id_dosen', '=', 'd.id_dosen')
            ->leftJoin('laboratorium_riset as lr', 'ak.id_lab', '=', 'lr.id_lab')
            ->leftJoin('km_anggota as ka', 'ak.id_km_anggota', '=', 'ka.id_km_anggota')
            ->where('ak.id_aktivitas', $idAktivitas)
            ->select(
                'ak.id_aktivitas',
                'ak.id_user',
                'ak.id_lab',
                'ak.id_km_anggota',
                'ak.kategori_km',
                'ak.sub_kategori_km',
                'ak.judul_aktivitas',
                'ak.status_progress',
                'ak.tanggal_mulai',
                'ak.tanggal_selesai',
                'ak.created_at',
                'u.username',
                'd.nama_dosen',
                'lr.nama_lab',
                'lr.id_kk',
                'ka.id_km_lab'
            )
            ->first();
    }

    private static function formatStatus(?string $status): string
    {
        return match (trim((string) $status)) {
            'On Progress' => 'Sedang dikerjakan',
            'Submitted' => 'Disubmit',
            'Accepted' => 'Diterima',
            'Rejected' => 'Ditolak',
            default => trim((string) $status) !== '' ? trim((string) $status) : 'Belum diketahui',
        };
    }

    private static function formatPeriode(?string $tanggalMulai, ?string $tanggalSelesai): string
    {
        if (empty($tanggalMulai) && empty($tanggalSelesai)) {
            return '-';
        }

        if (empty($tanggalSelesai)) {
            return Carbon::parse($tanggalMulai)->format('d/m/Y');
        }

        if (empty($tanggalMulai)) {
            return Carbon::parse($tanggalSelesai)->format('d/m/Y');
        }

        $mulai = Carbon::parse($tanggalMulai)->format('d/m/Y');
        $selesai = Carbon::parse($tanggalSelesai)->format('d/m/Y');

        return $mulai === $selesai ? $mulai : $mulai . ' s.d. ' . $selesai;
    }

    private static function formatTimestamp(?string $timestamp): string
    {
        if (empty($timestamp)) {
            return now()->format('d/m/Y H:i');
        }

        return Carbon::parse($timestamp)->format('d/m/Y H:i');
    }

    private static function resolveYear(?string $tanggalMulai, ?string $createdAt): int
    {
        if (!empty($tanggalMulai)) {
            return (int) Carbon::parse($tanggalMulai)->year;
        }

        if (!empty($createdAt)) {
            return (int) Carbon::parse($createdAt)->year;
        }

        return now()->year;
    }

    private static function createOnce(array $attributes): void
    {
        Notifikasi::firstOrCreate(
            ['kode_unik' => $attributes['kode_unik']],
            $attributes
        );
    }
}
