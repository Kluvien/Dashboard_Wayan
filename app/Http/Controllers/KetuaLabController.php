<?php

namespace App\Http\Controllers;

use App\Models\RealisasiKm;
use App\Models\TargetKm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KetuaLabController extends Controller
{
    public function dashboard()
    {
        /** @var User $user */
        $user = auth()->user();

        $tahun = now()->year;
        $idLab = $user->id_lab;

        $lab = DB::table('laboratorium_riset')
            ->where('id_lab', $idLab)
            ->first();

        $anggota = DB::table('users')
            ->where('role', 'Anggota')
            ->where('id_lab', $idLab)
            ->get();

        $jumlahAnggota = $anggota->count();

        $targetLabPerKategori = [
            'Pendidikan' => 0,
            'Penelitian' => 0,
            'Publikasi' => 0,
            'Pengabdian' => 0,
            'Penunjang' => 0,
        ];

        foreach ($anggota as $item) {
            $targets = DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->where('kontrak_manajemen.id_dosen', $item->id_dosen)
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->where('kontrak_manajemen.status_km', 'Aktif')
                ->pluck('target', 'indikator')
                ->toArray();

            foreach ($targets as $kategori => $target) {
                if (! isset($targetLabPerKategori[$kategori])) {
                    $targetLabPerKategori[$kategori] = 0;
                }

                $targetLabPerKategori[$kategori] += $target;
            }
        }

        $aktivitasPerKategori = DB::table('aktivitas_km')
            ->select('kategori_km', DB::raw('COUNT(*) as total'))
            ->where('id_lab', $idLab)
            ->groupBy('kategori_km')
            ->pluck('total', 'kategori_km');

        $rekapKategori = [];

        foreach ($targetLabPerKategori as $kategori => $target) {
            $realisasi = $aktivitasPerKategori[$kategori] ?? 0;
            $persentase = $target > 0 ? round(($realisasi / $target) * 100) : 0;

            $rekapKategori[] = [
                'kategori' => $kategori,
                'target' => $target,
                'realisasi' => $realisasi,
                'persentase' => min($persentase, 100),
                'status' => $target > 0 && $realisasi >= $target ? 'Tercapai' : 'Belum Tercapai',
            ];
        }

        $totalTargetLab = array_sum($targetLabPerKategori);
        $totalRealisasiLab = array_sum($aktivitasPerKategori->toArray());

        $persentaseTotal = $totalTargetLab > 0
            ? round(($totalRealisasiLab / $totalTargetLab) * 100)
            : 0;

        $aktivitasTerbaru = DB::table('aktivitas_km')
            ->leftJoin('users', 'aktivitas_km.id_user', '=', 'users.id_user')
            ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
            ->where('aktivitas_km.id_lab', $idLab)
            ->select(
                'aktivitas_km.kategori_km',
                'aktivitas_km.judul_aktivitas',
                'aktivitas_km.tanggal_mulai',
                'aktivitas_km.tanggal_selesai',
                'users.username',
                'dosen.nama_dosen'
            )
            ->orderBy('aktivitas_km.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('ketualab.dashboard', compact(
            'tahun',
            'lab',
            'jumlahAnggota',
            'totalTargetLab',
            'totalRealisasiLab',
            'persentaseTotal',
            'rekapKategori',
            'aktivitasTerbaru'
        ));
    }

    public function penurunanKm()
    {
        $targets = TargetKm::all();

        return view('ketualab.penurunan', compact('targets'));
    }

    // Menampilkan form pilih dosen
    public function createPlot($id)
    {
        $target = TargetKm::findOrFail($id);
        // Ambil semua user yang role-nya 'Anggota'
        $anggotas = User::where('role', 'Anggota')->get();

        return view('ketualab.plot_create', compact('target', 'anggotas'));
    }

    // Menyimpan hasil plot ke database realisasi
    public function storePlot(Request $request, $id)
    {
        $request->validate([
            'id_dosen' => 'required',
        ]);

        RealisasiKm::create([
            'id_target' => $id,
            'id_dosen' => $request->id_dosen,
            'realisasi' => 0,
            'status_realisasi' => 'Belum Tercapai',
        ]);

        return redirect('/ketualab/penurunan-km')->with('success', 'Target KM berhasil didistribusikan ke Anggota!');
    }

    public function pembagianKmAnggota()
    {
        $user = auth()->user();
        $idLab = $user->id_lab;
        $tahun = now()->year;

        $lab = DB::table('laboratorium_riset')
            ->where('id_lab', $idLab)
            ->first();

        $kmLab = DB::table('km_lab')
            ->where('id_lab', $idLab)
            ->where('tahun_km', $tahun)
            ->where('status_km', 'Aktif')
            ->orderBy('kategori_km')
            ->get();

        $dataKmLab = [];

        foreach ($kmLab as $km) {
            $sudahAssign = DB::table('km_anggota')
                ->where('id_km_lab', $km->id_km_lab)
                ->sum('jumlah_km');

            $sisaKm = $km->jumlah_km - $sudahAssign;

            $dataKmLab[] = [
                'id_km_lab' => $km->id_km_lab,
                'kategori_km' => $km->kategori_km,
                'jumlah_km' => $km->jumlah_km,
                'sudah_assign' => $sudahAssign,
                'sisa_km' => $sisaKm,
                'status' => $sisaKm <= 0 ? 'Sudah Dibagi' : 'Belum Selesai',
            ];
        }

        $anggota = DB::table('users')
            ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
            ->where('users.role', 'Anggota')
            ->where('users.id_lab', $idLab)
            ->select(
                'users.id_user',
                'users.id_dosen',
                'users.username',
                'dosen.nama_dosen',
                'dosen.nidn',
                'dosen.email',
                'dosen.jad'
            )
            ->orderBy('dosen.nama_dosen')
            ->get();

        return view('ketualab.pembagian-km-anggota', compact(
            'lab',
            'tahun',
            'dataKmLab',
            'anggota'
        ));
    }

    public function simpanPembagianKmAnggota(Request $request)
    {
        $request->validate([
            'id_km_lab' => 'required|exists:km_lab,id_km_lab',
            'id_user' => 'required|exists:users,id_user',
            'jumlah_km' => 'required|integer|min:1',
        ]);

        $user = auth()->user();
        $idLab = $user->id_lab;

        $kmLab = DB::table('km_lab')
            ->where('id_km_lab', $request->id_km_lab)
            ->where('id_lab', $idLab)
            ->where('status_km', 'Aktif')
            ->first();

        if (!$kmLab) {
            return redirect('/ketualab/penurunan-km')
                ->with('error', 'KM Lab tidak ditemukan atau bukan milik lab Anda.');
        }

        $anggota = DB::table('users')
            ->where('id_user', $request->id_user)
            ->where('role', 'Anggota')
            ->where('id_lab', $idLab)
            ->first();

        if (!$anggota) {
            return redirect('/ketualab/penurunan-km')
                ->with('error', 'Anggota tidak ditemukan atau bukan anggota lab Anda.');
        }

        $sudahAssign = DB::table('km_anggota')
            ->where('id_km_lab', $kmLab->id_km_lab)
            ->sum('jumlah_km');

        $sisaKm = $kmLab->jumlah_km - $sudahAssign;

        if ((int) $request->jumlah_km > $sisaKm) {
            return redirect('/ketualab/penurunan-km')
                ->with('error', 'Jumlah KM yang dibagikan melebihi sisa KM yang tersedia.');
        }

        $assignLama = DB::table('km_anggota')
            ->where('id_km_lab', $kmLab->id_km_lab)
            ->where('id_user', $anggota->id_user)
            ->first();

        if ($assignLama) {
            DB::table('km_anggota')
                ->where('id_km_anggota', $assignLama->id_km_anggota)
                ->update([
                    'jumlah_km' => $assignLama->jumlah_km + (int) $request->jumlah_km,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('km_anggota')->insert([
                'id_km_lab' => $kmLab->id_km_lab,
                'id_user' => $anggota->id_user,
                'id_dosen' => $anggota->id_dosen,
                'jumlah_km' => (int) $request->jumlah_km,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect('/ketualab/penurunan-km')
            ->with('success', 'KM berhasil dibagikan ke anggota.');
    }
}
