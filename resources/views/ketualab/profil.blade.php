@extends('layouts.app')

@section('title', 'Profil Ketua Lab')

@section('content')
<style>
    .ketualab-profile { background: #FFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; }
    .ketualab-profile__header { display: flex; justify-content: space-between; gap: 20px; align-items: flex-start; padding: 20px 22px; border-bottom: 1px solid #EEF2F7; }
    .ketualab-profile__eyebrow { margin: 0 0 5px; color: #2563EB; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .ketualab-profile__title { margin: 0; color: #0F172A; font-size: 24px; font-weight: 700; }
    .ketualab-profile__description { margin: 7px 0 0; color: #5B6472; font-size: 14px; line-height: 1.5; }
    .ketualab-profile__back { flex: 0 0 auto; min-height: 40px; padding: 8px 14px; border-color: #D5DCE5; border-radius: 8px; color: #374151; font-size: 14px; font-weight: 600; }
    .ketualab-profile__grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .ketualab-profile__item { min-height: 88px; padding: 17px 22px; border-right: 1px solid #EEF2F7; }
    .ketualab-profile__item:last-child { border-right: 0; }
    .ketualab-profile__label { display: block; margin-bottom: 7px; color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .ketualab-profile__value { color: #1F2937; font-size: 14px; line-height: 1.5; font-weight: 600; overflow-wrap: anywhere; }
    @media (max-width: 767.98px) {
        .ketualab-profile__header { flex-direction: column; }
        .ketualab-profile__grid { grid-template-columns: 1fr; }
        .ketualab-profile__item { border-right: 0; border-bottom: 1px solid #EEF2F7; }
        .ketualab-profile__item:last-child { border-bottom: 0; }
    }
</style>

<section class="ketualab-profile" aria-labelledby="ketualab-profile-title">
    <header class="ketualab-profile__header">
        <div>
            <p class="ketualab-profile__eyebrow">Akun Ketua Lab</p>
            <h1 id="ketualab-profile-title" class="ketualab-profile__title">Profil Ketua Lab</h1>
            <p class="ketualab-profile__description">Informasi akun dan Laboratorium Riset yang dipimpin.</p>
        </div>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary ketualab-profile__back">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </header>
    <div class="ketualab-profile__grid">
        <div class="ketualab-profile__item"><span class="ketualab-profile__label">Username</span><span class="ketualab-profile__value">{{ $user->username }}</span></div>
        <div class="ketualab-profile__item"><span class="ketualab-profile__label">Role</span><span class="ketualab-profile__value">{{ $user->role }}</span></div>
        <div class="ketualab-profile__item"><span class="ketualab-profile__label">Laboratorium Riset</span><span class="ketualab-profile__value">{{ $lab->nama_lab ?? '-' }}</span></div>
    </div>
</section>
@endsection
