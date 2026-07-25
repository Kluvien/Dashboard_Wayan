@extends('layouts.app')

@section('title', 'Detail Status Pengajuan KM')

@section('content')
@php
    $riwayatVerifikasi = collect($riwayatVerifikasi ?? []);
    $status = $aktivitas->status_progress ?? '-';
    $disetujui = $status === 'Accepted';
    $ditolak = $status === 'Rejected';

    $statusLabel = match($status) {
        'Accepted' => 'Disetujui',
        'Rejected' => 'Ditolak',
        'Submitted' => 'Diajukan',
        'On Progress' => 'Sedang Berjalan',
        default => $status ?: '-',
    };

    $statusClass = $disetujui
        ? 'status-approved'
        : ($ditolak ? 'status-rejected' : 'status-pending');

    $tenggat = [
        1 => $aktivitas->tanggal_selesai_tw1 ?? null,
        2 => $aktivitas->tanggal_selesai_tw2 ?? null,
        3 => $aktivitas->tanggal_selesai_tw3 ?? null,
        4 => $aktivitas->tanggal_selesai_tw4 ?? null,
    ];
@endphp

<style>
    .member-detail-page { padding-bottom: 28px; }

    .member-detail-card {
        margin-bottom: 16px;
        padding: 18px;
        border: 1px solid #E2E8F0;
        border-radius: 17px;
        background: #FFFFFF;
        box-shadow: 0 5px 15px rgba(15, 23, 42, .04);
    }

    .member-detail-title {
        color: #0F172A;
        font-size: 20px;
        font-weight: 900;
        margin-bottom: 4px;
    }

    .member-detail-subtitle {
        margin: 0;
        color: #64748B;
        font-size: 13px;
    }

    .member-detail-btn {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 15px;
        border: 0;
        border-radius: 10px;
        background: #64748B;
        color: #FFFFFF;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
    }

    .member-detail-btn:hover { color: #FFFFFF; background: #475569; }

    .decision-banner {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 13px;
        align-items: flex-start;
        padding: 17px;
        border: 1px solid;
        border-radius: 15px;
    }

    .decision-banner.approved { color: #15803D; background: #F0FDF4; border-color: #BBF7D0; }
    .decision-banner.rejected { color: #B91C1C; background: #FFF1F2; border-color: #FECACA; }
    .decision-banner.pending { color: #B45309; background: #FFFBEB; border-color: #FDE68A; }

    .decision-banner-icon {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: rgba(255, 255, 255, .72);
        font-size: 22px;
    }

    .decision-banner-title { font-size: 18px; font-weight: 900; }
    .decision-banner-text { margin-top: 4px; font-size: 13px; font-weight: 700; line-height: 1.5; }

    .rejection-note {
        margin-top: 11px;
        padding: 12px;
        border-radius: 11px;
        background: rgba(255, 255, 255, .8);
        color: #7F1D1D;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.5;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .detail-block {
        min-height: 88px;
        padding: 13px;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        background: #F8FAFC;
    }

    .detail-block.full { grid-column: span 2; }
    .detail-label { color: #64748B; font-size: 11px; font-weight: 900; text-transform: uppercase; }
    .detail-value { margin-top: 6px; color: #0F172A; font-size: 14px; font-weight: 800; line-height: 1.45; }
    .detail-value.muted { color: #475569; font-weight: 700; }

    .deadline-list { display: flex; flex-wrap: wrap; gap: 6px; }
    .deadline-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 8px;
        border: 1px solid #DBEAFE;
        border-radius: 8px;
        background: #EFF6FF;
        color: #1D4ED8;
        font-size: 11px;
        font-weight: 800;
    }

    .section-title { color: #111827; font-size: 18px; font-weight: 900; margin-bottom: 3px; }
    .section-desc { margin: 0 0 14px; color: #64748B; font-size: 13px; }

    .history-table th, .history-table td {
        padding: 11px 10px;
        border-bottom: 1px solid #F1F5F9;
        font-size: 12px;
        vertical-align: middle;
    }

    .history-table th {
        color: #475569;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .decision-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .decision-pill.accepted { color: #15803D; background: #DCFCE7; }
    .decision-pill.rejected { color: #B91C1C; background: #FEE2E2; }

    @media (max-width: 700px) {
        .detail-grid { grid-template-columns: 1fr; }
        .detail-block.full { grid-column: span 1; }
    }
</style>

<div class="member-detail-page">
    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
        <div>
            <div class="page-heading mb-1">Detail <span class="muted">Status Pengajuan KM</span></div>
            <p class="member-detail-subtitle">Rincian aktivitas dan hasil verifikasi dari Ketua Lab.</p>
        </div>

        <a href="{{ route('anggota.dashboard') }}" class="member-detail-btn">
            <i class="bi bi-arrow-left"></i>
            Kembali ke Dashboard
        </a>
    </div>

    <div class="member-detail-card">
        <div class="decision-banner {{ $statusClass }}">
            <div class="decision-banner-icon">
                <i class="bi {{ $disetujui ? 'bi-check-circle-fill' : ($ditolak ? 'bi-x-circle-fill' : 'bi-hourglass-split') }}"></i>
            </div>

            <div>
                <div class="decision-banner-title">Aktivitas KM {{ $statusLabel }}</div>
                <div class="decision-banner-text">
                    {{ $disetujui
                        ? 'Aktivitas ini telah diverifikasi Ketua Lab dan dihitung sebagai realisasi KM.'
                        : ($ditolak
                            ? 'Aktivitas ini perlu diperbaiki sebelum dapat diajukan kembali kepada Ketua Lab.'
                            : 'Aktivitas masih berada dalam proses pengajuan atau verifikasi.') }}
                </div>

                @if($ditolak)
                    <div class="rejection-note">
                        <strong>Catatan Ketua Lab:</strong><br>
                        {{ $aktivitas->catatan_verifikasi ?: 'Belum ada catatan tambahan dari Ketua Lab.' }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="member-detail-card">
        <div class="section-title">Rincian Aktivitas</div>
        <p class="section-desc">Informasi aktivitas KM yang diajukan.</p>

        <div class="detail-grid">
            <div class="detail-block full">
                <div class="detail-label">Judul Aktivitas</div>
                <div class="detail-value">{{ $aktivitas->judul_aktivitas ?? '-' }}</div>
            </div>

            <div class="detail-block">
                <div class="detail-label">Kategori KM</div>
                <div class="detail-value">{{ $aktivitas->kategori_km ?? '-' }}</div>
            </div>

            <div class="detail-block">
                <div class="detail-label">Sub Kategori</div>
                <div class="detail-value">{{ $aktivitas->sub_kategori_km ?? '-' }}</div>
            </div>

            <div class="detail-block">
                <div class="detail-label">Tanggal Mulai</div>
                <div class="detail-value">{{ !empty($aktivitas->tanggal_mulai) ? \Carbon\Carbon::parse($aktivitas->tanggal_mulai)->format('d/m/Y') : '-' }}</div>
            </div>

            <div class="detail-block">
                <div class="detail-label">Tanggal Selesai</div>
                <div class="detail-value">{{ !empty($aktivitas->tanggal_selesai) ? \Carbon\Carbon::parse($aktivitas->tanggal_selesai)->format('d/m/Y') : '-' }}</div>
            </div>

            <div class="detail-block">
                <div class="detail-label">Diajukan Pada</div>
                <div class="detail-value">{{ !empty($aktivitas->diajukan_pada) ? \Carbon\Carbon::parse($aktivitas->diajukan_pada)->format('d/m/Y H:i') : '-' }}</div>
            </div>

            <div class="detail-block">
                <div class="detail-label">Diverifikasi Pada</div>
                <div class="detail-value">{{ !empty($aktivitas->diverifikasi_pada) ? \Carbon\Carbon::parse($aktivitas->diverifikasi_pada)->format('d/m/Y H:i') : '-' }}</div>
            </div>

            <div class="detail-block full">
                <div class="detail-label">Deskripsi Aktivitas</div>
                <div class="detail-value muted">{{ $aktivitas->deskripsi_singkat ?: '-' }}</div>
            </div>
        </div>
    </div>

    <div class="member-detail-card">
        <div class="section-title">Detail Target KM</div>
        <p class="section-desc">Target asal aktivitas yang dibagikan oleh Ketua Lab.</p>

        <div class="detail-grid">
            <div class="detail-block">
                <div class="detail-label">Tahun KM</div>
                <div class="detail-value">{{ $aktivitas->tahun_km ?? '-' }}</div>
            </div>

            <div class="detail-block">
                <div class="detail-label">Lab Riset</div>
                <div class="detail-value">{{ $aktivitas->nama_lab ?? '-' }}</div>
            </div>

            <div class="detail-block full">
                <div class="detail-label">Keterangan KM</div>
                <div class="detail-value muted">{{ $aktivitas->keterangan_km ?? '-' }}</div>
            </div>

            <div class="detail-block full">
                <div class="detail-label">Tenggat Target per Triwulan</div>
                <div class="detail-value">
                    <div class="deadline-list">
                        @forelse($tenggat as $tw => $tanggal)
                            @if(!empty($tanggal))
                                <span class="deadline-pill">
                                    <i class="bi bi-calendar-event"></i>
                                    TW{{ $tw }}: {{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }}
                                </span>
                            @endif
                        @empty
                            <span class="text-muted">-</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="member-detail-card">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="section-title">Bukti Aktivitas</div>
                <p class="section-desc mb-0">Bukti yang digunakan Ketua Lab dalam proses verifikasi.</p>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                @if(!empty($aktivitas->bukti_pdf_path) || !empty($aktivitas->bukti_file_path))
                    <a href="/bukti-km/{{ $aktivitas->id_aktivitas }}/download" class="btn btn-outline-primary">
                        <i class="bi bi-download me-1"></i>
                        Download Bukti
                    </a>
                @endif

                @if(!empty($aktivitas->bukti_link))
                    <a href="{{ $aktivitas->bukti_link }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
                        <i class="bi bi-box-arrow-up-right me-1"></i>
                        Buka Tautan
                    </a>
                @endif

                @if($ditolak)
                    <a href="/anggota/aktivitas-km/{{ $aktivitas->id_aktivitas }}/edit" class="btn btn-primary">
                        <i class="bi bi-pencil-square me-1"></i>
                        Perbaiki Aktivitas
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="member-detail-card">
        <div class="section-title">Riwayat Aksi Verifikasi</div>
        <p class="section-desc">Riwayat keputusan Ketua Lab pada aktivitas ini.</p>

        <div class="table-responsive">
            <table class="table align-middle mb-0 history-table">
                <thead>
                    <tr>
                        <th>Waktu Aksi</th>
                        <th>Keputusan</th>
                        <th>Verifikator</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayatVerifikasi as $riwayat)
                        @php $riwayatDiterima = ($riwayat->keputusan ?? '') === 'Accepted'; @endphp
                        <tr>
                            <td>{{ !empty($riwayat->waktu_aksi) ? \Carbon\Carbon::parse($riwayat->waktu_aksi)->format('d/m/Y H:i') : '-' }}</td>
                            <td>
                                <span class="decision-pill {{ $riwayatDiterima ? 'accepted' : 'rejected' }}">
                                    <i class="bi {{ $riwayatDiterima ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                                    {{ $riwayatDiterima ? 'Disetujui' : 'Ditolak' }}
                                </span>
                            </td>
                            <td>{{ $riwayat->nama_verifikator ?? '-' }}</td>
                            <td>{{ $riwayat->catatan_verifikasi ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Belum ada riwayat aksi verifikasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
