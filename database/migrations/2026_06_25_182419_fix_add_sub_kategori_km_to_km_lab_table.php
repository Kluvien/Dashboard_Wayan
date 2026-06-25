<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('km_lab', function (Blueprint $table) {
            if (!Schema::hasColumn('km_lab', 'sub_kategori_km')) {
                $table->string('sub_kategori_km')->nullable()->after('kategori_km');
            }
        });
    }

    public function down(): void
    {
        Schema::table('km_lab', function (Blueprint $table) {
            if (Schema::hasColumn('km_lab', 'sub_kategori_km')) {
                $table->dropColumn('sub_kategori_km');
            }
        });
    }
};
