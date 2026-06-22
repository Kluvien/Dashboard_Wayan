<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TargetKmSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan user anggota dan ketualab terhubung ke dosen BMS
        DB::table('users')->where('username', 'anggota')->update([
            'id_dosen' => 1,
            'id_lab' => 1,
            'updated_at' => now(),
        ]);

        DB::table('users')->where('username', 'ketualab')->update([
            'id_dosen' => 1,
            'id_lab' => 1,
            'updated_at' => now(),
        ]);

        // Buat / update kontrak manajemen untuk Dosen BMS 1
        DB::table('kontrak_manajemen')->updateOrInsert(
            [
                'id_dosen' => 1,
                'tahun_km' => 2026,
            ],
            [
                'status_km' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $kontrak = DB::table('kontrak_manajemen')
            ->where('id_dosen', 1)
            ->where('tahun_km', 2026)
            ->first();

        $targets = [
            'Pendidikan' => 4,
            'Penelitian' => 5,
            'Publikasi' => 2,
            'Pengabdian' => 3,
            'Penunjang' => 2,
        ];

        foreach ($targets as $indikator => $target) {
            DB::table('target_km')->updateOrInsert(
                [
                    'id_km' => $kontrak->id_km,
                    'indikator' => $indikator,
                ],
                [
                    'target' => $target,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}