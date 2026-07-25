<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Menghapus seluruh data kategori KM Pendidikan dari seluruh sistem:
     * Ketua KK, Ketua Lab Riset, dan Anggota.
     *
     * Penghapusan meliputi target, penurunan ke lab, pembagian ke anggota,
     * aktivitas, realisasi legacy, notifikasi, serta berkas bukti aktivitas.
     */
    public function up(): void
    {
        if (
            ! Schema::hasTable('target_km') ||
            ! Schema::hasTable('km_lab') ||
            ! Schema::hasTable('km_anggota') ||
            ! Schema::hasTable('aktivitas_km')
        ) {
            return;
        }

        $kategoriDihapus = 'Pendidikan';

        /*
        |--------------------------------------------------------------------------
        | Hapus backup database lama di dalam folder project karena masih dapat
        | menyimpan rekaman kategori yang telah dihentikan.
        |--------------------------------------------------------------------------
        */
        foreach ([
            database_path('backup_before_delete_old_accounts.sqlite'),
            database_path('database-before-remove-pendidikan-anggota.sqlite'),
        ] as $backupDatabase) {
            File::delete($backupDatabase);
        }

        /*
        |--------------------------------------------------------------------------
        | Kumpulkan ID seluruh data yang saling terkait lebih dahulu.
        |--------------------------------------------------------------------------
        */
        $idTarget = DB::table('target_km')
            ->where('kategori_km', $kategoriDihapus)
            ->pluck('id_target')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $kmLabQuery = DB::table('km_lab')
            ->where('kategori_km', $kategoriDihapus);

        if (! empty($idTarget) && Schema::hasColumn('km_lab', 'id_target')) {
            $kmLabQuery->orWhereIn('id_target', $idTarget);
        }

        $idKmLab = $kmLabQuery
            ->pluck('id_km_lab')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $idKmAnggota = empty($idKmLab)
            ? []
            : DB::table('km_anggota')
                ->whereIn('id_km_lab', $idKmLab)
                ->pluck('id_km_anggota')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

        $kolomBukti = array_values(array_filter([
            Schema::hasColumn('aktivitas_km', 'bukti_file_path') ? 'bukti_file_path' : null,
            Schema::hasColumn('aktivitas_km', 'bukti_pdf_path') ? 'bukti_pdf_path' : null,
        ]));

        $aktivitasQuery = DB::table('aktivitas_km')
            ->where('kategori_km', $kategoriDihapus);

        if (! empty($idKmAnggota) && Schema::hasColumn('aktivitas_km', 'id_km_anggota')) {
            $aktivitasQuery->orWhereIn('id_km_anggota', $idKmAnggota);
        }

        $aktivitasPendidikan = $aktivitasQuery
            ->select(array_merge(['id_aktivitas'], $kolomBukti))
            ->get();

        $idAktivitas = $aktivitasPendidikan
            ->pluck('id_aktivitas')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Bersihkan berkas bukti fisik.
        |--------------------------------------------------------------------------
        */
        foreach ($aktivitasPendidikan as $aktivitas) {
            $paths = collect([
                $aktivitas->bukti_file_path ?? null,
                $aktivitas->bukti_pdf_path ?? null,
            ])
                ->filter()
                ->unique()
                ->values()
                ->all();

            foreach ($paths as $path) {
                Storage::disk('public')->delete($path);
                Storage::disk('local')->delete($path);
            }
        }

        DB::transaction(function () use (
            $kategoriDihapus,
            $idTarget,
            $idKmLab,
            $idKmAnggota,
            $idAktivitas
        ): void {
            /*
            |----------------------------------------------------------------------
            | Notifikasi dihapus terlebih dahulu karena dapat merujuk ke KM Lab
            | maupun KM Anggota yang akan dihapus setelahnya.
            |----------------------------------------------------------------------
            */
            if (Schema::hasTable('notifikasi')) {
                DB::table('notifikasi')
                    ->where(function ($query) use ($kategoriDihapus, $idKmLab, $idKmAnggota): void {
                        $query->where('kategori_km', $kategoriDihapus);

                        if (! empty($idKmLab)) {
                            $query->orWhereIn('id_km_lab', $idKmLab);
                        }

                        if (! empty($idKmAnggota)) {
                            $query->orWhereIn('id_km_anggota', $idKmAnggota);
                        }
                    })
                    ->delete();
            }

            /*
            |----------------------------------------------------------------------
            | Aktivitas dan realisasi legacy.
            |----------------------------------------------------------------------
            */
            if (! empty($idAktivitas)) {
                DB::table('aktivitas_km')
                    ->whereIn('id_aktivitas', $idAktivitas)
                    ->delete();
            }

            if (Schema::hasTable('realisasi_km') && ! empty($idTarget)) {
                DB::table('realisasi_km')
                    ->whereIn('id_target', $idTarget)
                    ->delete();
            }

            /*
            |----------------------------------------------------------------------
            | Rantai pembagian KM: anggota -> lab -> target KK.
            |----------------------------------------------------------------------
            */
            if (! empty($idKmAnggota)) {
                DB::table('km_anggota')
                    ->whereIn('id_km_anggota', $idKmAnggota)
                    ->delete();
            }

            if (! empty($idKmLab)) {
                DB::table('km_lab')
                    ->whereIn('id_km_lab', $idKmLab)
                    ->delete();
            }

            if (! empty($idTarget)) {
                DB::table('target_km')
                    ->whereIn('id_target', $idTarget)
                    ->delete();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Lindungi database SQLite agar kategori yang telah dihapus tidak bisa
        | dimasukkan kembali melalui query manual di luar formulir aplikasi.
        |--------------------------------------------------------------------------
        */
        if (DB::connection()->getDriverName() === 'sqlite') {
            $triggers = [
                'prevent_pendidikan_target_km_insert' => "
                    CREATE TRIGGER IF NOT EXISTS prevent_pendidikan_target_km_insert
                    BEFORE INSERT ON target_km
                    WHEN NEW.kategori_km = 'Pendidikan'
                    BEGIN
                        SELECT RAISE(ABORT, 'Kategori KM Pendidikan tidak didukung.');
                    END;
                ",
                'prevent_pendidikan_target_km_update' => "
                    CREATE TRIGGER IF NOT EXISTS prevent_pendidikan_target_km_update
                    BEFORE UPDATE OF kategori_km ON target_km
                    WHEN NEW.kategori_km = 'Pendidikan'
                    BEGIN
                        SELECT RAISE(ABORT, 'Kategori KM Pendidikan tidak didukung.');
                    END;
                ",
                'prevent_pendidikan_km_lab_insert' => "
                    CREATE TRIGGER IF NOT EXISTS prevent_pendidikan_km_lab_insert
                    BEFORE INSERT ON km_lab
                    WHEN NEW.kategori_km = 'Pendidikan'
                    BEGIN
                        SELECT RAISE(ABORT, 'Kategori KM Pendidikan tidak didukung.');
                    END;
                ",
                'prevent_pendidikan_km_lab_update' => "
                    CREATE TRIGGER IF NOT EXISTS prevent_pendidikan_km_lab_update
                    BEFORE UPDATE OF kategori_km ON km_lab
                    WHEN NEW.kategori_km = 'Pendidikan'
                    BEGIN
                        SELECT RAISE(ABORT, 'Kategori KM Pendidikan tidak didukung.');
                    END;
                ",
                'prevent_pendidikan_aktivitas_insert' => "
                    CREATE TRIGGER IF NOT EXISTS prevent_pendidikan_aktivitas_insert
                    BEFORE INSERT ON aktivitas_km
                    WHEN NEW.kategori_km = 'Pendidikan'
                    BEGIN
                        SELECT RAISE(ABORT, 'Kategori KM Pendidikan tidak didukung.');
                    END;
                ",
                'prevent_pendidikan_aktivitas_update' => "
                    CREATE TRIGGER IF NOT EXISTS prevent_pendidikan_aktivitas_update
                    BEFORE UPDATE OF kategori_km ON aktivitas_km
                    WHEN NEW.kategori_km = 'Pendidikan'
                    BEGIN
                        SELECT RAISE(ABORT, 'Kategori KM Pendidikan tidak didukung.');
                    END;
                ",
            ];

            foreach ($triggers as $triggerSql) {
                DB::unprepared($triggerSql);
            }
        }
    }

    /**
     * Penghapusan kategori ini bersifat permanen dan tidak dapat dipulihkan
     * otomatis melalui rollback.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        foreach ([
            'prevent_pendidikan_target_km_insert',
            'prevent_pendidikan_target_km_update',
            'prevent_pendidikan_km_lab_insert',
            'prevent_pendidikan_km_lab_update',
            'prevent_pendidikan_aktivitas_insert',
            'prevent_pendidikan_aktivitas_update',
        ] as $trigger) {
            DB::unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }
    }
};
