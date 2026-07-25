<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - SIKM Wayan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --blue: #477EF7;
            --blue-soft: #EAF1FF;
            --page-bg: #F4F6FB;
            --text-dark: #20242A;
            --text-muted: #8A8D91;
            --border: #E2E5EA;
            --green-soft: #22C55E;
            --green-text: #ffffff;
            --pink-soft: #EF4444;
            --pink-text: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--page-bg);
            color: var(--text-dark);
            font-family: Arial, Helvetica, sans-serif;
        }

        .app-wrapper {
            min-height: 100vh;
            min-width: 1100px;
            background: var(--page-bg);
        }

        /*
        |--------------------------------------------------------------------------
        | Sidebar Fixed
        |--------------------------------------------------------------------------
        | Sidebar tidak ikut bergerak ketika bagian konten kanan di-scroll.
        */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;

            width: 270px;
            height: 100vh;

            background: #ffffff;
            border-right: 1px solid var(--border);

            overflow-y: auto;
            overflow-x: hidden;

            z-index: 1030;

            scrollbar-width: thin;
            scrollbar-color: #D8DEE8 #ffffff;
        }

        .sidebar::-webkit-scrollbar {
            width: 7px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #ffffff;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #D8DEE8;
            border-radius: 999px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #BFC7D5;
        }

        .brand {
            height: 70px;
            display: flex;
            align-items: center;
            padding: 0 30px;
            font-size: 20px;
            font-weight: 800;
        }

        .brand .brand-blue {
            color: var(--blue);
            margin-right: 4px;
        }

        .sidebar-menu {
            padding-top: 8px;
            padding-bottom: 20px;
        }

        .sidebar-link,
        .sidebar-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 52px;
            padding: 0 30px;
            color: #171A1F;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
        }

        .sidebar-link:hover {
            background: #F3F6FF;
            color: var(--blue);
        }

        .sidebar-link.active {
            background: var(--blue);
            color: #ffffff;
        }

        .sidebar-heading {
            cursor: pointer;
            user-select: none;
        }

        .sidebar-heading:hover {
            background: #F8FAFF;
            color: var(--blue);
        }

        .sidebar-heading i {
            transition: transform 0.2s ease;
        }

        .sidebar-heading.collapsed i {
            transform: rotate(-90deg);
        }

        .sidebar-sub {
            overflow: hidden;
            transition: max-height 0.25s ease;
        }

        .sidebar-sub.is-collapsed {
            max-height: 0;
        }

        .sidebar-sub.is-open {
            max-height: 500px;
        }

        .sidebar-sub .sidebar-link {
            padding-left: 48px;
            font-size: 15px;
        }

        /*
        |--------------------------------------------------------------------------
        | Main Area
        |--------------------------------------------------------------------------
        */
        .main-area {
            margin-left: 270px;
            width: calc(100% - 270px);
            min-width: 0;
            min-height: 100vh;
        }

        .topbar {
            height: 70px;
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 28px 0 50px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .role-pill {
            border: 1px solid #BFC3CA;
            border-radius: 10px;
            padding: 8px 14px;
            background: #ffffff;
            font-size: 14px;
            font-weight: 600;
        }

        .user-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FFB4D8, #8B5CF6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 800;
        }

        .user-name {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .user-role {
            font-size: 12px;
            color: #666;
        }

        .content-wrapper {
            padding: 32px 28px;
        }

        .page-heading {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 26px;
        }

        .page-heading .muted {
            color: #8C8C8C;
        }

        .card {
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #ffffff;
            box-shadow: none;
            padding: 22px;
        }

        table {
            width: 100%;
            background: #ffffff;
            border: 1px solid var(--border) !important;
            border-radius: 12px;
            overflow: hidden;
        }

        table th {
            background: #ffffff;
            color: #34383F;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 18px 22px !important;
            border-bottom: 1px solid var(--border) !important;
        }

        table td {
            padding: 18px 22px !important;
            border-bottom: 1px solid var(--border) !important;
            vertical-align: middle;
        }

        .table-blue th {
            background: var(--blue) !important;
            color: #ffffff !important;
            text-transform: none;
            font-size: 18px;
        }

        .btn {
            border-radius: 8px;
            font-weight: 700;
            padding: 8px 18px;
        }

        .btn-primary {
            background: var(--blue);
            border-color: var(--blue);
        }

        .btn-edit {
            background: var(--green-soft);
            color: var(--green-text);
            border: none;
            min-width: 92px;
        }

        .btn-edit:hover {
            background: #16A34A;
            color: #ffffff;
        }

        .btn-delete {
            background: var(--pink-soft);
            color: var(--pink-text);
            border: none;
            min-width: 92px;
        }

        .btn-delete:hover {
            background: #DC2626;
            color: #ffffff;
        }

        .status-success {
            color: #39B52A;
            font-weight: 800;
        }

        .status-danger {
            color: #FF2F2F;
            font-weight: 800;
        }

        .form-control,
        .form-select {
            border-radius: 0;
            min-height: 46px;
            border: 1px solid #BFC3CA;
        }

        .dropdown-menu {
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        /*
        |--------------------------------------------------------------------------
        | Notifikasi KM
        |--------------------------------------------------------------------------
        */
        .notification-dropdown {
            position: relative;
        }

        .notification-button {
            position: relative;
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: 12px;
            background: #F8FAFC;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all 0.2s ease;
        }

        .notification-button:hover,
        .notification-button:focus {
            background: #EAF1FF;
            color: var(--blue);
        }

        .notification-badge {
            position: absolute;
            top: -6px;
            right: -7px;
            min-width: 20px;
            height: 20px;
            padding: 0 5px;
            border-radius: 999px;
            background: #EF4444;
            border: 2px solid #ffffff;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            line-height: 16px;
            text-align: center;
        }

        .notification-menu {
            width: min(390px, calc(100vw - 32px));
            max-height: 480px;
            padding: 0;
            overflow: hidden;
            border-radius: 16px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
        }

        .notification-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 15px 16px;
            border-bottom: 1px solid #E2E8F0;
        }

        .notification-menu-title {
            font-size: 15px;
            font-weight: 800;
            color: #1E293B;
        }

        .notification-menu-count {
            padding: 4px 8px;
            border-radius: 999px;
            background: #FEE2E2;
            color: #DC2626;
            font-size: 11px;
            font-weight: 800;
        }

        .notification-menu-list {
            max-height: 340px;
            overflow-y: auto;
        }

        .notification-menu-item {
            display: flex;
            gap: 10px;
            padding: 13px 16px;
            border-bottom: 1px solid #EEF2F7;
            color: #334155;
            text-decoration: none;
            transition: background 0.2s ease;
        }

        .notification-menu-item:hover {
            background: #F8FAFC;
            color: #334155;
        }

        .notification-menu-item.unread {
            background: #EFF6FF;
        }

        .notification-menu-icon {
            width: 34px;
            height: 34px;
            min-width: 34px;
            border-radius: 10px;
            background: #EAF1FF;
            color: #2563EB;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
        }

        .notification-menu-item.unread .notification-menu-icon {
            background: #DBEAFE;
        }

        .notification-menu-item-title {
            font-size: 13px;
            font-weight: 800;
            color: #1E293B;
            line-height: 1.35;
        }

        .notification-menu-item-message {
            margin-top: 3px;
            color: #64748B;
            font-size: 12px;
            line-height: 1.45;
        }

        .notification-menu-item-time {
            margin-top: 5px;
            color: #94A3B8;
            font-size: 11px;
            font-weight: 700;
        }

        .notification-menu-empty {
            padding: 30px 18px;
            color: #64748B;
            text-align: center;
            font-size: 13px;
        }

        .notification-menu-footer {
            display: block;
            padding: 12px 16px;
            background: #F8FAFC;
            color: var(--blue);
            text-align: center;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
        }

        .notification-menu-footer:hover {
            color: #1D4ED8;
            background: #EFF6FF;
        }

        /* =========================================================
            Anti Slop UI Polish - Tambahan Aman
            Jangan hapus CSS lama di atas
        ========================================================= */

        :root {
            --ui-primary: #2563EB;
            --ui-primary-dark: #1D4ED8;
            --ui-primary-soft: #EFF6FF;
            --ui-bg: #F5F7FB;
            --ui-surface: #FFFFFF;
            --ui-border: #E5E7EB;
            --ui-text: #111827;
            --ui-muted: #64748B;
            --ui-radius: 14px;
        }

        body {
            background: var(--ui-bg);
            color: var(--ui-text);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 14px;
        }

        .sidebar {
            background: var(--ui-surface);
            border-right: 1px solid var(--ui-border);
        }

        .brand {
            border-bottom: 1px solid var(--ui-border);
            letter-spacing: -0.03em;
        }

        .sidebar-menu {
            padding: 16px 14px 24px;
        }

        .sidebar-link,
        .sidebar-heading {
            position: relative;
            min-height: 44px;
            margin-bottom: 4px;
            padding: 0 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 650;
            color: #334155;

        }

        .sidebar-link:hover,
        .sidebar-heading:hover {
            background: #F8FAFC;
            color: var(--ui-primary);
        }

        .sidebar-link.active {
            background: var(--ui-primary-soft);
            color: var(--ui-primary-dark);
        }

        .sidebar-link.active::before {
            content: "";
            position: absolute;
            left: 7px;
            top: 11px;
            bottom: 11px;
            width: 3px;
            border-radius: 999px;
            background: var(--ui-primary);
        }

        .sidebar-sub .sidebar-link {
            min-height: 40px;
            margin-left: 10px;
            padding-left: 18px;
            font-size: 13px;
        }

        .topbar {
            position: sticky;
            top: 0;
            height: 72px;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--ui-border);
            z-index: 1020;
        }

        .role-pill {
            border-radius: 999px;
            background: #F8FAFC;
            border: 1px solid var(--ui-border);
            color: #334155;
            font-size: 12px;
            font-weight: 750;
        }

        .avatar {
            background: #E0ECFF;
            color: var(--ui-primary-dark);
            border: 1px solid #BFDBFE;
        }

        .page-heading {
            font-size: 30px;
            letter-spacing: -0.04em;
            color: var(--ui-text);
        }

        .card {
            border: 1px solid var(--ui-border);
            border-radius: 18px;
            background: var(--ui-surface);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        table {
            border-radius: 14px;
            border: 1px solid var(--ui-border) !important;
        }

        table th {
            background: #F8FAFC;
            color: #475569;
            font-size: 12px;
            letter-spacing: 0.04em;
        }

        table td {
            color: #334155;
        }

        table tbody tr:hover {
            background: #FAFBFF;
        }

        .form-control,
        .form-select {
            min-height: 44px;
            border-radius: 12px;
            border: 1px solid #CBD5E1;
            font-size: 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--ui-primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .btn {
            border-radius: 12px;
            font-weight: 750;
        }

        .btn-primary {
            background: var(--ui-primary);
            border-color: var(--ui-primary);
        }

        .btn-primary:hover {
            background: var(--ui-primary-dark);
            border-color: var(--ui-primary-dark);
        }

        .dropdown-menu {
            border-radius: 14px;
            border: 1px solid var(--ui-border);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        }

        .dashboard-stat-grid {
            gap: 12px;
        }

        .dashboard-stat-card {
            min-height: 250px;
            padding: 18px;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: none;
        }

        .dashboard-stat-card::before {
            display: none;
        }

        .dashboard-stat-card:hover {
            transform: none;
            border-color: #CBD5E1;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        }

        .dashboard-stat-card-top {
            align-items: center;
            margin-bottom: 14px;
        }

        .dashboard-stat-label {
            color: #0F172A;
            font-size: 15px;
            font-weight: 750;
        }

        .dashboard-stat-subtitle {
            margin-top: 3px;
            color: #64748B;
            font-size: 12px;
            font-weight: 500;
        }

        .dashboard-stat-value {
            min-width: auto;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            color: #1D4ED8;
            font-size: 20px;
            font-weight: 750;
        }

        .dashboard-category-progress {
            height: 6px;
            margin-bottom: 16px;
            background: #E2E8F0;
        }

        .dashboard-category-progress-fill {
            background: #2563EB;
        }

        .dashboard-km-info {
            gap: 8px;
        }

        .dashboard-km-item {
            min-height: 58px;
            padding: 10px 12px;
            border-color: #E2E8F0 !important;
            background: #F8FAFC !important;
        }

        .dashboard-km-item-label {
            margin-bottom: 5px;
            color: #64748B;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .dashboard-km-item-value {
            font-size: 20px;
            font-weight: 750;
        }

        .dashboard-status-note {
            min-height: 34px;
            margin-top: auto;
            margin-bottom: 10px;
            padding-top: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .dashboard-category-button {
            align-self: flex-start;
            width: auto;
            padding: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            color: #2563EB;
            font-size: 12px;
            font-weight: 700;
        }

        .dashboard-category-button::after {
            content: " →";
        }

        .dashboard-category-button:hover {
            background: transparent;
            box-shadow: none;
            color: #1D4ED8;
            text-decoration: underline;
            opacity: 1;
        }
    </style>
</head>

<body>
    @auth
    @php
    $role = auth()->user()->role;

    $profileUrl = '#';

    if ($role === 'Ketua KK') {
    $profileUrl = '/ketuakk/profil';
    } elseif ($role === 'Ketua Lab') {
    $profileUrl = '/ketualab/profil';
    } elseif ($role === 'Anggota') {
    $profileUrl = '/anggota/profil';
    }

    $notifikasiTerbaru = collect();
    $jumlahNotifikasiBelumDibaca = 0;

    if (in_array($role, ['Ketua KK', 'Ketua Lab', 'Anggota'], true)) {
    $notifikasiTerbaru = \App\Models\Notifikasi::query()
    ->where('id_user', auth()->user()->id_user)
    ->latest('created_at')
    ->limit(6)
    ->get();

    $jumlahNotifikasiBelumDibaca = \App\Models\Notifikasi::query()
    ->where('id_user', auth()->user()->id_user)
    ->whereNull('dibaca_pada')
    ->count();
    }
    @endphp

    <div class="app-wrapper">
        <aside class="sidebar">
            <div class="brand">
                <span class="brand-blue">KM</span> EIMS
            </div>

            <nav class="sidebar-menu">
                @if($role === 'Ketua KK')
                <a class="sidebar-link {{ request()->is('ketuakk/dashboard') ? 'active' : '' }}"
                    href="/ketuakk/dashboard">
                    <span>Dashboard</span>
                </a>

                <div class="sidebar-heading js-sidebar-toggle collapsed" data-target="dataMasterMenu">
                    <span>Data Master</span>
                    <i class="bi bi-chevron-down"></i>
                </div>

                <div class="sidebar-sub is-collapsed" id="dataMasterMenu">
                    <a class="sidebar-link {{ request()->is('ketuakk/data-lab-riset') ? 'active' : '' }}"
                        href="/ketuakk/data-lab-riset">
                        <span>Data Lab. Riset</span>
                    </a>

                    <a class="sidebar-link {{ request()->is('ketuakk/data-dosen') ? 'active' : '' }}"
                        href="/ketuakk/data-dosen">
                        <span>Data Anggota KK</span>
                    </a>
                </div>

                <div class="sidebar-heading js-sidebar-toggle collapsed" data-target="kontrakMenu">
                    <span>Kontrak Manajemen</span>
                    <i class="bi bi-chevron-down"></i>
                </div>

                <div class="sidebar-sub is-collapsed" id="kontrakMenu">
                    <a class="sidebar-link {{ request()->is('ketuakk/km-kk*') || request()->is('ketuakk/target-km*') ? 'active' : '' }}"
                        href="/ketuakk/km-kk">
                        <span>Kelompok Keahlian</span>
                    </a>

                    <a class="sidebar-link {{ request()->is('ketuakk/km-lab-riset*') ? 'active' : '' }}"
                        href="/ketuakk/km-lab-riset">
                        <span>Lab. Riset</span>
                    </a>

                    <a class="sidebar-link {{ request()->is('ketuakk/km-anggota-kk*') ? 'active' : '' }}"
                        href="/ketuakk/km-anggota-kk">
                        <span>Anggota KK</span>
                    </a>
                </div>

                <div class="sidebar-heading js-sidebar-toggle collapsed" data-target="monitoringMenu">
                    <span>Monitoring</span>
                    <i class="bi bi-chevron-down"></i>
                </div>

                <div class="sidebar-sub is-collapsed" id="monitoringMenu">
                    <a class="sidebar-link {{ request()->is('ketuakk/monitoring-lab-riset*') ? 'active' : '' }}"
                        href="/ketuakk/monitoring-lab-riset">
                        <span>Monitoring Lab. Riset</span>
                    </a>

                    <a class="sidebar-link {{ request()->is('ketuakk/monitoring-anggota-kk*') ? 'active' : '' }}"
                        href="/ketuakk/monitoring-anggota-kk">
                        <span>Monitoring Anggota KK</span>
                    </a>

                    <a class="sidebar-link {{ request()->is('ketuakk/laporan*') ? 'active' : '' }}"
                        href="/ketuakk/laporan">
                        <span>Laporan</span>
                    </a>
                </div>

                @elseif($role === 'Ketua Lab')
                <a class="sidebar-link {{ request()->is('ketualab/dashboard') ? 'active' : '' }}"
                    href="/ketualab/dashboard">
                    <span>Dashboard</span>
                </a>

                <a class="sidebar-link {{ request()->is('ketualab/penurunan-km*') ? 'active' : '' }}"
                    href="/ketualab/penurunan-km">
                    <span>Target KM Anggota</span>
                </a>

                <a class="sidebar-link {{ request()->is('ketualab/monitoring-lab') ? 'active' : '' }}"
                    href="/ketualab/monitoring-lab">
                    <span>Monitoring KM Lab</span>
                </a>

                <a class="sidebar-link {{ request()->is('ketualab/monitoring-anggota') || request()->is('ketualab/detail-anggota*') ? 'active' : '' }}"
                    href="/ketualab/monitoring-anggota">
                    <span>Monitoring Anggota</span>
                </a>

                <a class="sidebar-link {{ request()->is('ketualab/laporan') ? 'active' : '' }}"
                    href="/ketualab/laporan">
                    <span>Laporan</span>
                </a>

                @elseif($role === 'Anggota')
                <a class="sidebar-link {{ request()->is('anggota/dashboard') ? 'active' : '' }}"
                    href="/anggota/dashboard">
                    <span>Dashboard</span>
                </a>

                <a class="sidebar-link {{ request()->is('anggota/aktivitas-km*') ? 'active' : '' }}"
                    href="/anggota/aktivitas-km">
                    <span>Aktivitas KM</span>
                </a>

                <a class="sidebar-link {{ request()->is('anggota/laporan*') ? 'active' : '' }}"
                    href="{{ route('anggota.laporan.index') }}">
                    <span>Laporan</span>
                </a>
                @endif
            </nav>
        </aside>

        <main class="main-area">
            <header class="topbar">
                <div class="topbar-right">
                    @if(in_array(auth()->user()->role, ['Ketua KK', 'Ketua Lab', 'Anggota'], true))
                    <div class="dropdown notification-dropdown">
                        <button
                            class="notification-button"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            aria-label="Buka notifikasi">
                            <i class="bi bi-bell-fill"></i>

                            @if($jumlahNotifikasiBelumDibaca > 0)
                            <span class="notification-badge">
                                {{ $jumlahNotifikasiBelumDibaca > 99 ? '99+' : $jumlahNotifikasiBelumDibaca }}
                            </span>
                            @endif
                        </button>

                        <div class="dropdown-menu dropdown-menu-end notification-menu">
                            <div class="notification-menu-header">
                                <span class="notification-menu-title">Notifikasi</span>

                                @if($jumlahNotifikasiBelumDibaca > 0)
                                <span class="notification-menu-count">
                                    {{ $jumlahNotifikasiBelumDibaca }} baru
                                </span>
                                @endif
                            </div>

                            <div class="notification-menu-list">
                                @forelse($notifikasiTerbaru as $notifikasi)
                                <a
                                    href="{{ route('notifikasi.baca', $notifikasi->id_notifikasi) }}"
                                    class="notification-menu-item {{ empty($notifikasi->dibaca_pada) ? 'unread' : '' }}">

                                    <span class="notification-menu-icon">
                                        @if(str_starts_with($notifikasi->jenis_notifikasi, 'peringatan_tenggat'))
                                        <i class="bi bi-clock-history"></i>
                                        @elseif(str_starts_with($notifikasi->jenis_notifikasi, 'aktivitas_km'))
                                        <i class="bi bi-clipboard2-check-fill"></i>
                                        @else
                                        <i class="bi bi-bell-fill"></i>
                                        @endif
                                    </span>

                                    <span class="flex-grow-1">
                                        <span class="notification-menu-item-title d-block">
                                            {{ $notifikasi->judul }}
                                        </span>

                                        <span class="notification-menu-item-message d-block">
                                            {{ \Illuminate\Support\Str::limit($notifikasi->pesan, 120) }}
                                        </span>

                                        <span class="notification-menu-item-time d-block">
                                            {{ optional($notifikasi->created_at)->format('d/m/Y H:i') }}
                                        </span>
                                    </span>
                                </a>
                                @empty
                                <div class="notification-menu-empty">
                                    Belum ada notifikasi.
                                </div>
                                @endforelse
                            </div>

                            <a href="{{ route('notifikasi.index') }}" class="notification-menu-footer">
                                Lihat Semua Notifikasi
                            </a>
                        </div>
                    </div>
                    @endif

                    @if(auth()->user()->role === 'Ketua KK')
                    <div class="role-pill">Ketua KK EIMS</div>
                    @else
                    <div class="role-pill">{{ auth()->user()->role }}</div>
                    @endif

                    <div class="dropdown">
                        <button class="btn p-0 border-0 bg-transparent dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown">

                            <div class="user-box">
                                <div class="avatar">
                                    {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
                                </div>

                                <div class="text-start">
                                    <div class="user-name">
                                        {{ auth()->user()->username }}
                                    </div>

                                    <div class="user-role">
                                        {{ auth()->user()->role }}
                                    </div>
                                </div>
                            </div>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <a class="dropdown-item" href="{{ $profileUrl }}">
                                    <i class="bi bi-person me-2"></i>
                                    Profil
                                </a>
                            </li>

                            <li>
                                <hr class="dropdown-divider">
                            </li>

                            <li>
                                <form action="/logout" method="POST">
                                    @csrf

                                    <button class="dropdown-item text-danger" type="submit">
                                        <i class="bi bi-box-arrow-right me-2"></i>
                                        Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <section class="content-wrapper">
                @yield('content')
            </section>
        </main>
    </div>
    @else
    @yield('content')
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @if(session('success') || session('error'))
    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 18px;">
                <div class="modal-body text-center p-5">
                    @if(session('success'))
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                        style="width: 72px; height: 72px; border-radius: 50%; background:#E9F8EF; color:#28A745;">
                        <i class="bi bi-check-lg" style="font-size: 38px;"></i>
                    </div>

                    <h4 class="fw-bold mb-2">Data Berhasil Diproses</h4>
                    <p class="text-muted mb-4">{{ session('success') }}</p>

                    <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">
                        Oke
                    </button>
                    @endif

                    @if(session('error'))
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                        style="width: 72px; height: 72px; border-radius: 50%; background:#FDECEC; color:#DC3545;">
                        <i class="bi bi-x-lg" style="font-size: 32px;"></i>
                    </div>

                    <h4 class="fw-bold mb-2">Data Gagal Diproses</h4>
                    <p class="text-muted mb-4">{{ session('error') }}</p>

                    <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">
                        Oke
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 18px;">
                <div class="modal-body text-center p-5">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                        style="width: 72px; height: 72px; border-radius: 50%; background:#FDECEC; color:#DC3545;">
                        <i class="bi bi-trash3" style="font-size: 34px;"></i>
                    </div>

                    <h4 class="fw-bold mb-2">Konfirmasi Hapus Data</h4>

                    <p class="text-muted mb-4" id="deleteConfirmText">
                        Apakah Anda yakin ingin menghapus data ini?
                    </p>

                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                            Batal
                        </button>

                        <button type="button" class="btn btn-delete px-4" id="deleteConfirmButton">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            /*
            |--------------------------------------------------------------------------
            | Modal status sukses / error
            |--------------------------------------------------------------------------
            */
            const statusModalElement = document.getElementById('statusModal');

            if (statusModalElement && window.bootstrap) {
                const statusModal = new bootstrap.Modal(statusModalElement);
                statusModal.show();
            }

            /*
            |--------------------------------------------------------------------------
            | Modal konfirmasi hapus
            |--------------------------------------------------------------------------
            */
            const deleteModalElement = document.getElementById('deleteConfirmModal');
            const deleteButton = document.getElementById('deleteConfirmButton');
            const deleteText = document.getElementById('deleteConfirmText');

            let selectedDeleteForm = null;

            document.querySelectorAll('.js-delete-form').forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();

                    selectedDeleteForm = form;

                    const message = form.getAttribute('data-message') ||
                        'Apakah Anda yakin ingin menghapus data ini?';

                    if (deleteText) {
                        deleteText.textContent = message;
                    }

                    if (deleteModalElement && window.bootstrap) {
                        const deleteModal = new bootstrap.Modal(deleteModalElement);
                        deleteModal.show();
                    }
                });
            });

            if (deleteButton) {
                deleteButton.addEventListener('click', function() {
                    if (selectedDeleteForm) {
                        selectedDeleteForm.submit();
                    }
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Sidebar dropdown tetap terbuka saat pindah halaman
            |--------------------------------------------------------------------------
            */
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggles = document.querySelectorAll('.js-sidebar-toggle');

            function getStorageValue(key) {
                try {
                    return localStorage.getItem(key);
                } catch (error) {
                    return null;
                }
            }

            function setStorageValue(key, value) {
                try {
                    localStorage.setItem(key, value);
                } catch (error) {
                    // Tidak perlu menghentikan halaman bila browser memblokir localStorage.
                }
            }

            function setSidebarMenuState(toggle, target, isOpen) {
                if (!toggle || !target) {
                    return;
                }

                if (isOpen) {
                    target.classList.remove('is-collapsed');
                    target.classList.add('is-open');
                    toggle.classList.remove('collapsed');
                } else {
                    target.classList.remove('is-open');
                    target.classList.add('is-collapsed');
                    toggle.classList.add('collapsed');
                }
            }

            sidebarToggles.forEach(function(toggle) {
                const targetId = toggle.getAttribute('data-target');
                const target = document.getElementById(targetId);

                if (!target) {
                    return;
                }

                const storageKey = 'sidebar-menu-' + targetId;
                const savedState = getStorageValue(storageKey);

                /*
                | Jika ada menu aktif di dropdown ini,
                | dropdown otomatis dibuka agar menu aktif terlihat.
                */
                const adaMenuAktif = target.querySelector('.sidebar-link.active') !== null;

                const shouldOpen = adaMenuAktif || savedState === 'open';

                setSidebarMenuState(toggle, target, shouldOpen);

                toggle.addEventListener('click', function() {
                    const sedangTerbuka = target.classList.contains('is-open');
                    const bukaMenu = !sedangTerbuka;

                    setSidebarMenuState(toggle, target, bukaMenu);

                    setStorageValue(
                        storageKey,
                        bukaMenu ? 'open' : 'closed'
                    );
                });
            });

            /*
            |--------------------------------------------------------------------------
            | Menyimpan posisi scroll sidebar
            |--------------------------------------------------------------------------
            | Saat pindah halaman, sidebar kembali pada posisi scroll terakhir.
            */
            if (sidebar) {
                const sidebarScrollKey = 'sidebar-scroll-position';
                const savedSidebarScroll = parseInt(
                    getStorageValue(sidebarScrollKey) || '0',
                    10
                );

                if (!Number.isNaN(savedSidebarScroll)) {
                    sidebar.scrollTop = savedSidebarScroll;
                }

                sidebar.addEventListener('scroll', function() {
                    setStorageValue(
                        sidebarScrollKey,
                        sidebar.scrollTop.toString()
                    );
                });
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>

</html>