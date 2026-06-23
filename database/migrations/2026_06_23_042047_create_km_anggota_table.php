<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('km_anggota', function (Blueprint $table) {
            $table->id('id_km_anggota');
            $table->unsignedBigInteger('id_km_lab');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_dosen')->nullable();
            $table->integer('jumlah_km')->default(0);
            $table->timestamps();

            $table->foreign('id_km_lab')
                ->references('id_km_lab')
                ->on('km_lab')
                ->onDelete('cascade');

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('id_dosen')
                ->references('id_dosen')
                ->on('dosen')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('km_anggota');
    }
};