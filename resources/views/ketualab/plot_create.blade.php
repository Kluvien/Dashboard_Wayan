@extends('layouts.app')

@section('title', 'Plot Anggota Target KM')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">Distribusi Target ke Anggota Laboratorium</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info border-0 mb-4">
                    <strong>Indikator Target:</strong> {{ $target->indikator }} <br>
                    <strong>Volume Target:</strong> {{ $target->target }}
                </div>

                <form action="/ketualab/penurunan-km/{{ $target->id_target }}/plot" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilih Dosen (Anggota Lab)</label>
                        <select name="id_dosen" class="form-select" required>
                            <option value="">-- Pilih Anggota yang Bertanggung Jawab --</option>
                            @foreach($anggotas as $anggota)
                                <option value="{{ $anggota->id_dosen }}">{{ $anggota->username }} (NIDN: {{ $anggota->id_dosen }})</option>
                            @endforeach
                        </select>
                        <div class="form-text">Pilih anggota yang akan mengeksekusi target ini.</div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary">Simpan Plotting</button>
                    <a href="/ketualab/penurunan-km" class="btn btn-light border">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection