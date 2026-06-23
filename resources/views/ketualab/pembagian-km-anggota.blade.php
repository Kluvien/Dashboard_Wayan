@extends('layouts.app')

@section('title', 'Pembagian KM Anggota')

@section('content')
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
        <table class="table align-middle mb-0" style="table-layout: fixed; width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 6%;">No</th>
                    <th style="width: 22%;">Kategori KM</th>
                    <th style="width: 13%;">Total KM</th>
                    <th style="width: 14%;">Sudah Assign</th>
                    <th style="width: 13%;">Sisa KM</th>
                    <th style="width: 16%;">Status</th>
                    <th style="width: 16%;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($dataKmLab as $index => $km)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="fw-bold">
                        {{ $km['kategori_km'] }}
                    </td>

                    <td>
                        {{ $km['jumlah_km'] }}
                    </td>

                    <td>
                        {{ $km['sudah_assign'] }}
                    </td>

                    <td>
                        {{ $km['sisa_km'] }}
                    </td>

                    <td>
                        @if($km['status'] === 'Sudah Dibagi')
                        <span class="badge bg-success">Sudah Dibagi</span>
                        @else
                        <span class="badge bg-warning text-dark">Belum Selesai</span>
                        @endif
                    </td>

                    <td>
                        @if($km['sisa_km'] > 0)
                        <button
                            type="button"
                            class="btn btn-primary btn-sm js-open-assign-modal"
                            data-id-km-lab="{{ $km['id_km_lab'] }}"
                            data-kategori="{{ $km['kategori_km'] }}"
                            data-sisa="{{ $km['sisa_km'] }}"
                            data-bs-toggle="modal"
                            data-bs-target="#assignKmModal">
                            Bagi
                        </button>
                        @else
                        <span class="text-muted">Selesai</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Belum ada KM yang diturunkan Ketua KK ke lab ini.
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
        <table class="table align-middle mb-0" style="table-layout: fixed; width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 18%;">Kategori KM</th>
                    <th style="width: 25%;">Nama Anggota</th>
                    <th style="width: 13%;">NIDN</th>
                    <th style="width: 12%;">JAD</th>
                    <th style="width: 12%;">Jumlah KM</th>
                    <th style="width: 15%;">Tanggal Assign</th>
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
                        {{ $assign->nama_dosen ?? $assign->username }}
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
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
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
        <table class="table align-middle mb-0" style="table-layout: fixed; width: 100%; font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 25%;">Nama Anggota</th>
                    <th style="width: 15%;">NIDN</th>
                    <th style="width: 25%;">Email</th>
                    <th style="width: 15%;">JAD</th>
                    <th style="width: 15%;">Bobot Saran</th>
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
                ];

                $bobotJad = [
                'GB' => 1.4,
                'LK' => 1.2,
                'L' => 1.0,
                'AA' => 0.8,
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
                            {{ $jadLabel[$jad] ?? 'Asisten Ahli' }}
                        </div>
                    </td>

                    <td>
                        {{ $bobotJad[$jad] ?? 0.8 }}
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
                                - {{ $item->jad ?? 'AA' }}
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