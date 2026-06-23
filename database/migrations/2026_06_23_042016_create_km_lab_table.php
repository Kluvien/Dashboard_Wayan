<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('km_lab', function (Blueprint $table) {
            $table->id('id_km_lab');
            $table->unsignedBigInteger('id_lab');
            $table->integer('tahun_km');
            $table->string('kategori_km', 100);
            $table->integer('jumlah_km')->default(0);
            $table->string('status_km', 50)->default('Aktif');
            $table->timestamps();

            $table->foreign('id_lab')
                ->references('id_lab')
                ->on('laboratorium_riset')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('km_lab');
    }
};