<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTargetKmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('target_km', function (Blueprint $table) {
            $table->id('id_target');
            $table->unsignedBigInteger('id_km');
            $table->string('indikator');
            $table->integer('target');
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
        Schema::dropIfExists('target_km');
    }
}
