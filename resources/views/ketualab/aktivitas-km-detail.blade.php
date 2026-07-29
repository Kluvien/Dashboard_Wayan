@extends('layouts.app')

@section('title', 'Detail Verifikasi Aktivitas KM')

@section('content')
@php
    $riwayatAktivitas = collect($riwayatAktivitas ?? []);

    $status = $aktivitas->status_progress ?? '-';
    $statusLabel = match($status) {
        'Accepted' => 'Disetujui',
        'Submitted' => 'Diajukan',
        'On Progress' => 'Sedang Berjalan',
        'Rejected' => 'Ditolak',
        default => $status,
    };

    $statusClass = match($status) {
        'Accepted' => 'success',
        'Submitted', 'On Progress' => 'warning',
        'Rejected' => 'danger',
        default => 'secondary',
    };
@endphp

<style>
    .verification-detail-page {
        padding-bottom: 28px;
    }

    .verification-card,
    .verification-info-card {
        padding: 18px;
        border: 1px solid #E2E8F0;
        border-radius: 17px;
        background: #FFFFFF;
        box-shadow: 0 5px 15px rgba(15, 23, 42, .04);
    }

    .verification-card {
        margin-bottom: 16px;
    }

    .verification-title {
        color: #0F172A;
        font-size: 20px;
        font-weight: 900;
        margin-bottom: 4px;
    }

    .verification-subtitle {
        margin: 0;
        color: #64748B;
        font-size: 13px;
        line-height: 1.45;
    }

    .verification-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .status-success { color: #15803D; background: #DCFCE7; }
    .status-warning { color: #B45309; background: #FEF3C7; }
    .status-danger { color: #B91C1C; background: #FEE2E2; }
    .status-secondary { color: #475569; background: #E5E7EB; }

    .btn-verification-primary,
    .btn-verification-secondary,
    .btn-approve,
    .btn-reject {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 40px;
        padding: 0 14px;
        border: 0;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-verification-primary {
        background: #4F7DF3;
        color: #FFFFFF;
    }

    .btn-verification-primary:hover {
        background: #3E6DE8;
        color: #FFFFFF;
    }

    .btn-verification-secondary {
        background: #6B7280;
        color: #FFFFFF;
    }

    .btn-verification-secondary:hover {
        background: #4B5563;
        color: #FFFFFF;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 1.2fr .8fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    .section-title {
        color: #111827;
        font-size: 17px;
        font-weight: 900;
        margin-bottom: 4px;
    }

    .section-desc {
        margin: 0 0 14px;
        color: #64748B;
        font-size: 13px;
    }

    .activity-name {
        color: #0F172A;
        font-size: 18px;
        font-weight: 900;
        line-height: 1.35;
    }

    .activity-description {
        margin-top: 11px;
        color: #475569;
        font-size: 13px;
        line-height: 1.65;
        white-space: pre-line;
    }

    .info-list {
        display: grid;
        gap: 10px;
    }

    .info-row {
        padding: 10px 11px;
        border: 1px solid #E2E8F0;
        border-radius: 11px;
        background: #F8FAFC;
    }

    .info-label {
        margin-bottom: 3px;
        color: #64748B;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .2px;
        text-transform: uppercase;
    }

    .info-value {
        color: #1E293B;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.4;
    }

    .proof-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px;
        border: 1px dashed #93C5FD;
        border-radius: 12px;
        background: #EFF6FF;
    }

    .proof-title {
        color: #1D4ED8;
        font-size: 13px;
        font-weight: 900;
    }

    .proof-caption {
        margin-top: 3px;
        color: #64748B;
        font-size: 11px;
        line-height: 1.4;
    }

    .approval-decision-card {
        padding: 18px;
        border: 1px solid #FDE68A;
        border-left: 5px solid #F59E0B;
        border-radius: 17px;
        background: linear-gradient(135deg, #FFFBEB 0%, #FFFFFF 72%);
        box-shadow: 0 5px 15px rgba(180, 83, 9, .08);
        margin-bottom: 16px;
    }

    .approval-decision-title {
        color: #92400E;
        font-size: 18px;
        font-weight: 900;
        margin-bottom: 4px;
    }

    .approval-decision-desc {
        margin: 0;
        color: #A16207;
        font-size: 13px;
        line-height: 1.5;
    }

    .approval-action-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 13px;
        margin-top: 15px;
    }

    .approval-action {
        padding: 14px;
        border: 1px solid #E2E8F0;
        border-radius: 13px;
        background: #FFFFFF;
    }

    .approval-action-title {
        color: #0F172A;
        font-size: 14px;
        font-weight: 900;
        margin-bottom: 4px;
    }

    .approval-action-desc {
        min-height: 37px;
        margin-bottom: 12px;
        color: #64748B;
        font-size: 12px;
        line-height: 1.45;
    }

    .btn-approve {
        width: 100%;
        background: #16A34A;
        color: #FFFFFF;
    }

    .btn-approve:hover {
        background: #15803D;
        color: #FFFFFF;
    }

    .btn-reject {
        width: 100%;
        background: #DC2626;
        color: #FFFFFF;
    }

    .btn-reject:hover {
        background: #B91C1C;
        color: #FFFFFF;
    }

    .reject-note {
        width: 100%;
        min-height: 95px;
        margin-bottom: 9px;
        padding: 10px 11px;
        border: 1px solid #FECACA;
        border-radius: 10px;
        color: #334155;
        font-size: 12px;
        resize: vertical;
    }

    .reject-note:focus {
        outline: none;
        border-color: #F87171;
        box-shadow: 0 0 0 3px rgba(248, 113, 113, .14);
    }

    .verification-result-card {
        margin-bottom: 16px;
        padding: 15px;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        background: #F8FAFC;
    }

    .verification-result-card.accepted {
        border-color: #BBF7D0;
        background: #ECFDF5;
    }

    .verification-result-card.rejected {
        border-color: #FECACA;
        background: #FEF2F2;
    }

    .history-table {
        width: 100%;
        width: 100%;
        border-collapse: collapse;
    }

    .history-table th {
        padding: 10px 8px;
        color: #475569;
        border-bottom: 1px solid #E2E8F0;
        background: #F8FAFC;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .history-table td {
        padding: 11px 8px;
        color: #334155;
        border-bottom: 1px solid #F1F5F9;
        font-size: 12px;
        vertical-align: middle;
    }

    .history-note {
        max-width: 360px;
        color: #64748B !important;
        line-height: 1.45;
    }

    @media (max-width: 1000px) {
        .detail-grid,
        .approval-action-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .proof-box {
            align-items: flex-start;
            flex-direction: column;
        }

        .btn-verification-primary,
        .btn-verification-secondary {
            width: 100%;
        }
    }
    .ketualab-activity-detail__header { padding: 20px 22px; margin-bottom: 16px; background: #FFF; border: 1px solid #E2E8F0; border-radius: 14px; }
    .ketualab-activity-detail__eyebrow { margin: 0 0 5px; color: #2563EB; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .ketualab-activity-detail__title { margin: 0; color: #0F172A; font-size: 24px; font-weight: 700; }
    .ketualab-aktivitas-detail__section{min-width:0}.ketualab-aktivitas-detail__section .section-title{margin:0;color:#1F2937;font-size:18px;line-height:1.4}.ketualab-aktivitas-detail__section .info-label,.ketualab-aktivitas-detail__section .info-value{font-size:14px;line-height:1.5}.ketualab-aktivitas-detail__section .proof-box{min-width:0;flex-wrap:wrap}.ketualab-aktivitas-detail__section .proof-caption{overflow-wrap:anywhere}.ketualab-aktivitas-detail__section textarea{width:100%;min-height:110px}
</style>

<div class="verification-detail-page">
    <header class="ketualab-activity-detail__header">
        <p class="ketualab-activity-detail__eyebrow">Verifikasi Anggota Lab</p>
        <h1 class="ketualab-activity-detail__title">Detail Verifikasi Aktivitas KM</h1>
        <p class="verification-subtitle mb-0">Tinjau rincian aktivitas dan bukti sebelum mengambil keputusan.</p>
    </header>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Verifikasi belum dapat diproses:</strong>
            <ul class="mb-0 mt-2 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="verification-card">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="verification-title">Rincian Aktivitas KM</div>
                <p class="verification-subtitle">
                    Periksa informasi aktivitas, kesesuaian target KM, dan bukti sebelum mengambil keputusan verifikasi.
                </p>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="verification-status status-{{ $statusClass }}">
                    <i class="bi {{ $status === 'Accepted' ? 'bi-check-circle-fill' : ($status === 'Rejected' ? 'bi-x-circle-fill' : 'bi-hourglass-split') }}"></i>
                    {{ $statusLabel }}
                </span>
                <a href="{{ $backUrl }}" class="btn-verification-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="detail-grid">
        <section class="verification-info-card ketualab-aktivitas-detail__section" aria-labelledby="aktivitas-diajukan-title">
            <h2 id="aktivitas-diajukan-title" class="section-title">Aktivitas yang Diajukan</h2>
            <p class="section-desc">Informasi inti yang diinput oleh anggota.</p>

            <div class="activity-name">{{ $aktivitas->judul_aktivitas ?? '-' }}</div>
            <div class="activity-description">{{ $aktivitas->deskripsi_singkat ?: 'Tidak ada deskripsi aktivitas.' }}</div>

            <div class="mt-4">
                <div class="proof-box">
                    <div>
                        <div class="proof-title"><i class="bi bi-paperclip me-1"></i> Bukti Aktivitas</div>
                        <div class="proof-caption">
                            {{ $aktivitas->bukti_file_nama_asli ?? (!empty($aktivitas->bukti_link) ? 'Tautan bukti aktivitas tersedia' : 'Belum ada bukti') }}
                        </div>
                    </div>

                    @if(!empty($aktivitas->bukti_pdf_path) || !empty($aktivitas->bukti_file_path))
                        <a href="/bukti-km/{{ $aktivitas->id_aktivitas }}/download" class="btn-verification-primary">
                            <i class="bi bi-download"></i>
                            Unduh Bukti
                        </a>
                    @elseif(!empty($aktivitas->bukti_link))
                        <a href="{{ $aktivitas->bukti_link }}" target="_blank" rel="noopener" class="btn-verification-primary">
                            <i class="bi bi-box-arrow-up-right"></i>
                            Buka Link
                        </a>
                    @else
                        <span class="small text-danger fw-bold">Bukti belum tersedia</span>
                    @endif
                </div>
            </div>
        </section>

        <section class="verification-info-card ketualab-aktivitas-detail__section" aria-labelledby="anggota-target-title">
            <h2 id="anggota-target-title" class="section-title">Anggota dan Target KM</h2>
            <p class="section-desc">Relasi aktivitas terhadap target KM yang diberikan kepada anggota.</p>

            <div class="info-list">
                <div class="info-row">
                    <div class="info-label">Anggota</div>
                    <div class="info-value">{{ $aktivitas->nama_anggota ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">NIDN / Email</div>
                    <div class="info-value">{{ $aktivitas->nidn_anggota ?? '-' }} · {{ $aktivitas->email_anggota ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tahun · Kategori · Sub Kategori</div>
                    <div class="info-value">{{ $aktivitas->tahun_km ?? '-' }} · {{ $aktivitas->kategori_km ?? '-' }} · {{ $aktivitas->sub_kategori_km ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Keterangan Target KM</div>
                    <div class="info-value">{{ $aktivitas->keterangan_target ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Periode Pelaksanaan</div>
                    <div class="info-value">
                        {{ !empty($aktivitas->tanggal_mulai) ? \Carbon\Carbon::parse($aktivitas->tanggal_mulai)->format('d/m/Y') : '-' }}
                        sampai
                        {{ !empty($aktivitas->tanggal_selesai) ? \Carbon\Carbon::parse($aktivitas->tanggal_selesai)->format('d/m/Y') : '-' }}
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Waktu Pengajuan</div>
                    <div class="info-value">
                        {{ !empty($aktivitas->diajukan_pada) ? \Carbon\Carbon::parse($aktivitas->diajukan_pada)->format('d/m/Y H:i') : '-' }}
                    </div>
                </div>
            </div>
        </section>
    </div>

    @if($status === 'Submitted')
        <div class="approval-decision-card">
            <div class="approval-decision-title">
                <i class="bi bi-shield-check me-1"></i>
                Ambil Keputusan Verifikasi
            </div>
            <p class="approval-decision-desc">
                Aktivitas ini masih menunggu keputusan. Setujui hanya jika rincian dan bukti telah sesuai dengan target KM anggota.
            </p>

            <div class="approval-action-grid">
                <div class="approval-action">
                    <div class="approval-action-title text-success">Setujui Aktivitas</div>
                    <div class="approval-action-desc">Aktivitas akan menjadi realisasi KM dan langsung memengaruhi rekap, monitoring, serta laporan.</div>
                    <form method="POST" action="{{ route('ketualab.aktivitas-km.verifikasi', $aktivitas->id_aktivitas) }}" onsubmit="return confirm('Setujui aktivitas KM ini? Aktivitas akan dihitung sebagai realisasi.');">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="keputusan" value="Accepted">
                        <button type="submit" class="btn-approve">
                            <i class="bi bi-check-lg"></i>
                            Setujui Aktivitas
                        </button>
                    </form>
                </div>

                <div class="approval-action">
                    <div class="approval-action-title text-danger">Tolak Aktivitas</div>
                    <div class="approval-action-desc">Tuliskan catatan agar anggota dapat mengetahui bagian yang harus diperbaiki sebelum mengajukan kembali.</div>
                    <form method="POST" action="{{ route('ketualab.aktivitas-km.verifikasi', $aktivitas->id_aktivitas) }}" onsubmit="return confirm('Tolak aktivitas KM ini? Catatan akan dikirim kepada anggota.');">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="keputusan" value="Rejected">
                        <textarea name="catatan_verifikasi" class="reject-note" placeholder="Contoh: Bukti publikasi belum memuat tautan atau identitas penulis yang sesuai." required>{{ old('catatan_verifikasi') }}</textarea>
                        <button type="submit" class="btn-reject">
                            <i class="bi bi-x-lg"></i>
                            Tolak dengan Catatan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @elseif($status === 'Accepted')
        <div class="verification-result-card accepted">
            <div class="fw-bold text-success"><i class="bi bi-check-circle-fill me-1"></i> Aktivitas telah disetujui.</div>
            <div class="small text-muted mt-1">
                Disetujui pada {{ !empty($aktivitas->diverifikasi_pada) ? \Carbon\Carbon::parse($aktivitas->diverifikasi_pada)->format('d/m/Y H:i') : '-' }} dan dihitung sebagai realisasi KM.
            </div>
        </div>
    @elseif($status === 'Rejected')
        <div class="verification-result-card rejected">
            <div class="fw-bold text-danger"><i class="bi bi-x-circle-fill me-1"></i> Aktivitas telah ditolak.</div>
            <div class="small text-muted mt-1">Catatan kepada anggota: {{ $aktivitas->catatan_verifikasi ?: '-' }}</div>
        </div>
    @endif

    <div class="verification-card">
        <div class="section-title">Riwayat Aksi Verifikasi Aktivitas Ini</div>
        <p class="section-desc">Riwayat disimpan setiap kali Ketua Lab mengambil keputusan atas aktivitas ini.</p>

        <div class="table-responsive">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Verifikator</th>
                        <th>Keputusan</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayatAktivitas as $riwayat)
                        @php $accepted = ($riwayat->keputusan ?? '') === 'Accepted'; @endphp
                        <tr>
                            <td>{{ !empty($riwayat->waktu_aksi) ? \Carbon\Carbon::parse($riwayat->waktu_aksi)->format('d/m/Y H:i') : '-' }}</td>
                            <td>{{ $riwayat->nama_verifikator ?? '-' }}</td>
                            <td>
                                <span class="verification-status status-{{ $accepted ? 'success' : 'danger' }}">
                                    <i class="bi {{ $accepted ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                                    {{ $accepted ? 'Disetujui' : 'Ditolak' }}
                                </span>
                            </td>
                            <td class="history-note">{{ $riwayat->catatan_verifikasi ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Belum ada aksi verifikasi yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
