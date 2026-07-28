@extends('layouts.app')

@section('title', 'Detail KM Lab Riset')

@section('content')
@php
    $daftarKmTurun = collect($daftarKmTurun ?? []);
    $rekapKategori = collect($rekapKategori ?? []);
    $riwayatAssign = collect($riwayatAssign ?? []);
    $anggota = collect($anggota ?? []);
@endphp

<style>
    .ketuakk-lab-detail__overview,.ketuakk-lab-detail__section { overflow:hidden; border:1px solid #E2E8F0; border-radius:14px; background:#FFF; margin-bottom:16px; }
    .ketuakk-lab-detail__header { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; padding:20px 22px; border-bottom:1px solid #E2E8F0; flex-wrap:wrap; }
    .ketuakk-lab-detail__title { margin:0; color:#0F172A; font-size:22px; font-weight:700; }
    .ketuakk-lab-detail__description { margin:5px 0 0; color:#64748B; font-size:13px; }
    .ketuakk-lab-detail__section-title { margin:0; padding:15px 20px; border-bottom:1px solid #E2E8F0; background:#F8FAFC; color:#0F172A; font-size:14px; font-weight:700; }
    .ketuakk-lab-detail__table { border:0!important; border-radius:0; }
    .ketuakk-lab-detail__table th,.ketuakk-lab-detail__table td { padding:11px 16px!important; border-bottom:1px solid #EEF2F7!important; }
    .km-table th {
        white-space: nowrap;
        vertical-align: middle;
        font-size: 13px;
    }

    .km-table td {
        vertical-align: middle;
        font-size: 13px;
    }

    .progress-soft {
        height: 10px;
        border-radius: 999px;
        background: #E5E7EB;
        overflow: hidden;
        min-width: 120px;
    }

    .progress-soft-fill {
        height: 100%;
        border-radius: 999px;
        background: #477EF7;
    }
</style>

<section class="ketuakk-lab-detail__overview" aria-labelledby="lab-detail-title">
    <header class="ketuakk-lab-detail__header">
        <div>
            <h1 id="lab-detail-title" class="ketuakk-lab-detail__title">{{ $lab->nama_lab ?? '-' }}</h1>
            <p class="ketuakk-lab-detail__description">
                Rekap penurunan dan pembagian KM Lab Riset tahun {{ $tahun ?? now()->year }}.
            </p>
        </div>

        <a href="/ketuakk/km-lab-riset?tahun={{ $tahun }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </header>
</section>

<section class="ketuakk-lab-detail__section">
    <h2 class="ketuakk-lab-detail__section-title">Daftar KM Turun dari Kelompok Keahlian</h2>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table ketuakk-lab-detail__table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori KM</th>
                    <th>Jenis KM</th>
                    <th>Sub Kategori</th>
                    <th>Keterangan</th>
                    <th>TW 1</th>
                    <th>TW 2</th>
                    <th>TW 3</th>
                    <th>TW 4</th>
                    <th>Total KM Turun</th>
                    <th>Sudah Assign</th>
                    <th>Sisa</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($daftarKmTurun as $index => $km)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $km['kategori_km'] ?? '-' }}</td>
                        <td>{{ $km['jenis_km'] ?? '-' }}</td>
                        <td>{{ $km['sub_kategori_km'] ?? '-' }}</td>

                        <td style="min-width: 220px;">
                            {{ $km['keterangan'] ?? '-' }}
                        </td>

                        <td>{{ $km['triwulan_1'] ?? 0 }}</td>
                        <td>{{ $km['triwulan_2'] ?? 0 }}</td>
                        <td>{{ $km['triwulan_3'] ?? 0 }}</td>
                        <td>{{ $km['triwulan_4'] ?? 0 }}</td>

                        <td class="fw-bold">{{ $km['jumlah_km'] ?? 0 }}</td>
                        <td>{{ $km['sudah_assign'] ?? 0 }}</td>
                        <td>{{ $km['sisa_km'] ?? 0 }}</td>

                        <td>
                            {{ !empty($km['created_at']) ? \Carbon\Carbon::parse($km['created_at'])->format('d/m/Y') : '-' }}
                        </td>

                        <td>
                            <form
                                action="/ketuakk/km-lab-riset/{{ $km['id_km_lab'] }}"
                                method="POST"
                                class="js-delete-form"
                                data-message="Apakah Anda yakin ingin menghapus KM turun ini? Semua assign anggota yang terkait juga akan terhapus.">

                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-delete btn-sm w-100">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center text-muted py-4">
                            Belum ada KM yang diberikan ke Lab Riset ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Rekap KM per Kategori</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori KM</th>
                    <th>Total Target</th>
                    <th>Sudah Assign</th>
                    <th>Sisa</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rekapKategori as $index => $item)
                    @php
                        $total = $item['total_km'] ?? 0;
                        $assign = $item['sudah_assign'] ?? 0;
                        $sisa = $item['sisa_km'] ?? max($total - $assign, 0);
                        $persen = $item['persentase'] ?? ($total > 0 ? round(($assign / $total) * 100) : 0);
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $item['kategori'] }}</td>
                        <td>{{ $total }}</td>
                        <td>{{ $assign }}</td>
                        <td>{{ $sisa }}</td>

                        <td>
                            <div class="progress-soft mb-1">
                                <div class="progress-soft-fill" style="width: {{ min($persen, 100) }}%;"></div>
                            </div>

                            <div class="small text-muted">
                                {{ min($persen, 100) }}%
                            </div>
                        </td>

                        <td>
                            @if($total > 0 && $sisa <= 0)
                                <span class="badge bg-success">Selesai</span>
                            @else
                                <span class="badge bg-warning text-dark">Belum</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Belum ada data KM pada Lab Riset ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Riwayat Assign KM ke Anggota</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori KM</th>
                    <th>Sub Kategori</th>
                    <th>Nama Anggota</th>
                    <th>NIDN</th>
                    <th>JAD</th>
                    <th>Jumlah KM</th>
                    <th>Tanggal</th>
                </tr>
            </thead>

            <tbody>
                @forelse($riwayatAssign as $index => $assign)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $assign->kategori_km }}</td>
                        <td>{{ $assign->sub_kategori_km ?? '-' }}</td>
                        <td>{{ $assign->nama_dosen ?? $assign->username }}</td>
                        <td>{{ $assign->nidn ?? '-' }}</td>

                        <td>
                            <span class="badge bg-primary">
                                {{ $assign->jad ?? 'AA' }}
                            </span>
                        </td>

                        <td>{{ $assign->jumlah_km }}</td>
                        <td>{{ \Carbon\Carbon::parse($assign->created_at)->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Belum ada KM yang dibagikan ke anggota.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h4 class="fw-bold mb-3">Daftar Anggota Lab</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Anggota</th>
                    <th>NIDN</th>
                    <th>Email</th>
                    <th>JAD</th>
                    <th>Total Assign</th>
                </tr>
            </thead>

            <tbody>
                @forelse($anggota as $index => $item)
                    @php
                        $totalAssignAnggota = $riwayatAssign
                            ->where('nidn', $item->nidn)
                            ->sum('jumlah_km');
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $item->nama_dosen ?? $item->username }}</td>
                        <td>{{ $item->nidn ?? '-' }}</td>
                        <td>{{ $item->email ?? '-' }}</td>

                        <td>
                            <span class="badge bg-primary">
                                {{ $item->jad ?? 'AA' }}
                            </span>
                        </td>

                        <td>{{ $totalAssignAnggota }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Belum ada anggota pada Lab Riset ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
