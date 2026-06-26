<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aktivitas_km', function (Blueprint $table) {
            if (!Schema::hasColumn('aktivitas_km', 'id_km_anggota')) {
                $table->unsignedBigInteger('id_km_anggota')->nullable()->after('id_lab');
            }

            if (!Schema::hasColumn('aktivitas_km', 'sub_kategori_km')) {
                $table->string('sub_kategori_km')->nullable()->after('kategori_km');
            }

            if (!Schema::hasColumn('aktivitas_km', 'bukti_link')) {
                $table->string('bukti_link')->nullable()->after('tanggal_selesai');
            }

            if (!Schema::hasColumn('aktivitas_km', 'status_progress')) {
                $table->string('status_progress')->default('On Progress')->after('bukti_link');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aktivitas_km', function (Blueprint $table) {
            if (Schema::hasColumn('aktivitas_km', 'status_progress')) {
                $table->dropColumn('status_progress');
            }

            if (Schema::hasColumn('aktivitas_km', 'bukti_link')) {
                $table->dropColumn('bukti_link');
            }

            if (Schema::hasColumn('aktivitas_km', 'sub_kategori_km')) {
                $table->dropColumn('sub_kategori_km');
            }

            if (Schema::hasColumn('aktivitas_km', 'id_km_anggota')) {
                $table->dropColumn('id_km_anggota');
            }
        });
    }
};
