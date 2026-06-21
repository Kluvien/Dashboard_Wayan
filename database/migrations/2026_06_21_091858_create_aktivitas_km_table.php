<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aktivitas_km', function (Blueprint $table) {
            $table->id('id_aktivitas');

            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_lab')->nullable();

            $table->string('kategori_km');
            $table->string('judul_aktivitas');
            $table->text('deskripsi_singkat')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');

            $table->timestamps();

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktivitas_km');
    }
};