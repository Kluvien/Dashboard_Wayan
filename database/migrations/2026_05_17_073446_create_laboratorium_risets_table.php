<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLaboratoriumRisetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laboratorium_riset', function (Blueprint $table) {
            $table->id('id_lab');
            $table->unsignedBigInteger('id_kk');
            $table->string('nama_lab');
            $table->timestamps();

            // Relasi
            $table->foreign('id_kk')->references('id_kk')->on('kelompok_keahlian')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('laboratorium_risets');
    }
}
