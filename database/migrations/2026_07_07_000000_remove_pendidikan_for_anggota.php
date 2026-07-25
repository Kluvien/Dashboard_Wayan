<?php

use Illuminate\Database\Migrations\Migration;

/*
|--------------------------------------------------------------------------
| Compatibility migration
|--------------------------------------------------------------------------
| File ini dipertahankan agar riwayat migration lama yang sudah tercatat
| pada database tetap dapat dikenali oleh Laravel. Pembersihan kategori
| yang tidak didukung sekarang ditangani oleh migration global berikutnya.
*/
return new class extends Migration
{
    public function up(): void
    {
        // No-op.
    }

    public function down(): void
    {
        // No-op.
    }
};
