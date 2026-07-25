<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $users = [
            [
                'username' => 'ketuakk',
                'role' => 'Ketua KK',
                'id_lab' => null,
                'id_dosen' => 1,
            ],

            /*
            |--------------------------------------------------------------------------
            | Akun default lama
            |--------------------------------------------------------------------------
            */
            [
                'username' => 'ketualab',
                'role' => 'Ketua Lab',
                'id_lab' => 1,
                'id_dosen' => 2,
            ],
            [
                'username' => 'anggota',
                'role' => 'Anggota',
                'id_lab' => 1,
                'id_dosen' => 3,
            ],

            /*
            |--------------------------------------------------------------------------
            | Akun Ketua Lab 1 - 5
            |--------------------------------------------------------------------------
            */
            [
                'username' => 'ketualab1',
                'role' => 'Ketua Lab',
                'id_lab' => 1,
                'id_dosen' => 2,
            ],
            [
                'username' => 'ketualab2',
                'role' => 'Ketua Lab',
                'id_lab' => 2,
                'id_dosen' => 4,
            ],
            [
                'username' => 'ketualab3',
                'role' => 'Ketua Lab',
                'id_lab' => 3,
                'id_dosen' => 6,
            ],
            [
                'username' => 'ketualab4',
                'role' => 'Ketua Lab',
                'id_lab' => 4,
                'id_dosen' => 8,
            ],
            [
                'username' => 'ketualab5',
                'role' => 'Ketua Lab',
                'id_lab' => 5,
                'id_dosen' => 10,
            ],

            /*
            |--------------------------------------------------------------------------
            | Akun Anggota 1 - 5
            |--------------------------------------------------------------------------
            */
            [
                'username' => 'anggota1',
                'role' => 'Anggota',
                'id_lab' => 1,
                'id_dosen' => 3,
            ],
            [
                'username' => 'anggota2',
                'role' => 'Anggota',
                'id_lab' => 2,
                'id_dosen' => 5,
            ],
            [
                'username' => 'anggota3',
                'role' => 'Anggota',
                'id_lab' => 3,
                'id_dosen' => 7,
            ],
            [
                'username' => 'anggota4',
                'role' => 'Anggota',
                'id_lab' => 4,
                'id_dosen' => 9,
            ],
            [
                'username' => 'anggota5',
                'role' => 'Anggota',
                'id_lab' => 5,
                'id_dosen' => 11,
            ],
        ];

        foreach ($users as $user) {
            $data = [
                'username' => $user['username'],
                'password' => Hash::make('password123'),
                'role' => $user['role'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('users', 'id_lab')) {
                $data['id_lab'] = $user['id_lab'];
            }

            if (Schema::hasColumn('users', 'id_dosen')) {
                $data['id_dosen'] = $user['id_dosen'];
            }

            DB::table('users')->updateOrInsert(
                ['username' => $user['username']],
                $data
            );
        }
    }
}
