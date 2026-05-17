@extends('layouts.app')

@section('title', 'Edit Target KM')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">Form Edit Target KM</h6>
            </div>
            <div class="card-body">
                <form action="/ketuakk/target-km/{{ $target->id_target }}" method="POST">
                    @csrf
                    @method('PUT') {{-- Wajib di Laravel untuk proses Update --}}
                    
                    <div class="mb-3">
                        <label class="form-label">Indikator Target</label>
                        <input type="text" name="indikator" class="form-control" value="{{ $target->indikator }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nilai Target (Angka Murni)</label>
                        <input type="number" name="target" class="form-control" value="{{ $target->target }}" required>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-warning text-white">Perbarui Target</button>
                    <a href="/ketuakk/target-km" class="btn btn-light border">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection