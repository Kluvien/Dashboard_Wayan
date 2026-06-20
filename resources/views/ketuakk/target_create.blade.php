@extends('layouts.app')

@section('title', 'Tambah Target KM')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">Form Input Target Baru</h6>
            </div>
            <div class="card-body">
                <form action="/ketuakk/target-km" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Indikator Target</label>
                        <input type="text" name="indikator" class="form-control" placeholder="Contoh: Jumlah Publikasi Jurnal Internasional" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nilai Target (Angka / Teks)</label>
                        <input type="text" name="target" class="form-control" placeholder="Contoh: 5" required>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary">Simpan Target</button>
                    <a href="/ketuakk/target-km" class="btn btn-light border">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection