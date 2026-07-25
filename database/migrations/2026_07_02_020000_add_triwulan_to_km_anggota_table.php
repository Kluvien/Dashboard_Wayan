<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = [
        'triwulan_1',
        'triwulan_2',
        'triwulan_3',
        'triwulan_4',
    ];

    public function up(): void
    {
        foreach ($this->columns as $column) {
            if (!Schema::hasColumn('km_anggota', $column)) {
                Schema::table('km_anggota', function (Blueprint $table) use ($column) {
                    $table->unsignedInteger($column)->default(0);
                });
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Backfill data assign lama
        |--------------------------------------------------------------------------
        | Data lama yang hanya memiliki jumlah_km akan dibagi proporsional
        | mengikuti pembagian triwulan dari KM Lab.
        */
        $assignments = DB::table('km_anggota as ka')
            ->join('km_lab as kl', 'ka.id_km_lab', '=', 'kl.id_km_lab')
            ->select(
                'ka.id_km_anggota',
                'ka.jumlah_km',
                'ka.triwulan_1 as anggota_tw1',
                'ka.triwulan_2 as anggota_tw2',
                'ka.triwulan_3 as anggota_tw3',
                'ka.triwulan_4 as anggota_tw4',
                'kl.triwulan_1 as lab_tw1',
                'kl.triwulan_2 as lab_tw2',
                'kl.triwulan_3 as lab_tw3',
                'kl.triwulan_4 as lab_tw4'
            )
            ->get();

        foreach ($assignments as $assignment) {
            $sudahAdaPembagian =
                (int) $assignment->anggota_tw1 +
                (int) $assignment->anggota_tw2 +
                (int) $assignment->anggota_tw3 +
                (int) $assignment->anggota_tw4;

            if ($sudahAdaPembagian > 0) {
                continue;
            }

            $distribusi = $this->distribute(
                (int) $assignment->jumlah_km,
                [
                    1 => (int) $assignment->lab_tw1,
                    2 => (int) $assignment->lab_tw2,
                    3 => (int) $assignment->lab_tw3,
                    4 => (int) $assignment->lab_tw4,
                ]
            );

            DB::table('km_anggota')
                ->where('id_km_anggota', $assignment->id_km_anggota)
                ->update([
                    'triwulan_1' => $distribusi[1],
                    'triwulan_2' => $distribusi[2],
                    'triwulan_3' => $distribusi[3],
                    'triwulan_4' => $distribusi[4],
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        foreach ($this->columns as $column) {
            if (Schema::hasColumn('km_anggota', $column)) {
                Schema::table('km_anggota', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    private function distribute(int $jumlahKm, array $bobotTriwulan): array
    {
        $hasil = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
        ];

        if ($jumlahKm <= 0) {
            return $hasil;
        }

        $totalBobot = array_sum($bobotTriwulan);

        /*
        | Fallback bila data KM Lab lama belum punya pembagian triwulan.
        */
        if ($totalBobot <= 0) {
            $base = intdiv($jumlahKm, 4);
            $sisa = $jumlahKm % 4;

            return [
                1 => $base + ($sisa >= 1 ? 1 : 0),
                2 => $base + ($sisa >= 2 ? 1 : 0),
                3 => $base + ($sisa >= 3 ? 1 : 0),
                4 => $base,
            ];
        }

        $pecahan = [];
        $totalSementara = 0;

        foreach ([1, 2, 3, 4] as $triwulan) {
            $nilaiAsli = ($jumlahKm * ($bobotTriwulan[$triwulan] ?? 0)) / $totalBobot;
            $nilaiBulat = (int) floor($nilaiAsli);

            $hasil[$triwulan] = $nilaiBulat;
            $totalSementara += $nilaiBulat;

            $pecahan[$triwulan] = $nilaiAsli - $nilaiBulat;
        }

        $sisaPembagian = $jumlahKm - $totalSementara;

        arsort($pecahan, SORT_NUMERIC);

        foreach (array_keys($pecahan) as $triwulan) {
            if ($sisaPembagian <= 0) {
                break;
            }

            $hasil[$triwulan]++;
            $sisaPembagian--;
        }

        return $hasil;
    }
};