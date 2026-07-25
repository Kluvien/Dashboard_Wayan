<?php

namespace App\Http\Controllers;

use App\Services\AktivitasKmNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AktivitasKmController extends Controller
{
    /**
     * Menyimpan aktivitas KM baru.
     * Anggota hanya dapat menyimpan sebagai "Sedang Berjalan" atau
     * "Diajukan". Status "Disetujui" dan "Ditolak" hanya dapat ditentukan
     * oleh Ketua Lab melalui method verifikasi().
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'id_km_anggota' => 'required|integer|exists:km_anggota,id_km_anggota',
            'judul_aktivitas' => 'required|string|max:255',
            'deskripsi_singkat' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'bukti_link' => 'nullable|url|max:255',
            'bukti_file' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:10240',
            'status_progress' => 'required|in:On Progress,Submitted',
        ]);

        $user = auth()->user();
        $statusBaru = (string) $request->status_progress;

        if ($statusBaru === 'Submitted' && ! $this->hasEvidence($request)) {
            return back()
                ->withErrors([
                    'bukti_file' => 'Bukti aktivitas wajib diisi sebelum aktivitas dapat diajukan untuk verifikasi.',
                ])
                ->withInput();
        }

        $kmAnggota = $this->findKmAnggotaMilikUser(
            (int) $request->id_km_anggota,
            (int) $user->id_user
        );

        if (! $kmAnggota) {
            return back()
                ->withErrors([
                    'id_km_anggota' => 'KM yang dipilih tidak ditemukan atau bukan milik Anda.',
                ])
                ->withInput();
        }

        $buktiFileData = km_eims_store_bukti_file($request);

        $idAktivitas = DB::table('aktivitas_km')->insertGetId(array_merge([
            'id_user' => $user->id_user,
            'id_lab' => $kmAnggota->id_lab,
            'id_km_anggota' => $kmAnggota->id_km_anggota,
            'kategori_km' => $kmAnggota->kategori_km,
            'sub_kategori_km' => $kmAnggota->sub_kategori_km,
            'judul_aktivitas' => $request->judul_aktivitas,
            'deskripsi_singkat' => $request->deskripsi_singkat,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'bukti_link' => $request->bukti_link,
            'status_progress' => $statusBaru,
            'diajukan_pada' => $statusBaru === 'Submitted' ? now() : null,
            'diverifikasi_pada' => null,
            'diverifikasi_oleh' => null,
            'catatan_verifikasi' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $buktiFileData));

        if ($statusBaru === 'Submitted') {
            AktivitasKmNotificationService::notifyAktivitasDisubmit((int) $idAktivitas);

            return redirect('/anggota/aktivitas-km')
                ->with('success', 'Aktivitas KM berhasil diajukan untuk verifikasi Ketua Lab.');
        }

        return redirect('/anggota/aktivitas-km')
            ->with('success', 'Aktivitas KM berhasil disimpan sebagai Sedang Berjalan.');
    }

    /**
     * Memperbarui aktivitas yang masih dapat dikelola anggota.
     * Aktivitas berstatus Diajukan tidak boleh diubah saat proses verifikasi.
     * Aktivitas berstatus Disetujui dikunci agar bukti yang sudah sah tidak berubah.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'id_km_anggota' => 'required|integer|exists:km_anggota,id_km_anggota',
            'judul_aktivitas' => 'required|string|max:255',
            'deskripsi_singkat' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'bukti_link' => 'nullable|url|max:255',
            'bukti_file' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:10240',
            'status_progress' => 'required|in:On Progress,Submitted',
        ]);

        $user = auth()->user();

        $aktivitas = DB::table('aktivitas_km')
            ->where('id_aktivitas', $id)
            ->where('id_user', $user->id_user)
            ->first();

        if (! $aktivitas) {
            abort(404);
        }

        $statusLama = (string) ($aktivitas->status_progress ?? 'On Progress');

        if (in_array($statusLama, ['Submitted', 'Accepted'], true)) {
            return redirect('/anggota/aktivitas-km')
                ->with('error', $statusLama === 'Submitted'
                    ? 'Aktivitas sedang menunggu verifikasi Ketua Lab sehingga belum dapat diubah.'
                    : 'Aktivitas yang sudah disetujui tidak dapat diubah.');
        }

        $statusBaru = (string) $request->status_progress;

        if ($statusBaru === 'Submitted' && ! $this->hasEvidence($request, $aktivitas)) {
            return back()
                ->withErrors([
                    'bukti_file' => 'Bukti aktivitas wajib tersedia sebelum aktivitas dapat diajukan untuk verifikasi.',
                ])
                ->withInput();
        }

        $kmAnggota = $this->findKmAnggotaMilikUser(
            (int) $request->id_km_anggota,
            (int) $user->id_user
        );

        if (! $kmAnggota) {
            return back()
                ->withErrors([
                    'id_km_anggota' => 'KM yang dipilih tidak ditemukan atau bukan milik Anda.',
                ])
                ->withInput();
        }

        $buktiFileData = km_eims_store_bukti_file($request);

        if (!empty($buktiFileData)) {
            foreach (['bukti_file_path', 'bukti_pdf_path'] as $pathColumn) {
                if (!empty($aktivitas->{$pathColumn})) {
                    Storage::disk('local')->delete($aktivitas->{$pathColumn});
                }
            }
        }

        /*
        |------------------------------------------------------------------
        | Jika anggota tidak mengubah tautan dan tidak mengunggah file baru,
        | pertahankan bukti lama agar pengajuan ulang tidak kehilangan bukti.
        |------------------------------------------------------------------
        */
        $buktiLink = trim((string) $request->bukti_link);

        if ($buktiLink === '' && empty($buktiFileData) && !empty($aktivitas->bukti_link)) {
            $buktiLink = $aktivitas->bukti_link;
        }

        $payload = [
            'id_lab' => $kmAnggota->id_lab,
            'id_km_anggota' => $kmAnggota->id_km_anggota,
            'kategori_km' => $kmAnggota->kategori_km,
            'sub_kategori_km' => $kmAnggota->sub_kategori_km,
            'judul_aktivitas' => $request->judul_aktivitas,
            'deskripsi_singkat' => $request->deskripsi_singkat,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'bukti_link' => $buktiLink !== '' ? $buktiLink : null,
            'status_progress' => $statusBaru,
            'diajukan_pada' => $statusBaru === 'Submitted' ? now() : null,
            'diverifikasi_pada' => null,
            'diverifikasi_oleh' => null,
            'catatan_verifikasi' => null,
            'updated_at' => now(),
        ];

        DB::table('aktivitas_km')
            ->where('id_aktivitas', $id)
            ->where('id_user', $user->id_user)
            ->update(array_merge($payload, $buktiFileData));

        if ($statusBaru === 'Submitted') {
            AktivitasKmNotificationService::notifyAktivitasDisubmit($id);

            return redirect('/anggota/aktivitas-km')
                ->with('success', 'Aktivitas KM berhasil diperbarui dan diajukan untuk verifikasi Ketua Lab.');
        }

        return redirect('/anggota/aktivitas-km')
            ->with('success', 'Aktivitas KM berhasil diperbarui sebagai Sedang Berjalan.');
    }

    /**
     * Verifikasi aktivitas oleh Ketua Lab.
     * Hanya aktivitas milik Lab Ketua Lab yang sedang login dan berstatus
     * Submitted yang boleh disetujui atau ditolak.
     */
    public function verifikasi(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'keputusan' => 'required|in:Accepted,Rejected',
            'catatan_verifikasi' => 'nullable|string|max:1000',
        ]);

        $ketuaLab = auth()->user();

        $idLab = $ketuaLab->id_lab;

        if (! $idLab && !empty($ketuaLab->id_dosen)) {
            $idLab = DB::table('dosen')
                ->where('id_dosen', $ketuaLab->id_dosen)
                ->value('id_lab');
        }

        abort_unless($idLab, 403, 'Lab Riset Ketua Lab tidak ditemukan.');

        $aktivitas = DB::table('aktivitas_km')
            ->where('id_aktivitas', $id)
            ->where('id_lab', $idLab)
            ->first();

        if (! $aktivitas) {
            abort(404, 'Aktivitas KM tidak ditemukan atau bukan milik Lab Anda.');
        }

        if (($aktivitas->status_progress ?? null) !== 'Submitted') {
            return back()->with('error', 'Hanya aktivitas berstatus Diajukan yang dapat diverifikasi.');
        }

        $keputusan = (string) $request->keputusan;
        $catatan = trim((string) $request->catatan_verifikasi);

        if ($keputusan === 'Rejected' && $catatan === '') {
            return back()
                ->withErrors([
                    'catatan_verifikasi' => 'Alasan penolakan wajib diisi agar anggota mengetahui bagian yang perlu diperbaiki.',
                ])
                ->withInput();
        }

        $waktuVerifikasi = now();

        DB::table('aktivitas_km')
            ->where('id_aktivitas', $aktivitas->id_aktivitas)
            ->where('id_lab', $idLab)
            ->update([
                'status_progress' => $keputusan,
                'diverifikasi_pada' => $waktuVerifikasi,
                'diverifikasi_oleh' => $ketuaLab->id_user,
                'catatan_verifikasi' => $catatan !== '' ? $catatan : null,
                'updated_at' => $waktuVerifikasi,
            ]);

        $this->catatRiwayatVerifikasi(
            $aktivitas,
            (int) $idLab,
            (int) $ketuaLab->id_user,
            $keputusan,
            $catatan !== '' ? $catatan : null,
            $waktuVerifikasi
        );

        $aktivitas->status_progress = $keputusan;
        $aktivitas->catatan_verifikasi = $catatan !== '' ? $catatan : null;

        $this->kirimNotifikasiKeAnggotaSetelahVerifikasi($aktivitas);

        return back()->with(
            'success',
            $keputusan === 'Accepted'
                ? 'Aktivitas KM berhasil disetujui dan kini dihitung sebagai realisasi.'
                : 'Aktivitas KM telah ditolak. Anggota menerima catatan verifikasi untuk diperbaiki.'
        );
    }

    /**
     * Menyimpan jejak setiap keputusan verifikasi agar dapat ditampilkan pada
     * Dashboard dan Monitoring Anggota, termasuk ketika sebuah aktivitas
     * ditolak lalu diajukan kembali oleh anggota.
     */
    private function catatRiwayatVerifikasi(
        object $aktivitas,
        int $idLab,
        int $idVerifikator,
        string $keputusan,
        ?string $catatan,
        $waktuVerifikasi
    ): void {
        if (! Schema::hasTable('riwayat_verifikasi_aktivitas_km')) {
            return;
        }

        DB::table('riwayat_verifikasi_aktivitas_km')->insert([
            'id_aktivitas' => $aktivitas->id_aktivitas,
            'id_user_anggota' => $aktivitas->id_user,
            'id_lab' => $idLab,
            'id_verifikator' => $idVerifikator,
            'keputusan' => $keputusan,
            'catatan_verifikasi' => $catatan,
            'created_at' => $waktuVerifikasi,
            'updated_at' => $waktuVerifikasi,
        ]);
    }

    private function findKmAnggotaMilikUser(int $idKmAnggota, int $idUser): ?object
    {
        return DB::table('km_anggota')
            ->join('km_lab', 'km_anggota.id_km_lab', '=', 'km_lab.id_km_lab')
            ->where('km_anggota.id_km_anggota', $idKmAnggota)
            ->where('km_anggota.id_user', $idUser)
            ->select(
                'km_anggota.id_km_anggota',
                'km_lab.id_lab',
                'km_lab.kategori_km',
                'km_lab.sub_kategori_km'
            )
            ->first();
    }

    private function hasEvidence(Request $request, ?object $aktivitasLama = null): bool
    {
        return $request->hasFile('bukti_file')
            || filled($request->input('bukti_link'))
            || !empty($aktivitasLama?->bukti_file_path)
            || !empty($aktivitasLama?->bukti_pdf_path)
            || !empty($aktivitasLama?->bukti_link);
    }

    /**
     * Notifikasi balasan untuk anggota setelah Ketua Lab mengambil keputusan.
     */
    private function kirimNotifikasiKeAnggotaSetelahVerifikasi(object $aktivitas): void
    {
        if (!Schema::hasTable('notifikasi')) {
            return;
        }

        $status = (string) ($aktivitas->status_progress ?? '');
        $disetujui = $status === 'Accepted';

        $judulAktivitas = trim((string) ($aktivitas->judul_aktivitas ?? 'Aktivitas KM'));
        $catatan = trim((string) ($aktivitas->catatan_verifikasi ?? ''));

        $pesan = $disetujui
            ? "Aktivitas KM \"{$judulAktivitas}\" telah disetujui oleh Ketua Lab dan sekarang dihitung sebagai realisasi."
            : "Aktivitas KM \"{$judulAktivitas}\" ditolak oleh Ketua Lab. "
                . ($catatan !== '' ? "Catatan: {$catatan}" : 'Silakan perbaiki data atau bukti lalu ajukan ulang.');

        $columns = Schema::getColumnListing('notifikasi');

        $payload = [
            'id_user' => $aktivitas->id_user,
            'jenis_notifikasi' => $disetujui
                ? 'aktivitas_km_disetujui'
                : 'aktivitas_km_ditolak',
            'judul' => $disetujui
                ? 'Aktivitas KM Disetujui'
                : 'Aktivitas KM Ditolak',
            'pesan' => $pesan,
            'url_tujuan' => '/anggota/aktivitas-km/' . (int) ($aktivitas->id_aktivitas ?? 0) . '/detail',
            'dibaca_pada' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        /*
        | Kolom kode_unik pada tabel notifikasi di project ini bersifat NOT NULL.
        | Kode dibuat hanya ketika kolom tersebut memang tersedia agar tetap
        | kompatibel dengan struktur database lain.
        */
        if (in_array('kode_unik', $columns, true)) {
            $payload['kode_unik'] = sprintf(
                'AKM-%s-%s-%s',
                $disetujui ? 'ACC' : 'TOLAK',
                (int) ($aktivitas->id_aktivitas ?? 0),
                Str::upper(Str::random(10))
            );
        }

        $payload = array_filter(
            $payload,
            fn ($value, $column) => in_array($column, $columns, true),
            ARRAY_FILTER_USE_BOTH
        );

        if (isset($payload['id_user']) && count($payload) > 1) {
            DB::table('notifikasi')->insert($payload);
        }
    }
}
