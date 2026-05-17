<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Data Kelompok Keahlian
        $id_kk = DB::table('kelompok_keahlian')->insertGetId([
            'nama_kk' => 'Rekayasa Perangkat Lunak',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Buat Data Laboratorium Riset
        $id_lab = DB::table('laboratorium_riset')->insertGetId([
            'id_kk' => $id_kk,
            'nama_lab' => 'Lab Pengembangan Game & Mobile',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Buat 3 Data Dosen
        $dosen_ketua_kk = DB::table('dosen')->insertGetId([
            'id_kk' => $id_kk,
            'id_lab' => null,
            'nama_dosen' => 'Prof. Budi (Ketua KK)',
            'nidn' => '11111111',
            'email' => 'budikk@kampus.ac.id',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $dosen_ketua_lab = DB::table('dosen')->insertGetId([
            'id_kk' => $id_kk,
            'id_lab' => $id_lab,
            'nama_dosen' => 'Dr. Andi (Ketua Lab)',
            'nidn' => '22222222',
            'email' => 'andilab@kampus.ac.id',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $dosen_anggota = DB::table('dosen')->insertGetId([
            'id_kk' => $id_kk,
            'id_lab' => $id_lab,
            'nama_dosen' => 'Devan (Anggota)',
            'nidn' => '33333333',
            'email' => 'devan@kampus.ac.id',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Buat 3 Akun User (Password semua sama: 'password123')
        DB::table('users')->insert([
            [
                'id_dosen' => $dosen_ketua_kk,
                'username' => 'ketuakk',
                'password' => Hash::make('password123'),
                'role' => 'Ketua KK',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_dosen' => $dosen_ketua_lab,
                'username' => 'ketualab',
                'password' => Hash::make('password123'),
                'role' => 'Ketua Lab',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_dosen' => $dosen_anggota,
                'username' => 'anggota',
                'password' => Hash::make('password123'),
                'role' => 'Anggota',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}