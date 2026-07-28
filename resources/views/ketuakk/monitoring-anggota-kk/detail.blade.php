@extends('layouts.app')

@section('title', 'Detail Monitoring Anggota KK')

@section('content')
    @include('ketuakk.partials.detail-anggota-km', [
        'pageTitle' => 'Monitoring',
        'pageMuted' => 'Anggota KK',
        'detailDescription' => 'Rincian target, realisasi, dan aktivitas anggota pada periode monitoring.',
    ])
@endsection
