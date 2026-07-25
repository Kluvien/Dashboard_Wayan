<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetKm extends Model
{
    use HasFactory;

    protected $table = 'target_km';

    protected $primaryKey = 'id_target';

    protected $fillable = [
        'id_km',
        'kategori_km',
        'indikator',
        'keterangan',
        'target',
        'triwulan_1',
        'triwulan_2',
        'triwulan_3',
        'triwulan_4',
        'tanggal_mulai_tw1',
        'tanggal_selesai_tw1',
        'tanggal_mulai_tw2',
        'tanggal_selesai_tw2',
        'tanggal_mulai_tw3',
        'tanggal_selesai_tw3',
        'tanggal_mulai_tw4',
        'tanggal_selesai_tw4',
    ];

    protected $casts = [
        'tanggal_mulai_tw1' => 'date',
        'tanggal_selesai_tw1' => 'date',
        'tanggal_mulai_tw2' => 'date',
        'tanggal_selesai_tw2' => 'date',
        'tanggal_mulai_tw3' => 'date',
        'tanggal_selesai_tw3' => 'date',
        'tanggal_mulai_tw4' => 'date',
        'tanggal_selesai_tw4' => 'date',
    ];
}