<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';

    protected $primaryKey = 'id_notifikasi';

    protected $fillable = [
        'id_user',
        'id_km_lab',
        'id_km_anggota',
        'jenis_notifikasi',
        'judul',
        'pesan',
        'kategori_km',
        'sub_kategori_km',
        'jumlah_km',
        'tanggal_tenggat',
        'url_tujuan',
        'kode_unik',
        'dibaca_pada',
    ];

    protected $casts = [
        'tanggal_tenggat' => 'date',
        'dibaca_pada' => 'datetime',
    ];
}
