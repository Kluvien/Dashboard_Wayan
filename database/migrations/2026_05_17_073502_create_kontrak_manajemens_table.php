<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKontrakManajemensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kontrak_manajemen', function (Blueprint $table) {
            $table->id('id_km');
            $table->unsignedBigInteger('id_dosen');
            $table->integer('tahun_km');
            $table->string('status_km'); // misal: Draft, Aktif, Selesai
            $table->timestamps();

            $table->foreign('id_dosen')->references('id_dosen')->on('dosen')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kontrak_manajemens');
    }
}
