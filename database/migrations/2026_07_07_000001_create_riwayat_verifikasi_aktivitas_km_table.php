<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('riwayat_verifikasi_aktivitas_km')) {
            Schema::create('riwayat_verifikasi_aktivitas_km', function (Blueprint $table) {
                $table->increments('id_riwayat');
                $table->unsignedInteger('id_aktivitas');
                $table->unsignedInteger('id_user_anggota');
                $table->unsignedInteger('id_lab');
                $table->unsignedInteger('id_verifikator')->nullable();
                $table->string('keputusan', 20);
                $table->text('catatan_verifikasi')->nullable();
                $table->timestamps();

                $table->index('id_aktivitas');
                $table->index('id_lab');
                $table->index('id_user_anggota');
                $table->index('keputusan');
                $table->unique(['id_aktivitas', 'created_at', 'keputusan'], 'riwayat_verifikasi_aktivitas_waktu_keputusan_unique');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Backfill keputusan yang sudah pernah dilakukan sebelum tabel riwayat
        | ditambahkan. Dengan demikian daftar riwayat tidak kosong untuk data lama
        | yang sudah menyimpan waktu dan pihak verifikasi pada aktivitas_km.
        |--------------------------------------------------------------------------
        */
        if (
            Schema::hasTable('aktivitas_km') &&
            Schema::hasColumn('aktivitas_km', 'diverifikasi_pada') &&
            Schema::hasColumn('aktivitas_km', 'diverifikasi_oleh') &&
            Schema::hasColumn('aktivitas_km', 'catatan_verifikasi')
        ) {
            $aktivitasLama = DB::table('aktivitas_km')
                ->whereIn('status_progress', ['Accepted', 'Rejected'])
                ->whereNotNull('diverifikasi_pada')
                ->select(
                    'id_aktivitas',
                    'id_user',
                    'id_lab',
                    'diverifikasi_oleh',
                    'status_progress',
                    'catatan_verifikasi',
                    'diverifikasi_pada'
                )
                ->get();

            foreach ($aktivitasLama as $aktivitas) {
                DB::table('riwayat_verifikasi_aktivitas_km')->insertOrIgnore([
                    'id_aktivitas' => $aktivitas->id_aktivitas,
                    'id_user_anggota' => $aktivitas->id_user,
                    'id_lab' => $aktivitas->id_lab,
                    'id_verifikator' => $aktivitas->diverifikasi_oleh,
                    'keputusan' => $aktivitas->status_progress,
                    'catatan_verifikasi' => $aktivitas->catatan_verifikasi,
                    'created_at' => $aktivitas->diverifikasi_pada,
                    'updated_at' => $aktivitas->diverifikasi_pada,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_verifikasi_aktivitas_km');
    }
};
