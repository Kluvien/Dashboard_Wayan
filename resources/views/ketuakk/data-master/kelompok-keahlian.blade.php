@extends('layouts.app')

@section('title', 'Data Kelompok Keahlian')

@section('content')
<style>
    .ketuakk-expertise { overflow:hidden; border:1px solid #E2E8F0; border-radius:14px; background:#FFF; }
    .ketuakk-expertise__header { padding:20px 22px; border-bottom:1px solid #E2E8F0; background:#F8FAFC; }
    .ketuakk-expertise__eyebrow { color:#2563EB; font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
    .ketuakk-expertise__title { margin:4px 0 0; color:#0F172A; font-size:22px; font-weight:700; }
    .ketuakk-expertise__description { margin:5px 0 0; color:#64748B; font-size:13px; }
    .ketuakk-expertise__scroll { overflow-x:auto; }
    .ketuakk-expertise__table { width:100%; margin:0; border:0; border-collapse:collapse; }
    .ketuakk-expertise__table th,.ketuakk-expertise__table td { padding:11px 16px; border-bottom:1px solid #EEF2F7; color:#334155; font-size:13px; }
    .ketuakk-expertise__table th { background:#F8FAFC; color:#475569; font-size:11px; font-weight:700; text-transform:uppercase; }
    .ketuakk-expertise__table tbody tr:hover td { background:#F8FAFC; }
    .ketuakk-expertise__index { width:70px; text-align:center; }
    .ketuakk-expertise__number { text-align:right; font-weight:700; font-variant-numeric:tabular-nums; }
    .ketuakk-expertise__identity { color:#0F172A!important; font-weight:700; }
    .ketuakk-expertise__empty { padding:24px 16px!important; color:#64748B!important; text-align:center; }
</style>
<section class="ketuakk-expertise" aria-labelledby="expertise-title">
    <header class="ketuakk-expertise__header">
        <div class="ketuakk-expertise__eyebrow">Data Master Ketua KK</div>
        <h1 id="expertise-title" class="ketuakk-expertise__title">Data Kelompok Keahlian</h1>
        <p class="ketuakk-expertise__description">Kelompok Keahlian yang menjadi induk Laboratorium Riset.</p>
    </header>
    <div class="table-responsive ketuakk-expertise__scroll">
    <table class="ketuakk-expertise__table">
        <thead>
            <tr>
                <th scope="col" class="ketuakk-expertise__index">No</th>
                <th scope="col" class="text-end">ID KK</th>
                <th scope="col">Nama Kelompok Keahlian</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kelompokKeahlian as $index => $kk)
                <tr>
                    <td class="ketuakk-expertise__index">{{ $index + 1 }}</td>
                    <td class="ketuakk-expertise__number">{{ $kk->id_kk }}</td>
                    <td class="ketuakk-expertise__identity">{{ $kk->nama_kk }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="ketuakk-expertise__empty">Belum ada data kelompok keahlian.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</section>
@endsection
