<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealisasiKm extends Model
{
    use HasFactory;

    protected $table = 'realisasi_km';
    protected $primaryKey = 'id_realisasi';

    protected $fillable = [
        'id_target',
        'id_dosen',
        'realisasi',
        'status_realisasi'
    ];
}