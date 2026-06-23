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
        $tahun = now()->year;
        $idLab = $user->id_lab;

        $lab = DB::table('laboratorium_riset')
            ->where('id_lab', $idLab)
            ->first();

        $anggota = DB::table('users')
            ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
            ->where('users.role', 'Anggota')
            ->where('users.id_lab', $idLab)
            ->select(
                'users.id_user',
                'users.username',
                'users.id_dosen',
                'dosen.nama_dosen',
                'dosen.nidn',
                'dosen.email',
                'dosen.jad'
            )
            ->orderBy('dosen.nama_dosen')
            ->get();

        $bobotJad = [
            'GB' => 1.4,
            'LK' => 1.2,
            'L' => 1.0,
            'AA' => 0.8,
        ];

        $jadLabel = [
            'GB' => 'Guru Besar',
            'LK' => 'Lektor Kepala',
            'L' => 'Lektor',
            'AA' => 'Asisten Ahli',
        ];

        $kategori = [
            'Pendidikan',
            'Penelitian',
            'Publikasi',
            'Pengabdian',
            'Penunjang',
        ];

        $dataAnggota = [];

        foreach ($anggota as $item) {
            $jad = $item->jad ?? 'AA';

            $targets = DB::table('target_km')
                ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
                ->where('kontrak_manajemen.id_dosen', $item->id_dosen)
                ->where('kontrak_manajemen.tahun_km', $tahun)
                ->where('kontrak_manajemen.status_km', 'Aktif')
                ->pluck('target_km.target', 'target_km.indikator')
                ->toArray();

            $totalTarget = array_sum($targets);

            $totalRealisasi = DB::table('aktivitas_km')
                ->where('id_user', $item->id_user)
                ->count();

            $dataAnggota[] = [
                'id_user' => $item->id_user,
                'id_dosen' => $item->id_dosen,
                'nama_dosen' => $item->nama_dosen ?? $item->username,
                'nidn' => $item->nidn ?? '-',
                'email' => $item->email ?? '-',
                'jad' => $jad,
                'jad_label' => $jadLabel[$jad] ?? 'Asisten Ahli',
                'bobot' => $bobotJad[$jad] ?? 0.8,
                'targets' => $targets,
                'total_target' => $totalTarget,
                'total_realisasi' => $totalRealisasi,
            ];
        }

        return view('ketualab.pembagian-km-anggota', compact(
            'lab',
            'tahun',
            'kategori',
            'dataAnggota'
        ));
    }

    public function simpanPembagianKmAnggota(Request $request)
    {
        $request->validate([
            'tahun_km' => 'required|integer',
            'target_pendidikan' => 'required|integer|min:0',
            'target_penelitian' => 'required|integer|min:0',
            'target_publikasi' => 'required|integer|min:0',
            'target_pengabdian' => 'required|integer|min:0',
            'target_penunjang' => 'required|integer|min:0',
        ]);

        $user = auth()->user();
        $idLab = $user->id_lab;
        $tahun = $request->tahun_km;

        $targetLab = [
            'Pendidikan' => (int) $request->target_pendidikan,
            'Penelitian' => (int) $request->target_penelitian,
            'Publikasi' => (int) $request->target_publikasi,
            'Pengabdian' => (int) $request->target_pengabdian,
            'Penunjang' => (int) $request->target_penunjang,
        ];

        $anggota = DB::table('users')
            ->leftJoin('dosen', 'users.id_dosen', '=', 'dosen.id_dosen')
            ->where('users.role', 'Anggota')
            ->where('users.id_lab', $idLab)
            ->whereNotNull('users.id_dosen')
            ->select(
                'users.id_user',
                'users.id_dosen',
                'dosen.jad'
            )
            ->get();

        if ($anggota->isEmpty()) {
            return redirect('/ketualab/penurunan-km')
                ->with('error', 'Belum ada anggota lab yang dapat menerima pembagian KM.');
        }

        $bobotJad = [
            'GB' => 1.4,
            'LK' => 1.2,
            'L' => 1.0,
            'AA' => 0.8,
        ];

        $totalBobot = 0;

        foreach ($anggota as $item) {
            $jad = $item->jad ?? 'AA';
            $totalBobot += $bobotJad[$jad] ?? 0.8;
        }

        foreach ($anggota as $item) {
            $jad = $item->jad ?? 'AA';
            $bobot = $bobotJad[$jad] ?? 0.8;

            $idKm = DB::table('kontrak_manajemen')
                ->where('id_dosen', $item->id_dosen)
                ->where('tahun_km', $tahun)
                ->value('id_km');

            if (! $idKm) {
                $idKm = DB::table('kontrak_manajemen')->insertGetId([
                    'id_dosen' => $item->id_dosen,
                    'tahun_km' => $tahun,
                    'status_km' => 'Aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('kontrak_manajemen')
                    ->where('id_km', $idKm)
                    ->update([
                        'status_km' => 'Aktif',
                        'updated_at' => now(),
                    ]);
            }

            DB::table('target_km')
                ->where('id_km', $idKm)
                ->delete();

            foreach ($targetLab as $indikator => $target) {
                $porsi = $totalBobot > 0
                    ? round(($target * $bobot) / $totalBobot)
                    : 0;

                DB::table('target_km')->insert([
                    'id_km' => $idKm,
                    'indikator' => $indikator,
                    'target' => $porsi,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect('/ketualab/penurunan-km')
            ->with('success', 'Pembagian KM anggota berdasarkan JAD berhasil disimpan.');
    }
}
