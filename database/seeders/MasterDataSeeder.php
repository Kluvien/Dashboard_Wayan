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

        $dosens = [
            [
                'id_dosen' => 1,
                'id_kk' => 1,
                'id_lab' => 1,
                'nama_dosen' => 'Dosen BMS 1',
                'nidn' => '0000000001',
                'email' => 'dosen.bms1@example.com',
            ],
            [
                'id_dosen' => 2,
                'id_kk' => 1,
                'id_lab' => 2,
                'nama_dosen' => 'Dosen PMDT 1',
                'nidn' => '0000000002',
                'email' => 'dosen.pmdt1@example.com',
            ],
            [
                'id_dosen' => 3,
                'id_kk' => 1,
                'id_lab' => 3,
                'nama_dosen' => 'Dosen ESS 1',
                'nidn' => '0000000003',
                'email' => 'dosen.ess1@example.com',
            ],
            [
                'id_dosen' => 4,
                'id_kk' => 1,
                'id_lab' => 4,
                'nama_dosen' => 'Dosen ReaLISM 1',
                'nidn' => '0000000004',
                'email' => 'dosen.realism1@example.com',
            ],
            [
                'id_dosen' => 5,
                'id_kk' => 1,
                'id_lab' => 5,
                'nama_dosen' => 'Dosen DMI 1',
                'nidn' => '0000000005',
                'email' => 'dosen.dmi1@example.com',
            ],
        ];

        foreach ($dosens as $dosen) {
        DB::table('dosen')->updateOrInsert(
                ['id_dosen' => $dosen['id_dosen']],
                [
                    'id_kk' => $dosen['id_kk'],
                    'id_lab' => $dosen['id_lab'],
                    'nama_dosen' => $dosen['nama_dosen'],
                    'nidn' => $dosen['nidn'],
                    'email' => $dosen['email'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}