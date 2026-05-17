<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Cek apakah user sudah login DAN role-nya sesuai dengan yang diizinkan
        if (auth()->check() && auth()->user()->role == $role) {
            return $next($request); // Silakan lewat
        }

        // Kalau role tidak sesuai, tendang kembali ke halaman utama
        return redirect('/')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut!');
    }
}