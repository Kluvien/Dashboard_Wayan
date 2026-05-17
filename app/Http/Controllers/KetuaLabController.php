<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TargetKm;

class KetuaLabController extends Controller
{
    public function penurunanKm()
    {
        $targets = TargetKm::all();
        
        return view('ketualab.penurunan', compact('targets'));
    }
}