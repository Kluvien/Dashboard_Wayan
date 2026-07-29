@extends('layouts.app')

@section('title', 'Profil Anggota')

@section('content')
<style>
    .anggota-profile { background: #FFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; }
    .anggota-profile__header { display: flex; justify-content: space-between; gap: 20px; align-items: flex-start; padding: 20px 22px; border-bottom: 1px solid #EEF2F7; }
    .anggota-profile__eyebrow { margin: 0 0 5px; color: #2563EB; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .anggota-profile__title { margin: 0; color: #0F172A; font-size: 24px; font-weight: 700; }
    .anggota-profile__description { margin: 7px 0 0; color: #5B6472; font-size: 14px; line-height: 1.5; }
    .anggota-profile__back { min-height: 40px; padding: 8px 14px; border-color: #D5DCE5; border-radius: 8px; color: #374151; font-size: 14px; font-weight: 600; white-space: nowrap; }
    .anggota-profile__grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .anggota-profile__item { min-height: 86px; padding: 16px 22px; border-right: 1px solid #EEF2F7; border-bottom: 1px solid #EEF2F7; }
    .anggota-profile__item:nth-child(3n) { border-right: 0; }
    .anggota-profile__item:nth-last-child(-n+3) { border-bottom: 0; }
    .anggota-profile__label { display: block; margin-bottom: 7px; color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .anggota-profile__value { color: #1F2937; font-size: 14px; line-height: 1.5; font-weight: 600; overflow-wrap: anywhere; }
    @media (max-width: 767.98px) {
        .anggota-profile__header { flex-direction: column; }
        .anggota-profile__grid { grid-template-columns: 1fr; }
        .anggota-profile__item, .anggota-profile__item:nth-child(3n), .anggota-profile__item:nth-last-child(-n+3) { border-right: 0; border-bottom: 1px solid #EEF2F7; }
        .anggota-profile__item:last-child { border-bottom: 0; }
    }
</style>

<section class="anggota-profile" aria-labelledby="anggota-profile-title">
    <header class="anggota-profile__header">
        <div>
            <p class="anggota-profile__eyebrow">Akun Anggota</p>
            <h1 id="anggota-profile-title" class="anggota-profile__title">Profil Anggota</h1>
            <p class="anggota-profile__description">Informasi akun, identitas dosen, dan Laboratorium Riset.</p>
        </div>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary anggota-profile__back">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </header>
    <div class="anggota-profile__grid">
        <div class="anggota-profile__item"><span class="anggota-profile__label">Username</span><span class="anggota-profile__value">{{ $user->username }}</span></div>
        <div class="anggota-profile__item"><span class="anggota-profile__label">Role</span><span class="anggota-profile__value">{{ $user->role }}</span></div>
        <div class="anggota-profile__item"><span class="anggota-profile__label">Nama Dosen</span><span class="anggota-profile__value">{{ $dosen->nama_dosen ?? '-' }}</span></div>
        <div class="anggota-profile__item"><span class="anggota-profile__label">NIDN</span><span class="anggota-profile__value">{{ $dosen->nidn ?? '-' }}</span></div>
        <div class="anggota-profile__item"><span class="anggota-profile__label">Email</span><span class="anggota-profile__value">{{ $dosen->email ?? '-' }}</span></div>
        <div class="anggota-profile__item"><span class="anggota-profile__label">Laboratorium Riset</span><span class="anggota-profile__value">{{ $lab->nama_lab ?? '-' }}</span></div>
    </div>
</section>
@endsection
