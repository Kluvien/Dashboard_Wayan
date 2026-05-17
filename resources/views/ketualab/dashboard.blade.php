@extends('layouts.app')

@section('title', 'Dashboard Ketua Lab')

@section('content')
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card bg-info text-white shadow-sm border-0">
            <div class="card-body">
                <h6 class="card-title text-uppercase text-white-50">Total Anggota Lab</h6>
                <h2 class="mb-0 display-5 fw-bold">{{ $totalAnggota }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card bg-secondary text-white shadow-sm border-0">
            <div class="card-body">
                <h6 class="card-title text-uppercase text-white-50">Target Kontrak Manajemen</h6>
                <h2 class="mb-0 display-5 fw-bold">{{ $totalTarget }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-white fw-bold">
        Tugas Utama
    </div>
    <div class="card-body text-secondary">
        <p>Sebagai Ketua Laboratorium, Anda bertugas untuk menerima target KM dari Ketua KK dan menurunkannya (distribusi) ke masing-masing dosen anggota di laboratorium Anda.</p>
    </div>
</div>
@endsection