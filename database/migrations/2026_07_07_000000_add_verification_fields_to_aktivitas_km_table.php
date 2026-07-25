<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aktivitas_km', function (Blueprint $table) {
            if (!Schema::hasColumn('aktivitas_km', 'diajukan_pada')) {
                $table->timestamp('diajukan_pada')->nullable();
            }

            if (!Schema::hasColumn('aktivitas_km', 'diverifikasi_pada')) {
                $table->timestamp('diverifikasi_pada')->nullable();
            }

            if (!Schema::hasColumn('aktivitas_km', 'diverifikasi_oleh')) {
                $table->unsignedBigInteger('diverifikasi_oleh')->nullable()->index();
            }

            if (!Schema::hasColumn('aktivitas_km', 'catatan_verifikasi')) {
                $table->text('catatan_verifikasi')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('aktivitas_km', function (Blueprint $table) {
            $columns = [
                'diajukan_pada',
                'diverifikasi_pada',
                'diverifikasi_oleh',
                'catatan_verifikasi',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('aktivitas_km', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
