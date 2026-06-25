<?php

namespace App\Http\Controllers;

use App\Models\TargetKm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TargetKmController extends Controller
{
    private function getOrCreateKontrakManajemen(int $tahun): int
    {
        $km = DB::table('kontrak_manajemen')
            ->where('tahun_km', $tahun)
            ->first();

        if ($km) {
            return $km->id_km;
        }

        return DB::table('kontrak_manajemen')->insertGetId([
            'id_dosen' => auth()->user()->id_dosen,
            'tahun_km' => $tahun,
            'status_km' => 'Draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function getKategoriOptions(): array
    {
        return [
            'Pendidikan' => [
                'Perkuliahan',
                'Bimbingan Mahasiswa',
                'Pengembangan Bahan Ajar',
                'Asistensi Akademik',
            ],
            'Penelitian' => [
                'Pendanaan Internal',
                'Pendanaan Eksternal',
                'Penelitian Kolaboratif',
                'Luaran Penelitian',
            ],
            'Publikasi' => [
                'Jurnal Internasional Bereputasi',
                'Jurnal Internasional',
                'Jurnal Nasional Terakreditasi',
                'Prosiding Seminar',
                'HKI',
                'Buku/Book Chapter',
            ],
            'Pengabdian' => [
                'Pengabdian Masyarakat Internal',
                'Pengabdian Masyarakat Eksternal',
                'Kegiatan Sosialisasi/Pelatihan',
                'Luaran Pengabdian',
            ],
            'Penunjang' => [
                'Kepanitiaan',
                'Narasumber',
                'Keanggotaan Organisasi',
                'Prestasi/Penghargaan',
            ],
        ];
    }

    public function index(Request $request)
    {
        $tahun = (int) $request->query('tahun', now()->year);

        $tahunOptions = DB::table('kontrak_manajemen')
            ->select('tahun_km')
            ->distinct()
            ->orderBy('tahun_km', 'desc')
            ->pluck('tahun_km');

        if ($tahunOptions->isEmpty()) {
            $tahunOptions = collect([now()->year]);
        }

        if (! $tahunOptions->contains($tahun)) {
            $tahunOptions->push($tahun);
            $tahunOptions = $tahunOptions->unique()->sortDesc()->values();
        }

        $targets = TargetKm::query()
            ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
            ->where('kontrak_manajemen.tahun_km', $tahun)
            ->select('target_km.*', 'kontrak_manajemen.tahun_km')
            ->orderBy('target_km.kategori_km')
            ->orderBy('target_km.indikator')
            ->get();

        return view('ketuakk.target', [
            'targets' => $targets,
            'tahun' => $tahun,
            'tahunOptions' => $tahunOptions,
        ]);
    }

    public function create()
    {
        return view('ketuakk.target_create', [
            'kategoriOptions' => $this->getKategoriOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $kategoriOptions = $this->getKategoriOptions();
        $kategoriList = array_keys($kategoriOptions);
        $subKategoriList = collect($kategoriOptions)->flatten()->values()->all();

        $request->validate([
            'tahun_km' => 'required|integer|min:2020|max:2100',
            'kategori_km' => 'required|string|in:' . implode(',', $kategoriList),
            'indikator' => 'required|string|in:' . implode(',', $subKategoriList),
            'triwulan_1' => 'required|integer|min:0',
            'triwulan_2' => 'required|integer|min:0',
            'triwulan_3' => 'required|integer|min:0',
            'triwulan_4' => 'required|integer|min:0',
        ]);

        $totalTarget =
            (int) $request->triwulan_1 +
            (int) $request->triwulan_2 +
            (int) $request->triwulan_3 +
            (int) $request->triwulan_4;

        $idKm = $this->getOrCreateKontrakManajemen((int) $request->tahun_km);

        TargetKm::create([
            'id_km' => $idKm,
            'kategori_km' => $request->kategori_km,
            'indikator' => $request->indikator,
            'triwulan_1' => (int) $request->triwulan_1,
            'triwulan_2' => (int) $request->triwulan_2,
            'triwulan_3' => (int) $request->triwulan_3,
            'triwulan_4' => (int) $request->triwulan_4,
            'target' => $totalTarget,
        ]);

        return redirect('/ketuakk/target-km?tahun=' . $request->tahun_km)
            ->with('success', 'Target KM berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $target = TargetKm::query()
            ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
            ->where('target_km.id_target', $id)
            ->select('target_km.*', 'kontrak_manajemen.tahun_km')
            ->firstOrFail();

        return view('ketuakk.target_edit', [
            'target' => $target,
            'kategoriOptions' => $this->getKategoriOptions(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $kategoriOptions = $this->getKategoriOptions();
        $kategoriList = array_keys($kategoriOptions);
        $subKategoriList = collect($kategoriOptions)->flatten()->values()->all();

        $request->validate([
            'tahun_km' => 'required|integer|min:2020|max:2100',
            'kategori_km' => 'required|string|in:' . implode(',', $kategoriList),
            'indikator' => 'required|string|in:' . implode(',', $subKategoriList),
            'triwulan_1' => 'required|integer|min:0',
            'triwulan_2' => 'required|integer|min:0',
            'triwulan_3' => 'required|integer|min:0',
            'triwulan_4' => 'required|integer|min:0',
        ]);

        $totalTarget =
            (int) $request->triwulan_1 +
            (int) $request->triwulan_2 +
            (int) $request->triwulan_3 +
            (int) $request->triwulan_4;

        $idKm = $this->getOrCreateKontrakManajemen((int) $request->tahun_km);

        $target = TargetKm::findOrFail($id);

        $target->update([
            'id_km' => $idKm,
            'kategori_km' => $request->kategori_km,
            'indikator' => $request->indikator,
            'triwulan_1' => (int) $request->triwulan_1,
            'triwulan_2' => (int) $request->triwulan_2,
            'triwulan_3' => (int) $request->triwulan_3,
            'triwulan_4' => (int) $request->triwulan_4,
            'target' => $totalTarget,
        ]);

        return redirect('/ketuakk/target-km?tahun=' . $request->tahun_km)
            ->with('success', 'Target KM berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $target = TargetKm::query()
            ->join('kontrak_manajemen', 'target_km.id_km', '=', 'kontrak_manajemen.id_km')
            ->where('target_km.id_target', $id)
            ->select('target_km.*', 'kontrak_manajemen.tahun_km')
            ->firstOrFail();

        $tahun = $target->tahun_km;

        TargetKm::where('id_target', $id)->delete();

        return redirect('/ketuakk/target-km?tahun=' . $tahun)
            ->with('success', 'Target KM berhasil dihapus!');
    }
}
