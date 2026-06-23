<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRealisasiKmsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('realisasi_km', function (Blueprint $table) {
            $table->id('id_realisasi'); // Primary Key

            $table->integer('id_target');
            $table->integer('id_dosen');

            $table->integer('realisasi')->default(0);
            $table->string('status_realisasi')->default('Belum Tercapai');
            $table->timestamps();
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
