<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $notifikasi = Notifikasi::query()
            ->where('id_user', $user->id_user)
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('notifikasi.index', compact('notifikasi'));
    }

    public function baca(int $id): RedirectResponse
    {
        $user = auth()->user();

        $notifikasi = Notifikasi::query()
            ->where('id_notifikasi', $id)
            ->where('id_user', $user->id_user)
            ->firstOrFail();

        if (empty($notifikasi->dibaca_pada)) {
            $notifikasi->update([
                'dibaca_pada' => now(),
            ]);
        }

        return redirect($notifikasi->url_tujuan ?: '/');
    }

    public function bacaSemua(): RedirectResponse
    {
        $user = auth()->user();

        Notifikasi::query()
            ->where('id_user', $user->id_user)
            ->whereNull('dibaca_pada')
            ->update([
                'dibaca_pada' => now(),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }
}
