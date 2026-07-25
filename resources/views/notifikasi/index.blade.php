@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<style>
    .notification-page-card {
        padding: 0;
        overflow: hidden;
    }

    .notification-page-header {
        padding: 22px 24px;
        border-bottom: 1px solid #E5E7EB;
    }

    .notification-page-item {
        display: flex;
        gap: 15px;
        padding: 18px 24px;
        border-bottom: 1px solid #EEF2F7;
        text-decoration: none;
        color: inherit;
        transition: background 0.2s ease;
    }

    .notification-page-item:hover {
        background: #F8FAFC;
        color: inherit;
    }

    .notification-page-item.unread {
        background: #EFF6FF;
    }

    .notification-page-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 12px;
        background: #EAF1FF;
        color: #2563EB;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
    }

    .notification-page-item.unread .notification-page-icon {
        background: #DBEAFE;
    }

    .notification-page-title {
        font-size: 15px;
        font-weight: 800;
        color: #1E293B;
    }

    .notification-page-message {
        margin-top: 4px;
        color: #64748B;
        font-size: 14px;
        line-height: 1.55;
    }

    .notification-page-time {
        margin-top: 7px;
        font-size: 12px;
        color: #94A3B8;
        font-weight: 700;
    }

    .notification-unread-dot {
        width: 9px;
        height: 9px;
        min-width: 9px;
        margin-top: 6px;
        border-radius: 50%;
        background: #EF4444;
    }

    .notification-empty {
        padding: 62px 20px;
        text-align: center;
        color: #64748B;
    }

    .notification-empty i {
        display: block;
        margin-bottom: 12px;
        font-size: 42px;
        color: #94A3B8;
    }
</style>

<div class="page-heading">
    Notifikasi <span class="muted">Aktivitas & KM</span>
</div>

<div class="card notification-page-card">
    <div class="notification-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Semua Notifikasi</h4>
            <p class="text-muted mb-0">
                Informasi aktivitas anggota, penurunan KM, serta pengingat tenggat penyelesaian KM.
            </p>
        </div>

        <form action="{{ route('notifikasi.baca-semua') }}" method="POST">
            @csrf

            <button type="submit" class="btn btn-secondary">
                <i class="bi bi-check2-all me-1"></i>
                Tandai Semua Dibaca
            </button>
        </form>
    </div>

    @forelse($notifikasi as $item)
        <a
            href="{{ route('notifikasi.baca', $item->id_notifikasi) }}"
            class="notification-page-item {{ empty($item->dibaca_pada) ? 'unread' : '' }}">

            <div class="notification-page-icon">
                @if(str_starts_with($item->jenis_notifikasi, 'peringatan_tenggat'))
                    <i class="bi bi-clock-history"></i>
                @elseif(str_starts_with($item->jenis_notifikasi, 'aktivitas_km'))
                    <i class="bi bi-clipboard2-check-fill"></i>
                @else
                    <i class="bi bi-bell-fill"></i>
                @endif
            </div>

            <div class="flex-grow-1">
                <div class="notification-page-title">
                    {{ $item->judul }}
                </div>

                <div class="notification-page-message">
                    {{ $item->pesan }}
                </div>

                <div class="notification-page-time">
                    {{ optional($item->created_at)->format('d/m/Y H:i') }}
                </div>
            </div>

            @if(empty($item->dibaca_pada))
                <span class="notification-unread-dot"></span>
            @endif
        </a>
    @empty
        <div class="notification-empty">
            <i class="bi bi-bell-slash"></i>
            <strong>Belum ada notifikasi.</strong>
            <div class="mt-1">
                Notifikasi aktivitas anggota, KM baru, dan pengingat tenggat akan muncul di halaman ini.
            </div>
        </div>
    @endforelse

    @if($notifikasi->hasPages())
        <div class="p-3 border-top">
            {{ $notifikasi->links() }}
        </div>
    @endif
</div>
@endsection
