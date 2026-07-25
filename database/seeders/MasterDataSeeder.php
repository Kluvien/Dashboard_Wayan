<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Kelompok Keahlian
        |--------------------------------------------------------------------------
        */
        DB::table('kelompok_keahlian')->updateOrInsert(
            ['id_kk' => 1],
            [
                'nama_kk' => 'Kelompok Keahlian Sistem Informasi',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Laboratorium Riset
        |--------------------------------------------------------------------------
        */
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
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Data Dosen Dummy
        |--------------------------------------------------------------------------
        */
        $dosens = [
            [
                'id_dosen' => 1,
                'id_kk' => 1,
                'id_lab' => null,
                'nama_dosen' => 'Ketua KK Sistem Informasi',
                'nidn' => '0000000000',
                'email' => 'ketuakk@example.com',
                'jad' => 'LK',
            ],

            [
                'id_dosen' => 2,
                'id_kk' => 1,
                'id_lab' => 1,
                'nama_dosen' => 'Ketua Lab BMS',
                'nidn' => '0000000001',
                'email' => 'ketualab.bms@example.com',
                'jad' => 'L',
            ],
            [
                'id_dosen' => 3,
                'id_kk' => 1,
                'id_lab' => 1,
                'nama_dosen' => 'Anggota Lab BMS',
                'nidn' => '0000000002',
                'email' => 'anggota.bms@example.com',
                'jad' => 'AA',
            ],

            [
                'id_dosen' => 4,
                'id_kk' => 1,
                'id_lab' => 2,
                'nama_dosen' => 'Ketua Lab PMDT',
                'nidn' => '0000000003',
                'email' => 'ketualab.pmdt@example.com',
                'jad' => 'L',
            ],
            [
                'id_dosen' => 5,
                'id_kk' => 1,
                'id_lab' => 2,
                'nama_dosen' => 'Anggota Lab PMDT',
                'nidn' => '0000000004',
                'email' => 'anggota.pmdt@example.com',
                'jad' => 'AA',
            ],

            [
                'id_dosen' => 6,
                'id_kk' => 1,
                'id_lab' => 3,
                'nama_dosen' => 'Ketua Lab ESS',
                'nidn' => '0000000005',
                'email' => 'ketualab.ess@example.com',
                'jad' => 'L',
            ],
            [
                'id_dosen' => 7,
                'id_kk' => 1,
                'id_lab' => 3,
                'nama_dosen' => 'Anggota Lab ESS',
                'nidn' => '0000000006',
                'email' => 'anggota.ess@example.com',
                'jad' => 'AA',
            ],

            [
                'id_dosen' => 8,
                'id_kk' => 1,
                'id_lab' => 4,
                'nama_dosen' => 'Ketua Lab ReaLISM',
                'nidn' => '0000000007',
                'email' => 'ketualab.realism@example.com',
                'jad' => 'L',
            ],
            [
                'id_dosen' => 9,
                'id_kk' => 1,
                'id_lab' => 4,
                'nama_dosen' => 'Anggota Lab ReaLISM',
                'nidn' => '0000000008',
                'email' => 'anggota.realism@example.com',
                'jad' => 'AA',
            ],

            [
                'id_dosen' => 10,
                'id_kk' => 1,
                'id_lab' => 5,
                'nama_dosen' => 'Ketua Lab DMI',
                'nidn' => '0000000009',
                'email' => 'ketualab.dmi@example.com',
                'jad' => 'L',
            ],
            [
                'id_dosen' => 11,
                'id_kk' => 1,
                'id_lab' => 5,
                'nama_dosen' => 'Anggota Lab DMI',
                'nidn' => '0000000010',
                'email' => 'anggota.dmi@example.com',
                'jad' => 'AA',
            ],
        ];

        foreach ($dosens as $dosen) {
            $data = [
                'id_kk' => $dosen['id_kk'],
                'id_lab' => $dosen['id_lab'],
                'nama_dosen' => $dosen['nama_dosen'],
                'nidn' => $dosen['nidn'],
                'email' => $dosen['email'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('dosen', 'jad')) {
                $data['jad'] = $dosen['jad'];
            }

            DB::table('dosen')->updateOrInsert(
                ['id_dosen' => $dosen['id_dosen']],
                $data
            );
        }
    }
}
