<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('km_lab', function (Blueprint $table) {
            $table->unsignedBigInteger('id_target')->nullable()->index()->after('id_km_lab');

            $table->foreign('id_target')
                ->references('id_target')
                ->on('target_km')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('km_lab', function (Blueprint $table) {
            $table->dropForeign(['id_target']);
            $table->dropIndex(['id_target']);
            $table->dropColumn('id_target');
        });
    }
};