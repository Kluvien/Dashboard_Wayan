@extends('layouts.app')

@section('title', 'Riwayat Realisasi')

@section('content')
<style>
    .anggota-history { background: #FFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; }
    .anggota-history__header { display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; padding: 20px 22px; border-bottom: 1px solid #E2E8F0; }
    .anggota-history__eyebrow { margin: 0 0 5px; color: #2563EB; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .anggota-history__title { margin: 0; color: #0F172A; font-size: 24px; font-weight: 700; }
    .anggota-history__description { margin: 7px 0 0; color: #64748B; font-size: 13px; }
    .anggota-history__back { padding: 7px 13px; border-color: #CBD5E1; border-radius: 8px; color: #334155; font-size: 13px; font-weight: 700; white-space: nowrap; }
    .anggota-history__scroll { overflow-x: auto; }
    .anggota-history__table { width: 100%; min-width: 900px; margin: 0; }
    .anggota-history__table > thead > tr > th { padding: 11px 16px; background: #F8FAFC; border: 0; border-bottom: 1px solid #CBD5E1; color: #334155; font-size: 11px; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; vertical-align: middle; }
    .anggota-history__table > tbody > tr > td { min-height: 56px; padding: 11px 16px; border: 0; border-bottom: 1px solid #EEF2F7; color: #334155; font-size: 13px; font-weight: 500; vertical-align: middle; }
    .anggota-history__table > tbody > tr:last-child > td { border-bottom: 0; }
    .anggota-history__table > tbody > tr:hover > td { background: #F8FAFC; }
    .anggota-history__number { text-align: center; font-variant-numeric: tabular-nums; }
    .anggota-history__identity { color: #0F172A; font-weight: 600; }
    .anggota-history__date { text-align: center; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .anggota-history__empty { padding: 24px 16px !important; color: #64748B !important; font-size: 13px !important; text-align: center; }
    @media (max-width: 575.98px) { .anggota-history__header { flex-direction: column; } }
</style>

<section class="anggota-history" aria-labelledby="anggota-history-title">
    <header class="anggota-history__header">
        <div>
            <p class="anggota-history__eyebrow">Aktivitas KM</p>
            <h1 id="anggota-history-title" class="anggota-history__title">Riwayat Realisasi KM</h1>
            <p class="anggota-history__description">Seluruh aktivitas yang telah Anda input sebagai realisasi Kontrak Manajemen.</p>
        </div>
        <a href="/anggota/dashboard" class="btn btn-outline-secondary anggota-history__back"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </header>
    <div class="table-responsive anggota-history__scroll">
        <table class="table anggota-history__table">
            <thead>
                <tr>
                    <th scope="col" class="anggota-history__number">No</th>
                    <th scope="col">Kategori KM</th>
                    <th scope="col">Judul Aktivitas</th>
                    <th scope="col">Deskripsi</th>
                    <th scope="col">Tanggal Mulai</th>
                    <th scope="col">Tanggal Selesai</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aktivitas as $index => $item)
                <tr>
                    <td class="anggota-history__number">{{ $index + 1 }}</td>
                    <td class="anggota-history__identity">{{ $item->kategori_km ?? '-' }}</td>
                    <td>{{ $item->judul_aktivitas ?? '-' }}</td>
                    <td>{{ $item->deskripsi_singkat ?? '-' }}</td>
                    <td class="anggota-history__date">{{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') }}</td>
                    <td class="anggota-history__date">{{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="anggota-history__empty">Belum ada riwayat realisasi KM.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
