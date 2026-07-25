<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id('id_notifikasi');
            $table->unsignedBigInteger('id_user');

            $table->unsignedBigInteger('id_km_lab')->nullable();
            $table->unsignedBigInteger('id_km_anggota')->nullable();

            $table->string('jenis_notifikasi', 60);
            $table->string('judul', 255);
            $table->text('pesan');

            $table->string('kategori_km', 100)->nullable();
            $table->string('sub_kategori_km', 255)->nullable();
            $table->unsignedInteger('jumlah_km')->default(0);
            $table->date('tanggal_tenggat')->nullable();

            $table->string('url_tujuan', 500)->nullable();
            $table->string('kode_unik', 255)->unique();
            $table->timestamp('dibaca_pada')->nullable();
            $table->timestamps();

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->onDelete('cascade');

            $table->index(['id_user', 'dibaca_pada']);
            $table->index(['id_km_lab', 'jenis_notifikasi']);
            $table->index(['id_km_anggota', 'jenis_notifikasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
