<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDosensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dosen', function (Blueprint $table) {
            $table->id('id_dosen');
            $table->unsignedBigInteger('id_kk')->nullable();
            $table->unsignedBigInteger('id_lab')->nullable();
            $table->string('nama_dosen');
            $table->string('nidn')->unique();
            $table->string('email')->unique();
            $table->timestamps();

            // Relasi
            $table->foreign('id_kk')->references('id_kk')->on('kelompok_keahlian')->onDelete('set null');
            $table->foreign('id_lab')->references('id_lab')->on('laboratorium_riset')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dosens');
    }
}
