@extends('layouts.app')

@section('title', 'Pembagian KM Anggota')

@section('content')
<style>
    .km-table th {
        white-space: nowrap;
        vertical-align: middle;
    }

    .km-table td {
        vertical-align: middle;
    }

    .km-table .text-small-muted {
        font-size: 12px;
        color: #6c757d;
    }
</style>
<div class="page-heading">
    Pembagian <span class="muted">KM Anggota</span>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <div class="fw-bold mb-1">Terjadi kesalahan:</div>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">KM yang Diturunkan ke Lab</h4>
            <p class="text-muted mb-0">
                Lab: {{ $lab->nama_lab ?? '-' }} | Tahun: {{ $tahun }}
            </p>
        </div>

        <a href="/ketualab/dashboard" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="card mb-4">
    <h4 class="fw-bold mb-3">Daftar KM dari Ketua KK</h4>

    <div class="table-responsive">
        <table class="table align-middle mb-0 km-table" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori KM</th>
                    <th>Nama Anggota</th>
                    <th>NIDN</th>
                    <th>JAD</th>
                    <th>Jumlah KM</th>
                    <th>Tanggal Assign</th>
                </tr>
            </thead>

            <tbody>
                @forelse($dataKmLab as $index => $km)
                @php
                $totalKm = $km['jumlah_km'] ?? $km['total_km'] ?? 0;
                $sudahAssign = $km['sudah_assign'] ?? $km['total_assign'] ?? $km['jumlah_assign'] ?? 0;
                $sisaKm = $km['sisa_km'] ?? $km['sisa'] ?? max($totalKm - $sudahAssign, 0);
                $kategoriKm = $km['kategori_km'] ?? $km['kategori'] ?? '-';
                $idKmLab = $km['id_km_lab'] ?? null;
                @endphp

                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $kategoriKm }}
                    </td>

                    <td>
                        {{ $totalKm }}
                    </td>

                    <td>
                        {{ $sudahAssign }}
                    </td>

                    <td>
                        {{ $sisaKm }}
                    </td>

                    <td>
                        @if($sisaKm <= 0)
                            <span class="badge bg-success">Sudah Dibagi</span>
                            @else
                            <span class="badge bg-warning text-dark">Belum Selesai</span>
                            @endif
                    </td>

                    <td>
                        @if($sisaKm > 0)
                        <button
                            type="button"
                            class="btn btn-primary btn-sm js-open-assign-modal"
                            data-bs-toggle="modal"
                            data-bs-target="#assignKmModal"
                            data-id-km-lab="{{ $idKmLab }}"
                            data-kategori="{{ $kategoriKm }}"
                            data-sisa="{{ $sisaKm }}">
                            Bagi
                        </button>
                        @else
                        <button type="button" class="btn btn-secondary btn-sm" disabled>
                            Selesai
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Belum ada KM yang diturunkan ke lab.
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
        <table class="table align-middle mb-0 km-table" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori KM</th>
                    <th>Nama Anggota</th>
                    <th>NIDN</th>
                    <th>JAD</th>
                    <th>Jumlah KM</th>
                    <th>Tanggal Assign</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($riwayatAssign as $index => $assign)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $assign->kategori_km }}
                    </td>

                    <td>
                        {{ $assign->nama_dosen ?? $assign->username ?? '-' }}
                    </td>

                    <td>
                        {{ $assign->nidn ?? '-' }}
                    </td>

                    <td>
                        <span class="badge bg-primary">
                            {{ $assign->jad ?? 'AA' }}
                        </span>
                    </td>

                    <td>
                        {{ $assign->jumlah_km }}
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($assign->created_at)->format('d/m/Y') }}
                    </td>

                    <td>
                        <form
                            action="/ketualab/penurunan-km/assign/{{ $assign->id_km_anggota }}"
                            method="POST"
                            class="js-delete-form"
                            data-message="Apakah Anda yakin ingin menghapus assign KM ini?">
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
                    <td colspan="8" class="text-center text-muted py-4">
                        Belum ada riwayat assign KM.
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
        <table class="table align-middle mb-0 km-table" style="width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Anggota</th>
                    <th>NIDN</th>
                    <th>Email</th>
                    <th>JAD</th>
                    <th>Bobot Saran</th>
                </tr>
            </thead>

            <tbody>
                @forelse($anggota as $index => $item)
                @php
                $jad = $item->jad ?? 'AA';

                $jadLabel = [
                'GB' => 'Guru Besar',
                'LK' => 'Lektor Kepala',
                'L' => 'Lektor',
                'AA' => 'Asisten Ahli',
                'NJFA' => 'Non-Jabatan Fungsional Akademik',
                ];

                $bobotJad = [
                'GB' => 1.4,
                'LK' => 1.2,
                'L' => 1.0,
                'AA' => 0.8,
                'NJFA' => 0.6,
                ];
                @endphp

                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $item->nama_dosen ?? $item->username }}
                    </td>

                    <td>
                        {{ $item->nidn ?? '-' }}
                    </td>

                    <td>
                        {{ $item->email ?? '-' }}
                    </td>

                    <td>
                        <span class="badge bg-primary">{{ $jad }}</span>
                        <div class="small text-muted mt-1">
                            {{ $jadLabel[$jad] ?? 'Non-Jabatan Fungsional Akademik' }}
                        </div>
                    </td>

                    <td>
                        {{ $bobotJad[$jad] ?? 0.6 }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Belum ada anggota pada lab ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="assignKmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 18px;">
            <form action="/ketualab/penurunan-km" method="POST">
                @csrf

                <input type="hidden" name="id_km_lab" id="modalIdKmLab">

                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold">Bagi KM ke Anggota</h5>
                        <p class="text-muted mb-0 small">
                            Kategori: <span id="modalKategoriKm">-</span>
                        </p>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-3">
                    <div class="alert alert-info py-2">
                        Sisa KM tersedia:
                        <strong><span id="modalSisaKm">0</span></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Anggota</label>
                        <select name="id_user" class="form-select" required>
                            <option value="">-- Pilih Anggota --</option>

                            @foreach($anggota as $item)
                            <option value="{{ $item->id_user }}">
                                {{ $item->nama_dosen ?? $item->username }}
                                ({{ $item->username }}) - {{ $item->jad ?? 'AA' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Jumlah KM</label>
                        <input
                            type="number"
                            name="jumlah_km"
                            id="modalJumlahKm"
                            class="form-control"
                            min="1"
                            required
                            placeholder="Masukkan jumlah KM">
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Simpan Pembagian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.js-open-assign-modal');

        const inputIdKmLab = document.getElementById('modalIdKmLab');
        const textKategori = document.getElementById('modalKategoriKm');
        const textSisa = document.getElementById('modalSisaKm');
        const inputJumlah = document.getElementById('modalJumlahKm');

        buttons.forEach(function(button) {
            button.addEventListener('click', function() {
                const idKmLab = button.getAttribute('data-id-km-lab');
                const kategori = button.getAttribute('data-kategori');
                const sisa = button.getAttribute('data-sisa');

                inputIdKmLab.value = idKmLab;
                textKategori.textContent = kategori;
                textSisa.textContent = sisa;

                inputJumlah.value = '';
                inputJumlah.setAttribute('max', sisa);
            });
        });
    });
</script>
@endsection