<?php

namespace App\Http\Controllers;

use App\Models\TargetKm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class TargetKmController extends Controller
{
    private function getOrCreateKontrakManajemen(int $tahun): int
    {
        $idDosen = auth()->user()->id_dosen;

        $km = DB::table('kontrak_manajemen')
            ->where('id_dosen', $idDosen)
            ->where('tahun_km', $tahun)
            ->first();

        if ($km) {
            return $km->id_km;
        }

        return DB::table('kontrak_manajemen')->insertGetId([
            'id_dosen' => $idDosen,
            'tahun_km' => $tahun,
            'status_km' => 'Draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function getKategoriOptions(): array
    {
        return [
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

    private function getQuarterRange(int $tahun, int $triwulan): array
    {
        $bulanMulai = (($triwulan - 1) * 3) + 1;
        $bulanSelesai = $bulanMulai + 2;

        return [
            'mulai' => Carbon::create($tahun, $bulanMulai, 1)->startOfMonth(),
            'selesai' => Carbon::create($tahun, $bulanSelesai, 1)->endOfMonth(),
        ];
    }

    private function validateTarget(Request $request): array
    {
        $kategoriOptions = $this->getKategoriOptions();
        $kategoriList = array_keys($kategoriOptions);

        $hasKeterangan = Schema::hasColumn('target_km', 'keterangan');

        $rules = [
            'tahun_km' => 'required|integer|min:2020|max:2100',
            'kategori_km' => 'required|string|in:' . implode(',', $kategoriList),
            'indikator' => 'required|string|max:255',

            'triwulan_1' => 'required|integer|min:0',
            'triwulan_2' => 'required|integer|min:0',
            'triwulan_3' => 'required|integer|min:0',
            'triwulan_4' => 'required|integer|min:0',

            'tanggal_mulai_tw1' => 'nullable|date',
            'tanggal_selesai_tw1' => 'nullable|date',

            'tanggal_mulai_tw2' => 'nullable|date',
            'tanggal_selesai_tw2' => 'nullable|date',

            'tanggal_mulai_tw3' => 'nullable|date',
            'tanggal_selesai_tw3' => 'nullable|date',

            'tanggal_mulai_tw4' => 'nullable|date',
            'tanggal_selesai_tw4' => 'nullable|date',
        ];

        if ($hasKeterangan) {
            $rules['keterangan'] = 'nullable|string|max:1000';
        }

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            $tahun = (int) $request->tahun_km;

            for ($triwulan = 1; $triwulan <= 4; $triwulan++) {
                $jumlahTarget = (int) $request->{'triwulan_' . $triwulan};

                $fieldMulai = 'tanggal_mulai_tw' . $triwulan;
                $fieldSelesai = 'tanggal_selesai_tw' . $triwulan;

                $tanggalMulai = $request->input($fieldMulai);
                $tanggalSelesai = $request->input($fieldSelesai);

                /*
                | Bila target TW = 0, deadline tidak diperlukan.
                */
                if ($jumlahTarget <= 0) {
                    continue;
                }

                if (empty($tanggalMulai)) {
                    $validator->errors()->add(
                        $fieldMulai,
                        'Tanggal mulai Triwulan ' . $triwulan . ' wajib diisi karena target KM lebih dari 0.'
                    );
                }

                if (empty($tanggalSelesai)) {
                    $validator->errors()->add(
                        $fieldSelesai,
                        'Tanggal selesai Triwulan ' . $triwulan . ' wajib diisi karena target KM lebih dari 0.'
                    );
                }

                if (empty($tanggalMulai) || empty($tanggalSelesai)) {
                    continue;
                }

                try {
                    $mulai = Carbon::parse($tanggalMulai)->startOfDay();
                    $selesai = Carbon::parse($tanggalSelesai)->startOfDay();
                    $periode = $this->getQuarterRange($tahun, $triwulan);

                    if ($selesai->lt($mulai)) {
                        $validator->errors()->add(
                            $fieldSelesai,
                            'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.'
                        );
                    }

                    if (
                        $mulai->lt($periode['mulai']) ||
                        $mulai->gt($periode['selesai'])
                    ) {
                        $validator->errors()->add(
                            $fieldMulai,
                            'Tanggal mulai harus berada dalam periode Triwulan ' . $triwulan . '.'
                        );
                    }

                    if (
                        $selesai->lt($periode['mulai']) ||
                        $selesai->gt($periode['selesai'])
                    ) {
                        $validator->errors()->add(
                            $fieldSelesai,
                            'Tanggal selesai harus berada dalam periode Triwulan ' . $triwulan . '.'
                        );
                    }
                } catch (\Throwable $error) {
                    $validator->errors()->add(
                        $fieldMulai,
                        'Format deadline Triwulan ' . $triwulan . ' tidak valid.'
                    );
                }
            }
        });

        return $validator->validate();
    }

    private function buildTargetPayload(array $validated): array
    {
        $totalTarget =
            (int) $validated['triwulan_1'] +
            (int) $validated['triwulan_2'] +
            (int) $validated['triwulan_3'] +
            (int) $validated['triwulan_4'];

        $payload = [
            'kategori_km' => $validated['kategori_km'],
            'indikator' => $validated['indikator'],
            'triwulan_1' => (int) $validated['triwulan_1'],
            'triwulan_2' => (int) $validated['triwulan_2'],
            'triwulan_3' => (int) $validated['triwulan_3'],
            'triwulan_4' => (int) $validated['triwulan_4'],
            'target' => $totalTarget,
        ];

        if (Schema::hasColumn('target_km', 'keterangan')) {
            $payload['keterangan'] = $validated['keterangan'] ?? null;
        }

        for ($triwulan = 1; $triwulan <= 4; $triwulan++) {
            $jumlahTarget = (int) $validated['triwulan_' . $triwulan];

            $fieldMulai = 'tanggal_mulai_tw' . $triwulan;
            $fieldSelesai = 'tanggal_selesai_tw' . $triwulan;

            $payload[$fieldMulai] = $jumlahTarget > 0
                ? ($validated[$fieldMulai] ?? null)
                : null;

            $payload[$fieldSelesai] = $jumlahTarget > 0
                ? ($validated[$fieldSelesai] ?? null)
                : null;
        }

        return $payload;
    }

    private function findOwnedTargetOrFail(int $id): TargetKm
    {
        return TargetKm::query()
            ->join(
                'kontrak_manajemen',
                'target_km.id_km',
                '=',
                'kontrak_manajemen.id_km'
            )
            ->where('target_km.id_target', $id)
            ->where('kontrak_manajemen.id_dosen', auth()->user()->id_dosen)
            ->select('target_km.*', 'kontrak_manajemen.tahun_km')
            ->firstOrFail();
    }

    public function index(Request $request)
    {
        $tahun = (int) $request->query('tahun', now()->year);

        $tahunOptions = DB::table('kontrak_manajemen')
            ->where('id_dosen', auth()->user()->id_dosen)
            ->select('tahun_km')
            ->distinct()
            ->orderBy('tahun_km', 'desc')
            ->pluck('tahun_km');

        if ($tahunOptions->isEmpty()) {
            $tahunOptions = collect([now()->year]);
        }

        if (!$tahunOptions->contains($tahun)) {
            $tahunOptions->push($tahun);
            $tahunOptions = $tahunOptions->unique()->sortDesc()->values();
        }

        $targets = TargetKm::query()
            ->join(
                'kontrak_manajemen',
                'target_km.id_km',
                '=',
                'kontrak_manajemen.id_km'
            )
            ->where('kontrak_manajemen.id_dosen', auth()->user()->id_dosen)
            ->where('kontrak_manajemen.tahun_km', $tahun)
            ->select('target_km.*', 'kontrak_manajemen.tahun_km')
            ->orderBy('target_km.kategori_km')
            ->orderBy('target_km.indikator')
            ->get();

        return view('ketuakk.target', compact(
            'targets',
            'tahun',
            'tahunOptions'
        ));
    }

    public function create()
    {
        return view('ketuakk.target_create', [
            'kategoriOptions' => $this->getKategoriOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateTarget($request);

        $idKm = $this->getOrCreateKontrakManajemen(
            (int) $validated['tahun_km']
        );

        TargetKm::create(array_merge([
            'id_km' => $idKm,
        ], $this->buildTargetPayload($validated)));

        return redirect('/ketuakk/target-km?tahun=' . $validated['tahun_km'])
            ->with('success', 'Target KM beserta deadline Triwulan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $target = $this->findOwnedTargetOrFail((int) $id);

        return view('ketuakk.target_edit', [
            'target' => $target,
            'kategoriOptions' => $this->getKategoriOptions(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateTarget($request);

        $target = $this->findOwnedTargetOrFail((int) $id);

        $idKm = $this->getOrCreateKontrakManajemen(
            (int) $validated['tahun_km']
        );

        TargetKm::where('id_target', $target->id_target)
            ->update(array_merge([
                'id_km' => $idKm,
                'updated_at' => now(),
            ], $this->buildTargetPayload($validated)));

        return redirect('/ketuakk/target-km?tahun=' . $validated['tahun_km'])
            ->with('success', 'Target KM beserta deadline Triwulan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $target = $this->findOwnedTargetOrFail((int) $id);

        $tahun = $target->tahun_km;

        $sudahDiturunkanKeLab = false;

        if (Schema::hasColumn('km_lab', 'id_target')) {
            $sudahDiturunkanKeLab = DB::table('km_lab')
                ->where('id_target', $target->id_target)
                ->exists();
        } else {
            $sudahDiturunkanKeLab = DB::table('km_lab')
                ->where('tahun_km', $tahun)
                ->where('kategori_km', $target->kategori_km)
                ->where('sub_kategori_km', $target->indikator)
                ->exists();
        }

        if ($sudahDiturunkanKeLab) {
            return redirect('/ketuakk/target-km?tahun=' . $tahun)
                ->with(
                    'error',
                    'Target KM tidak dapat dihapus karena sudah diturunkan ke Lab Riset.'
                );
        }

        TargetKm::where('id_target', $target->id_target)->delete();

        return redirect('/ketuakk/target-km?tahun=' . $tahun)
            ->with('success', 'Target KM berhasil dihapus.');
    }
}