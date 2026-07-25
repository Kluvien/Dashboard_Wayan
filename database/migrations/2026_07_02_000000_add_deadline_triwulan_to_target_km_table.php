<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $deadlineColumns = [
        'tanggal_mulai_tw1',
        'tanggal_selesai_tw1',
        'tanggal_mulai_tw2',
        'tanggal_selesai_tw2',
        'tanggal_mulai_tw3',
        'tanggal_selesai_tw3',
        'tanggal_mulai_tw4',
        'tanggal_selesai_tw4',
    ];

    public function up(): void
    {
        foreach ($this->deadlineColumns as $column) {
            if (!Schema::hasColumn('target_km', $column)) {
                Schema::table('target_km', function (Blueprint $table) use ($column) {
                    $table->date($column)->nullable();
                });
            }
        }

        /*
        | Mengisi target lama dengan periode standar triwulan.
        | Data lama tetap bisa dipakai tanpa harus diisi ulang semua.
        */
        $targets = DB::table('target_km as tk')
            ->join(
                'kontrak_manajemen as km',
                'tk.id_km',
                '=',
                'km.id_km'
            )
            ->select(
                'tk.id_target',
                'tk.triwulan_1',
                'tk.triwulan_2',
                'tk.triwulan_3',
                'tk.triwulan_4',
                'tk.tanggal_mulai_tw1',
                'tk.tanggal_selesai_tw1',
                'tk.tanggal_mulai_tw2',
                'tk.tanggal_selesai_tw2',
                'tk.tanggal_mulai_tw3',
                'tk.tanggal_selesai_tw3',
                'tk.tanggal_mulai_tw4',
                'tk.tanggal_selesai_tw4',
                'km.tahun_km'
            )
            ->get();

        foreach ($targets as $target) {
            $updates = [];

            for ($triwulan = 1; $triwulan <= 4; $triwulan++) {
                $jumlahTarget = (int) $target->{'triwulan_' . $triwulan};

                if ($jumlahTarget <= 0) {
                    continue;
                }

                $bulanMulai = (($triwulan - 1) * 3) + 1;
                $bulanSelesai = $bulanMulai + 2;

                $tanggalMulai = Carbon::create(
                    (int) $target->tahun_km,
                    $bulanMulai,
                    1
                )->startOfMonth();

                $tanggalSelesai = Carbon::create(
                    (int) $target->tahun_km,
                    $bulanSelesai,
                    1
                )->endOfMonth();

                $kolomMulai = 'tanggal_mulai_tw' . $triwulan;
                $kolomSelesai = 'tanggal_selesai_tw' . $triwulan;

                if (empty($target->{$kolomMulai})) {
                    $updates[$kolomMulai] = $tanggalMulai->toDateString();
                }

                if (empty($target->{$kolomSelesai})) {
                    $updates[$kolomSelesai] = $tanggalSelesai->toDateString();
                }
            }

            if (!empty($updates)) {
                $updates['updated_at'] = now();

                DB::table('target_km')
                    ->where('id_target', $target->id_target)
                    ->update($updates);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->deadlineColumns as $column) {
            if (Schema::hasColumn('target_km', $column)) {
                Schema::table('target_km', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};