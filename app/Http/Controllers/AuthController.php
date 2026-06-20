<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Logika Redirect Otomatis Berdasarkan Role
            $role = Auth::user()->role;

            if ($role == 'Ketua KK') {
                return redirect('/ketuakk/dashboard');
            } elseif ($role == 'Ketua Lab') {
                return redirect('/ketualab/dashboard');
            } elseif ($role == 'Anggota') {
                return redirect('/anggota/dashboard');
            } else {
                return redirect('/'); 
            }
        }

        return back()->with('loginError', 'Username atau Password salah!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}