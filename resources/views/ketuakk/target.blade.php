@extends('layouts.app')

@section('title', 'Kelola Target KM')

@section('content')
<div class="page-heading">
    Kelola <span class="muted">Target KM</span>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Daftar Target Kontrak Manajemen</h4>
            <p class="text-muted mb-0">
                Halaman ini digunakan untuk mengelola target tahunan berdasarkan kategori, jenis KM, dan triwulan.
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <form action="/ketuakk/target-km" method="GET" class="d-flex gap-2">
                <select name="tahun" class="form-select" style="min-width: 130px;" onchange="this.form.submit()">
                    @foreach($tahunOptions as $itemTahun)
                    <option value="{{ $itemTahun }}" {{ (int) $tahun === (int) $itemTahun ? 'selected' : '' }}>
                        {{ $itemTahun }}
                    </option>
                    @endforeach
                </select>
            </form>

            <a href="/ketuakk/km-kk" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>

            <a href="/ketuakk/target-km/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Tambah Target
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0" style="font-size: 14px;">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 10%;">Tahun</th>
                    <th style="width: 15%;">Kategori KM</th>
                    <th>Jenis KM / Sub Kategori</th>
                    <th style="width: 9%;">TW 1</th>
                    <th style="width: 9%;">TW 2</th>
                    <th style="width: 9%;">TW 3</th>
                    <th style="width: 9%;">TW 4</th>
                    <th style="width: 10%;">Total</th>
                    <th style="width: 12%;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($targets as $index => $t)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td>{{ $t->tahun_km }}</td>

                    <td>
                        <span class="badge bg-primary">
                            {{ $t->kategori_km ?? '-' }}
                        </span>
                    </td>

                    <td class="fw-bold">
                        {{ $t->indikator }}
                    </td>

                    <td>{{ $t->triwulan_1 ?? 0 }}</td>
                    <td>{{ $t->triwulan_2 ?? 0 }}</td>
                    <td>{{ $t->triwulan_3 ?? 0 }}</td>
                    <td>{{ $t->triwulan_4 ?? 0 }}</td>

                    <td class="fw-bold">
                        {{ $t->target ?? 0 }}
                    </td>

                    <td>
                        <div class="d-flex gap-2">
                            <a href="/ketuakk/target-km/{{ $t->id_target }}/edit" class="btn btn-edit btn-sm">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <form
                                action="/ketuakk/target-km/{{ $t->id_target }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus target ini?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-delete btn-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        Belum ada data target KM untuk tahun {{ $tahun }}.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection