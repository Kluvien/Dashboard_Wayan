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
        'indikator',
        'target',
        'kategori_km',
        'triwulan_1',
        'triwulan_2',
        'triwulan_3',
        'triwulan_4',
    ];
}
