@extends('layouts.app')

@section('title', 'Tambah Target KM')

@section('content')
<div class="page-heading">
    Tambah <span class="muted">Target KM</span>
</div>

@include('ketuakk.partials.target-form', [
    'formTitle' => 'Form Tambah Target KM',
    'formAction' => '/ketuakk/target-km',
    'httpMethod' => 'POST',
    'submitLabel' => 'Simpan Target',
    'kategoriOptions' => $kategoriOptions,
    'target' => null,
])
@endsection