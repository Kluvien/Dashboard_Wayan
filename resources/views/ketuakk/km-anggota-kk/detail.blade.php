@extends('layouts.app')

@section('title', 'Detail KM Anggota KK')

@section('content')
    @include('ketuakk.partials.detail-anggota-km', [
        'pageTitle' => 'Kontrak Manajemen',
        'pageMuted' => 'Anggota KK',
        'detailDescription' => 'Rincian distribusi target KM dan aktivitas anggota.',
    ])
@endsection
