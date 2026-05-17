@extends('layouts.app')

@section('title', 'Dashboard Ketua KK')

@section('content')
<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card bg-primary text-white shadow-sm border-0">
            <div class="card-body">
                <h6 class="card-title text-uppercase text-white-50">Total Laboratorium</h6>
                <h2 class="mb-0 display-5 fw-bold">4</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-success text-white shadow-sm border-0">
            <div class="card-body">
                <h6 class="card-title text-uppercase text-white-50">Dosen Anggota</h6>
                <h2 class="mb-0 display-5 fw-bold">12</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-warning text-dark shadow-sm border-0">
            <div class="card-body">
                <h6 class="card-title text-uppercase text-black-50">Rata-rata Capaian KM</h6>
                <h2 class="mb-0 display-5 fw-bold">78%</h2>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-white fw-bold">
        Informasi Sistem
    </div>
    <div class="card-body text-secondary">
        <p>Selamat datang di Sistem Informasi Kontrak Manajemen (SIKM). Sebagai Ketua Kelompok Keahlian, Anda memiliki akses penuh untuk memantau progres seluruh laboratorium riset di bawah naungan Anda.</p>
    </div>
</div>
@endsection