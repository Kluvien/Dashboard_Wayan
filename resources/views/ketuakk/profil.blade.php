@extends('layouts.app')

@section('title', 'Profil Ketua KK')

@section('content')
<style>
    .ketuakk-profile { overflow:hidden; border:1px solid #E2E8F0; border-radius:14px; background:#FFF; }
    .ketuakk-profile__header { display:flex; justify-content:space-between; align-items:center; gap:18px; padding:20px 22px; border-bottom:1px solid #E2E8F0; flex-wrap:wrap; }
    .ketuakk-profile__identity { display:flex; align-items:center; gap:14px; }
    .ketuakk-profile__avatar { width:56px; height:56px; display:flex; align-items:center; justify-content:center; border:1px solid #BFDBFE; border-radius:10px; background:#EFF6FF; color:#1D4ED8; font-size:20px; font-weight:700; }
    .ketuakk-profile__eyebrow { color:#2563EB; font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
    .ketuakk-profile__title { margin:3px 0 0; color:#0F172A; font-size:22px; font-weight:700; }
    .ketuakk-profile__role { margin:3px 0 0; color:#64748B; font-size:13px; }
    .ketuakk-profile__back { min-height:38px; display:inline-flex; align-items:center; padding:0 13px; border:1px solid #CBD5E1; border-radius:8px; color:#334155; text-decoration:none; font-size:12px; font-weight:700; }
    .ketuakk-profile__section-title { padding:13px 20px; border-bottom:1px solid #EEF2F7; background:#F8FAFC; color:#334155; font-size:12px; font-weight:700; }
    .ketuakk-profile__details { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); }
    .ketuakk-profile__item { padding:14px 20px; border-right:1px solid #EEF2F7; border-bottom:1px solid #EEF2F7; }
    .ketuakk-profile__label { display:block; color:#64748B; font-size:11px; }
    .ketuakk-profile__value { display:block; margin-top:4px; color:#0F172A; font-size:13px; font-weight:600; overflow-wrap:anywhere; }
    @media(max-width:640px){.ketuakk-profile__details{grid-template-columns:1fr}.ketuakk-profile__item{border-right:0}}
</style>
<section class="ketuakk-profile" aria-labelledby="ketuakk-profile-title">
    <header class="ketuakk-profile__header">
    <div class="ketuakk-profile__identity">
        <div class="ketuakk-profile__avatar">
            {{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}
        </div>
        <div>
            <div class="ketuakk-profile__eyebrow">Profil Ketua KK</div>
            <h1 id="ketuakk-profile-title" class="ketuakk-profile__title">{{ $user->username ?? '-' }}</h1>
            <p class="ketuakk-profile__role">{{ $user->role ?? 'Ketua KK' }}</p>
        </div>
    </div>
    <a href="{{ url()->previous() }}" class="ketuakk-profile__back"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </header>
    <div class="ketuakk-profile__section-title">Informasi profil</div>
    <div class="ketuakk-profile__details">
        @foreach([
            'Username' => ($user->username ?? '-'),
            'Role' => ($user->role ?? '-'),
            'Nama Dosen' => ($dosen->nama_dosen ?? '-'),
            'NIDN' => ($dosen->nidn ?? '-'),
            'Email' => ($dosen->email ?? '-'),
            'JAD' => ($dosen->jad ?? 'AA'),
            'Lab Riset' => ($lab->nama_lab ?? '-'),
        ] as $label => $profileValue)
            <div class="ketuakk-profile__item">
                <span class="ketuakk-profile__label">{{ $label }}</span>
                <span class="ketuakk-profile__value">{{ $profileValue }}</span>
            </div>
        @endforeach
    </div>
</section>
@endsection
