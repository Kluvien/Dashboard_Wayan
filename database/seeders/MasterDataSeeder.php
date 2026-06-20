<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kelompok_keahlian')->updateOrInsert(
            ['id_kk' => 1],
            [
                'nama_kk' => 'Kelompok Keahlian Sistem Informasi',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $labs = [
            [
                'id_lab' => 1,
                'id_kk' => 1,
                'nama_lab' => 'BMS - Business Modelling & Simulation',
            ],
            [
                'id_lab' => 2,
                'id_kk' => 1,
                'nama_lab' => 'PMDT - Project Management & Digital Talent',
            ],
            [
                'id_lab' => 3,
                'id_kk' => 1,
                'nama_lab' => 'ESS - Enterprise System and Solution',
            ],
            [
                'id_lab' => 4,
                'id_kk' => 1,
                'nama_lab' => 'ReaLISM - E-Logistic and Supply Chain',
            ],
            [
                'id_lab' => 5,
                'id_kk' => 1,
                'nama_lab' => 'DMI - Digital Marketing and Intelligence',
            ],
        ];

        foreach ($labs as $lab) {
            DB::table('laboratorium_riset')->updateOrInsert(
                ['id_lab' => $lab['id_lab']],
                [
                    'id_kk' => $lab['id_kk'],
                    'nama_lab' => $lab['nama_lab'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}