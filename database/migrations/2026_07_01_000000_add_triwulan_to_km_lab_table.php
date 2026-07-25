<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('km_lab', 'triwulan_1')) {
            Schema::table('km_lab', function (Blueprint $table) {
                $table->unsignedInteger('triwulan_1')->default(0);
            });
        }

        if (!Schema::hasColumn('km_lab', 'triwulan_2')) {
            Schema::table('km_lab', function (Blueprint $table) {
                $table->unsignedInteger('triwulan_2')->default(0);
            });
        }

        if (!Schema::hasColumn('km_lab', 'triwulan_3')) {
            Schema::table('km_lab', function (Blueprint $table) {
                $table->unsignedInteger('triwulan_3')->default(0);
            });
        }

        if (!Schema::hasColumn('km_lab', 'triwulan_4')) {
            Schema::table('km_lab', function (Blueprint $table) {
                $table->unsignedInteger('triwulan_4')->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('km_lab', 'triwulan_1')) {
            Schema::table('km_lab', function (Blueprint $table) {
                $table->dropColumn('triwulan_1');
            });
        }

        if (Schema::hasColumn('km_lab', 'triwulan_2')) {
            Schema::table('km_lab', function (Blueprint $table) {
                $table->dropColumn('triwulan_2');
            });
        }

        if (Schema::hasColumn('km_lab', 'triwulan_3')) {
            Schema::table('km_lab', function (Blueprint $table) {
                $table->dropColumn('triwulan_3');
            });
        }

        if (Schema::hasColumn('km_lab', 'triwulan_4')) {
            Schema::table('km_lab', function (Blueprint $table) {
                $table->dropColumn('triwulan_4');
            });
        }
    }
};