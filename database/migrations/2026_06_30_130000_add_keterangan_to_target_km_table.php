<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('target_km', function (Blueprint $table) {
            $table->text('keterangan')->nullable()->after('indikator');
        });
    }

    public function down(): void
    {
        Schema::table('target_km', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};