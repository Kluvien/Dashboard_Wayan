<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRealisasiKmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('realisasi_km', function (Blueprint $table) {
            $table->id('id_realisasi');
            $table->unsignedBigInteger('id_km');
            $table->string('indikator');
            $table->integer('realisasi');
            $table->timestamps();

            $table->foreign('id_km')->references('id_km')->on('kontrak_manajemen')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('realisasi_km');
    }
}
