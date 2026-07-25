<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aktivitas_km', function (Blueprint $table) {
            if (!Schema::hasColumn('aktivitas_km', 'bukti_file_path')) {
                $table->string('bukti_file_path')->nullable()->after('bukti_link');
            }

            if (!Schema::hasColumn('aktivitas_km', 'bukti_pdf_path')) {
                $table->string('bukti_pdf_path')->nullable()->after('bukti_file_path');
            }

            if (!Schema::hasColumn('aktivitas_km', 'bukti_file_nama_asli')) {
                $table->string('bukti_file_nama_asli')->nullable()->after('bukti_pdf_path');
            }

            if (!Schema::hasColumn('aktivitas_km', 'bukti_file_mime')) {
                $table->string('bukti_file_mime')->nullable()->after('bukti_file_nama_asli');
            }

            if (!Schema::hasColumn('aktivitas_km', 'bukti_file_size')) {
                $table->unsignedBigInteger('bukti_file_size')->nullable()->after('bukti_file_mime');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aktivitas_km', function (Blueprint $table) {
            foreach ([
                'bukti_file_size',
                'bukti_file_mime',
                'bukti_file_nama_asli',
                'bukti_pdf_path',
                'bukti_file_path',
            ] as $column) {
                if (Schema::hasColumn('aktivitas_km', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
