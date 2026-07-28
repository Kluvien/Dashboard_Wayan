@extends('layouts.app')

@section('title', 'Edit Target KM')

@section('content')
@include('ketuakk.partials.target-form', [
    'formTitle' => 'Form Edit Target KM',
    'formAction' => '/ketuakk/target-km/' . $target->id_target,
    'httpMethod' => 'PUT',
    'submitLabel' => 'Perbarui Target',
    'kategoriOptions' => $kategoriOptions,
    'target' => $target,
])
@endsection
